<?php namespace MODX\Shell\Command\Session;

use MODX\Shell\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;

class Remove extends ProcessorCmd
{
    protected $name = 'session:remove';
    protected $description = 'Remove given modSession';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The session id'
            ),
        );
    }

    protected function process()
    {
        /** @var \modSession $session */
        $session = $this->modx->getObject('modSession', $this->argument('id'));
        if (!$session) {
            return $this->error('Session not found');
        }

        if (!$session->remove()) {
            return $this->error('Something went wrong while trying to delete the session');
        }

        $this->removeSessionIDS();

        $this->info('Session removed');
    }

    protected function removeSessionIDS()
    {
        $c = $this->modx->newQuery('modUserProfile');
        $c->where(array(
            'sessionid:!=' => '',
        ));

        $collection = $this->modx->getIterator('modUserProfile', $c);
        /** @var \modUserProfile $object */
        foreach ($collection as $object) {
            $object->set('sessionid', '');
            $object->save();
        }
    }
}
