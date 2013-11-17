<?php namespace MODX\Command;

class Version extends BaseCmd
{
    const MODX = true;

    protected $name = 'version';
    protected $description = 'Get current MODX version';

    protected function process()
    {
        $data = $this->modx->getVersionData();

        $this->info($data['full_appname']);
    }
}
