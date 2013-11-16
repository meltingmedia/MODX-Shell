<?php namespace MODX\Command\Context;

use MODX\Command\ListProcessor;

class GetList extends ListProcessor
{
    protected $processor = 'context/getlist';
    protected $headers = array(
        'key', 'description', 'rank'
    );

    protected $name = 'context:list';
    protected $description = 'List contexts';
}
