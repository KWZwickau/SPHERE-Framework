<?php
namespace SPHERE\Common\Frontend\Form\Repository\Field;

use MOC\V\Component\Captcha\Captcha;
use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;

/**
 * @deprecated
 * Class TextCaptcha
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Field
 */
class TextCaptcha extends AbstractField implements IFieldInterface
{

    /**
     * @param string         $Name
     * @param null|string    $Placeholder
     * @param null|string    $Label
     * @param IIconInterface $Icon
     */
    public function __construct(
        $Name,
        $Placeholder = '',
        $Label = '',
        IIconInterface $Icon = null
    ) {

        $this->Name = $Name;
        $this->Template = $this->getTemplate(__DIR__.'/TextCaptcha.twig');
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementLabel', $Label);
        $this->Template->setVariable('ElementPlaceholder', $Placeholder);
        if (null !== $Icon) {
            $this->Template->setVariable('ElementIcon', $Icon);
        }
        $this->Template->setVariable('ElementSource', Captcha::getCaptcha()->createCaptcha()->getCaptcha());
    }

}
