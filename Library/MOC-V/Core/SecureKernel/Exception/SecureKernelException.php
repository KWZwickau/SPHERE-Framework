<?php
namespace MOC\V\Core\SecureKernel\Exception;

use Exception;

/**
 * Class SecureKernelException
 *
 * @package MOC\V\Core\SecureKernel\Exception
 */
class SecureKernelException extends Exception
{

    /**
     * @param string $Message
     * @param int    $Code
     * @param null   $Previous
     */
    public function __construct($Message = "", $Code = 0, $Previous = null)
    {

        parent::__construct($Message, $Code, $Previous);
    }
}
