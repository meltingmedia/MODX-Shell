<?php namespace MODX\Command;

use Symfony\Component\Console\Input\InputOption;

abstract class ProcessorCmd extends BaseCmd
{
    const MODX = true;

    protected $processor;

    protected $defaultsOptions = array();
    protected $defaultsProperties = array();

    protected $headers = array();

    protected $required = array();

    /** @var \modProcessorResponse */
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
        $this->handleColumns();

        if ($this->beforeRun($properties, $options) === false) {
            return $this->info('Operation aborted');
        }

        /** @var \modProcessorResponse $response */
        $response = $this->modx->runProcessor($this->processor, $properties, $options);
        if (!($response instanceof \modProcessorResponse) || !$response->getResponse()) {
            $this->error('Something went wrong while executing the processor');
            return $this->error($response->getMessage());
        }
        if ($response->isError()) {
            $errors = $response->getFieldErrors();
            foreach ($errors as $e) {
                //$this->error(print_r($e, true));
                $this->error($e->field .' : '. $e->message);
            }
            //$this->info(print_r(, true));
            return;
        }
        $this->response =& $response;

        return $this->processResponse($this->decodeResponse($response));
    }

    protected function processResponse(array $response = array())
    {
        $this->info('Override me to process the processor response');
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {

    }

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

    protected function parseValue($value, $column)
    {
        $method = 'format'. ucfirst($column);
        if (method_exists($this, $method)) {
            return $this->$method($value);
        }

        return $value;
    }

    protected function renderBoolean($value)
    {
        $result = 'No';
        if ($value) {
            $result = 'Yes';
        }

        return $result;
    }

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
