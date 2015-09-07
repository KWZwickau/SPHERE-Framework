<?php
namespace SPHERE\Application\Setting\Authorization\Account;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Authorization\Account
 */
class Frontend implements IFrontendInterface
{

    /**
     * @param null $Account
     *
     * @return Stage
     */
    public function frontendAccount($Account = null)
    {

        $Stage = new Stage('Benutzerkonnten');
        $Stage->setMessage('Bestehende Benutzerkonnten');

        // Account
        $tblAccountAll = Account::useService()->getAccountAll();
//        array_walk($tblAccountAll, function (TblAccount &$tblAccount) {
//
//            /** @noinspection PhpUndefinedFieldInspection */
//            $tblAccount->Option = new Danger('Löschen',
//                '/Platform/Gatekeeper/Authorization/Account/Destroy',
//                new Remove(), array('Id' => $tblAccount->getId()), 'Löschen'
//            );
//        });

        $Stage->setContent(
            ( $tblAccountAll
                ? new TableData($tblAccountAll, null, array(
                    'Username' => 'Benutzername',
//                    'Option' => 'Optionen'
                ))
                : new Warning('Keine Benutzerkonnten vorhanden')
            )
            .Account::useService()->createAccount(
                $this->formAccount()
                    ->appendFormButton(new Primary('Benutzerkonnto hinzufügen'))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                , $Account));
        return $Stage;
    }

    /**
     * @return Form
     */
    private function formAccount()
    {

        $tblConsumer = Consumer::useService()->getConsumerBySession();

        // Identification
        $tblIdentificationAll = Account::useService()->getIdentificationAll();
        array_walk($tblIdentificationAll, function (TblIdentification &$tblIdentification) {

            if ($tblIdentification->getName() == 'System') {
                $tblIdentification = false;
            } else {
                $tblIdentification = new RadioBox(
                    'Account[Identification]', $tblIdentification->getDescription(), $tblIdentification->getId()
                );
            }
        });
        $tblIdentificationAll = array_filter($tblIdentificationAll);

        // Role
        $tblRoleAll = Access::useService()->getRoleAll();
        array_walk($tblRoleAll, function (TblRole &$tblRole) {

            if ($tblRole->getName() == 'Administrator') {
                $tblRole = false;
            } else {
                $tblRole = new CheckBox('Account[Role]['.$tblRole->getId().']', $tblRole->getName(),
                    $tblRole->getId());
            }
        });
        $tblRoleAll = array_filter($tblRoleAll);

        // Token
        $tblTokenAll = Token::useService()->getTokenAll();
        array_walk($tblTokenAll, function (TblToken &$tblToken) {

            if (Account::useService()->getAccountAllByToken($tblToken)) {
                $tblToken = false;
            } else {
                $tblToken = new RadioBox('Account[Token]',
                    implode(' ', str_split($tblToken->getSerial(), 4)), $tblToken->getId());
            }
        });
        $tblTokenAll = array_filter($tblTokenAll);
        array_unshift($tblTokenAll,
            new RadioBox('Account[Token]',
                new Danger('KEIN Hardware-Token'),
                null
            )
        );

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Benutzerkonto hinzufügen', array(
                            (new TextField('Account[Name]', 'Benutzername', 'Benutzername', new Person()))
                                ->setPrefixValue($tblConsumer->getAcronym()),
                            new PasswordField(
                                'Account[Password]', 'Passwort', 'Passwort', new Lock()),
                            new PasswordField(
                                'Account[PasswordSafety]', 'Passwort wiederholen', 'Passwort wiederholen',
                                new Repeat()
                            ),
                        ), Panel::PANEL_TYPE_INFO), 3),
                    new FormColumn(array(
                        new Panel('Berechtigungsstufe zuweisen', $tblRoleAll, Panel::PANEL_TYPE_INFO)
                    ), 3),
                    new FormColumn(array(
                        new Panel('Authentifizierungstyp wählen', $tblIdentificationAll, Panel::PANEL_TYPE_INFO)
                    ), 3),
                    new FormColumn(array(
                        new Panel('Hardware-Token zuweisen', $tblTokenAll, Panel::PANEL_TYPE_INFO)
                    ), 3),
                ))

            )),
        ));
    }
}
