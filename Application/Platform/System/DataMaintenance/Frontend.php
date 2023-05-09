<?php

namespace SPHERE\Application\Platform\System\DataMaintenance;

use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiGradeMaintenance;
use SPHERE\Application\Api\Platform\DataMaintenance\ApiMigrateDivision;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account as AccountAuthorization;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblUser;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\User\Account\Account;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Server;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title as TitleLayout;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Code;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    public function frontendDataMaintenance()
    {

        $Stage = new Stage('Datenpflege');
        // Schüler Account Zählung
        $tblUserAccountList = Account::useService()->getUserAccountAllByType(TblUserAccount::VALUE_TYPE_STUDENT);
        $StudentAccountCount = ($tblUserAccountList ? count($tblUserAccountList) : 0);
        // Sorgeberechtigte Account Zählung
        $tblUserAccountList = Account::useService()->getUserAccountAllByType(TblUserAccount::VALUE_TYPE_CUSTODY);
        $CustodyAccountCount = ($tblUserAccountList ? count($tblUserAccountList) : 0);

        // Import Integration
//        $IsImport = false;
//        if(Student::useService()->countSupportAll() !== '0'){
//            $IsImport = true;
//        }
//        if(!$IsImport && Student::useService()->countSpecialAll() !== '0'){
//            $IsImport = true;
//        }
//
//        $ImportCount = 0;
//        if(!$IsImport && ($tblStudentAll = Student::useService()->getStudentAll())) {
//            foreach ($tblStudentAll as $tblStudent) {
//                $tblPerson = $tblStudent->getServiceTblPerson();
//                if (($tblStudentIntegration = $tblStudent->getTblStudentIntegration())) {
//                    $Request = $tblStudentIntegration->getCoachingRequestDate();
//                    $Counsel = $tblStudentIntegration->getCoachingCounselDate();
//                    $Decision = $tblStudentIntegration->getCoachingDecisionDate();
//                    if ($tblPerson && ($Request || $Counsel || $Decision)) {
//                        $ImportCount++;
//                    }
//                }
//            }
//        }
//        if($IsImport){
//            $IntegrationColumn = new LayoutColumn('');
//        } else {
//            $IntegrationColumn = new LayoutColumn(array(
//                new TitleLayout('Integration', 'Übernehmen'),
//                new Standard('Import aus alter Datenbank '.new Label($ImportCount, Label::LABEL_TYPE_INFO), __NAMESPACE__.'/Integration', new CogWheels())
//            ));
//        }

        // SoftRemoved Person
        $CountSoftRemovePerson = 0;
        if (($tblPersonList = Person::useService()->getPersonAllBySoftRemove())) {
            $CountSoftRemovePerson = count($tblPersonList);
        }


        $Stage->setContent( new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        new TitleLayout('Gelöschte Personen'),
                        ($CountSoftRemovePerson >= 1
                            ? new Standard('Personenübersicht ('.$CountSoftRemovePerson.')', __NAMESPACE__.'/Restore/Person')
                            : new Success('Keine Personen gelöscht')
                        )
                    )),
                    new LayoutColumn(array(
                        new TitleLayout('Benutzer-Accounts löschen'),
                        new Standard('Alle Schüler '.new Label($StudentAccountCount, Label::LABEL_TYPE_INFO), __NAMESPACE__.'/OverView', new EyeOpen(),
                            array('AccountType' => 'STUDENT')),
                        new Standard('Alle Sorgeberechtigte '.new Label($CustodyAccountCount, Label::LABEL_TYPE_INFO), __NAMESPACE__.'/OverView', new EyeOpen(),
                            array('AccountType' => 'CUSTODY'))
                    )),
                    new LayoutColumn(array(
                        new TitleLayout('Zensuren/Noten'),
                        (new Standard('Verschieben', __NAMESPACE__.'/Grade'))
                        .  (new Standard('Unerreichbare Zensuren', __NAMESPACE__.'/GradeUnreachable'))
                    )),
                    new LayoutColumn(array(
                            new TitleLayout('Jährliches DEV Update (Datum) wird um 1 Jahr erhöht'),
                            new Standard('Jährliches Update', __NAMESPACE__.'/Yearly', null, array(), 'Anzeige eines SQL Script\'s')
                        )
                    ),
//                    $IntegrationColumn,
                    new LayoutColumn(array(
                            new TitleLayout('Migration Klassen zu Kursen'),
                            new Standard('Migration Klassen', __NAMESPACE__.'/DivisionCourse')
                        )
                    ),
                ))
            )
        ));

        return $Stage;
    }

    /**
     * @return Stage
     */
    public static function frontendPersonRestore()
    {
        $Stage = new Stage('Personen Wiederherstellen', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $dataList = array();
        if (($tblPersonList = Person::useService()->getPersonAllBySoftRemove())) {
            foreach ($tblPersonList as $tblPerson) {
                if (($date = $tblPerson->getEntityRemove())) {
                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson, true);
                    $dataList[] = array(
                        'EntityRemove' => $date->format('d.m.Y'),
                        'Time' => $date->format('H:i:s'),
                        'Name' => $tblPerson->getLastFirstName(),
                        'Address' => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Option' => new Standard(
                            '',
                            '\Platform\System\DataMaintenance\Restore\Person\Selected',
                            new EyeOpen(),
                            array(
                                'PersonId' => $tblPerson->getId()
                            ),
                            'Anzeigen'
                        )
                    );
                }
            }
        }

        $Stage->setContent(
            empty($dataList)
                ? new Warning('Es sind keine soft gelöschten Person vorhanden.', new Exclamation())
                : new TableData(
                $dataList,
                null,
                array(
                    'EntityRemove' => 'Gelöscht am',
                    'Time' => 'Uhrzeit',
                    'Name' => 'Name',
                    'Address' => 'Adresse',
                    'Option' => ''
                ),
                array(
                    'order' => array(
                        array('0', 'desc'),
                        array('1', 'desc'),
                    ),
                    'columnDefs' => array(
                        array('type' => 'de_date', 'targets' => 0),
                        array('type' => 'de_time', 'targets' => 1),
                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                        array('width' => '1%', 'targets' => -1),
                    ),
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $PersonId
     * @param bool $IsRestore
     *
     * @return Stage|string
     */
    public function frontendPersonRestoreSelected($PersonId = null, $IsRestore = false)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId, true))) {
            $Stage = new Stage('Person Wiederherstellen', 'Anzeigen');
            $Stage->addButton(new Standard('Zurück', __NAMESPACE__.'/Restore/Person', new ChevronLeft()));

            if (!$IsRestore) {
                $Stage->addButton(
                    new Standard('Alle Daten wiederherstellen', __NAMESPACE__.'/Restore/Person/Selected', new Upload(),
                        array(
                            'PersonId' => $PersonId,
                            'IsRestore' => true
                        )
                    )
                );
            }

            if ($IsRestore) {
                $columns =  array(
                    'Number' => '#',
                    'Type' => 'Typ',
                    'Value' => 'Wert'
                );
            } else {
                $columns =  array(
                    'Number' => '#',
                    'Type' => 'Typ',
                    'Value' => 'Wert',
                    'EntityRemove' => 'Gelöscht am'
                );
            }

            $Stage->setContent(
                ($IsRestore ? new Success('Die Daten wurden wieder hergestellt.', new SuccessIcon()) : '')
                . new TableData(Person::useService()->getRestoreDetailList($tblPerson, $IsRestore), null, $columns,
                    array(
                        "paging" => false, // Deaktivieren Blättern
                        "iDisplayLength" => -1,    // Alle Einträge zeigen
                    )
                )
            );

            return $Stage;
        } else {
            return new Stage('Person Wiederherstellen', 'Anzeigen')
                . new Danger('Die Person wurde nicht gefunden', new Exclamation())
                . new Redirect(__NAMESPACE__.'/Restore/Person', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param null $AccountType
     *
     * @return Stage
     */
    public function frontendUserAccount($AccountType = null)
    {

        $Stage = new Stage('Datenpflege', '');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $TableContent = array();
        if ($AccountType) {
            $tblUserAccountList = Account::useService()->getUserAccountAllByType($AccountType);
            if ($tblUserAccountList) {
                array_walk($tblUserAccountList, function (TblUserAccount $tblUserAccount) use (&$TableContent) {
                    $Item['Account'] = '';
                    $Item['User'] = '';
                    $Item['Type'] = '';

                    $tblAccount = $tblUserAccount->getServiceTblAccount();
                    if ($tblAccount) {
                        $Item['Account'] = $tblAccount->getUsername();
                        $tblUserList = AccountAuthorization::useService()->getUserAllByAccount($tblAccount);
                        /** @var TblUser $tblUser */
                        if ($tblUserList && ($tblUser = current($tblUserList))) {
                            $tblPerson = $tblUser->getServiceTblPerson();
                            if ($tblPerson) {
                                $Item['User'] = $tblPerson->getLastFirstName();
                            }
                        }
                    }
                    $Type = $tblUserAccount->getType();
                    if ($Type === 'STUDENT') {
                        $Item['Type'] = 'Schüler';
                    } elseif ($Type === 'CUSODY') {
                        $Item['Type'] = 'Sorgeberechtigte';
                    }
                    array_push($TableContent, $Item);
                });
            } else {
                $Stage->setContent(
                    new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new Warning('Es sind keine Accounts für den Typ: "'.$AccountType.'" vorhanden')
                                )
                            )
                        )
                    )
                );
            }
        }
        if (!empty($TableContent)) {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new TableData($TableContent, null,
                                    array(
                                        'Account' => 'Benutzer-Account',
                                        'User'    => 'Person',
                                        'Type'    => 'Account-Typ'
                                    )
                                )
                            ),
                            new LayoutColumn(
                                new DangerLink('Löschen', __NAMESPACE__.'/Destroy', new Remove(),
                                    array('AccountType' => $AccountType))
                            )
                        ))
                    )
                )
            );
        }

        return $Stage;
    }

    /**
     * @param string $AccountType
     * @param bool   $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyAccount($AccountType, $Confirm = false)
    {

        $Stage = new Stage('Benutzeraccounts', 'Löschen');

        if (($tblUserAccountList = Account::useService()->getUserAccountAllByType($AccountType))) {
            $Stage->addButton(new Standard(
                'Zurück', __NAMESPACE__, new ChevronLeft()
            ));

            if (!$Confirm) {
                $Type = 'Unbekannt';
                if ($AccountType == 'STUDENT') {
                    $Type = 'Schüler';
                } elseif ($AccountType == 'CUSTODY') {
                    $Type = 'Sorgeberechtigte';
                }
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(
                            new Question().' Löschabfrage',
                            'Sollen die Accounts mit dem Typ "'.new Bold($Type).'" wirklich gelöscht werden? (Anzahl: '.count($tblUserAccountList).')',
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', __NAMESPACE__.'/Destroy', new Ok(),
                                array(
                                    'AccountType' => $AccountType,
                                    'Confirm'     => true
                                )
                            )
                            .new Standard(
                                'Nein', __NAMESPACE__, new Disable()
                            )
                        ),
                    )))))
                );
            } else {

                $AccountList = array();
                $UserAccountList = array();
                //Service delete complete Account

                foreach ($tblUserAccountList as $tblUserAccount) {
                    if ($tblUserAccount) {
                        $tblAccount = $tblUserAccount->getServiceTblAccount();
                        if ($tblAccount) {
                            // remove tblAccount
                            if (!AccountAuthorization::useService()->destroyAccount($tblAccount)) {
                                $AccountList[] = $tblAccount;
                            }
                        }
                        // remove tblUserAccount
                        if (!Account::useService()->removeUserAccount($tblUserAccount)) {
                            $UserAccountList[] = $tblUserAccount;
                        }
                    }
                }


                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            (empty($AccountList) && empty($UserAccountList)
                                ? new Success(new SuccessIcon().' Die Accounts wurden erfolgreich gelöscht')
                                .new Redirect('/Platform/System/DataMaintenance', Redirect::TIMEOUT_SUCCESS)
                                : new Danger(new Remove().' Die Angezeigten Accounts konnten nicht gelöscht werden.')
                                .new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn(
                                        new TableData($AccountList, new Title('Account'), array(
                                            'Id'                 => 'Id',
                                            'Username'           => 'Benutzer',
                                            'serviceTblConsumer' => 'Consumer Id',
                                        ))
                                        , 6),
                                    new LayoutColumn(
                                        new TableData($UserAccountList, new Title('UserAccount')
                                            , array(
                                                'Id'                => 'Id',
                                                'serviceTblAccount' => 'Account Id',
                                                'EntityCreate'      => 'Erstellungsdatum',
                                                'EntityUpdate'      => 'Letztes Update',
                                            ))
                                        , 6),
                                ))))
                            )
                        ))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban().' Es konnten keine Accounts gefunden werden'),
                        new Redirect('/Platform/System/DataMaintenance', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param array|null $Data
     *
     * @return Stage
     */
    public function frontendGrade(?array $Data = null): Stage
    {
        $stage = new Stage('Datenpflege', 'Zensuren verschieben');
        $stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $tblYearList = Term::useService()->getYearAll();

        $stage->setContent(
            ApiGradeMaintenance::receiverBlock('', 'Message')
            . new Form(new FormGroup(new FormRow(new FormColumn(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Source',
                                array(
                                    (new SelectBox('Data[Source][YearId]', 'Schuljahr',
                                        array('{{ Year }} {{ Description }}' => $tblYearList), null, false, SORT_DESC))
                                        ->ajaxPipelineOnChange(array(
                                            ApiGradeMaintenance::pipelineLoadDivisionSelect($Data, 'Source')
                                        ))->setRequired(),
                                    ApiGradeMaintenance::receiverBlock('', 'SourceDivisionSelect'),
                                    ApiGradeMaintenance::receiverBlock('', 'SourceDivisionSubjectSelect'),
                                    ApiGradeMaintenance::receiverBlock('', 'SourceDivisionSubjectInformation')
                                ),
                                Panel::PANEL_TYPE_INFO
                            )
                            , 6),
                        new LayoutColumn(
                            new Panel(
                                'Target',
                                array(
                                    (new SelectBox('Data[Target][YearId]', 'Schuljahr',
                                        array('{{ Year }} {{ Description }}' => $tblYearList), null, false, SORT_DESC))
                                        ->ajaxPipelineOnChange(array(
                                                ApiGradeMaintenance::pipelineLoadDivisionSelect($Data, 'Target'))
                                        )->setRequired(),
                                    ApiGradeMaintenance::receiverBlock('', 'TargetDivisionSelect'),
                                    ApiGradeMaintenance::receiverBlock('', 'TargetDivisionSubjectSelect'),
                                    ApiGradeMaintenance::receiverBlock('', 'TargetDivisionSubjectInformation')
                                ),
                                Panel::PANEL_TYPE_INFO
                            )
                            , 6)
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(ApiGradeMaintenance::receiverBlock(ApiGradeMaintenance::loadMoveButton($Data), 'MoveButton'))
                    )),
                    new LayoutRow(array(
                        new LayoutColumn('&nbsp;'),
                        new LayoutColumn(ApiGradeMaintenance::receiverBlock('', 'OutputInformation'))
                    )),
                )))
            ))))
        );

        return $stage;
    }

    /**
     * @param array|null $Data
     *
     * @return Stage
     */
    public function frontendGradeUnreachable(?array $Data = null): Stage
    {
        $stage = new Stage('Unerreichbare Zensuren');
        $stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $tblYearList = Term::useService()->getYearAll();

        $stage->setContent(
            (new SelectBox('Data[YearId]', 'Schuljahr',
                array('{{ Year }} {{ Description }}' => $tblYearList), null, false, SORT_DESC))
                ->ajaxPipelineOnChange(array(
                    ApiGradeMaintenance::pipelineLoadUnreachableGrades($Data)
                ))->setRequired()
            . ApiGradeMaintenance::receiverBlock('', 'UnreachableGrades')
        );

        return  $stage;
    }

    /**
     * @return Stage
     */
    public function frontendYearly()
    {

        $tblConsumer = \SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer::useService()->getConsumerBySession();
        $Acronym = $tblConsumer->getAcronym();
        $Stage = new Stage('SQL Anweisung');
        $Stage->addButton(new Standard('Zurück', '/Platform/System/Anonymous', new ChevronLeft()));

        $Stage->setContent(new Layout(
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Info('Ausführen des SQL Script\'s in der Datenbank ('.new Bold('aktueller Mandant!').')'
                            .new Container('Diesen bitte in der Datenbank ausführen.'))
                        , 6),
                    new LayoutColumn(
                        new Info(
                            new Container(new Bold('Nach SQL Script notwendig!&nbsp;&nbsp;&nbsp;')
                                .new External('Cache löschen', '/Platform/System/Cache', new Server(),
                                    array('Clear' => 1)))
                        )
                        , 6)
                )),
                new LayoutRow(
                    new LayoutColumn(
                        new Code("UPDATE ".$Acronym."_PeopleMeta.tblCommonBirthDates SET Birthday = date_add(Birthday, interval 1 YEAR);
UPDATE ".$Acronym."_PeopleMeta.tblSpecial SET Date = date_add(Date, interval 1 YEAR);
UPDATE ".$Acronym."_PeopleMeta.tblStudent SET SchoolAttendanceStartDate = date_add(SchoolAttendanceStartDate, interval 1 YEAR);
UPDATE ".$Acronym."_PeopleMeta.tblStudentBaptism SET BaptismDate = date_add(BaptismDate, interval 1 YEAR);
UPDATE ".$Acronym."_PeopleMeta.tblStudentTransfer SET TransferDate = date_add(TransferDate, interval 1 YEAR);
UPDATE ".$Acronym."_PeopleMeta.tblSupport SET Date = date_add(Date, interval 1 YEAR);
UPDATE ".$Acronym."_EducationClassRegister.tblAbsence SET FromDate = date_add(FromDate, interval 1 YEAR), ToDate = date_add(ToDate, interval 1 YEAR);
UPDATE ".$Acronym."_EducationGraduationEvaluation.tblTask SET Date = date_add(Date, interval 1 YEAR), FromDate = date_add(FromDate, interval 1 YEAR), ToDate = date_add(ToDate, interval 1 YEAR);
UPDATE ".$Acronym."_EducationGraduationEvaluation.tblTest SET Date = date_add(Date, interval 1 YEAR), CorrectionDate = date_add(CorrectionDate, interval 1 YEAR), ReturnDate = date_add(ReturnDate, interval 1 YEAR);
UPDATE ".$Acronym."_EducationGraduationGradebook.tblGrade SET Date = date_add(Date, interval 1 YEAR);
UPDATE ".$Acronym."_EducationLessonDivision.tblDivisionStudent SET LeaveDate = date_add(LeaveDate, interval 1 YEAR);
UPDATE ".$Acronym."_EducationLessonTerm.tblHoliday SET FromDate = date_add(FromDate, interval 1 YEAR), ToDate = date_add(ToDate, interval 1 YEAR);
UPDATE ".$Acronym."_EducationLessonTerm.tblPeriod SET FromDate = date_add(FromDate, interval 1 YEAR), ToDate = date_add(ToDate, interval 1 YEAR);
Update ".$Acronym."_EducationLessonTerm.tblYear SET Name = CONCAT(SUBSTRING_INDEX(Name, '/', 1)+1,'/',SUBSTRING_INDEX(Name, '/', -1)+1), YEAR = CONCAT(SUBSTRING_INDEX(YEAR, '/', 1)+1,'/',SUBSTRING_INDEX(YEAR, '/', -1)+1);
UPDATE ".$Acronym."_SettingConsumer.tblLeaveInformation SET Value = CONCAT(SUBSTRING_INDEX(Value, '.',2),'.',YEAR(CURDATE())) where Field like 'CertificateDate';
UPDATE ".$Acronym."_SettingConsumer.tblPrepareCertificate SET Date = date_add(Date, interval 1 YEAR);
UPDATE ".$Acronym."_SettingConsumer.tblPrepareInformation SET Value = CONCAT(SUBSTRING_INDEX(Value, '.',2),'.',YEAR(CURDATE())) where Field LIKE 'DateConference' OR Field LIKE 'DateConsulting'OR Field LIKE 'DateCertifcate';"
                        )
                    )
                )
            ))
        ));

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendDivisionCourse(): Stage
    {
        $stage = new Stage('Migration Klassen zu Kursen');
        $stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        ini_set('memory_limit', '2G');

        if ((DivisionCourse::useService()->getDivisionCourseAll())) {
            $stage->setContent(new Success('Die Daten wurden bereits migriert.'));
        } else {
            $content = ApiMigrateDivision::receiverBlock('', 'MigrateDivisions')
                . ApiMigrateDivision::receiverBlock('', 'MigrateGroups')
                . ApiMigrateDivision::receiverBlock('', 'MigrateScoreRules')
                . ApiMigrateDivision::receiverBlock('', 'MigrateMinimumGradeCounts')
                . ApiMigrateDivision::receiverBlock('', 'MigrateStudentSubjectLevels');
            if (($tblYearList = Term::useService()->getYearAll())) {
                $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('Id');
                /** @var TblYear $tblYear */
                foreach ($tblYearList as $tblYear) {
                    $content .= new Panel(
                        $tblYear->getDisplayName(),
                        ApiMigrateDivision::receiverBlock('', 'MigrateYear_' . $tblYear->getId()),
                        Panel::PANEL_TYPE_INFO
                    );
                }
            }

            $stage->setContent(
                new Warning('Press F12 before Migration', new Exclamation())
                . ApiMigrateDivision::receiverBlock(ApiMigrateDivision::pipelineStatus(ApiMigrateDivision::STATUS_BUTTON), 'Status')
                . $content
            );
        }

        return $stage;
    }
}