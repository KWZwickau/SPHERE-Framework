<?php
namespace MOC\V\Core\FileSystem\Component\Exception\Repository;

use MOC\V\Core\FileSystem\Component\Exception\ComponentException;

/**
 * Class TypeFileException
 *
 * @package MOC\V\Core\FileSystem\Component\Exception
 */
class TypeFileException extends ComponentException
{

    /**
     * @param string $Message
     * @param int    $Code
     * @param null   $Previous
     */
    public function __construct( $Message = "", $Code = 0, $Previous = null )
    {

        $Message = $Message.' is not a file!';

        parent::__construct( $Message, $Code, $Previous );
    }

}
