<?php namespace MODX\Shell\Command\Menu;

use MODX\Shell\Command\ListProcessor;
use MODX\Shell\Formatter\Tree;
use MODX\Shell\TreeBuilder;

/**
 * A command to list modMenu records
 */
class GetList extends ListProcessor
{
    protected $processor = 'system/menu/getlist';
    protected $headers = array(
        'text', 'parent', 'action'
    );

    protected $defaultsProperties = array(
        'sort' => 'parent',
        'dir' => 'ASC',
        'limit' => 9999,
        'start' => 0,
    );

    protected $name = 'menu:list';
    protected $description = 'List menus';

    protected function processResponse(array $results = array())
    {
        $builder = new TreeBuilder($results['results'], 'text', 'parent', 'children');
        $tree = $builder->getSortedTree('menuindex', 'desc');

        $format = new Tree($this->output);
        $format->setValueField(function($item) {
            return "{$item['text_lex']} - {$item['menuindex']} (<comment>{$item['text']}</comment>)";
        });
        $format->setChildrenField('children');
        $format->render($tree);
    }
}
