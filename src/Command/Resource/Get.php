<?php namespace MODX\Shell\Command\Resource;

use MODX\Shell\Command\GetProcessor;
use Symfony\Component\Console\Input\InputArgument;

class Get extends GetProcessor
{
    protected $processor = 'resource/get';

    protected $name = 'resource:get';
    protected $description = 'Get a modResource';

    protected $required = array('id');

    protected $headers = array(
        'id', 'pagetitle', 'published', 'alias', 'context_key'
    );

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
