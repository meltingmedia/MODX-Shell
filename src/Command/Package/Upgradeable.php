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
    /**
     * @var \modTransportProvider[]
     */
    protected $providerCache = array();

    protected function process()
    {
//        $c = $this->modx->newQuery('transport.modTransportPackage');
//        $c->where(array(
//            'installed:!=' => null,
//            'provider:>' => 0,
//        ));

        $upgrades = array();
        //$collection = $this->modx->getCollection('transport.modTransportPackage', $c);
        $list = $this->modx->call('transport.modTransportPackage', 'listPackages', array(&$this->modx, 1));
        $collection = $list['collection'];
        /** @var \modTransportPackage $package */
        foreach ($collection as $package) {
            if ($package->provider === 0) {
                continue;
            }
            if ($this->isUpgradeable($package)) {
                $upgrades[] = $package->package_name;
            }
        }

        if (!empty($upgrades)) {
            $this->info("The following packages are upgradeable : \n");
            foreach ($upgrades as $packageName) {
                $this->comment("* {$packageName}");
            }

            return $this->line('');
        }

        return $this->info('Packages are up to date');
    }

    /**
     * Check whether or not the given package is upgradeable
     *
     * @see modPackageGetListProcessor::checkForUpdates
     *
     * @param \modTransportPackage $package
     *
     * @return bool
     */
    protected function isUpgradeable(\modTransportPackage $package)
    {
        $updates = false;
        // Found upgrades (most likely 1, until we switch to package/versions "resource")
        $found = array();

        /** @var \modTransportProvider $provider */
        if (!empty($this->providerCache[$package->get('provider')])) {
            $provider = $this->providerCache[$package->get('provider')];
        } else {
            $provider = $package->getOne('Provider');
            if ($provider) {
                $this->providerCache[$provider->get('id')] = $provider;
            }
        }

        if ($provider) {
            // Only supported by some providers
            //$updates = $provider->latest($package->get('signature'));

            /** @var \modRestResponse $response */
            $response = $provider->request('package/update', 'GET', array(
                'signature' => $package->get('signature'),
            ));
            if ($response && !$response->isError()) {
                $found = $response->toXml();
            }
            $updates = count($found) > 0;
        }

        return $updates;
    }
}
