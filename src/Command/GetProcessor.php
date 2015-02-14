<?php namespace MODX\Shell\Command;

/**
 * Command to help display an object as a table (mostly fot get* processors)
 */
abstract class GetProcessor extends ProcessorCmd
{
    protected $headers = array(
        'id', 'name', 'description'
    );

    protected function processResponse(array $results = array())
    {
        $object = $results['object'];

        /** @var \Symfony\Component\Console\Helper\TableHelper $table */
        $table = $this->getApplication()->getHelperSet()->get('table');
        $table->setHeaders($this->headers);
        $table->addRow($this->processRow($object));

        $table->render($this->output);
    }
}
