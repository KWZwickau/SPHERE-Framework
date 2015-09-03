<?php
namespace MOC\V\Core\FileSystem\Component\Exception;

use MOC\V\Core\FileSystem\Exception\FileSystemException;

/**
 * Class ComponentException
 *
 * @package MOC\V\Core\FileSystem\Component\Exception
 */
class ComponentException extends FileSystemException
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
