<?php namespace MODX\Command\System;

use MODX\Command\ProcessorCmd;

class LogClear extends ProcessorCmd
{
    protected $processor = 'system/errorlog/clear';

    protected $defaultOptions = array();

    protected $name = 'system:clearlog';
    protected $description = 'Clears MODX error log';

    protected function processResponse(array $response = array())
    {
        $this->info('Log cleared');
    }
}
