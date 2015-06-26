<?php namespace MODX\Shell\Command\Config;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Helper\Table;

class GetExcludeCommand extends BaseCmd
{
    protected $name = 'config:exclude:get';
    protected $description = 'List excluded command classes, if any';

    protected function process()
    {
        $excludes = $this->getApplication()->getExcludedCommands();
        if (empty($excludes)) {
            return $this->info('No excluded command classes');
        }

        $table = new Table($this->output);
        $table->setHeaders(array(
            'Command class'
        ));
        foreach ($excludes as $class) {
            $table->addRow(array($class));
        }
        //$table->addRow($this->processRow($object));

        $table->render();
    }
}
