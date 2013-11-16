<?php namespace MODX\Command\Resource;

use MODX\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;

class Remove extends ProcessorCmd
{
    protected $processor = 'resource/delete';

    protected $name = 'resource:delete';
    protected $description = 'Delete modResource';

    protected $required = array('id');

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $result = $this->confirm('Are you sure you want to delete the resource ? [y|N]', false);
        //$this->info('result : '. $result);

        return $result;
    }

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The resource id'
            ),
        );
    }
}
