<?php namespace MODX\Command\System;

use MODX\Command\BaseCmd;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Demo extends BaseCmd
{
    protected $name = 'demo';
    protected $description = 'Sample CMD';
    protected $help = 'php modx.phar modx:demo';

    protected function process()
    {
        $this->comment('Bah');
    }

    protected function getOptions()
    {
        return array(
//            array(
//                'option_name',
//                'shortcut',
//                InputOption::VALUE_REQUIRED,
//                'Description',
//                'default_value'
//            ),
        );
    }

    protected function getArguments()
    {
        return array(
//            array(
//                'argument_name',
//                InputArgument::REQUIRED,
//                'The new class name to use'
//            ),
        );
    }
}
