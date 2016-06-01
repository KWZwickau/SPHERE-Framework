<?php
namespace MOC\V\TestSuite\Tests\Core\FileSystem;

use MOC\V\Core\FileSystem\Vendor\Vendor;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class VendorTest
 *
 * @package MOC\V\TestSuite\Tests\Core\FileSystem
 */
class VendorTest extends AbstractTestCase
{

    public function testVendor()
    {

        /** @var \MOC\V\Core\FileSystem\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockForAbstractClass('MOC\V\Core\FileSystem\Component\Bridge\Bridge');

        $Vendor = new Vendor($MockBridge);

        $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\IBridgeInterface',
            $Vendor->getBridgeInterface());

        $this->assertInstanceOf('MOC\V\Core\FileSystem\Component\IVendorInterface',
            $Vendor->setBridgeInterface($MockBridge));
    }
}
