<?php
namespace MOC\V\Component\Document\Exception;

use Exception;

/**
 * Class DocumentException
 *
 * @package MOC\V\Component\Document\Exception
 */
class DocumentException extends Exception
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
