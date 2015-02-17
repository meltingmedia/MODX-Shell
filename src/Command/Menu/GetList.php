<?php namespace MODX\Shell\Command\Menu;

use MODX\Shell\Command\ListProcessor;

/**
 * A command to list modMenu records
 */
abstract class GetList extends ListProcessor
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
