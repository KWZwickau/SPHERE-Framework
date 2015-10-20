<?php
namespace MOC\V\TestSuite\Tests\Core\SecureKernel;

use MOC\V\Core\SecureKernel\SecureKernel;
use MOC\V\Core\SecureKernel\Vendor\Vendor;
use MOC\V\TestSuite\AbstractTestCase;

/**
 * Class ModuleTest
 *
 * @package MOC\V\TestSuite\Tests\Core\SecureKernel
 */
class ModuleTest extends AbstractTestCase
{

    public function testModule()
    {

        /** @var \MOC\V\Core\SecureKernel\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockBuilder('MOC\V\Core\SecureKernel\Component\Bridge\Bridge')->getMock();
        $Vendor = new Vendor(new $MockBridge);
        $Module = new SecureKernel($Vendor);

        $this->assertInstanceOf('MOC\V\Core\SecureKernel\Component\IVendorInterface',
            $Module->getVendorInterface()
        );
        $this->assertInstanceOf('MOC\V\Core\SecureKernel\Component\IVendorInterface',
            $Module->setBridgeInterface($MockBridge)
        );
        $this->assertInstanceOf('MOC\V\Core\SecureKernel\Component\IBridgeInterface',
            $Module->getBridgeInterface()
        );
    }

    public function testStaticPhpSecLibSFTP()
    {

        $SFTP = $this->invokeClassMethod('MOC\V\Core\SecureKernel\SecureKernel', 'getPhpSecLibSFTP');
        $this->assertInstanceOf('MOC\V\Core\SecureKernel\Component\IBridgeInterface', $SFTP);
    }

    public function testStaticSFTP()
    {

        $SFTP = SecureKernel::getSFTP();
        $this->assertInstanceOf('MOC\V\Core\SecureKernel\Component\IBridgeInterface', $SFTP);
    }

}
