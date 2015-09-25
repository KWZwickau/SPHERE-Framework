<?php
namespace SPHERE\Application;

use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Roadmap as RoadmapExtension;

/**
 * Class Roadmap
 *
 * @package SPHERE\Application
 */
class Roadmap extends Extension
{

    /** @var null|RoadmapExtension $Roadmap */
    private $Roadmap = null;

    /**
     *
     */
    public function __construct()
    {

        $this->Roadmap = $this->getRoadmap();

        $this->versionMajor0Minor8Patch0();
        $this->versionMajor0Minor9Patch0();
        $this->versionMajor1Minor0Patch0();
        $this->versionMajor1Minor1Patch0();
        $this->versionMajor1Minor2Patch0();
        $this->poolMajor1MinorXPatchX();
    }

    /**
     * Version 0.8.0
     * To be released 28.09.2015
     */
    private function versionMajor0Minor8Patch0()
    {

        $Release = $this->Roadmap->createRelease('0.8.0', 'Demoversion (Ziel 28.09.2015)');

        // Personenverwaltung
        $Category = $Release->createCategory('Personenverwaltung');

        $Feature = $Category->createFeature('Suche');
        $Feature->createTask('Filterung über Gruppen', '', true);
        $Feature->createTask('Volltextsuche über Name', '', true);

        $Feature = $Category->createFeature('Gruppen');
        $Feature->createTask('Fest definierte Gruppen')
            ->createDuty('Alle (Personendaten)', true)
            ->createDuty('Interessent', true)
            ->createDuty('Schüler', true)
            ->createDuty('Sorgeberechtigt', true)
            ->createDuty('Mitarbeiter', true);

        $Feature->createTask('Frei definierbare Gruppen', false)
            ->createDuty('Gruppen hinzufügen', true)
            ->createDuty('Gruppen bearbeiten', true)
            ->createDuty('Gruppen löschen', true);

        $Feature = $Category->createFeature('Person');
        $Feature->createTask('Grunddaten', 'Personname und Gruppenzugehörigkeit')
            ->createDuty('Name', true)
            ->createDuty('Gruppen', true);
        $Feature->createTask('Informationen (Metadaten)')
            ->createDuty('Personendaten', true)
            ->createDuty('Interessent', true)
            ->createDuty('Schülerakte (Versetzung, 3x AG, Kurse)', false)
            ->createDuty('Sorgerechtdaten', true);
        $Feature->createTask('Adressdaten')
            ->createDuty('Adresse hinzufügen', true)
            ->createDuty('Adresse bearbeiten', true)
            ->createDuty('Adresse löschen', true);
        $Feature->createTask('Kontaktdaten')
            ->createDuty('Telefonnummer hinzufügen', true)
            ->createDuty('Telefonnummer bearbeiten', true)
            ->createDuty('Telefonnummer löschen', true)
            ->createDuty('E-Mail Adresse hinzufügen', true)
            ->createDuty('E-Mail Adresse bearbeiten', true)
            ->createDuty('E-Mail Adresse löschen', true);
        $Feature->createTask('Beziehungen')
            ->createDuty('Personenbeziehung hinzufügen', true)
            ->createDuty('Personenbeziehung bearbeiten', true)
            ->createDuty('Personenbeziehung löschen', true)
            ->createDuty('Firmenbeziehung hinzufügen', true)
            ->createDuty('Firmenbeziehung bearbeiten', true)
            ->createDuty('Firmenbeziehung löschen', true);

        // Firmenverwaltung
        $Category = $Release->createCategory('Firmenverwaltung');

        $Feature = $Category->createFeature('Suche');
        $Feature->createTask('Filterung über Gruppen', '', true);
        $Feature->createTask('Volltextsuche über Name', '', true);

        $Feature = $Category->createFeature('Gruppen');
        $Feature->createTask('Fest definierte Gruppen')
            ->createDuty('Alle', true)
            ->createDuty('Schulen', true);
        $Feature->createTask('Frei definierbare Gruppen')
            ->createDuty('Gruppen hinzufügen', true)
            ->createDuty('Gruppen bearbeiten', true)
            ->createDuty('Gruppen löschen', true);

        $Feature = $Category->createFeature('Firma');
        $Feature->createTask('Grunddaten', 'Firmenname und Gruppenzugehörigkeit', true)
            ->createDuty('Name', true)
            ->createDuty('Gruppen', true);
        $Feature->createTask('Adressdaten')
            ->createDuty('Adresse hinzufügen', true)
            ->createDuty('Adresse bearbeiten', true)
            ->createDuty('Adresse löschen', true);
        $Feature->createTask('Kontaktdaten')
            ->createDuty('Telefonnummer hinzufügen', true)
            ->createDuty('Telefonnummer bearbeiten', true)
            ->createDuty('Telefonnummer löschen', true)
            ->createDuty('E-Mail Adresse hinzufügen', true)
            ->createDuty('E-Mail Adresse bearbeiten', true)
            ->createDuty('E-Mail Adresse löschen', true);
        $Feature->createTask('Beziehungen')
            ->createDuty('Erfolgt über Personenverwaltung', true);

        // Bildung
        $Category = $Release->createCategory('Bildung');

        $Feature = $Category->createFeature('Unterricht');
        $Feature->createTask('Kategorie-Gruppen', 'z.B. Neigungskurs, Vertiefungskurs, Profil, ...')
            ->createDuty('Fest definierte Gruppen in Datenbank', true);
        $Feature->createTask('Fach-Kategorie', 'z.B. Fremdsprache, Soziales & Diakonie, Technik')
            ->createDuty('Fest definierte Kategorien in Datenbank', true)
            ->createDuty('Vordefinierte Kategorien in Datenbank', true);
        $Feature->createTask('Kategorie-Gruppen/Kategorie zuweisen')
            ->createDuty('Vordefinierte Verknüpfungen in Datenbank', true);
        $Feature->createTask('Fächer', 'z.B. Deutsch, Mathematik, Künstlerisches Profil')
            ->createDuty('Vordefinierte Fächer in Datenbank', true);
        $Feature->createTask('Fach-Kategorie zuweisen')
            ->createDuty('Vordefinierte Verknüpfungen in Datenbank', true);

        // Einstellungen
        $Category = $Release->createCategory('Einstellungen');

        $Feature = $Category->createFeature('Mandant',
            new External('siehe EGE', 'http://www.ege-annaberg.de/node/416'));
        $Feature->createTask('Schulen')
            ->createDuty('Eigene Schulen aus Firmen wählen')
            ->createDuty('Kontaktdaten und Adressen kommen aus Firmen')
            ->createDuty('Mitarbeiter und Beziehungen kommen aus Personen')
            ->createDuty('Vordefinierte Schulform in Datenbank')
            ->createDuty('Zugehörige Schulform wählen');
        $Feature->createTask('Schulträger')
            ->createDuty('Eigenen Träger aus Firmen wählen')
            ->createDuty('Kontaktdaten und Adressen kommen aus Firmen')
            ->createDuty('Mitarbeiter und Beziehungen kommen aus Personen');
        $Feature->createTask('Förderverein')
            ->createDuty('Eigenen Verein aus Firmen wählen')
            ->createDuty('Kontaktdaten und Adressen kommen aus Firmen')
            ->createDuty('Mitarbeiter und Beziehungen kommen aus Personen');

        // Einstellungen
        $Feature = $Category->createFeature('Benutzer');
        $Feature->createTask('Hardware-Schlüssel')
            ->createDuty('YubiKey hinzufügen', true)
            ->createDuty('YubiKey entfernen', true);
        $Feature->createTask('Benutzerkonten')
            ->createDuty('Zugangsdaten', false)
            ->createDuty('Berechtigungsstufen', false)
            ->createDuty('Authentifizierungsart', false)
            ->createDuty('Person', false)
            ->createDuty('Benutzerkonten anlegen', false)
            ->createDuty('Benutzerkonten löschen', true);
        $Feature->createTask('Mein Benutzerkonto')
            ->createDuty('Informationen anzeigen', false)
            ->createDuty('Passwort ändern', true)
            ->createDuty('Mandant ändern (Administrator)', true);

        // Demoversion
        $Feature = $Category->createFeature('Demoversion');
        $Feature->createTask('Datenbank')
            ->createDuty('Demodaten neu', false)
            ->createDuty('Benutzerzugänge übernehmen', false);
        $Feature->createTask('Programmcode')
            ->createDuty('Programmcode veröffentlichen', false);

    }

    /**
     * Version 0.9.0
     * To be released Oktober
     */
    private function versionMajor0Minor9Patch0()
    {

        $Release = $this->Roadmap->createRelease('0.9.0', 'Demoversion (Ziel Oktober)');

        // Personenverwaltung
        $Category = $Release->createCategory('Personenverwaltung');
        $Feature = $Category->createFeature('Dashboards');
        $Feature->createTask('Board: People')
            ->createDuty('Klären welcher Inhalt enthalten sein soll');

        $Feature = $Category->createFeature('Person');
        $Feature->createTask('Informationen (Metadaten)')
            ->createDuty('Schülerakte (Ersteinschulung: Abgebende Kita)');
        $Feature->createTask('Beziehungen')
            ->createDuty('Anzeige von Adress- und Kontaktdaten')
            ->createDuty('Frei definierbare Beziehungstypen');

        // Firmenverwaltung
        $Category = $Release->createCategory('Firmenverwaltung');
        $Feature = $Category->createFeature('Dashboards');
        $Feature->createTask('Board: Corporation')
            ->createDuty('Klären welcher Inhalt enthalten sein soll');

        // Bildung
        $Category = $Release->createCategory('Bildung');

        $Feature = $Category->createFeature('Dashboards');
        $Feature->createTask('Board: Education')
            ->createDuty('Klären welcher Inhalt enthalten sein soll');
        $Feature->createTask('Board: Lesson')
            ->createDuty('Klären welcher Inhalt enthalten sein soll');

        $Feature = $Category->createFeature('Unterricht');
        $Feature->createTask('Fächer')
            ->createDuty('Fach hinzufügen')
            ->createDuty('Fach bearbeiten')
            ->createDuty('Fach löschen');
        $Feature->createTask('Fach-Kategorie')
            ->createDuty('Kategorie hinzufügen')
            ->createDuty('Kategorie bearbeiten')
            ->createDuty('Kategorie löschen');
        $Feature->createTask('Fach-Kategorie zuweisen')
            ->createDuty('Verknüpfung hinzufügen')
            ->createDuty('Verknüpfung löschen');
        $Feature->createTask('Kategorie-Gruppe zuweisen')
            ->createDuty('Verknüpfung hinzufügen')
            ->createDuty('Verknüpfung löschen');
        $Feature->createTask('Schuljahr')
            ->createDuty('Schuljahr hinzufügen')
            ->createDuty('Zeitraum hinzufügen')
            ->createDuty('Abschnitt verknüpfen');
        $Feature->createTask('Klassenstufe')
            ->createDuty('Klassenstufe hinzufügen')
            ->createDuty('Klassenstufe löschen')
            ->createDuty('Schulform verknüpfen');
        $Feature->createTask('Klassen')
            ->createDuty('Klasse hinzufügen')
            ->createDuty('Klasse löschen')
            ->createDuty('Schuljahr verknüpfen')
            ->createDuty('Klassenstufe verknüpfen')
            ->createDuty('Fachklasse verknüpfen')
            ->createDuty('Klassen-Schüler verknüpfen')
            ->createDuty('Klassen-Lehrer verknüpfen')
            ->createDuty('Fach-Schüler verknüpfen')
            ->createDuty('Fach-Lehrer verknüpfen');
    }

    /**
     * Version 1.0.0
     * To be released November
     */
    private function versionMajor1Minor0Patch0()
    {

        $Release = $this->Roadmap->createRelease('1.0.0', 'KREDA (Ziel November)');

        // Personenverwaltung
        $Category = $Release->createCategory('Personenverwaltung');
        $Category->createFeature('Person löschen');

        // Firmenverwaltung
        $Category = $Release->createCategory('Firmenverwaltung');
        $Category->createFeature('Firma löschen');

        // Fehlerbehebung
        $Category = $Release->createCategory('Fehlerkorrekturen');
        $Feature = $Category->createFeature('Cache System');
        $Task = $Feature->createTask('MemcacheD');
        $Task->createDuty('Konfiguration');
        $Task->createDuty('Performance');
        $Task = $Feature->createTask('ApcU');
        $Task->createDuty('Konfiguration');
        $Feature = $Category->createFeature('Database System');
        $Task = $Feature->createTask('MySql');
        $Task->createDuty('Konfiguration');
        $Task->createDuty('Performance');
        $Feature = $Category->createFeature('Code Style');
        $Feature->createTask('PSR-1/PSR2');
        $Category->createFeature('Code Performance');

        // Auswertungen
        $Release->createCategory('Auswertungen')
            ->createFeature('Festdefinierte Auswertungen')
            ->createTask('für ESZC');
    }

    /**
     * Version 1.1.0
     * To be released Q1 2016
     */
    private function versionMajor1Minor1Patch0()
    {

        $Release = $this->Roadmap->createRelease('1.1.0', 'KREDA (Ziel Q1 2016)');

        // Bildung
        $Category = $Release->createCategory('Bildung');
        $Feature = $Category->createFeature('Zensuren');
        $Feature->createTask('Elektronisches Notenbuch');
        $Feature->createTask('Zeugnisdruck (vorerst feste Zeugnislayouts)');
    }

    /**
     * Version 1.2.0
     * To be released Q1 2016
     */
    private function versionMajor1Minor2Patch0()
    {

        $Release = $this->Roadmap->createRelease('1.2.0', 'KREDA (Ziel Q1 2016)');

        $Category = $Release->createCategory('Fakturierung');
        $Category->createFeature('Leistungen');
        $Category->createFeature('Buchhaltung');
        $Category->createFeature('Rechnungswesen');

        $Category = $Release->createCategory('Auswertungen');
        $Category->createFeature('Statistik / Berichte (ähnlich Fuxschool)');
        $Category->createFeature('Kamenz-Bericht');
    }

    /**
     * Version 1.x.x
     * To be released 2016
     */
    private function poolMajor1MinorXPatchX()
    {

        $Release = $this->Roadmap->createRelease('1.x.x', 'KREDA (Ziel 2016)');

        $Category = $Release->createCategory('Datentransfer', 'Austausch mit anderen Programmen');

        $Feature = $Category->createFeature('Indiware', 'Import von Daten');

        $Feature->createTask('Import von Lehraufträgen', 'Zusätzlich zur Vergabe in Kreda')
            ->createDuty('Analyse von Indiware')
            ->createDuty('Analyse von Exportfunktion')
            ->createDuty('Analyse der Daten');

        $Feature = $Category->createFeature('Fuxschool', 'Import von Daten');

        $Feature->createTask('Import von Personendaten', 'Zusätzlich zur Eingabe in Kreda')
            ->createDuty('Analyse von Fuxschool')
            ->createDuty('Analyse der Exportfunktion')
            ->createDuty('Analyse der Daten');

        $Category = $Release->createCategory('Auswertungen');
        $Feature = $Category->createFeature('Dynamische Auswertungen');
        $Feature->createTask('Report-Designer');

        // Bildung
        $Category = $Release->createCategory('Bildung');
        $Feature = $Category->createFeature('Zensuren');
        $Feature->createTask('Zeugnisdruck')
            ->createDuty('Layout-Designer');
        $Feature = $Category->createFeature('Unterricht');
        $Feature->createTask('Klassen')
            ->createDuty('Sitzplan');

        // Diverses
        $Category = $Release->createCategory('Diverses');
        $Feature = $Category->createFeature('Letzte Änderung der Daten anzeigen');
        $Feature->createTask('Person')
            ->createDuty('Metadaten');

    }

    /**
     * @return Stage
     */
    public function frontendMap()
    {

        return $this->Roadmap->getStage();
    }
}
