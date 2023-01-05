<?php
namespace SPHERE\Application\Api\Education\Graduation\Evaluation;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation as EvaluationApp;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;

/**
 * Class Evaluation
 *
 * @package SPHERE\Application\Api\Education\Graduation\Evaluation
 */
class Evaluation implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/TaskGrades/Download',
            __CLASS__ . '::downloadTaskGrades'
        ));
    }

    public static function useService()
    {
    }

    public static function useFrontend()
    {
    }

    public static function downloadTaskGrades($Id = null, $DivisionId = null): string
    {

        // Klassenobjekt abrufen
        $tblTask = EvaluationApp::useService()->getTaskById($Id);
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblTask && $tblDivision) {
            // Liste von Personenobjekten abrufen
            // Notenübersicht generieren
            list($tableHeader, $tableContent) = EvaluationApp::useService()->getStudentGrades($tblTask, $tblDivision);

            if ($tableHeader && $tableContent) {
                // Excel-Datei mit Notenübersicht erstellen
                $fileLocation = EvaluationApp::useService()->generateTaskGradesExcel($tableHeader, $tableContent);
                // Download-Link für Excel-Datei erstellen
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Notenübersicht " . date("Y-m-d H:i:s") . ".xlsx")->__toString();
            }
        }
        return 'Keine Daten vorhanden';
    }
}

/*
Debugger::screenDump();
exit;
*/