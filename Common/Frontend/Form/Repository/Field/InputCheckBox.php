<?php
namespace SPHERE\Common\Frontend\Form\Repository\Field;

use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\Repository\Field;

/**
 * Class InputCheckBox
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Field
 */
class InputCheckBox extends Field implements IFieldInterface
{

    /**
     * @param string $Name
     * @param string $Label
     * @param mixed  $Value
     * @param array  $Target
     */
    public function __construct(
        $Name,
        $Label,
        $Value,
        $Target
    ) {

        $this->Name = $Name;
        $this->Template = $this->getTemplate(__DIR__.'/InputCheckBox.twig');
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementLabel', $Label);
        $this->Template->setVariable('ElementValue', $Value);
        $this->Template->setVariable('ElementTarget', $Target);
        $this->Template->setVariable('ElementHash', sha1($Name.$Label.$Value.(new \DateTime())->getTimestamp()));
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
