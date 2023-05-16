<?php
namespace SPHERE\Application\Setting\Authorization\Account;

use SPHERE\Application\Api\Platform\Gatekeeper\ApiAuthenticatorApp;
use SPHERE\Application\Api\Setting\Authorization\ApiAccount;
use SPHERE\Application\Api\Setting\Authorization\ApiGroupRole;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Globe;
use SPHERE\Common\Frontend\Icon\Repository\Key;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\PersonKey;
use SPHERE\Common\Frontend\Icon\Repository\PersonParent;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\QrCode;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\CustomPanel;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Repository\WellReadOnly;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\ToggleCheckbox;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
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
     * @return Stage
     */
    public function frontendLayoutAccount()
    {
        $Stage = new Stage('Benutzerkonten');
        $Stage->setMessage('Hier können neue Nutzerzugänge angelegt und bestehende Benutzerkonten bearbeitet bzw. gelöscht werden');
        $Stage->addButton(new Standard(
            'Neues Benutzerkonto anlegen', '/Setting/Authorization/Account/Create', new PlusSign()
        ));
        $Stage->addButton(
            (new Standard(
                new Nameplate() . ' Einzelnes Benutzerrecht zuweisen',
                ApiAccount::getEndpoint()
            ))->ajaxPipelineOnClick(ApiAccount::pipelineOpenMassReplaceModal())
        );

        // diese Recht wird erst später gesetzt
        if (Access::useService()->hasAuthorization('/Api/Setting/Authorization/ApiAccount')) {
            $content = ApiAccount::receiverModal() . ApiAccount::receiverBlock($this->layoutAccount(), 'LayoutAccountContent');
        } else {
            $content = $this->layoutAccount();
        }

        $Stage->setContent(
            $content
        );
        return $Stage;
    }

    /**
     * @return Layout
     */
    public function layoutAccount()
    {
        $TableContent = array();
        if (($tblAccountConsumerTokenList = Account::useService()->getAccountAllForEdit())) {
            array_walk($tblAccountConsumerTokenList, function (TblAccount $tblAccount) use (&$TableContent) {
                $PersonList = array();
                $tblPersonAll = Account::useService()->getPersonAllByAccount($tblAccount);
                if ($tblPersonAll) {
                    array_walk($tblPersonAll, function (TblPerson $tblPerson) use (&$PersonList){
                        $PersonList[] = $tblPerson->getFullName();
                    });
                }

                $AuthorizationList = array();
                $tblAuthorizationAll = Account::useService()->getAuthorizationAllByAccount($tblAccount);
                if ($tblAuthorizationAll) {
                    array_walk($tblAuthorizationAll, function (TblAuthorization $tblAuthorization) use (&$AuthorizationList){
                        if ($tblAuthorization->getServiceTblRole()) {
                            $AuthorizationList[] = $tblAuthorization->getServiceTblRole()->getName();
                        }
                    });
                    if (!empty($AuthorizationList)) {
                        sort($AuthorizationList);
                    }
                }

                $tblIdentification = $tblAccount->getServiceTblIdentification();

                $Item['Username'] = new Listing(array($tblAccount->getUsername()));
                $Item['Person'] = new Listing(!empty( $PersonList )
                    ? $PersonList
                    : array(new Danger(new Exclamation().new Small(' Keine Person angeben'))));
                $Item['Authentication'] = new Listing(array($tblIdentification
                    ? $tblIdentification->getDescription()
                    : '')
                );

                $isEmpty = false;
                if(empty($AuthorizationList)){
                    $isEmpty = true;
                }
                $Item['Authorization'] = ($isEmpty ? '<span hidden>000</span>' : '<span hidden>'.count($AuthorizationList).'</span>').(new CustomPanel(
                    (! $isEmpty
                        ? 'Anzahl vergebener Benutzerrechte: '.count($AuthorizationList)
                        : new Danger(new Exclamation().new Small(' Keine Berechtigungen vergeben')))
                    , $AuthorizationList))->setHash($tblAccount->getId())->setAccordeon();
                $Item['Token'] = new Listing(array($tblAccount->getServiceTblToken()
                        ? substr($tblAccount->getServiceTblToken()->getSerial(), 0,
                            4).' '.substr($tblAccount->getServiceTblToken()->getSerial(), 4, 4)
                        : new Muted(new Small('Kein Hardware-Schlüssel vergeben'))
                ));
                $Item['Option'] =
                    ApiAuthenticatorApp::receiverModal()
                    . (new Standard('',
                        '/Setting/Authorization/Account/Edit',
                        new Edit(), array('Id' => $tblAccount->getId()),
                        'Benutzer '.$tblAccount->getUsername().' bearbeiten'
                    ))
                    . (new Standard('',
                        '/Setting/Authorization/Account/Destroy',
                        new Remove(), array('Id' => $tblAccount->getId()),
                        'Benutzer '.$tblAccount->getUsername().' löschen'
                    ))
                    . (new External('',
                        'SPHERE\Application\Api\Document\Standard\Account\Create',
                        new Download(),
                        array('AccountId' => $tblAccount->getId()),
                        'Download PDF-Anschreiben'
                    ))
                    . ($tblIdentification && $tblIdentification->getName() == TblIdentification::NAME_AUTHENTICATOR_APP
                        ? (new Standard(
                            '', ApiAuthenticatorApp::getEndpoint(), new Repeat(), array(), 'QR-Code neu erstellen'
                        ))->ajaxPipelineOnClick(ApiAuthenticatorApp::pipelineOpenResetQrCodeModal($tblAccount->getId()))
                        . (new Standard(
                            '', ApiAuthenticatorApp::getEndpoint(), new QrCode(), array(), 'QR-Code anzeigen'
                        ))->ajaxPipelineOnClick(ApiAuthenticatorApp::pipelineOpenShowQrCodeModal($tblAccount->getId()))
                        : ''
                    );
                array_push($TableContent, $Item);
            });
        }

        return new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new TableData(
                            $TableContent,
                            null,
                            array(
                                'Username'       => new PersonKey().' Benutzerkonto',
                                'Person'         => new Person().' Person',
                                'Authentication' => new Lock().' Kontotyp',
                                'Authorization'  => new Nameplate().' Benutzerrechte',
                                'Token'          => new Key().' Hardware-Schlüssel',
                                'Option'         => 'Optionen'
                            ), array(
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => 3),
                                ),
//                                'order'      => array(array(1, 'asc')),
//                                'pageLength' => -1,
//                                'paging'     => false,
//                                'info'       => false,
//                                'searching'  => false,
//                                'responsive' => false
                            )
                        )
                    )
                ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Listing().' Übersicht')
            )
        );
    }

    /**
     * @param null $Account
     *
     * @return Stage
     */
    public function frontendCreateAccount($Account = null)
    {
        $Stage = new Stage('Benutzerkonto', 'Hinzufügen');
        $Stage->addButton(new Standard('Zurück', '/Setting/Authorization/Account', new ChevronLeft()));
        $tblAuthentication = Account::useService()->getIdentificationByName('Token');
        if ($tblAuthentication) {
            $Global = $this->getGlobal();
            $Global->POST['Account']['Identification'] = $tblAuthentication->getId();
            $Global->savePost();
        }
        $Stage->setContent(
            new Layout(array(
                Account::useService()->getGroupRoleLayoutGroup(),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Account::useService()->createAccount(
                                $this->formAccount()
                                    ->appendFormButton(new Primary('Speichern', new Save()))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $Account)
                        ))
                    ), new Title(new PlusSign().' Hinzufügen')
                ),
            ))
        );
        return $Stage;
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return Form
     */
    private function formAccount(TblAccount $tblAccount = null)
    {
        $tblConsumer = Consumer::useService()->getConsumerBySession();

        $tblRoleAll = Account::useService()->getRoleCheckBoxList('Account[Role]');

        // Token
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Account']['Token'])){
            $Global->POST['Account']['Token'] = 0;
            $Global->savePost();
        }

        $tblTokenAll = Token::useService()->getTokenAllByConsumer(Consumer::useService()->getConsumerBySession());
        if ($tblAccount){
            $tblToken = $tblAccount->getServiceTblToken();
        } else {
            $tblToken = false;
        }
        if ($tblTokenAll){
            $tblTokenAll = $this->getSorter($tblTokenAll)->sortObjectBy(TblToken::ATTR_SERIAL);
            array_walk($tblTokenAll, function(TblToken &$tblTokenItem) use ($tblToken){

                if (
                    ($tblToken === false || $tblTokenItem->getId() != $tblToken->getId())
                    && !Account::useService()->getAccountAllByToken($tblTokenItem)
                ){
                    $tblTokenItem = new RadioBox('Account[Token]',
                        implode(' ', str_split($tblTokenItem->getSerial(), 4)), $tblTokenItem->getId());
                } else {
                    $tblTokenItem = false;
                }
            });
            $tblTokenAll = array_filter($tblTokenAll);
        } else {
            $tblTokenAll = array();
        }
        array_unshift($tblTokenAll,
            new RadioBox('Account[Token]',
                new Danger('KEIN Hardware-Schlüssel notwendig'),
                0
            )
        );

        // Authenticator App
        $tblTokenAll[] = new RadioBox('Account[Token]', 'Authenticator App', -1);

        // Token Panel
        if ($tblToken){
            array_unshift($tblTokenAll, new Danger('ODER eine andere Variante auswählen: '));
            array_unshift($tblTokenAll,
                new RadioBox('Account[Token]',
                    implode(' ', str_split($tblToken->getSerial(), 4)), $tblToken->getId())
            );
            array_unshift($tblTokenAll, new Danger('AKTUELL hinterlegter Hardware-Schlüssel, '));
        }

        $PanelToken = new Panel(new Person() . ' mit folgender Authentifizierungsmethode anmelden', $tblTokenAll, Panel::PANEL_TYPE_INFO);

        // Person
        if ($tblAccount){
            $User = Account::useService()->getUserAllByAccount($tblAccount);
        }
        if (!empty($User)){
            $tblPerson = $User[0]->getServiceTblPerson();
        } else {
            $tblPerson = false;
        }

        $tblPersonAll = array();
        if (!$tblPerson){
            $tblGroup = Group::useService()->getGroupByMetaTable('STAFF');
            if ($tblGroup){
                $tblPersonAll = Group::useService()->getPersonAllByGroup($tblGroup);
            }
            if ($tblPersonAll){
                array_walk($tblPersonAll, function(TblPerson &$tblPersonItem) use ($tblPerson, $Global){
                    if($tblPersonItem){
                        $PersonId = $tblPersonItem->getId();
                        $PersonColumn = '<span hidden>'.$tblPersonItem->getLastFirstName().'</span>'
                            .(new Link($tblPersonItem->getLastFirstName(), '/People/Person', new Person(), array('Id' => $PersonId)))->setExternal();
                    } else {
                        $PersonColumn = $tblPersonItem->getLastFirstName();
                    }

                    $tblUserNameList = array();

                    $Account = '';
                    if(($tblAccountList = \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account::useService()->getAccountAllByPerson($tblPersonItem))) {
                        foreach($tblAccountList as $tempAccount){
                            $Icon = new PersonKey();
                            if($tempAccount->getServiceTblIdentification()->getName() == TblIdentification::NAME_USER_CREDENTIAL){
                                $Icon = new PersonParent();
                            }
                            $tblUserNameList[] = '<span hidden>'.$tempAccount->getUsername().'</span> '.$Icon.' '.$tempAccount->getUsername();
                        }
                        $Account = implode(', ', $tblUserNameList);
                    }

                    $tblPersonItem = array(
                        'Select'  => ($Account ? '': new RadioBox('Account[User]', '&nbsp;', $tblPersonItem->getId())),
                        'Person'  => $PersonColumn,
                        'Account' => $Account,
                        'Address' => $tblPersonItem->fetchMainAddress() ? $tblPersonItem->fetchMainAddress()->getGuiTwoRowString() : ''
                    );
                });
                $tblPersonAll = array_filter($tblPersonAll);
            }
        }

        $columns = array(
            'Select' => '',
            'Person' => 'Name',
            'Account' => 'Benutzerkonto',
            'Address' => 'Adresse'
        );

        $interactive =  array(
            'columnDefs' => array(
                array("orderable" => false, 'width' => '20px', "targets" => 0),
            ),
            'order' => array(array(1, 'asc')),
            'responsive' => false,
            'lengthMenu' => [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
            'pageLength' => 5
        );

        // Person Panel
        if ($tblPerson) {
            $PanelPerson = new Panel(new Person().' für folgenden Mitarbeiter', array(
//                new Danger('AKTUELL hinterlegte Person, '),
                new RadioBox('Account[User]', $tblPerson->getFullName(), $tblPerson->getId()),
//                new Danger('ODER eine andere Person wählen: '),
//                new TableData(
//                    $tblPersonAll,
//                    null,
//                    $columns,
//                    $interactive
//                )
            ), Panel::PANEL_TYPE_INFO);
        } elseif (isset( $Global->POST['Account']['User'] )) {
            $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($Global->POST['Account']['User']);
            $PanelPerson = new Panel(new Person().' für folgenden Mitarbeiter', array(
//                new Warning('AKTUELL selektierte Person, '),
                new RadioBox('Account[User]', $tblPerson->getFullName(), $tblPerson->getId()),
//                new Danger('ODER eine andere Person wählen: '),
//                new TableData(
//                    $tblPersonAll,
//                    null,
//                    $columns,
//                    $interactive
//                ),
            ), Panel::PANEL_TYPE_INFO);
        } else {
            $PanelPerson = new Panel(new Person().' für folgenden Mitarbeiter', array(
                new TableData(
                    $tblPersonAll,
                    null,
                    $columns,
                    $interactive
                ),
            ), Panel::PANEL_TYPE_INFO);
        }
        $MaxString = 20;
        if($tblConsumer = Consumer::useService()->getConsumerBySession()){
            $MaxString = $MaxString - strlen($tblConsumer->getAcronym().'-');
        }
        // Username Panel
        if ($tblAccount) {
            $UsernamePanel = new Panel(new PersonKey().' Benutzerkonto', array(
                (new TextField('Account[Name]', 'Benutzername (max. '.$MaxString.' Zeichen)', 'Benutzername',
                    new Person()))
                    ->setPrefixValue($tblConsumer->getAcronym())->setDisabled(),
                new Danger('Die Passwort-Felder nur ausfüllen wenn das Passwort dieses Benutzers geändert werden soll'),
                new PasswordField(
                    'Account[Password]', 'Passwort (min. 8 Zeichen)', 'Passwort', new Lock()),
                new PasswordField(
                    'Account[PasswordSafety]', 'Passwort wiederholen', 'Passwort wiederholen',
                    new Repeat()
                ),
            ), Panel::PANEL_TYPE_INFO);
        } else {
            $UsernamePanel = new Panel(new PersonKey().' Benutzerkonto', array(
                (new TextField('Account[Name]', 'Benutzername (max. '.$MaxString.' Zeichen)', 'Benutzername',
                    new Person()))
                    ->setPrefixValue($tblConsumer->getAcronym()),
                new PasswordField(
                    'Account[Password]', 'Passwort (min. 8 Zeichen)', 'Passwort', new Lock()),
                new PasswordField(
                    'Account[PasswordSafety]', 'Passwort wiederholen', 'Passwort wiederholen',
                    new Repeat()
                ),
            ), Panel::PANEL_TYPE_INFO);
        }

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        $UsernamePanel,
                        $PanelToken,
                        $PanelPerson
                    ), 5),
                    new FormColumn(array(
                        new Panel(new Nameplate().' mit folgenden Benutzerrechten', $tblRoleAll, Panel::PANEL_TYPE_INFO),
//                        $PanelPersonRight,
                    ), 7),
                )),
            )),
        ));
    }

    /**
     * @param null|int   $Id
     * @param null|array $Account
     *
     * @return Stage
     */
    public function frontendUpdateAccount($Id = null, $Account = null)
    {

        $Stage = new Stage('Benutzerkonto', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Setting/Authorization/Account', new ChevronLeft()));
        $tblAccount = Account::useService()->getAccountById($Id);
        if ($tblAccount) {
            $tblIdentification = $tblAccount->getServiceTblIdentification();
            $Global = $this->getGlobal();
            if (!$Global->POST) {
//                $Global->POST['Account']['Identification'] = $tblAccount->getServiceTblIdentification()
//                    ? $tblAccount->getServiceTblIdentification()->getId() : 0;
                $Global->POST['Account']['Token'] = (
                $tblAccount->getServiceTblToken()
                    ? $tblAccount->getServiceTblToken()->getId()
                    : ($tblIdentification && $tblIdentification->getName() == TblIdentification::NAME_AUTHENTICATOR_APP
                        ? -1 : 0)
                );
                $User = Account::useService()->getUserAllByAccount($tblAccount);
                if ($User) {
                    $tblPerson = $User[0]->getServiceTblPerson();
                    if ($tblPerson) {
                        $Global->POST['Account']['User'] = $tblPerson->getId();
                    }
                }

                $Authorization = Account::useService()->getAuthorizationAllByAccount($tblAccount);
                if ($Authorization) {
                    /** @var TblAuthorization $Role */
                    foreach ((array)$Authorization as $Role) {
                        if ($Role->getServiceTblRole()) {
                            $Global->POST['Account']['Role'][$Role->getServiceTblRole()->getId()] = $Role->getServiceTblRole()->getId();
                        }
                    }
                }

            }
            $Global->POST['Account']['Name'] = preg_replace('!^(.*?)-!is', '', $tblAccount->getUsername());
            $Global->savePost();

            $extraButton = '';
            if($tblSessionAccount = \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account::useService()->getAccountBySession()){
                if(($tblIdentificationSession = $tblSessionAccount->getServiceTblIdentification())){
                    if($tblIdentificationSession->getName() == 'System'){
                        if(isset($_POST['Account']['Identification'])){
                            $extraButton = new Center(new ToggleCheckbox('Alles auswählen/abwählen', $this->formAccount($tblAccount)));
                        }
                    }
                }
            }

            $Stage->setContent(
                new Layout(array(
                    Account::useService()->getGroupRoleLayoutGroup(),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(new Well(
                                $extraButton
                                .Account::useService()->changeAccountForm(
                                    $this->formAccount($tblAccount)
                                        ->appendFormButton(new Primary('Speichern', new Save()))
                                        ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert'),
                                    $tblAccount, $Account)
                            ))
                        ), new Title(new Pencil().' Bearbeiten')
                    ),
                ))
            );
        } else {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Danger(
                                    'Das Benutzerkonto konnte nicht gefunden werden'
                                )
                            )
                        ), new Title('Benutzerkonto ändern')
                    )
                )
            );
        }
        return $Stage;
    }

    /**
     * @param int   $tblAccountId
     * @param array $Account
     * @param bool  $confirm
     *
     * @return IFormInterface|Panel|string
     */
    public function frontendConfirmChange($tblAccountId, $Account, $confirm = false)
    {

        $tblAccount = Account::useService()->getAccountById($tblAccountId);
        $Panel = new Panel('Alle Benutzerrrechte dieses Accounts werden entfernt. Wollen Sie diese Änderung vornehmen?',
            new \SPHERE\Common\Frontend\Link\Repository\Danger('Ja', '/Setting/Authorization/Account/Edit/Confirm', new Ok(),
                array(
                    'tblAccountId' => $tblAccount->getId(),
                    'Account'    => $Account,
                    'confirm'    => true
                ))
            . new Standard('Nein', ''), Panel::PANEL_TYPE_DANGER);
        if($confirm){
            unset($Account['Role']);
            return Account::useService()->changeAccount($tblAccount->getId(), $Account);
        }

        return $Panel;
    }

    /**
     * @param int  $Id
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyAccount($Id, $Confirm = false)
    {

        $Stage = new Stage('Benutzerkonto', 'Löschen');
        if ($Id) {
            $tblAccount = Account::useService()->getAccountById($Id);
            if (!$Confirm) {

                $Content = array(
                    $tblAccount->getUsername(),
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

                        if ($tblAuthorization->getServiceTblRole()) {
                            $tblAuthorization = new Nameplate().' '.$tblAuthorization->getServiceTblRole()->getName();
                        } else {
                            $tblAuthorization = false;
                        }
                    });
                    $tblAuthorizationAll = array_filter(( $tblAuthorizationAll ));
                    $Content = array_merge($Content, $tblAuthorizationAll);
                }
                // ist ein UCS Mandant?
                $IsUCSMandant = false;
                if(($tblConsumer = Consumer::useService()->getConsumerBySession())){
                    if(Consumer::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_UCS)){
                        $IsUCSMandant = true;
                    }
                }
                $UcsRemark = '';
                if($IsUCSMandant){
                    $UcsRemark = new WellReadOnly('Nach dem Löschen des Accounts in der Schulsoftware wird dieser auch über die UCS Schnittstelle aus dem DLLP Projekt gelöscht.');
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonKey().' Benutzerkonto', $Content, Panel::PANEL_TYPE_SUCCESS),
                        new Panel(new Question().' Dieses Benutzerkonto wirklich löschen?', $UcsRemark,
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/Authorization/Account/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/Setting/Authorization/Account', new Disable()
                            )
                        )
                    )))))
                );
            } else {

                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( Account::useService()->destroyAccount($tblAccount)
                                ? new Success('Das Benutzerkonto wurde gelöscht')
                                : new Danger('Das Benutzerkonto konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Setting/Authorization/Account', Redirect::TIMEOUT_SUCCESS)
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Das Benutzerkonto konnte nicht gefunden werden'),
                        new Redirect('/Setting/Authorization/Account', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param null $RoleId
     * @param null $PersonGroupId
     *
     * @return string
     */
    public function openMassReplaceModal($RoleId = null, $PersonGroupId = null)
    {
        $groupList = array();
        if (($tblGroup = Group::useService()->getGroupByMetaTable('TEACHER'))) {
            $groupList[] = $tblGroup;
        }
        if (($tblGroup = Group::useService()->getGroupByMetaTable('TUDOR'))) {
            $groupList[] = $tblGroup;
        }
        if (($tblGroup = Group::useService()->getGroupByMetaTable('CLUB'))) {
            $groupList[] = $tblGroup;
        }

        $filter = new Panel(
            new Filter() . ' Filter',
            new Form(new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        (new SelectBox('RoleId', 'Benutzerrecht ' . new Danger('*'), array('{{ Name }}' => Access::useService()->getRolesForSelect(false))))
                            ->ajaxPipelineOnChange(ApiAccount::pipelineLoadMassReplaceContent($RoleId, $PersonGroupId))
                        , 3),
                    new FormColumn(
                        (new SelectBox('PersonGroupId', 'Personengruppe', array('{{ Name }}' => $groupList)))
                            ->ajaxPipelineOnChange(ApiAccount::pipelineLoadMassReplaceContent($RoleId, $PersonGroupId))
                        , 3),
                ))
            )),
            Panel::PANEL_TYPE_INFO
        );

        return
            new Title('Massenänderung', 'Einzelnes Benutzerrecht zuweisen')
            . $filter
            . ApiGroupRole::receiverBlock($this->loadMassReplaceContent(), 'MassReplaceContent');
    }

    /**
     * @param null $RoleId
     * @param null $PersonGroupId
     * @param null $Accounts
     *
     * @return string
     */
    public function loadMassReplaceContent($RoleId = null,$PersonGroupId = null, $Accounts = null)
    {
        if ($PersonGroupId) {
            $tblGroup = Group::useService()->getGroupById($PersonGroupId);
        } else {
            $tblGroup = false;
        }

        if ($RoleId) {
            $tblRole = Access::useService()->getRoleById($RoleId);
        } else {
            $tblRole = false;
        }

        if ($tblRole) {
            $dataList = array();
            $count = 0;
            if (($tblAccountList = Account::useService()->getAccountAllForEdit())) {
                $tblAccountList = $this->getSorter($tblAccountList)->sortObjectBy('UserName');
                foreach ($tblAccountList as $tblAccount) {
                    if (($tblPerson = Account::useService()->getFirstPersonByAccount($tblAccount))
                        && (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson))
                    ) {
                        // bei Rolle nur für Hardware-Token, Benutzerkonten ohne entsprechenden Ausfiltern
                        if ($tblRole->isSecure()
                            && ($tblIdentification = $tblAccount->getServiceTblIdentification())
                        ) {
                            switch ($tblIdentification->getName()) {
                                case 'AuthenticatorApp':
                                    $isAdd = true;
                                    break;
                                case 'Token':
                                    // Token muss gesetzt sein
                                    if ($tblAccount->getServiceTblToken()) {
                                        $isAdd = true;
                                    } else {
                                        $isAdd = false;
                                    }
                                    break;
                                default : $isAdd = false;
                            }
                        } else {
                            $isAdd = true;
                        }

                        if ($isAdd) {
                            $count++;
                            $dataList[] = array(
                                'Select' => new CheckBox('Accounts[' . $tblAccount->getId() . ']', '&nbsp;', 1),
                                'AccountName' => $tblAccount->getUsername(),
                                'PersonName' => $tblPerson->getLastFirstName(),
                                'Role' => Account::useService()->hasAuthorization($tblAccount, $tblRole)
                                    ? ($tblRole->isSecure() ? new Key() : new Globe()) . ' ' . $tblRole->getName() : ''
                            );
                        }
                    }
                }
            }

            $form = new Form(new FormGroup(new FormRow(array(
                new FormColumn(
                    new TableData(
                        $dataList,
                        null,
                        array(
                            'Select' => 'Auswahl',
                            'AccountName' => 'Benutzerkonto',
                            'PersonName' => 'Person',
                            'Role' => 'Benutzerrecht'
                        ),
                        null
                    )
                ),
                new FormColumn(ApiAccount::receiverBlock('', 'MessageContent')),
                new FormColumn(
                    (new \SPHERE\Common\Frontend\Link\Repository\Primary('Speichern', ApiAccount::getEndpoint(), new Save()))
                        ->ajaxPipelineOnClick(ApiAccount::pipelineMassReplaceSave($RoleId, $Accounts))
                )
            ))));

            if (!empty($dataList)) {
                return new Well(
                    new Title(new PersonKey() . ' Benutzerkonten Mitarbeiter', '(' . new Bold($count) . ' nach Filterung)')
                    . new ToggleCheckbox('Alle auswählen/abwählen', $form)
                    . $form
                );
            } else {
                return new Warning('Keine Benutzerkonten gefunden!', new Exclamation());
            }
        } else {
            return new Warning('Bitte wählen Sie zunächst ein Benutzerrecht aus!', new Exclamation());
        }
    }

    /**
     * @param null $RoleId
     *
     * @return string
     */
    public function loadMessageContent($RoleId = null)
    {
        if ($RoleId && ($tblRole = Access::useService()->getRoleById($RoleId))) {
            return new \SPHERE\Common\Frontend\Message\Repository\Danger('Für alle ausgewählten Benutzerkonten wird das 
                Benutzerrecht: ' . new Bold($tblRole->getName()) . ' gesetzt. Die Massenänderung kann nicht automatisch
                rückgängig gemacht werden!');
        }

        return '&nbsp;';
    }
}
