<?php
namespace SPHERE\Common\Frontend\Form\Repository\Field;

use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\Repository\Field;

/**
 * Class CheckBox
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Field
 */
class CheckBox extends Field implements IFieldInterface
{

    /**
     * @param string $Name
     * @param string $Label
     * @param mixed  $Value
     * @param array  $ToggleTarget
     */
    public function __construct(
        $Name,
        $Label,
        $Value,
        $ToggleTarget = array()
    ) {

        $this->Name = $Name;
        $this->Template = $this->getTemplate(__DIR__.'/CheckBox.twig');
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementLabel', $Label);
        $this->Template->setVariable('ElementValue', $Value);
        $this->Template->setVariable('ElementToggleTarget', $ToggleTarget);
        $this->Template->setVariable('ElementHash', md5($Name . $Label . $Value . (new \DateTime())->getTimestamp()));
        if ($this->isChecked($this->getName(), $Value)) {
            $this->Template->setVariable('ElementChecked', 'checked="checked"');
        }
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    public function getContent()
    {

        return $this->Template->getContent();
    }
}
