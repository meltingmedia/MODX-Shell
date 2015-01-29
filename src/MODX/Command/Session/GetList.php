<?php namespace MODX\Command\Session;

use MODX\Command\ProcessorCmd;
use Symfony\Component\Console\Helper\Table;

class GetList extends ProcessorCmd
{
    protected $headers = array(
        'id', 'access', 'user'
    );

    protected $name = 'session:list';
    protected $description = 'List existing modSession';

    protected function process()
    {
        //$this->handleColumns();

        /** @var \Symfony\Component\Console\Helper\Table $table */
        $table = new Table($this->output);
        $table->setHeaders($this->headers);


        $c = $this->modx->newQuery('modSession');

        $collection = $this->modx->getIterator('modSession', $c);

        //$rows = array();
        /** @var \modDbRegisterTopic $object */
        foreach ($collection as $object) {
            $row = $object->toArray();
            $row['user'] = $this->getUser($row['id']);
//            if ($row['data'] != '') {
//                $row['a'] = unserialize($row['data']);
//            }
//            print_r($row);

            $table->addRow($this->processRow($row));
        }

        $table->render();
    }

    protected function getUser($session)
    {
        $value = 'Anonymous (?)';

        $c = $this->modx->newQuery('modUserProfile');
        $c->where(array(
            'sessionid' => $session,
        ));

        if ($this->modx->getCount('modUserProfile', $c) > 0) {
            $c->limit(1);
            /** @var \modUserProfile $profile */
            $profile = $this->modx->getObject('modUserProfile', $c);
            /** @var \modUser $user */
            $user = $profile->getOne('User');

            $value = $user->get('username');
        }

        return $value;
    }
}
