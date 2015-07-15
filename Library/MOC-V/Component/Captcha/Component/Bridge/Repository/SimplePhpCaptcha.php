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

        \session_start();
        $_SESSION[sha1( __CLASS__ )] = \simple_php_captcha();
        \session_write_close();
        return $this;
    }

    /**
     * @param string $InputValue
     *
     * @return bool
     */
    public function verifyCaptcha( $InputValue )
    {

        \session_start();
        $Result = $_SESSION[sha1( __CLASS__ )]['code'] == $InputValue;
        \session_write_close();
        return $Result;
    }

    /**
     * @return string
     */
    public function getCaptcha()
    {

        \session_start();
        $Result = $_SESSION[sha1( __CLASS__ )]['image_src'];
        \session_write_close();
        return $Result;
    }
}
