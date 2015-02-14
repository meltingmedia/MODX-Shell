<?php namespace MODX\Shell\Command\System\Log;

use MODX\Shell\Command\ProcessorCmd;

class Clear extends ProcessorCmd
{
    protected $processor = 'system/errorlog/clear';

    protected $defaultOptions = array();

    protected $name = 'system:log:clear';
    protected $description = 'Clears MODX error log';

    protected function processResponse(array $response = array())
    {
        $this->info('Log cleared');
    }
}
