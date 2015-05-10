<?php namespace MODX\Shell\Command\Package;

use MODX\Shell\Command\BaseCmd;
use Melting\MODX\Package\Installer;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to install packages
 */
class Install extends BaseCmd
{
    const MODX = true;

    protected $name = 'require';
    protected $description = 'Install package(s)';

    public function process()
    {
        $modx = $this->getMODX();

        $target = 'ECHO';
        $modx->setLogTarget($target);

        $dependencies = $this->getNormalizedPackages($this->argument('packages'));

        if (!empty($dependencies)) {
            $installer = new Installer($modx);

            $result = $installer->installPackages($dependencies);

            return $result;
        }
    }

    protected function getNormalizedPackages(array $packages)
    {
        $output = array();
        foreach ($packages as $package) {
            $split = explode(':', $package);
            $output[$split[0]] = isset($split[1]) ? $split[1] : '*';
        }

        return $output;
    }

    protected function getArguments()
    {
        return array(
            array(
                'packages',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'The package(s) you want to install, ie. getResources:1.6.0-pl'
            ),
        );
    }
}
