<?php namespace MODX\Command\System;

use MODX\Command\ProcessorCmd;

class LogView extends ProcessorCmd
{
    protected $processor = 'system/errorlog/get';

    protected $defaultOptions = array();

    protected $name = 'system:viewlog';
    protected $description = 'Read MODX error log';

    protected function processResponse(array $response = array())
    {
        $result = $response['object']['log'];
        $tooBig = $response['object']['tooLarge'];
        if (!empty($tooBig)) {
            return $this->comment('Log is too large to be displayed');
        }

        if (empty($result) || $result == ' ') {
            return $this->comment('Log is empty');
        }

        $this->info($result);
    }
}
