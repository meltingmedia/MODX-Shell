<?php namespace MODX\Command\TV;

use MODX\Command\ListProcessor;

class GetList extends ListProcessor
{
    protected $processor = 'element/tv/getlist';
    protected $headers = array(
        'id', 'name', 'type', 'description'
    );

    protected $name = 'tv:list';
    protected $description = 'List TVs';
}
