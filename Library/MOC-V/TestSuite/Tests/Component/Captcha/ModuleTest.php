<?php
namespace MOC\V\TestSuite\Tests\Component\Captcha;

use MOC\V\Component\Captcha\Captcha;
use MOC\V\Component\Captcha\Vendor\Vendor;

/**
 * Class ModuleTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Captcha
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{

    public function testModule()
    {

        /** @var \MOC\V\Component\Captcha\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockBuilder( 'MOC\V\Component\Captcha\Component\Bridge\Bridge' )->getMock();
        $Vendor = new Vendor( new $MockBridge );
        $Module = new Captcha( $Vendor );

        $this->assertInstanceOf( 'MOC\V\Component\Captcha\Component\IVendorInterface',
            $Module->getVendorInterface()
        );
        $this->assertInstanceOf( 'MOC\V\Component\Captcha\Component\IVendorInterface',
            $Module->setBridgeInterface( $MockBridge )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Captcha\Component\IBridgeInterface',
            $Module->getBridgeInterface()
        );

    }

}
