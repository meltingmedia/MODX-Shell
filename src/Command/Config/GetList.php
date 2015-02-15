<?php namespace MODX\Shell\Command\Config;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Helper\Table;

/**
 * List known modx installations
 */
class GetList extends BaseCmd
{
    protected $name = 'config:list';
    protected $description = 'List registered modx instances';

    protected function process()
    {
        /** @var \MODX\Shell\Application $app */
        $app = $this->getApplication();
        $config = $app->getCurrentConfig();

        if (empty($config)) {
            return $this->error('No configuration file found');
        }
        $currentDir = $this->getApplication()->getCwd();


        /** @var \Symfony\Component\Console\Helper\Table $table */
        $table = new Table($this->output);
        $table->setHeaders(array(
            'Name', 'Path'/*, 'Is valid'*/
        ));

        $total = count($config);
        $length = strlen($total);
        $idx = 1;

        foreach ($config as $name => $data) {
            //$separator = ' - ';
            $separator = '   ';
            if ($this->isCurrent($currentDir, $data)) {
                //$separator = ' <info>></info> ';
                $separator = ' > ';
            } elseif (!file_exists($data['base_path']) || !file_exists($data['base_path'] . 'config.core.php')) {
                $separator = ' x ';
            }

            $row = array(
                $this->formatNumber($idx, $length) .$separator. $name,
                $data['base_path']
            );

            $table->addRow($row);
            $idx += 1;
        }

        $table->render($this->output);
    }

    /**
     * Check whether or not we are in declared MODX installation
     *
     * @param string $currentDir
     * @param array $data
     *
     * @return bool
     */
    protected function isCurrent($currentDir, array $data)
    {
        $current = false;
        $length = strlen($data['base_path']);
        $result = substr($currentDir, 0, $length);

        if ($result == $data['base_path']) {
            $current = true;
        }

        return $current;
    }

    /**
     *
     * @param int $number
     * @param int $digits
     *
     * @return string
     */
    protected function formatNumber($number, $digits)
    {
        return str_pad($number, $digits, '0', STR_PAD_LEFT);
    }
}
