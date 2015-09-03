<?php
namespace MOC\V\Component\Document\Component\Exception\Repository;

use MOC\V\Component\Document\Component\Exception\ComponentException;

/**
 * Class TypeFileException
 *
 * @package MOC\V\Component\Document\Component\Exception
 */
class TypeFileException extends ComponentException
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
