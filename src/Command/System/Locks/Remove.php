<?php namespace MODX\Shell\Command\System\Locks;

use MODX\Shell\Command\ProcessorCmd;

/**
 * A command to remove objects locks
 */
class Remove extends ProcessorCmd
{
    protected $processor = 'system/remove_locks';

    protected $defaultOptions = array();

    protected $name = 'system:locks:remove';
    protected $description = 'Remove locks on all objects';

    protected function processResponse(array $response = array())
    {
        $this->info('Locks removed');
    }
}
