<?php
namespace SPHERE\Application\Setting\User\Account;

use SPHERE\Application\Api\Contact\ApiContactAddress;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Authorization\Account\Account as AccountAuthorization;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Übersicht', 'Accounts');
        $Stage->addButton(new Standard('Zurück', '/People', new ChevronLeft()));
//        $Stage->addButton(new Standard('Personenzuweisung', '/Setting/User/Account/Person', new Listing(), array()
//            , 'Auswahl der Personen'));

        $tblUserAccountAll = Account::useService()->getUserAccountAll();
        $TableContent = array();
        if ($tblUserAccountAll) {
            array_walk($tblUserAccountAll, function (TblUserAccount $tblUserAccount) use (&$TableContent) {

                $Item['Salutation'] = new Muted('-NA-');
                $Item['Name'] = '';
                $Item['UserName'] = new Warning(new WarningIcon().' Keine Accountnamen hinterlegt');
                $Item['UserPassword'] = '';
                $Item['Address'] = new Warning(new WarningIcon().' Keine Adresse gewählt');
                $Item['Mail'] = new Warning(new WarningIcon().' Keine E-Mail gewählt');
                $Item['PersonListCustody'] = '';
                $Item['PersonListStudent'] = '';
                $Item['Option'] =
//                    new Standard('', '/Setting/User/Account/Address/Edit', new Building(),
//                        array('Id' => $tblUserAccount->getId()), 'Adresse ändern/anlegen')
//                    .new Standard('', '/Setting/User/Account/Mail/Edit', new MailIcon(),
//                        array('Id' => $tblUserAccount->getId()), 'E-Mail ändern/anlegen')
                    new Standard('', '/Setting/User/Account/Reset', new Repeat(),
                        array('Id' => $tblUserAccount->getId())
                        , 'Passwort Zurücksetzten')
                    .new Standard('', '/Setting/User/Account/Destroy', new Remove(),
                        array('Id' => $tblUserAccount->getId()), 'Benutzer entfernen');
                $tblAccount = $tblUserAccount->getServiceTblAccount();
                if ($tblAccount) {
                    $Item['UserName'] = $tblAccount->getUsername();
                    if (hash('sha256', $tblUserAccount->getUserPassword()) != $tblAccount->getPassword()) {
                        $Item['UserPassword'] = new Success(new SuccessIcon().' PW geändert');
                    } else {
                        $Item['UserPassword'] = new Warning($tblUserAccount->getUserPassword());
                    }
                } else {
                    $Item['UserPassword'] = $tblUserAccount->getUserPassword();
                }

                $tblToPersonAddress = $tblUserAccount->getServiceTblToPersonAddress();
                if ($tblToPersonAddress) {
                    $tblAddress = $tblToPersonAddress->getTblAddress();
                    if ($tblAddress) {
                        $Item['Address'] = $tblAddress->getGuiString();
                    }
                }

                $tblToPersonMail = $tblUserAccount->getServiceTblToPersonMail();
                if ($tblToPersonMail) {
                    $tblMail = $tblToPersonMail->getTblMail();
                    if ($tblMail) {
                        $Item['Mail'] = $tblMail->getAddress();
                    }
                }

                $tblPerson = $tblUserAccount->getServiceTblPerson();
                if ($tblPerson) {

                    if ($tblPerson->getSalutation() != '') {
                        $Item['Salutation'] = $tblPerson->getSalutation();
                    }
                    $Item['Name'] = $tblPerson->getLastFirstName();

                    $CustodyList = array();
                    $StudentList = array();
                    $tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                    if ($tblRelationshipList) {
                        foreach ($tblRelationshipList as $tblRelationship) {
                            if ($tblRelationship->getTblType()->getName() == 'Sorgeberechtigt') {

                                $tblPersonCustody = $tblRelationship->getServiceTblPersonFrom();
                                if ($tblPersonCustody && $tblPersonCustody->getId() != $tblPerson->getId()) {
                                    $CustodyList[] = new Container($tblPersonCustody->getLastFirstName());
                                }
                                $tblPersonStudent = $tblRelationship->getServiceTblPersonTo();
                                if ($tblPersonStudent && $tblPersonStudent->getId() != $tblPerson->getId()) {
                                    $StudentList[] = new Container($tblPersonStudent->getLastFirstName());
                                }
                            }
                        }
                    }
                    if (!empty($CustodyList)) {
                        $Item['PersonListCustody'] = implode($CustodyList);
                    }
                    if (!empty($StudentList)) {
                        $Item['PersonListStudent'] = implode($StudentList);
                    }

                }

                array_push($TableContent, $Item);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( !empty($TableContent)
                                ?
                                new TableData($TableContent, new Title('Übersicht', 'Benutzer'),
                                    array(
                                        'Salutation'        => 'Anrede',
                                        'Name'              => 'Name',
                                        'UserName'          => 'Account',
                                        'UserPassword'      => 'Passwort',
                                        'Address'           => 'Adresse',
                                        'Mail'              => 'E-Mail',
                                        'PersonListCustody' => 'Sorgeberechtigte',
                                        'PersonListStudent' => 'Schüler',
                                        'Option'            => ''
                                    ))
                                : new WarningMessage('Keine Benutzerzugänge vorhanden.')
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Year
     * @param null $Division
     *
     * @return Stage
     */
    public function frontendStudentAdd($Year = null, $Division = null)
    {

        $Stage = new Stage('Schüler-Accounts', 'Erstellen');

        $form = $this->getStudentFilterForm();

        $TableContent = $this->getStudentFilterResult($Year, $Division);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            $form
                        ))
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            new Form(
                                new FormGroup(
                                    new FormRow(
                                        new FormColumn(
                                            new TableData($TableContent, null, array(
                                                'Check'         => 'Auswahl',
                                                'Name'          => 'Name',
                                                'StudentNumber' => 'Schüler-Nr.',
                                                'Course'        => 'Schulart',
                                                'Division'      => 'Klasse',
                                                'Address'       => 'Adresse',
                                            ), null)
                                        )
                                    )
                                )
                            )
                        )
                    )
                ))
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function getStudentFilterForm()
    {
        $tblLevelShowList = array();

        $tblLevelList = Division::useService()->getLevelAll();
        if ($tblLevelList) {
            foreach ($tblLevelList as &$tblLevel) {
                if (!$tblLevel->getName()) {
                    $tblLevelClone = clone $tblLevel;
                    $tblLevelClone->setName('Stufenübergreifende Klassen');
                    $tblLevelShowList[] = $tblLevelClone;
                } else {
                    $tblLevelShowList[] = $tblLevel;
                }
            }
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                            new Panel('Bildung: Schuljahr', array(
                                (new SelectBox('Year['.ViewYear::TBL_YEAR_ID.']', 'Schuljahr',
                                    array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(1))))
                                    ->setRequired()
                            ), Panel::PANEL_TYPE_INFO
                            )
                        )
                        , 3),
                    new FormColumn(
                        new Panel('Bildung: Schulart', array(
                            new SelectBox('Division['.ViewDivision::TBL_LEVEL_SERVICE_TBL_TYPE.']', 'Schulart',
                                array('Name' => Type::useService()->getTypeAll()))
                        ), Panel::PANEL_TYPE_INFO)
                        , 3),
                    new FormColumn(
                        new Panel('Klasse: Stufe', array(
                            new SelectBox('Division['.ViewDivision::TBL_LEVEL_ID.']', 'Stufe',
                                array('{{ Name }} {{ serviceTblType.Name }}' => $tblLevelShowList))
                        ), Panel::PANEL_TYPE_INFO
                        ), 3),
                    new FormColumn(
                        new Panel('Klasse: Gruppe', array(
                            new AutoCompleter('Division['.ViewDivision::TBL_DIVISION_NAME.']', 'Gruppe',
                                'Klasse: Gruppe',
                                array('Name' => Division::useService()->getDivisionAll()))
                        ), Panel::PANEL_TYPE_INFO
                        ), 3),
                )),
                new FormRow(
                    new FormColumn(
                        new Danger('*'.new Small('Pflichtfeld'))
                    )
                )
            ))
            , new Primary('Filtern')
        );
    }

    /**
     * @param $Year
     * @param $Division
     *
     * @return array
     */
    private function getStudentFilterResult($Year, $Division)
    {

        $Pile = new Pile(Pile::JOIN_TYPE_INNER);
        $Pile->addPile((new ViewPeopleGroupMember())->getViewService(), new ViewPeopleGroupMember(),
            null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
        );
        $Pile->addPile((new ViewPerson())->getViewService(), new ViewPerson(),
            ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
        );
        $Pile->addPile((new ViewDivisionStudent())->getViewService(), new ViewDivisionStudent(),
            ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, ViewDivisionStudent::TBL_DIVISION_TBL_YEAR
        );
        $Pile->addPile((new ViewYear())->getViewService(), new ViewYear(),
            ViewYear::TBL_YEAR_ID, ViewYear::TBL_YEAR_ID
        );

        $Result = '';

        if (isset($Year) && $Year['TblYear_Id'] != 0 && isset($Pile)) {
            // Preparation Filter
            array_walk($Year, function (&$Input) {

                if (!empty($Input)) {
                    $Input = explode(' ', $Input);
                    $Input = array_filter($Input);
                } else {
                    $Input = false;
                }
            });
            $Year = array_filter($Year);
//            // Preparation FilterPerson
//            $Filter['Person'] = array();

            // Preparation $FilterType
            if (isset($Division) && $Division) {
                array_walk($Division, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });
                $Division = array_filter($Division);
            } else {
                $Division = array();
            }

            $StudentGroup = Group::useService()->getGroupByMetaTable('STUDENT');
            $Result = $Pile->searchPile(array(
                0 => array(ViewPeopleGroupMember::TBL_GROUP_ID => array($StudentGroup->getId())),
                1 => array(),   // empty Person search
                2 => $Division,
                3 => $Year
            ));
        }

        $SearchResult = array();
        if ($Result != '') {
            /**
             * @var int                                $Index
             * @var ViewPerson[]|ViewDivisionStudent[] $Row
             */
            foreach ($Result as $Index => $Row) {

                /** @var ViewPerson $DataPerson */
                $DataPerson = $Row[1]->__toArray();
                /** @var ViewDivisionStudent $DivisionStudent */
                $DivisionStudent = $Row[2]->__toArray();
                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                $DataPerson['Check'] = '';
                $DataPerson['Course'] = '';
                $DataPerson['Course'] = '';

                $Button = (new Standard('', ApiContactAddress::getEndpoint(), new Edit(), array(),
                    'Bearbeiten der Hauptadresse'))
                    ->ajaxPipelineOnClick(ApiContactAddress::pipelineOpen($tblPerson->getId()));

                $AddressReceiver = new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                ApiContactAddress::receiverColumn($tblPerson->getId())
                                , 11),
                            new LayoutColumn(
                                new Center($Button).ApiContactAddress::receiverModal($tblPerson->getId())
                                , 1),
                        ))
                    )
                );
//                $AddressReceiver = ApiContactAddress::receiverColumn($tblPerson->getId()); // . new PullRight($Button);


                $DataPerson['Address'] = $AddressReceiver;

                if ($tblPerson) {
                    $DataPerson['Check'] = (new CheckBox('PersonIdArray['.$tblPerson->getId().']', ' ',
                        $tblPerson->getId()
                        , array($tblPerson->getId())))->setChecked();
                    $DataPerson['Name'] = $tblPerson->getLastFirstName();
//                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {
                        $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                        if ($tblTransferType) {
                            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                $tblTransferType);
                            if ($tblStudentTransfer && ($tblCourse = $tblStudentTransfer->getServiceTblCourse())) {
                                $DataPerson['Course'] = $tblCourse->getName();
                            }
                        }
                    }
                }
                $DataPerson['Division'] = '';
                $DataPerson['Level'] = '';

                $tblDivision = Division::useService()->getDivisionById($DivisionStudent['TblDivision_Id']);
                if ($tblDivision) {
                    $DataPerson['Division'] = $tblDivision->getDisplayName();
                }

//                /** @noinspection PhpUndefinedFieldInspection */
//                if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
//                    /** @noinspection PhpUndefinedFieldInspection */
//                    $DataPerson['Address'] = $AddressReceiver;
//                }
                $DataPerson['StudentNumber'] = new Small(new Muted('-NA-'));
                if (isset($tblStudent) && $tblStudent && $DataPerson['Name']) {
                    $DataPerson['StudentNumber'] = $tblStudent->getIdentifier();
                }

                if (!isset($DataPerson['ProspectYear'])) {
                    $DataPerson['ProspectYear'] = new Small(new Muted('-NA-'));
                }
                if (!isset($DataPerson['ProspectDivision'])) {
                    $DataPerson['ProspectDivision'] = new Small(new Muted('-NA-'));
                }

                // ignor existing Accounts (By Person)
                if (!Account::useService()->getUserAccountByPerson($tblPerson)) {
                    // ignore duplicated Person
                    if ($DataPerson['Name']) {
                        if (!array_key_exists($DataPerson['TblPerson_Id'], $SearchResult)) {
                            $SearchResult[$DataPerson['TblPerson_Id']] = $DataPerson;
                        }
                    }
                }
            }
        }

        return $SearchResult;
    }

    /**
     * @return Stage
     */
    public function frontendCustodyAdd()
    {
        $Stage = new Stage('Eltern-Accounts', 'Erstellen');

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendStudentShow()
    {
        $Stage = new Stage('Schüler-Accounts', 'Übersicht');

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendCustodyShow()
    {
        $Stage = new Stage('Eltern-Accounts', 'Übersicht');

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendAccountExport()
    {
        $Stage = new Stage('Account', 'Serienbrief Export');

        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendResetAccount($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Benutzer Passwort', 'Zurücksetzen');
        if ($Id) {
            $tblUserAccount = Account::useService()->getUserAccountById($Id);
            if (!$tblUserAccount) {
                return $Stage.new DangerMessage('Benutzeraccount nicht gefunden', new Ban())
                    .new Redirect('/Setting/User', Redirect::TIMEOUT_ERROR);
            }
            $tblAccount = $tblUserAccount->getServiceTblAccount();
            if (!$tblAccount) {
                return $Stage->setContent(new WarningMessage('Account nicht vorhanden')
                    .new Redirect('/Setting/User', Redirect::TIMEOUT_ERROR));
            }
            $tblPerson = $tblUserAccount->getServiceTblPerson();
            if (!$tblPerson) {
                return $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Person', new WarningMessage('Person wurde nicht gefunden')),
                        new Panel(new Question().' Diesen Benutzer wirklich Zurücksetzen?', '',
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/User/Account/Reset', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard('Nein', '/Setting/User', new Disable())
                        )
                    )
                )))));
            }

            $Stage->addButton(
                new Standard('Zurück', '/Setting/User', new ChevronLeft())
            );
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon().' Benutzerdaten',
                            array(
                                'Person: '.new Bold($tblPerson->getFullName())
                            ,
                                'Benutzer: '.new Bold($tblAccount->getUserName())
                            ),
                            Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Panel(new Question().' Diesen Benutzer wirklich Zurücksetzen?', '',
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/User/Account/Reset', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard('Nein', '/Setting/User', new Disable())
                        )
                    )))))
                );
            } else {
                $IsChanged = false;
                $Password = $tblUserAccount->getUserPassword();
                // remove tblAccount
                if ($tblAccount && $Password) {
                    if (AccountAuthorization::useService()->changePassword($Password, $tblAccount)) {
                        $IsChanged = true;
                    }
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ($IsChanged
                                ? new SuccessMessage(new SuccessIcon().' Der Benutzer wurde Zurückgesetzt')
                                : new DangerMessage(new Ban().' Der Benutzer konnte nicht Zurückgesetzt werden')
                            ),
                            new Redirect('/Setting/User', Redirect::TIMEOUT_SUCCESS)
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new DangerMessage(new Ban().' Der Benutzer konnte nicht gefunden werden'),
                        new Redirect('/Setting/User', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyPrepare($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Benutzer', 'Löschen');
        if ($Id) {
            $tblUserAccount = Account::useService()->getUserAccountById($Id);
            if (!$tblUserAccount) {
                return $Stage.new DangerMessage('Benutzeraccount nicht gefunden', new Ban())
                    .new Redirect('/Setting/User', Redirect::TIMEOUT_ERROR);
            }
            $tblAccount = $tblUserAccount->getServiceTblAccount();
            $tblPerson = $tblUserAccount->getServiceTblPerson();
            if (!$tblPerson) {
                // remove prepare if person are deleted (without asking)
                if ($tblAccount) {
                    // remove tblAccount
                    AccountAuthorization::useService()->destroyAccount($tblAccount);
                }
                // remove tblUserAccount
                Account::useService()->removeUserAccount($tblUserAccount);
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            array(
                                new SuccessMessage(new SuccessIcon().' Der Benutzer wurde gelöscht'),
                                new Redirect('/Setting/User', Redirect::TIMEOUT_SUCCESS)
                            )
                        ))
                    )))
                );
                return $Stage;
            }
            $Stage->addButton(
                new Standard('Zurück', '/Setting/User', new ChevronLeft())
            );
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon().' Benutzerdaten',
                            array(
                                'Person: '.new Bold($tblPerson->getFullName())
                            ,
                                'Benutzer: '.new Bold($tblAccount->getUserName())
                            ),
                            Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Panel(new Question().' Diesen Benutzer wirklich löschen?', '',
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/User/Account/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard('Nein', '/Setting/User', new Disable())
                        )
                    )))))
                );
            } else {
                $IsDestroy = false;
                // remove tblAccount
                if ($tblAccount) {
                    AccountAuthorization::useService()->destroyAccount($tblAccount);
                }
                // remove tblUserAccount
                if (Account::useService()->removeUserAccount($tblUserAccount)) {
                    $IsDestroy = true;
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ($IsDestroy
                                ? new SuccessMessage(new SuccessIcon().' Der Benutzer wurde gelöscht')
                                : new DangerMessage(new Ban().' Der Benutzer konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Setting/User', Redirect::TIMEOUT_SUCCESS)
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new DangerMessage(new Ban().' Der Benutzer konnte nicht gefunden werden'),
                        new Redirect('/Setting/User', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }
}