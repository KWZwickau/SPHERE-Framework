<?php
namespace MOC\V\TestSuite\Tests\Component\Mail;

use MOC\V\Component\Mail\Mail;
use MOC\V\Component\Mail\Vendor\Vendor;

/**
 * Class ModuleTest
 *
 * @package MOC\V\TestSuite\Tests\Component\Mail
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{

    public function testModule()
    {

        /** @var \MOC\V\Component\Mail\Component\Bridge\Bridge $MockBridge */
        $MockBridge = $this->getMockBuilder( 'MOC\V\Component\Mail\Component\Bridge\Bridge' )->getMock();
        $Vendor = new Vendor( new $MockBridge );
        $Module = new Mail( $Vendor );

        $this->assertInstanceOf( 'MOC\V\Component\Mail\Component\IVendorInterface',
            $Module->getVendorInterface()
        );
        $this->assertInstanceOf( 'MOC\V\Component\Mail\Component\IVendorInterface',
            $Module->setBridgeInterface( $MockBridge )
        );
        $this->assertInstanceOf( 'MOC\V\Component\Mail\Component\IBridgeInterface',
            $Module->getBridgeInterface()
        );

    }

    public function testStaticMail()
    {

        $Mail = Mail::getMail( Mail::MAIL_TYPE_SMTP );
        $this->assertInstanceOf( 'MOC\V\Component\Mail\Component\IBridgeInterface', $Mail );
        $Mail = Mail::getMail( Mail::MAIL_TYPE_POP3 );
        $this->assertInstanceOf( 'MOC\V\Component\Mail\Component\IBridgeInterface', $Mail );
        $Mail = Mail::getMail( Mail::MAIL_TYPE_IMAP );
        $this->assertInstanceOf( 'MOC\V\Component\Mail\Component\IBridgeInterface', $Mail );
    }

    public function testStaticSmtpMail()
    {

        $Mail = Mail::getSmtpMail();
        $this->assertInstanceOf( 'MOC\V\Component\Mail\Component\IBridgeInterface', $Mail );
    }

    public function testStaticPop3Mail()
    {

        $Mail = Mail::getPop3Mail();
        $this->assertInstanceOf( 'MOC\V\Component\Mail\Component\IBridgeInterface', $Mail );
    }

    public function testStaticImapMail()
    {

        $Mail = Mail::getImapMail();
        $this->assertInstanceOf( 'MOC\V\Component\Mail\Component\IBridgeInterface', $Mail );
    }
}
