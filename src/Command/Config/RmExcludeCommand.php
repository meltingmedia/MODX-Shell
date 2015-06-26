<?php namespace MODX\Shell\Command\Config;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

class RmExcludeCommand extends BaseCmd
{
    protected $name = 'config:exclude:rm';
    protected $description = 'Remove the given command class from the exclusions';

    protected function getArguments()
    {
        return array(
            array(
                'class',
                InputArgument::REQUIRED,
                'Your full command class name to remove from excludes'
            ),
        );
    }

    protected function process()
    {
        $class = $this->argument('class');

        $excludes = $this->getApplication()->excludedCommands;
        $exists = $excludes->get($class);
        if (!$exists) {
            return $this->comment("{$class} command is not excluded");
        }

        $excludes->remove($class);
        $saved = $excludes->save();
        if (!$saved) {
            return $this->error('Something went wrong while updating the excluded commands config');
        }

        return $this->info('Config file updated');
    }
}
