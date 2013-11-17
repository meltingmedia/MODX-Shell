<?php namespace MODX\Command\Package;

use MODX\Command\ListProcessor;

class GetList extends ListProcessor
{
    protected $processor = 'workspace/packages/getlist';
    protected $headers = array(
        'package_name', 'signature'/*, 'version'*/, 'installed', 'updateable'
    );

    protected $name = 'package:list';
    protected $description = 'List package';

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $this->comment('Listing packages, please wait...');
    }

    protected function formatUpdateable($value)
    {
        return $this->renderBoolean($value);
    }
}
