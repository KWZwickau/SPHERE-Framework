<?php
namespace MOC\V\TestSuite\Tests\Core\AutoLoader;

use MOC\V\Core\AutoLoader\AutoLoader;
use MOC\V\Core\AutoLoader\Vendor\Vendor;
use MOC\V\TestSuite\AbstractTestCase;

class ModuleTest extends AbstractTestCase
{

    public function testModule()
    {

        /** @var \MOC\V\Core\AutoLoader\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockBuilder('MOC\V\Core\AutoLoader\Component\Bridge\Bridge')->getMock();
        $Vendor = new Vendor(new $MockBridge);
        $Module = new AutoLoader($Vendor);

        $this->assertInstanceOf('MOC\V\Core\AutoLoader\Component\IVendorInterface',
            $Module->getVendorInterface()
        );
        $this->assertInstanceOf('MOC\V\Core\AutoLoader\Component\IVendorInterface',
            $Module->setBridgeInterface($MockBridge)
        );
        $this->assertInstanceOf('MOC\V\Core\AutoLoader\Component\IBridgeInterface',
            $Module->getBridgeInterface()
        );

    }

    public function testStaticUniversalNamespaceAutoLoader()
    {

        $Loader = AutoLoader::getUniversalNamespaceAutoLoader(__NAMESPACE__, __DIR__);
        $this->assertInstanceOf('MOC\V\Core\AutoLoader\Component\IBridgeInterface', $Loader);
        $Loader->unregisterLoader();
    }

    public function testStaticMultitonNamespaceAutoLoader()
    {

        $Loader = AutoLoader::getMultitonNamespaceAutoLoader(__NAMESPACE__, __DIR__);
        $this->assertInstanceOf('MOC\V\Core\AutoLoader\Component\IBridgeInterface', $Loader);
        $Loader->unregisterLoader();
    }

    public function testStaticNamespaceAutoLoader()
    {

        $Loader = AutoLoader::getNamespaceAutoLoader(__NAMESPACE__, __DIR__);
        $this->assertInstanceOf('MOC\V\Core\AutoLoader\Component\IBridgeInterface', $Loader);
        $Loader->unregisterLoader();
    }
}
