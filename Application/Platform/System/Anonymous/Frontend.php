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

        if ($Confirm){
            $Stage->setContent(Anonymous::useService()->UpdatePerson());
        } else {

            $Acronym = new DangerText('!Mandant konnte nicht ermittelt werden!');
            if (($tblAccount = Account::useService()->getAccountBySession())){
                if (($tblConsumer = $tblAccount->getServiceTblConsumer())){
                    $Acronym = $tblConsumer->getAcronym();
                }
            }

            $Stage->setContent(new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Wollen Sie für den Mandanten '.new Bold('"'.$Acronym.'"').' wirklich alle Personen Anonymisieren?',
                                new Standard('Ja', '/Platform/System/Anonymous/UpdatePerson', new Ok(),
                                    array('Confirm' => true))
                                .new Standard('Nein', '/Platform/System/Anonymous', new Disable()),
                                Panel::PANEL_TYPE_DANGER
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

        if ($Confirm){
            $Stage->setContent(Anonymous::useService()->UpdateAddress());
        } else {

            $Acronym = new DangerText('!Mandant konnte nicht ermittelt werden!');
            if (($tblAccount = Account::useService()->getAccountBySession())){
                if (($tblConsumer = $tblAccount->getServiceTblConsumer())){
                    $Acronym = $tblConsumer->getAcronym();
                }
            }

            $Stage->setContent(new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Wollen Sie für den Mandanten '.new Bold('"'.$Acronym.'"').' wirklich alle Adressen Anonymisieren?',
                                new Standard('Ja', '/Platform/System/Anonymous/UpdateAddress', new Ok(),
                                    array('Confirm' => true))
                                .new Standard('Nein', '/Platform/System/Anonymous', new Disable()),
                                Panel::PANEL_TYPE_DANGER
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
        if ($Confirm){
            $Stage->setContent(Anonymous::useService()->UpdateCompany());
        } else {

            $Acronym = new DangerText('!Mandant konnte nicht ermittelt werden!');
            if (($tblAccount = Account::useService()->getAccountBySession())){
                if (($tblConsumer = $tblAccount->getServiceTblConsumer())){
                    $Acronym = $tblConsumer->getAcronym();
                }
            }

            $Stage->setContent(new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Wollen Sie für den Mandanten '.new Bold('"'.$Acronym.'"').' wirklich alle Institutionen Anonymisieren?',
                                new Standard('Ja', '/Platform/System/Anonymous/UpdateCompany', new Ok(),
                                    array('Confirm' => true))
                                .new Standard('Nein', '/Platform/System/Anonymous', new Disable()),
                                Panel::PANEL_TYPE_DANGER
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
                            .new External('DatenbankUpdate für aktuellen Mandanten',
                                '/Platform/System/Database/Setup/Execution',
                                new CogWheels(), array(), false)
                            .new External('Cache löschen', '/Platform/System/Cache', new Server(), array('Clear' => 1),
                                false)
                        )
                        , 6)
                )),
                new LayoutRow(
                    new LayoutColumn(
                        new Code("TRUNCATE ".$Acronym."_PeopleMeta.tblHandyCap;
TRUNCATE ".$Acronym."_SettingConsumer.tblStudentCustody;
TRUNCATE ".$Acronym."_SettingConsumer.tblUntisImportLectureship;
TRUNCATE ".$Acronym."_SettingConsumer.tblUserAccount;
TRUNCATE ".$Acronym."_SettingConsumer.tblWorkSpace;
DROP DATABASE ".$Acronym."_BillingInvoice;
DROP DATABASE ".$Acronym."_ReportingCheckList;
DROP DATABASE ".$Acronym."_DocumentStorage;
DELETE FROM ".$Acronym."_CorporationCompany.tblCompany WHERE EntityRemove IS NOT null;
UPDATE ".$Acronym."_ContactMail.tblMail SET Address = 'Ref@schulsoftware.schule';
UPDATE ".$Acronym."_ContactPhone.tblPhone SET Number = concat('00000/', LPAD(FLOOR(RAND()*1000000), 6, '0'));
UPDATE ".$Acronym."_ContactWeb.tblWeb SET Address = 'www.schulsoftware.schule';
UPDATE ".$Acronym."_PeopleGroup.tblGroup SET Description = '' where MetaTable = '';
UPDATE ".$Acronym."_PeopleMeta.tblClub SET Remark = '' , Identifier = FLOOR(RAND()*100000);
UPDATE ".$Acronym."_PeopleMeta.tblCommonBirthDates SET Birthplace = '';
UPDATE ".$Acronym."_PeopleMeta.tblCustody SET Remark = '';
UPDATE ".$Acronym."_PeopleMeta.tblSpecial SET PersonEditor = 'DatenÃ¼bernahme', Remark = '';
UPDATE ".$Acronym."_PeopleMeta.tblStudentTransfer SET Remark = '';
UPDATE ".$Acronym."_PeopleMeta.tblStudentTransport SET Remark = '';
UPDATE ".$Acronym."_PeopleMeta.tblSupport SET PersonSupport = '', PersonEditor = 'DatenÃ¼bernahme', Remark = '';
UPDATE ".$Acronym."_PeopleRelationship.tblToCompany SET Remark = '';
UPDATE ".$Acronym."_PeopleRelationship.tblToPerson SET Remark = '';
UPDATE ".$Acronym."_ContactAddress.tblToCompany SET Remark = '';
UPDATE ".$Acronym."_ContactAddress.tblToPerson SET Remark = '';
UPDATE ".$Acronym."_EducationClassRegister.tblAbsence SET Remark = '';
UPDATE ".$Acronym."_EducationGraduationEvaluation.tblTask SET IsLocked = 0;
UPDATE ".$Acronym."_EducationGraduationGradebook.tblGrade SET Comment = '', PublicComment = '';
UPDATE ".$Acronym."_EducationLessonDivision.tblDivisionTeacher SET Description = '';
UPDATE ".$Acronym."_SettingConsumer.tblGenerateCertificate SET HeadmasterName = '', IsLocked = 0;
UPDATE ".$Acronym."_SettingConsumer.tblLeaveInformation SET Value = '' WHERE Field like 'HeadmasterName' or Field = 'Remark';
UPDATE ".$Acronym."_SettingConsumer.tblPrepareInformation SET Value = '' where Field like 'Remark';
UPDATE ".$Acronym."_SettingConsumer.tblPrepareStudent SET IsApproved = 0, IsPrinted = 0;
UPDATE ".$Acronym."_SettingConsumer.tblPreset SET PersonCreator = '', IsPublic = 1;
UPDATE ".$Acronym."_SettingConsumer.tblSchool SET CompanyNumber = '';
UPDATE ".$Acronym."_SettingConsumer.tblSetting SET Value = '' WHERE Identifier like '%Picture%';
UPDATE ".$Acronym."_SettingConsumer.tblSetting SET Value = 0 WHERE Identifier like 'PictureDisplayLocationForDiplomaCertificate';"
                        )
                    )
                )
            ))
        ));

        return $Stage;
    }
}
