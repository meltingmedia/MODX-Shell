<?php namespace MODX\Command;

use Symfony\Component\Console\Input\InputOption;

abstract class ProcessorCmd extends BaseCmd
{
    protected $processor;

    protected $defaultsOptions = array();
    protected $defaultsProperties = array();

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

        /** @var \modProcessorResponse $response */
//        $response = $this->modx->runProcessor($this->processor, $properties, $options);
//        if (!$response->getMessage()) {
//            return $this->error('Something went wrong while executing the processor');
//        }
        $response = 'Fake response';

        return $this->processResponse($response);
    }

    //protected function processResponse(\modProcessorResponse &$response)
    protected function processResponse($response)
    {
        $this->info('Override me to process the processor response');
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
                'An array of options to be sent to the processor, ie. --properties=\'processors_path=value\' --properties=\'location=value\''
            ),
        );
    }
}
