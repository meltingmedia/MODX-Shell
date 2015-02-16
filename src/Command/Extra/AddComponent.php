<?php namespace MODX\Shell\Command\Extra;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to register the given service as "command provider"
 */
class AddComponent extends BaseCmd
{
    const MODX = true;

    protected $name = 'extra:component:add';
    protected $description = 'Add the given component service class to the "registered" services, so its defined commands could be loaded/used.';

    protected function process()
    {
        $components = $this->getApplication()->getComponentsWithCommands();
        $service = $this->argument('service');
        $lower = strtolower($service);

        if (array_key_exists($lower, $components)) {
            $this->info($service .' is already registered');
            return;
        }

        $components[$lower] = array(
            'service' => $service,
        );

        $saved = $this->getApplication()->storeServices($components);
        if ($saved) {
            $this->info($service. ' successfully registered');
            return;
        }

        $this->error('An error occurred when trying to register '. $service .' service');
    }

    protected function getArguments()
    {
        return array(
            array(
                'service',
                InputArgument::REQUIRED,
                'The service class name'
            ),
        );
    }

    protected function getOptions()
    {
        return array(
            array(
                'parameters',
                'p',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'An array of parameters to be sent to the service when instantiated, ie. --parameters=\'key=value\' --parameters=\'another_key=value\''
            ),
        );
    }
}
