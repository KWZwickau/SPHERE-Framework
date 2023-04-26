<?php

namespace SPHERE\Application\Setting\Consumer\Service;

use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblAccountDownloadLock;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblAccountSetting;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblSetting;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblStudentCustody;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Database;
use SPHERE\System\Database\Fitting\Element;
use MOC\V\Component\Database\Component\IBridgeInterface;
use MOC\V\Component\Database\Database as MocDatabase;
use SPHERE\System\Database\Type\MySql;

/**
 * Class Data
 * @package SPHERE\Application\Setting\Consumer\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {
        if (($tblStudentSubjectTypeOrientation = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))) {
            $orientationName = $tblStudentSubjectTypeOrientation->getName();
        } else {
            $orientationName = 'Wahlbereich';
        }

        // Allgemein Public
        $this->createSetting('People', 'Person', 'Relationship', 'GenderOfS1', TblSetting::TYPE_INTEGER, 2, 'Allgemein',
            'Für Personenbeziehungen vom Typ: Sorgeberechtigt wird das folgende Geschlecht für S1 vorausgewählt. [Standard: Weiblich]', true);
        $this->createSetting('Reporting', 'KamenzReport', 'Validation', 'FirstForeignLanguageLevel', TblSetting::TYPE_INTEGER,
            1, 'Allgemein', 'Validierung 1. Fremdsprache im Stammdaten- und Bildungsmodul sowie Modul Kamenzstatistik.
            Klassenstufe, ab welcher ist 1. Fremdsprache unterrichtet wird. [Standard: 1]', true);
        if (($tblSetting = $this->createSetting('Education', 'Lesson', 'Subject', 'HasOrientationSubjects', TblSetting::TYPE_BOOLEAN, '1',
            'Allgemein', 'Validierung ' . $orientationName . 'e im Stammdaten- und Bildungsmodul sowie Modul Kamenzstatistik. Schulträger unterrichtet ' . $orientationName . 'e. [Standard: Ja]', true))
        ) {
            $this->updateSettingDescription($tblSetting, $tblSetting->getCategory(),
                'Validierung ' . $orientationName . 'e im Stammdaten- und Bildungsmodul sowie Modul Kamenzstatistik. Schulträger unterrichtet ' . $orientationName . 'e. [Standard: Ja]',
            $tblSetting->isPublic());
        };
        $this->createSetting('Setting', 'Consumer', 'Service', 'Sort_UmlautWithE', TblSetting::TYPE_BOOLEAN, '1',
            'Allgemein', 'Bei der alphabetischen Sortierung von Namen werden Umlaute ersetzt nach DIN 5007-2 (z.B. ä => ae). 
            Bei Deaktivierung erfolgt Sortierung nach DIN 5007-1 (z.B. Ä/ä und a sind gleich) [Standard: Ja]', true);
        $this->createSetting('Setting', 'Consumer', 'Service', 'Sort_WithShortWords', TblSetting::TYPE_BOOLEAN, '1',
            'Allgemein', 'Bei der alphabetischen Sortierung von Namen werden Präpositionen (z.B. von, de, etc.) für die
            Sortierung berücksichtigt [Standard: Ja]', true);
        $this->createSetting('Setting', 'Consumer', 'Service', 'EmergencyNumber', TblSetting::TYPE_BOOLEAN, '0',
            'Allgemein', 'Notfallnummer in der Stammdatenverwaltung wird am Ende der Ansichten aufgelistet. [Standard: Nein]', true);
        // Allgemein non-public
        $this->createSetting('People', 'Meta', 'Student', 'Automatic_StudentNumber', TblSetting::TYPE_BOOLEAN, '0',
            'Allgemein', 'Die Schülernummern werden automatisch vom System erstellt. In diesem Fall können die
             Schülernummern nicht per Hand vergeben werden. [Standard: Nein]');
        $this->createSetting('Contact', 'Address', 'Address', 'Format_GuiString', TblSetting::TYPE_STRING,
            TblAddress::VALUE_PLZ_ORT_OT_STR_NR, 'Allgemein', 'Reihenfolge der Adressanzeige [Standard: PLZ_ORT_OT_STR_NR]');
        $this->createSetting('People', 'Meta', 'Child', 'AuthorizedToCollectGroups', TblSetting::TYPE_STRING, '', 'Allgemein',
            'Für folgende zusätzliche Personengruppen (mit Komma getrennt) wird der Block Abholberechtigte mit angezeigt. [Standard: ]',
            true);

        // Indiware non-public
        $this->createSetting('Transfer', 'Indiware', 'Import', 'Lectureship_ConvertDivisionLatinToGreek',
            TblSetting::TYPE_BOOLEAN, '0', 'Indiware', 'Ersetzung der Klassengruppennamen beim Import in ausgeschriebene
            Griechische Buchstaben. (z.B. a => alpha) [Standard: Nein]');

        // Document public
        $this->createSetting('Api', 'Document', 'StudentCard_PrimarySchool', 'ShowSchoolName', TblSetting::TYPE_BOOLEAN,
            '1', 'Dokumente', 'Anzeige des Schulnamens (Stempel-Feld oben rechts) auf der Schülerkartei für die
             Grundschule. [Standard: Ja]', true);
        // Document non-public
        $this->createSetting('Api', 'Document', 'Standard', 'PasswordChange_PictureAddress', TblSetting::TYPE_STRING, '',
            'Dokumente', 'Für die Eltern und Schülerzugänge sowie Passwortänderungsanschreiben kann ein Bild (Logo)
             hinterlegt werden. Adresse des Bildes: [Standard: ]');
        $this->createSetting('Api', 'Document', 'Standard', 'PasswordChange_PictureHeight', TblSetting::TYPE_STRING, '',
            'Dokumente', 'Für die Eltern und Schülerzugänge sowie Passwortänderungsanschreiben kann ein Bild (Logo)
             hinterlegt werden. Höhe des Bildes (Maximal 120px): [Standard: 120px]');
        $this->createSetting('Api', 'Document', 'Standard', 'SignOutCertificate_PictureAddress', TblSetting::TYPE_STRING,
            '', 'Dokumente', 'Für die Abmeldebescheinigung kann ein Bild (Logo) hinterlegt werden. Adresse des Bildes: [Standard: ]');
        $this->createSetting('Api', 'Document', 'Standard', 'SignOutCertificate_PictureHeight', TblSetting::TYPE_STRING,
            '', 'Dokumente', 'Für die Abmeldebescheinigung kann ein Bild (Logo) hinterlegt werden. Höhe des Bildes
            (Maximal 120px): [Standard: 80px]');
        $this->createSetting('Api', 'Document', 'Standard', 'EnrollmentDocument_PictureAddress', TblSetting::TYPE_STRING,
            '', 'Dokumente', 'Für die Schulbescheinigung kann ein Bild (Logo) hinterlegt werden. Adresse des Bildes: [Standard: ]');
        $this->createSetting('Api', 'Document', 'Standard', 'EnrollmentDocument_PictureHeight', TblSetting::TYPE_STRING,
            '', 'Dokumente', 'Für die Schulbescheinigung kann ein Bild (Logo) hinterlegt werden. Höhe des Bildes
            (Maximal 120px): [Standard: 90px]');
        $this->createSetting('Api', 'Document', 'Standard', 'Billing_PictureAddress', TblSetting::TYPE_STRING,
            '', 'Dokumente', 'Für den Bescheinigung der Fakturierung kann ein Bild (Logo) hinterlegt werden. Adresse des
             Bildes: [Standard: ]');
        $this->createSetting('Api', 'Document', 'Standard', 'Billing_PictureHeight', TblSetting::TYPE_STRING, '',
            'Dokumente', 'Für den Bescheinigung der Fakturierung kann ein Bild (Logo) hinterlegt werden. Höhe des Bildes
             (Maximal 150px): [Standard: 90px]');

        // Zeugnisse public
        $this->createSetting('Education', 'Certificate', 'Generate', 'DocumentBorder', TblSetting::TYPE_INTEGER, '2',
            'Zeugnisse', 'Festlegung der seitlichen Ränder für Zeugnisse mittels Zahleneingabe (1=mittlerer Rand,
            2=breiter Rand): [Standard: 2] (Diese Einstellung zählt nicht für Abschluss- und Abgangszeugnisse)', true);
        $this->createSetting('Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnDiploma', TblSetting::TYPE_BOOLEAN,
            '0', 'Zeugnisse', 'Anzeige der Zensuren im Wortlaut auf Abschlusszeugnissen [Standard: Nein]', true);
        $this->createSetting('Education', 'Certificate', 'Prepare', 'IsSchoolExtendedNameDisplayed',
            TblSetting::TYPE_BOOLEAN, '0', 'Zeugnisse', 'Anzeige des Schul-Zusatzes (Institutionszusatz) auf Zeugnissen
            [Standard: Nein]', true);
        $this->createSetting('Education', 'Certificate', 'Prepare', 'UseMultipleBehaviorTasks', TblSetting::TYPE_BOOLEAN,
            '0', 'Zeugnisse', 'Anzeige der Kopfnoten aus allen Kopfnotenaufträge bei der Zeugnisvorbereitung, wenn pro
             Halbjahr mehrere Kopfnotenaufträge erteilt wurden. [Standard: Nein]', true);
        $this->createSetting('Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnLeave', TblSetting::TYPE_BOOLEAN,
            '0', 'Zeugnisse', 'Anzeige der Zensuren im Wortlaut auf Abgangszeugnissen [Standard: Nein]', true);
        $this->createSetting('Education', 'Certificate', 'Prepare', 'ShowParentTitle', TblSetting::TYPE_BOOLEAN, '1',
            'Zeugnisse', 'Anzeige des Personentitels von Sorgeberechtigten in der Bildungsempfehlung [Standard: Ja]', true);
        $this->createSetting('Education', 'Certificate', 'Diploma', 'ShowExtendedSchoolName', TblSetting::TYPE_BOOLEAN,
            '', 'Zeugnisse', 'Schul-Zusatz-Name von der Institution auf Abschlusszeugnissen und Abgangszeugnissen
            anzeigen [Standard: Nein]', true);
        $tblSetting = $this->createSetting('Education', 'Certificate', 'Prepare', 'HasRemarkBlocking', TblSetting::TYPE_BOOLEAN, '1',
            'Zeugnisse', 'Sollen leere Bemerkung- und Einschätzungsfelder auf Zeugnissen mit  ("—") ergänzen. [Standard: Ja]', true);
        if($tblSetting->getDescription() != 'Sollen leere Bemerkung- und Einschätzungsfelder auf Zeugnissen mit  ("—") ergänzen. [Standard: Ja]'){
            $this->updateSettingDescription($tblSetting, $tblSetting->getCategory(),
                'Sollen leere Bemerkung- und Einschätzungsfelder auf Zeugnissen mit  ("—") ergänzen. [Standard: Ja]', $tblSetting->isPublic());
        }
        $this->createSetting('Education', 'Certificate', 'Prepare', 'ShowTeamsInCertificateRemark', TblSetting::TYPE_BOOLEAN, '1',
            'Zeugnisse', 'Sollen Arbeitsgemeinschaften in das Bemerkungsfeld der Zeugnisse eingetragen werden. [Standard: Ja]', true);
        $this->createSetting('Education', 'Certificate', 'Prepare', 'ShowOrientationsInCertificateRemark', TblSetting::TYPE_BOOLEAN, '1',
            'Zeugnisse', 'Sollen ' . $orientationName . 'e in das Bemerkungsfeld der Zeugnisse eingetragen werden. [Standard: Ja]', true);

        // Zeugnisse non-public
        $this->createSetting('Education', 'Certificate', 'Generate', 'PictureAddress', TblSetting::TYPE_STRING, '',
            'Zeugnisse', 'Für die Standard-Zeugnisse kann ein Bild (Logo) hinterlegt werden. Logo Maximalmaße 100 x 250.
            Adresse des Bildes: [Standard: ]');
        $this->createSetting('Education', 'Certificate', 'Generate', 'PictureHeight', TblSetting::TYPE_STRING, '',
            'Zeugnisse', 'Für die Standard-Zeugnisse kann ein Bild (Logo) hinterlegt werden. Logo Maximalmaße 100 x 250.
             Das Bild ist ohne Einstellung 66px Hoch, wie das Sachsenlogo. Höhe des Bildes: [Standard: ]');
        $this->createSetting('Education', 'Certificate', 'Generate', 'PictureAddressForOS', TblSetting::TYPE_STRING, '',
            'Zeugnisse', 'Für die Standard-Zeugnisse der Oberschule kann ein Bild (Logo) hinterlegt werden. Logo Maximalmaße 100 x 250.
            Adresse des Bildes: [Standard: ]');
        $this->createSetting('Education', 'Certificate', 'Generate', 'PictureHeightForOS', TblSetting::TYPE_STRING, '',
            'Zeugnisse', 'Für die Standard-Zeugnisse der Oberschule kann ein Bild (Logo) hinterlegt werden. Logo Maximalmaße 100 x 250.
             Das Bild ist ohne Einstellung 66px Hoch, wie das Sachsenlogo. Höhe des Bildes: [Standard: ]');
        $this->createSetting('Education', 'Certificate', 'Generate', 'PictureAddressForDiplomaCertificate',
            TblSetting::TYPE_STRING, '', 'Zeugnisse', 'Für die Standard-Abschluss-Zeugnisse kann ein Bild (Logo)
             hinterlegt werden. Adresse des Bildes: []');
        $this->createSetting('Education', 'Certificate', 'Generate', 'PictureHeightForDiplomaCertificate',
            TblSetting::TYPE_STRING, '', 'Zeugnisse', 'Für die Standard-Abschluss-Zeugnisse kann ein Bild (Logo)
             hinterlegt werden. Höhe des Bildes: []');
        $this->createSetting('Education', 'Certificate', 'Generate', 'PictureDisplayLocationForDiplomaCertificate',
            TblSetting::TYPE_BOOLEAN, '1', 'Zeugnisse', 'Für die Standard-Abschluss-Zeugnisse wird das Logo auf der 2.
             Seite unter dem Abschluss angezeigt (ansonsten auf dem Cover oben links): [Ja]');
        $this->createSetting('Education', 'Certificate', 'Generate', 'PictureAddressForLeaveCertificate',
            TblSetting::TYPE_STRING, '', 'Zeugnisse', 'Für die Standard-Abgangs-Zeugnisse kann ein Bild (Logo)
             hinterlegt werden. Adresse des Bildes: []');
        $this->createSetting('Education', 'Certificate', 'Generate', 'PictureHeightForLeaveCertificate',
            TblSetting::TYPE_STRING, '', 'Zeugnisse', 'Für die Standard-Abgangs-Zeugnisse kann ein Bild (Logo)
             hinterlegt werden. Höhe des Bildes: []');
        $this->createSetting('Api', 'Education', 'Certificate', 'OrientationAcronym', TblSetting::TYPE_STRING, '',
            'Zeugnisse','Werden die Neigungskurse in der Bildung nicht einzeln gepflegt, sondern nur ein einzelner
             Standard-Neigungskurs, kann hier das Kürzel des Standard-Neigungskurses (z.B. NK) hinterlegt werden. Für
              die Zeugnisse wir dann der eigentliche Neigungskurs aus der Schülerakte des Schülers gezogen. [Standard: ]');
        $this->createSetting('Api', 'Education', 'Certificate', 'ProfileAcronym', TblSetting::TYPE_STRING, '', 'Zeugnisse',
            'Werden die Profile in der Bildung nicht einzeln gepflegt, sondern nur ein einzelnes Standard-Profil, 
            kann hier das Kürzel des Standard-Profils (z.B. PRO) hinterlegt werden. Für die Zeugnisse wir dann das
            eigentliche Profil aus der Schülerakte des Schülers gezogen. [Standard: ]');
        $this->createSetting('Education', 'Certificate', 'Prepare', 'SchoolExtendedNameSeparator',
            TblSetting::TYPE_STRING, '', 'Zeugnisse', 'Anzeige des Schul-Zusatzes (Institutionszusatz) auf Zeugnissen
             mit dem Trennzeichen: [Standard: ]');
        $this->createSetting('Education', 'Certificate', 'Generate', 'UseCourseForCertificateChoosing',
            TblSetting::TYPE_BOOLEAN, '1', 'Zeugnisse', 'Es wird der Bildungsgang des Schülers verwendet, um die
            entsprechende Zeugnisvorlage (Mittelschule) dem Schüler automatisch zuzuordnen. [Standard: Ja]');
        $this->createSetting('Education', 'Certificate', 'Diploma', 'PreArticleForSchoolName',
            TblSetting::TYPE_STRING, '', 'Zeugnisse', 'Artikel vor dem Schulnamen auf Abschlusszeugnissen und
            Abgangszeugnissen (z.B. das): [Standard: ]');
        $this->createSetting('Education', 'Certificate', 'Diploma', 'AlternateSchoolName', TblSetting::TYPE_STRING,
            '', 'Zeugnisse', 'Schulname auf Abschlusszeugnissen und Abgangszeugnissen: [Standard: ]');
        $this->createSetting('Education', 'Certificate', 'Diploma', 'AlternateExtendedSchoolName', TblSetting::TYPE_STRING,
            '', 'Zeugnisse', 'Schul-Zusatz-Name auf Abschlusszeugnissen und Abgangszeugnissen: [Standard: ]');
        $this->createSetting('Education', 'Certificate', 'Generator', 'IsDescriptionAsJustify', TblSetting::TYPE_BOOLEAN,
            '1', 'Zeugnisse', 'die Schülereinschätzung als Blocksatz dargestellen [Standard: Ja]');

        // Notenbücher public
        $this->createSetting('Education', 'Graduation', 'Gradebook', 'SortHighlighted', TblSetting::TYPE_BOOLEAN, '0',
            'Notenbücher', 'Sortierung der Zensuren im Notenbuch nach Großen (fettmarkiert) und Kleinen Noten. Bei
             Deaktivierung erfolgt Sortierung nach Datum. [Standard: Nein]', true);
        $this->createSetting('Education', 'Graduation', 'Gradebook', 'IsHighlightedSortedRight', TblSetting::TYPE_BOOLEAN,
            '1', 'Notenbücher', 'Bei der Sortierung der Zensuren im Notenbuch nach Großen (fettmarkiert) und Kleinen
             Noten, stehen die Großen Noten rechts. [Standard: Ja]', true);
        $this->createSetting('Education', 'Graduation', 'Gradebook', 'ShowAverageInPdf', TblSetting::TYPE_BOOLEAN, '1',
            'Notenbücher', 'Anzeige des Notendurchschnitts im heruntergeladenen Notenbuch (PDF) [Standard: Ja]', true);
        $this->createSetting('Education', 'Graduation', 'Gradebook', 'ShowCertificateGradeInPdf', TblSetting::TYPE_BOOLEAN,
            '1', 'Notenbücher', 'Anzeige der Zeugnisnote im heruntergeladenen Notenbuch (PDF) [Standard: Ja]', true);
        $this->createSetting('Education', 'Graduation', 'Gradebook', 'AddNameRowAtCount', TblSetting::TYPE_INTEGER, 10,
            'Notenbücher', 'Anzeige zusätzliche Namensspalte im Notenbuch bei mehr als (Anzahl) Noten im gesamten
            Schuljahr [Standard: 10]', true);

        // Schüler-Eltern-Zugang
        $this->createSetting('ParentStudentAccess', 'Person', 'ContactDetails', 'OnlineContactDetailsAllowedForSchoolTypes', TblSetting::TYPE_STRING, '',
            'Eltern/Schüler-Zugang', 'Online Kontaktdaten Anzeige und Änderungswünsche durch Eltern ist für Schüler folgender Schularten (Kürzel z.B. GS, OS, Gy, BS, BFS, BGJ, BVJ, BGy, FOS, FS, GMS, ISS) möglich. 
            Mehrere Schularten sind mit Komma zu trennen. [Standard: ]', true, 1);
        if (($tblSetting = $this->createSetting('Education', 'ClassRegister', 'Absence', 'OnlineAbsenceAllowedForSchoolTypes', TblSetting::TYPE_STRING, '',
            'Fehlzeiten', 'Online Fehlzeiten von Eltern/Schüler ist für Schüler folgender Schularten (Kürzel z.B. GS, OS, Gy, BS, BFS, BGJ, BVJ, BGy, FOS, FS, GMS, ISS) möglich. Mehrere Schularten sind mit
             Komma zu trennen. [Standard: ]', true, 2))
        ){
            $this->updateSettingSortOrderAndCategory($tblSetting, 2, 'Eltern/Schüler-Zugang');
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'IgnoreSchoolType', TblSetting::TYPE_STRING, '',
            'Eltern/Schüler-Zugang', 'Eingrenzung Anzeige Notenübersicht für Eltern/Schüler nach Schulart(en). Festlegung mittels Kürzel der Schulart.
            (Kürzel z.B. GS, OS, Gy, BS, BFS, BGJ, BVJ, BGy, FOS, FS, GMS, ISS) Mehrere Schularten sind mit Komma zu trennen. [Standard: ]', true, 3))
        ) {
            $this->updateSettingDescription($tblSetting, 'Eltern/Schüler-Zugang',
                'Eingrenzung Anzeige Notenübersicht für Eltern/Schüler nach Schulart(en). Festlegung mittels Kürzel der Schulart. (Kürzel z.B. GS, OS, Gy, BS, BFS, BGJ, BVJ, BGy, FOS, FS, GMS, ISS)
                Mehrere Schularten sind mit Komma zu trennen. [Standard: ]', $tblSetting->isPublic()
            );
            $this->updateSettingSortOrder($tblSetting, 3);
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'YearOfUserView', TblSetting::TYPE_STRING, '', 'Eltern/Schüler-Zugang',
            'Anzeige der Noten in der Notenübersicht für Eltern/Schüler ab folgenden Schuljahr (z.B. 2019/20). Wenn leer werden
            Noten aller Schuljahre angezeigt [Standard: ]', true, 4))
        ) {
            $this->updateSettingDescription($tblSetting, 'Eltern/Schüler-Zugang',
                'Anzeige der Noten in der Notenübersicht für Eltern/Schüler ab folgenden Schuljahr (z.B. 2019/20). Wenn leer werden
                Noten aller Schuljahre angezeigt [Standard: ]', $tblSetting->isPublic()
            );
            $this->updateSettingSortOrderAndCategory($tblSetting, 4, 'Eltern/Schüler-Zugang');
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'IsShownAverageInStudentOverview',
            TblSetting::TYPE_BOOLEAN, false, 'Eltern/Schüler-Zugang', 'Anzeige des Notendurchschnitts in der
            Notenübersicht für Eltern/Schüler [Standard: Nein]', true, 5))
        ) {
            $this->updateSettingDescription($tblSetting, 'Eltern/Schüler-Zugang',
                'Anzeige des Notendurchschnitts in der Notenübersicht für Eltern/Schüler [Standard: Nein]', $tblSetting->isPublic()
            );
            $this->updateSettingSortOrderAndCategory($tblSetting, 5, 'Eltern/Schüler-Zugang');
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'IsShownDivisionSubjectScoreInStudentOverview',
            TblSetting::TYPE_BOOLEAN, false, 'Eltern/Schüler-Zugang',
            'Anzeige des Fach-Klassen-Durchschnitts in der Notenübersicht für Eltern/Schüler. [Standard: Nein]', true, 6))
        ) {
            $this->updateSettingDescription($tblSetting, 'Eltern/Schüler-Zugang',
                'Anzeige des Fach-Klassen-Durchschnitts in der Notenübersicht für Eltern/Schüler. [Standard: Nein]', $tblSetting->isPublic()
            );
            $this->updateSettingSortOrderAndCategory($tblSetting, 6, 'Eltern/Schüler-Zugang');
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'IsShownGradeMirrorInStudentOverview',
            TblSetting::TYPE_BOOLEAN, false, 'Eltern/Schüler-Zugang',
            'Anzeige des Notenspiegels in der Notenübersicht für Eltern/Schüler. [Standard: Nein]', true, 7))
        ) {
            $this->updateSettingDescription($tblSetting, 'Eltern/Schüler-Zugang',
                'Anzeige des Notenspiegels in der Notenübersicht für Eltern/Schüler. [Standard: Nein]', $tblSetting->isPublic()
            );
            $this->updateSettingSortOrderAndCategory($tblSetting, 7, 'Eltern/Schüler-Zugang');
        }
        if (($tblSetting = $this->createSetting('Education', 'Graduation', 'Gradebook', 'ShowHighlightedTestsInGradeOverview',
            TblSetting::TYPE_BOOLEAN, '1', 'Eltern/Schüler-Zugang', 'Anzeige der geplanten Großen Noten (fettmarkiert, z.B.
             Klassenarbeiten) in der Notenübersicht für Eltern/Schüler und in der Schülerübersicht [Standard: Ja]', true, 8))
        ) {
            $this->updateSettingDescription($tblSetting, 'Eltern/Schüler-Zugang',
                'Anzeige der geplanten Großen Noten (fettmarkiert, z.B.
                Klassenarbeiten) in der Notenübersicht für Eltern/Schüler und in der Schülerübersicht [Standard: Ja]', $tblSetting->isPublic()
            );
            $this->updateSettingSortOrderAndCategory($tblSetting, 8, 'Eltern/Schüler-Zugang');
        }
        if (($tblSetting = $this->createSetting('ParentStudentAccess', 'OnlineGradebook', 'OnlineGradebook' , 'IsScoreRuleShown', TblSetting::TYPE_BOOLEAN, '0', 'Eltern/Schüler-Zugang',
            'Anzeige der Berechnungsvorschrift in der Notenübersicht für Eltern/Schüler. [Standard: Nein]', true, 9))
        ) {
            $this->updateSettingSortOrder($tblSetting, 9);
        }

        // Adresslisten für Serienbriefe public
        $this->createSetting('Reporting', 'SerialLetter', 'GenderSort', 'FirstFemale', TblSetting::TYPE_BOOLEAN, 1,
            'Adresslisten für Serienbriefe', 'Im Excel-Download wird in den Briefanreden nach DIN 5008 die Frau zuerst
             angesprochen (Sehr geehrte Frau, sehr geehrter Herr). [Standard: Ja]', true);

        // Klassenbücher public
        $this->createSetting('Education', 'ClassRegister', 'Sort', 'SortMaleFirst', TblSetting::TYPE_BOOLEAN, '1',
            'Klassenbücher', 'Bei der Sortierung der Schüler im Klassenbuch nach Geschlecht, stehen die männlichen
            Schüler zuerst. [Standard: Ja]', true);
        if (($tblSetting = $this->getSetting('Education', 'ClassRegister', 'Frontend', 'ShowDownloadButton'))) {
            $this->destroySetting($tblSetting);
        }
        $this->createSetting('Education', 'ClassRegister', 'LessonContent', 'IsChangeLessonContentByOtherTeacherAllowed', TblSetting::TYPE_BOOLEAN, '1',
            'Klassenbücher', 'Darf ein andere Fachlehrer einen Klassentagebucheintrag nachträglich ändern [Standard: Ja]', true);
        $this->createSetting('Education', 'ClassRegister', 'LessonContent', 'StartsLessonContentWithZeroLesson', TblSetting::TYPE_BOOLEAN, '0',
            'Klassenbücher', 'Klassentagebuch beginnt mit der 0. Unterrichtseinheit [Standard: Nein]', true
        );
        $this->createSetting('Education', 'ClassRegister', 'LessonContent', 'SaturdayLessonsSchoolTypes', TblSetting::TYPE_STRING, '',
            'Klassenbücher', 'Samstags-Unterricht und Samstags-Fehlzeiten sind für folgende Schularten (Kürzel z.B. GS, OS, Gy, BS, BFS, BGJ, BVJ, BGy, FOS, FS, GMS, ISS) möglich.
             Mehrere Schularten sind mit Komma zu trennen. [Standard: ]', true);

        // Leistungsüberprüfungen public
        $this->createSetting('Education', 'Graduation', 'Evaluation', 'HasBehaviorGradesForSubjectsWithNoGrading',
            TblSetting::TYPE_BOOLEAN, '0', 'Leistungsüberprüfungen', 'Bei Kopfnotenaufträgen können auch Kopfnoten für
            Fächer vergeben werden, welche nicht benotet werden. [Standard: Nein]', true);
        $this->createSetting('Education', 'Graduation', 'Evaluation', 'AutoPublicationOfTestsAfterXDays',
            TblSetting::TYPE_INTEGER, '28', 'Leistungsüberprüfungen', 'Automatische Bekanntgabe von
             Leistungsüberprüfungen für die Notenübersicht der Schüler/Eltern nach x Tagen: [Standard: 28]', true);
        $this->createSetting('Education', 'Graduation', 'Evaluation', 'ShowProposalBehaviorGrade',
            TblSetting::TYPE_BOOLEAN, '0', 'Leistungsüberprüfungen', 'Anzeige der Kopfnoten der Klassenlehrer als
            Notenvorschlag [Standard: Nein]', true);

        // Fehlzeiten public
        $this->createSetting('Education', 'ClassRegister', 'Absence', 'DefaultStatusForNewOnlineAbsence', TblSetting::TYPE_INTEGER, TblAbsence::VALUE_STATUS_UNEXCUSED, 'Fehlzeiten',
            'Voreingestellter Fehlzeiten-Status beim Erstellen einer neuen Online Fehlzeiten von Eltern/Schüler [Standard: unentschuldigt]', true, 2);
        $this->createSetting('Education', 'ClassRegister', 'Absence', 'DefaultStatusForNewAbsence', TblSetting::TYPE_INTEGER, TblAbsence::VALUE_STATUS_UNEXCUSED, 'Fehlzeiten',
            'Voreingestellter Fehlzeiten-Status beim Erstellen einer neuen Fehlzeit [Standard: unentschuldigt]', true, 3);
        if (($tblSetting = $this->createSetting('Education', 'ClassRegister', 'Absence', 'UseClassRegisterForAbsence', TblSetting::TYPE_BOOLEAN,
            '1', 'Fehlzeiten', 'Automatische Übernahme der Fehlzeiten aus dem Klassenbuch aufs Zeugnis [Standard: Ja]', true, 4))
        ) {
            $this->updateSettingDescription($tblSetting, $tblSetting->getCategory(),
                'Automatische Übernahme der Fehlzeiten aus dem Klassenbuch aufs Zeugnis [Standard: Ja]', $tblSetting->isPublic());
            $this->updateSettingSortOrder($tblSetting, 4);
        }

        // UCS
        if (($tblSetting = $this->createSetting('Setting', 'Univention', 'Univention', 'API_Mail',
            TblSetting::TYPE_STRING, '', 'Univention', 'E-Mail-Adresse für UCS Benutzername
             ist kein Pflichtfeld für Schüler folgender Schularten (Kürzel z.B. GS, OS, Gy, BS, BFS, BGJ, BVJ, BGy, FOS, FS, GMS, ISS). Mehrere Schularten sind mit
             Komma zu trennen. [Standard: ]'
        ))) {
            $this->updateSettingDescription($tblSetting, 'Univention',
                'E-Mail-Adresse für UCS Benutzername
                ist kein Pflichtfeld für Schüler folgender Schularten (Kürzel z.B. GS, OS, Gy, BS, BFS, BGJ, BVJ, BGy, FOS, FS, GMS, ISS). Mehrere Schularten sind mit
                Komma zu trennen. [Standard: ]', $tblSetting->isPublic()
            );
        }
    }

    /**
     * @param        $Cluster
     * @param        $Application
     * @param null   $Module
     * @param string $Identifier
     *
     * @return false|TblSetting
     */
    public function getSetting(
        $Cluster,
        $Application,
        $Module = null,
        $Identifier = ''
    ) {

        return $this->getCachedEntityBy(
            __METHOD__,
            $this->getConnection()->getEntityManager(),
            'TblSetting',
            array(
                TblSetting::ATTR_CLUSTER => $Cluster,
                TblSetting::ATTR_APPLICATION => $Application,
                TblSetting::ATTR_MODULE => $Module ? $Module : null,
                TblSetting::ATTR_IDENTIFIER => $Identifier,
            )
        );
    }

    /**
     * @param $Id
     *
     * @return false|TblSetting
     */
    public function getSettingById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblSetting', $Id);
    }

    /**
     * @param bool $IsSystem
     *
     * @return false|TblSetting[]
     */
    public function getSettingAll($IsSystem = false)
    {

        if ($IsSystem) {
            return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblSetting', array(
                TblSetting::ATTR_CATEGORY => self::ORDER_ASC, TblSetting::ATTR_SORT_ORDER => self::ORDER_ASC, TblSetting::ATTR_DESCRIPTION => self::ORDER_ASC
            ));
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblSetting', array(
                TblSetting::ATTR_IS_PUBLIC => true
            ), array(
                TblSetting::ATTR_CATEGORY => self::ORDER_ASC, TblSetting::ATTR_SORT_ORDER => self::ORDER_ASC, TblSetting::ATTR_DESCRIPTION => self::ORDER_ASC
            ));
        }
    }

    /**
     * @param $Id
     *
     * @return false|TblStudentCustody
     */
    public function getStudentCustodyById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudentCustody',
            $Id);
    }

    /**
     * @param TblAccount $tblAccountStudent
     *
     * @return false|TblStudentCustody[]
     */
    public function getStudentCustodyByStudent(TblAccount $tblAccountStudent)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudentCustody',
            array(
                TblStudentCustody::ATTR_SERVICE_TBL_ACCOUNT_STUDENT => $tblAccountStudent->getId()
            ));
    }

    /**
     * @param TblAccount $tblAccountStudent
     * @param TblAccount $tblAccountCustody
     *
     * @return false|TblStudentCustody
     */
    public function getStudentCustodyByStudentAndCustody(TblAccount $tblAccountStudent, TblAccount $tblAccountCustody)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudentCustody',
            array(
                TblStudentCustody::ATTR_SERVICE_TBL_ACCOUNT_STUDENT => $tblAccountStudent->getId(),
                TblStudentCustody::ATTR_SERVICE_TBL_ACCOUNT_CUSTODY => $tblAccountCustody->getId()
            ));
    }

    /**
     * @param $Cluster
     * @param $Application
     * @param $Module
     * @param $Identifier
     * @param string $Type
     * @param $Value
     * @param string $Category
     * @param string $Description
     * @param bool $IsPublic
     * @param null $SortOrder
     *
     * @return TblSetting
     */
    public function createSetting(
        $Cluster,
        $Application,
        $Module,
        $Identifier,
        $Type,
        $Value,
        $Category = 'Allgemein',
        $Description = '',
        $IsPublic = false,
        $SortOrder = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblSetting')->findOneBy(array(
            TblSetting::ATTR_CLUSTER => $Cluster,
            TblSetting::ATTR_APPLICATION => $Application,
            TblSetting::ATTR_MODULE => $Module ? $Module : null,
            TblSetting::ATTR_IDENTIFIER => $Identifier,
        ));
        if ($Entity === null) {
            $Entity = new TblSetting();
            $Entity->setCluster($Cluster);
            $Entity->setApplication($Application);
            $Entity->setModule($Module);
            $Entity->setIdentifier($Identifier);
            $Entity->setType($Type);
            $Entity->setValue($Value);
            $Entity->setCategory($Category);
            $Entity->setDescription($Description);
            $Entity->setIsPublic($IsPublic);
            $Entity->setSortOrder($SortOrder);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSetting $tblSetting
     * @param $value
     *
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function updateSetting(TblSetting $tblSetting, $value)
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSetting $Entity */
        $Entity = $Manager->getEntityById('TblSetting', $tblSetting->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setValue($value);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblSetting $tblSetting
     * @param string $category
     * @param string $description
     * @param bool $isPublic
     *
     * @return bool
     */
    public function updateSettingDescription(TblSetting $tblSetting, $category, $description, $isPublic = false)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSetting $Entity */
        $Entity = $Manager->getEntityById('TblSetting', $tblSetting->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setCategory($category);
            $Entity->setDescription($description);
            $Entity->setIsPublic($isPublic);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblSetting $tblSetting
     * @param int $SortOrder
     *
     * @return bool
     */
    public function updateSettingSortOrder(TblSetting $tblSetting, int $SortOrder): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSetting $Entity */
        $Entity = $Manager->getEntityById('TblSetting', $tblSetting->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setSortOrder($SortOrder);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblSetting $tblSetting
     * @param int $SortOrder
     * @param string $Category
     *
     * @return bool
     */
    public function updateSettingSortOrderAndCategory(TblSetting $tblSetting, int $SortOrder, string $Category): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSetting $Entity */
        $Entity = $Manager->getEntityById('TblSetting', $tblSetting->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setSortOrder($SortOrder);
            $Entity->setCategory($Category);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblAccount $tblAccountStudent
     * @param TblAccount $tblAccountCustody
     * @param TblAccount $tblAccountBlocker
     *
     * @return TblStudentCustody
     */
    public function createStudentCustody(
        TblAccount $tblAccountStudent,
        TblAccount $tblAccountCustody,
        TblAccount $tblAccountBlocker
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblStudentCustody();
        $Entity->setServiceTblAccountStudent($tblAccountStudent);
        $Entity->setServiceTblAccountCustody($tblAccountCustody);
        $Entity->setServiceTblAccountBlocker($tblAccountBlocker);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentCustody $tblStudentCustody
     *
     * @return bool
     */
    public function removeStudentCustody(TblStudentCustody $tblStudentCustody)
    {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblStudentCustody')->findOneBy(array('Id' => $tblStudentCustody->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSetting $tblSetting
     * @param TblConsumer $tblConsumer
     *
     * @return string
     */
    public function getSettingByConsumer(TblSetting $tblSetting, TblConsumer $tblConsumer)
    {

        $value = '';
        $connection = false;
        $container = Database::getDataBaseConfig($tblConsumer);

        if ($container) {
            try {
                $connection = $this->getConnectionByAcronym(
                    $container->getContainer('Host')->getValue(),
                    $container->getContainer('Username')->getValue(),
                    $container->getContainer('Password')->getValue(),
                    $tblConsumer->getAcronym()
                );
                if ($connection) {
                    $queryBuilder = $connection->getQueryBuilder();

                    $query = $queryBuilder->select('S.Value')
                        ->from($tblConsumer->getAcronym().'_SettingConsumer.tblSetting', 'S')
                        ->where('S.Identifier = :identifier')
                        ->setParameter('identifier', $tblSetting->getIdentifier());
                    $result = $query->execute();
                    $array = $result->fetch();

                    if (isset($array['Value'])) {
                        $value = $array['Value'];
                    }

                    $connection->getConnection()->close();
                }
            } catch (\Exception $Exception) {
                if ($connection) {
                    $connection->getConnection()->close();
                }
                $connection = null;
            }
        }

        return $value;
    }

    /**
     * @param string $Host Server-Address (IP)
     * @param string $User
     * @param string $Password
     * @param string $Acronym DatabaseName will get prefix '_SettingConsumer' e.g. {Acronym}_SettingConsumer
     *
     * @return bool|IBridgeInterface
     */
    private function getConnectionByAcronym($Host, $User, $Password, $Acronym)
    {
        $Connection = MocDatabase::getDatabase(
            $User, $Password, strtoupper($Acronym).'_SettingConsumer', (new MySql())->getIdentifier(), $Host
        );
        if ($Connection->getConnection()->isConnected()) {
            return $Connection;
        }
        return false;
    }

    /**
     * @param TblAccount $tblAccount
     * @param \DateTime $dateTime
     * @param string $identifier
     * @param boolean $isLocked
     * @param boolean $isLockedLastLoad
     *
     * @return TblAccountDownloadLock
     */
    public function createAccountDownloadLock(
        TblAccount $tblAccount,
        \DateTime $dateTime,
        $identifier,
        $isLocked,
        $isLockedLastLoad
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblAccountDownloadLock $Entity */
        $Entity = $Manager->getEntity('TblAccountDownloadLock')->findOneBy(array(
            TblAccountDownloadLock::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            TblAccountDownloadLock::ATTR_IDENTIFIER => $identifier
        ));
        if ($Entity === null) {
            $Entity = new TblAccountDownloadLock();
            $Entity->setServiceTblAccount($tblAccount);
            $Entity->setDate($dateTime);
            $Entity->setIdentifier($identifier);
            $Entity->setIsLocked($isLocked);
            $Entity->setIsLockedLastLoad($isLockedLastLoad);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        } else {
            $Protocol = clone $Entity;
            if (null !== $Entity) {
                $Entity->setServiceTblAccount($tblAccount);
                $Entity->setDate($dateTime);
                $Entity->setIdentifier($identifier);
                $Entity->setIsLocked($isLocked);
                $Entity->setIsLockedLastLoad($isLockedLastLoad);

                $Manager->saveEntity($Entity);
                Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            }
        }

        return $Entity;
    }

    /**
     * @param TblAccount $tblAccount
     * @param $identifier
     *
     * @return false|TblAccountDownloadLock
     */
    public function getAccountDownloadLock(
        TblAccount $tblAccount,
        $identifier
    ) {
        return $this->getForceEntityBy(
            __METHOD__,
            $this->getEntityManager(),
            'TblAccountDownloadLock', array(
                TblAccountDownloadLock::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
                TblAccountDownloadLock::ATTR_IDENTIFIER => $identifier
            )
        );
    }

    /**
     * @param TblSetting $tblSetting
     *
     * @return bool
     */
    public function destroySetting(TblSetting $tblSetting)
    {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblSetting')->findOneBy(array('Id' => $tblSetting->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblAccount $tblAccount
     * @param string $Identifier
     * @param string $Value
     *
     * @return TblAccountSetting
     */
    public function createAccountSetting(
        TblAccount $tblAccount,
        string $Identifier,
        string $Value
    ): TblAccountSetting {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblAccountSetting $Entity */
        $Entity = $Manager->getEntity('TblAccountSetting')->findOneBy(array(
            TblAccountSetting::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            TblAccountSetting::ATTR_IDENTIFIER => $Identifier,
        ));

        if ($Entity === null) {
            $Entity = new TblAccountSetting();
            $Entity->setServiceTblAccount($tblAccount);
            $Entity->setIdentifier($Identifier);
            $Entity->setValue($Value);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        } else {
            $Protocol = clone $Entity;
            $Entity->setValue($Value);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblAccount $tblAccount
     * @param string $Identifier
     *
     * @return false|TblAccountSetting
     */
    public function getAccountSetting(
        TblAccount $tblAccount,
        string $Identifier
    ) {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblAccountSetting', array(
            TblAccountSetting::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            TblAccountSetting::ATTR_IDENTIFIER => $Identifier
        ));
    }
}