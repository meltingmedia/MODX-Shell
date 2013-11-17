<?php namespace MODX\Command\Misc;

use MODX\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

class ListColumns extends BaseCmd
{
    const MODX = true;

    protected $name = 'info:objectcolumns';
    protected $description = 'List available fields/columns for the given object';

    protected function getArguments()
    {
        return array(
            array(
                'class',
                InputArgument::REQUIRED,
                'The object class'
            ),
        );
    }

    protected function process()
    {
        $class = $this->argument('class');

        $ancestry = array_reverse($this->modx->getAncestry($class));
        if (empty($ancestry)) {
            return $this->error('Seems like it\'s not a valid object');
        }

        $this->output->writeln("\n" . 'Ancestry tree for '. $class .' : ' . "\n");
        $msg = '';
        $last = count($ancestry) -1;
        foreach ($ancestry as $idx => $name) {
            if ($idx !== $last) {
                $msg .= '<info>'. $name .'</info>';
            } else {
                $msg .= '<comment>'. $name .'</comment>';
            }

            if ($idx !== $last) {
                $msg .= '<comment> > </comment>';
            }
        }

        $this->output->writeln($msg . "\n");

//        /** @var \Symfony\Component\Console\Helper\TableHelper $t */
//        $t = $this->getApplication()->getHelperSet()->get('table');
//        $t->setHeaders(array(
//            'ancestor'
//        ));
//        foreach ($ancestry as $name) {
//            $t->addRow(array($name));
//        }
//        $t->render($this->output);

        //$this->output = new \Symfony\Component\Console\Output\OutputInterface;

        /** @var \Symfony\Component\Console\Helper\TableHelper $table */
        $table = $this->getApplication()->getHelperSet()->get('table');
        $table->setHeaders(array(
            'object', 'column', 'default value'
        ));


        $fields = array();
        foreach ($ancestry as $name) {
            $f = $this->modx->getFields($name);
            foreach ($f as $k => $default) {
//                $fields[] = array(
//                    $name,
//                    $k,
//                    $default
//                );

                $table->addRow(array(
                    $name,
                    $k,
                    $default
                ));
            }
        }

        //$this->info(print_r($fields, true));

        $table->render($this->output);
    }
}
