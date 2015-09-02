<?php
namespace MOC\V\Component\Captcha\Component\Bridge\Repository;

use MOC\V\Component\Captcha\Component\Bridge\Bridge;
use MOC\V\Component\Captcha\Component\IBridgeInterface;

/**
 * Class SimplePhpCaptcha
 *
 * @package MOC\V\Component\Captcha\Component\Bridge
 */
class SimplePhpCaptcha extends Bridge implements IBridgeInterface
{

    /**
     *
     */
    function __construct()
    {

        require_once( __DIR__.'/../../../Vendor/SimplePhpCaptcha/0.0-Master/simple-php-captcha.php' );
    }

    /**
     * @return SimplePhpCaptcha
     * @throws \Exception
     */
    public function createCaptcha()
    {

        $_SESSION[sha1(__CLASS__)] = \simple_php_captcha();
        return $this;
    }

    /**
     * @param string $InputValue
     *
     * @return bool
     */
    public function verifyCaptcha($InputValue)
    {

        return $_SESSION[sha1(__CLASS__)]['code'] == $InputValue;
    }

    /**
     * @return string
     */
    public function getCode()
    {

        return $_SESSION[sha1(__CLASS__)]['code'];
    }

    /**
     * @return string
     */
    public function getCaptcha()
    {

        return $_SESSION[sha1(__CLASS__)]['image_src'];
    }
}
