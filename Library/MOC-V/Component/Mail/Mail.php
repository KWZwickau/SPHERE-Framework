<?php
namespace MOC\V\Component\Mail;

use MOC\V\Component\Mail\Component\Bridge\Repository\EdenPhpImap;
use MOC\V\Component\Mail\Component\Bridge\Repository\EdenPhpPop3;
use MOC\V\Component\Mail\Component\Bridge\Repository\EdenPhpSmtp;
use MOC\V\Component\Mail\Component\IBridgeInterface;
use MOC\V\Component\Mail\Component\IVendorInterface;
use MOC\V\Component\Mail\Exception\MailException;
use MOC\V\Component\Mail\Vendor\Vendor;

/**
 * Class Mail
 *
 * @package MOC\V\Component\Mail
 */
class Mail implements IVendorInterface
{

    const MAIL_TYPE_POP3 = 0;
    const MAIL_TYPE_SMTP = 1;
    const MAIL_TYPE_IMAP = 2;

    /** @var IVendorInterface $VendorInterface */
    private $VendorInterface = null;

    /**
     * @param IVendorInterface $VendorInterface
     */
    public function __construct(IVendorInterface $VendorInterface)
    {

        $this->setVendorInterface($VendorInterface);
    }

    /**
     * @param int $Type
     *
     * @return IBridgeInterface
     * @throws MailException
     */
    public static function getMail($Type = self::MAIL_TYPE_SMTP)
    {

        switch ($Type) {
            case self::MAIL_TYPE_POP3: {
                return self::getSmtpMail();
            }
            case self::MAIL_TYPE_SMTP: {
                return self::getSmtpMail();
            }
            case self::MAIL_TYPE_IMAP: {
                return self::getSmtpMail();
            }
            default:
                throw new MailException();
        }
    }

    /**
     * @return IBridgeInterface
     */
    public static function getSmtpMail()
    {

        $Mail = new Mail(
            new Vendor(
                new EdenPhpSmtp()
            )
        );

        return $Mail->getBridgeInterface();
    }

    /**
     * @return IBridgeInterface
     */
    public function getBridgeInterface()
    {

        return $this->VendorInterface->getBridgeInterface();
    }

    /**
     * @return IBridgeInterface
     */
    public static function getPop3Mail()
    {

        $Mail = new Mail(
            new Vendor(
                new EdenPhpPop3()
            )
        );

        return $Mail->getBridgeInterface();
    }

    /**
     * @return IBridgeInterface
     */
    public static function getImapMail()
    {

        $Mail = new Mail(
            new Vendor(
                new EdenPhpImap()
            )
        );

        return $Mail->getBridgeInterface();
    }

    /**
     * @return IVendorInterface
     */
    public function getVendorInterface()
    {

        return $this->VendorInterface;
    }

    /**
     * @param IVendorInterface $VendorInterface
     *
     * @return IVendorInterface
     */
    public function setVendorInterface(IVendorInterface $VendorInterface)
    {

        $this->VendorInterface = $VendorInterface;
        return $this;
    }

    /**
     * @param IBridgeInterface $BridgeInterface
     *
     * @return IBridgeInterface
     */
    public function setBridgeInterface(IBridgeInterface $BridgeInterface)
    {

        return $this->VendorInterface->setBridgeInterface($BridgeInterface);
    }
}
