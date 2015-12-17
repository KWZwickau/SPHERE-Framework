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
    public static function Patch0(RoadmapExtension $Roadmap)
    {

        $Release = $Roadmap->createRelease('1.0.0', 'KREDA (Ziel Q4 2015)');

        // Hilfe
        $Category = $Release->createCategory('Hilfe');
        $Feature = $Category->createFeature('Feedback- & Ticket-Erstellung');
        $Task = $Feature->createTask('Oberfläche erstellen', '', false);
        $Task = $Feature->createTask('Mail-Server einrichten', '', false);
        $Task = $Feature->createTask('Anbindung an Ticket-/Support-System');

        // Bildung
        $Category = $Release->createCategory('Bildung');
        $Feature = $Category->createFeature('Zensuren');
        $Task = $Feature->createTask('Elektronisches Notenbuch')
            ->createDuty('Basisfunktionalitäten', true)
            ->createDuty('Eingabe/Ausgabe von Zensuren', true)
            ->createDuty('Anlegen & Auswahl von Leistungsüberprüfungen', true);

        // Einstellungen
        $Category = $Release->createCategory('Einstellungen');

        $Feature = $Category->createFeature('Benutzer');
        $Feature->createTask('Benutzerkonten')
            ->createDuty('Benutzerkonten bearbeiten', false);
        $Feature->createTask('Mein Benutzerkonto')
            ->createDuty('Informationen anzeigen (Vervollständigen)', false);

        // Auswertungen
        $Release->createCategory('Auswertungen')
            ->createFeature('Festdefinierte Auswertungen')
            ->createTask('für ESZC', '', true);
    }
}
