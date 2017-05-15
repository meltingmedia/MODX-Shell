<?php namespace MODX\Shell\Configuration;

/**
 * An object with all configured instances
 */
class Instance extends Base
{
    /**
     * The ini file instance data is stored in
     *
     * @var string
     */
    protected $path = '';

    public function __construct(array $items = array())
    {
        $this->path = $this->getConfigPath() . 'config.ini';
        if (empty($items)) {
            $this->load($this->path);
        } else {
            $this->items = $items;
        }
    }

    public function save()
    {
        $content = $this->formatConfigurationData();
        $this->makeSureConfigPathExists();

        return (file_put_contents($this->path, $content) !== false);
    }

    /**
     * Format the items array as a correct ini string
     *
     * @return string
     */
    protected function formatConfigurationData()
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

        return $content;
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

        //echo print_r($this->items, true);
    }

    /**
     * Get the confgiured default instance name, if any
     *
     * @return null|string
     */
    public function getDefaultInstance()
    {
        return $this->getConfig('__default__', 'class');
    }

    /**
     * Set the given instance name as the default instance
     *
     * @param string $name
     */
    public function setDefaultInstance($name)
    {
        $this->set('__default__', array(
            'class' => $name,
        ));
    }

    /**
     * Remove any configured default instance
     */
    public function removeDefaultInstance()
    {
        $this->remove('__default__');
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
        $path = realpath($path);
        foreach ($this->items as $name => $data) {
            if (array_key_exists('base_path', $data)) {
                $instancePath = realpath($data['base_path']);
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
        if (!empty($key) && isset($config[$key])) {
            return $config[$key];
        }

        return $config;
    }

    /**
     * Get the given instance configuration data, if any
     *
     * @param string $instance The instance name
     * @param string $key An optional key to retrieve from the configuration data
     *
     * @return null|string|array
     */
    public function getConfig($instance, $key = '')
    {
        if (array_key_exists($instance, $this->items)) {
            $data = $this->items[$instance];
            if (!empty($key) && isset($data[$key])) {
                $data = $data[$key];
            }

            return $data;
        }

        return null;
    }
}
