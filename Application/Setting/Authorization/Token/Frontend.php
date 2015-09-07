<?php
namespace SPHERE\Application\Setting\Authorization\Token;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Authorization\Token
 */
class Frontend implements IFrontendInterface
{

    /**
     * @param null|string $CredentialKey
     *
     * @return Stage
     */
    public function frontendYubiKey($CredentialKey = null)
    {

        $Stage = new Stage('Hardware-Token', 'YubiKey');
        $Stage->setMessage('Bestehende Hardware-Token');
        $tblTokenAll = Token::useService()->getTokenAllByConsumer(Consumer::useService()->getConsumerBySession());

        array_walk($tblTokenAll, function (TblToken &$tblToken) {

            /** @noinspection PhpUndefinedFieldInspection */
            $tblToken->Name = strtoupper($tblToken->getIdentifier());
            strtoupper($tblToken->getIdentifier());
            if ($tblToken->getSerial() % 2 != 0) {
                /** @noinspection PhpUndefinedFieldInspection */
                $tblToken->Number = '0'.$tblToken->getSerial();
            } else {
                /** @noinspection PhpUndefinedFieldInspection */
                $tblToken->Number = $tblToken->getSerial();
            }
            /** @noinspection PhpUndefinedFieldInspection */
            $tblToken->Number = substr($tblToken->Number, 0, 4).' '.substr($tblToken->Number, 4, 4);
        });
        $Stage->setContent(
            ( $tblTokenAll
                ? new TableData($tblTokenAll, null, array(
                    'Number' => 'Seriennummer',
                    'Name'   => 'Name',
                ))
                : new Warning('Keine Hardware-Token vorhanden')
            )
            .Token::useService()->createToken(
                $this->formYubiKey()
                    ->appendFormButton(new Primary('Hardware-Token hinzufügen'))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                , $CredentialKey, Consumer::useService()->getConsumerBySession())
        );
        return $Stage;
    }

    /**
     * @return Form
     */
    private function formYubiKey()
    {

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('Hardware-Token hinzufügen', array(
                            new PasswordField('CredentialKey', 'YubiKey', 'YubiKey'),
                        ), Panel::PANEL_TYPE_INFO)
                    )),
                )),
            ))
        );
    }

}
