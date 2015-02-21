<?php namespace MODX\Shell\Command\System\Log;

use MODX\Shell\Command\ProcessorCmd;
use MODX\Shell\Formatter\ColoredLog;

/**
 * A command to view modX system log
 */
class View extends ProcessorCmd
{
    protected $processor = 'system/errorlog/get';

    protected $defaultOptions = array();

    protected $name = 'system:log:view';
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

        $formatter = new ColoredLog;
        $result = $formatter->process($result);

        // @TODO: ability to filter levels (ie. only error, warn, info or debug)

        $this->line($result);
    }
}
