<?php namespace MODX\Shell\Command\TV;

use MODX\Shell\Command\ListProcessor;

class GetList extends ListProcessor
{
    protected $processor = 'element/tv/getlist';
    protected $headers = array(
        'id', 'name', 'type', 'description'
    );

    protected $name = 'tv:list';
    protected $description = 'List TVs';
}
