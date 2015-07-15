<?php
namespace MOC\V\Component\Mail\Exception;

use Exception;

/**
 * Class MailException
 *
 * @package MOC\V\Component\Mail\Exception
 */
class MailException extends Exception
{

    /**
     * @param string $Message
     * @param int    $Code
     * @param null   $Previous
     */
    public function __construct( $Message = "", $Code = 0, \Exception $Previous = null )
    {

        parent::__construct( $Message, $Code, $Previous );
    }
}
