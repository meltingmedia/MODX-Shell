<?php namespace MODX\Command;

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
    }
}
