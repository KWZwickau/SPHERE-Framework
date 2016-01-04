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
    public static function definePatch0(RoadmapExtension $Roadmap)
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
    public static function definePatch1(RoadmapExtension $Roadmap)
    {

        $Release = $Roadmap->createRelease('0.9.1', 'Demoversion (Ziel Oktober)');

        // Bildung
        $Category = $Release->createCategory('Bildung');

        $Feature = $Category->createFeature('Unterricht');
        $Feature->createTask('Fächer')
            ->createDuty('Fach hinzufügen', true)
            ->createDuty('Fach bearbeiten', true);
        $Feature->createTask('Fach-Kategorie')
            ->createDuty('Kategorie hinzufügen', true)
            ->createDuty('Kategorie bearbeiten', true);
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
            ->createDuty('Schulform verknüpfen', true);
        $Feature->createTask('Klassen')
            ->createDuty('Klasse hinzufügen', true)
            ->createDuty('Schuljahr verknüpfen', true)
            ->createDuty('Klassenstufe verknüpfen', true)
            ->createDuty('Fachklasse verknüpfen', true)
            ->createDuty('Klassen-Schüler verknüpfen', true)
            ->createDuty('Klassen-Lehrer verknüpfen', true)
            ->createDuty('Fach-Schüler verknüpfen', true)
            ->createDuty('Fach-Lehrer verknüpfen', true);
    }
}
