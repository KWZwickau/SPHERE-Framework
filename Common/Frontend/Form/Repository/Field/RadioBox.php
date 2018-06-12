<?php
namespace SPHERE\Common\Frontend\Form\Repository\Field;

use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;

/**
 * Class RadioBox
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Field
 */
class RadioBox extends AbstractField implements IFieldInterface
{

    const RADIO_BOX_TYPE_DEFAULT = 'radio-primary';
    const RADIO_BOX_TYPE_BLACK = 'radio-default';
    const RADIO_BOX_TYPE_SUCCESS = 'radio-success';
    const RADIO_BOX_TYPE_WARNING = 'radio-warning';
    const RADIO_BOX_TYPE_INFO = 'radio-info';
    const RADIO_BOX_TYPE_DANGER = 'radio-danger';

    /**
     * @param string $Name
     * @param string $Label
     * @param mixed  $Value
     * @param mixed  $Type
     */
    public function __construct(
        $Name,
        $Label,
        $Value,
        $Type = RadioBox::RADIO_BOX_TYPE_DEFAULT
    ) {

        $this->Name = $Name;
        $this->Template = $this->getTemplate(__DIR__.'/RadioBox.twig');
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementLabel', $Label);
        $this->Template->setVariable('ElementValue', $Value);
        $this->Template->setVariable('ElementType', $Type);
        $this->Template->setVariable('ElementHash', md5($Name . $Label . $Value . (new \DateTime())->getTimestamp()));
        if ($this->isChecked($this->getName(), $Value)) {
            $this->Template->setVariable('ElementChecked', 'checked="checked"');
        }
    }

    /**
     * MUST NOT USE parent::getContent() cause Element POST Value will be overwritten!
     *
     * e.g: POST =1
     *
     * Expected: Box{1}=1(Selected) Box{2}=2 | Behaviour: Box{1}=1(Selected) Box{2}=1
     *
     * @return string
     */
    public function getContent()
    {
        foreach ($this->TemplateVariableList as $Key => $Value) {
            $this->Template->setVariable($Key, $Value);
        }
        return $this->Template->getContent();
        //return parent::getContent();
    }
}
