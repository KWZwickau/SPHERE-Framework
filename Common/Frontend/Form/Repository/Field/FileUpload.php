<?php
namespace SPHERE\Common\Frontend\Form\Repository\Field;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;

/**
 * Class FileUpload
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Field
 */
class FileUpload extends AbstractField implements IFieldInterface
{

    /** @var string $Name */
    protected $Name;
    /** @var IBridgeInterface $Template */
    protected $Template = null;
    /** @var int $MaxSize */
    protected $MaxSize = 2 * 1024 * 1024; // 2MB

    /**
     * @param string         $Name
     * @param null|string    $Placeholder
     * @param null|string    $Label
     * @param IIconInterface $Icon
     * @param null|array     $Option
     */
    public function __construct(
        $Name,
        $Placeholder = '',
        $Label = '',
        IIconInterface $Icon = null,
        $Option = null
    ) {

        $this->Name = $Name;
        $this->Template = $this->getTemplate(__DIR__.'/FileUpload.twig');
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementLabel', $Label);
        $this->Template->setVariable('ElementPlaceholder', $Placeholder);
        if (null !== $Icon) {
            $this->Template->setVariable('ElementIcon', $Icon);
        }
        $this->setPostValue($this->Template, $Name, 'ElementValue');
        if (is_array($Option)) {
            $this->Template->setVariable('ElementOption', json_encode($Option));
        }
    }

    /**
     * @param int $MaxSize as MB
     *
     * @return $this
     */
    public function setMaxSize($MaxSize = 2)
    {

        $this->MaxSize = $MaxSize * 1024 * 1024;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $this->Template->setVariable('ElementMaxSize', $this->MaxSize);

        return parent::getContent();
    }

}
