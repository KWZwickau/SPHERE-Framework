<?php
namespace MOC\V\Core\GlobalsKernel;

use MOC\V\Core\GlobalsKernel\Component\Bridge\Repository\UniversalGlobals;
use MOC\V\Core\GlobalsKernel\Component\IBridgeInterface;
use MOC\V\Core\GlobalsKernel\Component\IVendorInterface;

/**
 * Class GlobalsKernel
 *
 * @package MOC\V\Core\GlobalsKernel
 */
class GlobalsKernel implements IVendorInterface
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
    public static function getGlobals()
    {

        return self::getUniversalGlobals();
    }

    /**
     * @return IBridgeInterface
     */
    private static function getUniversalGlobals()
    {

        return new UniversalGlobals();
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
