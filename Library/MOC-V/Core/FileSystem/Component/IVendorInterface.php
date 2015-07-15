<?php
namespace MOC\V\Core\FileSystem\Component;

/**
 * Interface IVendorInterface
 *
 * @package MOC\V\Core\FileSystem\Component
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

