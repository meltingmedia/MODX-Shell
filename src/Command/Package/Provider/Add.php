<?php namespace MODX\Shell\Command\Package\Provider;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to easy the process of adding a package provider
 */
class Add extends BaseCmd
{
    const MODX = true;

    protected $name = 'package:provider:add';
    protected $description = 'Add a package provider from the most common ones.';
    protected $pool = array(
        'modmore' => array(
            'url' => 'https://rest.modmore.com/'
        ),
        'simpledream' => array(
            'url' => array(
                'http://modstore.pro/extras/',
                'http://store.simpledream.ru/extras/'
            ),
        ),
        'extras.io' => array(
            'url' => 'https://rest.extras.io/v1/'
        ),
        'simplecart' => array(
            'url' => 'https://rest.modxsimplecart.com/'
        ),
        'modx' => array(
            'url' => array(
                'http://rest.modx.com/extras/',
                'http://rest.modxcms.com/extras/',
            ),
        ),
        'meltingmedia' => array(
            'url' => 'https://extras.melting-media.com/'
        ),
//        'fake' => array(
//            'url' => 'https://fake.melting-media.com/'
//        ),
    );

    protected function process()
    {
        $provider = $this->argument('provider');
        if (!array_key_exists($provider, $this->pool)) {
            return $this->line('<error>Unknown provider, valid ones</error> : '. implode(', ', array_keys($this->pool)));
        }

        if ($this->isAlreadyPresent($provider)) {
            return $this->info('Provider already present');
        }

        //$this->info('Seems good to add');
        $this->addProvider($provider);
    }

    protected function addProvider($provider)
    {
        /** @var \modTransportProvider $data */
        $data = $this->modx->newObject('transport.modTransportProvider');
        $url = $this->pool[$provider]['url'];
        if (is_array($url)) {
            $url = $url[0];
        }
        $data->fromArray(array(
            'service_url' => $url,
            'name' => $provider,
            'description' => $provider . ' package provider.',
            'username' => $this->option('username'),
            'api_key' => $this->option('api_key'),
        ));

        $valid = $data->verify();
        if ($valid !== true) {
            return $this->error('Provider did not validate with message : '. $valid);
        }

        $data->save();

        $this->line("Provider added with id : <info>{$data->get('id')}</info>");
    }

    protected function isAlreadyPresent($provider)
    {
        $criteria = array(
            'service_url' => $this->pool[$provider]['url'],
        );
        if (is_array($this->pool[$provider]['url'])) {
            $criteria = array(
                'service_url:IN' => $this->pool[$provider]['url'],
            );
        }
        return $this->modx->getObject('transport.modTransportProvider', $criteria);
    }

    protected function getArguments()
    {
        return array(
            array(
                'provider',
                InputArgument::REQUIRED,
                'Provider name.'
            ),
        );
    }

    protected function getOptions()
    {
        return array(
            array(
                'username',
                'u',
                InputOption::VALUE_REQUIRED,
                'The provider username',
                ''
            ),
            array(
                'api_key',
                'a',
                InputOption::VALUE_REQUIRED,
                'The provider API key',
                ''
            ),
        );
    }
}
