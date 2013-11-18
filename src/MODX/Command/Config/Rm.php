<?php namespace MODX\Command\Config;

use MODX\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

class Rm extends BaseCmd
{
    protected $name = 'config:rm';
    protected $description = 'Remove an entry from configuration';

    protected function getArguments()
    {
        return array(
            array(
                'service_class',
                InputArgument::REQUIRED,
                'Your component service class name'
            ),
        );
    }

    protected function process()
    {
        $service = $this->argument('service_class');

        /** @var \MODX\Application $app */
        $app = $this->getApplication();
        $original = $app->getCurrentConfig();

        if (!array_key_exists($service, $original)) {
            return $this->error('Component with name ' . $service . ' not found!');
        }

        unset($original[$service]);
        $this->writeConfig($original);

        $this->info('Component '. $service .' removed');
    }

    protected function writeConfig(array $data)
    {
        /** @var \MODX\Application $application */
        $application = $this->getApplication();
        if ($application->writeConfig($data)) {
            return $this->info('Config file updated');
        }

        $this->error('Something went wrong while updating the config');
    }
}
