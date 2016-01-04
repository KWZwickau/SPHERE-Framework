<?php
namespace SPHERE\Common\Roadmap\Milestone;

use SPHERE\System\Extension\Repository\Roadmap as RoadmapExtension;

/**
 * Class Major1Minor3
 *
 * @package SPHERE\Common\Roadmap\Milestone
 */
class Major1Minor3
{

    /**
     * Version 1.3.0
     * To be released Q2 2016
     *
     * @param RoadmapExtension $Roadmap
     */
    public static function definePatch0(RoadmapExtension $Roadmap)
    {

        $Release = $Roadmap->createRelease('1.3.0', 'KREDA (Ziel Q2 2016)');

        // Optimierung
        $Category = $Release->createCategory('Optimierung');
        $Feature = $Category->createFeature('Optimierung der Eingabeoberflächen');
        $Feature->createTask('Design');
        $Feature->createTask('Bedienung');

        // Bildung
        $Category = $Release->createCategory('Bildung');
        $Feature = $Category->createFeature('Zensuren');
        $Feature->createTask('Zeugnisdruck')
            ->createDuty('Layout-Designer');

        // Fehlerbehebung
        $Category = $Release->createCategory('Fehlerkorrekturen');
        $Feature = $Category->createFeature('Code Style');
        $Feature->createTask('PSR-1/PSR2');
        $Category->createFeature('Code Performance', '', false);
    }
}
