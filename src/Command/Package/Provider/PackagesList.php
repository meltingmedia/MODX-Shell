<?php namespace MODX\Shell\Command\Package\Provider;

use MODX\Shell\Command\ListProcessor;
use Symfony\Component\Console\Input\InputArgument;

/**
 * List packages from the given provider
 */
class PackagesList extends ListProcessor
{
    protected $processor = 'workspace/packages/rest/getlist';
    protected $headers = array(
        'id', 'name', 'version', 'release', /*'description',*/ 'downloads', 'author'
    );

    protected $required = array(
        'provider'
    );

    protected $name = 'package:provider:packages';
    protected $description = 'List provider packages';

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
}
