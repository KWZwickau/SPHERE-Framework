<?php

namespace SPHERE\Application\Platform\System\DataMaintenance;

use SPHERE\Application\Api\Platform\DataMaintenance\ApiDocumentStorage;
use SPHERE\Application\Api\Platform\DataMaintenance\ApiMigrateDivision;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account as AccountAuthorization;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblUser;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\User\Account\Account;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
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
//                    new LayoutColumn(array(
//                        new TitleLayout('Zensuren/Noten'),
//                        (new Standard('Verschieben', __NAMESPACE__.'/Grade'))
//                        .  (new Standard('Unerreichbare Zensuren', __NAMESPACE__.'/GradeUnreachable'))
//                    )),
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
                    new LayoutColumn(array(
                            new TitleLayout('Document Storage'),
                            new Standard('Datei-Größe setzen für alte Dateien', __NAMESPACE__.'/DocumentStorage/FileSize'),
                            new Standard('Datei-Größe aller Mandanten', __NAMESPACE__.'/DocumentStorage/AllConsumers')
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
                        new Code("
UPDATE ".$Acronym."_BillingInvoice.tblBankReference SET ReferenceDate = date_add(ReferenceDate, interval 1 YEAR);
UPDATE ".$Acronym."_BillingInvoice.tblBasket SET Year = Year + 1, TargetTime = date_add(TargetTime, interval 1 YEAR), BillTime = date_add(BillTime, interval 1 YEAR);
UPDATE ".$Acronym."_BillingInvoice.tblBasket SET SepaDate = date_add(SepaDate, interval 1 YEAR) where SepaDate IS NOT NULL;
UPDATE ".$Acronym."_BillingInvoice.tblBasket SET DatevDate = date_add(DatevDate, interval 1 YEAR) where DatevDate IS NOT NULL;
UPDATE ".$Acronym."_BillingInvoice.tblDebtorSelection SET FromDate = date_add(FromDate, interval 1 YEAR);
UPDATE ".$Acronym."_BillingInvoice.tblDebtorSelection SET ToDate = date_add(ToDate, interval 1 YEAR) where ToDate IS NOT NULL;
UPDATE ".$Acronym."_BillingInvoice.tblInvoice SET InvoiceNumber = concat(substr(InvoiceNumber, 1, 4)+1, substr(InvoiceNumber, 5, 20)), Year = Year + 1, TargetTime = date_add(TargetTime, interval 1 YEAR), BillTime = date_add(BillTime, interval 1 YEAR);
UPDATE ".$Acronym."_BillingInvoice.tblItemCalculation SET DateFrom = date_add(DateFrom, interval 1 YEAR);
UPDATE ".$Acronym."_BillingInvoice.tblItemCalculation SET DateTo = date_add(DateTo, interval 1 YEAR) where DateTo IS NOT NULL;
UPDATE ".$Acronym."_DocumentStorage.tblDirectory SET Name = concat(substr(Name, 1, 4)+1, \"/\", substr(Name, 6, 2)+1) where Identifier like \"TBL-YEAR-ID%\";".
// Funktioniert durch geänderten String nicht mehr. Ein Update ist hier auch nicht zwingend erforderlich.
// "UPDATE ".$Acronym."_DocumentStorage.tblFile SET Name = concat(substr(Name, 1, 4)+1, \"/\", substr(Name, 6, 2)+1, substr(Name, 8, 100)), Description = concat(substr(Description, 1, 16), substr(Description, 17, 4)+1, substr(Description, 21, 100));
"UPDATE ".$Acronym."_EducationApplication.tblAbsence SET FromDate = date_add(FromDate, interval 1 YEAR);
UPDATE ".$Acronym."_EducationApplication.tblAbsence SET ToDate = date_add(ToDate, interval 1 YEAR) where ToDate IS NOT NULL;
UPDATE ".$Acronym."_EducationApplication.tblClassRegisterCourseContent SET Date = date_add(Date, interval 1 YEAR);
UPDATE ".$Acronym."_EducationApplication.tblClassRegisterCourseContent SET DateHeadmaster = date_add(DateHeadmaster, interval 1 YEAR) where DateHeadmaster IS NOT NULL;
UPDATE ".$Acronym."_EducationApplication.tblClassRegisterDiary SET Date = date_add(Date, interval 1 YEAR);
UPDATE ".$Acronym."_EducationApplication.tblClassRegisterInstructionItem SET Date = date_add(Date, interval 1 YEAR);
UPDATE ".$Acronym."_EducationApplication.tblClassRegisterLessonContent SET Date = date_add(Date, interval 1 YEAR);
UPDATE ".$Acronym."_EducationApplication.tblClassRegisterLessonWeek SET Date = date_add(Date, interval 1 YEAR);
UPDATE ".$Acronym."_EducationApplication.tblClassRegisterLessonWeek SET DateDivisionTeacher = date_add(DateDivisionTeacher, interval 1 YEAR) where DateDivisionTeacher IS NOT NULL;
UPDATE ".$Acronym."_EducationApplication.tblClassRegisterLessonWeek SET DateHeadmaster = date_add(DateHeadmaster, interval 1 YEAR) where DateHeadmaster IS NOT NULL;
UPDATE ".$Acronym."_EducationApplication.tblClassRegisterTimetable SET DateFrom = date_add(DateFrom, interval 1 YEAR);
UPDATE ".$Acronym."_EducationApplication.tblClassRegisterTimetable SET DateTo = date_add(DateTo, interval 1 YEAR) where DateTo IS NOT NULL;
UPDATE ".$Acronym."_EducationApplication.tblClassRegisterTimetableWeek SET Date = date_add(Date, interval 1 YEAR);
UPDATE ".$Acronym."_EducationApplication.tblGraduationTask SET Date = date_add(Date, interval 1 YEAR), FromDate = date_add(FromDate, interval 1 YEAR), ToDate = date_add(ToDate, interval 1 YEAR);
UPDATE ".$Acronym."_EducationApplication.tblGraduationTest SET Date = date_add(Date, interval 1 YEAR) where Date IS NOT NULL;
UPDATE ".$Acronym."_EducationApplication.tblGraduationTest SET FinishDate = date_add(FinishDate, interval 1 YEAR) where FinishDate IS NOT NULL;
UPDATE ".$Acronym."_EducationApplication.tblGraduationTest SET CorrectionDate = date_add(CorrectionDate, interval 1 YEAR) where CorrectionDate IS NOT NULL;
UPDATE ".$Acronym."_EducationApplication.tblGraduationTest SET ReturnDate = date_add(ReturnDate, interval 1 YEAR) where ReturnDate IS NOT NULL;
UPDATE ".$Acronym."_EducationApplication.tblGraduationTestGrade SET Date = date_add(Date, interval 1 YEAR) where Date IS NOT NULL;
UPDATE ".$Acronym."_EducationApplication.tblLessonDivisionCourseMember SET LeaveDate = date_add(LeaveDate, interval 1 YEAR) where LeaveDate IS NOT NULL;
UPDATE ".$Acronym."_EducationApplication.tblLessonStudentEducation SET LeaveDate = date_add(LeaveDate, interval 1 YEAR) where LeaveDate IS NOT NULL;
UPDATE ".$Acronym."_EducationLessonTerm.tblHoliday SET FromDate = date_add(FromDate, interval 1 YEAR), ToDate = date_add(ToDate, interval 1 YEAR);
UPDATE ".$Acronym."_EducationLessonTerm.tblPeriod SET FromDate = date_add(FromDate, interval 1 YEAR), ToDate = date_add(ToDate, interval 1 YEAR);
Update ".$Acronym."_EducationLessonTerm.tblYear SET Name = CONCAT(SUBSTRING_INDEX(Name, \"/\", 1)+1,\"/\",SUBSTRING_INDEX(Name, \"/\", -1)+1), YEAR = CONCAT(SUBSTRING_INDEX(YEAR, \"/\", 1)+1,\"/\",SUBSTRING_INDEX(YEAR, \"/\", -1)+1);
UPDATE ".$Acronym."_PeopleMeta.tblClub SET EntryDate = date_add(EntryDate, interval 1 YEAR) where EntryDate IS NOT NULL;
UPDATE ".$Acronym."_PeopleMeta.tblClub SET ExitDate = date_add(ExitDate, interval 1 YEAR) where ExitDate IS NOT NULL;
UPDATE ".$Acronym."_PeopleMeta.tblCommonBirthDates SET Birthday = date_add(Birthday, interval 1 YEAR) where Birthday IS NOT NULL;
UPDATE ".$Acronym."_PeopleMeta.tblHandyCap SET Date = date_add(Date, interval 1 YEAR) where Date IS NOT NULL;
UPDATE ".$Acronym."_PeopleMeta.tblPersonMasern SET MasernDate = date_add(MasernDate, interval 1 YEAR);
UPDATE ".$Acronym."_PeopleMeta.tblProspectAppointment SET ReservationDate = date_add(ReservationDate, interval 1 YEAR) where ReservationDate IS NOT NULL;
UPDATE ".$Acronym."_PeopleMeta.tblProspectAppointment SET InterviewDate = date_add(InterviewDate, interval 1 YEAR) where InterviewDate IS NOT NULL;
UPDATE ".$Acronym."_PeopleMeta.tblProspectAppointment SET TrialDate = date_add(TrialDate, interval 1 YEAR) where TrialDate IS NOT NULL;
UPDATE ".$Acronym."_PeopleMeta.tblProspectReservation SET ReservationYear = concat(substr(ReservationYear, 1, 4)+1, \"/\", substr(ReservationYear, 6, 4)+1) where ReservationYear != '';
UPDATE ".$Acronym."_PeopleMeta.tblSpecial SET Date = date_add(Date, interval 1 YEAR);
UPDATE ".$Acronym."_PeopleMeta.tblStudent SET SchoolAttendanceStartDate = date_add(SchoolAttendanceStartDate, interval 1 YEAR) where SchoolAttendanceStartDate IS NOT NULL;
UPDATE ".$Acronym."_PeopleMeta.tblStudentBaptism SET BaptismDate = date_add(BaptismDate, interval 1 YEAR) where BaptismDate IS NOT NULL;
UPDATE ".$Acronym."_PeopleMeta.tblStudentMedicalRecord SET MasernDate = date_add(MasernDate, interval 1 YEAR) where MasernDate IS NOT NULL;
UPDATE ".$Acronym."_PeopleMeta.tblStudentTransfer SET TransferDate = date_add(TransferDate, interval 1 YEAR) where TransferDate IS NOT NULL;
UPDATE ".$Acronym."_PeopleMeta.tblSupport SET Date = date_add(Date, interval 1 YEAR) where Date IS NOT NULL;
UPDATE ".$Acronym."_SettingConsumer.tblGenerateCertificate SET Date = date_add(Date, interval 1 YEAR) where Date IS NOT NULL;
UPDATE ".$Acronym."_SettingConsumer.tblLeaveInformation SET Value = CONCAT(SUBSTRING_INDEX(Value, '.',2),'.',YEAR(CURDATE())) where Field like 'CertificateDate';
UPDATE ".$Acronym."_SettingConsumer.tblUserAccount SET ExportDate = date_add(ExportDate, interval 1 YEAR) where ExportDate IS NOT NULL;
UPDATE ".$Acronym."_SettingConsumer.tblUserAccount SET GroupByTime = date_add(GroupByTime, interval 1 YEAR) where GroupByTime IS NOT NULL;
UPDATE ".$Acronym."_SettingConsumer.tblUserAccount SET UpdateDate = date_add(UpdateDate, interval 1 YEAR) where UpdateDate IS NOT NULL;"
// UPDATE ".$Acronym."_SettingConsumer.tblPrepareInformation SET Value = CONCAT(SUBSTRING_INDEX(Value, '.',2),'.',YEAR(CURDATE())) where Field LIKE 'DateConference' OR Field LIKE 'DateConsulting'OR Field LIKE 'DateCertifcate';"
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

    /**
     * @return Stage
     */
    public function frontendFileSize(): Stage
    {
        $stage = new Stage('Document Storage', 'Datei-Größe setzen für alte Dateien');
        $stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        ini_set('memory_limit', '2G');

        if (Storage::useService()->getBinariesWithoutFileSize(1)) {
            $stage->setContent(
//                new Warning('Press F12 before', new Exclamation())
                ApiDocumentStorage::receiverBlock(ApiDocumentStorage::pipelineStatus(ApiDocumentStorage::STATUS_BUTTON), 'Status')
                . ApiDocumentStorage::receiverBlock('', 'FileSize_0')
            );
        } else {
            $stage->setContent(new Success('Die Datei-Größen wurden bereits für alle Dateien gesetzt.'));
        }

        return $stage;
    }

    /**
     * @return Stage
     */
    public function frontendAllConsumers(): Stage
    {
        $stage = new Stage('Document Storage', 'Datei-Größe aller Mandanten');
        $stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        ini_set('memory_limit', '2G');

        $sumAllConsumer['Total'] = 0;
        $sumAllConsumer['WithoutFile'] = 0;
        $content = array();
        if (($tblConsumerAll = GatekeeperConsumer::useService()->getConsumerAll())) {
            // aktuell nicht genutzte Mandanten
            $blackList = Consumer::useService()->getConsumerBlackList();
            foreach ($tblConsumerAll as $tblConsumer) {
                if (!isset($blackList[$tblConsumer->getAcronym()])) {
                    $sumTotal = intdiv(Storage::useService()->getFileSizeByConsumer($tblConsumer), 1024);
                    $sumWithoutFile = intdiv(Storage::useService()->getFileSizeByConsumer($tblConsumer, true), 1024);

                    $sumAllConsumer['Total'] += $sumTotal;
                    $sumAllConsumer['WithoutFile'] += $sumWithoutFile;

                    $content[] = array(
                        'Acronym' => $tblConsumer->getAcronym(),
                        'Name' => $tblConsumer->getName(),
                        'FileSizeTotal' => $sumTotal,
                        'FileSizeWithoutFile' => $sumWithoutFile,
                    );
                }
            }
        }

        $stage->setContent(
            new Panel(
                'Datei-Größe über alle Mandanten',
                array(
                    'Gesamt in MByte: ' . $sumAllConsumer['Total'],
                    'Ohne File in MByte: ' . $sumAllConsumer['WithoutFile'],
                ),
                Panel::PANEL_TYPE_INFO
            )
            . new TableData(
                $content,
                null,
                array(
                    'Acronym' => 'Kürzel',
                    'Name' => 'Name',
                    'FileSizeTotal' => 'Gesamt in MByte',
                    'FileSizeWithoutFile' => 'Ohne File in MByte'
                )
            )
        );

        return $stage;
    }
}