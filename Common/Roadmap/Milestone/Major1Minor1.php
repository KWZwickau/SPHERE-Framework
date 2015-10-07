<?php
namespace SPHERE\Common\Roadmap\Milestone;

use SPHERE\System\Extension\Repository\Roadmap as RoadmapExtension;

/**
 * Class Major1Minor1
 *
 * @package SPHERE\Common\Roadmap\Milestone
 */
class Major1Minor1
{

    /**
     * Version 1.1.0
     * To be released Q1 2016
     *
     * @param RoadmapExtension $Roadmap
     */
    public static function Patch0(RoadmapExtension $Roadmap)
    {

        $Release = $Roadmap->createRelease('1.1.0', 'KREDA (Ziel Q1 2016)');

        // Bildung
        $Category = $Release->createCategory('Bildung');
        $Feature = $Category->createFeature('Zensuren');
        $Feature->createTask('Elektronisches Notenbuch');
        $Feature->createTask('Zeugnisdruck (vorerst feste Zeugnislayouts)');
    }
}
