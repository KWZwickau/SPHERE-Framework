<?php
namespace MOC\V\Core\AutoLoader\Component;

/**
 * Interface IVendorInterface
 *
 * @package MOC\V\Core\AutoLoader\Component
 */
interface IVendorInterface
{

    /**
     * @return IBridgeInterface
     */
    public function getBridgeInterface();

    /**
     * @param IBridgeInterface $BridgeInterface
     *
     * @return IVendorInterface
     */
    public function setBridgeInterface(IBridgeInterface $BridgeInterface);
}
