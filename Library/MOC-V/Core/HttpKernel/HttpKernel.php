<?php
namespace MOC\V\Core\HttpKernel;

use MOC\V\Core\HttpKernel\Component\Bridge\Repository\UniversalRequest;
use MOC\V\Core\HttpKernel\Component\IBridgeInterface;
use MOC\V\Core\HttpKernel\Component\IVendorInterface;

/**
 * Class HttpKernel
 *
 * @package MOC\V\Core\HttpKernel
 */
class HttpKernel implements IVendorInterface
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
    public static function getRequest()
    {

        return self::getUniversalRequest();
    }

    /**
     * @return IBridgeInterface
     */
    private static function getUniversalRequest()
    {

        return new UniversalRequest();
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
     * @return IBridgeInterface
     */
    public function getBridgeInterface()
    {

        return $this->VendorInterface->getBridgeInterface();
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
