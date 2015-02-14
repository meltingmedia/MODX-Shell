<?php namespace MODX\Shell\Command\Ns;

use MODX\Shell\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Create a new modNamespace
 */
class Create extends ProcessorCmd
{
    protected $name = 'namespace:create';
    protected $description = 'Create a new namespace';

    protected $processor = 'workspace/namespace/create';
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

        $this->info('Namespace created : '. $name);
    }
}
