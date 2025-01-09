<?php
namespace SPHERE\Common\Frontend\Form\Repository\Field;

use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\Repository\AbstractTextField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;

/**
 * Class PasswordField
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Field
 */
class PasswordField extends AbstractTextField implements IFieldInterface
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
        $this->Template = $this->getTemplate(__DIR__.'/PasswordField.twig');
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementLabel', $Label);
        $this->Template->setVariable('ElementPlaceholder', $Placeholder);
        if (null !== $Icon) {
            $this->Template->setVariable('ElementIcon', $Icon);
        }
    }

    /**
     * @return string
     */
    public function getContent()
    {

        if( $this->isForceDefaultValue ) {
            $this->setPostValue($this->Template, $this->getName(), 'ElementValue');
        }
        return parent::getContent();
    }

    public function setShow(IIconInterface $Icon)
    {
        $this->Template->setVariable('ElementIconShow', $Icon);
        // new ToolTip($Icon, 'Passwort verstecken')
//        $this->Icon = new ToolTip($this->Icon, 'Passwort anzeigen');
//        $this->Template->setVariable('ElementIcon', new ToolTip($this->Icon, 'Passwort anzeigen'));
    }
}
