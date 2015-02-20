<?php namespace MODX\Shell\Configuration;

/**
 * An object with all configured instances
 */
class Instance extends Base
{
    protected $path = '';

    public function __construct(array $items = array())
    {
        $this->path = getenv('HOME') . '/.modx/config.ini';
        if (empty($items)) {
            $this->load($this->path);
        } else {
            $this->items = $items;
        }
    }

    public function save()
    {
        $content = "; This is MODX Shell configuration file \n\n";
        ksort($this->items);
        foreach ($this->items as $label => $config) {
            // Section
            $content .= '['. $label ."]\n";
            foreach ($config as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $content .= $key ."['{$k}'] = '" . $v ."'\n";
                    }
                } elseif ($value == '') {
                    $content .= $key . " = \n";
                } else {
                    $content .= $key." = '". $value ."'\n";
                }
            }
            $content .= "\n";
        }

        return (file_put_contents($this->path, $content) !== false);
    }

    public function load($path = null)
    {
        if (!$path) {
            $path = $this->path;
        }

        if (file_exists($path)) {
            $this->items = parse_ini_file($path, true);
        }
    }

    public function findFormPath($path)
    {

        foreach ($this->items as $name => $data) {
            if (array_key_exists('base_path', $data)) {
                $instancePath = $data['base_path'];
                if (substr($path, 0, strlen($instancePath)) === $instancePath) {
                    return $name;
                }
            }
        }

        return null;
    }

    public function current()
    {
        return $this->findFormPath(getcwd() . '/');
    }
}
