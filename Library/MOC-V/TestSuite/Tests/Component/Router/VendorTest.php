<?php
namespace MOC\V\TestSuite\Tests\Component\Router;

use MOC\V\Component\Router\Vendor\Vendor;

/**
 * Class VendorTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Router
 */
class VendorTest extends \PHPUnit_Framework_TestCase
{

    public function testVendor()
    {

        /** @var \MOC\V\Component\Router\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockForAbstractClass( 'MOC\V\Component\Router\Component\Bridge\Bridge' );

        $Vendor = new Vendor( $MockBridge );

        $this->assertInstanceOf( 'MOC\V\Component\Router\Component\IBridgeInterface',
            $Vendor->getBridgeInterface() );

        $this->assertInstanceOf( 'MOC\V\Component\Router\Component\IVendorInterface',
            $Vendor->setBridgeInterface( $MockBridge ) );
    }
}
