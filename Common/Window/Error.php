<?php
namespace SPHERE\Common\Window;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\ITemplateInterface;
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
     * @param null $Message
     */
    public function __construct($Code, $Message = null)
    {

        $this->Template = $this->getTemplate(__DIR__ . '/Error.twig');

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
                    new Form(
                        new FormGroup(
                            new FormRow(
                                new FormColumn(array(
                                    (new HiddenField('TicketSubject'))->setDefaultValue(urlencode($Code . ' Account: ' .
                                        (($Account = Account::useService()->getAccountBySession()) ? $Account->getId() : ''))),
                                    (new HiddenField('TicketMessage'))->setDefaultValue(urlencode($Message)),
                                    new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Fehlerbericht senden')
                                ))
                            )
                        )
                        , null, '/Platform/Assistance/Support')
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
