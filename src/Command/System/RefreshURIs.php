<?php namespace MODX\Shell\Command\System;

use MODX\Shell\Command\ProcessorCmd;

/**
 * A command to regenerate resources URIs
 */
class RefreshURIs extends ProcessorCmd
{
    protected $processor = 'system/refreshuris';

    protected $defaultOptions = array();

    protected $name = 'system:refreshuris';
    protected $description = 'Regenerate the resources URIs';

    protected function processResponse(array $response = array())
    {
        $response['message'] === 'refresh_success'
            ? $this->info('URIs refreshed')
            : $this->error('Error while trying to refresh the resources URIs');
    }
}
