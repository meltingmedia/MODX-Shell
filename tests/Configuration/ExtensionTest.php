<?php namespace Configuration;

use MODX\Shell\Configuration\Extension;

class ExtensionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param array $items
     *
     * @dataProvider getData
     */
    public function testConstructor($items)
    {
        $config = new Extension($items);

        $this->assertNotEmpty($config->getAll(), 'Classes passed in constructor should be set');
        $this->assertEquals($items, $config->getAll(), 'Classes passed in constructor should match');
    }

    /**
     * @param array $items
     *
     * @dataProvider getData
     */
    public function testGetter($items)
    {
        $config = new Extension($items);

        $this->assertEquals('\Another\Command\Class', $config->get('\Another\Command\Class'), 'Getting a valid class name should return its class name');
        $this->assertNull($config->get('\Fake\Class'), 'Getting an invalid class name should return null');
    }

    /**
     * @param array $items
     *
     * @dataProvider getData
     */
    public function testSetter($items)
    {
        $config = new Extension($items);

        $config->set('\Another\Command\Class');
        $this->assertEquals($items, $config->getAll(), 'Trying to add an already added class should not change the items');

        $config->remove('\Another\Command\Class');
        $this->assertEquals(1, count($config->getAll()), 'Removing a class name from the items is possible');
        $this->assertNull($config->get('\Another\Command\Class'));

        $class = '\\Some\\Class\\Name';
        $config->set($class);
        $this->assertEquals($class, $config->get($class), 'Adding a new command class is possible');
    }

    /**
     * @param array $items
     *
     * @dataProvider getData
     */
    public function testFormat($items)
    {
        $config = new Extension($items);
        $formatted = $config->formatData();
        $formatted = str_replace('<?php', '', $formatted);
        sort($items);

        $this->assertEquals($items, eval($formatted), 'Items are correctly formatted for file storage');
    }

    public function _testSave()
    {
        // @TODO
    }

    public function _testLoad()
    {
        // @TODO
    }

    public function getData()
    {
        return array(
            array(
                array(
                    '\Some\Command\Class',
                    '\Another\Command\Class'
                ),
            ),
        );
    }
}
