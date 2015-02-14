<?php namespace MODX\Shell\Command\Config;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

class Go extends BaseCmd
{
    protected $name = 'config:go';
    protected $description = 'Go to the given modx installation path';

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'The instance name'
            ),
        );
    }

    protected function process()
    {
        $search = $this->argument('name');
        $key = 'name';
        if (is_numeric($search)) {
            $key = 'idx';
        }

        $app = $this->getApplication();
        $config = $app->getCurrentConfig();

        $idx = 1;
        foreach ($config as $name => $data) {
            if ($$key == $search) {
                //$this->info($name);
                if (!array_key_exists('base_path', $data)) {
                    return $this->error('No base path found in config file');
                }
                $this->info('Want to go to <comment>'. $data['base_path'] . '</comment> ?');
//                $bash = __DIR__.'/go.sh';
//                $this->info($bash);
//                `{$bash} a`;
                //$process = new Process('sh '. $bash .' aa');
//                $process = new Process('cd '. $data['base_path']);
//                $done = chdir($data['base_path']);
//                $this->info($done ? 'done' : 'nop');
//                $this->output->write('cd '. $data['base_path']);
                //echo 'cd '. $data['base_path'];
//                `echo {$data['base_path']}`;
//                `cd {$data['base_path']}`;
                return;
            }
            $idx += 1;
        }
    }
}
