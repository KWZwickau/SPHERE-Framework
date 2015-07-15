<?php
namespace MOC\V\Component\Template\Component\Exception;

use MOC\V\Component\Template\Exception\TemplateException;

/**
 * Class ComponentException
 *
 * @package MOC\V\Component\Template\Component\Exception
 */
class ComponentException extends TemplateException
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
