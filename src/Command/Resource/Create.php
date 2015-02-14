<?php namespace MODX\Shell\Command\Resource;

use MODX\Shell\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;

class Create extends ProcessorCmd
{
    protected $processor = 'resource/create';

    protected $defaultOptions = array(
        'context_key' => 'web',
    );

    protected $required = array('pagetitle');

    protected $name = 'resource:create';
    protected $description = 'Create a modResource';

    protected function processResponse(array $response = array())
    {
        $id = $response['object']['id'];

        $this->info('Resource created with id '. $id);
    }

    protected function getArguments()
    {
        return array(
            array(
                'pagetitle',
                InputArgument::REQUIRED,
                'The resource pagetitle'
            ),
        );
    }
}
