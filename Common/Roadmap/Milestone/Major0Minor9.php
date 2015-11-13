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
            ->createDuty('Schülerakte (Ersteinschulung: Abgebende Kita)', true);
        $Feature->createTask('Beziehungen')
            ->createDuty('Trennung von Beziehungstypen (Personenbeziehungen)', true);
//            ->createDuty('Frei definierbare Beziehungstypen', false);

        // Firmenverwaltung
        $Category = $Release->createCategory('Firmenverwaltung');
        $Feature = $Category->createFeature('Firma');
        $Feature->createTask('Beziehungen')
            ->createDuty('Trennung von Beziehungstypen (Firmenbeziehungen)', true);
//            ->createDuty('Frei definierbare Beziehungstypen', false);
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
            ->createDuty('Fach bearbeiten', true)
            ->createDuty('Fach löschen', false);
        $Feature->createTask('Fach-Kategorie')
            ->createDuty('Kategorie hinzufügen', true)
            ->createDuty('Kategorie bearbeiten', true)
            ->createDuty('Kategorie löschen', false);
        $Feature->createTask('Fach-Kategorie zuweisen')
            ->createDuty('Verknüpfung hinzufügen', true)
            ->createDuty('Verknüpfung löschen', true);
        $Feature->createTask('Kategorie-Gruppe zuweisen')
            ->createDuty('Verknüpfung hinzufügen', true)
            ->createDuty('Verknüpfung löschen', true);
        $Feature->createTask('Schuljahr')
            ->createDuty('Schuljahr hinzufügen', true)
            ->createDuty('Zeitraum hinzufügen', true)
            ->createDuty('Abschnitt verknüpfen', true);
        $Feature->createTask('Klassenstufe')
            ->createDuty('Klassenstufe hinzufügen', true)
            ->createDuty('Klassenstufe löschen', false)
            ->createDuty('Schulform verknüpfen', false);
        $Feature->createTask('Klassen')
            ->createDuty('Klasse hinzufügen', true)
            ->createDuty('Klasse löschen', false)
            ->createDuty('Schuljahr verknüpfen', true)
            ->createDuty('Klassenstufe verknüpfen', true)
            ->createDuty('Fachklasse verknüpfen', false)
            ->createDuty('Klassen-Schüler verknüpfen')
            ->createDuty('Klassen-Lehrer verknüpfen')
            ->createDuty('Fach-Schüler verknüpfen')
            ->createDuty('Fach-Lehrer verknüpfen');
    }
}
