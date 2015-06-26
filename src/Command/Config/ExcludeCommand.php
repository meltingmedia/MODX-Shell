<?php namespace MODX\Shell\Command\Config;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

class ExcludeCommand extends BaseCmd
{
    protected $name = 'config:exclude';
    protected $description = 'Exclude the given command class from available commands';

    protected function getArguments()
    {
        return array(
            array(
                'class',
                InputArgument::REQUIRED,
                'Your full command class name to exclude'
            ),
        );
    }

    protected function process()
    {
        $class = $this->argument('class');
        if (strpos($class, '/') === false) {
            // No namespaced class given, try to search for a command
            // Disabled commands are not "findable" from their command "alias", so better not rely on it
            try {
                $object = $this->getApplication()->find($class);
            } catch (\InvalidArgumentException $e) {
                $object = null;
            }

            if ($object) {
                //$this->comment(get_class($object));
                $class = get_class($object);
            }
        }

        $excludes = $this->getApplication()->excludedCommands;
        $exists = $excludes->get($class);
        if ($exists) {
            return $this->comment("{$class} command class is already excluded");
        }

        $excludes->set($class);
        $saved = $excludes->save();
        if (!$saved) {
            return $this->error('Something went wrong while updating the excluded commands config');
        }

        return $this->info('Config file updated');
    }
}
