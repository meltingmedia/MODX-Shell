<?php namespace MODX\Shell\Command\System\Log\Actions;

use MODX\Shell\Command\ProcessorCmd;

/**
 * A command to wipe manager actions log
 */
class Truncate extends ProcessorCmd
{
    protected $processor = 'system/log/truncate';

    protected $name = 'system:actions:clear';
    protected $description = 'Clear manager actions log';

    protected function processResponse(array $response = array())
    {
        $this->info('Log cleared');
    }
}
