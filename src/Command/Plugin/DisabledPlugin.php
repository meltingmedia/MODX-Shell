<?php namespace MODX\Shell\Command\Plugin;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to disable a plugin
 */
class DisabledPlugin extends BaseCmd
{
    const MODX = true;

    protected $name = 'plugin:disable';
    protected $description = 'Disable a plugin';

    protected function process()
    {
        $pk = $this->argument('identifier');
        if (is_numeric($pk)) {
            // Assume an ID was given
            $key = 'id';
        } else {
            $key = 'name';
        }

        /** @var \modPlugin $plugin */
        $plugin = $this->modx->getObject('modPlugin', array($key => $pk));
        if (!$plugin) {
            return $this->error("No plugin found with {$key} : {$pk}");
        }

        if ($plugin->get('disabled')) {
            return $this->info('Plugin already disabled');
        }
        $plugin->set('disabled', true);
        $plugin->save();
        $this->modx->getCacheManager()->refresh();

        return $this->info('Plugin should be disabled');
    }

    protected function getArguments()
    {
        return array(
            array(
                'identifier',
                InputArgument::REQUIRED,
                'The plugin ID or name.'
            ),
        );
    }
}
