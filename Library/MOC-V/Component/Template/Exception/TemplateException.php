<?php
namespace MOC\V\Component\Template\Exception;

use Exception;

/**
 * Class TemplateException
 *
 * @package MOC\V\Component\Template\Exception
 */
class TemplateException extends Exception
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
