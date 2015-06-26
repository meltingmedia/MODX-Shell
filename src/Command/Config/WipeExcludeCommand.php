<?php namespace MODX\Shell\Command\Config;

use MODX\Shell\Command\BaseCmd;

class WipeExcludeCommand extends BaseCmd
{
    protected $name = 'config:exclude:wipe';
    protected $description = 'Restore all excluded command classes';

    protected function process()
    {
        $excludes = $this->getApplication()->excludedCommands;

        foreach ($excludes->getAll() as $class) {
            $excludes->remove($class);
        }
        $saved = $excludes->save();
        if (!$saved) {
            return $this->error('Something went wrong while updating the excluded commands config');
        }

        return $this->info('Restored all excluded commands');
    }
}
