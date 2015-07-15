<?php
namespace MOC\V\Component\Router\Component\Exception\Repository;

use MOC\V\Component\Router\Component\Exception\ComponentException;

/**
 * Class MissingParameterException
 *
 * @package MOC\V\Component\Router\Component\Exception
 */
class MissingParameterException extends ComponentException
{

    /**
     * @param string $Message
     * @param int    $Code
     * @param null   $Previous
     */
    public function __construct( $Message = "", $Code = 0, $Previous = null )
    {

        $Message = 'Parameter '.$Message.' not defined!';

        parent::__construct( $Message, $Code, $Previous );
    }
}
