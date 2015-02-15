<?php namespace MODX\Shell\Command\Package\Provider;

use MODX\Shell\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Get provider details (newest & most downloaded packages)
 */
abstract class Info extends ProcessorCmd
{
    protected $processor = 'workspace/packages/rest/getinfo';
    protected $headers = array(
        'id', 'text'
    );

    protected $required = array(
        'provider'
    );

    protected $name = 'package:provider:info';
    protected $description = 'Get provider information';

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

    protected function processResponse(array $results = array())
    {
        $this->info(print_r($results, true));
//        $total = $results['total'];
//        $results = $results['results'];
//
//        $this->renderBody($results);
//        if ($this->showPagination) {
//            $this->renderPagination($results, $total);
//        }
    }


//    protected function decodeResponse(\modProcessorResponse &$response)
//    {
//        $results = $response->getResponse();
//        if (!is_array($results)) {
//            $results = json_decode($results, true);
//        }
//
//        $data = array(
//            'results' => $results,
//            'total' => count($results),
//        );
//
//        //$this->info(print_r($results, true));
//
//        return $data;
//    }
}
