<?php
namespace SPHERE\Common\Frontend\Form\Structure;

use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\System\Authenticator\Configuration as Authenticator;
use SPHERE\System\Authenticator\Type\Get;
use SPHERE\System\Extension\Configuration;

/**
 * Class Form
 *
 * @package SPHERE\Common\Frontend\Form\Structure
 */
class Form extends Configuration implements IFormInterface
{

    /**
     * @param FormGroup|FormGroup[]                $FormGroup
     * @param null|AbstractButton|AbstractButton[] $AbstractButton
     * @param string                               $FormAction
     * @param array                                $FormData
     */
    public function __construct( $FormGroup, $AbstractButton = null, $FormAction = '', $FormData = array() )
    {

        if (!is_array( $FormGroup )) {
            $FormGroup = array( $FormGroup );
        }
        $this->GridGroupList = $FormGroup;

        if (!is_array( $AbstractButton ) && null !== $AbstractButton) {
            $AbstractButton = array( $AbstractButton );
        } elseif (empty( $AbstractButton )) {
            $AbstractButton = array();
        }
        $this->GridButtonList = $AbstractButton;

        $this->Template = $this->getTemplate( __DIR__.'/Form.twig' );
//        $this->Template->setVariable( 'UrlBase', $this->extensionRequest()->getUrlBase() );
        if (!empty( $FormData )) {
            $this->Template->setVariable( 'FormAction', $this->getRequest()->getUrlBase().$FormAction );
            $this->Template->setVariable( 'FormData', '?'.http_build_query(
                    ( new Authenticator( new Get() ) )->getAuthenticator()->createSignature(
                        $FormData, $FormAction
                    )
                ) );
        } else {
            if (empty( $FormAction )) {
                $this->Template->setVariable( 'FormAction', $FormAction );
            } else {
                $this->Template->setVariable( 'FormAction', $this->getRequest()->getUrlBase().$FormAction );
            }
        }
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $this->Template->setVariable( 'FormButtonList', $this->GridButtonList );
        $this->Template->setVariable( 'GridGroupList', $this->GridGroupList );
        $this->Template->setVariable( 'Hash', $this->getHash() );
        return $this->Template->getContent();
    }

}
