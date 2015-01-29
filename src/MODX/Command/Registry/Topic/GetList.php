<?php namespace MODX\Command\Registry\Topic;

use MODX\Command\ProcessorCmd;
use Symfony\Component\Console\Helper\Table;

class GetList extends ProcessorCmd
{
    protected $headers = array(
        'id', 'queue', 'name'
    );

    protected $name = 'registry:topic:list';
    protected $description = 'List existing modRegistry topics';

    protected function process()
    {
        $this->handleColumns();

        /** @var \Symfony\Component\Console\Helper\Table $table */
        $table = new Table($this->output);
        $table->setHeaders($this->headers);


        $c = $this->modx->newQuery('registry.db.modDbRegisterTopic');

        $collection = $this->modx->getIterator('registry.db.modDbRegisterTopic', $c);

        //$rows = array();
        /** @var \modDbRegisterTopic $object */
        foreach ($collection as $object) {
            $row = $object->toArray();
            $table->addRow($this->processRow($row));
        }

        $table->render();
    }

    protected function formatQueue($value)
    {
        return $this->renderObject('registry.db.modDbRegisterQueue', $value, 'name');
    }
}
