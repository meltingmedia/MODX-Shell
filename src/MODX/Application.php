<?php namespace MODX;

use Symfony\Component\Console\Application as BaseApp;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class Application extends BaseApp
{
    const VERSION = '0.0.1';

    /**
     * @var \modX
     */
    protected $modx;

    public function __construct()
    {
        parent::__construct('MODX Shell', self::VERSION);
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $this->loadCommands($commands);

        return $commands;
    }

    protected function loadCommands(array &$commands = array())
    {
        $basePath = dirname(__DIR__) . '/MODX/Command';

        $finder = new \Symfony\Component\Finder\Finder();
        $finder->files()
            ->in($basePath)
            ->notContains('abstract class')
            ->name('*.php');

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $className = $this->getCommandClass($file);

            $commands[] = new $className();
        }

        return $commands;
    }

    protected function getCommandClass(\Symfony\Component\Finder\SplFileInfo &$file)
    {
        $name = rtrim($file->getRelativePathname(), '.php');
        $name = str_replace('/', '\\', $name);

        return 'MODX\Command\\' . $name;
    }
}
