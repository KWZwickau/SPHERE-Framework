<?php
namespace SPHERE\Common\Frontend\Form\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class AbstractField
 *
 * @package SPHERE\Common\Frontend\Form\Repository
 */
abstract class AbstractField extends Extension implements IFieldInterface
{

    /** @var string $Name */
    protected $Name;
    /** @var IBridgeInterface $Template */
    protected $Template = null;
    /** @var bool $isForceDefaultValue */
    protected $isForceDefaultValue = false;

    protected $ErrorMessage = array();
    protected $SuccessMessage = array();

    protected $TemplateVariableList = array();

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @param string $Key
     * @param mixed $Value
     * @return $this
     */
    protected function setTemplateVariable( $Key, $Value )
    {
        $this->TemplateVariableList[$Key] = $Value;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $this->setPostValue($this->Template, $this->getName(), 'ElementValue');
        foreach( $this->TemplateVariableList as $Key => $Value ) {
            $this->Template->setVariable( $Key, $Value );
        }
        return $this->Template->getContent();
    }

    /**
     * @param IBridgeInterface $Template
     * @param string           $RequestKey
     * @param string           $VariableName
     *
     * @return AbstractField
     */
    protected function setPostValue(IBridgeInterface &$Template, $RequestKey, $VariableName)
    {

        if (preg_match('!^(.*?)\[(.*?)\]$!is', $RequestKey, $Match)) {
            if (false === strpos($Match[2], '[')) {
                if (isset( $this->getGlobal()->POST[$Match[1]][$Match[2]] )) {
                    $Template->setVariable($VariableName,
                        htmlentities($this->getGlobal()->POST[$Match[1]][$Match[2]], ENT_QUOTES));
                } elseif (isset( $this->getGlobal()->GET[$Match[1]][$Match[2]] )) {
                    $Template->setVariable($VariableName,
                        htmlentities($this->getGlobal()->GET[$Match[1]][$Match[2]], ENT_QUOTES));
                }
            } else {
                /**
                 * Next dimension
                 */
                if (preg_match_all('!\]\[!is', $Match[2]) == 1) {
                    $Key = explode('][', $Match[2]);
                    if (isset( $this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]] )) {
                        $Template->setVariable($VariableName,
                            htmlentities($this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]],
                                ENT_QUOTES));
                    } elseif (isset( $this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]] )) {
                        $Template->setVariable($VariableName,
                            htmlentities($this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]],
                                ENT_QUOTES));
                    }
                } else {
                    /**
                     * Next dimension
                     */
                    if (preg_match_all('!\]\[!is', $Match[2]) == 2) {
                        $Key = explode('][', $Match[2]);
                        if (isset( $this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]][$Key[2]] )) {
                            $Template->setVariable($VariableName,
                                htmlentities($this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]][$Key[2]],
                                    ENT_QUOTES));
                        } elseif (isset( $this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]][$Key[2]] )) {
                            $Template->setVariable($VariableName,
                                htmlentities($this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]][$Key[2]],
                                    ENT_QUOTES));
                        }
                    } else {
                        /**
                         * Next dimension
                         */
                    }
                }
            }
        } else {
            if (isset( $this->getGlobal()->POST[$RequestKey] )) {
                $Template->setVariable($VariableName,
                    htmlentities($this->getGlobal()->POST[$RequestKey], ENT_QUOTES));
            } elseif (isset( $this->getGlobal()->GET[$RequestKey] )) {
                $Template->setVariable($VariableName,
                    htmlentities($this->getGlobal()->GET[$RequestKey], ENT_QUOTES));
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param string         $Message
     * @param IIconInterface $Icon
     */
    public function setError($Message, IIconInterface $Icon = null)
    {
        if( $this->Template ) {
            $this->setTemplateVariable('ElementGroup', 'has-error has-feedback');
            if (null !== $Icon) {
                $this->setTemplateVariable('ElementFeedbackIcon',
                    '<span class="' . $Icon->getValue() . ' form-control-feedback"></span>');
            }
            $this->setTemplateVariable('ElementFeedbackMessage',
                '<span class="help-block text-left">' . $Message . '</span>');
        } else {
            $this->ErrorMessage['ElementGroup'] = 'has-error has-feedback';
            $this->ErrorMessage['ElementFeedbackMessage'] = '<span class="help-block text-left">' . $Message . '</span>';
            if (null !== $Icon) {
                $this->ErrorMessage['ElementFeedbackIcon'] = '<span class="' . $Icon->getValue() . ' form-control-feedback"></span>';
            }
        }
    }

    /**
     * @param string         $Message
     * @param IIconInterface $Icon
     */
    public function setSuccess($Message, IIconInterface $Icon = null)
    {

        if( $this->Template ) {
            $this->setTemplateVariable('ElementGroup', 'has-success has-feedback');
            if (null !== $Icon) {
                $this->setTemplateVariable('ElementFeedbackIcon',
                    '<span class="' . $Icon->getValue() . ' form-control-feedback"></span>');
            }
            $this->setTemplateVariable('ElementFeedbackMessage',
                '<span class="help-block text-left">' . $Message . '</span>');
        } else {
            $this->SuccessMessage['ElementGroup'] = 'has-success has-feedback';
            $this->SuccessMessage['ElementFeedbackMessage'] =  '<span class="help-block text-left">' . $Message . '</span>';
            if (null !== $Icon) {
                $this->SuccessMessage['ElementFeedbackIcon'] = '<span class="' . $Icon->getValue() . ' form-control-feedback"></span>';
            }
        }
    }

    /**
     * @param mixed $Value
     * @param bool  $Force
     *
     * @return AbstractField
     */
    public function setDefaultValue($Value, $Force = false)
    {

        $Global = $this->getGlobal();
        if ($Force || !isset( $Global->POST[$this->getName()] )) {
            if( $Force ) {
                $this->isForceDefaultValue = true;
            }
            $Global->POST[$this->getName()] = $Value;
            $Global->savePost();
        }

        return $this;
    }

    /**
     * @param mixed $Value
     *
     * @return AbstractField
     */
    public function setPrefixValue($Value)
    {

        $this->setTemplateVariable('ElementPrefix', $Value);
        return $this;
    }

    /**
     * @param string           $RequestKey
     * @param                  $Value
     *
     * @return bool
     */
    public function isChecked($RequestKey, $Value)
    {

        if (preg_match('!^(.*?)\[(.*?)\]$!is', $RequestKey, $Match)) {
            if (false === strpos($Match[2], '[')) {
                if (
                    isset( $this->getGlobal()->POST[$Match[1]][$Match[2]] )
                    && $this->getGlobal()->POST[$Match[1]][$Match[2]] == $Value
                ) {
                    return true;
                } elseif (
                    isset( $this->getGlobal()->GET[$Match[1]][$Match[2]] )
                    && $this->getGlobal()->GET[$Match[1]][$Match[2]] == $Value
                ) {
                    return true;
                }
            } else {
                /**
                 * Next dimension
                 */
                if (preg_match_all('!\]\[!is', $Match[2]) == 1) {
                    $Key = explode('][', $Match[2]);
                    if (
                        isset( $this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]] )
                        && $this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]] == $Value
                    ) {
                        return true;
                    } elseif (
                        isset( $this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]] )
                        && $this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]] == $Value
                    ) {
                        return true;
                    }
                } else {
                    /**
                     * Next dimension
                     */
                    if (preg_match_all('!\]\[!is', $Match[2]) == 2) {
                        $Key = explode('][', $Match[2]);
                        if (
                            isset( $this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]][$Key[2]] )
                            && $this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]][$Key[2]] == $Value
                        ) {
                            return true;
                        } elseif (
                            isset( $this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]][$Key[2]] )
                            && $this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]][$Key[2]] == $Value
                        ) {
                            return true;
                        }
                    } else {
                        /**
                         * Next dimension
                         */
                    }
                }
            }
        } else {
            if (
                isset( $this->getGlobal()->POST[$RequestKey] )
                && $this->getGlobal()->POST[$RequestKey] == $Value
            ) {
                return true;
            } elseif (
                isset( $this->getGlobal()->GET[$RequestKey] )
                && $this->getGlobal()->GET[$RequestKey] == $Value
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return AbstractField
     */
    public function setDisabled()
    {

        $this->setTemplateVariable('Disabled', true);
        return $this;
    }

    /**
     * @return AbstractField
     */
    public function setRequired()
    {

        $this->setTemplateVariable('Required', true);
        return $this;
    }

    /**
     * Set Tabulator-Order (Index)
     *
     * @param int $Index
     *
     * @return AbstractField
     */
    public function setTabIndex($Index = 1)
    {

        $this->setTemplateVariable('TabIndex', (int)$Index);
        return $this;
    }

    /**
     * Set Field to automatically Focus on page load
     *
     * @return AbstractField
     */
    public function setAutoFocus()
    {
        $this->setTemplateVariable('AutoFocus', 'autofocus');
        return $this;
    }

    /**
     * @return AbstractField
     */
    public function setInputAlignLeft()
    {
        $this->setTemplateVariable( 'ElementClass', 'text-left' );
        return $this;
    }

    /**
     * @return AbstractField
     */
    public function setInputAlignCenter()
    {
        $this->setTemplateVariable( 'ElementClass', 'text-center' );
        return $this;
    }

    /**
     * @return AbstractField
     */
    public function setInputAlignRight()
    {
        $this->setTemplateVariable( 'ElementClass', 'text-right' );
        return $this;
    }

    /**
     * Alias for ajaxPipelineOnChange
     *
     * @param Pipeline|Pipeline[] $Pipeline
     * @return $this
     */
    public function ajaxPipelineOnClick( $Pipeline ) {
        return $this->ajaxPipelineOnChange( $Pipeline );
    }

    /**
     * @param Pipeline|Pipeline[] $Pipeline
     * @return $this
     */
    public function ajaxPipelineOnChange( $Pipeline )
    {
        $Script = '';
        if( is_array( $Pipeline ) ) {
            foreach( $Pipeline as $Element ) {
                $Script .= $Element->parseScript( $this );
            }
        } else {
            $Script = $Pipeline->parseScript( $this );
        }

        $this->setTemplateVariable('AjaxEventChange', $Script);
        return $this;
    }

    /**
     * @param Pipeline|Pipeline[] $Pipeline
     * @return $this
     */
    public function ajaxPipelineOnKeyUp( $Pipeline )
    {
        $Script = '';
        if( is_array( $Pipeline ) ) {
            foreach( $Pipeline as $Element ) {
                $Script .= $Element->parseScript( $this );
            }
        } else {
            $Script = $Pipeline->parseScript( $this );
        }

        $this->setTemplateVariable('AjaxEventKeyUp', $Script);
        return $this;
    }

    /**
     * @param array $Configuration
     * @return array
     */
    protected function convertLibraryConfiguration( $Configuration ) {
        $Convert = array();
        $Index = array();
        foreach($Configuration as $Key => $Value){
            // Look for values starting with 'function('
            if(strpos($Value, 'function(')===0){
                // Store function string.
                $Convert[] = $Value;
                // Replace function string in $foo with a unique special key.
                $Value = '<%'.$Key.'%>';
                $Configuration[$Key] = $Value;
                // Later on, well look for the value, and replace it.
                $Index[] = '"'.$Value.'"';
            }
        }
        // Now encode the array to json format
        $Configuration = json_encode( $Configuration, JSON_FORCE_OBJECT );
        // Replace the special keys with the original string.
        return str_replace($Index, $Convert, $Configuration);
    }
}
