<?php
namespace SPHERE\Common\Roadmap\Milestone;

use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\System\Extension\Repository\Roadmap as RoadmapExtension;

/**
 * Class Major0Minor8
 *
 * @package SPHERE\Common\Roadmap\Milestone
 */
class Major0Minor8
{

    /**
     * Version 0.8.2
     * To be released KW41/42
     *
     * @param RoadmapExtension $Roadmap
     */
    public static function Patch2(RoadmapExtension $Roadmap)
    {

        $Release = $Roadmap->createRelease('0.8.2', 'Demoversion (Ziel KW41/42)');

        // Personenverwaltung
        $Category = $Release->createCategory('Personenverwaltung');

        $Feature = $Category->createFeature('Person');
        $Feature->createTask('Informationen (Metadaten)')
            ->createDuty('Schülerakte (Speichern)', false)
            ->createDuty('Personendaten (Vorbelegtes Autocomplete, Konfession)', true);
    }

    /**
     * Version 0.8.1
     * To be released KW41
     *
     * @param RoadmapExtension $Roadmap
     */
    public static function Patch1(RoadmapExtension $Roadmap)
    {

        $Release = $Roadmap->createRelease('0.8.1', 'Demoversion (Ziel KW41)');

        // Personenverwaltung
        $Category = $Release->createCategory('Personenverwaltung');

        $Feature = $Category->createFeature('Person');
        $Feature->createTask('Informationen (Metadaten)')
            ->createDuty('Schülerakte (Anpassungen: Feedback von 0.8.0)', true);

        // Einstellungen
        $Category = $Release->createCategory('Einstellungen');

        $Feature = $Category->createFeature('Benutzer');
        $Feature->createTask('Benutzerkonten')
            ->createDuty('Benutzerkonten bearbeiten', false);
        $Feature->createTask('Mein Benutzerkonto')
            ->createDuty('Informationen anzeigen (Vervollständigen)', false);

        $Feature = $Category->createFeature('Mandant',
            new External('siehe EGE', 'http://www.ege-annaberg.de/node/416'));
        $Feature->createTask('Schulen')
            ->createDuty('Eigene Schulen aus Firmen wählen', true)
            ->createDuty('Kontaktdaten und Adressen kommen aus Firmen', true)
            ->createDuty('Mitarbeiter und Beziehungen kommen aus Personen', true)
            ->createDuty('Vordefinierte Schulform in Datenbank', true)
            ->createDuty('Zugehörige Schulform wählen', true);
        $Feature->createTask('Schulträger')
            ->createDuty('Eigenen Träger aus Firmen wählen', true)
            ->createDuty('Kontaktdaten und Adressen kommen aus Firmen', true)
            ->createDuty('Mitarbeiter und Beziehungen kommen aus Personen', true);
        $Feature->createTask('Förderverein')
            ->createDuty('Eigenen Verein aus Firmen wählen', true)
            ->createDuty('Kontaktdaten und Adressen kommen aus Firmen', true)
            ->createDuty('Mitarbeiter und Beziehungen kommen aus Personen', true);
    }

    /**
     * Version 0.8.0.1
     * To be released KW41
     *
     * @param RoadmapExtension $Roadmap
     */
    public static function Patch0Fix1(RoadmapExtension $Roadmap)
    {

        $Release = $Roadmap->createRelease('0.8.0.1', 'Demoversion (Ziel KW41)');

        // Plattform
        $Category = $Release->createCategory('Plattform');
        $Feature = $Category->createFeature('Rollenverwaltung');
        $Feature->createTask('Trennung interne/externe Rollen')
            ->createDuty('Entity anpassen', true)
            ->createDuty('Schema anpassen', true)
            ->createDuty('System: Verwaltung anpassen', true)
            ->createDuty('Mandant: Einstellungen anpassen', true);

        // Personenverwaltung
        $Category = $Release->createCategory('Personenverwaltung');

        $Feature = $Category->createFeature('Person');
        $Feature->createTask('Informationen (Metadaten)')
            ->createDuty('Personendaten (Autocomplete, Staatsangehörigkeit)', true)
            ->createDuty('Personendaten (Autocomplete, Konfession)', true);

        // Einstellungen
        $Category = $Release->createCategory('Einstellungen');

        $Feature = $Category->createFeature('Benutzer');
        $Feature->createTask('Mein Benutzerkonto')
            ->createDuty('Passwort ändern', true);
    }

    /**
     * Version 0.8.0
     * To be released 28.09.2015
     *
     * @param RoadmapExtension $Roadmap
     */
    public static function Patch0(RoadmapExtension $Roadmap)
    {

        $Release = $Roadmap->createRelease('0.8.0', 'Demoversion (Ziel 28.09.2015)');

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

        $Feature->createTask('Frei definierbare Gruppen')
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
            ->createDuty('Schülerakte (Ansicht)', true)
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
        $Feature = $Category->createFeature('Benutzer');
        $Feature->createTask('Hardware-Schlüssel')
            ->createDuty('YubiKey hinzufügen', true)
            ->createDuty('YubiKey entfernen', true);
        $Feature->createTask('Benutzerkonten')
            ->createDuty('Zugangsdaten', true)
            ->createDuty('Berechtigungsstufen', true)
            ->createDuty('Authentifizierungstyp', true)
            ->createDuty('Hardware-Schlüssel', true)
            ->createDuty('Person wählen', true)
            ->createDuty('Benutzerkonten anlegen', true)
            ->createDuty('Benutzerkonten löschen', true);
        $Feature->createTask('Mein Benutzerkonto')
            ->createDuty('Informationen anzeigen (Teilweise)', true)
            ->createDuty('Passwort ändern', true)
            ->createDuty('Mandant ändern (Administrator)', true);

        // Demoversion
        $Category = $Release->createCategory('Demoversion');
        $Feature = $Category->createFeature('Veröffentlichen');
        $Feature->createTask('Datenbank')
            ->createDuty('Berechtigungskonzept erstellen / übernehmen', true)
            ->createDuty('Benutzerzugänge übernehmen', true);
    }
}
