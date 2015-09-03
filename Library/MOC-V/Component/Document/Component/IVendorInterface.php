<?php
namespace MOC\V\Component\Document\Component;

/**
 * Interface IVendorInterface
 *
 * @package MOC\V\Component\Document\Component
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
