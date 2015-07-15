<?php
namespace MOC\V\Component\Router\Exception;

use Exception;

/**
 * Class RouterException
 *
 * @package MOC\V\Component\Router\Exception
 */
class RouterException extends Exception
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
