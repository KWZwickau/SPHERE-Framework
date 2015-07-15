<?php
namespace MOC\V\Component\Template\Component;

/**
 * Interface IVendorInterface
 *
 * @package MOC\V\Component\Template\Component
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
