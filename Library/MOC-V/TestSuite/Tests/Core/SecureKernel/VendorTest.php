<?php
namespace MOC\V\TestSuite\Tests\Core\SecureKernel;

use MOC\V\Core\SecureKernel\Vendor\Vendor;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class VendorTest
 *
 * @package MOC\V\TestSuite\Tests\Core\SecureKernel
 */
class VendorTest extends AbstractTestCase
{

    public function testVendor()
    {

        /** @var \MOC\V\Core\SecureKernel\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockForAbstractClass('MOC\V\Core\SecureKernel\Component\Bridge\Bridge');

        $Vendor = new Vendor($MockBridge);

        $this->assertInstanceOf('MOC\V\Core\SecureKernel\Component\IBridgeInterface',
            $Vendor->getBridgeInterface());

        $this->assertInstanceOf('MOC\V\Core\SecureKernel\Component\IVendorInterface',
            $Vendor->setBridgeInterface($MockBridge));
    }
}
