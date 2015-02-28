<?php namespace MODX\Shell\Command\Config;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

class Add extends BaseCmd
{
    protected $name = 'config:add';
    protected $description = 'Add a new modx installation to configuration';

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'Your instance name'
            ),
            array(
                'path',
                InputArgument::OPTIONAL,
                'Your instance base path, defaults to current dir',
                getcwd(),
            ),
            array(
                'erase',
                InputArgument::OPTIONAL,
                'Whether or not override existing configuration',
                false
            ),
        );
    }

    protected function process()
    {
        $service = $this->argument('name');
        $path = $this->argument('path');
        $erase = $this->argument('erase');

        if (substr($path, -1) !== '/') {
            $path .= '/';
        }

        $config = $this->getApplication()->instances;
        $exists = $config->get($service);
        if ($exists && !$erase) {
            return $this->error('Entry with that name already in config : '. $service);
        }

        $data = array(
            $service => array(
                'class' => $service,
                'base_path' => $path,
            ),
        );
        if (chdir($path) && $this->getMODX()) {
            $data[$service]['core_path'] = $this->modx->getOption('core_path');
        }

        $config->set($service, $data[$service]);
        $saved = $config->save();
        if (!$saved) {
            return $this->error('Something went wrong while updating the config');
        }

        return $this->info('Config file updated');
    }
}
