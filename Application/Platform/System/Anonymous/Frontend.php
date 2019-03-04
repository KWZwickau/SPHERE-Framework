<?php
namespace SPHERE\Application\Platform\System\Anonymous;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\CogWheels;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Server;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\Warning as WarningLink;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Code;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Platform\System\Anonymous
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendAnonymous()
    {

        $Stage = new Stage('Daten Anonymisieren');

        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Info(
                            new Container(new Bold('Jährliches Update (Datum)'))
                            .new Standard('Jährliches Update', __NAMESPACE__.'/Yearly', null, array(), 'Anzeige eines SQL Script\'s')
                        )
                    , 8),
                    new LayoutColumn(
                        new Danger(new Container(new Bold('Mandanten Anonymisieren:'))
                            .new Container(new DangerLink('1. Personen Anonymisieren', __NAMESPACE__.'/UpdatePerson', null, array(), 'Passiert sofort!')
                                .(new ToolTip(new InfoIcon(), htmlspecialchars('random Vorname (laut Geschlecht)<br/>random Nachname<br/>'
                                    .'Lehrer Acronym (Initialien Nachname, Vorname)')))->enableHtml())
                            .new Container(new DangerLink('2. Adressen Anonymisieren', __NAMESPACE__.'/UpdateAddress', null, array(), 'Passiert sofort!')
                                .(new ToolTip(new InfoIcon(), htmlspecialchars('random Städte<br/>random PLZ<br/>leert Ortsteil<br/>'
                                    .'leert Bundesland<br/>leert Land<br/>leert Postfach<br/>random Straßennummer (1-99)')))->enableHtml())
                            .new Container(new DangerLink('3. Institutionen Anonymisieren', __NAMESPACE__.'/UpdateCompany', null, array(), 'Passiert sofort!')
                                .(new ToolTip(new InfoIcon(), 'Institutionen Umbenennen')))
                            .new Container(new WarningLink('4. Zusatzdaten Anonymisieren', __NAMESPACE__.'/MySQLScript', null, array(), 'Anzeige eines SQL Script\'s')
                                .new Warning(new ToolTip(new InfoIcon(), 'Bemerkungsfelder sowie andere Personenbezogene Daten entfernen'))))
                        , 4),
                )
            ))
        ));

        return $Stage;
    }

    /**
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendUpdatePerson($Confirm = false)
    {

        $Stage = new Stage('Daten Anonymisieren', 'Personen');

        if($Confirm){
            $Stage->setContent(Anonymous::useService()->UpdatePerson());
        } else {

            $Acronym = new DangerText('!Mandant konnte nicht ermittelt werden!');
            if(($tblAccount = Account::useService()->getAccountBySession())){
                if(($tblConsumer = $tblAccount->getServiceTblConsumer())){
                    $Acronym = $tblConsumer->getAcronym();
                }
            }

            $Stage->setContent(new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Wollen Sie für den Mandanten '.new Bold('"'.$Acronym.'"').' wirklich alle Personen Anonymisieren?',
                                new Standard('Ja', '/Platform/System/Anonymous/UpdatePerson', new Ok(), array('Confirm' => true))
                                .new Standard('Nein', '/Platform/System/Anonymous', new Disable()), Panel::PANEL_TYPE_DANGER
                            )
                        )
                    )
                )
            ));
        }

        return $Stage;
    }

    /**
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendUpdateAddress($Confirm = false)
    {

        $Stage = new Stage('Daten Anonymisieren', 'Adressen');

        if($Confirm){
            $Stage->setContent(Anonymous::useService()->UpdateAddress());
        } else {

            $Acronym = new DangerText('!Mandant konnte nicht ermittelt werden!');
            if(($tblAccount = Account::useService()->getAccountBySession())){
                if(($tblConsumer = $tblAccount->getServiceTblConsumer())){
                    $Acronym = $tblConsumer->getAcronym();
                }
            }

            $Stage->setContent(new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Wollen Sie für den Mandanten '.new Bold('"'.$Acronym.'"').' wirklich alle Adressen Anonymisieren?',
                                new Standard('Ja', '/Platform/System/Anonymous/UpdateAddress', new Ok(), array('Confirm' => true))
                                .new Standard('Nein', '/Platform/System/Anonymous', new Disable()), Panel::PANEL_TYPE_DANGER
                            )
                        )
                    )
                )
            ));
        }

        return $Stage;
    }

    /**
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendUpdateCompany($Confirm = false)
    {

        $Stage = new Stage('Daten Anonymisieren', 'Institution');
        if($Confirm){
            $Stage->setContent(Anonymous::useService()->UpdateCompany());
        } else {

            $Acronym = new DangerText('!Mandant konnte nicht ermittelt werden!');
            if(($tblAccount = Account::useService()->getAccountBySession())){
                if(($tblConsumer = $tblAccount->getServiceTblConsumer())){
                    $Acronym = $tblConsumer->getAcronym();
                }
            }

            $Stage->setContent(new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Wollen Sie für den Mandanten '.new Bold('"'.$Acronym.'"').' wirklich alle Institutionen Anonymisieren?',
                                new Standard('Ja', '/Platform/System/Anonymous/UpdateCompany', new Ok(), array('Confirm' => true))
                                .new Standard('Nein', '/Platform/System/Anonymous', new Disable()), Panel::PANEL_TYPE_DANGER
                            )
                        )
                    )
                )
            ));
        }

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendMySQLScript()
    {

        $tblConsumer = Consumer::useService()->getConsumerBySession();
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
                            new Container(new Bold('Nach SQL Script notwendig!'))
                            .new External('DatenbankUpdate für aktuellen Mandanten', '/Platform/System/Database/Setup/Execution',
                                new CogWheels(), array(), false)
                            .new External('Cache löschen', '/Platform/System/Cache', new Server(), array('Clear' => 1), false)
                        )
                        , 6)
                )),
                new LayoutRow(
                    new LayoutColumn(
                        new Code("TRUNCATE PeopleMeta_".$Acronym.".tblHandyCap;
TRUNCATE SettingConsumer_".$Acronym.".tblStudentCustody;
TRUNCATE SettingConsumer_".$Acronym.".tblUntisImportLectureship;
TRUNCATE SettingConsumer_".$Acronym.".tblUserAccount;
TRUNCATE SettingConsumer_".$Acronym.".tblWorkSpace;
DROP DATABASE BillingInvoice_".$Acronym.";
DROP DATABASE ReportingCheckList_".$Acronym.";
DROP DATABASE DocumentStorage_".$Acronym.";
DELETE FROM CorporationCompany_".$Acronym.".tblCompany WHERE EntityRemove IS NOT null;
UPDATE ContactMail_".$Acronym.".tblMail SET Address = 'Ref@schulsoftware.schule';
UPDATE ContactPhone_".$Acronym.".tblPhone SET Number = concat('00000/', LPAD(FLOOR(RAND()*1000000), 6, '0'));
UPDATE ContactWeb_".$Acronym.".tblWeb SET Address = 'www.schulsoftware.schule';
UPDATE PeopleGroup_".$Acronym.".tblGroup SET Description = '' where MetaTable = '';
UPDATE PeopleMeta_".$Acronym.".tblClub SET Remark = '' , Identifier = FLOOR(RAND()*100000);
UPDATE PeopleMeta_".$Acronym.".tblCommonBirthDates SET Birthplace = '';
UPDATE PeopleMeta_".$Acronym.".tblCustody SET Remark = '';
UPDATE PeopleMeta_".$Acronym.".tblSpecial SET PersonEditor = 'DatenÃ¼bernahme', Remark = '';
UPDATE PeopleMeta_".$Acronym.".tblStudentIntegration SET CoachingRemark = '';
UPDATE PeopleMeta_".$Acronym.".tblStudentTransfer SET Remark = '';
UPDATE PeopleMeta_".$Acronym.".tblStudentTransport SET Remark = '';
UPDATE PeopleMeta_".$Acronym.".tblSupport SET PersonSupport = '', PersonEditor = 'DatenÃ¼bernahme', Remark = '';
UPDATE PeopleRelationship_".$Acronym.".tblToCompany SET Remark = '';
UPDATE PeopleRelationship_".$Acronym.".tblToPerson SET Remark = '';
UPDATE ContactAddress_".$Acronym.".tblToCompany SET Remark = '';
UPDATE ContactAddress_".$Acronym.".tblToPerson SET Remark = '';
UPDATE EducationClassRegister_".$Acronym.".tblAbsence SET Remark = '';
UPDATE EducationGraduationEvaluation_".$Acronym.".tblTask SET IsLocked = 0;
UPDATE EducationGraduationGradebook_".$Acronym.".tblGrade SET Comment = '', PublicComment = '';
UPDATE EducationLessonDivision_".$Acronym.".tblDivisionTeacher SET Description = '';
UPDATE SettingConsumer_".$Acronym.".tblGenerateCertificate SET HeadmasterName = '', IsLocked = 0;
UPDATE SettingConsumer_".$Acronym.".tblLeaveInformation SET Value = '' WHERE Field like 'HeadmasterName' or Field = 'Remark';
UPDATE SettingConsumer_".$Acronym.".tblPrepareInformation SET Value = '' where Field like 'Remark';
UPDATE SettingConsumer_".$Acronym.".tblPrepareStudent SET IsApproved = 0, IsPrinted = 0;
UPDATE SettingConsumer_".$Acronym.".tblPreset SET PersonCreator = '', IsPublic = 1;
UPDATE SettingConsumer_".$Acronym.".tblSchool SET CompanyNumber = '';
UPDATE SettingConsumer_".$Acronym.".tblSetting SET Value = '' WHERE Identifier like '%Picture%';
UPDATE SettingConsumer_".$Acronym.".tblSetting SET Value = 0 WHERE Identifier like 'PictureDisplayLocationForDiplomaCertificate';"
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
    public function frontendYearly()
    {

        $tblConsumer = Consumer::useService()->getConsumerBySession();
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
                                .new External('Cache löschen', '/Platform/System/Cache', new Server(), array('Clear' => 1)))
                        )
                    , 6)
                )),
                new LayoutRow(
                    new LayoutColumn(
                        new Code("UPDATE PeopleMeta_".$Acronym.".tblCommonBirthDates SET Birthday = date_add(Birthday, interval 1 YEAR);
UPDATE PeopleMeta_".$Acronym.".tblSpecial SET Date = date_add(Date, interval 1 YEAR);
UPDATE PeopleMeta_".$Acronym.".tblStudent SET SchoolAttendanceStartDate = date_add(SchoolAttendanceStartDate, interval 1 YEAR);
UPDATE PeopleMeta_".$Acronym.".tblStudentBaptism SET BaptismDate = date_add(BaptismDate, interval 1 YEAR);
UPDATE PeopleMeta_".$Acronym.".tblStudentIntegration SET CoachingRequestDate = date_add(CoachingRequestDate, interval 1 YEAR),CoachingCounselDate = date_add(CoachingCounselDate, interval 1 YEAR),CoachingDecisionDate = date_add(CoachingDecisionDate, interval 1 YEAR);
UPDATE PeopleMeta_".$Acronym.".tblStudentTransfer SET TransferDate = date_add(TransferDate, interval 1 YEAR);
UPDATE PeopleMeta_".$Acronym.".tblSupport SET Date = date_add(Date, interval 1 YEAR);
UPDATE EducationClassRegister_".$Acronym.".tblAbsence SET FromDate = date_add(FromDate, interval 1 YEAR), ToDate = date_add(ToDate, interval 1 YEAR);
UPDATE EducationGraduationEvaluation_".$Acronym.".tblTask SET Date = date_add(Date, interval 1 YEAR), FromDate = date_add(FromDate, interval 1 YEAR), ToDate = date_add(ToDate, interval 1 YEAR);
UPDATE EducationGraduationEvaluation_".$Acronym.".tblTest SET Date = date_add(Date, interval 1 YEAR), CorrectionDate = date_add(CorrectionDate, interval 1 YEAR), ReturnDate = date_add(ReturnDate, interval 1 YEAR);
UPDATE EducationGraduationGradebook_".$Acronym.".tblGrade SET Date = date_add(Date, interval 1 YEAR);
UPDATE EducationLessonDivision_".$Acronym.".tblDivisionStudent SET LeaveDate = date_add(LeaveDate, interval 1 YEAR);
UPDATE EducationLessonTerm_".$Acronym.".tblHoliday SET FromDate = date_add(FromDate, interval 1 YEAR), ToDate = date_add(ToDate, interval 1 YEAR);
UPDATE EducationLessonTerm_".$Acronym.".tblPeriod SET FromDate = date_add(FromDate, interval 1 YEAR), ToDate = date_add(ToDate, interval 1 YEAR);
Update EducationLessonTerm_".$Acronym.".tblYear SET Name = CONCAT(SUBSTRING_INDEX(Name, '/', 1)+1,'/',SUBSTRING_INDEX(Name, '/', -1)+1), YEAR = CONCAT(SUBSTRING_INDEX(YEAR, '/', 1)+1,'/',SUBSTRING_INDEX(YEAR, '/', -1)+1);
UPDATE SettingConsumer_".$Acronym.".tblLeaveInformation SET Value = CONCAT(SUBSTRING_INDEX(Value, '.',2),'.',YEAR(CURDATE())) where Field like 'CertificateDate';
UPDATE SettingConsumer_".$Acronym.".tblPrepareCertificate SET Date = date_add(Date, interval 1 YEAR);
UPDATE SettingConsumer_".$Acronym.".tblPrepareInformation SET Value = CONCAT(SUBSTRING_INDEX(Value, '.',2),'.',YEAR(CURDATE())) where Field LIKE 'DateConference' OR Field LIKE 'DateConsulting'OR Field LIKE 'DateCertifcate';"
                        )
                    )
                )
            ))
        ));

        return $Stage;
    }
}
