<?php
namespace SPHERE\Common\Frontend\Form\Repository\Field;

use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\Repository\AbstractTextField;

/**
 * Class Editor
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Field
 */
class Editor extends AbstractTextField implements IFieldInterface
{

    private $height = 300;
    private $menubar = false;
    private $plugins = 'lists code wordcount paste'; //
    private $toolbar = 'undo redo | bold italic underline | bullist numlist outdent indent | removeformat | Source code';
    // toolbar doesn't work in DOM-PDF -> | alignleft aligncenter alignright alignjustify

    /**
     * Editor constructor.
     *
     * @param string $Name
//     * @param string $toolbar
     */
    public function __construct(
        $Name// , $toolbar = 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
    ) {

        $this->Name = $Name;
        $this->Template = $this->getTemplate(__DIR__.'/Editor.twig');
        $this->Template->setVariable('ElementHash', md5(uniqid(microtime(),true)));
        $this->Template->setVariable('ElementName', $Name);
        $this->setPostValue($this->Template, $Name, 'ElementValue');
    }

    /**
     * @param string $toolbar
     *
     * @return $this
     */
    public function setHeight($height = 300)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @param false|string $menubar
     * e.g. https://www.tiny.cloud/docs/configure/editor-appearance/#menu
     *
     * @return $this
     */
    public function setMenubar($menubar = false)
    {
        $this->menubar = $menubar;
        return $this;
    }

    /**
     * @param string $toolbar
     * e.g. https://www.tiny.cloud/docs/plugins/
     * @return $this
     */
    public function setPlugins($plugins = 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount')
    {
        $this->plugins = $plugins;
        return $this;
    }

    /**
     * @param string $toolbar
     * e.g. https://www.tiny.cloud/docs/advanced/editor-control-identifiers/#toolbarcontrols
     * @return $this
     */
    public function setToolbar($toolbar = 'undo redo | bold italic unterline | bullist numlist outdent indent | removeformat | Source code')
    {
        $this->toolbar = $toolbar;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $Option = array();
        if($this->height){
            $Option = array_merge($Option, array('height' => $this->height));
        }
        if($this->menubar){
            $Option = array_merge($Option, array('menubar' => $this->menubar));
        }
        if($this->plugins){
            $Option = array_merge($Option, array('plugins' => $this->plugins));
        }
        if($this->toolbar){
            $Option = array_merge($Option, array('toolbar' => $this->toolbar));
        }
        $Option = json_encode($Option);
        $this->Template->setVariable('ElementOption', $Option);

        return parent::getContent();
    }
}
