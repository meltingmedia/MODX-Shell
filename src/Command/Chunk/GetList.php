<?php namespace MODX\Shell\Command\Chunk;

use MODX\Shell\Command\ListProcessor;

/**
 * A command ot list chunks
 */
abstract class GetList extends ListProcessor
{
    protected $processor = 'element/chunk/getlist';
    protected $headers = array(
        'id', 'name', 'description'
    );

    protected $name = 'chunk:list';
    protected $description = 'List chunks';
}
