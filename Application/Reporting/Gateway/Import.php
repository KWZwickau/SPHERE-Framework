<?php
namespace SPHERE\Application\Reporting\Gateway;

use MOC\V\Component\Document\Component\Bridge\Repository\UniversalXml;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Vendor\UniversalXml\Source\Node;

/**
 * Class Import
 *
 * @package SPHERE\Application\Reporting\Gateway
 */
class Import
{
    /** @var null|Node */
    private $Content = null;

    public function loadFile( $Location )
    {

        /** @var UniversalXml $Document */
        $Document = Document::getDocument( $Location );
        $this->Content = $Document->getContent();

        // TODO: Parse XML
        $FragmentList = $this->Content->getChildList();
        foreach( $FragmentList as $Fragment ) {
            $Class = $Fragment->getAttribute( 'name' );
            new $Class();
        }
    }
}
