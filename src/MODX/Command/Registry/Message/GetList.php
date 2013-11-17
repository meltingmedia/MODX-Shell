<?php namespace MODX\Command\Registry\Message;

use MODX\Command\ProcessorCmd;

class GetList extends ProcessorCmd
{
    protected $headers = array(
        'id', 'topic', 'created', 'valid'
    );

    protected $name = 'registry:message:list';
    protected $description = 'List existing modRegistry messages';

    protected function process()
    {
        /** @var \Symfony\Component\Console\Helper\TableHelper $table */
        $table = $this->getApplication()->getHelperSet()->get('table');
        $table->setHeaders($this->headers);


        $c = $this->modx->newQuery('registry.db.modDbRegisterMessage');

        $collection = $this->modx->getIterator('registry.db.modDbRegisterMessage', $c);

        //$rows = array();
        /** @var \modDbRegisterTopic $object */
        foreach ($collection as $object) {
            $row = $object->toArray();
            $table->addRow($this->processRow($row));
        }

        $table->render($this->output);
    }

    protected function formatTopic($value)
    {
        return $this->renderObject('registry.db.modDbRegisterTopic', $value, 'name');
    }
}
