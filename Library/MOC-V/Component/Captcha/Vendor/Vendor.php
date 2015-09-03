<?php
namespace MOC\V\Component\Captcha\Vendor;

use MOC\V\Component\Captcha\Component\IBridgeInterface;
use MOC\V\Component\Captcha\Component\IVendorInterface;

/**
 * Class Vendor
 *
 * @package MOC\V\Component\Captcha\Component
 */
class Vendor implements IVendorInterface
{

    /** @var IBridgeInterface $BridgeInterface */
    private $BridgeInterface = null;

    /**
     * @param IBridgeInterface $BridgeInterface
     */
    public function __construct(IBridgeInterface $BridgeInterface)
    {

        $this->setBridgeInterface($BridgeInterface);
    }

    /**
     * @return IBridgeInterface
     */
    public function getBridgeInterface()
    {

        return $this->BridgeInterface;
    }

    /**
     * @param IBridgeInterface $BridgeInterface
     *
     * @return IVendorInterface
     */
    public function setBridgeInterface(IBridgeInterface $BridgeInterface)
    {

        $this->BridgeInterface = $BridgeInterface;
        return $this;
    }
}
