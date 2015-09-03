<?php
namespace MOC\V\Component\Captcha\Component;

/**
 * Interface IVendorInterface
 *
 * @package MOC\V\Component\Captcha\Component
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
