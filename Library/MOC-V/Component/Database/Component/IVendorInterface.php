<?php
namespace MOC\V\Component\Database\Component;

/**
 * Interface IVendorInterface
 *
 * @package MOC\V\Component\Database\Component
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
