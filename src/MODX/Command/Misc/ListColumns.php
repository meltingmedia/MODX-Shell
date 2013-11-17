<?php namespace MODX\Command\Misc;

use MODX\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * List ancestries & columns for the given object
 */
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
        $ancestries = $this->getAncestries();
        if (!$ancestries || empty($ancestries)) {
            return $this->error('Seems like it\'s not a valid object');
        }

        /** @var \Symfony\Component\Console\Helper\TableHelper $table */
        $table = $this->getApplication()->getHelperSet()->get('table');
        $table->setHeaders(array(
            'object', 'column', 'default value'
        ));

        foreach ($ancestries as $name) {
            $f = $this->modx->getFields($name);
            foreach ($f as $k => $default) {
                $table->addRow(array(
                    $name,
                    $k,
                    $default
                ));
            }
        }

        $table->render($this->output);
    }

    /**
     * Get the ancestries tree
     *
     * @return array|bool
     */
    protected function getAncestries()
    {
        $class = $this->argument('class');

        $ancestry = array_reverse($this->modx->getAncestry($class));
        if (empty($ancestry)) {
            return false;
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

        return $ancestry;
    }
}
