<?php
namespace SPHERE\Common\Roadmap;

use SPHERE\Common\Roadmap\Milestone\Version08;
use SPHERE\Common\Roadmap\Milestone\Version09;
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

        Version08::versionMajor0Minor8Patch0($this->Roadmap);
        Version08::versionMajor0Minor8Patch1($this->Roadmap);
        Version08::versionMajor0Minor8Patch2($this->Roadmap);

        Version09::versionMajor0Minor9Patch0($this->Roadmap);
        Version09::versionMajor0Minor9Patch1($this->Roadmap);

        $this->versionMajor1Minor0Patch0();
        $this->versionMajor1Minor1Patch0();
        $this->versionMajor1Minor2Patch0();

        $this->poolMajor1MinorXPatchX();
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
