<?php namespace MODX\Shell\Command\System;

use MODX\Shell\Command\ProcessorCmd;

/**
 * A command to clear/refresh system cache
 */
class ClearCache extends ProcessorCmd
{
    protected $processor = 'system/clearcache';

    protected $defaultOptions = array();

    protected $name = 'system:clearcache';
    protected $description = 'Clears MODX cache';

    protected function processResponse(array $response = array())
    {
        $this->info('Cache cleared');
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $this->modx->setLogTarget('ECHO');
    }
}
