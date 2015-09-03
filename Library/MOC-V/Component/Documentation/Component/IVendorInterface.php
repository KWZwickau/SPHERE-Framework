<?php
namespace MOC\V\Component\Documentation\Component;

/**
 * Interface IVendorInterface
 *
 * @package MOC\V\Component\Documentation\Component
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
