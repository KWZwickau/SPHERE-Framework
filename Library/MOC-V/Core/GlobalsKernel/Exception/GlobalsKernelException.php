<?php
namespace MOC\V\Core\GlobalsKernel\Exception;

use Exception;

/**
 * Class GlobalsKernelException
 *
 * @package MOC\V\Core\GlobalsKernel\Exception
 */
class GlobalsKernelException extends Exception
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
