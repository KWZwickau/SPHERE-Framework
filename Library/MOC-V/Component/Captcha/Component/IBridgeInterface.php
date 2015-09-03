<?php
namespace MOC\V\Component\Captcha\Component;

/**
 * Interface IBridgeInterface
 *
 * @package MOC\V\Component\Captcha\Component
 */
interface IBridgeInterface
{

    /**
     * @param string $InputValue
     *
     * @return bool
     */
    public function verifyCaptcha($InputValue);


    /**
     * @return IBridgeInterface
     * @throws \Exception
     */
    public function createCaptcha();

    /**
     * @return mixed
     */
    public function getCaptcha();
}
