<?php namespace MODX\Shell\Command\Package\Provider;

use MODX\Shell\Command\ListProcessor;
use Symfony\Component\Console\Input\InputArgument;

/**
 * List categories & tags from the given provider
 */
class CategoriesList extends ListProcessor
{
    protected $processor = 'workspace/packages/rest/getnodes';
    protected $headers = array(
        'id', 'text'
    );

    protected $required = array(
        'provider'
    );

    protected $name = 'package:provider:categories';
    protected $description = 'List provider categories';

    protected function getArguments()
    {
        return array(
            array(
                'provider',
                InputArgument::OPTIONAL,
                'The provider ID',
                1
            ),
        );
    }


    protected function decodeResponse(\modProcessorResponse &$response)
    {
        $results = $response->getResponse();
        if (!is_array($results)) {
            $results = json_decode($results, true);
        }

        $data = array(
            'results' => $results,
            'total' => count($results),
        );

        //$this->info(print_r($results, true));

        return $data;
    }
}
