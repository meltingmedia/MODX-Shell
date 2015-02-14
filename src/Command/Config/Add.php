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

        /** @var \MODX\Shell\Application $application */
        $application = $this->getApplication();
        // @todo: beware of refactoring
        $original = $application->getCurrentConfig();

        $data = array(
            $service => array(
                'class' => $service,
                'base_path' => $path,
            ),
        );
        $data = array_merge($original, $data);
        ksort($data);

        //$this->writeConfig($data, $input, $output);
        if ($erase || !array_key_exists($service, $original)) {
            $this->writeConfig($data);
        } else {
            $this->error('Entry with that name already in config : '. $service);
        }
    }

    protected function writeConfig(array $data)
    {
        /** @var \MODX\Shell\Application $application */
        $application = $this->getApplication();
        if ($application->writeConfig($data)) {
            return $this->info('Config file updated');
        }

        $this->error('Something went wrong while updating the config');
    }
}
