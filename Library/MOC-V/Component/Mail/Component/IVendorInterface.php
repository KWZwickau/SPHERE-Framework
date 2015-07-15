<?php
namespace MOC\V\Component\Mail\Component;

/**
 * Interface IVendorInterface
 *
 * @package MOC\V\Component\Mail\Component
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
