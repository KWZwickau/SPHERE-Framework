<?php
namespace MOC\V\Component\Documentation\Exception;

use Exception;

/**
 * Class DocumentationException
 *
 * @package MOC\V\Component\Documentation\Exception
 */
class DocumentationException extends Exception
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
