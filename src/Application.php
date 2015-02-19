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

    /**
     * @var \modX
     */
    public $modx;

    public function __construct()
    {
        parent::__construct('MODX Shell', self::VERSION);
        //$this->getMODX();
    }

    protected function getDefaultInputDefinition()
    {
        $def = parent::getDefaultInputDefinition();
        $def->addOption(new InputOption('--site', '-s', InputOption::VALUE_OPTIONAL, 'An instance name'));

        return $def;
    }

//    protected function configureIO(InputInterface $input, OutputInterface $output)
//    {
//        //echo print_r($input, true);
////        if ($input->hasParameterOption(array('-i'))) {
////            echo 'Site found'. "\n";
////        }
////        echo 'First argument : ' . $input->getFirstArgument();
//        parent::configureIO($input, $output);
//
//        if ($input->hasParameterOption(array('--site', '-s'))) {
//            $site = $input->getParameterOption(array('--site', '-s'));
//            echo 'Site given : '. $site . "\n";
//            $this->readConfigFile();
//            $config = $this->config;
//            //echo 'Config : ' . print_r($config, true) . "\n";
//            if (array_key_exists($site, $config)) {
//                $changed = chdir($config[$site]['base_path']);
//                echo $changed .' changed to : '. $config[$site]['base_path']. "\n";
//            }
//        }
//        $this->getMODX();
//        //echo print_r($input->getOptions(), true);
//
//    }

    /**
     * @return \Symfony\Component\Console\Command\Command[]
     */
    protected function getDefaultCommands()
    {
        /** @var InputDefinition $commands */
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
        $args = array_filter($_SERVER['argv'], function($value) use ($app) {
            if (strpos($value, '-s') === 0 || strpos($value, '--site') === 0) {
                //echo $value . ' is a match'."\n";
                $site = str_replace(array('-s', '--site'), '', $value);
                $app->readConfigFile();
                $config = $app->config;
//                echo print_r($config, true) . "\n";
                if (array_key_exists($site, $config)) {
                    chdir($config[$site]['base_path']);
                }
                return false;
            }

            return true;
        });
        $_SERVER['argv'] = array_values($args);
//        echo print_r($_SERVER['argv'], true) . "\n";

//        $input = new ArgvInput();
//        if ($input->hasParameterOption(array('--site', '-s'))) {
//            $site = $input->getParameterOption(array('--site', '-s'));
//            $this->readConfigFile();
//            $config = $this->config;
//            if (array_key_exists($site, $config)) {
//                chdir($config[$site]['base_path']);
//            }
//        }
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
        $toRemove = array();
        $configFile = $this->getExtraCommandsConfig();
        if (file_exists($configFile)) {
            // Check if modX is available
            $modx = $this->getMODX();

            $extras = include $configFile;
            foreach ($extras as $class) {
                if (!class_exists($class)) {
                    $toRemove[] = $class;
                    continue;
                }
                // Prevent commands which requires modX to be displayed if modX is not available
                if (defined("{$class}::MODX") && (!$class::MODX || $modx)) {
                    $commands[] = new $class();
                }
            }
            if (!empty($toRemove)) {
                $this->unRegisterExtraCommands($toRemove);
            }
        }
    }

    /**
     * Un-register the given command classes from the extra commands
     *
     * @param string|array $commands
     */
    public function unRegisterExtraCommands($commands)
    {
        if (!is_array($commands)) {
            $commands = array($commands);
        }

        $path = $this->getExtraCommandsConfig();
        if (file_exists($path)) {
            $registered = include $path;
            foreach ($commands as $class) {
                $idx = array_search($class, $registered);
                if ($idx !== false && isset($registered[$idx])) {
                    unset($registered[$idx]);
                }
            }
            $this->writeExtraConfig($registered);
        }
    }

    /**
     * Write the given commands class to the "extra configuration" commands file
     *
     * @param array $commands
     *
     * @return bool
     */
    public function writeExtraConfig(array $commands = array())
    {
        sort($commands);
        $path = $this->getExtraCommandsConfig();
        $content = $this->arrayToString($commands);

        return file_put_contents($path, $content) !== false;
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
     * Load registered commands within the modX instance
     *
     * @param array $commands
     */
    protected function loadComponentsCommands(array & $commands = array())
    {
        if ($this->getMODX()) {
            $componentsCommands = $this->getComponentsWithCommands();
            //echo print_r($componentsCommands, true);
            foreach ($componentsCommands as $k => $config) {
                //echo print_r($config, true);

                $service = $config['service'];
                $lower = strtolower($service);

                //$path = $config['service_path'];

                $params = array();
                if (array_key_exists('params', $config)) {
                    $params = $config['params'];
                }
                $cmpCommands = array();
                //$cmpCommands = $config['commands'];

                //$loaded = $this->modx->getService($lower, $service, $path, $params);
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
                    // @TODO: inject service ?
                    $commands[] = new $c();
                }
            }
        }
    }

    /**
     * Get an array of registered services "adding" additional commands
     *
     * @return array
     */
    public function getComponentsWithCommands()
    {
        return $this->modx->fromJSON($this->modx->getOption('console_commands', null, '{}'));
    }

    /**
     * Convenient method to store extra services
     *
     * @param array $services
     *
     * @return bool
     */
    public function storeServices(array $services = array())
    {
        $modx = $this->getMODX();
        if (!$modx) {
            //
            return false;
        }

        /** @var \modSystemSetting $setting */
        $setting = $this->modx->getObject('modSystemSetting', array(
            'key' => 'console_commands'
        ));
        if (!$setting) {
            $setting = $this->modx->newObject('modSystemSetting');
            $setting->set('key', 'console_commands');
        }
        $setting->set('value', $this->modx->toJSON($services));
        $saved = $setting->save();
        if ($saved) {
            $this->modx->getCacheManager()->refresh();
            return true;
        }

        return false;
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
            $config = $this->getCurrentConfig();
            $currentPath = $this->getCwd();
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
                $this->modx = $this->loadMODX($coreConfig);
            }
        }

        return $this->modx;
    }

    /**
     * Set an instance of modX for this application
     *
     * @param \modX $modx
     *
     * @return void
     */
    public function setMODX(\modX &$modx)
    {
        $this->modx = $modx;
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
     */
    public function getExtraCommandsConfig()
    {
        $path = getenv('HOME') . '/.modx/extraCommands.php';

        return $path;
    }

    /**
     * Reads & return the configuration file
     *
     * @return array The original config file (or an empty array)
     */
    public function getCurrentConfig()
    {
        if (empty($this->config)) {
            $this->readConfigFile();
        }

        return $this->config;
    }

    /**
     * Refresh the configuration file
     *
     * @return void
     */
    protected function readConfigFile()
    {
        $config = $this->getConfigFile();
        if (file_exists($config)) {
            $this->config = parse_ini_file($config, true);
        }
    }

    /**
     * Writes the given data to the configuration file
     *
     * @param array $data Configuration data to write
     *
     * @return bool Whether or not the write succeed
     */
    public function writeConfig(array $data)
    {
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

        $result = (file_put_contents($path, $content) !== false);
        if ($result) {
            $this->readConfigFile();
        }

        return $result;
    }

    /**
     * Get the given instance configuration details
     *
     * @param string $name The optional instance name (defaulting to the current one)
     *
     * @return mixed Either an array of details if details are found, or null
     */
    public function getInstanceDetail($name = '')
    {
        if (empty($name)) {
            $name = $this->getCurrentInstanceName();
        }

        if (empty($name)) {
            return;
        }

        $config = $this->getCurrentConfig();
        if (array_key_exists($name, $config)) {
            return $config[$name];
        }

        return;
    }

    /**
     * Iterate through the configured instances to find the instance name we are "in"
     *
     * @return null|string Either the instance name if found, or null
     */
    public function getCurrentInstanceName()
    {
        $path = $this->getCwd();
        $config = $this->getCurrentConfig();
        foreach ($config as $name => $data) {
            if (array_key_exists('base_path', $data)) {
                $instancePath = $data['base_path'];
                if (substr($path, 0, strlen($instancePath)) === $instancePath) {
                    return $name;
                }
            }
        }

        return null;
    }

    /**
     * Iterate through the configured instances to find the base path of the instance we are "in"
     *
     * @return string|null Either the base path if found, or null
     */
    public function getCurrentInstancePath()
    {
        $path = $this->getCwd();
        $config = $this->getCurrentConfig();
        foreach ($config as $name => $data) {
            if (array_key_exists('base_path', $data)) {
                $instancePath = $data['base_path'];
                if (substr($path, 0, strlen($instancePath)) === $instancePath) {
                    return $instancePath;
                }
            }
        }

        return null;
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
        $this->getMODX();

        if (empty($name)) {
            $name = $this->getCurrentInstanceName();
        }
        if (!$name) {
            return null;
        }
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
