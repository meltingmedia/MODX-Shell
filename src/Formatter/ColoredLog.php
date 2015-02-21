<?php namespace MODX\Shell\Formatter;

/**
 * A convenient class to help format modX system log output
 */
class ColoredLog
{
    /**
     * @param string $data
     *
     * @return mixed
     */
    public function process($data)
    {
        return preg_replace_callback("/\(([^)].*) @ (.+?)\)/", function($matches) {
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
        }, $data);
    }
}
