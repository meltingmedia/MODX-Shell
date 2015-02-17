<?php namespace MODX\Shell\Command\System\Log;

use MODX\Shell\Command\ProcessorCmd;

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

        $result = preg_replace_callback("/\(([^)].*) @ (.+?)\)/", function($matches) {
            $style = strtolower($matches[1]);
            if ($style === 'debug') {
                return "({$matches[1]} @ {$matches[2]})";
            }
            if ($style === 'warn') {
                $style = 'comment';
            } elseif ($style === 'fatal') {
                $style = 'error';
            }
            return "(<{$style}>{$matches[1]} @ {$matches[2]}</{$style}>)";
        }, $result);

        // @TODO: ability to filter levels (ie. only error, warn, info or debug)

        $this->line($result);
    }
}
