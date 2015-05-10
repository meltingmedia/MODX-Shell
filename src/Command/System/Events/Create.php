<?php namespace MODX\Shell\Command\System\Events;

use modEvent;
use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to create a system event
 */
class Create extends BaseCmd
{
    const MODX = true;

    protected $name = 'system:events:create';
    protected $description = 'Create a new system event';

    protected function process()
    {
        // @TODO validate name
        $name = $this->argument('name');
        $type = $this->argument('type');
        $type = $this->getEventType($type);
        if (!$type) {
            return $this->error("No valid event type given");
        }

        // Find for existing/duplicate event
        if ($this->exists($name)) {
            return $this->error("An event with name {$name} already exists");
        }

        // Prepare the data to create the MODX object
        $data = array(
            'name' => $name,
            'service' => $type,
            'groupname' => '',
        );
        /** @var modEvent $event */
        $event = $this->modx->newObject('modEvent');
        $event->fromArray($data, '', true);

        if (!$event->save()) {
            return $this->error("Error while to create the system event {$name}");
        }

        $this->info("{$name} event created");
    }

    protected function exists($name)
    {
        $c = $this->modx->newQuery('modEvent');
        $c->where(array(
            'name' => $name,
        ));

        return $this->modx->getCount('modEvent', $c) > 0;
    }

    /**
     * @param int|string $input
     *
     * @return int|null
     */
    protected function getEventType($input)
    {
        $pool = array(
            // Parser Service Events
            'parser' => 1,
            // Manager Access Events
            'manager' => 2,
            // Web Access Service Events
            'web' => 3,
            // Cache Service Events
            'cache' => 4,
            // Template Service Events
            'template' => 5,
            // User Defined Events
            'custom' => 6,
        );

        if (is_numeric($input) && array_search($input, $pool) !== false) {
            return (int) $input;
        }
        if (is_string($input) && array_key_exists($input, $pool)) {
            return (int) $pool[$input];
        }

        return null;
    }

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'The event name.'
            ),
            array(
                'type',
                InputArgument::OPTIONAL,
                'The event type, defaulting to "custom".',
                6
            ),
        );
    }
}
