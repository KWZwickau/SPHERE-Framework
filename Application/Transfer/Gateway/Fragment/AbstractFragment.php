<?php
namespace SPHERE\Application\Transfer\Gateway\Fragment;

use SPHERE\Application\Transfer\Gateway\Converter\Output;
use SPHERE\Application\Transfer\Gateway\Item\AbstractItem;

/**
 * Class AbstractFragment
 *
 * @package SPHERE\Application\Transfer\Gateway\Fragment
 */
abstract class AbstractFragment
{

    /** @var string $XmlClass */
    private $XmlClass = '';

    /** @var AbstractItem[] $ItemList */
    private $ItemList = array();

    /** @var AbstractFragment[] $FragmentList */
    private $FragmentList = array();

    /**
     * @param AbstractItem $Item
     *
     * @return $this
     */
    public function addItem(AbstractItem $Item)
    {

        array_push($this->ItemList, $Item);
        return $this;
    }

    /**
     * @param AbstractFragment $Fragment
     *
     * @return $this
     */
    public function addFragment(AbstractFragment $Fragment)
    {

        array_push($this->FragmentList, $Fragment);
        return $this;
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
     */
    public function getXmlNode()
    {
        if( empty( $this->XmlClass ) ) {
            $this->setXmlClass(
                get_called_class()
//                (new \ReflectionClass($this))->getShortName()
            );
        }

        $Root = Output::getDocument()->createElement('Fragment');
        $Root->setAttribute('Class', $this->XmlClass);
        /** @var AbstractItem $Item */
        foreach ((array)$this->ItemList as $Item) {
            $Root->appendChild($Item->getXmlNode());
        }
        /** @var AbstractFragment $Fragment */
        foreach ((array)$this->FragmentList as $Fragment) {
            $Root->appendChild($Fragment->getXmlNode());
        }
        return $Root;
    }
}
