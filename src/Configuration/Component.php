<?php namespace MODX\Shell\Configuration;

use MODX\Shell\Application;

/**
 * A configuration object storing all components commands
 */
class Component extends Base
{
    protected $app;
    protected $modx;

    public function __construct(Application $app, array $items = array())
    {
        $this->app = $app;
        $this->modx = $app->getMODX();
        if (empty($items)) {
            $this->load();
        } else {
            $this->items = $items;
        }
    }

    public function save()
    {
        if ($this->modx instanceof \modX) {
            /** @var \modSystemSetting $setting */
            $setting = $this->modx->getObject('modSystemSetting', array(
                'key' => 'console_commands'
            ));
            if (!$setting) {
                $setting = $this->modx->newObject('modSystemSetting');
                $setting->set('key', 'console_commands');
            }
            ksort($this->items);
            $setting->set('value', $this->modx->toJSON($this->items));
            $saved = $setting->save();
            if ($saved) {
                $this->modx->getCacheManager()->refresh();
                return true;
            }
        }

        return false;
    }

    public function load()
    {
        if ($this->modx instanceof \modX) {
            $this->items = $this->modx->fromJSON($this->modx->getOption('console_commands', null, '{}'));
        }
    }
}
