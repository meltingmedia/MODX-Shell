<?php namespace MODX\Shell\Configuration;

/**
 * A configuration object storing all third party commands
 */
class Extension extends Base
{
    protected $path;

    public function __construct(array $items = array())
    {
        $this->path = $this->getConfigPath() . 'extraCommands.php';
        if (empty($items)) {
            $this->load($this->path);
        } else {
            $this->items = $items;
        }
    }

    public function get($key, $default = null)
    {
        $index = array_search($key, $this->items);
        if ($index !== false) {
            return $key;
        }

        return $default;
    }

    public function set($key, $value = null)
    {
        $index = array_search($key, $this->items);
        if ($index === false) {
            $this->items[] = $key;
        }
    }

    public function remove($key)
    {
        $index = array_search($key, $this->items);
        if ($index !== false) {
            unset($this->items[$index]);
        }
    }

    public function save()
    {
        $content = $this->formatData();
        $this->makeSureConfigPathExists();

        return file_put_contents($this->path, $content) !== false;
    }

    /**
     * Format the items so they could be stored as PHP array
     *
     * @return string
     */
    public function formatData()
    {
        $content = '<?php' . "\n\n"
                   .'return array(' ."\n";

        sort($this->items);
        foreach ($this->items as $c) {
            $content .= "    '{$c}',\n";
        }

        $content .= ');' ."\n";

        return $content;
    }

    public function load($path = null)
    {
        if (!$path) {
            $path = $this->path;
        }
        if (file_exists($path)) {
            $this->items = include $path;
        }
    }
}
