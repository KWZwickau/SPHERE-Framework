<?php
namespace SPHERE\Common\Frontend\Form\Repository\Field;

use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Template;
use SPHERE\Common\Frontend\Form\IFieldInterface;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class SelectBox
 *
 * @package SPHERE\Common\Frontend\Form\Repository\Field
 */
class SelectBox extends Extension implements IFieldInterface
{

    /** @var string $Name */
    protected $Name;
    /** @var IBridgeInterface $Template */
    protected $Template = null;

    /**
     * @param string         $Name
     * @param null|string    $Label
     * @param array          $Data array( value => title )
     * @param IIconInterface $Icon
     */
    public function __construct(
        $Name,
        $Label = '',
        $Data = array(),
        IIconInterface $Icon = null
    ) {

        $this->Name = $Name;
        $this->Template = $this->getTemplate( __DIR__.'/SelectBox.twig' );
        $this->Template->setVariable( 'ElementName', $Name );
        $this->Template->setVariable( 'ElementLabel', $Label );
        // Data is Entity-List ?
        if (count( $Data ) == 1 && !is_numeric( key( $Data ) )) {
            $Attribute = key( $Data );
            $Convert = array();
            // Attribute is Twig-Template ?
            if (preg_match_all( '/\{\%\s*(.*)\s*\%\}|\{\{(?!%)\s*((?:[^\s])*)\s*(?<!%)\}\}/i',
                $Attribute,
                $Placeholder )
            ) {
                /** @var AbstractEntity $Entity */
                foreach ((array)$Data[$Attribute] as $Entity) {
                    if (is_object( $Entity )) {
                        if ($Entity->getId() === null) {
                            $Entity->setId( 0 );
                        }
                        $Template = Template::getTwigTemplateString( $Attribute );
                        foreach ((array)$Placeholder[2] as $Variable) {
                            $Chain = explode( '.', $Variable );
                            if (count( $Chain ) > 1) {
                                $Template->setVariable( $Chain[0], $Entity->{'get'.$Chain[0]}() );
                            } else {
                                if (method_exists( $Entity, 'get'.$Variable )) {
                                    $Template->setVariable( $Variable, $Entity->{'get'.$Variable}() );
                                } else {
                                    if (property_exists( $Entity, $Variable )) {
                                        $Template->setVariable( $Variable, $Entity->{$Variable} );
                                    } else {
                                        $Template->setVariable( $Variable, null );
                                    }
                                }
                            }
                        }
                        $Convert[$Entity->getId()] = $Template->getContent();
                    }
                }
            } else {
                /** @var AbstractEntity $Entity */
                foreach ((array)$Data[$Attribute] as $Entity) {
                    if (is_object( $Entity )) {
                        if ($Entity->getId() === null) {
                            $Entity->setId( 0 );
                        }
                        if (method_exists( $Entity, 'get'.$Attribute )) {
                            $Convert[$Entity->getId()] = $Entity->{'get'.$Attribute}();
                        } else {
                            $Convert[$Entity->getId()] = $Entity->{$Attribute};
                        }
                    }
                }
            }
            if (array_key_exists( 0, $Convert )) {
                $Convert[0] = '-[ Nicht ausgewählt ]-';
            }
            asort( $Convert );
            $this->Template->setVariable( 'ElementData', $Convert );
        } else {
            if (array_key_exists( 0, $Data )) {
                $Data[0] = '-[ Nicht ausgewählt ]-';
            }
            asort( $Data );
            $this->Template->setVariable( 'ElementData', $Data );
        }
        if (null !== $Icon) {
            $this->Template->setVariable( 'ElementIcon', $Icon );
        }
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

        $this->setPostValue( $this->Template, $this->getName(), 'ElementValue' );
        return $this->Template->getContent();
    }

    /**
     * @param IBridgeInterface $Template
     * @param string           $RequestKey
     * @param string           $VariableName
     */
    public function setPostValue( IBridgeInterface &$Template, $RequestKey, $VariableName )
    {

        if (preg_match( '!^(.*?)\[(.*?)\]$!is', $RequestKey, $Match )) {
            if (false === strpos( $Match[2], '[' )) {
                if (isset( $this->getGlobal()->POST[$Match[1]][$Match[2]] )) {
                    $Template->setVariable( $VariableName,
                        htmlentities( $this->getGlobal()->POST[$Match[1]][$Match[2]], ENT_QUOTES ) );
                } elseif (isset( $this->getGlobal()->GET[$Match[1]][$Match[2]] )) {
                    $Template->setVariable( $VariableName,
                        htmlentities( $this->getGlobal()->GET[$Match[1]][$Match[2]], ENT_QUOTES ) );
                }
            } else {
                /**
                 * Next dimension
                 */
                if (preg_match_all( '!\]\[!is', $Match[2] ) == 1) {
                    $Key = explode( '][', $Match[2] );
                    if (isset( $this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]] )) {
                        $Template->setVariable( $VariableName,
                            htmlentities( $this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]],
                                ENT_QUOTES ) );
                    } elseif (isset( $this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]] )) {
                        $Template->setVariable( $VariableName,
                            htmlentities( $this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]],
                                ENT_QUOTES ) );
                    }
                } else {
                    /**
                     * Next dimension
                     */
                    if (preg_match_all( '!\]\[!is', $Match[2] ) == 2) {
                        $Key = explode( '][', $Match[2] );
                        if (isset( $this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]][$Key[2]] )) {
                            $Template->setVariable( $VariableName,
                                htmlentities( $this->getGlobal()->POST[$Match[1]][$Key[0]][$Key[1]][$Key[2]],
                                    ENT_QUOTES ) );
                        } elseif (isset( $this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]][$Key[2]] )) {
                            $Template->setVariable( $VariableName,
                                htmlentities( $this->getGlobal()->GET[$Match[1]][$Key[0]][$Key[1]][$Key[2]],
                                    ENT_QUOTES ) );
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
                $Template->setVariable( $VariableName,
                    htmlentities( $this->getGlobal()->POST[$RequestKey], ENT_QUOTES ) );
            } elseif (isset( $this->getGlobal()->GET[$RequestKey] )) {
                $Template->setVariable( $VariableName,
                    htmlentities( $this->getGlobal()->GET[$RequestKey], ENT_QUOTES ) );
            }
        }
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
    public function setError( $Message, IIconInterface $Icon = null )
    {

        $this->Template->setVariable( 'ElementGroup', 'has-error has-feedback' );
        if (null !== $Icon) {
            $this->Template->setVariable( 'ElementFeedbackIcon',
                '<span class="'.$Icon->getValue().' form-control-feedback"></span>' );
        }
        $this->Template->setVariable( 'ElementFeedbackMessage',
            '<span class="help-block text-left">'.$Message.'</span>' );
    }

    /**
     * @param string         $Message
     * @param IIconInterface $Icon
     */
    public function setSuccess( $Message, IIconInterface $Icon = null )
    {

        $this->Template->setVariable( 'ElementGroup', 'has-success has-feedback' );
        if (null !== $Icon) {
            $this->Template->setVariable( 'ElementFeedbackIcon',
                '<span class="'.$Icon->getValue().' form-control-feedback"></span>' );
        }
        $this->Template->setVariable( 'ElementFeedbackMessage',
            '<span class="help-block text-left">'.$Message.'</span>' );
    }

    /**
     * @param mixed $Value
     * @param bool  $Force
     *
     * @return TextField
     */
    public function setDefaultValue( $Value, $Force = false )
    {

        $Global = $this->getGlobal();
        if ($Force || !isset( $Global->POST[$this->getName()] )) {
            $Global->POST[$this->getName()] = $Value;
            $Global->savePost();
        }

        return $this;
    }

    /**
     * @param mixed $Value
     */
    public function setPrefixValue( $Value )
    {

        $this->Template->setVariable( 'ElementPrefix', $Value );
    }
}
