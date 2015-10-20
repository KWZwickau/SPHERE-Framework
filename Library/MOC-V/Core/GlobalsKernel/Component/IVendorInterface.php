<?php
namespace MOC\V\Core\GlobalsKernel\Component;

/**
 * Interface IVendorInterface
 *
 * @package MOC\V\Core\GlobalsKernel\Component
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
