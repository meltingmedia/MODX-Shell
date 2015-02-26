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
        $config = $this->getApplication()->instances->getAll();

        if (empty($config)) {
            return $this->error('No entries found');
        }

        $table = new Table($this->output);
        //$table->setStyle('compact');
        $table->setHeaders(array(
            'Name', 'Path', 'Revo version'
        ));
        $idx = 1;
        $current = $this->getApplication()->instances->current();

        foreach ($config as $name => $data) {
            $version = '<error>Unknown</error>';
            $color = '';
            if (isset($data['core_path'])) {
                $version = $this->getRemoteMODXVersion($data['core_path']);
                if ($name === $current) {
                    $color = 'info';
                }
            } elseif (!file_exists($data['base_path']) || !file_exists($data['base_path'] . 'config.core.php')) {
                $color = 'error';
            }

            $row = array(
                $this->renderColors($name, (empty($color) ? 'comment' : $color)),
                $this->renderColors($data['base_path'], $color),
                $this->renderColors($version, $color)
            );

            $table->addRow($row);
            $idx += 1;
        }

        $table->render($this->output);
    }

    /**
     * Convenient method to render a colored table cell
     *
     * @param string $data
     * @param string $color
     *
     * @return string
     */
    protected function renderColors($data, $color = '')
    {
        if (empty($color)) {
            return $data;
        }

        return "<{$color}>{$data}</{$color}>";
    }

    /**
     * Try to read modX version from a not instantiated version
     *
     * @param string $path Revolution core path
     *
     * @return string
     */
    protected function getRemoteMODXVersion($path)
    {
        $file = $path . 'docs/version.inc.php';
        if (!file_exists($file)) {
            return 'Unknown';
        }
        $v = include $file;

        return $v['full_version'];
    }
}
