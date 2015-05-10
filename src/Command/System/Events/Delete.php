<?php namespace MODX\Shell\Command\System\Events;

use modEvent;
use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to create a system event
 */
class Delete extends BaseCmd
{
    const MODX = true;

    protected $name = 'system:events:delete';
    protected $description = 'Delete an existing new system event';

    protected function process()
    {
        $name = $this->argument('name');
        $vent = $this->getVent($name);

        if (!$vent) {
            return $this->error("No event with name {$name} found");
        }

        // Check for event type and/or prevent default events removal

        if (!$vent->remove()) {
            return $this->error("Error while to remove the system event {$name}");
        }

        $this->info("{$name} event deleted");
    }

    /**
     * @param string $name
     *
     * @return modEvent|null
     */
    protected function getVent($name)
    {
        $c = $this->modx->newQuery('modEvent');
        $c->where(array(
            'name' => $name,
        ));

        return $this->modx->getObject('modEvent', $c);
    }

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'The event name to delete.'
            ),
        );
    }
}
