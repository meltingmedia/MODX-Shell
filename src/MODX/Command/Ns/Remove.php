<?php namespace MODX\Command\Ns;

use MODX\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Remove a modNamespace
 */
class Remove extends ProcessorCmd
{
    protected $name = 'namespace:remove';
    protected $description = 'Remove a namespace';

    protected $processor = 'workspace/namespace/remove';
    protected $required = array('name');

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'The namespace name'
            ),
        );
    }

    protected function processResponse(array $response = array())
    {
        $name = $response['object']['name'];

        $this->info('Namespace deleted : '. $name);
    }
}
