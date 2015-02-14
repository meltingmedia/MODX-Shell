<?php namespace MODX\Shell;

use Composer\Script\Event;
use MODX\Shell\Application;

/**
 * Sample script to self register commands in MODX Shell
 */

abstract class CommandRegistrar
{
    /**
     * @var \Composer\IO\IOInterface $io
     */
    public static $io;
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
        self::$io->write('<info>Editing extra commands for '.__NAMESPACE__.'...</info>');

        $commands = array();
        if (file_exists($extraFile)) {
            // Load already registered commands
            $commands = include $extraFile;
            // And remove "deprecated" ones, if any
            self::unRegister($commands);
        }
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

        sort($commands);
        $result = self::arrayToString($commands);

        file_put_contents($extraFile, $result);
        self::$io->write('<info>...done</info>');
    }

    /**
     * Un-register previous commands, now deprecated
     *
     * @param array $commands Existing commands
     */
    public static function unRegister(array &$commands = array())
    {
        $deprecated = __DIR__ .'/deprecated.php';
        if (file_exists($deprecated)) {
            self::$io->write('<info>...looking for commands to remove...</info>');
            $deprecated = include $deprecated;
            foreach ($deprecated as $class) {
                $idx = array_search($class, $commands);
                if ($idx !== false) {
                    unset($commands[$idx]);
                    self::$io->write("Removing {$commands[$idx]}");
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
        $basePath = __DIR__ . '/Command';

        $finder = new \Symfony\Component\Finder\Finder();
        $finder->files()
            ->in($basePath)
            ->notContains('abstract class')
            ->name('*.php');

        return $finder;
    }

    /**
     * Convert an array of commands to a string, to be usable with file_put_content
     *
     * @param array $data The list of commands to register
     *
     * @return string
     */
    public static function arrayToString(array $data = array())
    {
        $string = '<?php' . "\n\n"
                  .'return array(' ."\n";

        foreach ($data as $c) {
            $string .= "    '{$c}',\n";
        }

        $string .= ');' ."\n";

        return $string;
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

        return __NAMESPACE__ . '\\Command\\' . $name;
    }
}
