<?php
namespace SPHERE\System\Token\YubiKey;

/**
 * Class ReplayedOTPException
 *
 * @package SPHERE\System\Token\YubiKey
 */
class ReplayedOTPException extends ComponentException
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
