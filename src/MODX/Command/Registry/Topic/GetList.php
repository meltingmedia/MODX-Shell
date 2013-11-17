<?php namespace MODX\Command\Registry\Topic;

use MODX\Command\ProcessorCmd;

class GetList extends ProcessorCmd
{
    const MODX = true;

    protected $headers = array(
        'id', 'queue', 'name'
    );

    protected $name = 'registry:topic:list';
    protected $description = 'List existing modRegistry topics';

    protected function process()
    {
        /** @var \Symfony\Component\Console\Helper\TableHelper $table */
        $table = $this->getApplication()->getHelperSet()->get('table');
        $table->setHeaders($this->headers);


        $c = $this->modx->newQuery('registry.db.modDbRegisterTopic');

        $collection = $this->modx->getIterator('registry.db.modDbRegisterTopic', $c);

        //$rows = array();
        /** @var \modDbRegisterTopic $object */
        foreach ($collection as $object) {
            $row = $object->toArray();
            $table->addRow($this->processRow($row));
        }

        $table->render($this->output);
    }
}
