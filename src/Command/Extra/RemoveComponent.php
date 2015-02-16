<?php namespace MODX\Shell\Command\Extra;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to un-register the given service as "command provider"
 */
class RemoveComponent extends BaseCmd
{
    const MODX = true;

    protected $name = 'extra:component:rm';
    protected $description = 'Remove the given component service class from the "registered" services.';

    protected function process()
    {
        $components = $this->getApplication()->getComponentsWithCommands();
        $service = $this->argument('service');
        $lower = strtolower($service);

        if (!array_key_exists($lower, $components)) {
            $this->info($service .' not registered');
            return;
        }

        unset($components[$lower]);

        $saved = $this->getApplication()->storeServices($components);
        if ($saved) {
            $this->info($service. ' unregistered');
            return;
        }

        $this->error('An error occurred while trying to un-register service '. $service);
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
}
