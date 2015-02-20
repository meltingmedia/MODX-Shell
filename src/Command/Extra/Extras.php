<?php namespace MODX\Shell\Command\Extra;

/**
 * A command to list third party commands
 */
class Extras extends Components
{
    const MODX = false;

    protected $name = 'extra:extras';
    protected $description = 'List third party commands';

    protected function process()
    {
        $extras = $this->getApplication()->extensions->getAll();
        if (empty($extras)) {
            $this->info('No third party commands registered');
            return;
        }
        $this->info(print_r($extras, true));

        $table = $this->prepareTable();
        foreach ($extras as $class) {
            $table->addRow($this->getTableRow($class));
        }
        $table->render();
    }
}
