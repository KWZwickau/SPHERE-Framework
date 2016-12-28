<?php
namespace SPHERE\Common\Frontend\Ajax\Receiver;

use MOC\V\Component\Template\Component\IBridgeInterface;

/**
 * Class ModalReceiver
 *
 * @package SPHERE\Common\Frontend\Ajax\Receiver
 */
class ModalReceiver extends AbstractReceiver
{
    /** @var IBridgeInterface|null $Template */
    private $Template = null;

    /**
     * ModalReceiver constructor.
     * @param string|null $Header
     * @param string|null $Footer
     */
    public function __construct($Header = null, $Footer = null)
    {
        $this->Template = $this->getTemplate(__DIR__ . '/ModalReceiver.twig');
        $this->Template->setVariable('Header', $Header);
        $this->Template->setVariable('Footer', $Footer);
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getHandler()
    {
        return 'jQuery("#' . $this->getSelector() . '").find("div.modal-body").html(' . self::RESPONSE_CONTAINER . ');  jQuery("#' . $this->getSelector() . '").modal();';
    }

    /**
     * @return mixed
     */
    public function getSelector()
    {
        return $this->getIdentifier();
    }

    /**
     * @return string
     */
    public function getContainer()
    {
        $this->Template->setVariable('IDENTIFIER', $this->getIdentifier());
        return $this->Template->getContent();
    }
}
