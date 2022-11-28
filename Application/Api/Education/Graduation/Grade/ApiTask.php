<?php

namespace SPHERE\Application\Api\Education\Graduation\Grade;

use DateTime;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Extension;

class ApiTask extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('loadViewTaskList');
        $Dispatcher->registerMethod('loadViewTaskEditContent');
        $Dispatcher->registerMethod('saveTaskEdit');
        $Dispatcher->registerMethod('loadTaskGradeTypes');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock(string $Content = '', string $Identifier = ''): BlockReceiver
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @param $YearId
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewTaskList($YearId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewTaskList',
        ));
        $ModalEmitter->setPostPayload(array(
            'YearId' => $YearId
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $YearId
     *
     * @return string
     */
    public function loadViewTaskList($YearId): string
    {
        return Grade::useFrontend()->loadViewTaskList($YearId);
    }

    /**
     * @param $YearId
     * @param $TaskId
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewTaskEditContent($YearId, $TaskId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewTaskEditContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'YearId' => $YearId,
            'TaskId' => $TaskId
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $YearId
     * @param $TaskId
     *
     * @return string
     */
    public function loadViewTaskEditContent($YearId, $TaskId): string
    {
        return Grade::useFrontend()->getTaskEdit(
            Grade::useFrontend()->formTask($YearId, $TaskId, true),
            $YearId, $TaskId
        );
    }

    /**
     * @param $YearId
     * @param null $TaskId
     *
     * @return Pipeline
     */
    public static function pipelineSaveTaskEdit($YearId, $TaskId = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveTaskEdit'
        ));
        $ModalEmitter->setPostPayload(array(
            'YearId' => $YearId,
            'TaskId' => $TaskId
        ));
        $ModalEmitter->setLoadingMessage("Wird bearbeitet");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $YearId
     * @param $TaskId
     * @param $Data
     *
     * @return string
     */
    public function saveTaskEdit($YearId, $TaskId, $Data): string
    {
        if (!($tblYear = Term::useService()->getYearById($YearId))) {
            return (new Danger("Schuljahr wurde nicht gefunden!", new Exclamation()));
        }

        if (($form = Grade::useService()->checkFormTask($Data, $YearId, $TaskId))) {
            // display Errors on form
            return Grade::useFrontend()->getTaskEdit($form, $YearId, $TaskId);
        }

        $isBehaviorType = isset($Data['Type']) && $Data['Type'] == 2;
        $name = $Data['Name'];
        $date = $this->getDateTime('Date', $Data);
        $fromDate = $this->getDateTime('FromDate', $Data);
        $toDate = $this->getDateTime('ToDate', $Data);
        $isAllYears = isset($Data ['IsAllYears']);
        $tblScoreType = null;
        if (isset($Data['ScoreType']) && ($temp = Grade::useService()->getScoreTypeById($Data['ScoreType']))) {
            $tblScoreType = $temp;
        }

        if (($tblTask = Grade::useService()->getTaskById($TaskId))) {
            Grade::useService()->updateTask($tblTask, $name, $date, $fromDate, $toDate, $isAllYears, $tblScoreType);

            $createList = array();
            $removeList = array();
            // todo kurse
//            if (($tblDivisionCourseList = $tblTask->getDivisionCourses())) {
//                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
//                    // löschen
//                    if (!isset($Data['DivisionCourses'][$tblDivisionCourse->getId()])) {
//                        $removeList[] = Grade::useService()->getTestCourseLinkBy($tblTask, $tblDivisionCourse);
//                    }
//                }
//            } else {
//                $tblDivisionCourseList = array();
//            }
//
//            // neu
//            if (isset($Data['DivisionCourses'])) {
//                foreach ($Data['DivisionCourses'] as $divisionCourseId => $value) {
//                    if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($divisionCourseId))
//                        && !isset($tblDivisionCourseList[$divisionCourseId])
//                    ) {
//                        $createList[] = new TblTestCourseLink($tblTask, $tblDivisionCourse);
//                    }
//                }
//            }
//
//            if (!empty($createList)) {
//                Grade::useService()->createEntityListBulk($createList);
//            }
//            if (!empty($removeList)) {
//                Grade::useService()->deleteEntityListBulk($removeList);
//            }
        } else {
            if (($tblTaskNew = Grade::useService()->createTask(
                $tblYear, $isBehaviorType, $name, $date, $fromDate, $toDate, $isAllYears, $tblScoreType
            ))) {
                // todo Kurse hinzufügen
//                if (isset($Data['DivisionCourses'])) {
//                    $createList = array();
//                    foreach ($Data['DivisionCourses'] as $divisionCourseId => $value) {
//                        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($divisionCourseId))) {
//                            $createList[] = new TblTestCourseLink($tblTestNew, $tblDivisionCourse);
//                        }
//                    }
//
//                    Grade::useService()->createEntityListBulk($createList);
//                }

                // todo Zensuren-Typ bei Kopfnotenauftrag
            }
        }

        return new Success("Notenauftrag wurde erfolgreich gespeichert.")
            . self::pipelineLoadViewTaskList($YearId);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadTaskGradeTypes(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'TaskGradeTypesContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadTaskGradeTypes',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     *
     * @return string
     */
    public function loadTaskGradeTypes($Data = null): string
    {
        return Grade::useFrontend()->loadTaskGradeTypes($Data);
    }

    /**
     * @param string $Identifier
     * @param $Data
     *
     * @return DateTime|null
     */
    private function getDateTime(string $Identifier, $Data): ?DateTime
    {
        if (isset($Data[$Identifier]) && $Data[$Identifier]) {
            return new DateTime($Data[$Identifier]);
        }

        return  null;
    }
}