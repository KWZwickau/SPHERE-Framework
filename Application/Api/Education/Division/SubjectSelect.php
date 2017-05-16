<?php

namespace SPHERE\Application\Api\Education\Division;

use SPHERE\Application\Api\Response;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\Icon\Repository\HazardSign;
use SPHERE\Common\Frontend\Icon\Repository\Success;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

class SubjectSelect extends Extension implements IModuleInterface
{
    //ToDO Umbau aus der alten API-Variante
    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Select/Subject',
            __CLASS__.'::executeSelectSubject'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @param null|array $Direction
     * @param null|array $Data
     * @param null|array $Additional
     *
     * @return Response
     */
    public function executeSelectSubject($Direction = null, $Data = null, $Additional = null)
    {


        if ($Data && $Direction) {
            if (!isset($Data['Id']) || !isset($Data['Division'])) {
                return (new Response())->addError('Fehler!',
                    new HazardSign().' Die Zuweisung des Fachs konnte nicht aktualisiert werden.', 0);
            }
            $Id = $Data['Id'];
            $DivisionId = $Data['Division'];

            if ($Direction['From'] == 'TableAvailable') {
                $Remove = false;
            } else {
                $Remove = true;
            }

            $tblDivision = \SPHERE\Application\Education\Lesson\Division\Division::useService()->getDivisionById($DivisionId);
            $tblSubject = Subject::useService()->getSubjectById($Id);
            if ($tblSubject && $tblDivision) {
                if ($Remove) {
                    $tblDivisionSubject = \SPHERE\Application\Education\Lesson\Division\Division::useService()
                        ->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup($tblDivision, $tblSubject);
                    if ($tblDivisionSubject) {
                        \SPHERE\Application\Education\Lesson\Division\Division::useService()->removeDivisionSubject($tblDivisionSubject);
                    }
                } else {
                    \SPHERE\Application\Education\Lesson\Division\Division::useService()->addSubjectToDivision($tblDivision,
                        $tblSubject);
                }
            }

            return (new Response())->addData(new Success().' Die Zuweisung des Fachs wurde erfolgreich aktualisiert.');
        }
        return (new Response())->addError('Fehler!',
            new HazardSign().' Die Zuweisung des Fachs konnte nicht aktualisiert werden.', 0);
    }
}
