<?php
namespace SPHERE\System\Token\YubiKey;

use Exception;

/**
 * Class ComponentException
 *
 * @package SPHERE\System\Token\YubiKey
 */
class ComponentException extends \Exception
{

    /**
     * @param string    $Message
     * @param int       $Code
     * @param Exception $Previous
     */
    public function __construct($Message = "", $Code = 0, Exception $Previous = null)
    {

        parent::__construct($Message, $Code, $Previous);
    }

}
