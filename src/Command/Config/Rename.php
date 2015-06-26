<?php namespace MODX\Shell\Command\Config;

use MODX\Shell\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

class Rename extends BaseCmd
{
    protected $name = 'config:rename';
    protected $description = 'Rename an instance';

    protected function getArguments()
    {
        return array(
            array(
                'old-name',
                InputArgument::REQUIRED,
                'The current instance name you want to rename'
            ),
            array(
                'new-name',
                InputArgument::REQUIRED,
                'Your new desired instance name',
            ),
        );
    }


    protected function process()
    {
        $old = $this->argument('old-name');
        /** @var string $new */
        $new = $this->argument('new-name');

        $config = $this->getApplication()->instances;
        $exists = $config->get($old);
        if (!$exists) {
            return $this->error('No entry found with that name : '. $old);
        }

        $data = array(
            $new => array_merge($exists, array(
                'class' => $new
            ))
        );

        $config->set($new, $data[$new]);
        $config->remove($old);

        $default = $config->getDefaultInstance();
        if ($default && $default === $old) {
            // We are renaming the configured default instance, let's update it
            $config->setDefaultInstance($new);
        }

        $saved = $config->save();
        if (!$saved) {
            return $this->error('Something went wrong while updating the config');
        }

        return $this->info('Config file updated');
    }
}
