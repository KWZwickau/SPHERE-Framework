<?php
namespace SPHERE\System\Token\YubiKey;

/**
 * Class BadOTPException
 *
 * @package SPHERE\System\Token\YubiKey
 */
class BadOTPException extends ComponentException
{

    /**
     * @param string             $Message
     * @param int                $Code
     * @param ComponentException $Previous
     */
    public function __construct($Message = "", $Code = 0, ComponentException $Previous = null)
    {

        parent::__construct($Message, $Code, $Previous);
    }

}
