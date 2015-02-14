<?php namespace MODX\Shell\Command\System\Setting;

use MODX\Shell\Command\ListProcessor;

class GetList extends ListProcessor
{
    protected $processor = 'system/settings/getlist';
    protected $headers = array(
        'key', 'value', 'namespace', 'area'
    );

    protected $name = 'system:setting:list';
    protected $description = 'List system settings';
}
