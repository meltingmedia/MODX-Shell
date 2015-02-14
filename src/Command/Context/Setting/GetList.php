<?php namespace MODX\Shell\Command\Context\Setting;

use MODX\Shell\Command\ListProcessor;
use Symfony\Component\Console\Input\InputArgument;

class GetList extends ListProcessor
{
    protected $processor = 'context/setting/getlist';
    protected $headers = array(
        'key', 'value', 'namespace', 'area'
    );
    protected $required = array('context_key');

    protected $name = 'context:setting:list';
    protected $description = 'List context settings';

    protected function getArguments()
    {
        return array(
            array(
                'context_key',
                InputArgument::REQUIRED,
                'The context key to read settings from'
            ),
        );
    }
}
