<?php namespace MODX\Shell\Command\System\Locks;

use MODX\Shell\Command\BaseCmd;

/**
 * A command to list objects locks
 */
abstract class Read extends BaseCmd
{
    const MODX = true;

    protected $name = 'system:locks:list';
    protected $description = 'List objects locks';

    protected function process()
    {
        $this->call('registry:message:read', array(
            'register' => 'locks',
            'topic' => '',
            'remove_read' => false,
        ));
    }
}
