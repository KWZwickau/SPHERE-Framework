<?php
namespace MOC\V\Component\Document\Exception;

/**
 * Class DocumentTypeException
 *
 * @package MOC\V\Component\Document\Exception
 */
class DocumentTypeException extends DocumentException
{

    /**
     * @param string $Message
     * @param int    $Code
     * @param null   $Previous
     */
    public function __construct( $Message = "", $Code = 0, $Previous = null )
    {

        $Message = 'Document type '.$Message.' not supported!';

        parent::__construct( $Message, $Code, $Previous );
    }
}
