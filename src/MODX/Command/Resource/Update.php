<?php namespace MODX\Command\Resource;

use MODX\Command\ProcessorCmd;

class Update extends ProcessorCmd
{
    protected $processor = 'resource/update';

    protected $name = 'resource:update';
    protected $description = 'Update a modResource';

    protected function processResponse(array $response = array())
    {
        $this->info('Should be done');
//        $o = $this->call('resource:get', array(
//            'properties' => array('id=487')
//        ));
//
//        print print_r($o, true);
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        /** @var \modResource $object */
        $object = $this->modx->getObject('modResource', $properties['id']);
        $properties = array_merge(
            $object->toArray(),
            $properties
        );
    }
}
