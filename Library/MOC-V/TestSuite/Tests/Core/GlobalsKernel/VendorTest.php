<?php
namespace MOC\V\TestSuite\Tests\Core\GlobalsKernel;

use MOC\V\Core\GlobalsKernel\Vendor\Vendor;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class VendorTest
 *
 * @package MOC\V\TestSuite\Tests\Core\GlobalsKernel
 */
class VendorTest extends AbstractTestCase
{

    public function testVendor()
    {

        /** @var \MOC\V\Core\GlobalsKernel\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockForAbstractClass('MOC\V\Core\GlobalsKernel\Component\Bridge\Bridge');

        $Vendor = new Vendor($MockBridge);

        $this->assertInstanceOf('MOC\V\Core\GlobalsKernel\Component\IBridgeInterface',
            $Vendor->getBridgeInterface());

        $this->assertInstanceOf('MOC\V\Core\GlobalsKernel\Component\IVendorInterface',
            $Vendor->setBridgeInterface($MockBridge));
    }
}
