<?php namespace MODX\Command\Chunk;

use MODX\Command\ListProcessor;

class GetList extends ListProcessor
{
    protected $processor = 'element/chunk/getlist';
    protected $headers = array(
        'id', 'name', 'description'
    );

    protected $name = 'chunk:list';
    protected $description = 'List chunks';
}
