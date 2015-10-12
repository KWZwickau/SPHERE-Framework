<?php
namespace SPHERE\Common\Window;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\System\Extension\Extension;

/**
 * Class Error
 *
 * @package SPHERE\Common\Window
 */
class Error extends Extension implements ITemplateInterface
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /**
     * @param integer|string $Code
     * @param null           $Message
     */
    public function __construct($Code, $Message = null)
    {

        $this->Template = $this->getTemplate(__DIR__.'/Error.twig');

        $this->Template->setVariable('ErrorCode', $Code);
        if (null === $Message) {
            switch ($Code) {
                case 404:
                    $this->Template->setVariable('ErrorMessage',
                        'Die angeforderte Ressource konnte nicht gefunden werden');
                    break;
                default:
                    $this->Template->setVariable('ErrorMessage', '');
            }
        } else {
            $this->Template->setVariable('ErrorMessage', $Message);
            $this->Template->setVariable('ErrorMenu', array(
                    new Primary('Fehlerbericht senden', '/System/Assistance/Support/Ticket', null,
                        array(
                            'TicketSubject' => urlencode($Code),
                            'TicketMessage' => urlencode($Message)
                        )
                    )
                )
            );
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
