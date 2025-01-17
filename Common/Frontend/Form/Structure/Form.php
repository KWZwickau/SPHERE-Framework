<?php
namespace SPHERE\Common\Frontend\Form\Structure;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Form\IButtonInterface;
use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\System\Authenticator\Authenticator as Authenticator;
use SPHERE\System\Authenticator\Type\Get;
use SPHERE\System\Extension\Extension;

/**
 * Class Form
 *
 * @package SPHERE\Common\Frontend\Form\Structure
 */
class Form extends Extension implements IFormInterface
{

    /** @var FormGroup[] $GridGroupList */
    protected $GridGroupList = array();
    /** @var IButtonInterface[] $GridButtonList */
    protected $GridButtonList = array();
    /** @var string $Hash */
    protected $Hash = '';
    /** @var array $Data */
    protected $Data;
    /** @var IBridgeInterface $Template */
    protected $Template = null;
    /** @var bool $EnableSaveDraft */
    private $EnableSaveDraft = false;
    /** @var bool $EnableNewTab */
    private $EnableNewTab = false;
    /** @var bool $DisableSubmitAction */
    private $DisableSubmitAction = false;

    /**
     * @param FormGroup|FormGroup[] $FormGroup
     * @param null|IButtonInterface|IButtonInterface[] $FormButtonList
     * @param string $Action
     * @param array $Data
     */
    public function __construct($FormGroup, $FormButtonList = null, $Action = '', $Data = array())
    {

        if (!is_array($FormGroup)) {
            $FormGroup = array($FormGroup);
        }
        $this->GridGroupList = $FormGroup;

        if (!is_array($FormButtonList) && null !== $FormButtonList) {
            $FormButtonList = array($FormButtonList);
        } elseif (empty( $FormButtonList )) {
            $FormButtonList = array();
        }
        $this->GridButtonList = $FormButtonList;

        if (!empty($Data)) {
            $this->Data = $Data;
        } else {
            $this->Data = array();
        }

        $this->Template = $this->getTemplate(__DIR__.'/Form.twig');
        if (!empty($Data)) {
            $this->Template->setVariable('FormAction', $this->getRequest()->getUrlBase() . $Action);
            $this->Template->setVariable('FormData', '?'.http_build_query(
                    (new Authenticator(new Get()))->getAuthenticator()->createSignature($Data, $Action)
                    )
            );
        } else {
            if (empty($Action)) {
                $this->Template->setVariable('FormAction', $Action);
            } else {
                $this->Template->setVariable('FormAction', $this->getRequest()->getUrlBase() . $Action);
            }
        }
    }

    /**
     * @param string $Name
     * @param string $Message
     * @param IIconInterface|null $Icon
     *
     * @return Form
     */
    public function setError($Name, $Message, IIconInterface $Icon = null)
    {

        /** @var FormGroup $GridGroup */
        foreach ((array)$this->GridGroupList as $GridGroup) {
            /** @var FormRow $GridRow */
            foreach ((array)$GridGroup->getFormRow() as $GridRow) {
                /** @var FormColumn $GridCol */
                foreach ((array)$GridRow->getFormColumn() as $GridCol) {
                    /** @var IFieldInterface|Panel $GridElement */
                    foreach ((array)$GridCol->getFrontend() as $GridElement) {
                        if ($GridElement instanceof Panel) {
                            foreach ((array)$GridElement->getElementList() as $PanelElement) {
                                if ($PanelElement instanceof AbstractField) {
                                    /** @var IFieldInterface $PanelElement */
                                    if ($PanelElement->getName() == $Name) {
                                        $PanelElement->setError($Message, $Icon);
                                    }
                                } elseif (is_array($PanelElement)) {
                                    // Layout im Panel
                                    foreach ($PanelElement as $LayoutGroup) {
                                        if ($LayoutGroup instanceof LayoutGroup) {
                                            /** @var LayoutRow $LayoutRow */
                                            foreach ((array) $LayoutGroup->getLayoutRow() as $LayoutRow) {
                                                /** @var LayoutColumn $LayoutColumn */
                                                foreach ((array) $LayoutRow->getLayoutColumn() as $LayoutColumn) {
                                                    foreach ((array) $LayoutColumn->getFrontend() as $LayoutElement) {
                                                        if ($LayoutElement instanceof AbstractField) {
                                                            if ($LayoutElement->getName() == $Name) {
                                                                $LayoutElement->setError($Message, $Icon);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if ($GridElement instanceof AbstractField) {
                            if ($GridElement->getName() == $Name) {
                                $GridElement->setError($Message, $Icon);
                            }
                        }
                        if ($GridElement instanceof BlockReceiver) {
                            if (($GridSubElement = $GridElement->getContent())) {
                                if ($GridSubElement instanceof AbstractField && $GridSubElement->getName() == $Name) {
                                    $GridSubElement->setError($Message, $Icon);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param string $Name
     * @param string $Message
     * @param IIconInterface|null $Icon
     *
     * @return Form
     */
    public function setSuccess($Name, $Message = '', IIconInterface $Icon = null)
    {

        /** @var FormGroup $GridGroup */
        foreach ((array)$this->GridGroupList as $GridGroup) {
            /** @var FormRow $GridRow */
            foreach ((array)$GridGroup->getFormRow() as $GridRow) {
                /** @var FormColumn $GridCol */
                foreach ((array)$GridRow->getFormColumn() as $GridCol) {
                    /** @var IFieldInterface|Panel $GridElement */
                    foreach ((array)$GridCol->getFrontend() as $GridElement) {
                        if ($GridElement instanceof Panel) {
                            foreach ((array)$GridElement->getElementList() as $PanelElement) {
                                if ($PanelElement instanceof AbstractField) {
                                    /** @var IFieldInterface $PanelElement */
                                    if ($PanelElement->getName() == $Name) {
                                        $PanelElement->setSuccess($Message, $Icon);
                                    }
                                } elseif (is_array($PanelElement)) {
                                    // Layout im Panel
                                    foreach ($PanelElement as $LayoutGroup) {
                                        if ($LayoutGroup instanceof LayoutGroup) {
                                            /** @var LayoutRow $LayoutRow */
                                            foreach ((array) $LayoutGroup->getLayoutRow() as $LayoutRow) {
                                                /** @var LayoutColumn $LayoutColumn */
                                                foreach ((array) $LayoutRow->getLayoutColumn() as $LayoutColumn) {
                                                    foreach ((array) $LayoutColumn->getFrontend() as $LayoutElement) {
                                                        if ($LayoutElement instanceof AbstractField) {
                                                            if ($LayoutElement->getName() == $Name) {
                                                                $LayoutElement->setSuccess($Message, $Icon);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if ($GridElement instanceof AbstractField) {
                            if ($GridElement->getName() == $Name) {
                                $GridElement->setSuccess($Message, $Icon);
                            }
                        }
                        if ($GridElement instanceof BlockReceiver) {
                            if (($GridSubElement = $GridElement->getContent())) {
                                if ($GridSubElement instanceof AbstractField && $GridSubElement->getName() == $Name) {
                                    $GridSubElement->setSuccess($Message, $Icon);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param string $Message
     *
     * @return Form
     */
    public function setConfirm($Message)
    {

        $this->Template->setVariable('FormConfirm', $Message);
        return $this;
    }

    /**
     * @return Form
     */
    public function enableSaveDraft()
    {
        $this->EnableSaveDraft = true;
        return $this;
    }

    /**
     * @return Form
     */
    public function enableNewTab()
    {
        $this->EnableNewTab = true;
        return $this;
    }

    /**
     * @param IButtonInterface $Button
     *
     * @return Form
     */
    public function appendFormButton(IButtonInterface $Button)
    {

        array_push($this->GridButtonList, $Button);
        return $this;
    }

    /**
     * @param IButtonInterface $Button
     *
     * @return Form
     */
    public function prependFormButton(IButtonInterface $Button)
    {

        array_unshift($this->GridButtonList, $Button);
        return $this;
    }

    /**
     * @param FormGroup $GridGroup
     *
     * @return Form
     */
    public function appendGridGroup(FormGroup $GridGroup)
    {

        array_push($this->GridGroupList, $GridGroup);
        return $this;
    }

    /**
     * @param FormGroup $GridGroup
     *
     * @return Form
     */
    public function prependGridGroup(FormGroup $GridGroup)
    {

        array_unshift($this->GridGroupList, $GridGroup);
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $this->Template->setVariable('FormButtonList', $this->GridButtonList);
        $this->Template->setVariable('GridGroupList', $this->GridGroupList);
        $this->Template->setVariable('Hash', $this->getHash());
        if ($this->EnableSaveDraft) {
            $this->Template->setVariable('EnableSaveDraft', true);
        } else {
            $this->Template->setVariable('EnableSaveDraft', false);
        }
        if ($this->EnableNewTab) {
            $this->Template->setVariable('EnableNewTab', true);
        } else {
            $this->Template->setVariable('EnableNewTab', false);
        }
        if ($this->DisableSubmitAction) {
            $this->Template->setVariable('DisableSubmitAction', true);
        }
        return $this->Template->getContent();
    }

    /**
     * @return string
     */
    public function getHash()
    {
        $HashList = array();

        if (empty($this->Hash)) {
            $GroupList = $this->GridGroupList;
            array_walk($GroupList, function ($FormGroup) use (&$HashList) {
                if (is_object($FormGroup)) {
                    $HashList[] = get_class($FormGroup);
                    /** @var FormGroup $FormGroup */
                    $RowList = $FormGroup->getFormRow();
                    array_walk($RowList, function ($FormRow) use (&$HashList) {
                        if (is_object($FormRow)) {
                            $HashList[] = get_class($FormRow);
                            /** @var FormRow $FormRow */
                            $ColumnList = $FormRow->getFormColumn();
                            array_walk($ColumnList, function ($FormColumn) use (&$HashList) {
                                if (is_object($FormColumn)) {
                                    $HashList[] = get_class($FormColumn);
                                    /** @var FormColumn $FormColumn */
                                    $FrontendList = $FormColumn->getFrontend();
                                    array_walk($FrontendList, function ($Frontend) use (&$HashList) {
                                        if (is_object($Frontend)) {
                                            $HashList[] = get_class($Frontend);
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
            });
            $this->Hash = md5(json_encode($HashList) . date('Ymd'));
        }
        return $this->Hash;
    }

    /**
     * @return $this
     */
    public function disableSubmitAction()
    {
        $this->DisableSubmitAction = true;
        return $this;
    }

    /**
     * @param Pipeline $Pipeline
     * @return $this
     */
    public function ajaxPipelineOnSubmit(Pipeline $Pipeline)
    {

        $this->Template->setVariable('AjaxEventSubmit', $Pipeline->parseScript($this));
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->Data;
    }
}
