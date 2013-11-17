<?php namespace MODX\Command\Menu;

use MODX\Command\ListProcessor;

/**
 * List namespaces
 */
class GetList extends ListProcessor
{
    protected $processor = 'system/menu/getlist';
    protected $headers = array(
        'text', 'parent', 'action'
    );

    protected $defaultsProperties = array(
        'sort' => 'text',
        'dir' => 'ASC',
        'limit' => 10,
        'start' => 0,
    );

    protected $name = 'menu:list';
    protected $description = 'List menus';

//    protected function formatAction($value)
//    {
//        return $this->renderObject('modAction', $value)
//    }
}
