<?php namespace MODX\Shell\Command\Config;

use MODX\Shell\Command\BaseCmd;

class GetDefault extends BaseCmd
{
    protected $name = 'config:default:get';
    protected $description = 'Get the currently configured default instance, if any';

    protected function process()
    {
        $config = $this->getApplication()->instances;

        $name = $config->getDefaultInstance();
        if (!$name) {
            return $this->comment('No default instance configured.');
        }

        return $this->line("Default instance is currently : <info>{$name}</info>");
    }
}
