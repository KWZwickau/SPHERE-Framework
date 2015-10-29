<?php
namespace MOC\V\TestSuite\Tests\Component\Captcha;

use MOC\V\Component\Captcha\Vendor\Vendor;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class VendorTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Captcha
 */
class VendorTest extends AbstractTestCase
{

    public function testVendor()
    {

        /** @var \MOC\V\Component\Captcha\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockForAbstractClass('MOC\V\Component\Captcha\Component\Bridge\Bridge');

        $Vendor = new Vendor($MockBridge);

        $this->assertInstanceOf('MOC\V\Component\Captcha\Component\IBridgeInterface',
            $Vendor->getBridgeInterface());

        $this->assertInstanceOf('MOC\V\Component\Captcha\Component\IVendorInterface',
            $Vendor->setBridgeInterface($MockBridge));
    }
}
