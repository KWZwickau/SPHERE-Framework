<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Account;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
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
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Account
 */
class Frontend
{

    /**
     * @return Stage
     */
    public function frontendAccount()
    {

        $Stage = new Stage('Benutzerkonnten');

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $isSystem = Account::useService()->hasAuthorization(
                $tblAccount, Access::useService()->getRoleByName('Administrator')
            );
        } else {
            $isSystem = false;
        }
        $tblConsumer = Consumer::useService()->getConsumerBySession();
        // Token
        $tblTokenAll = Token::useService()->getTokenAll();
        if ($tblTokenAll) {
            array_walk($tblTokenAll, function (TblToken &$tblToken) {

                if (Account::useService()->getAccountAllByToken($tblToken)) {
                    $tblToken = false;
                } else {
                    $tblToken = new RadioBox('Account[Token]',
                        implode(' ', str_split($tblToken->getSerial(), 4)), $tblToken->getId());
                }
            });
            $tblTokenAll = array_filter($tblTokenAll);
        } else {
            $tblTokenAll = array();
        }
        array_unshift($tblTokenAll,
            new RadioBox('Account[Token]',
                new \SPHERE\Common\Frontend\Text\Repository\Danger('KEIN Hardware-Schlüssel'),
                null
            )
        );

        // Identification
        $tblIdentificationAll = Account::useService()->getIdentificationAll();
        if ($tblIdentificationAll) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblIdentificationAll, function (TblIdentification &$tblIdentification, $Index, $isSystem) {

                if ($tblIdentification->getName() == 'System' && !$isSystem) {
                    $tblIdentification = false;
                } else {
                    $tblIdentification = new RadioBox(
                        'Account[Identification]', $tblIdentification->getDescription(), $tblIdentification->getId()
                    );
                }
            }, $isSystem);
            $tblIdentificationAll = array_filter($tblIdentificationAll);
        } else {
            $tblIdentificationAll = array();
        }

        // Role
        $tblRoleAll = Access::useService()->getRoleAll();
        if ($tblRoleAll) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblRoleAll, function (TblRole &$tblRole, $Index, $isSystem) {

                if ($tblRole->getName() == 'Administrator' && !$isSystem) {
                    $tblRole = false;
                } else {
                    $tblRole = new CheckBox('Account[Role]['.$tblRole->getId().']', $tblRole->getName(),
                        $tblRole->getId());
                }
            }, $isSystem);
            $tblRoleAll = array_filter($tblRoleAll);
        } else {
            $tblRoleAll = array();
        }
        // Account
        $tblAccountAll = Account::useService()->getAccountAll();
        if ($tblAccountAll) {
            array_walk($tblAccountAll, function (TblAccount &$tblAccount) {

                /** @noinspection PhpUndefinedFieldInspection */
                $tblAccount->Option = new Danger('Löschen',
                    '/Platform/Gatekeeper/Authorization/Account/Destroy',
                    new Remove(), array('Id' => $tblAccount->getId()), 'Löschen'
                );
            });
        }

        $Stage->setContent(
            ( $tblAccountAll
                ? new TableData($tblAccountAll, new Title('Bestehende Benutzerkonnten'), array(
                    'Username' => 'Benutzername',
//                    'Option' => 'Optionen'
                ))
                : new Warning('Keine Benutzerkonnten vorhanden')
            )
            //.Account::useService()->createAccount(
            .new Form(array(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(
                            (new TextField('Account[Name]', 'Benutzername', 'Benutzername', new Person()))
                                ->setPrefixValue($tblConsumer->getAcronym())
                            , 4),
                        new FormColumn(
                            new PasswordField(
                                'Account[Password]', 'Passwort', 'Passwort', new Lock()
                            ), 4),
                        new FormColumn(
                            new PasswordField(
                                'Account[PasswordSafety]', 'Passwort wiederholen', 'Passwort wiederholen',
                                new Repeat()
                            ), 4),
                    )),
                ), new \SPHERE\Common\Frontend\Form\Repository\Title('Benutzerkonnto anlegen')),
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(array(
                            new Panel('Authentifizierungstyp', $tblIdentificationAll)
                        ), 4),
                        new FormColumn(array(
                            new Panel('Berechtigungsstufe', $tblRoleAll)
                        ), 4),
                        new FormColumn(array(
                            new Panel('Hardware-Schlüssel', $tblTokenAll)
                        ), 4),
                    ))

                ), new \SPHERE\Common\Frontend\Form\Repository\Title('Berechtigungen zuweisen')),
            ), new Primary('Hinzufügen'))
        );
        return $Stage;
    }
}
