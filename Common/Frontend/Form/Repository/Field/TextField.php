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
     */
    public function __construct(
        $Name,
        $Placeholder = '',
        $Label = '',
        IIconInterface $Icon = null,
        $Mask = null
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
