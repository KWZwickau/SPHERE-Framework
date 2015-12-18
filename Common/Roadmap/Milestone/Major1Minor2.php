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

        // Bildung
        $Category = $Release->createCategory('Bildung');
        $Feature = $Category->createFeature('Zensuren');
        $Feature->createTask('Erweiterung der Funktionalitäten')
            ->createDuty('Berechnungsvorschriften für Notendurchschnitt', true)
            ->createDuty('Notenspiegel / Verteilung für Leistungsüberprüfung', false)
            ->createDuty('Hinterlegung von Aufgabenpunkten bei Leistungsüberprüfungen und automatische Wichtung für Notenbildung')
            ->createDuty('Auftragserteilung für Stichtags- & Kopfnoten-Ermittlung (Schulleiter)', false)
            ->createDuty('Zeugnisdruck mit Möglichkeit zur revisionssicheren Speicherung');
        $Feature->createTask('Zeugnisdruck (vorerst feste Zeugnislayouts)');
        $Feature->createTask('Schüler & Elternansicht', '', false);

        // Fakturierung
        $Category = $Release->createCategory('Fakturierung');
        $Category->createFeature('Leistungen', '', true);
        $Category->createFeature('Buchhaltung', '', false);
        $Category->createFeature('Rechnungswesen', '', false);

        // Auswertungen
        $Category = $Release->createCategory('Auswertungen');
        $Category->createFeature('Checklisten direkt in KREDA', '', true);
        $Feature = $Category->createFeature('Berichte (ähnlich Fuxschool)');
        $Feature->createTask('Berichte (ähnlich Fuxschool)', '', true);
        $Feature = $Category->createFeature('Statistik (ähnlich Fuxschool)');
        $Feature->createTask('Kamenz-Bericht');
    }
}
