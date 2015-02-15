<?php namespace MODX\Shell;

use Composer\Script\Event;
use MODX\Shell\Application;

/**
 * A simple class to implement to register third party commands
 */
abstract class CommandRegistrar
{
    /**
     * @var \Composer\IO\IOInterface $io
     */
    public static $io;
    /**
     * @var \ReflectionClass
     */
    protected static $reflection = null;

    /**
     * Process the command registration
     *
     * @param Event $event
     */
    public static function run(Event $event)
    {
        self::$io = $event->getIO();

        $app = new Application;
        $extraFile = $app->getExtraCommandsConfig();
        self::$io->write('<info>Editing extra commands for '.self::getNS().'...</info>');

        $commands = array();
        if (file_exists($extraFile)) {
            // Load already registered commands
            $commands = include $extraFile;
            // And remove "deprecated" ones, if any
            self::unRegister($commands);
        }

        // Iterate the Command folder, looking for command classes
        self::$io->write('<info>...looking for commands to register...</info>');
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach (self::listCommands() as $file) {
            $className = self::getCommandClass($file);
            if (in_array($className, $commands)) {
                // Command already registered, skipping it
                continue;
            }
            $commands[] = $className;
        }

        // Write to extra commands configuration file
        $app->writeExtraConfig($commands);

        self::$io->write('<info>...done</info>');
        self::$reflection = null;
    }

    /**
     * Un-register previous commands, that are now deprecated/removed
     *
     * @param array $commands Existing commands
     */
    public static function unRegister(array &$commands = array())
    {
        $deprecated = self::getRootPath() .'/deprecated.php';
        if (file_exists($deprecated)) {
            self::$io->write('<info>...looking for commands to remove...</info>');
            $deprecated = include $deprecated;
            foreach ($deprecated as $class) {
                $idx = array_search($class, $commands);
                if ($idx !== false && isset($commands[$idx])) {
                    self::$io->write("Removing {$commands[$idx]}");
                    unset($commands[$idx]);
                }
            }
        }
    }

    /**
     * List instantiable commands
     *
     * @return \Symfony\Component\Finder\Finder
     */
    public static function listCommands()
    {
        $basePath = self::getRootPath() . '/Command';

        $finder = new \Symfony\Component\Finder\Finder();
        $finder->files()
            ->in($basePath)
            ->notContains('abstract class')
            ->name('*.php');

        return $finder;
    }

    /**
     * Convert a file name to a class name
     *
     * @param \Symfony\Component\Finder\SplFileInfo $file
     *
     * @return string
     */
    public static function getCommandClass(\Symfony\Component\Finder\SplFileInfo &$file)
    {
        $name = rtrim($file->getRelativePathname(), '.php');
        $name = str_replace('/', '\\', $name);

        return self::getNS() . '\\Command\\' . $name;
    }

    /**
     * Get the namespace of the called sub class
     *
     * @return string
     */
    protected static function getNS()
    {
        return self::getReflection()->getNamespaceName();
    }

    /**
     * Get a reflection of the called sub class
     *
     * @return \ReflectionClass
     */
    protected static function getReflection()
    {
        if (!self::$reflection) {
            self::$reflection = new \ReflectionClass(get_called_class());
        }

        return self::$reflection;
    }

    /**
     * Get the path of the called sub class
     *
     * @return string
     */
    protected static function getRootPath()
    {
        return dirname(self::getReflection()->getFileName());
    }
}
