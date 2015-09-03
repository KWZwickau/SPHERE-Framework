<?php
namespace MOC\V\Core\AutoLoader\Component\Exception;

use MOC\V\Core\AutoLoader\Exception\AutoLoaderException;

/**
 * Class ComponentException
 *
 * @package MOC\V\Core\AutoLoader\Component\Exception
 */
class ComponentException extends AutoLoaderException
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
