<?php namespace MODX\Command\Ns;

use MODX\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Update a modNamespace
 */
class Update extends ProcessorCmd
{
    protected $name = 'namespace:update';
    protected $description = 'Update a namespace';

    protected $processor = 'workspace/namespace/update';
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

        $this->info('Namespace updated : '. $name);
    }
}
