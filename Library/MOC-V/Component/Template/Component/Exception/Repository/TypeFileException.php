<?php
namespace MOC\V\Component\Template\Component\Exception\Repository;

use MOC\V\Component\Template\Component\Exception\ComponentException;

/**
 * Class TypeFileException
 *
 * @package MOC\V\Component\Template\Component\Exception
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

        $Message = $Message.' is a directory!';

        parent::__construct( $Message, $Code, $Previous );
    }

}
