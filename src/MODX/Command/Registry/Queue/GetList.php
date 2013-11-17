<?php namespace MODX\Command\Registry\Queue;

use MODX\Command\ProcessorCmd;

class GetList extends ProcessorCmd
{
    const MODX = true;

    protected $headers = array(
        'id', 'name'
    );

    protected $name = 'registry:queue:list';
    protected $description = 'List existing modRegistry queues';

    protected function process()
    {
        /** @var \Symfony\Component\Console\Helper\TableHelper $table */
        $table = $this->getApplication()->getHelperSet()->get('table');
        $table->setHeaders($this->headers);


        $c = $this->modx->newQuery('registry.db.modDbRegisterQueue');

        $collection = $this->modx->getIterator('registry.db.modDbRegisterQueue', $c);

        //$rows = array();
        /** @var \modDbRegisterTopic $object */
        foreach ($collection as $object) {
            $row = $object->toArray();
            $table->addRow($this->processRow($row));
        }

        $table->render($this->output);
    }
}
