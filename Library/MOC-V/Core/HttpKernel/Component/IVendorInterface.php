<?php
namespace MOC\V\Core\HttpKernel\Component;

/**
 * Interface IVendorInterface
 *
 * @package MOC\V\Core\HttpKernel\Component
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
    public function setBridgeInterface( IBridgeInterface $BridgeInterface );
}
