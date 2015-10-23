<?php
namespace SPHERE\Common\Roadmap\Milestone;

use SPHERE\System\Extension\Repository\Roadmap as RoadmapExtension;

/**
 * Class Major0Minor9
 *
 * @package SPHERE\Common\Roadmap\Milestone
 */
class Major0Minor9
{

    /**
     * Version 0.9.0
     * To be released Oktober
     *
     * @param RoadmapExtension $Roadmap
     */
    public static function Patch0(RoadmapExtension $Roadmap)
    {

        $Release = $Roadmap->createRelease('0.9.0', 'Demoversion (Ziel Oktober)');

        // Personenverwaltung
        $Category = $Release->createCategory('Personenverwaltung');

        $Feature = $Category->createFeature('Person');
        $Feature->createTask('Informationen (Metadaten)')
            ->createDuty('Schülerakte (Ersteinschulung: Abgebende Kita)');
        $Feature->createTask('Beziehungen')
            ->createDuty('Anzeige von Adress- und Kontaktdaten')
            ->createDuty('Trennung von Beziehungstypen (Personenbeziehungen)')
            ->createDuty('Frei definierbare Beziehungstypen');

        // Firmenverwaltung
        $Category = $Release->createCategory('Firmenverwaltung');
        $Feature = $Category->createFeature('Firma');
        $Feature->createTask('Beziehungen')
            ->createDuty('Trennung von Beziehungstypen (Firmenbeziehungen)')
            ->createDuty('Frei definierbare Beziehungstypen');
    }

    /**
     * Version 0.9.1
     * To be released Oktober
     *
     * @param RoadmapExtension $Roadmap
     */
    public static function Patch1(RoadmapExtension $Roadmap)
    {

        $Release = $Roadmap->createRelease('0.9.1', 'Demoversion (Ziel Oktober)');

        // Bildung
        $Category = $Release->createCategory('Bildung');

        $Feature = $Category->createFeature('Unterricht');
        $Feature->createTask('Fächer')
            ->createDuty('Fach hinzufügen', true)
            ->createDuty('Fach bearbeiten', false)
            ->createDuty('Fach löschen', false);
        $Feature->createTask('Fach-Kategorie')
            ->createDuty('Kategorie hinzufügen', true)
            ->createDuty('Kategorie bearbeiten', false)
            ->createDuty('Kategorie löschen', false);
        $Feature->createTask('Fach-Kategorie zuweisen')
            ->createDuty('Verknüpfung hinzufügen')
            ->createDuty('Verknüpfung löschen');
        $Feature->createTask('Kategorie-Gruppe zuweisen')
            ->createDuty('Verknüpfung hinzufügen')
            ->createDuty('Verknüpfung löschen');
        $Feature->createTask('Schuljahr')
            ->createDuty('Schuljahr hinzufügen')
            ->createDuty('Zeitraum hinzufügen')
            ->createDuty('Abschnitt verknüpfen');
        $Feature->createTask('Klassenstufe')
            ->createDuty('Klassenstufe hinzufügen')
            ->createDuty('Klassenstufe löschen')
            ->createDuty('Schulform verknüpfen');
        $Feature->createTask('Klassen')
            ->createDuty('Klasse hinzufügen')
            ->createDuty('Klasse löschen')
            ->createDuty('Schuljahr verknüpfen')
            ->createDuty('Klassenstufe verknüpfen')
            ->createDuty('Fachklasse verknüpfen')
            ->createDuty('Klassen-Schüler verknüpfen')
            ->createDuty('Klassen-Lehrer verknüpfen')
            ->createDuty('Fach-Schüler verknüpfen')
            ->createDuty('Fach-Lehrer verknüpfen');
    }
}
