<?php
namespace SPHERE\Common\Window;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Configuration;

/**
 * Class Error
 *
 * @package SPHERE\Common\Window
 */
class Error extends Configuration implements ITemplateInterface
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /**
     * @param integer|string $Code
     * @param null           $Message
     */
    function __construct( $Code, $Message = null )
    {

        $this->Template = $this->getTemplate( __DIR__.'/Error.twig' );

        $this->Template->setVariable( 'ErrorCode', $Code );
        if (null === $Message) {
            switch ($Code) {
                case 404:
                    $this->Template->setVariable( 'ErrorMessage',
                        'Die angeforderte Ressource konnte nicht gefunden werden' );
                    break;
                default:
                    $this->Template->setVariable( 'ErrorMessage', '' );
            }
        } else {
            $this->Template->setVariable( 'ErrorMessage', $Message );
//            $this->Template->setVariable( 'ErrorMenu', array(
//                $this->extensionRequest()->getUrlBase()
//                .'/'.trim( '/Sphere/Assistance/Support/Ticket'
//                    .'?TicketSubject='.urlencode( $Code )
//                    .'&TicketMessage='.urlencode( $Message ),
//                    '/' ) => 'Fehlerbericht senden'
//            ) );
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

        return $this->Template->getContent();
    }
}
