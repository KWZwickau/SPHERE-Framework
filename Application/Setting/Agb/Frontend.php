<?php

namespace SPHERE\Application\Setting\Agb;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSetting;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\MoreItems;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Paragraph;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

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

        $Stage = new Stage(new MoreItems() . ' Allgemeine Geschäftsbedingungen', '', '');

        // Create Form
        $Form = new Layout(
            new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Center(new Header('Ich möchte das elektronische Notenbuch nicht mehr nutzen oder bin mit den o.g. Regelungen nicht mehr einverstanden:')),
                        ))
                    ),
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Center(new Danger('Allgemeine Geschäftsbedingungen ablehnen / widerrufen',
                                new Route(__NAMESPACE__ . '/Decline'), new Disable(), array()))
                        ))
                    )
                )
            ));

        $Stage->setContent(new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        ''
                        , 2),
                    new LayoutColumn(
                        new Listing(array(
                            new Header(new Bold('Elektronische Notenübersicht in der Schulsoftware')),
                            Agb::useFrontend()->getAgbContent()
                        ))
                        . $Form
                        , 8),
                    new LayoutColumn(
                        ''
                        , 2),
                )),
            )))
        );

        return $Stage;
    }

    /**
     * @return string
     */
    public function getAgbContent()
    {
        return new Title(new TileBig() . ' Beschreibung der Anwendung')
            . new Paragraph('Die elektronische Notenübersicht bietet allen Eltern und Schülern die 
            Möglichkeit, sich über den aktuellen Notenstand zu informieren. Dieses Serviceangebot der Schulsoftware soll 
            die Kommunikation zwischen Lehrern, Eltern und Schülern in Bezug auf die schulische Leistungsentwicklung 
            verbessern. Mit der elektronischen Notenübersicht kann die Schule ihrer Informationspflicht noch besser als 
            bisher gerecht werden, insbesondere auch bei mündlichen und praktischen Leistungen. Selbstverständlich ist 
            die Nutzung dieser Software freiwillig. Gern können Sie auch weiterhin persönlich in der Schule vorsprechen, 
            um sich über den Leistungsstand Ihres Kindes zu informieren.')
            . new Title(new TileBig() . ' Datenschutzerklärung')
            . new Headline('Allgemeines')
            . new Paragraph('Der Schutz der persönlichen Daten von Eltern und Schülern liegt uns sehr am Herzen. An dieser 
            Stelle möchten wir Sie daher über Ihre Persönlichkeitsrechte aufklären. Selbstverständlich sind in der 
            Schulsoftware alle gesetzlich vorgeschriebenen Maßnahmen zum Schutz Ihrer personenbezogenen Daten und die 
            Ihres Kindes , entsprechend der geltenden Bestimmungen, insbesondere des Kirchengesetzes über den Datenschutz 
            der Evangelischen Kirche in Deutschland (DSG-EKD),  des Telemediengesetzes (TMG) und anderer 
            datenschutzrechtlicher Bestimmungen implementiert. Alle Schulen, welche diese Software anwenden, haben sich 
            als evangelische Schulen dem kirchlichen Datenschutzrecht unterworfen und stehen unter der Aufsicht des 
            Datenschutzbeauftragten der Evangelisch-Lutherischen Landeskirche Sachsen, der uns regelmäßig berät und die 
            Einhaltung aller einschlägigen Bestimmungen des Datenschutzes überwacht.  ')
            . new Headline('Gegenstand des Datenschutzes ')
            . new Paragraph(new Container(new DangerText('ACHTUNG: HIER wird es Veränderungen nach Verabschiedung des neuen 
            Datenschutzgesetzes (Oktober oder November 2017) geben.')) . 'Gegenstand des Datenschutzes sind personenbezogene Daten. Diese sind nach § 2 Abs. 1  
            DSG-EKD „Einzelangaben über persönlich oder sachliche Verhältnisse einer bestimmten oder bestimmbaren 
            natürlichen Person (betroffene Person)“. Hierunter fallen z. B. Angaben wie Name, Post-Adresse, 
            E-Mail-Adresse, Telefonnummer, die Benotung von Schülerinnen und Schülern, ggf. aber auch Nutzungsdaten wie 
            IP-Adressen. ')
            . new Headline('Umfang der Datenerhebung und -speicherung')
            . new Paragraph('Wir erheben und speichern persönliche Daten grundsätzlich nur, soweit es für die Erbringung 
            unserer Dienstleistungen als Schule notwendig ist. Die Schulsoftware stellt die elektronische Notenübersicht 
            unter der Internetadresse https://www.schulsoftware.schule bereit. Eine Nutzung dieser Dienste ist nur mit 
            gültigen Zugangsdaten und nach vorheriger Einwilligungserklärung möglich. Alle Daten werden in einer für 
            Dritte unzugänglichen Weise gespeichert und ausschließlich in verschlüsselter Form übertragen. ')
            . new Headline('Zweckgebundene Datenverwendung')
            . new Paragraph('Wir beachten den Grundsatz der zweckgebundenen Daten-Verwendung und erheben, verarbeiten und 
            speichern personenbezogene Daten nur für die Zwecke, für die Sie uns Ihre Angaben mitgeteilt haben oder eine 
            gesetzliche Verpflichtung besteht. Eine Weitergabe Ihrer persönlichen Daten an Dritte erfolgt grundsätzlich 
            nicht. Unsere Mitarbeiter und die von uns beauftragten Dienstleistungsunternehmen sind von uns zur 
            Verschwiegenheit und zur Einhaltung der datenschutzrechtlichen Bestimmungen verpflichtet. ')
            . new Headline('Auskunft- und Widerrufsrecht')
            . new Paragraph('Sie erhalten jederzeit ohne Angabe von Gründen kostenfrei Auskunft über Ihre bei uns 
            gespeicherten Daten. Sie können jederzeit Ihre bei uns erhobenen Daten berichtigen lassen. Auch können Sie 
            jederzeit die uns erteilte Einwilligung zur Datenerhebung und Verwendung ohne Angaben von Gründen widerrufen. 
            Wir stehen Ihnen jederzeit gern für weitergehende Fragen zu unserem Hinweisen zum Datenschutz und zur 
            Verarbeitung Ihrer persönlichen Daten zur Verfügung. ')
            . new Title(new TileBig() . ' Nutzungsbedingungen')
            . new Paragraph('Die Nutzung des elektronischen Notenbuchs ist freiwillig. Wenn Sie vorstehenden Regelungen 
            einverstanden sind und die elektronische Notenübersicht nutzen möchten, so klicken sie unten auf [JA].')
            . new Paragraph('Andernfalls klicken Sie auf [NEIN], um keinen Zugang zum elektronischen Notenbuch zu erhalten.')
            . new Paragraph('Wenn Sie auf die Nutzung der elektronischen Notenübersicht verzichten, gehen wir davon aus, 
            dass Sie sich zukünftig als Eltern auch weiterhin wie bisher durch Ihr Kind in geeigneter Weise (z.B. 
            regelmäßige Gespräche, Vorlage benoteter Arbeiten, usw.) über dessen schulische Leistungen informieren 
            lassen und ebenso die angebotenen Elternsprechtage unserer Lehrerschaft dafür nutzen.');
    }

    /**
     * @return Stage
     */
    public function frontendAcceptAgb()
    {
        $Stage = new Stage(new MoreItems() . ' Allgemeine Geschäftsbedingungen', '', '');

        //Update AGB Setting
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            Account::useService()->setSettingByAccount($tblAccount, 'AGB', TblSetting::VAR_ACCEPT_AGB);
        }

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
    public function frontendDeclineAgb()
    {
        $Stage = new Stage(new MoreItems() . ' Allgemeine Geschäftsbedingungen', '', '');

        //Update AGB Setting
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            Account::useService()->setSettingByAccount($tblAccount, 'AGB', TblSetting::VAR_EMPTY_AGB);
        }

        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new DangerMessage('Die Allgemeinen Geschäftsbedingungen wurden abgelehnt.')
                    ),
                    new LayoutColumn(Account::useService()->destroySession(
                            new Redirect('/Platform/Gatekeeper/Authentication', Redirect::TIMEOUT_WAIT)
                        ) . $this->getCleanLocalStorage())
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
