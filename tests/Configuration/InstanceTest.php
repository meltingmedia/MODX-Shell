<?php namespace Configuration;

use MODX\Shell\Configuration\Instance;

class InstanceTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $items = array(
            'InstanceName' => array(
                'base_path' => '/some/fake/path/',
            ),
        );
        $config = new Instance($items);

        $this->assertEquals($items, $config->getAll(), 'Items passed in the constructor should be available using getAll()');
    }

    public function testFindFromPath()
    {
        $items = array(
            'InstanceName' => array(
                'base_path' => '/some/fake/path/',
            ),
            'NewInstanceName' => array(
                'base_path' => getcwd(),
            ),
        );
        $config = new Instance($items);

        $this->assertEquals('InstanceName', $config->findFormPath('/some/fake/path/'), 'We are able to find an instance name from a given path.');
        $this->assertEquals('InstanceName', $config->findFormPath('/some/fake/path'), 'We are able to find an instance name from a given path minus its trailing slash.');
        $this->assertEquals('InstanceName', $config->findFormPath('/some/fake/path/sub/folder'), 'We are able to find an instance name from a given path nested in base_path.');
        $this->assertNull($config->findFormPath('/not/registered/path/'), 'Searching for a not registered path should return null');

        $this->assertEquals('NewInstanceName', $config->current(), 'We can find the current instance using current() method');
    }

    public function testCurrentConfig()
    {
        $items = array(
            'NewInstanceName' => array(
                'base_path' => getcwd(),
            ),
        );
        $config = new Instance($items);

        $this->assertEquals($items['NewInstanceName'], $config->getCurrentConfig(), 'We can get the full current instance configuration using getCurrentConfig');
        $this->assertEquals($items['NewInstanceName']['base_path'], $config->getCurrentConfig('base_path'), 'We can get a single configuration item/index from the current instance using getCurrentConfig');
    }

    public function _testLoad()
    {
        // @TODO
    }

    public function _testSave()
    {
        // @TODO
    }
}
