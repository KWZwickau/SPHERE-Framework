<?php
namespace SPHERE\Common\Frontend\Form\Repository\Field;

use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\Repository\AbstractTextField;
use SPHERE\Common\Frontend\Icon\IIconInterface;

/**
 * Class TextField
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Field
 */
class TextField extends AbstractTextField implements IFieldInterface
{
    /** @var string $Label */
    private $Label = '';
    /** @var string $Label */
    private $Placeholder = '';

    /**
     * @param string         $Name
     * @param null|string    $Placeholder
     * @param null|string    $Label
     * @param IIconInterface $Icon
     * @param null|string    $Mask 9: Number, a:Char, w:Alphanumeric, *:Any, ?:Optional (plus following)
     * @param int            $MaxLength
     */
    public function __construct(
        $Name,
        $Placeholder = '',
        $Label = '',
        IIconInterface $Icon = null,
        $Mask = null,
        $MaxLength = 255
    ) {

        $this->Name = $Name;
        $this->Label = $Label;
        $this->Placeholder = $Placeholder;
        $this->Template = $this->getTemplate(__DIR__.'/TextField.twig');
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementLabel', $Label);
        $this->Template->setVariable('ElementPlaceholder', $Placeholder);
        if (null !== $Icon) {
            $this->Template->setVariable('ElementIcon', $Icon);
        }
        if (null !== $Mask) {
            $this->Template->setVariable('ElementMask', $Mask);
        }
        $this->Template->setVariable('ElementMaxLength', $MaxLength);
        $this->Template->setVariable('ElementType', 'text');
    }

    /**
     * @param bool $upper // nur Eingabeoptik -> service muss ein strtoupper ausführen!
     *
     * @return $this
     */
    public function setCaseToUpper($upper = true){

        if ($upper) {
            $this->Template->setVariable('ElementCase', 'upper');
        } else {
            $this->Template->setVariable('ElementCase', 'lower');
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function setFieldType($value = 'tel'){

        $this->Template->setVariable('ElementType', $value);
        return $this;
    }

    /**
     * @return $this
     */
    public function setAutoComplete($value = 'one-time-code'){
        // Muss mit einem anderen Typ als password, text, number erfolgen -> wird sonst auf "off" gestellt
        $this->setFieldType();
        $this->Template->setVariable('ElementAutoComplete', $value);
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->Label;
    }

    /**
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->Placeholder;
    }

}
