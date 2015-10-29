<?php
namespace MOC\V\TestSuite\Tests\Core\AutoLoader;

use MOC\V\Core\AutoLoader\Vendor\Vendor;
use MOC\V\TestSuite\AbstractTestCase;

class VendorTest extends AbstractTestCase
{

    public function testVendor()
    {

        /** @var \MOC\V\Core\AutoLoader\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockForAbstractClass('MOC\V\Core\AutoLoader\Component\Bridge\Bridge');

        $Vendor = new Vendor($MockBridge);

        $this->assertInstanceOf('MOC\V\Core\AutoLoader\Component\IBridgeInterface',
            $Vendor->getBridgeInterface());

        $this->assertInstanceOf('MOC\V\Core\AutoLoader\Component\IVendorInterface',
            $Vendor->setBridgeInterface($MockBridge));
    }
}
