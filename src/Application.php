<?php namespace MODX\Shell;

use Symfony\Component\Console\Application as BaseApp;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class Application extends BaseApp
{
    const VERSION = '0.0.1';
    public $instances;
    public $extensions;
    public $components;

    /**
     * @var \modX
     */
    public $modx;

    public function __construct()
    {
        $this->instances = new Configuration\Instance();
        $this->extensions = new Configuration\Extension();
        $this->components = new Configuration\Component($this);
        parent::__construct('MODX Shell', self::VERSION);
    }

    protected function getDefaultInputDefinition()
    {
        $def = parent::getDefaultInputDefinition();
        $def->addOption(new InputOption('--site', '-s', InputOption::VALUE_OPTIONAL, 'An instance name'));

        return $def;
    }

    /**
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
        $this->handleInstanceAsArgument();
        $basePath = __DIR__ . '/Command';

        $finder = new \Symfony\Component\Finder\Finder();
        $finder->files()
            ->in($basePath)
            ->notContains('abstract class')
            ->name('*.php');

        // Check if modX is available
        $modx = $this->getMODX();

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            /** @var \MODX\Shell\Command\BaseCmd $className */
            $className = $this->getCommandClass($file);

            // Prevent commands which requires modX to be displayed if modX is not available
            if (defined("{$className}::MODX") && (!$className::MODX || $modx)) {
                $commands[] = new $className();
            }
        }
    }

    /**
     * Adds the ability to run a command on an instance without being in its folders/path
     */
    protected function handleInstanceAsArgument()
    {
        $app = $this;
        array_filter($_SERVER['argv'], function($value) use ($app) {
            if (strpos($value, '-s') === 0) {
                $site = str_replace('-s', '', $value);
                $config = $app->instances->getAll();
                if (array_key_exists($site, $config)) {
                    chdir($config[$site]['base_path']);
                }

                return false;
            }

            return true;
        });
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
        // Check if modX is available
        $modx = $this->getMODX();

        foreach ($this->extensions->getAll() as $class) {
            if (!class_exists($class)) {
                $this->extensions->remove($class);
                $toRemove = true;
                continue;
            }
            // Prevent commands which requires modX to be displayed if modX is not available
            if (defined("{$class}::MODX") && (!$class::MODX || $modx)) {
                $commands[] = new $class();
            }
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
                $service = $config['service'];
                $lower = strtolower($service);

                $cmpCommands = array();

                $loaded = $this->getExtraService($config);
                if (!$loaded) {
                    //echo 'Unable to load service class '.$service.' from '. $path ."\n";
                    continue;
                }

                $this->modx->{$lower} =& $loaded;
                if (method_exists($loaded, 'getCommands')) {
                    $cmpCommands = $loaded->getCommands();
                }

                foreach ($cmpCommands as $c) {
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
            //$config = $this->getCurrentConfig();
            $currentPath = $this->getCwd();
            // First search in current dir
            $coreConfig = file_exists('./config.core.php') ? './config.core.php' : false;
            if (!$coreConfig) {
                // Then iterate through the configuration file
                foreach ($this->instances->getAll() as $cmp => $data) {
                    if (array_key_exists('base_path', $data)) {
                        $length = strlen($data['base_path']);
                        if (substr($currentPath, 0, $length) === $data['base_path']) {
                            $coreConfig = $data['base_path'] . 'config.core.php';
                            break;
                        }
                    }
                }
            }
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
        if (!$config || !file_exists($config)) {
            return false;
        }

        require_once $config;
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
     * Get the configuration file path for the current user
     *
     * @return string The configuration file path
     */
    public function getConfigFile()
    {
        $path = getenv('HOME') . '/.modx/config.ini';
        if (!file_exists($path)) {
            $base = getenv('HOME') . '/.modx/';
            if (!file_exists($base)) {
                mkdir($base);
            }
            file_put_contents($path, '');
        }

        return $path;
    }

    /**
     * Get the extra commands configuration file path
     *
     * @return string
     * @deprecated
     */
    public function getExtraCommandsConfig()
    {
        $path = getenv('HOME') . '/.modx/extraCommands.php';

        return $path;
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
}
