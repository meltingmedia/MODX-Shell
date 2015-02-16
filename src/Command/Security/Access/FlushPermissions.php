<?php namespace MODX\Shell\Command\Security\Access;

use MODX\Shell\Command\ProcessorCmd;

/**
 * A command to flush users permissions
 */
class FlushPermissions extends ProcessorCmd
{
    protected $name = 'security:access:flush';
    protected $description = 'Flush users permissions';

    protected $processor = 'security/access/flush';

    protected function processResponse(array $response = array())
    {
        $this->info('Permissions flushed');
    }
}
