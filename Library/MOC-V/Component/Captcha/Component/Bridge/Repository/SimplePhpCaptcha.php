<?php
namespace MOC\V\Component\Captcha\Component\Bridge\Repository;

use MOC\V\Component\Captcha\Component\Bridge\Bridge;
use MOC\V\Component\Captcha\Component\IBridgeInterface;
use MOC\V\Core\GlobalsKernel\GlobalsKernel;

/**
 * Class SimplePhpCaptcha
 *
 * @package MOC\V\Component\Captcha\Component\Bridge
 */
class SimplePhpCaptcha extends Bridge implements IBridgeInterface
{

    /** @var string $SessionKey */
    private $SessionKey = '';

    /**
     *
     */
    public function __construct()
    {

        require_once( __DIR__.'/../../../Vendor/SimplePhpCaptcha/0.0-Master/simple-php-captcha.php' );
        $this->SessionKey = sha1(__CLASS__);
    }

    /**
     * @return SimplePhpCaptcha
     * @throws \Exception
     */
    public function createCaptcha()
    {

        $SESSION = GlobalsKernel::getGlobals()->getSESSION();
        $SESSION[$this->SessionKey] = \simple_php_captcha();
        GlobalsKernel::getGlobals()->setSESSION($SESSION);
        return $this;
    }

    /**
     * @param string $InputValue
     *
     * @return bool
     */
    public function verifyCaptcha($InputValue)
    {

        $SESSION = GlobalsKernel::getGlobals()->getSESSION();
        return $SESSION[$this->SessionKey]['code'] == $InputValue;
    }

    /**
     * @return string
     */
    public function getCode()
    {

        $SESSION = GlobalsKernel::getGlobals()->getSESSION();
        return $SESSION[$this->SessionKey]['code'];
    }

    /**
     * @return string
     */
    public function getCaptcha()
    {

        $SESSION = GlobalsKernel::getGlobals()->getSESSION();
        return $SESSION[$this->SessionKey]['image_src'];
    }
}
