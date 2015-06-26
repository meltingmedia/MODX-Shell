<?php namespace MODX\Shell\Command\System\Log;

use MODX\Shell\Command\BaseCmd;
use MODX\Shell\Formatter\ColoredLog;
use Symfony\Component\Process\Process;

/**
 * A command to watch the system log live (using tail)
 */
class Listen extends BaseCmd
{
    const MODX = true;

    protected $name = 'system:log:listen';
    protected $description = 'Watch MODX error log live';
    /**
     * @var ColoredLog
     */
    public $formatter;

    protected function process()
    {
        $this->formatter = new ColoredLog();
        $cache = $this->modx->getOption('cache_path') . 'logs/error.log';
        $cmd = 'tail -f -n 50 '. $cache;

        $process = new Process($cmd);
        $process->setTimeout(0);
        $me = $this;
        $output = $this->output;
        $process->run(function ($type, $buffer) use ($output, $me) {
            $output->write($me->formatter->process($buffer));
        });
    }
}
