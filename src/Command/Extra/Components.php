<?php namespace MODX\Shell\Command\Extra;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Helper\Table;

/**
 * A command to list commands added by third party components
 */
class Components extends BaseCmd
{
    const MODX = true;

    protected $name = 'extra:components';
    protected $description = 'List commands added by third party components';

    protected function process()
    {
        $components = $this->getApplication()->components->getAll();
        if (empty($components)) {
            $this->info('No additional commands registered by components');
            return;
        }
        foreach ($components as $ns => $data) {
            $service = $this->getApplication()->getExtraService($data);
            if ($service && method_exists($service, 'getCommands')) {
                $table = $this->prepareTable();

                $this->info($ns);
                $classes = $service->getCommands();
                foreach ($classes as $class) {
                    $table->addRow($this->getTableRow($class));
                }
                $table->render();
            } else {
                $this->error($ns . ' is registered but does not implement a "getCommand" method.');
            }
        }
    }

    /**
     * Prepare a table instance to be rendered
     *
     * @return Table
     */
    protected function prepareTable()
    {
        $table = new Table($this->output);
        $table->setHeaders(array(
            'Name', 'Description', 'Requires modX'
        ));

        return $table;
    }

    /**
     * Get the command details
     *
     * @param string|BaseCmd $class
     *
     * @return array
     */
    protected function getTableRow($class)
    {
        $reflection = new \ReflectionClass($class);
        $name = $reflection->getProperty('name');
        $name->setAccessible(true);
        $desc = $reflection->getProperty('description');
        $desc->setAccessible(true);

        $instance = new $class;

        return array(
            'name' => $name->getValue($instance),
            'description' => $desc->getValue($instance),
            'modx' => $class::MODX ? 'Yes' : 'No'
        );
    }
}
