<?php
namespace SPHERE\Common\Roadmap;

use SPHERE\Common\Roadmap\Milestone\Major0Minor8;
use SPHERE\Common\Roadmap\Milestone\Major0Minor9;
use SPHERE\Common\Roadmap\Milestone\Major1Minor0;
use SPHERE\Common\Roadmap\Milestone\Major1Minor1;
use SPHERE\Common\Roadmap\Milestone\Major1Minor2;
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

        Major0Minor8::Patch0($this->Roadmap);
        Major0Minor8::Patch1($this->Roadmap);
        Major0Minor8::Patch2($this->Roadmap);

        Major0Minor9::Patch0($this->Roadmap);
        Major0Minor9::Patch1($this->Roadmap);

        Major1Minor0::Patch0($this->Roadmap);
        Major1Minor1::Patch0($this->Roadmap);
        Major1Minor2::Patch0($this->Roadmap);

        $this->poolMajor1MinorXPatchX();
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
