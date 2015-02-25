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

    /**
     * @var array $items
     *
     * @dataProvider getData
     */
    public function testItemsInConstructors($items)
    {
        $app = $this->prophesize('MODX\Shell\Application');
        $config = new Component($app->reveal(), $items);

        $this->assertEquals($items, $config->getAll(), 'Items passed in the constructor are available');
    }

    /**
     * @var array $items
     *
     * @dataProvider getData
     */
    public function testGettingItem($items)
    {
        $app = $this->prophesize('MODX\Shell\Application');
        $config = new Component($app->reveal(), $items);

        $this->assertEquals($items['namespace'], $config->get('namespace'), 'Items are retrievable');
    }

    public function testSaveShouldFailIfNoModx()
    {
        $app = $this->prophesize('MODX\Shell\Application');
        $config = new Component($app->reveal());

        $this->assertFalse($config->save(), 'Saving components configuration is not possible with no modX instance');
    }

    public function testItemsShouldBeEmptyIfNoModx()
    {
        $app = $this->prophesize('MODX\Shell\Application');
        $config = new Component($app->reveal());

        $this->assertEmpty($config->getAll(), 'Items are empty when no modX instance is available');
    }

    /**
     * @param array $items
     *
     * @dataProvider getData
     */
    public function testItemsLoadedFromModx($items)
    {
        $json = json_encode($items);

        $modx = $this->getMock('modX', array('getOption', 'fromJSON'));
        $modx->expects($this->once())->method('getOption')->with('console_commands', null, '{}')->will($this->returnValue($json));
        $modx->expects($this->once())->method('fromJSON')->with($json)->will($this->returnValue($items));

        $app = $this->prophesize('MODX\Shell\Application');
        $app->getMODX()->willReturn($modx);

        $config = new Component($app->reveal());

        $this->assertEquals($items, $config->getAll(), 'Items retrieved from modX should be available');
    }

    public function getData()
    {
        return array(
            array(
                array(
                    'namespace' => array(
                        'service' => 'FakeService',
                        'params' => array(
                            'key' => 'value'
                        ),
                    ),
                ),
            ),
        );
    }
}
