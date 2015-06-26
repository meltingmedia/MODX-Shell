<?php namespace MODX\Shell\Configuration;

/**
 * A configuration object storing all excluded commands
 */
class ExcludedCommands extends Extension
{
    public function __construct(array $items = array())
    {
        $this->path = $this->getConfigPath() . 'excludedCommands.php';
        parent::__construct($items);
    }
}
