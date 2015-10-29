<?php
namespace MOC\V\Core\GlobalsKernel\Component\Exception;

use MOC\V\Core\GlobalsKernel\Exception\GlobalsKernelException;

/**
 * Class ComponentException
 *
 * @package MOC\V\Core\GlobalsKernel\Component\Exception
 */
class ComponentException extends GlobalsKernelException
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
