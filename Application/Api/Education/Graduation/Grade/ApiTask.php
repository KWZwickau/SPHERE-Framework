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

        $Dispatcher->registerMethod('loadViewTaskDelete');
        $Dispatcher->registerMethod('saveTaskDelete');

        $Dispatcher->registerMethod('loadViewTaskGradeContent');
        $Dispatcher->registerMethod('loadDivisionCourseTaskGradeContent');

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
     * @param null $Data
     *
     * @return string
     */
    public function loadViewTaskList($YearId, $Data = null): string
    {
        if (isset($Data["Year"]) && ($tblYear = Term::useService()->getYearById($Data["Year"]))) {
            return Grade::useFrontend()->loadViewTaskList($tblYear->getId());
        } else {
            return Grade::useFrontend()->loadViewTaskList($YearId);
        }
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

            // Kurse updaten
            Grade::useService()->updateTaskCourseLinks($tblTask, $Data);

            // Zensuren-Typen hinzufügen bei Kopfnotenauftrag
            if ($tblTask->getIsTypeBehavior()) {
                Grade::useService()->updateTaskGradeTypeLinks($tblTask, $Data);
            }
        } else {
            if (($tblTaskNew = Grade::useService()->createTask(
                $tblYear, $isBehaviorType, $name, $date, $fromDate, $toDate, $isAllYears, $tblScoreType
            ))) {
                // Kurse hinzufügen
                Grade::useService()->createTaskCourseLinks($tblTaskNew, $Data);

                // Zensuren-Typen hinzufügen bei Kopfnotenauftrag
                if ($tblTaskNew->getIsTypeBehavior()) {
                    Grade::useService()->createTaskGradeTypeLinks($tblTaskNew, $Data);
                }
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

    /**
     * @param $TaskId

     * @return Pipeline
     */
    public static function pipelineLoadViewTaskDelete($TaskId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewTaskDelete',
        ));
        $ModalEmitter->setPostPayload(array(
            'TaskId' => $TaskId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $TaskId
     *
     * @return string
     */
    public function loadViewTaskDelete($TaskId): string
    {
        return Grade::useFrontend()->loadViewTaskDelete($TaskId);
    }

    /**
     * @param $TaskId
     *
     * @return Pipeline
     */
    public static function pipelineSaveTaskDelete($TaskId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveTaskDelete'
        ));
        $ModalEmitter->setPostPayload(array(
            'TaskId' => $TaskId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $TaskId
     *
     * @return string
     */
    public function saveTaskDelete($TaskId): string
    {
        if (!($tblTask = Grade::useService()->getTaskById($TaskId))) {
            return new Danger('Der Notenauftrag wurde nicht gefunden', new Exclamation());
        }

        $YearId = ($tblYear = $tblTask->getServiceTblYear()) ? $tblYear->getId() : 0;
        if (Grade::useService()->deleteTask($tblTask)) {
            return new Success('Der Notenauftrag wurde erfolgreich gelöscht.')
                . self::pipelineLoadViewTaskList($YearId);
        } else {
            return new Danger('Der Notenauftrag konnte nicht gelöscht werden.');
        }
    }

    /**
     * @param $TaskId
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewTaskGradeContent($TaskId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewTaskGradeContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'TaskId' => $TaskId
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $TaskId
     *
     * @return string
     */
    public function loadViewTaskGradeContent($TaskId): string
    {
        return Grade::useFrontend()->getViewTaskGradeContent($TaskId);
    }

    /**
     * @param $TaskId
     *
     * @return Pipeline
     */
    public static function pipelineLoadDivisionCourseTaskGradeContent($TaskId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DivisionCourseTaskGradeContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDivisionCourseTaskGradeContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'TaskId' => $TaskId
        ));
        $ModalEmitter->setLoadingMessage("Kurs-Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $TaskId
     * @param null $Data
     *
     * @return string
     */
    public function loadDivisionCourseTaskGradeContent($TaskId, $Data = null): string
    {
        return Grade::useFrontend()->loadDivisionCourseTaskGradeContent($TaskId, $Data);
    }
}