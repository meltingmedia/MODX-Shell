<?php namespace MODX\Shell\Command\Config;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

class Rm extends BaseCmd
{
    protected $name = 'config:rm';
    protected $description = 'Remove an entry from configuration';

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'Your instance name'
            ),
        );
    }

    protected function process()
    {
        $service = $this->argument('name');

        /** @var \MODX\Shell\Application $app */
        $app = $this->getApplication();
        $original = $app->getCurrentConfig();

        if (!array_key_exists($service, $original)) {
            return $this->error('Component with name ' . $service . ' not found!');
        }

        unset($original[$service]);
        $this->writeConfig($original);

        $this->info('Instance '. $service .' removed from configuration');
    }

    protected function writeConfig(array $data)
    {
        /** @var \MODX\Shell\Application $application */
        $application = $this->getApplication();
        if ($application->writeConfig($data)) {
            return $this->info('Config file updated');
        }

        $this->error('Something went wrong while updating the config');
    }
}
