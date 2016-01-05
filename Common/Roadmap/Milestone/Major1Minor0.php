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
     * To be released Q4 2015
     *
     * @param RoadmapExtension $Roadmap
     */
    public static function definePatch0(RoadmapExtension $Roadmap)
    {

        $Release = $Roadmap->createRelease('1.0.0', 'KREDA (Ziel Q4 2015)');

        // Hilfe
        $Category = $Release->createCategory('Hilfe');
        $Feature = $Category->createFeature('Feedback- & Ticket-Erstellung');
        $Feature->createTask('Oberfläche erstellen', '', true);
        $Feature->createTask('Mail-Server einrichten', '', true);
        $Feature->createTask('Anbindung an Ticket-/Support-System', '', true);

        // Bildung
        $Category = $Release->createCategory('Bildung');
        $Feature = $Category->createFeature('Zensuren');
        $Feature->createTask('Elektronisches Notenbuch')
            ->createDuty('Basisfunktionalitäten', true)
            ->createDuty('Eingabe/Ausgabe von Zensuren', true)
            ->createDuty('Anlegen & Auswahl von Leistungsüberprüfungen', true);

        // Einstellungen
        $Category = $Release->createCategory('Einstellungen');

        $Feature = $Category->createFeature('Benutzer');
        $Feature->createTask('Mein Benutzerkonto')
            ->createDuty('Informationen anzeigen (Vervollständigen)', true);

        // Auswertungen
        $Release->createCategory('Auswertungen')
            ->createFeature('Festdefinierte Auswertungen')
            ->createTask('für ESZC', '', true);
    }

    /**
     * Version 1.0.1
     * To be released Q1 2016
     *
     * @param RoadmapExtension $Roadmap
     */
    public static function definePatch1(RoadmapExtension $Roadmap)
    {

        $Release = $Roadmap->createRelease('1.0.1', 'KREDA (Ziel Q1 2016)');

        // Einstellungen
        $Category = $Release->createCategory('Einstellungen');

        $Feature = $Category->createFeature('Benutzer');
        $Feature->createTask('Benutzerkonten')
            ->createDuty('Benutzerkonten bearbeiten', true);
    }
}
