<?php
namespace MOC\V\Core\FileSystem\Component\Exception\Repository;

use MOC\V\Core\FileSystem\Component\Exception\ComponentException;

/**
 * Class EmptyFileException
 *
 * @package MOC\V\Core\FileSystem\Component\Exception
 */
class EmptyFileException extends ComponentException
{

    /**
     * @param string $Message
     * @param int    $Code
     * @param null   $Previous
     */
    public function __construct( $Message = "", $Code = 0, $Previous = null )
    {

        $Message = 'File location must not be empty!';

        parent::__construct( $Message, $Code, $Previous );
    }

}
