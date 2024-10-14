<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 23.05.2018
 * Time: 08:37
 */

namespace SPHERE\Application\Document;


use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IClusterInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Stage;

class DataProtectionOrdinance implements IClusterInterface, IApplicationInterface, IModuleInterface
{

    public static function registerCluster()
    {

        self::registerApplication();
    }

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\DataProtectionOrdinance', __CLASS__ . '::frontendDashboard'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {

    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

    }

    public function frontendDashboard()
    {

        $Stage = new Stage('Datenschutzerklärung Schulsoftware');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Container('Wir freuen uns über Ihr Interesse an unserem Internetauftritt und unseren Angeboten.
                            Der Schutz Ihrer personenbezogenen Daten (im Folgenden kurz „Daten“) ist uns ein sehr wichtiges
                            Anliegen. Nachfolgend möchten wir Sie daher ausführlich darüber informieren, welche Daten
                            bei Ihrem Besuch unseres Internetauftritts und bei der Nutzung unserer dortigen Angebote 
                            erhoben und wie diese von uns im Folgenden verarbeitet oder genutzt werden.'),
                            new Container('&nbsp;'),
                            new Container('Zum Schutz Ihrer Rechte haben wir technische und organisatorische Maßnahmen
                            getroffen, dass die Vorschriften über den Datenschutz innerhalb der Evangelisch-Lutherischen
                            Landeskirche Sachsens als auch durch externe Dienstleister beachtet werden, wenn diese an
                            diesem Angebot mitwirken.'),
                            new Container('&nbsp;'),
                            new Container('Wir weisen darauf hin, dass die Datenübertragung im Internet (z.B. bei der
                            Kommunikation per E-Mail) Sicherheitslücken aufweisen kann. Ein lückenloser Schutz der Daten
                            vor dem Zugriff durch Dritte ist nicht möglich.'),
                            new Container('&nbsp;'),
                            new Container('Wenn Sie glauben, dass die hier aufgeführten Datenschutzrichtlinien nicht
                            eingehalten werden oder Missbrauch mit persönlichen Daten betrieben wird, dann wenden Sie
                            sich bitte per E-Mail an unseren Datenschutzbeauftragten.'),
                            new Container('&nbsp;'),
                            new Panel(
                                'Herr Erik Kahnt (Vertreter im Amt) ',
                                array(
                                    'Der Datenschutzbeauftragte für Kirche und Diakonie',
                                    'Leiter der Aufsichtsbehörde',
                                    'Dienstsitz: 09117 Chemnitz, Reichenbrander Str. 4',
                                    'Telefon: 0351 4692-460',
//                                    'Fax: 0351 4692-469',
                                    'E-Mail: datenschutzbeauftragter@evlks.de',
                                )
                            )
                        ))
                    ), new Title(
                        'ALLGEMEINES'
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Container('Die Nutzung unserer Webseite ist in der Regel ohne Angabe personenbezogener Daten möglich. 
                            Soweit auf unseren Seiten personenbezogene Daten (beispielsweise Name, Anschrift oder E-Mail-Adressen)
                            erhoben werden, erfolgt dies stets auf freiwilliger Basis. Diese Daten werden ohne Ihre ausdrückliche 
                            Zustimmung nicht an Dritte weitergegeben.'),
                            new Container('&nbsp;'),
                            new Container('Der Provider der Seiten erhebt und speichert automatisch Informationen in so genannten 
                            Server-Log Files, die Ihr Browser automatisch an uns übermittelt. Dies sind:'),
                            new Container('
                                <ul style="list-style-type:disc">
                                    <li>Datum und Uhrzeit des Abrufs einer unserer Internetseiten,</li>
                                    <li>Ihren Browsertyp,</li>
                                    <li>die Browser-Einstellungen,</li>
                                    <li>das verwendete Betriebssystem,</li>
                                    <li>die von Ihnen zuletzt besuchte Seite,</li>
                                    <li>die übertragene Datenmenge und der Zugriffsstatus (Datei übertragen, Datei nicht gefunden etc.) sowie</li>
                                    <li>Ihre IP-Adresse.</li>
                                </ul>
                            '),
                            new Container('&nbsp;'),
                            new Container('Diese Daten erheben und verwenden wir bei einem informatorischen Besuch 
                            ausschließlich in nicht-personenbezogener Form. Dies erfolgt, um die Nutzung der von Ihnen 
                            abgerufenen Internetseiten überhaupt zu ermöglichen, zu statistischen Zwecken sowie zur 
                            Verbesserung unseres Internetangebots. Die IP-Adresse speichern wir nur für die Dauer Ihres Besuchs,
                            eine personenbezogene Auswertung findet nicht statt.
                            '),
                        ))
                    ), new Title(
                        'DATENSCHUTZ BEIM BESUCH DER WEBSEITEN'
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Container('
                                Um die Nutzung unserer Website zu erleichtern, verwenden wir „Cookies“. Cookies sind kleine
                                Textdateien, die von Ihrem Browser auf der Festplatte Ihres Computers abgelegt werden können
                                und hilfreich für die Nutzung unserer Website sind.
                            '),
                            new Container('&nbsp;'),
                            new Container('
                                Wir setzen nur sog. Session-Cookies (auch als temporäre Cookies bezeichnet) ein, also solche,
                                die ausschließlich für die Dauer Ihrer Nutzung einer unserer Internetseiten zwischengespeichert werden.
                                Zweck dieser Cookies ist, Ihren Rechner während eines Besuchs unseres Internetauftritts
                                beim Wechsel von einer unserer Webseiten zu einer anderen unserer Webseiten weiterhin
                                zu identifizieren und das Ende Ihres Besuchs feststellen zu können. Die Cookies werden gelöscht,
                                sobald Sie Ihre Browsersitzung beenden. Eine Erhebung oder Speicherung personenbezogener Daten
                                in Cookies findet in diesem Zusammenhang durch uns nicht statt. Wir setzen auch keine Techniken ein,
                                die durch Cookies anfallende Informationen mit Nutzerdaten verbinden.
                            '),
                            new Container('&nbsp;'),
                            new Container('
                                Sie können Ihren Internet-Browser so einstellen, dass das Speichern von Cookies auf Ihrer
                                Festplatte verhindert wird bzw. Sie jedes Mal gefragt werden, ob Sie mit dem Setzen von Cookies
                                einverstanden sind. Einmal gesetzte Cookies können Sie auch jederzeit wieder löschen.
                                Wie dies im Einzelnen funktioniert, können Sie den Hilfeseiten Ihres Browsers entnehmen.
                            ')
                        ))
                    ), new Title(
                        'EINSATZ VON COOKIES'
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Container('
                                Soweit Sie von uns auf unserem Internetauftritt angebotene Leistungen (wie etwa die
                                Kontaktaufnahmemöglichkeit über das Kontaktformular) in Anspruch nehmen wollen, ist es nötig,
                                dass Sie dazu weitere Daten angeben. Es handelt sich um diejenigen Daten, die zur jeweiligen
                                Abwicklung erforderlich sind.
                            '),
                            new Container('&nbsp;'),
                            new Container('
                                Die Erhebung oder Verwendung Ihrer Daten erfolgt ausschließlich zu dem Zweck, die von 
                                Ihnen gewünschte Leistung zu erbringen.
                            '),
                            new Container('&nbsp;'),
                            new Container('
                                Ihre Daten werden zu vorgenanntem Zweck ggf. an uns unterstützende Dienstleister weitergegeben,
                                die wir selbstverständlich sorgfältig ausgewählt haben. Dabei kann es sich um technische
                                Dienstleister handeln. Die Weitergabe Ihrer Daten an andere Dritte erfolgt ansonsten nur,
                                wenn Sie zuvor eingewilligt haben.
                            ')
                        ))
                    ), new Title(
                        'DATENSCHUTZHINWEISE BEI NUTZUNG VON KONTAKT- / INTERAKTIONSANGEBOTEN'
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Container('
                                Wir verwenden auf unserer Website das Webanalyseprogramm „Piwik“. Dieses Programm ermöglicht
                                es uns, die Benutzung unserer Website zu erfassen und hierdurch gegebenenfalls Optimierungen
                                unserer Website vorzunehmen. Hierzu verwendet das Programm „Piwik“ sogenannte Cookies.
                                Die hierdurch gewonnenen Nutzungsinformationen werden zusammen mit Ihrer IP-Adresse an
                                unseren Server weitergeleitet und zur Analyse des Nutzungsverhaltens gespeichert.
                                Bei diesem Vorgang wird Ihre IP-Adresse umgehend anonymisiert, sodass wir keine Rückschlüsse
                                auf Sie als Nutzer ziehen können. Die derart gewonnenen Informationen werden nicht an
                                Dritte weitergebenen. Sie können die Einstellungen Ihrer Browser-Software ändern und so
                                verhindern, dass Cookies verwendet werden. In diesem Fall kann es vorkommen, dass Sie
                                nicht alle Funktionen unserer Website vollumfänglich nutzen können.
                            '),
                            new Container('&nbsp;'),
                            new Container('Sie können der Speicherung und Verwertung dieser Daten während Ihres Besuchs 
                            auf unserer Website jederzeit widersprechen. Hierfür müssen Sie lediglich das unten stehende 
                            Kästchen anklicken, sodass das gesetzte Häkchen nicht mehr zu sehen ist (Opt-out-Verfahren). 
                            In diesem Fall wird in Ihrem Browser ein sogenannter Opt-out-Cookie hinterlegt, so dass Piwik 
                            keine Sitzungsdaten mehr erheben kann. Beachten Sie bitte, dass das Löschen Ihrer Cookies der 
                            jeweiligen Sitzung zur Folge hat, dass auch das Opt-out-Cookie gelöscht wird und unter 
                            Umständen von Ihnen beim nächsten Besuch unserer Website erneut aktiviert werden muss.'),
                            new Container('&nbsp;'),
                            '<iframe class="sphere-iframe-style" src="/Library/Piwik/index.php?module=CoreAdminHome&action=optOut&language=de"></iframe>'
                        ))
                    ), new Title(
                        'NUTZUNG VON PIWIK'
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Container('
                                Diese Seite nutzt aus Gründen der Sicherheit und zum Schutz der Übertragung vertraulicher
                                Inhalte, wie zum Beispiel der Anfragen, die Sie an uns als Seitenbetreiber senden, 
                                eine SSL-Verschlüsselung. Eine verschlüsselte Verbindung erkennen Sie daran, dass die
                                Adresszeile des Browsers von "http://" auf "https://" wechselt und an dem Schloss-Symbol
                                in Ihrer Browserzeile. Wenn die SSL Verschlüsselung aktiviert ist, können die Daten,
                                die Sie an uns übermitteln, nicht von Dritten mitgelesen werden.
                            '),
                        ))
                    ), new Title(
                        'SSL-VERSCHLÜSSELUNG'
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Container('
                                Sie haben jederzeit das Recht auf unentgeltliche Auskunft über Ihre gespeicherten
                                personenbezogenen Daten, deren Herkunft und Empfänger und den Zweck der Datenverarbeitung
                                sowie ein Recht auf Berichtigung, Sperrung oder Löschung dieser Daten. Hierzu sowie zu
                                weiteren Fragen zum Thema personenbezogene Daten können Sie sich jederzeit unter der im
                                Impressum angegebenen Adresse an uns wenden.
                            '),
                        ))
                    ), new Title(
                        'RECHT AUF AUSKUNFT, LÖSCHUNG, SPERRUNG'
                    )
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Container('
                                Der Nutzung von im Rahmen der Impressumspflicht veröffentlichten Kontaktdaten zur
                                Übersendung von nicht ausdrücklich angeforderter Werbung und Informationsmaterialien wird
                                hiermit widersprochen. Die Betreiber der Seiten behalten sich ausdrücklich rechtliche
                                Schritte im Falle der unverlangten Zusendung von Werbeinformationen, etwa durch Spam-E-Mails, vor.
                            '),
                            new Container('&nbsp;'),
                        ))
                    ), new Title(
                        'WIDERSPRUCH BEI WERBEMAILS'
                    )
                ),
            ))
        );
        return $Stage;
    }
}