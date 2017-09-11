<?php

namespace SPHERE\Application\Setting\Agb;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSetting;
use SPHERE\Application\Setting\User\Account\Account as AccountUser;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\Icon\Repository\TileSmall;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Paragraph;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Agb
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendAgbView()
    {

//        $tblSetting = false;
//        $tblAccount = Account::useService()->getAccountBySession();
//        $tblUserAccount = AccountUser::useService()->getUserAccountByAccount($tblAccount);
//        // Account muss einem Eltern Account entsprechen
//        if ($tblAccount && $tblUserAccount && $tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_CUSTODY || true) { // ToDO remove true
//            // suchen der vorhandenen AGB Settings
//            $tblSetting = Account::useService()->getSettingByAccount($tblAccount, 'ABG');
//            if (!$tblSetting) {
//                // erstellen der AGB Settings wenn sie noch nicht vorhanden sind
//                $tblSetting = Account::useService()->setSettingByAccount($tblAccount, 'ABG', TblSetting::VAR_EMPTY_AGB);
//            }
//        }
//
//        // ToDO reagieren auf AGB Einstellung
//        if ($tblSetting) {
//            switch ($tblSetting->getValue()) {
//                case TblSetting::VAR_EMPTY_AGB:
//                    Debugger::screenDump('Emtyp');
//
//                    break;
//                case TblSetting::VAR_ACCEPT_AGB:
//                    Debugger::screenDump('Accept');
//
//                    break;
//                case TblSetting::VAR_UPDATE_AGB:
//                    Debugger::screenDump('Update');
//
//                    break;
//            }
//        }

        $Stage = new Stage('Elektronische Notenübersicht in der Schulsoftware');

        $PanelContent =
            new Headline(new TileBig().' Beschreibung der Anwendung')
            .new Paragraph(new TileSmall().' Die elektronische Notenübersicht bietet allen Eltern und Schülern die 
            Möglichkeit, sich über den aktuellen Notenstand zu informieren. Dieses Serviceangebot der Schulsoftware soll 
            die Kommunikation zwischen Lehrern, Eltern und Schülern in Bezug auf die schulische Leistungsentwicklung 
            verbessern. Mit der elektronischen Notenübersicht kann die Schule ihrer Informationspflicht noch besser als 
            bisher gerecht werden, insbesondere auch bei mündlichen und praktischen Leistungen. Selbstverständlich ist 
            die Nutzung dieser Software freiwillig. Gern können Sie auch weiterhin persönlich in der Schule vorsprechen, 
            um sich über den Leistungsstand Ihres Kindes zu informieren.')
            .new Ruler()
            .new Headline(new TileBig().' Datenschutzerklärung')
            .new Header(new TileSmall().' Allgemeines')
            .new Paragraph('Der Schutz der persönlichen Daten von Eltern und Schülern liegt uns sehr am Herzen. An dieser 
            Stelle möchten wir Sie daher über Ihre Persönlichkeitsrechte aufklären. Selbstverständlich sind in der 
            Schulsoftware alle gesetzlich vorgeschriebenen Maßnahmen zum Schutz Ihrer personenbezogenen Daten und die 
            Ihres Kindes , entsprechend der geltenden Bestimmungen, insbesondere des Kirchengesetzes über den Datenschutz 
            der Evangelischen Kirche in Deutschland (DSG-EKD),  des Telemediengesetzes (TMG) und anderer 
            datenschutzrechtlicher Bestimmungen implementiert. Alle Schulen, welche diese Software anwenden, haben sich 
            als evangelische Schulen dem kirchlichen Datenschutzrecht unterworfen und stehen unter der Aufsicht des 
            Datenschutzbeauftragten der Evangelisch-Lutherischen Landeskirche Sachsen, der uns regelmäßig berät und die 
            Einhaltung aller einschlägigen Bestimmungen des Datenschutzes überwacht.  ')
            .new Ruler()
            .new Header(new TileSmall().' Gegenstand des Datenschutzes ')
            .new Paragraph(new Container(new DangerText('ACHTUNG: HIER wird es Veränderungen nach Verabschiedung des neuen 
            Datenschutzgesetzes (Oktober oder November 2017) geben.')).'Gegenstand des Datenschutzes sind personenbezogene Daten. Diese sind nach § 2 Abs. 1  
            DSG-EKD „Einzelangaben über persönlich oder sachliche Verhältnisse einer bestimmten oder bestimmbaren 
            natürlichen Person (betroffene Person)“. Hierunter fallen z. B. Angaben wie Name, Post-Adresse, 
            E-Mail-Adresse, Telefonnummer, die Benotung von Schülerinnen und Schülern, ggf. aber auch Nutzungsdaten wie 
            IP-Adressen. ')
            .new Ruler()
            .new Header(new TileSmall().' Umfang der Datenerhebung und -speicherung')
            .new Paragraph('Wir erheben und speichern persönliche Daten grundsätzlich nur, soweit es für die Erbringung 
            unserer Dienstleistungen als Schule notwendig ist. Die Schulsoftware stellt die elektronische Notenübersicht 
            unter der Internetadresse https://www.schulsoftware.schule bereit. Eine Nutzung dieser Dienste ist nur mit 
            gültigen Zugangsdaten und nach vorheriger Einwilligungserklärung möglich. Alle Daten werden in einer für 
            Dritte unzugänglichen Weise gespeichert und ausschließlich in verschlüsselter Form übertragen. ')
            .new Ruler()
            .new Header(new TileSmall().' Zweckgebundene Datenverwendung')
            .new Paragraph('Wir beachten den Grundsatz der zweckgebundenen Daten-Verwendung und erheben, verarbeiten und 
            speichern personenbezogene Daten nur für die Zwecke, für die Sie uns Ihre Angaben mitgeteilt haben oder eine 
            gesetzliche Verpflichtung besteht. Eine Weitergabe Ihrer persönlichen Daten an Dritte erfolgt grundsätzlich 
            nicht. Unsere Mitarbeiter und die von uns beauftragten Dienstleistungsunternehmen sind von uns zur 
            Verschwiegenheit und zur Einhaltung der datenschutzrechtlichen Bestimmungen verpflichtet. ')
            .new Ruler()
            .new Header(new TileSmall().' Auskunft- und Widerrufsrecht')
            .new Paragraph('Sie erhalten jederzeit ohne Angabe von Gründen kostenfrei Auskunft über Ihre bei uns 
            gespeicherten Daten. Sie können jederzeit Ihre bei uns erhobenen Daten berichtigen lassen. Auch können Sie 
            jederzeit die uns erteilte Einwilligung zur Datenerhebung und Verwendung ohne Angaben von Gründen widerrufen. 
            Wir stehen Ihnen jederzeit gern für weitergehende Fragen zu unserem Hinweisen zum Datenschutz und zur 
            Verarbeitung Ihrer persönlichen Daten zur Verfügung. ')
            .new Ruler()
            .new Headline(new TileBig().' Nutzungsbedingungen')
            .new Paragraph('Die Nutzung des elektronischen Notenbuchs ist freiwillig. Wenn Sie vorstehenden Regelungen 
            einverstanden sind und die elektronische Notenübersicht nutzen möchten, so klicken sie unten auf [JA].')
            .new Paragraph('Andernfalls klicken Sie auf [NEIN], um keinen Zugang zum elektronischen Notenbuch zu erhalten.')
            .new Paragraph('Wenn Sie auf die Nutzung der elektronischen Notenübersicht verzichten, gehen wir davon aus, 
            dass Sie sich zukünftig als Eltern auch weiterhin wie bisher durch Ihr Kind in geeigneter Weise (z.B. 
            regelmäßige Gespräche, Vorlage benoteter Arbeiten, usw.) über dessen schulische Leistungen informieren 
            lassen und ebenso die angebotenen Elternsprechtage unserer Lehrerschaft dafür nutzen.');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Panel('Allgemeine Geschäftsbedingungen ', $PanelContent
                            , Panel::PANEL_TYPE_INFO,
                            new Paragraph(new Bold(new Center('Ich möchte das elektronische Notenbuch nutzen und bin mit den o.g. 
                            Regelungen einverstanden:<br/>'
                                .new Danger('Nicht Akzeptieren', __NAMESPACE__.'\Decline')
                                .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                                .new Success('Akzeptieren', __NAMESPACE__.'\Accept')
                            )))))
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendAcceptAbg()
    {
        $Stage = new Stage('AGB', 'Akzeptieren');

        //Update AGB Setting
        $tblAccount = Account::useService()->getAccountBySession();
        Account::useService()->setSettingByAccount($tblAccount, 'ABG', TblSetting::VAR_ACCEPT_AGB);

        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new SuccessMessage('Die Allgemeinen Geschäftsbedingungen wurden hiermit akzeptiert.')
                    ),
                    new LayoutColumn(new Redirect('/', Redirect::TIMEOUT_SUCCESS))
                ))
            )
        ));
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendDeclineAbg()
    {
        $Stage = new Stage('AGB', 'Akzeptieren');

        //Update AGB Setting
        $tblAccount = Account::useService()->getAccountBySession();
        Account::useService()->setSettingByAccount($tblAccount, 'ABG', TblSetting::VAR_EMPTY_AGB);

        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new DangerMessage('Die Allgemeinen Geschäftsbedingungen wurden abgelehnt.')
                    ),
                    new LayoutColumn(Account::useService()->destroySession(
                            new Redirect('/Platform/Gatekeeper/Authentication', Redirect::TIMEOUT_ERROR)
                        ).$this->getCleanLocalStorage())
                ))
            )
        ));
        return $Stage;
    }

    /**
     * @return string
     */
    private function getCleanLocalStorage()
    {

        return '<script language=javascript>
            //noinspection JSUnresolvedFunction
            executeScript(function()
            {
                Client.Use("ModCleanStorage", function()
                {
                    jQuery().ModCleanStorage();
                });
            });
        </script>';
    }
}
