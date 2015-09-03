<?php
namespace MOC\V\Core\SecureKernel\Component\Exception;

use MOC\V\Core\SecureKernel\Exception\SecureKernelException;

/**
 * Class ComponentException
 *
 * @package MOC\V\Core\SecureKernel\Component\Exception
 */
class ComponentException extends SecureKernelException
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
