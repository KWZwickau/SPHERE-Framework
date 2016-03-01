<?php
namespace MOC\V\Component\Document\Vendor\UniversalXml;

use MOC\V\Component\Document\Vendor\UniversalXml\Source\Node;
use MOC\V\Component\Document\Vendor\UniversalXml\Source\Parser;
use MOC\V\Component\Document\Vendor\UniversalXml\Source\Tokenizer;

/**
 * Class Api
 *
 * @package MOC\V\Component\Document\Vendor\UniversalXml
 */
class Api
{

    /** @var string $XmlContent */
    private $XmlContent = '';

    /**
     * @param string $XmlContent
     * @codeCoverageIgnore (Cache)
     */
    function __construct($XmlContent)
    {

        $this->XmlContent = (string)$XmlContent;
    }

    /**
     * @return Node|null
     */
    public function parseContent()
    {

        $Instance = new Parser(new Tokenizer($this->XmlContent));

        return $Instance->getResult();
    }

    /**
     * @param \SimpleXMLElement $Xml
     *
     * @codeCoverageIgnore
     * @return Node
     */
    private function parseSimpleXml(\SimpleXMLElement $Xml)
    {

        $Node = new Node();
        $Node->setName($Xml->getName());
        $Object = get_object_vars($Xml);

        if (isset( $Object['@attributes'] )) {
            array_walk($Object['@attributes'], function ($Value, $Name, Node $Node) {

                $Node->setAttribute($Name, $Value);
            }, $Node);
            unset( $Object['@attributes'] );
        }

        if (count($Object) > 0) {
            $Object = new \ArrayIterator($Object);
            foreach ($Object as $Children) {
                if (is_object($Children)) {
                    $Node->addChild($this->parseSimpleXml($Children));
                } else {
                    $Children = new \ArrayIterator($Children);
                    foreach ($Children as $Xml) {
                        $Node->addChild($this->parseSimpleXml($Xml));
                    }
                }
            }
        } else {
            $Node->setContent($Xml->__toString());
        }

        return $Node;
    }

}
