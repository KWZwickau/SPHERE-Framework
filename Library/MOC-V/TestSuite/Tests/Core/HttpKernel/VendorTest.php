<?php
namespace MOC\V\TestSuite\Tests\Core\HttpKernel;

use MOC\V\Core\HttpKernel\Vendor\Vendor;

/**
 * Class VendorTest
 *
 * @package MOC\V\TestSuite\Tests\Core\HttpKernel
 */
class VendorTest extends \PHPUnit_Framework_TestCase
{

    public function testVendor()
    {

        /** @var \MOC\V\Core\HttpKernel\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockForAbstractClass( 'MOC\V\Core\HttpKernel\Component\Bridge\Bridge' );

        $Vendor = new Vendor( $MockBridge );

        $this->assertInstanceOf( 'MOC\V\Core\HttpKernel\Component\IBridgeInterface',
            $Vendor->getBridgeInterface() );

        $this->assertInstanceOf( 'MOC\V\Core\HttpKernel\Component\IVendorInterface',
            $Vendor->setBridgeInterface( $MockBridge ) );
    }
}
