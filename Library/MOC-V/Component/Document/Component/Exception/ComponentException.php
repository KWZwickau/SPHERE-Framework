<?php
namespace MOC\V\Component\Document\Component\Exception;

use MOC\V\Component\Document\Exception\DocumentException;

/**
 * Class ComponentException
 *
 * @package MOC\V\Component\Document\Component\Exception
 */
class ComponentException extends DocumentException
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
