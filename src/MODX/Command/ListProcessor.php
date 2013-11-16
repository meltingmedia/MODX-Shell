<?php namespace MODX\Command;

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

        /** @var \Symfony\Component\Console\Helper\TableHelper $table */
        $table = $this->getApplication()->getHelperSet()->get('table');
        $table->setHeaders($this->headers);

        foreach ($results as $row) {
            $table->addRow($this->processRow($row));
        }

        $table->render($this->output);

        // Footer
        /** @var \Symfony\Component\Console\Helper\TableHelper $t */
        $t = $this->getApplication()->getHelperSet()->get('table');
        $t->setHeaders(array('', ''));
        $t->setLayout($t::LAYOUT_COMPACT);

        $t->setRows(array(
            array(
                'displaying '. count($results) .' item(s)',
                'of '. $total,
            ),
            array('',''),
        ));

        $t->render($this->output);
    }
}
