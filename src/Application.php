<?php namespace MODX\Shell;

use Symfony\Component\Console\Application as BaseApp;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

/**
 * MODX Shell application
 */
class Application extends BaseApp
{
    /**
     * @var Configuration\Instance
     */
    public $instances;
    /**
     * @var Configuration\Extension
     */
    public $extensions;
    /**
     * @var Configuration\Component
     */
    public $components;
    /**
     * @var Configuration\ExcludedCommands
     */
    public $excludedCommands;

    /**
     * @var \modX
     */
    public $modx;

    public function __construct()
    {
        $this->instances = new Configuration\Instance();
        // Change the "context" if executing the command on a specific instance
        $this->handleForcedInstance();
        $this->extensions = new Configuration\Extension();
        $this->excludedCommands = new Configuration\ExcludedCommands();
        $this->components = new Configuration\Component($this);
        parent::__construct('MODX Shell', file_get_contents(dirname(__DIR__) . '/VERSION'));
    }

    protected function getDefaultInputDefinition()
    {
        $def = parent::getDefaultInputDefinition();
        $def->addOption(
            new InputOption('--site', '-s', InputOption::VALUE_OPTIONAL, 'An instance name to execute the command to')
        );

        return $def;
    }

    /**
     * Load/register all available commands
     *
     * @return \Symfony\Component\Console\Command\Command[]
     */
    protected function getDefaultCommands()
    {
        // Regular Symfony Console commands
        $commands = parent::getDefaultCommands();
        // Core commands
        $this->loadCommands($commands);
        // Extension commands
        $this->loadExtraCommands($commands);
        // Commands registered in the modX instance we are dealing with
        $this->loadComponentsCommands($commands);

        return $commands;
    }

    /**
     * Iterate over existing commands to declare them in the application
     *
     * @param array $commands
     */
    protected function loadCommands(array &$commands = array())
    {
        $basePath = __DIR__ . '/Command';

        $finder = new \Symfony\Component\Finder\Finder();
        $finder->files()
            ->in($basePath)
            ->notContains('abstract class')
            ->name('*.php');

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            /** @var \MODX\Shell\Command\BaseCmd $className */
            $className = $this->getCommandClass($file);
            $commands[] = new $className();
        }
    }

    /**
     * Adds the ability to run a command on an instance without being in its folders/path
     */
    protected function handleForcedInstance()
    {
        if ($this->instances->current()) {
            return;
        }
        $app = $this;
        $instance = $this->checkInstanceAsArgument($this->getDefaultInstance());

        if ($instance) {
            //echo 'Instance used is '. $instance . "\n";
            $dir = $app->instances->getConfig($instance, 'base_path');
            if ($dir) {
                chdir($dir);
            }
        }
    }

    /**
     * Get the configured default instance, if any
     *
     * @return null|string
     */
    protected function getDefaultInstance()
    {
        return $this->instances->getConfig('__default__', 'class');
    }

    /**
     * Check if any instance name has been given from the CLI
     *
     * @param string|null $instance
     *
     * @return string|null
     */
    protected function checkInstanceAsArgument($instance)
    {
        $app = $this;
        if (isset($_SERVER['argv'])) {
            array_filter($_SERVER['argv'], function ($value) use ($app, &$instance) {
                if (strpos($value, '-s') === 0) {
                    $instance = str_replace('-s', '', $value);

                    return false;
                }

                return true;
            });
        }

        return $instance;
    }

    /**
     * Generate a command class name from a file
     *
     * @param \Symfony\Component\Finder\SplFileInfo $file
     *
     * @return string
     */
    protected function getCommandClass(\Symfony\Component\Finder\SplFileInfo &$file)
    {
        $name = rtrim($file->getRelativePathname(), '.php');
        $name = str_replace('/', '\\', $name);

        return 'MODX\\Shell\\Command\\' . $name;
    }

    /**
     * Allow custom commands to be added (ie. a composer library)
     *
     * @param array $commands
     */
    protected function loadExtraCommands(array &$commands = array())
    {
        $toRemove = false;

        foreach ($this->extensions->getAll() as $class) {
            if (!class_exists($class)) {
                // Purge non existing/badly configured commands
                $this->extensions->remove($class);
                $toRemove = true;
                continue;
            }
            $commands[] = new $class();
        }
        if ($toRemove) {
            $this->extensions->save();
        }
    }

    /**
     * Load registered commands within the modX instance
     *
     * @param array $commands
     */
    protected function loadComponentsCommands(array & $commands = array())
    {
        if ($this->getMODX()) {
            foreach ($this->components->getAll() as $k => $config) {
                $loaded = $this->getExtraService($config);
                if (!$loaded || !method_exists($loaded, 'getCommands')) {
                    //echo 'Unable to load service class '.$service.' from '. $path ."\n";
                    continue;
                }

                foreach ($loaded->getCommands() as $c) {
                    $commands[] = new $c();
                }
            }
        }
    }

    /**
     * Convenient method to load a service responsible of extra commands loading
     *
     * @param array $data
     *
     * @return null|object
     */
    public function getExtraService(array $data = array())
    {
        $service = $data['service'];

        $params = array();
        if (array_key_exists('params', $data)) {
            $params = $data['params'];
        }

        return $this->getService($service, $params);
    }



    /**
     * Get the modX instance
     *
     * @return \modX|null The modX instance if any
     */
    public function getMODX()
    {
        if (null === $this->modx) {
            $coreConfig = $this->instances->getCurrentConfig('base_path');
            if ($coreConfig) {
                // A base path has been found
                $coreConfig .= 'config.core.php';
            } else {
                // Get current path
                $coreConfig = $this->getCwd() . 'config.core.php';
            }
            $coreConfig = realpath($coreConfig);
            if ($coreConfig && file_exists($coreConfig)) {
                $this->modx = $this->loadMODX($coreConfig);
            }
        }

        return $this->modx;
    }

    /**
     * Get the current working dir with trailing slash
     *
     * @return string|bool
     */
    public function getCwd()
    {
        $path = getcwd();
        if ($path && substr($path, -1) !== '/') {
            $path .= '/';
        }

        return $path;
    }

    /**
     * Instantiate the MODx object from the given configuration file
     *
     * @param string $config The path to MODX configuration file
     *
     * @return bool|\modX False if modX was not instantiated, or a modX instance
     */
    protected function loadMODX($config)
    {
        if (!defined('MODX_CORE_PATH')) {
            if (!$config || !file_exists($config)) {
                return false;
            }

            require_once $config;
        }
        $loader = MODX_CORE_PATH . 'vendor/autoload.php';
        if (file_exists($loader)) {
            require_once $loader;
        }
        $modx = MODX_CORE_PATH . 'model/modx/modx.class.php';
        if (file_exists($modx)) {
            require_once $modx;
            $modx = new \modX();
            $this->initialize($modx);

            if ($modx instanceof \modX) {
                $version = $modx->getVersionData();
                if (version_compare($version['full_version'], '2.1.0-pl', '<')) {
                    return $this->hackedMODX();
                }

                return $modx;
            }
        }

        return false;
    }

    /**
     * Get an extended modX version for Revo < 2.1 because of modX::runProcessor issue
     *
     * @return Xdom
     */
    protected function hackedMODX()
    {
        $modx = new Xdom();
        $this->initialize($modx);

        return $modx;
    }

    /**
     * Convenient method to initialize modX
     *
     * @param \modX $modx
     *
     * @return \modX
     */
    protected function initialize(\modX &$modx)
    {
        $modx->initialize('mgr');
        $modx->getService('error', 'error.modError', '', '');
        //$this->modx->setLogTarget('ECHO');

        // @todo: ability to define a user (or anything else)

        return $modx;
    }

    /**
     * Try to load a service class
     *
     * @param string $name The service name
     * @param array $params Some parameters to construct the service class
     *
     * @return null|object The instantiated service class if found
     */
    public function getService($name = '', $params = array())
    {
        if (empty($name)) {
            $name = $this->instances->current();
        }
        if (!$name) {
            return null;
        }
        $this->getMODX();
        $lower = strtolower($name);

        $path = $this->modx->getOption(
            "{$lower}.core_path",
            null,
            $this->modx->getOption('core_path') . "components/{$lower}/"
        );
        $classFile = "{$lower}.class.php";
        if (file_exists($path . "model/{$lower}/{$classFile}")) {
            // First check "common" path
            $path .= "model/{$lower}/";
        } elseif (file_exists($path . "services/{$classFile}")) {
            // Then check "our" path
            $path .= 'services/';
        } else {
            // Assume it's a modX base service
            $path = null;
        }

        return $this->modx->getService($lower, $name, $path, $params);
    }

    /**
     * List command classes to be "hidden"
     *
     * @return array
     */
    public function getExcludedCommands()
    {
        return $this->excludedCommands->getAll();
    }
}
