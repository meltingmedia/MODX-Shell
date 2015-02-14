<?php namespace MODX\Shell\Command;

use Symfony\Component\Console\Helper\Table;

/**
 * Command to deal with list processors (ie. getlist)
 */
abstract class ListProcessor extends ProcessorCmd
{
    protected $headers = array(
        'id', 'name', 'description'
    );

    protected function processResponse(array $results = array())
    {
        //echo print_r($results, true);
        $total = $results['total'];
        $results = $results['results'];

        /** @var \Symfony\Component\Console\Helper\Table $table */
        //$table = $this->getApplication()->getHelperSet()->get('table');
        $table = new Table($this->output);
        $table->setHeaders($this->headers);

        foreach ($results as $row) {
            $table->addRow($this->processRow($row));
        }

        $table->render();

        // Footer
        // @todo: make this configurable
        /** @var \Symfony\Component\Console\Helper\Table $t */
        //$t = $this->getApplication()->getHelperSet()->get('table');
        $t = new Table($this->output);
        $t->setHeaders(array('', ''));
        $t->setStyle('compact');
        //$t->setLayout($t::LAYOUT_COMPACT);

        $t->setRows(array(
            array(
                'displaying '. count($results) .' item(s)',
                'of '. $total,
            ),
            array('',''),
        ));

        $t->render();
    }
}
