<?php namespace MODX\Shell\Configuration;

/**
 * A configuration interface to implement
 */
interface ConfigurationInterface
{
    public function get($key, $default = null);

    public function set($key, $value = null);

    public function remove($key);

    public function getAll();

    public function save();
}
