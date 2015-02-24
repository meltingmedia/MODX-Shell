<?php namespace Configuration;

use MODX\Shell\Configuration\Component;

class ComponentTest extends \PHPUnit_Framework_TestCase
{
    public function testGettingModxInstance()
    {
        $app = $this->prophesize('MODX\Shell\Application');
        $app->getMODX()->shouldBeCalled();

        $config = new Component($app->reveal());
    }

    public function testItemsInConstructors()
    {
        $app = $this->prophesize('MODX\Shell\Application');
        $items = array(
            'namespace' => array('service' => 'FakeService'),
        );

        $config = new Component($app->reveal(), $items);

        $this->assertEquals($items, $config->getAll(), 'Items passed in the constructor are available');
    }
}
