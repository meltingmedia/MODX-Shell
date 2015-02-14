<?php namespace MODX\Shell\Command\Resource;

use MODX\Shell\Command\ListProcessor;

class GetList extends ListProcessor
{
    protected $processor = 'resource/getlist';
    protected $headers = array(
        'id', 'pagetitle', 'published', 'context_key'
    );

    protected $name = 'resource:list';
    protected $description = 'List existing resources';

    protected function formatPublished($value)
    {
        return $this->renderBoolean($value);
    }

    protected function formatCreatedby($value)
    {
        return $this->renderObject('modUser', $value, 'username');
    }

    protected function formatEditedby($value)
    {
        return $this->renderObject('modUser', $value, 'username');
    }
}
