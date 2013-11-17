<?php namespace MODX\Command\Session;

use MODX\Command\ProcessorCmd;

class FlushSession extends ProcessorCmd
{
    protected $name = 'session:flush';
    protected $description = 'Flush sessions';

    protected function init()
    {
        $init = parent::init();
        $canFlush = $this->modx->hasPermission('flush_sessions');
        if (!$canFlush) {
            $init = false;
        }

        return $init;
    }

    protected function process()
    {
        if ($this->modx->getOption('session_handler_class',null,'modSessionHandler') == 'modSessionHandler') {
            if (!$this->flushSessions()) {
                return $this->error($this->modx->lexicon('flush_sessions_err'));
            }
        } else {
            return $this->error($this->modx->lexicon('flush_sessions_not_supported'));
        }

        $this->info('Sessions flushed');
    }

    protected function flushSessions()
    {
        $flushed = true;
        $sessionTable = $this->modx->getTableName('modSession');
        if ($this->modx->query("TRUNCATE TABLE {$sessionTable}") == false) {
            $flushed = false;
        }

        if ($flushed) {
            $this->removeSessionIDS();
        }

        return $flushed;
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
