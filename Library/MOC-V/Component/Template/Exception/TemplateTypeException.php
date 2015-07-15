<?php
namespace MOC\V\Component\Template\Exception;

/**
 * Class TemplateTypeException
 *
 * @package MOC\V\Component\Template\Exception
 */
class TemplateTypeException extends TemplateException
{

    /**
     * @param string $Message
     * @param int    $Code
     * @param null   $Previous
     */
    public function __construct( $Message = "", $Code = 0, $Previous = null )
    {
        $Message = 'Template type '.$Message.' not supported!';

        parent::__construct( $Message, $Code, $Previous );
    }
}
