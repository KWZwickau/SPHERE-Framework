<?php
namespace MOC\V\Component\Router\Component;

/**
 * Interface IVendorInterface
 *
 * @package MOC\V\Component\Router\Component
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
