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

    /** @var string $XmlName */
    protected $XmlName = '';

    /** @var AbstractItem[] $ItemList */
    protected $ItemList = array();

    /** @var AbstractFragment[] $FragmentList */
    protected $FragmentList = array();

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
     * @param string $XmlName
     */
    public function setXmlName($XmlName)
    {

        $this->XmlName = $XmlName;
    }

    /**
     * @return \DOMElement
     */
    public function getXmlNode()
    {

        $Root = Output::getDocument()->createElement('fragment');
        $Root->setAttribute('name', $this->XmlName);
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
