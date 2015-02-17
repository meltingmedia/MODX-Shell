<?php namespace MODX\Shell\Command\Source;

use MODX\Shell\Command\ListProcessor;

/**
 * A command to list media sources
 */
abstract class GetList extends ListProcessor
{
    protected $processor = 'source/getlist';
    protected $headers = array(
        'id', 'name', 'class_key', 'description', 'is_stream'
    );

    protected $name = 'source:list';
    protected $description = 'List Media Sources';

    protected function formatIs_stream($value)
    {
        return $this->renderBoolean($value);
    }
}
