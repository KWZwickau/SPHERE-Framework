<?php
namespace SPHERE\Application;

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

        $Release = $this->Roadmap->createRelease('0.8.0', 'Demoversion (28.09.2015)');

        // Personenverwaltung
        $Category = $Release->createCategory('Personenverwaltung');

        $Category->createFeature('Dashboard');

        $Feature = $Category->createFeature('Suche');
        $Feature->createTask('Filterung über Gruppen', '', true);
        $Feature->createTask('Volltextsuche über Name', '', true);

        $Feature = $Category->createFeature('Gruppen');
        $Feature->createTask('Festdefinierte Gruppen')
            ->createDuty('Alle (Personendaten)', true)
            ->createDuty('Interessent', false)
            ->createDuty('Schüler', false)
            ->createDuty('Sorgeberechtigt');

        $Feature->createTask('Freidefinierbare Gruppen', false);

        $Feature = $Category->createFeature('Person');
        $Feature->createTask('Grunddaten', 'Personname und Gruppenzugehörigkeit', true);
        $Feature->createTask('Informationen')
            ->createDuty('Personendaten', true)
            ->createDuty('Interessent', true)
            ->createDuty('Schülerakte', false)
            ->createDuty('Sorgerechtdaten');
        $Feature->createTask('Adressdaten');
        $Feature->createTask('Kontaktdaten')
            ->createDuty('Telefonnummer')
            ->createDuty('E-Mail Adresse');
        $Feature->createTask('Beziehungen');

        // Firmenverwaltung
        $Category = $Release->createCategory('Firmenverwaltung');

        $Category->createFeature('Dashboard');

        $Feature = $Category->createFeature('Suche');
        $Feature->createTask('Filterung über Gruppen');
        $Feature->createTask('Volltextsuche über Name');

        $Feature = $Category->createFeature('Gruppen');
        $Feature->createTask('Festdefinierte Gruppen')
            ->createDuty('Alle', true)
            ->createDuty('Schulen', true);
        $Feature->createTask('Freidefinierbare Gruppen')
            ->createDuty('Gruppen hinzufügen', false)
            ->createDuty('Gruppen löschen');

        $Feature = $Category->createFeature('Firma');
        $Feature->createTask('Grunddaten', 'Firmenname und Gruppenzugehörigkeit', true);
        $Feature->createTask('Adressdaten');
        $Feature->createTask('Kontaktdaten')
            ->createDuty('Telefonnummer')
            ->createDuty('E-Mail Adresse');

        // Bildung
        $Category = $Release->createCategory('Bildung');

        $Feature = $Category->createFeature('Unterricht');
        $Feature->createTask('Fächer');

        // Einstellungen
        $Category = $Release->createCategory('Einstellungen');

        $Feature = $Category->createFeature('Mandant');
        $Feature->createTask('Schulen');
        $Feature->createTask('Schulträger');
        $Feature->createTask('Förderverein');

        $Feature = $Category->createFeature('Benutzer');
        $Feature->createTask('Hardware-Schlüssel')
            ->createDuty('Dem System YubiKeys hinzufügen', true)
            ->createDuty('Bestehende YubiKeys entfernen');
        $Feature->createTask('Benutzerkonten')
            ->createDuty('Zugangsdaten')
            ->createDuty('Berechtigungsstufen')
            ->createDuty('Authentifizierungsart');
    }

    /**
     * Version 0.9.0
     * To be released Oktober
     */
    private function versionMajor0Minor9Patch0()
    {

        $Release = $this->Roadmap->createRelease('0.9.0', 'Demoversion (Oktober)', null);
    }

    /**
     * Version 1.0.0
     * To be released November
     */
    private function versionMajor1Minor0Patch0()
    {

        $Release = $this->Roadmap->createRelease('1.0.0', 'KREDA (November)', null);
    }

    /**
     * Version 1.1.0
     * To be released Q1 2016
     */
    private function versionMajor1Minor1Patch0()
    {

        $Release = $this->Roadmap->createRelease('1.1.0', 'KREDA (Q1 2016)', null);
    }

    /**
     * Version 1.2.0
     * To be released Q1 2016
     */
    private function versionMajor1Minor2Patch0()
    {

        $Release = $this->Roadmap->createRelease('1.2.0', 'KREDA (Q1 2016)', null);
    }

    /**
     * Version 1.x.x
     * To be released 2016
     */
    private function poolMajor1MinorXPatchX()
    {

        $Release = $this->Roadmap->createRelease('1.x.x', 'KREDA (2016)');

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

    }

    /**
     * @return Stage
     */
    public function frontendMap()
    {

        return $this->Roadmap->getStage();
    }
}
