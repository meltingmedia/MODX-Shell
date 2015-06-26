<?php namespace MODX\Shell\Command\Config;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

class SetDefault extends BaseCmd
{
    protected $name = 'config:default:set';
    protected $description = 'Set the default instance to use';

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'Your Default instance name'
            ),
        );
    }

    protected function process()
    {
        $service = $this->argument('name');

        $config = $this->getApplication()->instances;
        $exists = $config->get($service);
        if (!$exists) {
            return $this->error('No configured instance with that name : '. $service);
        }

        $config->setDefaultInstance($service);
        $saved = $config->save();
        if (!$saved) {
            return $this->error('Something went wrong while updating the config');
        }

        return $this->line("Default instance set <info>{$service}</info>");
    }
}
