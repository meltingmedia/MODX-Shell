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

    /**
     * Load the instances configuration/data from the given path/file
     *
     * @param string $path
     */
    public function load($path = '')
    {
        if (empty($path)) {
            $path = $this->path;
        }

        if (file_exists($path)) {
            $this->items = parse_ini_file($path, true);
        }
    }

    /**
     * Get the instance name for the given path
     *
     * @param string $path
     *
     * @return null|string
     */
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

    /**
     * Get the current instance name, if found
     *
     * @return string|null
     */
    public function current()
    {
        return $this->findFormPath(getcwd() . '/');
    }

    /**
     * Get current instance configuration data
     *
     * @param string $key An option key to retrieve from the config
     *
     * @return null|array|string
     */
    public function getCurrentConfig($key = '')
    {
        $config = $this->get($this->current());
        if (empty($key) && isset($config[$key])) {
            return $config[$key];
        }

        return $config;
    }
}
