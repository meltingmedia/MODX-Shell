<?php namespace MODX\Command\System;

use MODX\Command\ProcessorCmd;

class Fake extends ProcessorCmd
{
    protected $name = 'Fake';
    protected $description = 'Just a dummy command';

    protected function processResponse($response)
    {
        $this->info($response);
    }
}
