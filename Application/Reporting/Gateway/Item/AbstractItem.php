<?php
namespace SPHERE\Application\Reporting\Gateway\Item;

use SPHERE\Application\Reporting\Gateway\Converter\Output;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class AbstractItem
 *
 * @package SPHERE\Application\Reporting\Gateway\Item
 */
abstract class AbstractItem
{

    /** @var \DOMElement[] $PayloadList */
    protected $PayloadList = array();
    /**
     * @var Element[]
     */
    protected $EntityList;
    /** @var string $XmlType */
    private $XmlType = null;
    /** @var int $XmlIdentifier */
    private $XmlIdentifier = 0;
    /** @var int $XmlReference */
    private $XmlReference = 0;
    /** @var int $XmlTarget */
    private $XmlTarget = 0;

    /**
     * @param string $XmlType
     */
    public function setXmlType($XmlType)
    {

        $this->XmlType = $XmlType;
    }

    /**
     * @param int $XmlIdentifier
     */
    public function setXmlIdentifier($XmlIdentifier)
    {

        $this->XmlIdentifier = $XmlIdentifier;
    }

    /**
     * @return \DOMElement
     */
    public function getXmlNode()
    {

        $Root = Output::getDocument()->createElement('item');
        $Root->setAttribute('type', $this->XmlType);
        $Root->setAttribute('identifier', $this->XmlIdentifier);
        $Root->setAttribute('reference', $this->XmlReference);
        $Root->setAttribute('target', $this->XmlTarget);

        foreach ((array)$this->PayloadList as $Item) {
            $Root->appendChild($Item);
        }

        return $Root;
    }

    /**
     * @param int $XmlReference
     */
    public function setXmlReference($XmlReference)
    {

        $this->XmlReference = $XmlReference;
    }

    /**
     * @param int $XmlTarget
     */
    public function setXmlTarget($XmlTarget)
    {

        $this->XmlTarget = $XmlTarget;
    }

    /**
     * @param array $Data
     *
     * @return $this
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
                }
            }
        }
        return $this;
    }
}
