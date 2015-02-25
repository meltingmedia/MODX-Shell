<?php namespace Configuration;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    public function getMocked()
    {
        return $this->getMockForAbstractClass('MODX\Shell\Configuration\Base');
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @dataProvider getData
     */
    public function testSetterAndGetter($key, $value)
    {
        $mock = $this->getMocked();
        $mock->set($key, $value);

        $this->assertEquals($value, $mock->get($key), 'Retrieving item is possible');
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @dataProvider getData
     */
    public function testRemove($key, $value)
    {
        $mock = $this->getMocked();
        $mock->set($key, $value);
        $mock->remove($key);

        $this->assertNull($mock->get($key), 'It is possible to remove an item');
    }

    public function getData()
    {
        return array(
            array('key', 'value'),
            array('array', array('a' => 'b', 'c' => 'd')),
        );
    }
}
