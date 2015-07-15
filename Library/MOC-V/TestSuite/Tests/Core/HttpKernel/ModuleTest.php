<?php
namespace MOC\V\TestSuite\Tests\Core\HttpKernel;

use MOC\V\Core\HttpKernel\HttpKernel;
use MOC\V\Core\HttpKernel\Vendor\Vendor;

/**
 * Class ModuleTest
 *
 * @package MOC\V\TestSuite\Tests\Core\HttpKernel
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{

    public function testModule()
    {

        /** @var \MOC\V\Core\HttpKernel\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockBuilder( 'MOC\V\Core\HttpKernel\Component\Bridge\Bridge' )->getMock();
        $Vendor = new Vendor( new $MockBridge );
        $Module = new HttpKernel( $Vendor );

        $this->assertInstanceOf( 'MOC\V\Core\HttpKernel\Component\IVendorInterface',
            $Module->getVendorInterface()
        );
        $this->assertInstanceOf( 'MOC\V\Core\HttpKernel\Component\IVendorInterface',
            $Module->setBridgeInterface( $MockBridge )
        );
        $this->assertInstanceOf( 'MOC\V\Core\HttpKernel\Component\IBridgeInterface',
            $Module->getBridgeInterface()
        );
    }

    public function testStaticUniversalRequest()
    {

        $Request = HttpKernel::getUniversalRequest();
        $this->assertInstanceOf( 'MOC\V\Core\HttpKernel\Component\IBridgeInterface', $Request );
    }

    public function testStaticRequest()
    {

        $Request = HttpKernel::getRequest();
        $this->assertInstanceOf( 'MOC\V\Core\HttpKernel\Component\IBridgeInterface', $Request );
    }

}
