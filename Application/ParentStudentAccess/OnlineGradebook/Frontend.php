<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineGradebook;

use DateTime;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblUser;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\EyeMinus;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\NotAvailable;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param null $YearId
     * @param null $ParentAccount
     *
     * @return Stage|string
     */
    public function frontendOnlineGradebook($YearId = null, $ParentAccount = null)
    {
        $Stage = new Stage('Notenübersicht', 'Schüler/Eltern');
        $Stage->setMessage(
            new Container('Anzeige der Zensuren für die Schüler und Eltern.')
            .new Container('Der angemeldete Schüler sieht nur seine eigenen Zensuren.')
            .new Container('Der angemeldete Sorgeberechtigte sieht nur die Zensuren seiner Kinder.')
        );

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
        $rowList = array();
        $tblDisplayYearList = array();
        $data = array();
        $isStudent = false;
//        $isCustody = false;
        $isEighteen = false;    // oder Älter
        $tblPersonSession = false;

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount);
            if ($tblUserAccount) {
                $Type = $tblUserAccount->getType();
                if ($Type == TblUserAccount::VALUE_TYPE_STUDENT) {
                    $isStudent = true;
                }
//                if ($Type == TblUserAccount::VALUE_TYPE_CUSTODY) {
//                    $isCustody = true;
//                }
            }
            $UserList = Account::useService()->getUserAllByAccount($tblAccount);
            if ($UserList && $isStudent) {
                //
                $tblUser = current($UserList);
                /** @var TblUser $tblUser */
                if ($tblUser && $tblUser->getServiceTblPerson()) {
                    $tblPersonSession = $tblUser->getServiceTblPerson();
                    if ($isStudent) {
                        $tblCommon = Common::useService()->getCommonByPerson($tblPersonSession);
                        if ($tblCommon && ($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {

                            $Now = new DateTime();
                            $Now->modify('-18 year');

                            if ($Now >= new DateTime($tblCommonBirthDates->getBirthday())) {
                                $isEighteen = true;
                            }
                        }
                    }
                }
            }
            // POST if StudentView
            if ($isStudent && $isEighteen) {
                $tblStudentCustodyList = Consumer::useService()->getStudentCustodyByStudent($tblAccount);
                $Global = $this->getGlobal();
                if ($tblStudentCustodyList) {
                    foreach ($tblStudentCustodyList as $tblStudentCustody) {
                        $tblCustodyAccount = $tblStudentCustody->getServiceTblAccountCustody();
                        if ($tblCustodyAccount) {
                            $Global->POST['ParentAccount'][$tblCustodyAccount->getId()] = $tblCustodyAccount->getId();
                        }
                    }
                    $Global->savePost();
                }
            }
        }

        $tblPersonList = OnlineGradebook::useService()->getPersonListForStudent();

        list($isShownAverage, $isShownDivisionSubjectScore, $isShownGradeMirror, $tblSchoolTypeList, $startYear)
            = Gradebook::useFrontend()->getConsumerSettingsForGradeOverview();

        $BlockedList = array();
        // Jahre ermitteln, in denen Schüler in einer Klasse ist
        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {
                $tblPersonAccountList = Account::useService()->getAccountAllByPerson($tblPerson);
                if ($tblPersonAccountList && current($tblPersonAccountList)->getId() != $tblAccount->getId()) {
                    // Schüler überspringen wenn Sorgeberechtigter geblockt ist
                    if (Consumer::useService()->getStudentCustodyByStudentAndCustody(current($tblPersonAccountList),
                        $tblAccount)) {
                        // Merken des geblockten Accounts
                        $BlockedList[] = current($tblPersonAccountList);
                        continue;
                    }
                }
                $tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
                if ($tblDivisionStudentList) {

                    /** @var TblDivisionStudent $tblDivisionStudent */
                    foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                        $tblDivision = $tblDivisionStudent->getTblDivision();
                        // Schulart Prüfung nur, wenn auch Schularten in den Einstellungen erlaubt werden.
                        if($tblSchoolTypeList && ($tblLevel = $tblDivision->getTblLevel())){
                            if(($tblSchoolType = $tblLevel->getServiceTblType())){
                                if(!in_array($tblSchoolType->getId(), $tblSchoolTypeList)){
                                    // Klassen werden nicht angezeigt, wenn die Schulart nicht freigeben ist.
                                    continue;
                                }
                            }
                        }
                        if ($tblDivision && ($tblYear = $tblDivision->getServiceTblYear())) {
                            // Anzeige nur für Schuljahre die nach dem "Startschuljahr"(Veröffentlichung) liegen
                            if($tblYear->getYear() >= $startYear){
                                $tblDisplayYearList[$tblYear->getId()] = $tblYear;
                                $data[$tblYear->getId()][$tblPerson->getId()][$tblDivision->getId()] = $tblDivision;
                            }
                        }
                    }
                }
            }
        }

        if (!empty($tblDisplayYearList)) {
            $tblDisplayYearList = $this->getSorter($tblDisplayYearList)->sortObjectBy('DisplayName');
            $lastYear = end($tblDisplayYearList);
            /** @var TblYear $year */
            foreach ($tblDisplayYearList as $year) {
                $Stage->addButton(
                    new Standard(
                        ($YearId === null && $year->getId() == $lastYear->getId()) ? new Info(new Bold($year->getDisplayName())) : $year->getDisplayName(),
                        '/ParentStudentAccess/OnlineGradebook',
                        null,
                        array(
                            'YearId' => $year->getId()
                        )
                    )
                );
            }

            if ($YearId === null) {
                $YearId = $lastYear->getId();
            }
        }

        if (($tblYear = Term::useService()->getYearById($YearId))) {
            if (!empty($data)) {
                if (isset($data[$tblYear->getId()])) {
                    foreach ($data[$tblYear->getId()] as $personId => $divisionList) {
                        $tblPerson = Person::useService()->getPersonById($personId);
                        if ($tblPerson && is_array($divisionList)) {
                            $tableHeaderList = array();
                            $tblMainDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson, $tblYear);

                            $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear, $tblMainDivision ? $tblMainDivision : null);
                            if ($tblPeriodList) {
                                $tableHeaderList['Subject'] = 'Fach';
                                foreach ($tblPeriodList as $tblPeriod) {
                                    $tableHeaderList['Period' . $tblPeriod->getId()] = new Bold($tblPeriod->getDisplayName());
                                }

                                if($isShownAverage) {
                                    $tableHeaderList['Average'] = '&#216;';
                                }
                            }

                            Gradebook::useFrontend()->setGradeOverview($tblYear, $tblPerson, $divisionList, $rowList, $tblPeriodList,
                                $tblTestType, $isShownAverage, $isShownDivisionSubjectScore, $isShownGradeMirror,
                                $tableHeaderList, true, false);
                        }
                    }
                }
            }
        }

        $TableContent = array();
        if ($isStudent && $isEighteen) {
            if ($tblPersonSession) {
                $ParentList = array();
                $tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                if ($tblRelationshipType) {
                    $RelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPersonSession,
                        $tblRelationshipType);
                    if ($RelationshipList) {
                        foreach ($RelationshipList as $Relationship) {
                            if ($Relationship->getServiceTblPersonFrom() == $tblPersonSession->getId()) {
                                $ParentList[] = $Relationship->getServiceTblPersonTo();
                            } elseif ($Relationship->getServiceTblPersonTo() == $tblPersonSession->getId()) {
                                $ParentList[] = $Relationship->getServiceTblPersonFrom();
                            }
                        }
                    }
                }
                if (!empty($ParentList)) {
                    /** @var TblPerson $tblPersonParent */
                    foreach ($ParentList as $tblPersonParent) {
                        $tblAccountList = Account::useService()->getAccountAllByPerson($tblPersonParent);
                        if ($tblAccountList) {
                            // abbilden des Sorgeberechtigten mit einem Account
                            /** @var TblAccount $tblAccount */
                            $tblAccountParent = current($tblAccountList);
                            $Item['Check'] = new CheckBox('ParentAccount['.$tblAccountParent->getId().']', ' ',
                                $tblAccountParent->getId());
                            $Item['FirstName'] = $tblPersonParent->getFirstName();
                            $Item['LastName'] = $tblPersonParent->getLastName();
                            if (Consumer::useService()->getStudentCustodyByStudentAndCustody($tblAccount,
                                $tblAccountParent)) {
                                $Item['Status'] = new ToolTip(new DangerText(new EyeMinus()),
                                    'Noten&nbsp;nicht&nbsp;Sichtbar');
                            } else {
                                $Item['Status'] = new ToolTip(new SuccessText(new EyeOpen()),
                                    'Noten&nbsp;sind&nbsp;Sichtbar');
                            }

                            array_push($TableContent, $Item);
                        }
                    }
                }
            }
        }

        $form = new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new TableData($TableContent,
                            new \SPHERE\Common\Frontend\Table\Repository\Title('Sichtbarkeit der Notenübersicht für Sorgeberechtigte sperren'),
                            array(
                                'Check'     => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                'FirstName' => 'Vorname',
                                'LastName'  => 'Nachname',
                                'Status'    => 'Status'
                            ),
                            array(
                                "paging"         => false, // Deaktiviert Blättern
                                "iDisplayLength" => -1,    // Alle Einträge zeigen
                                "searching"      => false, // Deaktiviert Suche
                                "info"           => false,  // Deaktiviert Such-Info)
                                'columnDefs'     => array(
                                    array('width' => '1%', 'targets' => array(0)),
                                    array('width' => '1%', 'targets' => array(-1))
                                ),
                            )
                        )
                    ),
                    new FormColumn(new HiddenField('ParentAccount[IsSubmit]'))
                ))
            )
        );
        $form->appendFormButton(new Primary('Speichern', new Save()));

        $BlockedContent = '';
        if (!empty($BlockedList)) {
            /** @var TblAccount $StudentAccount */
            foreach ($BlockedList as $StudentAccount) {
                $tblPersonStudentList = Account::useService()->getPersonAllByAccount($StudentAccount);
                $tblStudentCustody = Consumer::useService()->getStudentCustodyByStudentAndCustody($StudentAccount,
                    $tblAccount);
                $BlockerPerson = new NotAvailable();
                // find Person who Blocked
                if ($tblStudentCustody) {
                    $tblAccountBlocker = $tblStudentCustody->getServiceTblAccountBlocker();
                    if ($tblAccountBlocker) {
                        $tblPersonBlockerList = Account::useService()->getPersonAllByAccount($tblAccountBlocker);
                        /** @var TblPerson $tblPersonBlocker */
                        if ($tblPersonBlockerList && ($tblPersonBlocker = current($tblPersonBlockerList))) {
                            $BlockerPerson = $tblPersonBlocker->getLastFirstName();
                        }
                    }
                }
                /** @var TblPerson $tblPersonStudent */
                if ($tblPersonStudent = current($tblPersonStudentList)) {
                    $BlockedContent .= new Title($tblPersonStudent->getLastFirstName())
                        .new Warning('Die Notenübersicht wurde durch '.$BlockerPerson.' gesperrt.');
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        ($YearId !== null ?
                            new Panel('Schuljahr', $tblYear->getDisplayName(), Panel::PANEL_TYPE_INFO)
                            : '')
                    )
                ))),
                ($YearId !== null ? new LayoutGroup($rowList) : null),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            $BlockedContent
                        )
                    )
                )
            ))
            .($isStudent && $isEighteen
                ? new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(new Well(
                                Gradebook::useService()->setDisableParent($form, $ParentAccount, $tblAccount)
                            ))
                        ))
                    )
                )
                : '')
        );

        return $Stage;
    }
}