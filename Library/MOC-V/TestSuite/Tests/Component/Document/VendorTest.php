<?php
namespace MOC\V\TestSuite\Tests\Component\Document;

use MOC\V\Component\Document\Vendor\Vendor;

/**
 * Class VendorTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Document
 */
class VendorTest extends \PHPUnit_Framework_TestCase
{

    public function testVendor()
    {

        /** @var \MOC\V\Component\Document\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockForAbstractClass( 'MOC\V\Component\Document\Component\Bridge\Bridge' );

        $Vendor = new Vendor( $MockBridge );

        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IBridgeInterface',
            $Vendor->getBridgeInterface() );

        $this->assertInstanceOf( 'MOC\V\Component\Document\Component\IVendorInterface',
            $Vendor->setBridgeInterface( $MockBridge ) );
    }
}
