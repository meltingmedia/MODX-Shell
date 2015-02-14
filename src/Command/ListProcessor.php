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
    protected $showPagination = true;

    protected function processResponse(array $results = array())
    {
        $total = $results['total'];
        $results = $results['results'];

        $this->renderBody($results);
        if ($this->showPagination) {
            $this->renderPagination($results, $total);
        }
    }

    /**
     * Render the results as a table
     *
     * @param array $results
     */
    protected function renderBody(array $results = array())
    {
        /** @var \Symfony\Component\Console\Helper\Table $table */
        $table = new Table($this->output);
        $table->setHeaders($this->headers);

        foreach ($results as $row) {
            $table->addRow($this->processRow($row));
        }

        $table->render();
    }

    /**
     * Render the "pagination" table
     *
     * @param array $results
     * @param int $total
     *
     * @return void
     */
    protected function renderPagination(array $results = array(), $total = 0)
    {
        /** @var \Symfony\Component\Console\Helper\Table $t */
        $table = new Table($this->output);
        $table->setHeaders(array('', ''));
        $table->setStyle('compact');

        $table->setRows(array(
            array(
                'displaying '. count($results) .' item(s)',
                'of '. $total,
            ),
            array('',''),
        ));

        $table->render();
    }
}
