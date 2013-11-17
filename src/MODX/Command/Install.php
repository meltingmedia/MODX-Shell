<?php namespace MODX\Command;

/**
 * Install MODX in the current folder
 */
class Install extends BaseCmd
{
    protected $name = 'install';
    protected $description = 'Install MODX here';

    protected function process()
    {
        if ($this->getMODX()) {
            return $this->error('Seems like MODX install already installed here...');
        }

        $this->comment('Not yet implemented...');

        // install from zip (local folder, offer to download builds from modx.com ?)

        // install from git (official repo or custom one) from branch (defaulting to master), tag or commit hash

        // offer to generate the configuration file to install from (interactive ?) or read from existing file (meaning we should perform some check)

        // offer to generate vhost (apache/nginx)

        // list post actions (storing user defined actions in some folder ?)
    }
}
