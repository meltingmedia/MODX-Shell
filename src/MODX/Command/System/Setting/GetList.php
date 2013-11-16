<?php namespace MODX\Command\System\Setting;

use MODX\Command\ListProcessor;

class GetList extends ListProcessor
{
    protected $processor = 'system/settings/getlist';
    protected $headers = array(
        'key', 'value', 'namespace', 'area'
    );

    protected $name = 'system:setting:list';
    protected $description = 'List system settings';
}
