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

    public function testSave()
    {
        $modx = $this->getMock('modX', array('getObject', 'newObject', 'getCacheManager', 'fromJSON', 'getOption', 'toJSON'));

        $app = $this->prophesize('MODX\Shell\Application');
        $app->getMODX()->willReturn($modx);

        $config = new Component($app->reveal());
        $config->set('dummy', array('service' => 'Fake'));

        $setting = $this->getMock('modSystemSetting', array('set', 'save'));
        $setting->expects($this->once())->method('save')->will($this->returnValue($setting));

        $modx->expects($this->once())->method('getObject')->with('modSystemSetting', array(
            'key' => 'console_commands'
        ))->will($this->returnValue($setting));

        $cache = $this->getMock('modCacheManager', array('refresh'));
        $cache->expects($this->once())->method('refresh')->will($this->returnValue(true));

        $modx->expects($this->once())->method('getCacheManager')->will($this->returnValue($cache));

        $this->assertTrue($config->save(), 'Saving components services is possible');
    }

    public function testSaveShouldCreateSystemSetting()
    {
        $modx = $this->getMock('modX', array('getObject', 'newObject', 'getCacheManager', 'fromJSON', 'getOption', 'toJSON'));

        $app = $this->prophesize('MODX\Shell\Application');
        $app->getMODX()->willReturn($modx);

        $config = new Component($app->reveal());
        $config->set('dummy', array('service' => 'Fake'));

        $setting = $this->getMock('modSystemSetting', array('set', 'save'));
        $setting->expects($this->once())->method('save')->will($this->returnValue($setting));

        $modx->expects($this->once())->method('getObject')->with('modSystemSetting', array(
            'key' => 'console_commands'
        ))->will($this->returnValue(null));

        $modx->expects($this->once())->method('newObject')->with('modSystemSetting')->will($this->returnValue($setting));

        $cache = $this->getMock('modCacheManager', array('refresh'));
        $cache->expects($this->once())->method('refresh')->will($this->returnValue(true));

        $modx->expects($this->once())->method('getCacheManager')->will($this->returnValue($cache));

        $this->assertTrue($config->save(), 'Saving components services creates the appropriate system setting');
    }

    public function testSaveShouldFail()
    {
        $modx = $this->getMock('modX', array('getObject', 'newObject', 'getCacheManager', 'fromJSON', 'getOption', 'toJSON'));

        $app = $this->prophesize('MODX\Shell\Application');
        $app->getMODX()->willReturn($modx);

        $config = new Component($app->reveal());
        $config->set('dummy', array('service' => 'Fake'));

        $setting = $this->getMock('modSystemSetting', array('set', 'save'));
        $setting->expects($this->once())->method('save')->will($this->returnValue(false));

        $modx->expects($this->once())->method('getObject')->with('modSystemSetting', array(
            'key' => 'console_commands'
        ))->will($this->returnValue($setting));


        $this->assertFalse($config->save(), 'Failing to save system setting should not trigger a cache refresh');
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
