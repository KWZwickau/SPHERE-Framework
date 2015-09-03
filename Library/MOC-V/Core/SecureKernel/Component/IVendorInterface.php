<?php
namespace MOC\V\Core\SecureKernel\Component;

/**
 * Interface IVendorInterface
 *
 * @package MOC\V\Core\SecureKernel\Component
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
