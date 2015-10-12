<?php
namespace SPHERE\System\Token\YubiKey;

/**
 * Class KeyValue
 *
 * @package SPHERE\System\Token\YubiKey
 */
class KeyValue
{

    /** @var string $KeyOTP */
    private $KeyOTP = '';
    /** @var string $KeyNOnce */
    private $KeyNOnce = '';

    /**
     * @param string $KeyOTP
     */
    public function __construct($KeyOTP)
    {

        $this->KeyOTP = $KeyOTP;
    }

    /**
     * @return string
     */
    public function getKeyOTP()
    {

        return $this->KeyOTP;
    }

    /**
     * @return string
     */
    public function getKeyNOnce()
    {

        return $this->KeyNOnce;
    }

    /**
     * @param string $KeyNOnce
     */
    public function setKeyNOnce($KeyNOnce)
    {

        $this->KeyNOnce = $KeyNOnce;
    }
}
