<?php
namespace MOC\V\Component\Documentation\Vendor;

use MOC\V\Component\Documentation\Component\IBridgeInterface;
use MOC\V\Component\Documentation\Component\IVendorInterface;

/**
 * Class Vendor
 *
 * @package MOC\V\Component\Documentation\Component
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
