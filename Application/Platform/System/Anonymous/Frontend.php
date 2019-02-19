<?php
namespace SPHERE\Application\Platform\System\Anonymous;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\CogWheels;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Code;
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
        $Stage->addButton(new Standard('Personen Anonymisieren', __NAMESPACE__.'/UpdatePerson'));
        $Stage->addButton(new Standard('Adressen Anonymisieren', __NAMESPACE__.'/UpdateAddress'));
        $Stage->addButton(new Standard('MySQL Script für DB', __NAMESPACE__.'/MySQLScript'));

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendUpdatePerson()
    {

        $Stage = new Stage('Daten Anonymisieren');
        $Stage->setContent(Anonymous::useService()->UpdatePerson());

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendUpdateAddress()
    {

        $Stage = new Stage('Daten Anonymisieren');
        $Stage->setContent(Anonymous::useService()->UpdateAddress());

        return $Stage;
    }

    public function frontendMySQLScript()
    {

        $tblConsumer = Consumer::useService()->getConsumerBySession();
        $Acronym = $tblConsumer->getAcronym();
        $Stage = new Stage('SQL Anweisung');

        $Stage->setContent(
            new Info('Ausführen der SQL funktionen in der Datenbank ('.new Bold('SQL für aktuellen Mandanten erzeugt!').')')
            .new Code("TRUNCATE PeopleMeta_".$Acronym.".tblHandyCap;
TRUNCATE SettingConsumer_".$Acronym.".tblStudentCustody;
TRUNCATE SettingConsumer_".$Acronym.".tblUntisImportLectureship;
TRUNCATE SettingConsumer_".$Acronym.".tblUserAccount;
TRUNCATE SettingConsumer_".$Acronym.".tblWorkSpace;
DROP DATABASE BillingInvoice_".$Acronym.";
DROP DATABASE ContactMail_".$Acronym.";
DROP DATABASE ContactPhone_".$Acronym.";
DROP DATABASE ContactWeb_".$Acronym.";
DROP DATABASE ReportingCheckList_".$Acronym.";
UPDATE PeopleMeta_".$Acronym.".tblClub SET Remark = '' , Identifier = FLOOR(RAND()*100000);
UPDATE PeopleMeta_".$Acronym.".tblCommonBirthDates SET Birthday = date_add(Birthday, interval 1 year) , Birthplace = '';
UPDATE PeopleMeta_".$Acronym.".tblCustody SET Remark = '';
UPDATE PeopleMeta_".$Acronym.".tblSpecial SET Date = date_add(Date, interval 1 year) , PersonEditor = 'DatenÃ¼bernahme', Remark = '';
UPDATE PeopleMeta_".$Acronym.".tblStudent SET SchoolAttendanceStartDate = date_add(SchoolAttendanceStartDate, interval 1 year);
UPDATE PeopleMeta_".$Acronym.".tblStudentBaptism SET BaptismDate = date_add(BaptismDate, interval 1 year);
UPDATE PeopleMeta_".$Acronym.".tblStudentIntegration SET CoachingRequestDate = date_add(CoachingRequestDate, interval 1 year),CoachingCounselDate = date_add(CoachingCounselDate, interval 1 year),CoachingDecisionDate = date_add(CoachingDecisionDate, interval 1 year),CoachingRemark = '';
UPDATE PeopleMeta_".$Acronym.".tblStudentTransfer SET TransferDate = date_add(TransferDate, interval 1 year), Remark = '';
UPDATE PeopleMeta_".$Acronym.".tblStudentTransport SET Remark = '';
UPDATE PeopleMeta_".$Acronym.".tblSupport SET Date = date_add(Date, interval 1 year) ,PersonSupport = '', PersonEditor = 'DatenÃ¼bernahme', Remark = '';
UPDATE PeopleRelationship_".$Acronym.".tblToCompany SET Remark = '';
UPDATE PeopleRelationship_".$Acronym.".tblToPerson SET Remark = '';
UPDATE ContactAddress_".$Acronym.".tblToCompany SET Remark = '';
UPDATE ContactAddress_".$Acronym.".tblToPerson SET Remark = '';
UPDATE EducationClassRegister_".$Acronym.".tblAbsence SET FromDate = date_add(FromDate, interval 1 year), ToDate = date_add(ToDate, interval 1 year), Remark = '';
UPDATE EducationGraduationEvaluation_".$Acronym.".tblTask SET Date = date_add(Date, interval 1 year), FromDate = date_add(FromDate, interval 1 year), ToDate = date_add(ToDate, interval 1 year), IsLocked = 0;
UPDATE EducationGraduationEvaluation_".$Acronym.".tblTest SET Date = date_add(Date, interval 1 year), CorrectionDate = date_add(CorrectionDate, interval 1 year), ReturnDate = date_add(ReturnDate, interval 1 year);
UPDATE EducationGraduationGradebook_".$Acronym.".tblGrade SET Date = date_add(Date, interval 1 year), Comment = '', PublicComment = '';
UPDATE EducationLessonDivision_".$Acronym.".tblDivisionStudent SET LeaveDate = date_add(LeaveDate, interval 1 year);
UPDATE EducationLessonDivision_".$Acronym.".tblDivisionTeacher SET Description = '';
UPDATE EducationLessonTerm_".$Acronym.".tblHoliday SET FromDate = date_add(FromDate, interval 1 year), ToDate = date_add(ToDate, interval 1 year);
UPDATE EducationLessonTerm_".$Acronym.".tblPeriod SET FromDate = date_add(FromDate, interval 1 year), ToDate = date_add(ToDate, interval 1 year);
Update EducationLessonTerm_".$Acronym.".tblYear SET Name = CONCAT(SUBSTRING_INDEX(Name, '/', 1)+1,'/',SUBSTRING_INDEX(Name, '/', -1)+1), Year = CONCAT(SUBSTRING_INDEX(Year, '/', 1)+1,'/',SUBSTRING_INDEX(Year, '/', -1)+1);
UPDATE SettingConsumer_".$Acronym.".tblGenerateCertificate SET HeadmasterName = '', IsLocked = 0;
UPDATE SettingConsumer_".$Acronym.".tblLeaveInformation SET Value = ''WHERE Field like 'HeadmasterName' or Field = 'Remark';
UPDATE SettingConsumer_".$Acronym.".tblLeaveInformation SET Value = CONCAT(SUBSTRING_INDEX(Value, '.',2),'.',YEAR(CURDATE())) where Field like 'CertificateDate';
UPDATE SettingConsumer_".$Acronym.".tblPrepareCertificate SET Date = date_add(Date, interval 1 year);
UPDATE SettingConsumer_".$Acronym.".tblPrepareInformation SET Value = CONCAT(SUBSTRING_INDEX(Value, '.',2),'.',YEAR(CURDATE())) where Field LIKE 'DateConference' OR Field LIKE 'DateConsulting'OR Field LIKE 'DateCertifcate';
UPDATE SettingConsumer_".$Acronym.".tblPrepareInformation SET Value = '' where Field like 'Remark';
UPDATE SettingConsumer_".$Acronym.".tblPrepareStudent SET IsApproved = 0, IsPrinted = 0;
UPDATE SettingConsumer_".$Acronym.".tblPreset SET PersonCreator = '', IsPublic = 1;
UPDATE SettingConsumer_".$Acronym.".tblSchool SET CompanyNumber = '';
UPDATE SettingConsumer_".$Acronym.".tblSetting SET Value = '' WHERE Identifier like '%Picture%';
UPDATE SettingConsumer_".$Acronym.".tblSetting SET Value = 0 WHERE Identifier like 'PictureDisplayLocationForDiplomaCertificate';"
            )
            .new Info(new Standard('DatenbankUpdate für aktuellen Mandanten', '/Platform/System/Database/Setup/Execution',
                new CogWheels()). new Bold(' Nach SQL Script notwendig!'))
        );

        return $Stage;
    }
}
