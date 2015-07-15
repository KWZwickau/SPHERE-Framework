<?php
namespace MOC\V\TestSuite\Tests\Component\Documentation;

use MOC\V\Component\Documentation\Vendor\Vendor;

/**
 * Class VendorTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Documentation
 */
class VendorTest extends \PHPUnit_Framework_TestCase
{

    public function testVendor()
    {

        /** @var \MOC\V\Component\Documentation\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockForAbstractClass( 'MOC\V\Component\Documentation\Component\Bridge\Bridge' );

        $Vendor = new Vendor( $MockBridge );

        $this->assertInstanceOf( 'MOC\V\Component\Documentation\Component\IBridgeInterface',
            $Vendor->getBridgeInterface() );

        $this->assertInstanceOf( 'MOC\V\Component\Documentation\Component\IVendorInterface',
            $Vendor->setBridgeInterface( $MockBridge ) );
    }
}
