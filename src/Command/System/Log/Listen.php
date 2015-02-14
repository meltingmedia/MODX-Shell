<?php namespace MODX\Shell\Command\System\Log;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Process\Process;

class Listen extends BaseCmd
{
    const MODX = true;

    protected $name = 'system:log:listen';
    protected $description = 'Watch MODX error log live';

    protected function process()
    {
        $cache = $this->modx->getOption('cache_path') . 'logs/error.log';
        $cmd = 'tailf -n 50 '. $cache;

        $process = new Process($cmd);
        $process->setTimeout(0);
        $process->run(function($type, $buffer) {
            //$this->line($buffer);
            echo $buffer;
        });
//        while ($process->isRunning()) {
//            $this->line();
//        }
    }
}
