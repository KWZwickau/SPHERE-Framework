<?php
namespace SPHERE\Common\Roadmap\Milestone;

use SPHERE\System\Extension\Repository\Roadmap as RoadmapExtension;

/**
 * Class Major1Minor0
 *
 * @package SPHERE\Common\Roadmap\Milestone
 */
class Major1Minor0
{

    /**
     * Version 1.0.0
     * To be released November
     *
     * @param RoadmapExtension $Roadmap
     */
    public static function Patch0(RoadmapExtension $Roadmap)
    {

        $Release = $Roadmap->createRelease('1.0.0', 'KREDA (Ziel November)');

        // Bildung
        $Category = $Release->createCategory('Bildung');
        $Feature = $Category->createFeature('Zensuren');
        $Task = $Feature->createTask('Elektronisches Notenbuch')
            ->createDuty('Basisfunktionalitäten', false)
            ->createDuty('Eingabe/Ausgabe von Zensuren', false)
            ->createDuty('Anlegen & Auswahl von Leistungsüberprüfungen', true);

        // Auswertungen
        $Release->createCategory('Auswertungen')
            ->createFeature('Festdefinierte Auswertungen')
            ->createTask('für ESZC', '', true);
    }
}
