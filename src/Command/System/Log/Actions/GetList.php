<?php namespace MODX\Shell\Command\System\Log\Actions;

use MODX\Shell\Command\ListProcessor;

/**
 * A command to list manager actions
 */
class GetList extends ListProcessor
{
    protected $processor = 'system/log/getlist';
    protected $headers = array(
        'occurred', 'username', 'action', 'item'
    );

    protected $name = 'system:actions:list';
    protected $description = 'List manager actions';
}
