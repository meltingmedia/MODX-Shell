<?php namespace MODX\Shell\Command\Package;

use MODX\Shell\Command\BaseCmd;

/**
 * A command to list packages with available upgrades
 */
class Upgradeable extends BaseCmd
{
    const MODX = true;

    protected $name = 'package:upgradeable';
    protected $description = 'List packages with available upgrades';

    protected function process()
    {
        $c = $this->modx->newQuery('transport.modTransportPackage');
        $c->where(array(
            'installed' => true,
            'provider:>' => 0,
        ));
    }
}
