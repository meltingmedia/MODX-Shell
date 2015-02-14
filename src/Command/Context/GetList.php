<?php namespace MODX\Shell\Command\Context;

use MODX\Shell\Command\ListProcessor;

class GetList extends ListProcessor
{
    protected $processor = 'context/getlist';
    protected $headers = array(
        'key', 'description', 'rank'
    );

    protected $name = 'context:list';
    protected $description = 'List contexts';
}
