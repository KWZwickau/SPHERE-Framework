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
    public static function definePatch0(RoadmapExtension $Roadmap)
    {

        $Release = $Roadmap->createRelease('1.1.0', 'KREDA (Ziel Q1 2016)');

        // Fehlerbehebung
        $Category = $Release->createCategory('Fehlerkorrekturen');
        $Feature = $Category->createFeature('Cache System');
        $Task = $Feature->createTask('MemcacheD');
        $Task->createDuty('Konfiguration', true);
        $Task->createDuty('Performance', true);
        $Task = $Feature->createTask('ApcU');
        $Task->createDuty('Konfiguration', true);
        $Feature = $Category->createFeature('Database System');
        $Task = $Feature->createTask('MySql');
        $Task->createDuty('Konfiguration', true);
        $Task->createDuty('Performance', true);

        // Bildung
        $Category = $Release->createCategory('Bildung');

        $Feature = $Category->createFeature('Unterricht');
        $Feature->createTask('Fächer')
            ->createDuty('Fach löschen');
        $Feature->createTask('Fach-Kategorie')
            ->createDuty('Kategorie löschen');
        $Feature->createTask('Klassenstufe')
            ->createDuty('Klassenstufe löschen', false);
        $Feature->createTask('Klassen')
            ->createDuty('Klasse löschen', false);

        // Personenverwaltung
        $Category = $Release->createCategory('Personenverwaltung');
        $Category->createFeature('Person löschen');

        // Firmenverwaltung
        $Category = $Release->createCategory('Firmenverwaltung');
        $Category->createFeature('Firma löschen');

    }
}
