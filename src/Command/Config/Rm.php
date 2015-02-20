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
        //$original = $app->getCurrentConfig();

        if (!$app->instances->get($service)) {
            return $this->error('Component with name ' . $service . ' not found!');
        }

        $app->instances->remove($service);
        $app->instances->save();

        $this->info('Instance '. $service .' removed from configuration');
    }
}
