<?php namespace MODX\Shell\Command;

use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to crawl resources (to cache them)
 */
class Crawl extends BaseCmd
{
    const MODX = true;

    protected $name = 'crawl';
    protected $description = 'Crawl resources to prime their caches';

    protected $curl;
    protected $start;

    protected function process()
    {
        $from = $this->argument('from');

        $c = $this->getCriteria($from);

        $total = $this->modx->getCount('modResource', $c);
        if ($total > 0) {
            $this->prepareCurl();
        }

        $this->comment("\n<info>{$total}</info> 'root' resources found");

        $collection = $this->modx->getCollection('modResource', $c);
        $context = '';
        /** @var \modResource $resource */
        foreach ($collection as $resource) {
            if ($context !== $resource->context_key) {
                $this->comment("\nProcessing context {$resource->context_key}");
                $this->modx->switchContext($resource->context_key);
                $context = $resource->context_key;
                //$this->modx->context->setOption('session_enabled', false);
            }
            $this->crawl($resource->id);

            if (is_numeric($from)) {
                // Process children too
                $children = $this->modx->getChildIds($resource->id, 999);
                foreach ($children as $id) {
                    $this->crawl($id);
                }
            }
        }

        if (is_numeric($from)) {
            // Crawl the container too
            $this->crawl($from);
        }

        curl_close($this->curl);
        $this->line("\n".sprintf("Executed in <info>%2.4f</info> seconds", (microtime(true) - $this->start)));

        //$this->info($c->toSQL());
    }

    /**
     * Build the query
     *
     * @param mixed $from Either the context key (string) or a resource id (int) to grab the resources to crawl from
     *
     * @return \xPDOQuery
     */
    protected function getCriteria($from)
    {
        $criteria = array(
            'published' => true,
            'deleted' => false,
            'cacheable' => true,
        );
        if (is_numeric($from)) {
            // From a given resource container
            $criteria['parent'] = $from;
        } else {
            if ($from === 'all') {
                // We want all contexts
                $criteria['context_key:!='] = 'mgr';
            } else {
                // A single context
                $criteria['context_key'] = $from;
            }
        }

        $c = $this->modx->newQuery('modResource');
        $c->select(array('id', 'pagetitle', 'context_key'));
        $c->where($criteria);
        $c->sortby('context_key');

        return $c;
    }

    /**
     * Perform the request to the given resource ID
     *
     * @param int $id
     */
    protected function crawl($id)
    {
        $url = $this->modx->makeUrl($id, '', '', 'full');
        $this->line("Requesting <info>{$url}</info> (<comment>{$id}</comment>)");
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_exec($this->curl);

        if (curl_errno($this->curl)) {
            $this->error('cURL error: ' . curl_errno($this->curl) . ' - ' . curl_error($this->curl));
        }

    }

    /**
     * Prepare cURL handler
     */
    protected function prepareCurl()
    {
        $this->start = microtime(true);
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_NOBODY => true,
            CURLOPT_FAILONERROR => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HEADER => true,
            //CURLOPT_USERAGENT => "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"
            //CURLOPT_FRESH_CONNECT => true,
        ));

        $this->curl = $ch;
    }

    protected function getArguments()
    {
        return array(
            array(
                'from',
                InputArgument::REQUIRED,
                'The context key or resource ID to crawl from. Use "all" to process all web contexts.'
            ),
        );
    }
}
