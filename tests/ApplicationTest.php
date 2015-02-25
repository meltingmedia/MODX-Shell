<?php

use MODX\Shell\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
//        putenv('HOME='. dirname(__DIR__));
//        echo getenv('HOME');
        $app = new Application;

        $this->assertInstanceOf('MODX\Shell\Configuration\Instance', $app->instances, 'Instances are configured in the constructor');
        $this->assertInstanceOf('MODX\Shell\Configuration\Extension', $app->extensions, 'Extensions are configured in the constructor');
        $this->assertInstanceOf('MODX\Shell\Configuration\Component', $app->components, 'components are configured in the constructor');

        $version = file_get_contents(dirname(__DIR__) . '/VERSION');

        $this->assertEquals($version, $app->getVersion(), 'Shell version matches file version');
        $this->assertEquals('MODX Shell', $app->getName(), 'Shell is named MODX Shell');
    }

    public function testInstanceAsArgument()
    {
        //Application::
        //$app = new Application;
    }
    public function _testInstanceArgumentInConstructor()
    {
        $app = $this->prophesize('MODX\Shell\Application');
        $app->handleInstanceAsArgument()->shouldBeCalled();

        $app->reveal();
    }
}
