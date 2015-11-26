<?php
namespace SPHERE\Common\Roadmap\Milestone;

use SPHERE\System\Extension\Repository\Roadmap as RoadmapExtension;

/**
 * Class Major1Minor2
 *
 * @package SPHERE\Common\Roadmap\Milestone
 */
class Major1Minor2
{

    /**
     * Version 1.2.0
     * To be released Q1 2016
     *
     * @param RoadmapExtension $Roadmap
     */
    public static function Patch0(RoadmapExtension $Roadmap)
    {

        $Release = $Roadmap->createRelease('1.2.0', 'KREDA (Ziel Q1 2016)');

        $Category = $Release->createCategory('Fakturierung');
        $Category->createFeature('Leistungen', '', true);
        $Category->createFeature('Buchhaltung', '', false);
        $Category->createFeature('Rechnungswesen', '', false);

        $Category = $Release->createCategory('Auswertungen');
        $Category->createFeature('Statistik / Berichte (ähnlich Fuxschool)');
        $Category->createFeature('Kamenz-Bericht');
    }
}
