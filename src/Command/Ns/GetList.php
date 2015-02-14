<?php namespace MODX\Shell\Command\Ns;

use MODX\Shell\Command\ListProcessor;

/**
 * List namespaces
 */
class GetList extends ListProcessor
{
    protected $processor = 'workspace/namespace/getlist';
    protected $headers = array(
        'name'/*, 'path', 'assets_path'*/
    );

    protected $name = 'namespace:list';
    protected $description = 'List namespaces';
}
