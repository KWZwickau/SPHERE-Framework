<?php
namespace MOC\V\Component\Mail\Component\Exception;

use MOC\V\Component\Mail\Exception\MailException;

/**
 * Class ComponentException
 *
 * @package MOC\V\Component\Mail\Component\Exception
 */
class ComponentException extends MailException
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
