<?php
namespace MOC\V\Component\Router\Component\Exception;

use MOC\V\Component\Router\Exception\RouterException;

/**
 * Class ComponentException
 *
 * @package MOC\V\Component\Router\Component\Exception
 */
class ComponentException extends RouterException
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
