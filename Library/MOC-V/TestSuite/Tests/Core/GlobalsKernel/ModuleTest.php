<?php
namespace MOC\V\TestSuite\Tests\Core\GlobalsKernel;

use MOC\V\Core\GlobalsKernel\GlobalsKernel;
use MOC\V\Core\GlobalsKernel\Vendor\Vendor;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ModuleTest
 *
 * @package MOC\V\TestSuite\Tests\Core\GlobalsKernel
 */
class ModuleTest extends AbstractTestCase
{

    public function testModule()
    {

        /** @var \MOC\V\Core\GlobalsKernel\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockBuilder('MOC\V\Core\GlobalsKernel\Component\Bridge\Bridge')->getMock();
        $Vendor = new Vendor(new $MockBridge);
        $Module = new GlobalsKernel($Vendor);

        $this->assertInstanceOf('MOC\V\Core\GlobalsKernel\Component\IVendorInterface',
            $Module->getVendorInterface()
        );
        $this->assertInstanceOf('MOC\V\Core\GlobalsKernel\Component\IVendorInterface',
            $Module->setBridgeInterface($MockBridge)
        );
        $this->assertInstanceOf('MOC\V\Core\GlobalsKernel\Component\IBridgeInterface',
            $Module->getBridgeInterface()
        );
    }

    public function testStaticUniversalGlobals()
    {

        $Globals = $this->invokeClassMethod('MOC\V\Core\GlobalsKernel\GlobalsKernel', 'getUniversalGlobals');
        $this->assertInstanceOf('MOC\V\Core\GlobalsKernel\Component\IBridgeInterface', $Globals);
    }

    public function testStaticGlobals()
    {

        $Globals = GlobalsKernel::getGlobals();
        $this->assertInstanceOf('MOC\V\Core\GlobalsKernel\Component\IBridgeInterface', $Globals);
    }

}
