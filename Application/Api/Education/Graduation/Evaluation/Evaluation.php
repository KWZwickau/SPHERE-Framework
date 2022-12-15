<?php
namespace SPHERE\Application\Api\Education\Graduation\Evaluation;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Reporting\Reporting;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation as EvaluationApp;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Reporting\Standard\Person\Person as ReportingPerson;
use SPHERE\Application\Reporting\Standard\Person\Service;
use SPHERE\Common\Frontend\IFrontendInterface;
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

    public static function downloadTaskGrades($DivisionId = null): string
    {

        // Content soll aus der funktion kommen
        $content = array(array('Name' => 'das ist der Name'), array('Name' => 'Zeile 2'));
        // Startpunkt muss im frontend nachgeschaut werden (könnte aber divisionId sien)
        if (// ($tblDivision = Division::useService()->getDivisionById($DivisionId))
//            && ($content = EvaluationApp::useService()->generateTaskGrades($tblDivision))          // TODO: erstell getTaskGradesContent    woher holt er denn die Daten max?
//            &&
        ($fileLocation = EvaluationApp::useService()->generateTaskGradesExcel($content))    // TODO: erstell createTaskGradesContentExcel
        ) {
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Notenübersicht ".date("Y-m-d H:i:s").".xlsx")->__toString();
        }
        return 'Keine Daten vorhanden!';
    }
}

