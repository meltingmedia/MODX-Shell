<?php namespace MODX\Shell\Command\Context;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Helper\Table;

/**
 * A command to list all context URLs
 */
class GetURLs extends BaseCmd
{
    const MODX = true;

    protected $name = 'context:urls';
    protected $description = 'List contexts URLs';

    protected function process()
    {
        $this->getMODX();
        $collection = $this->modx->getCollection('modContext');

        $urls = array();
        /** @var \modContext $context */
        foreach ($collection as $context) {
            $context->prepare();
            $url = $context->getOption('site_url');
            if ($context->key === 'mgr') {
                $url .= ltrim($this->modx->getOption('manager_url'), '/');
            }
            $urls[] = array(
                'key' => $context->key,
                'name' => $context->get('name') ? $context->name : $context->key,
                'url' => $url,
            );
        }

        $table = new Table($this->output);
        $table->setHeaders(array(
            'key', 'name', 'url'
        ));

        foreach ($urls as $row) {
            $table->addRow($row);
        }

        $table->render();
    }
}
