<?php
namespace MOC\V\Core\FileSystem\Vendor;

use MOC\V\Core\FileSystem\Component\IBridgeInterface;
use MOC\V\Core\FileSystem\Component\IVendorInterface;

/**
 * Class Vendor
 *
 * @package MOC\V\Core\FileSystem\Component
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
