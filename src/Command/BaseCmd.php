<?php namespace MODX\Shell\Command;

use MODX\Shell\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A base command
 */
abstract class BaseCmd extends Command
{
    /**
     * Define whether or not a modX instance is required to run the command
     */
    const MODX = false;
    const MIN_MODX = '';

    /**
     * The input interface implementation.
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * The output interface implementation.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description;

    /**
     * Command help
     * @todo allow advanced output (using a method ?)
     *
     * @var string
     */
    protected $help = '';

    /**
     * A modX instance
     *
     * @var \modX
     */
    public $modx;


    /**
     * Create a new console command instance.
     */
    public function __construct()
    {
        parent::__construct($this->name);
        $this->setDescription($this->description);
        $this->specifyParameters();

        if ($this->help && !empty($this->help)) {
            $this->setHelp("<info>{$this->help}</info>");
        }
    }



    /**
     * Gets the application instance for this command.
     *
     * @return \MODX\Shell\Application An Application instance
     */
    public function getApplication()
    {
        return parent::getApplication();
    }

    /**
     * Specify the arguments and options on the command.
     *
     * @return void
     */
    protected function specifyParameters()
    {
        foreach ($this->getArguments() as $arguments) {
            call_user_func_array(array($this, 'addArgument'), $arguments);
        }

        foreach ($this->getOptions() as $options) {
            call_user_func_array(array($this, 'addOption'), $options);
        }
    }

    /**
     * Run the console command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     *
     * @return integer
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        return parent::run($input, $output);
    }

    /**
     * Required actions to be performed before execution
     *
     * @return bool Whether or not required actions went successfully
     */
    protected function init()
    {
        if (self::MODX) {
            $loaded = $this->getMODX();
            if (!$loaded) {
                $this->error('Sorry, seems like MODX is not accessible here...');
                return false;
            }
        };

        return true;
    }

    /**
     * Execute the console command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     *
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->init()) {
            return $this->error('Unable to init the command!');
        }

        return $this->process();
    }

    /**
     * The real command logic to override
     */
    protected function process()
    {
        $this->error('Please override process() method!');
    }

    /**
     * Call another console command.
     *
     * @param  string  $command
     * @param  array   $arguments
     *
     * @return integer
     */
    public function call($command, array $arguments = array())
    {
        $instance = $this->getApplication()->find($command);
        $arguments['command'] = $command;

        return $instance->run(new ArrayInput($arguments), $this->output);
    }

    /**
     * Call another console command silently.
     *
     * @param  string  $command
     * @param  array   $arguments
     *
     * @return integer
     */
    public function callSilent($command, array $arguments = array())
    {
        $instance = $this->getApplication()->find($command);
        $arguments['command'] = $command;

        return $instance->run(new ArrayInput($arguments), new NullOutput);
    }

    /**
     * Get the value of a command argument.
     *
     * @param  string  $key
     *
     * @return string|array
     */
    public function argument($key = null)
    {
        if (is_null($key)) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get the value of a command option.
     *
     * @param  string  $key
     *
     * @return string|array
     */
    public function option($key = null)
    {
        if (is_null($key)) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Confirm a question with the user.
     *
     * @param  string  $question
     * @param  bool    $default
     *
     * @return bool
     */
    public function confirm($question, $default = true)
    {
        /** @var \Symfony\Component\Console\Helper\DialogHelper $dialog */
        $dialog = $this->getHelperSet()->get('dialog');

        return $dialog->askConfirmation($this->output, "<question>$question</question>", $default);
    }

    /**
     * Prompt the user for input.
     *
     * @param  string  $question
     * @param  string  $default
     *
     * @return string
     */
    public function ask($question, $default = null)
    {
        /** @var \Symfony\Component\Console\Helper\DialogHelper $dialog */
        $dialog = $this->getHelperSet()->get('dialog');

        return $dialog->ask($this->output, "<question>$question</question>", $default);
    }


    /**
     * Prompt the user for input but hide the answer from the console.
     *
     * @param  string  $question
     * @param  bool    $fallback
     *
     * @return string
     */
    public function secret($question, $fallback = true)
    {
        /** @var \Symfony\Component\Console\Helper\DialogHelper $dialog */
        $dialog = $this->getHelperSet()->get('dialog');

        return $dialog->askHiddenResponse($this->output, "<question>$question</question>", $fallback);
    }

    /**
     * Write a string as standard output.
     *
     * @param  string  $string
     *
     * @return void
     */
    public function line($string)
    {
        $this->output->writeln($string);
    }

    /**
     * Write a string as information output.
     *
     * @param  string  $string
     *
     * @return void
     */
    public function info($string)
    {
        $this->output->writeln("<info>$string</info>");
    }

    /**
     * Write a string as comment output.
     *
     * @param  string  $string
     *
     * @return void
     */
    public function comment($string)
    {
        $this->output->writeln("<comment>$string</comment>");
    }

    /**
     * Write a string as question output.
     *
     * @param  string  $string
     *
     * @return void
     */
    public function question($string)
    {
        $this->output->writeln("<question>$string</question>");
    }

    /**
     * Write a string as error output.
     *
     * @param  string  $string
     *
     * @return void
     */
    public function error($string)
    {
        $this->output->writeln("<error>$string</error>");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }

    /**
     * Get the output implementation.
     *
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Try to get a modX instance
     *
     * @return \modX|null
     */
    public function getMODX()
    {
        if (!($this->modx instanceof \modX)) {
            $this->modx = $this->getApplication()->getMODX();
        }

        return $this->modx;
    }

    /**
     * Check if the command if "available" (mostly if a modX instance if available)
     *
     * @return bool
     */
    public function isEnabled()
    {
        if ($this::MODX) {
            // Handle commands requiring a modX instance
            if (!$this->getMODX()) {
                return false;
            }
            if (!empty($this::MIN_MODX)) {
                // Handle commands requiring a minimum modX version
                $version = $this->modx->getVersionData();
                if (!version_compare($version['full_version'], $this::MIN_MODX, '>=')) {
                    return false;
                }
            }
        }

        return true;
    }
}
