<?php namespace MODX\Command;

use Symfony\Component\Console\Input\InputOption;

/**
 * A processor based command
 */
abstract class ProcessorCmd extends BaseCmd
{
    const MODX = true;

    /**
     * A processor path
     *
     * @var string
     */
    protected $processor;

    protected $defaultsOptions = array();
    protected $defaultsProperties = array();

    /**
     * An array of columns to be used in tables output
     *
     * @var array
     */
    protected $headers = array();

    /**
     * An array of required arguments to be set as processor properties (parameters)
     *
     * @var array
     */
    protected $required = array();

    /**
     * The processor response
     *
     * @var \modProcessorResponse
     */
    protected $response;

    protected function process()
    {
        $properties = array_merge(
            $this->defaultsProperties,
            $this->processArray('properties')
        );

        $options = array_merge(
            $this->defaultsOptions,
            $this->processArray('options')
        );

        // Place required fields into the properties to be sent to the processor
        if (!empty($this->required)) {
            foreach ($this->required as $field) {
                $properties[$field] = $this->argument($field);
            }
        }

        // Allow "on the fly" columns addition/removal
        $this->handleColumns();

        // Allow the command to break if some criteria aren't met
        if ($this->beforeRun($properties, $options) === false) {
            return $this->info('Operation aborted');
        }

        // @todo some TLC with processors errors/success
        /** @var \modProcessorResponse $response */
        $response = $this->modx->runProcessor($this->processor, $properties, $options);
        if (!($response instanceof \modProcessorResponse) || !$response->getResponse()) {
            $this->error('Something went wrong while executing the processor');
            return $this->error($response->getMessage());
        }
        if ($response->isError()) {
            $errors = $response->getFieldErrors();
            foreach ($errors as $e) {
                $this->error($e->field .' : '. $e->message);
            }
            return;
        }
        $this->response =& $response;

        return $this->processResponse($this->decodeResponse($response));
    }

    /**
     * Command result logic
     *
     * @param array $response
     */
    protected function processResponse(array $response = array())
    {
        $this->info('Override me to process the processor response');
    }

    /**
     *
     * @param array $properties The properties which will be sent to the processor
     * @param array $options The options which will be sent to the processor
     *
     * @return mixed Return false here to break the command execution
     */
    protected function beforeRun(array &$properties = array(), array &$options = array())
    {

    }

    /**
     * Decode the processor response if json encoded
     *
     * @param \modProcessorResponse $response
     *
     * @return array|mixed
     */
    protected function decodeResponse(\modProcessorResponse &$response)
    {
        $results = $response->getResponse();
        if (!is_array($results)) {
            $results = json_decode($results, true);
        }

        return $results;
    }

    /**
     * Process an array value (option/argument)
     *
     * @param string $key The argument/option name
     * @param string $type argument or option
     *
     * @return array
     */
    protected function processArray($key, $type = 'option')
    {
        $result = array();
        foreach ($this->$type($key) as $data) {
            $exp = explode('=', $data);

            $result[trim($exp[0])] = trim($exp[1]);
        }

        return $result;
    }

    protected function getOptions()
    {
        return array(
            array(
                'properties',
                'p',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'An array of properties to be sent to the processor, ie. --properties=\'key=value\' --properties=\'another_key=value\''
            ),
            array(
                'options',
                'o',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'An array of options to be sent to the processor, ie. --options=\'processors_path=value\' --options=\'location=value\''
            ),
            // Tables related
            array(
                'unset',
                'u',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'An array of columns to hidden from results table, ie. --unset=id --unset=name'
            ),
            array(
                'add',
                'a',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'An array of columns to add to results table, ie. --add=column -a\'other_column\''
            ),
        );
    }

// Tables related
// @todo find a cleaner way to handle this ? since all processors do not make use of tables

    /**
     * Allow "on the fly" table columns addition/removal
     *
     * @return void
     */
    protected function handleColumns()
    {
        // Support columns "removal"
        $unset = $this->option('unset');
        if ($unset && !empty($unset)) {
            //$this->info(print_r($unset, true));
            foreach ($unset as $k) {
                if (in_array($k, $this->headers)) {
                    $idx = array_search($k, $this->headers);
                    if ($idx !== false) {
                        unset($this->headers[$idx]);
                    }
                }
            }
        }

        // Support columns "addition"
        $add = $this->option('add');
        if ($add && !empty($add)) {
            foreach ($add as $k) {
                if (!in_array($k, $this->headers)) {
                    $this->headers[] = $k;
                }
            }
        }
    }

    /**
     * Grab the appropriate columns for the given record
     *
     * @param array $record
     *
     * @return array Usable row for the table output
     */
    protected function processRow(array $record = array())
    {
        $result = array();
        foreach ($this->headers as $k) {
            if (!array_key_exists($k, $record)) {
                $result[] = '';
                continue;
            }
            $value = $record[$k];
            $result[] = $this->parseValue($value, $k);
        }

        return $result;
    }

    /**
     * Allow raw values to be "formatted"
     *
     * @param mixed $value
     * @param string $column
     *
     * @return mixed
     */
    protected function parseValue($value, $column)
    {
        $method = 'format'. ucfirst($column);
        if (method_exists($this, $method)) {
            return $this->$method($value);
        }

        return $value;
    }

    /**
     *
     * @param string $value
     *
     * @return string
     */
    protected function renderBoolean($value)
    {
        $result = 'No';
        if ($value) {
            $result = 'Yes';
        }

        return $result;
    }

    /**
     * Retrieve a column value for the given object
     *
     * @param string $class The object class
     * @param mixed $pk The primary key or criteria to grab the object
     * @param string $column The desired column value
     *
     * @return mixed Either the column value if found, or the given primary key
     */
    protected function renderObject($class, $pk, $column)
    {
        if ($pk && $pk != '0') {
            /** @var \xPDOObject $object */
            $object = $this->modx->getObject($class, $pk);
            if ($object instanceof \xPDOObject) {
                return $object->get($column);
            }
        }

        return $pk;
    }
}
