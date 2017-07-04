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
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
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
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\ToggleCheckbox;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
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

        $countUserAccountStudent = Account::useService()->countUserAccountAllByType(TblUserAccount::VALUE_TYPE_STUDENT);
        $countUserAccountCustody = Account::useService()->countUserAccountAllByType(TblUserAccount::VALUE_TYPE_CUSTODY);
        $Sum = $countUserAccountStudent + $countUserAccountCustody;
        $Ratio = 0;
        $Empty = 100;
        if ($Sum) {
            $Ratio = 100 / $Sum;
            $Empty = 0;
        }


        $PanelLeft = new Panel('Account-Verteilung', array(
            (new ProgressBar($countUserAccountStudent * $Ratio, $countUserAccountCustody * $Ratio, $Empty, 10))
                ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_INFO, ProgressBar::BAR_COLOR_WARNING),
            'Anzahl der Schüler-Accounts: '.$countUserAccountStudent.'<span style="width: 40px; float: left; padding: 3px">'.
            (new ProgressBar(100, 0, 0, 10))
                ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_INFO, ProgressBar::BAR_COLOR_WARNING)
            .'</span>',
            'Anzahl der Sorbeberechtigten-Accounts: '.$countUserAccountCustody.'<span style="width: 40px; float: left; padding: 3px">'.
            (new ProgressBar(0, 100, 0, 10))
                ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_INFO, ProgressBar::BAR_COLOR_WARNING)
            .'</span>',
        ));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            $PanelLeft
                            , 6)
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null  $Person
     * @param null  $Year
     * @param null  $Division
     * @param array $PersonIdArray
     *
     * @return Stage
     */
    public function frontendStudentAdd($Person = null, $Year = null, $Division = null, $PersonIdArray = array())
    {

        $Stage = new Stage('Schüler-Accounts', 'Erstellen');

        $form = $this->getStudentFilterForm();

        $Result = $this->getStudentFilterResult($Person, $Year, $Division);
        $TableContent = $this->getStudentTableContent($Result);

        $Table = new TableData($TableContent, null, array(
            'Check'         => 'Auswahl',
            'Name'          => 'Name',
            'StudentNumber' => 'Schüler-Nr.',
            'Course'        => 'Schulart',
            'Division'      => 'Klasse',
            'Address'       => 'Adresse',
        ),
            array(
                'order'      => array(array(1, 'asc')),
                'columnDefs' => array(
                    array('type' => 'german-string', 'targets' => 1),
                ),
                'pageLength' => -1,
                'paging'     => false,
//                'info'       => false,
                'searching'  => false,
                'responsive' => false,
            )
//            false
        );

        //get ErrorMessage by Filter
        $formResult = new Form(new FormGroup(new FormRow(new FormColumn(
            (isset($Year[ViewYear::TBL_YEAR_ID]) && $Year[ViewYear::TBL_YEAR_ID] != 0
                ? new WarningMessage('Filterung findet keine Personen (ohne Account)')
                : new WarningMessage('Die Filterung benötigt ein Schuljahr'))
        ))));
        if (!empty($TableContent)) {
            $formResult = (new Form(new FormGroup(new FormRow(new FormColumn($Table)))))
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        }

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
                            new Panel('Filterung', array(
                                (!empty($TableContent) ? new ToggleCheckbox('Alle wählen/abwählen', $Table) : ''),
                                Account::useService()->createAccount($formResult, $PersonIdArray, 'S')
                            ))
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
//                    new FormColumn(
//                        new Panel('Person', array(
//                            new TextField('Person['.ViewPerson::TBL_PERSON_FIRST_NAME.']', '', 'Vorname'),
//                            new TextField('Person['.ViewPerson::TBL_PERSON_LAST_NAME.']', '', 'Nachname')
//                        ), Panel::PANEL_TYPE_INFO)
//                        , 4),
                    new FormColumn(
                        new Panel('Bildung', array(
                            (new SelectBox('Year['.ViewYear::TBL_YEAR_ID.']', 'Schuljahr',
                                array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(1))))
                                ->setRequired(),
                            new SelectBox('Division['.ViewDivision::TBL_LEVEL_SERVICE_TBL_TYPE.']', 'Schulart',
                                array('Name' => Type::useService()->getTypeAll()))
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Klasse: Stufe', array(
                            new SelectBox('Division['.ViewDivision::TBL_LEVEL_ID.']', 'Stufe',
                                array('{{ Name }} {{ serviceTblType.Name }}' => $tblLevelShowList)),
                            new AutoCompleter('Division['.ViewDivision::TBL_DIVISION_NAME.']', 'Gruppe',
                                'Klasse: Gruppe', array('Name' => Division::useService()->getDivisionAll()))
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                )),
                new FormRow(
                    new FormColumn(
                        new Primary('Filtern')
                    )
                )
            ))
//            , new Primary('Filtern')
        );
    }

    /**
     * @param $Person
     * @param $Year
     * @param $Division
     *
     * @return array
     */
    private function getStudentFilterResult($Person, $Year, $Division)
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

        $Result = array();

        if (isset($Year) && $Year['TblYear_Id'] != 0 && isset($Pile)) {
            // Preparation Filter $FilterYear
            array_walk($Year, function (&$Input) {

                if (!empty($Input)) {
                    $Input = explode(' ', $Input);
                    $Input = array_filter($Input);
                } else {
                    $Input = false;
                }
            });
            $FilterYear = array_filter($Year);
//            // Preparation FilterPerson
//            $Filter['Person'] = array();

            // Preparation $FilterPerson
            if (isset($Person) && $Person) {
                array_walk($Person, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });
                $FilterPerson = array_filter($Person);
            } else {
                $FilterPerson = array();
            }
            // Preparation $FilterDivision
            if (isset($Division) && $Division) {
                array_walk($Division, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });
                $FilterDivision = array_filter($Division);
            } else {
                $FilterDivision = array();
            }

            $StudentGroup = Group::useService()->getGroupByMetaTable('STUDENT');
            $Result = $Pile->searchPile(array(
                0 => array(ViewPeopleGroupMember::TBL_GROUP_ID => array($StudentGroup->getId())),
                1 => $FilterPerson,
                2 => $FilterDivision,
                3 => $FilterYear
            ));
        }

        return $Result;
    }

    /**
     * @param array $Result
     *
     * @return array
     */
    public function getStudentTableContent($Result)
    {

        $SearchResult = array();
        if (!empty($Result)) {
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
     * @param null  $Person
     * @param null  $Year
     * @param null  $Division
     * @param array $PersonIdArray
     *
     * @return Stage
     */
    public function frontendCustodyAdd($Person = null, $Year = null, $Division = null, $PersonIdArray = array())
    {
        $Stage = new Stage('Sorbeberechtigten-Accounts', 'Erstellen');

        $form = $this->getCustodyFilterForm();

        $Result = $this->getStudentFilterResult($Person, $Year, $Division);
        $TableContent = $this->getCustodyTableContent($Result);

        $Table = new TableData($TableContent, null, array(
            'Check'   => 'Auswahl',
            'Name'    => 'Name',
            'Address' => 'Adresse',
        ),
            array(
                'order'      => array(
                    array(2, 'asc'),
                    array(1, 'asc')
                ),
                'columnDefs' => array(
                    array('type' => 'german-string', 'targets' => 1),
                ),
                'pageLength' => -1,
                'paging'     => false,
//                'info'       => false,
                'searching'  => false,
                'responsive' => false,
            )
//            false
        );

        //get ErrorMessage by Filter
        $formResult = new Form(new FormGroup(new FormRow(new FormColumn(
            (isset($Year[ViewYear::TBL_YEAR_ID]) && $Year[ViewYear::TBL_YEAR_ID] != 0
                ? new WarningMessage('Filterung findet keine Personen (ohne Account)')
                : new WarningMessage('Die Filterung benötigt ein Schuljahr'))
        ))));
        if (!empty($TableContent)) {
            $formResult = (new Form(new FormGroup(new FormRow(new FormColumn($Table)))))
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        }

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
                            new Panel('Filterung', array(
                                (!empty($TableContent) ? new ToggleCheckbox('Alle wählen/abwählen', $Table) : ''),
                                Account::useService()->createAccount($formResult, $PersonIdArray, 'C')
                            ))
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
    private function getCustodyFilterForm()
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
//                    new FormColumn(
//                        new Success('')
//                        , 4),
                    new FormColumn(
                        new Panel('Bildung', array(
                            (new SelectBox('Year['.ViewYear::TBL_YEAR_ID.']', 'Schuljahr',
                                array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(1))))
                                ->setRequired(),
                            new SelectBox('Division['.ViewDivision::TBL_LEVEL_SERVICE_TBL_TYPE.']', 'Schulart',
                                array('Name' => Type::useService()->getTypeAll()))
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Klasse: Stufe', array(
                            new SelectBox('Division['.ViewDivision::TBL_LEVEL_ID.']', 'Stufe',
                                array('{{ Name }} {{ serviceTblType.Name }}' => $tblLevelShowList)),
                            new AutoCompleter('Division['.ViewDivision::TBL_DIVISION_NAME.']', 'Gruppe',
                                'Klasse: Gruppe', array('Name' => Division::useService()->getDivisionAll()))
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                )),
                new FormRow(
                    new FormColumn(
                        new Primary('Filtern')
                    )
                )
            ))
//            , new Primary('Filtern')
        );
    }

    /**
     * @param array $Result
     *
     * @return array
     */
    public function getCustodyTableContent($Result)
    {

        $SearchResult = array();
        if (!empty($Result)) {
            /**
             * @var int                                $Index
             * @var ViewPerson[]|ViewDivisionStudent[] $Row
             */
            foreach ($Result as $Index => $Row) {

                /** @var ViewPerson $DataPerson */
                $DataPerson = $Row[1]->__toArray();
//                /** @var ViewDivisionStudent $DivisionStudent */
//                $DivisionStudent = $Row[2]->__toArray();
                $tblPersonStudent = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
                $tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPersonStudent);
                if ($tblToPersonList) {
                    array_walk($tblToPersonList, function (TblToPerson $tblToPerson) use (&$SearchResult) {
                        $tblType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                        $tblPerson = $tblToPerson->getServiceTblPersonFrom();
                        if ($tblToPerson->getTblType() && $tblToPerson->getTblType()->getId() == $tblType->getId()) {

                            /** @noinspection PhpUndefinedFieldInspection */
                            $DataPerson['Name'] = false;
                            $DataPerson['Check'] = '';

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

                            $DataPerson['Address'] = $AddressReceiver;

                            if ($tblPerson) {
                                $DataPerson['Check'] = (new CheckBox('PersonIdArray['.$tblPerson->getId().']', ' ',
                                    $tblPerson->getId()
                                    , array($tblPerson->getId())))->setChecked();
                                $DataPerson['Name'] = $tblPerson->getLastFirstName();
                            }

                            // ignor existing Accounts (By Person)
                            if (!Account::useService()->getUserAccountByPerson($tblPerson)) {
                                // ignore duplicated Person
                                if (!array_key_exists($tblPerson->getId(), $SearchResult)) {
                                    $SearchResult[$tblPerson->getId()] = $DataPerson;
                                }
                            }
                        }
                    });
                }
            }
        }

        return $SearchResult;
    }

    /**
     * @return Stage
     */
    public function frontendStudentShow()
    {
        $Stage = new Stage('Schüler-Accounts', 'Übersicht');

        $Stage->addButton(new Standard('Zurück', '/Setting/User', new ChevronLeft()));

        $tblUserAccountAll = Account::useService()->getUserAccountAllByType(TblUserAccount::VALUE_TYPE_STUDENT);
        $TableContent = array();
        if ($tblUserAccountAll) {
            array_walk($tblUserAccountAll, function (TblUserAccount $tblUserAccount) use (&$TableContent) {

                $Item['Salutation'] = new Muted('-NA-');
                $Item['Name'] = '';
                $Item['UserName'] = new Warning(new WarningIcon().' Keine Accountnamen hinterlegt');
                $Item['UserPassword'] = '';
                $Item['Address'] = new Warning(new WarningIcon().' Keine Adresse gewählt');
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

                $tblPerson = $tblUserAccount->getServiceTblPerson();
                if ($tblPerson) {
                    if (($tblAddress = $tblPerson->fetchMainAddress())) {
                        $Item['Address'] = $tblAddress->getGuiString();
                    }

                    if ($tblPerson->getSalutation() != '') {
                        $Item['Salutation'] = $tblPerson->getSalutation();
                    }
                    $Item['Name'] = $tblPerson->getLastFirstName();

                    $CustodyList = array();
                    $tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                    if ($tblRelationshipList) {
                        foreach ($tblRelationshipList as $tblRelationship) {
                            if ($tblRelationship->getTblType()->getName() == 'Sorgeberechtigt') {

                                $tblPersonCustody = $tblRelationship->getServiceTblPersonFrom();
                                if ($tblPersonCustody && $tblPersonCustody->getId() != $tblPerson->getId()) {
                                    $CustodyList[] = new Container($tblPersonCustody->getLastFirstName());
                                }
                            }
                        }
                    }
                    if (!empty($CustodyList)) {
                        $Item['PersonListCustody'] = implode($CustodyList);
                    }

//                    //ToDO remove all Accounts (Test)
//                    $tblUserAccount = Account::useService()->getUserAccountByPerson($tblPerson);
//                    if ($tblUserAccount) {
//                        $tblAccount = $tblUserAccount->getServiceTblAccount();
//                        if ($tblAccount) {
//                            // remove tblAccount
//                            AccountAuthorization::useService()->destroyAccount($tblAccount);
//                        }
//                        // remove tblUserAccount
//                        Account::useService()->removeUserAccount($tblUserAccount);
//                    }
                }
                array_push($TableContent, $Item);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            (!empty($TableContent)
                                ? new TableData($TableContent, new Title('Übersicht', 'Benutzer'),
                                    array(
                                        'Salutation'        => 'Anrede',
                                        'Name'              => 'Name',
                                        'UserName'          => 'Account',
                                        'UserPassword'      => 'Passwort',      //ToDO remove from display
                                        'Address'           => 'Adresse',
                                        'PersonListCustody' => 'Sorgeberechtigte',
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
     * @return Stage
     */
    public function frontendCustodyShow()
    {
        $Stage = new Stage('Sorbeberechtigten-Accounts', 'Übersicht');

        $Stage->addButton(new Standard('Zurück', '/Setting/User', new ChevronLeft()));

        $tblUserAccountAll = Account::useService()->getUserAccountAllByType(TblUserAccount::VALUE_TYPE_CUSTODY);
        $TableContent = array();
        if ($tblUserAccountAll) {
            array_walk($tblUserAccountAll, function (TblUserAccount $tblUserAccount) use (&$TableContent) {

                $Item['Salutation'] = new Muted('-NA-');
                $Item['Name'] = '';
                $Item['UserName'] = new Warning(new WarningIcon().' Keine Accountnamen hinterlegt');
                $Item['UserPassword'] = '';
                $Item['Address'] = new Warning(new WarningIcon().' Keine Adresse gewählt');
                $Item['PersonListCustody'] = '';
                $Item['PersonListStudent'] = '';
                $Item['Option'] =
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

                $tblPerson = $tblUserAccount->getServiceTblPerson();
                if ($tblPerson) {
                    if (($tblAddress = $tblPerson->fetchMainAddress())) {
                        $Item['Address'] = $tblAddress->getGuiString();
                    }

                    if ($tblPerson->getSalutation() != '') {
                        $Item['Salutation'] = $tblPerson->getSalutation();
                    }
                    $Item['Name'] = $tblPerson->getLastFirstName();

                    $StudentList = array();
                    $tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                    $tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson,
                        $tblRelationshipType);
                    if ($tblRelationshipList) {
                        foreach ($tblRelationshipList as $tblRelationship) {
                            if ($tblRelationship->getTblType()->getName() == 'Sorgeberechtigt') {
                                $tblPersonStudent = $tblRelationship->getServiceTblPersonTo();
                                if ($tblPersonStudent && $tblPersonStudent->getId() != $tblPerson->getId()) {
                                    $StudentList[] = new Container($tblPersonStudent->getLastFirstName());
                                }
                            }
                        }
                    }
                    if (!empty($StudentList)) {
                        $Item['PersonListStudent'] = implode($StudentList);
                    }

//                    //ToDO remove all Accounts (Test)
//                    $tblUserAccount = Account::useService()->getUserAccountByPerson($tblPerson);
//                    if ($tblUserAccount) {
//                        $tblAccount = $tblUserAccount->getServiceTblAccount();
//                        if ($tblAccount) {
//                            // remove tblAccount
//                            AccountAuthorization::useService()->destroyAccount($tblAccount);
//                        }
//                        // remove tblUserAccount
//                        Account::useService()->removeUserAccount($tblUserAccount);
//                    }
                }
                array_push($TableContent, $Item);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            (!empty($TableContent)
                                ? new TableData($TableContent, new Title('Übersicht', 'Benutzer'),
                                    array(
                                        'Salutation'        => 'Anrede',
                                        'Name'              => 'Name',
                                        'UserName'          => 'Account',
                                        'UserPassword'      => 'Passwort',      //ToDO remove from display
                                        'Address'           => 'Adresse',
                                        'PersonListStudent' => 'Sorgeberechtigt für',
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
     * @return Stage
     */
    public function frontendAccountExport()
    {
        $Stage = new Stage('Account', 'Serienbrief Export');

        $tblUserAccountAll = Account::useService()->getUserAccountAll();
        $tblUserAccountList = Account::useService()->getUserAccountListAndCount($tblUserAccountAll);
        $TableContent = array();
        if ($tblUserAccountList) {
            /** @var TblUserAccount[] $UserAccountList */
            array_walk($tblUserAccountList, function ($tblUserAccountList, $groupByTime) use (&$TableContent) {
                /** @var TblUserAccount $tblUserAccountTarget */
                if (($tblUserAccountTarget = current($tblUserAccountList)) && $tblUserAccountTarget->getUserPassword()) {
//                    Debugger::screenDump($groupByTime.' -> '.count($tblUserAccountList));
                    $item['GroupByTime'] = $groupByTime;
                    $item['UserAccountCount'] = count($tblUserAccountList);
                    if ($tblUserAccountTarget->getType() == TblUserAccount::VALUE_TYPE_STUDENT) {
                        $item['AccountType'] = 'Schüler-Accounts';
                    } elseif ($tblUserAccountTarget->getType() == TblUserAccount::VALUE_TYPE_CUSTODY) {
                        $item['AccountType'] = 'Sorgeberechtigten-Accounts';
                    }
                    $item['Option'] = new External('', '/Api/Setting/UserAccount/Download', new Download()
                            , array('GroupByTime' => $groupByTime))
                        .new Standard('', '/Setting/User/Account/Clear', new Remove(),
                            array('GroupByTime' => $groupByTime),
                            'Entfernen der Klartext Passwörter und des damit verbundenem verfügbaren Download');

                    array_push($TableContent, $item);
                }
            });
        }
        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new TableData($TableContent, null
                            , array(
                                'GroupByTime'      => 'Erstellung am',
                                'UserAccountCount' => 'Anzahl Accounts',
                                'AccountType'      => 'Account Typ',
                                'Option'           => '',
                            ),
                            array(
                                'columnDefs' => array(
                                    array('type' => 'de_date', 'targets' => 0),
                                )
                            )
                        )
                    )
                )
            )
        ));

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
                                'Person: '.new Bold($tblPerson->getFullName()),
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
                $Password = $tblUserAccount->getAccountPassword();
                // remove tblAccount
                if ($tblAccount && $Password) {
                    if (AccountAuthorization::useService()->resetPassword($Password, $tblAccount)) {
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
            //default
            $Route = '/Setting/User/Account/Student/Show';
            if ($tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_STUDENT) {
                $Route = '/Setting/User/Account/Student/Show';
            } elseif ($tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_CUSTODY) {
                $Route = '/Setting/User/Account/Custody/Show';
            }
            $Stage->addButton(
                new Standard('Zurück', $Route, new ChevronLeft())
            );
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon().' Benutzerdaten',
                            array(
                                'Person: '.new Bold($tblPerson->getFullName()),
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
                            .new Standard('Nein', $Route, new Disable())
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
                            new Redirect($Route, Redirect::TIMEOUT_SUCCESS)
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
     * @param string $GroupByTime
     * @param bool   $Confirm
     *
     * @return Stage|string
     */
    public function clearPassword($GroupByTime, $Confirm = false)
    {
        $Stage = new Stage('Benutzer', 'Klartext Passwörter');
        if ($GroupByTime) {
            $GroupByTime = new \DateTime($GroupByTime);
            $tblUserAccountList = Account::useService()->getUserAccountByTimeGroup($GroupByTime);
            if (!$tblUserAccountList) {
                return $Stage.new DangerMessage('Export nicht gefunden', new Ban())
                    .new Redirect('/Setting/User/Account/Export', Redirect::TIMEOUT_ERROR);
            }
            $Stage->addButton(
                new Standard('Zurück', '/Setting/User', new ChevronLeft())
            );
            if (!$Confirm) {

                $AccountType = '';
                if (($tblUserAccount = current($tblUserAccountList))) {
                    if ($tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_STUDENT) {
                        $AccountType = 'Schüler';
                    }
                    if ($tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_CUSTODY) {
                        $AccountType = 'Sorgeberechtigte';
                    }
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(new PersonIcon().' Benutzer',
                            array(
                                'Anzahl: '.new Bold(count($tblUserAccountList)),
                                'Account Typ: '.new Bold($AccountType)
                            ),
                            Panel::PANEL_TYPE_SUCCESS
                        ),
                        new Panel(new Question().' Klartext-Passwörter dieser Accounts wirklich löschen?',
                            'Passwörter können hiernach nicht mehr exportiert werden!
                            Das zurücksetzen von Passwörtern ist weiterhin möglich.',
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Setting/User/Account/Clear', new Ok(),
                                array('GroupByTime' => $GroupByTime->format('d.m.Y H:i:s'), 'Confirm' => true)
                            )
                            .new Standard('Nein', '/Setting/User/Account/Export', new Disable())
                        )
                    )))))
                );
            } else {
                $IsDestroy = false;
                // remove tblUserAccount
                if (Account::useService()->clearPassword($GroupByTime)) {
                    $IsDestroy = true;
                }

                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(
                        new LayoutColumn(array(
                            ($IsDestroy
                                ? new SuccessMessage(new SuccessIcon().' Der Klartext wurde gelöscht')
                                : new DangerMessage(new Ban().' Der Klartext konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Setting/User/Account/Export', Redirect::TIMEOUT_SUCCESS)
                        ))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(
                    new LayoutColumn(array(
                        new DangerMessage(new Ban().' Export nicht gefunden'),
                        new Redirect('/Setting/User/Account/Export', Redirect::TIMEOUT_ERROR)
                    ))
                )))
            );
        }
        return $Stage;
    }
}