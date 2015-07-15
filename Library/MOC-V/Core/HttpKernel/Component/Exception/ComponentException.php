<?php
namespace MOC\V\Core\HttpKernel\Component\Exception;

use MOC\V\Core\HttpKernel\Exception\HttpKernelException;

/**
 * Class ComponentException
 *
 * @package MOC\V\Core\HttpKernel\Component\Exception
 */
class ComponentException extends HttpKernelException
{

    /**
     * @param string $Message
     * @param int    $Code
     * @param null   $Previous
     */
    public function __construct( $Message = "", $Code = 0, $Previous = null )
    {

        parent::__construct( $Message, $Code, $Previous );
    }
}
