<?php
namespace MOC\V\Component\Database\Exception;

use Exception;

/**
 * Class DatabaseException
 *
 * @package MOC\V\Component\Database\Exception
 */
class DatabaseException extends Exception
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
