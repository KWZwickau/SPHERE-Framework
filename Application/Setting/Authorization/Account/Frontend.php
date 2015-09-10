<?php
namespace SPHERE\Application\Setting\Authorization\Account;

use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization;
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
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Key;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\PersonKey;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Authorization\Account
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Account
     *
     * @return Stage
     */
    public function frontendAccount($Account = null)
    {

        $Stage = new Stage('Benutzerkonten');
        $Stage->setMessage('Hier können bestehende Benutzerkonten bearbeitet und neue angelegt werden');

        // Account
        $tblAccountAll = Account::useService()->getAccountAll();

        if ($tblAccountAll) {
            array_walk($tblAccountAll, function (TblAccount &$tblAccount) {

                if (
                    $tblAccount->getServiceTblIdentification()->getId() != Account::useService()->getIdentificationByName('System')->getId()
                    && $tblAccount->getServiceTblConsumer()->getId() == Consumer::useService()->getConsumerBySession()->getId()
                ) {

                    $Content = array(
                        ( $tblAccount->getServiceTblIdentification() ? new Lock().' '.$tblAccount->getServiceTblIdentification()->getDescription() : '' )
                        .( $tblAccount->getServiceTblToken() ? ' '.new Key().' '.$tblAccount->getServiceTblToken()->getSerial() : '' )
                    );

                    $tblPersonAll = Account::useService()->getPersonAllByAccount($tblAccount);
                    if ($tblPersonAll) {
                        array_walk($tblPersonAll, function (TblPerson &$tblPerson) {

                            $tblPerson = new Person().' '.$tblPerson->getFullName();
                        });
                        $Content = array_merge($Content, $tblPersonAll);
                    }

                    $tblAuthorizationAll = Account::useService()->getAuthorizationAllByAccount($tblAccount);
                    if ($tblAuthorizationAll) {
                        array_walk($tblAuthorizationAll, function (TblAuthorization &$tblAuthorization) {

                            $tblAuthorization = new Nameplate().' '.$tblAuthorization->getServiceTblRole()->getName();
                        });
                        $Content = array_merge($Content, $tblAuthorizationAll);
                    }

                    $Content = array_filter($Content);
                    $Footer = new PullLeft(
                        new Standard('',
                            '/Setting/Authorization/Account/Edit',
                            new Edit(), array('Id' => $tblAccount->getId()),
                            'Benutzer '.$tblAccount->getUsername().' bearbeiten'
                        )
                        .new Standard('',
                            '/Setting/Authorization/Account/Destroy',
                            new Remove(), array('Id' => $tblAccount->getId()),
                            'Benutzer '.$tblAccount->getUsername().' löschen'
                        )
                    );
                    $tblAccount = new LayoutColumn(
                        new Panel($tblAccount->getUsername(), $Content, Panel::PANEL_TYPE_INFO, new PullClear($Footer))
                        , 3);
                } else {
                    $tblAccount = false;
                }
            });
            $tblAccountAll = array_filter($tblAccountAll);
        }
        if ($tblAccountAll) {
            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;
            /**
             * @var LayoutColumn $tblAccount
             */
            foreach ($tblAccountAll as $tblAccount) {
                if ($LayoutRowCount % 4 == 0) {
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                }
                $LayoutRow->addColumn($tblAccount);
                $LayoutRowCount++;
            }
        } else {
            $LayoutRowList = new LayoutRow(
                new LayoutColumn(
                    new Warning('Keine Benutzerkonten vorhanden')
                )
            );
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    $LayoutRowList
                    , new Title('Benutzer')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Account::useService()->createAccount(
                                $this->formAccount()
                                    ->appendFormButton(new Primary('Benutzerkonto hinzufügen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $Account)
                        )
                    ), new Title('Benutzerkonto hinzufügen')
                ),
            ))
        );

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
                switch (strtoupper($tblIdentification->getName())) {
                    case 'STUDENT':
                        $Global = $this->getGlobal();
                        if (!isset( $Global->POST['Account']['Identification'] )) {
                            $Global->POST['Account']['Identification'] = $tblIdentification->getId();
                            $Global->savePost();
                        }
                        $Label = $tblIdentification->getDescription();
                        break;
                    default:
                        $Label = $tblIdentification->getDescription().' ('.new Key().')';
                }
                $tblIdentification = new RadioBox(
                    'Account[Identification]', $Label, $tblIdentification->getId()
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
        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Account']['Token'] )) {
            $Global->POST['Account']['Token'] = 0;
            $Global->savePost();
        }

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
                0
            )
        );

        // Person
        $tblPersonAll = Account::useService()->getPersonAllHavingNoAccount();
        array_walk($tblPersonAll, function (TblPerson &$tblPerson) {

            $tblPerson = new RadioBox('Account[User]', $tblPerson->getFullName(), $tblPerson->getId());
        });
        $tblPersonAll = array_filter($tblPersonAll);

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel(new PersonKey().' Benutzerkonto hinzufügen', array(
                            (new TextField('Account[Name]', 'Benutzername', 'Benutzername', new Person()))
                                ->setPrefixValue($tblConsumer->getAcronym()),
                            new PasswordField(
                                'Account[Password]', 'Passwort', 'Passwort', new Lock()),
                            new PasswordField(
                                'Account[PasswordSafety]', 'Passwort wiederholen', 'Passwort wiederholen',
                                new Repeat()
                            ),
                        ), Panel::PANEL_TYPE_INFO), 4),
                    new FormColumn(array(
                        new Panel(new Nameplate().' Berechtigungsstufe zuweisen', $tblRoleAll, Panel::PANEL_TYPE_INFO),
                        new Panel(new Person().' Person zuweisen', $tblPersonAll, Panel::PANEL_TYPE_INFO, null, true)
                    ), 4),
                    new FormColumn(array(
                        new Panel(new Lock().' Authentifizierungstyp wählen', $tblIdentificationAll,
                            Panel::PANEL_TYPE_INFO),
                        new Panel(new Key().' Hardware-Token zuweisen', $tblTokenAll, Panel::PANEL_TYPE_INFO)
                    ), 4),
                ))

            )),
        ));
    }
}
