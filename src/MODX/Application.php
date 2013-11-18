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

//        if ('phar:' === substr(__FILE__, 0, 5)) {
//            $commands[] = new Command\SelfUpdateCommand();
//        }

        $this->loadExtraCommands($commands);
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

        // Check if modX is available
        $modx = $this->getMODX();

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $className = $this->getCommandClass($file);

            // Prevent commands which requires modX to be displayed if modX is not available
            if (defined("{$className}::MODX") && (!$className::MODX || $modx)) {
                $commands[] = new $className();
            }

        }
    }

    protected function getCommandClass(\Symfony\Component\Finder\SplFileInfo &$file)
    {
        $name = rtrim($file->getRelativePathname(), '.php');
        $name = str_replace('/', '\\', $name);

        return 'MODX\\Command\\' . $name;
    }

    /**
     * Allow custom commands to be added (ie. a composer library)
     *
     * @param array $commands
     */
    protected function loadExtraCommands(array &$commands = array())
    {
        $configFile = __DIR__ .'/config/config.php';
        if (file_exists($configFile)) {

            // Check if modX is available
            $modx = $this->getMODX();

            $extras = include $configFile;
            foreach ($extras as $class) {
                // Prevent commands which requires modX to be displayed if modX is not available
                if (defined("{$class}::MODX") && (!$class::MODX || $modx)) {
                    $commands[] = new $class();
                }
            }
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
            $componentsCommands = $this->modx->fromJSON($this->modx->getOption('console_commands', null, '{}'));
            //echo print_r($componentsCommands, true);
            foreach ($componentsCommands as $k => $config) {

                //echo print_r($config, true);

                $service = $config['service'];
                $lower = strtolower($service);

                $path = $config['service_path'];

                $params = array();
                if (array_key_exists('params', $config)) {
                    $params = $config['params'];
                }
                $cmpCommands = array();
                //$cmpCommands = $config['commands'];

                $loaded = $this->modx->getService($lower, $service, $path, $params);
                if (!$loaded) {
                    echo 'Unable to load service class '.$service.' from '. $path ."\n";
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


    // @TODO refactor below (taken from CmpHelper)

    /**
     * Get the modX instance
     *
     * @return \modX|null The modX instance if any
     */
    public function getMODX()
    {
        if (null === $this->modx) {
            $config = $this->getCurrentConfig();
            $currentPath = getcwd();
            if (substr($currentPath, -1) !== '/') {
                $currentPath .= '/';
            }
            // First search in current dir
            $coreConfig = file_exists('./config.core.php') ? './config.core.php' : false;
            if (!$coreConfig) {
                // Then iterate through the configuration file
                foreach ($config as $cmp => $data) {
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
                $this->loadMODX($coreConfig);
            }
        }

        return $this->modx;
    }

    /**
     * Instantiate the MODx object from the given configuration file
     *
     * @param string $config The path to MODX configuration file
     *
     * @return bool Whether or not modX was instantiated
     */
    protected function loadMODX($config)
    {
        if (!$config || !file_exists($config)) {
            return false;
        }
        require_once $config;
        $modx = MODX_CORE_PATH . 'model/modx/modx.class.php';
        if (file_exists($modx)) {
            require_once $modx;
            $this->modx = new \modX();
            $this->modx->initialize('mgr');
            $this->modx->getService('error', 'error.modError', '', '');

            // @todo: ability to define a user (or anything else)

            if ($this->modx instanceof \modX) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the configuration file path for the current user
     *
     * @return string The configuration file path
     */
    public function getConfigFile()
    {
        //$path = '/home/romain/.cmphelper/test.ini';
        $path = getenv('HOME') . '/.cmphelper/config.ini';
        //$path = __DIR__ . '/config/config.ini';
        //echo $path ."\n";

        return $path;
    }

    public function getExtraCommandsConfig()
    {
        return __DIR__ .'/config/extraCommands.php';
    }

    /**
     * Reads & return the configuration file
     *
     * @return array The original config file (or an empty array)
     */
    public function getCurrentConfig()
    {
        if (empty($this->config)) {
            $config = $this->getConfigFile();
            if (file_exists($config)) {
                $this->config = parse_ini_file($config, true);
            }
        }

        return $this->config;
    }

    /**
     * Writes the given data to the configuration file
     *
     * @param array $data Configuration data to write
     *
     * @return bool Whether or not the write succeed
     */
    public function writeConfig(array $data) {
        $content = "; This is MODX Shell configuration file \n\n";
        $path = $this->getConfigFile();

        foreach ($data as $cmp => $config) {
            // Section
            $content .= '['. $cmp ."]\n";
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

        // Create the config folder if required
        if (!file_exists($path)) {
            $tmp = explode('/', $path);
            unset ($tmp[(count($tmp) - 1)]);
            $folder = implode('/', $tmp);
            if (!file_exists($folder)) {
                mkdir($folder);
            }
        }

        return (file_put_contents($path, $content) !== false);
    }
}
