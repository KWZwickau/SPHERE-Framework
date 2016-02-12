<?php
namespace SPHERE\Application\Transfer\Gateway\Item;

use SPHERE\Application\Transfer\Gateway\Converter\Output;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class AbstractItem
 *
 * @package SPHERE\Application\Transfer\Gateway\Item
 */
abstract class AbstractItem
{

    /** @var \DOMElement[] $PayloadList */
    private $PayloadList = array();
    /** @var Element[] $EntityList */
    private $EntityList;
    /** @var array $EssentialList */
    private $EssentialList = array();
    /** @var string $XmlClass */
    private $XmlClass = null;
    /** @var int $XmlIdentifierCount */
    private static $XmlIdentifierCount = 0;
    /** @var int $XmlIdentifier */
    private $XmlIdentifier = 0;
    /** @var int $XmlReference */
    private $XmlReference = 0;
    /** @var int $XmlTarget */
    private $XmlTarget = 0;

    /**
     * Item constructor.
     *
     * @param Element|Element[] $EntityList
     */
    protected function __construct($EntityList)
    {

        self::$XmlIdentifierCount++;
        $this->XmlIdentifier = self::$XmlIdentifierCount;
        $this->setEntity( $EntityList );
    }

    /**
     * @param string $XmlClass
     */
    public function setXmlClass($XmlClass)
    {

        $this->XmlClass = $XmlClass;
    }

    /**
     * @return \DOMElement
     * @throws \Exception
     */
    public function getXmlNode()
    {

        if( empty( $this->XmlClass ) ) {
            $this->setXmlClass(
                get_called_class()
//                (new \ReflectionClass(current($this->EntityList)))->getShortName()
            );
        }

        $Root = Output::getDocument()->createElement('Item');
        $Root->setAttribute('Class', $this->XmlClass);
        $Root->setAttribute('Identifier', $this->XmlIdentifier);
        $Root->setAttribute('Reference', $this->XmlReference);
        $Root->setAttribute('Target', $this->XmlTarget);

        if( empty( $this->PayloadList ) ) {
            throw new \Exception( 'Payload missing: '.$this->XmlClass );
        }

        foreach ((array)$this->PayloadList as $Item) {
            $Root->appendChild($Item);
        }

        return $Root;
    }

    /**
     * @param int $Value
     */
    public function setXmlReference($Value)
    {

        $this->XmlReference = $Value;
    }

    /**
     * @param int $Value
     */
    public function setXmlTarget($Value)
    {

        $this->XmlTarget = $Value;
    }

    /**
     * @param Element[] $List
     */
    public function setEntity($List)
    {

        if( !is_array( $List ) ) {
            $List = array( $List );
        }
        $this->EntityList = $List;
    }

    /**
     * @param array $Data
     *
     * @return $this
     * @throws \Exception
     */
    public function setPayload($Data)
    {

        foreach ((array)$this->EntityList as $Entity) {
            $Class = new \ReflectionClass($Entity);
            $Properties = $Class->getProperties();
            /** @var  \ReflectionProperty $Property */
            foreach ($Properties as $Property) {
                $Name = $Property->getName();
                if (isset( $Data[$Name] )) {
                    $Payload = Output::getDocument()->createElement($Name, $Data[$Name]);
                    array_push($this->PayloadList, $Payload);
                } else {
                    if( in_array( $Name, $this->EssentialList ) ) {
                        throw new \Exception( 'Missing essential: '.$Name );
                    }
                }
            }
        }
        return $this;
    }

    public function setEssential($Property)
    {

        array_push( $this->EssentialList, $Property );
        return $this;
    }

    /**
     * @return int
     */
    public function getXmlIdentifier()
    {

        return $this->XmlIdentifier;
    }
}
