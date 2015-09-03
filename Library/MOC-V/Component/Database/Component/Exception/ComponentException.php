<?php
namespace MOC\V\Component\Database\Component\Exception;

use MOC\V\Component\Database\Exception\DatabaseException;

/**
 * Class ComponentException
 *
 * @package MOC\V\Component\Database\Component\Exception
 */
class ComponentException extends DatabaseException
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
