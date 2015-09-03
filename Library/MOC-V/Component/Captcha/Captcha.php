<?php
namespace MOC\V\Component\Captcha;

use MOC\V\Component\Captcha\Component\Bridge\Repository\SimplePhpCaptcha;
use MOC\V\Component\Captcha\Component\IBridgeInterface;
use MOC\V\Component\Captcha\Component\IVendorInterface;
use MOC\V\Component\Captcha\Vendor\Vendor;

/**
 * Class Captcha
 *
 * @package MOC\V\Component\Captcha
 */
class Captcha implements IVendorInterface
{

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
     * @return IBridgeInterface
     */
    public static function getCaptcha()
    {

        return self::getSimplePhpCaptcha();
    }

    /**
     * @return IBridgeInterface
     */
    public static function getSimplePhpCaptcha()
    {

        $Doctrine = new Captcha(
            new Vendor(
                new SimplePhpCaptcha()
            )
        );

        return $Doctrine->getBridgeInterface();
    }

    /**
     * @return \MOC\V\Component\Captcha\Component\IBridgeInterface
     */
    public function getBridgeInterface()
    {

        return $this->VendorInterface->getBridgeInterface();
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
     * @return \MOC\V\Component\Captcha\Component\IBridgeInterface
     */
    public function setBridgeInterface(IBridgeInterface $BridgeInterface)
    {

        return $this->VendorInterface->setBridgeInterface($BridgeInterface);
    }
}
