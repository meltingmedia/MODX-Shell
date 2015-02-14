<?php namespace MODX\Shell\Command\Config;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

class Edit extends Add
{
    protected $name = 'config:edit';
    protected $description = 'Edit a modx installation configuration';

    protected function getArguments()
    {
        return array(
            array(
                'service_class',
                InputArgument::REQUIRED,
                'Your component service class name'
            ),
            array(
                'path',
                InputArgument::OPTIONAL,
                'Your component base path, defaults to current dir',
                getcwd(),
            ),
            array(
                'erase',
                InputArgument::OPTIONAL,
                'Whether or not override existing component',
                true
            ),
        );
    }
}
