<?php namespace MODX\Shell\Command\Resource;

use MODX\Shell\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to purge deleted resources
 */
class Purge extends ProcessorCmd
{
    protected $processor = 'resource/emptyrecyclebin';

    protected $name = 'resource:purge';
    protected $description = 'Purge deleted modResource';

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $result = $this->confirm('Are you sure you want to purge all deleted resources ? [y|N]', false);
        //$this->info('result : '. $result);

        return $result;
    }
}
