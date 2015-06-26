<?php namespace MODX\Shell\Command\Config;

use MODX\Shell\Command\BaseCmd;

class RmDefault extends BaseCmd
{
    protected $name = 'config:default:rm';
    protected $description = 'Remove the configured default instance, if any';

    protected function process()
    {
        $config = $this->getApplication()->instances;

        $config->removeDefaultInstance();
        $saved = $config->save();
        if (!$saved) {
            return $this->error('Something went wrong while updating the config');
        }

        return $this->info('Default instance removed');
    }
}
