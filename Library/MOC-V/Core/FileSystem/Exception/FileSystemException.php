<?php
namespace MOC\V\Core\FileSystem\Exception;

use Exception;

/**
 * Class FileSystemException
 *
 * @package MOC\V\Core\FileSystem\Exception
 */
class FileSystemException extends Exception
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
