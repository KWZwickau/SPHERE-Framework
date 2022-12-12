<?php

namespace SPHERE\Application\Api\Education\Graduation\Grade;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreTypeSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Extension\Extension;

class ApiScoreType extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('loadScoreTypeSubjects');
        $Dispatcher->registerMethod('saveScoreTypeEdit');

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
     * @param $ScoreTypeId
     *
     * @return Pipeline
     */
    public static function pipelineLoadScoreTypeSubjects($ScoreTypeId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ScoreTypeSubjectsContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadScoreTypeSubjects',
        ));
        $ModalEmitter->setPostPayload(array(
            'ScoreTypeId' => $ScoreTypeId
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $ScoreTypeId
     * @param null $Data
     *
     * @return string
     */
    public function loadScoreTypeSubjects($ScoreTypeId = null, $Data = null): string
    {
        if (!($tblScoreType = Grade::useService()->getScoreTypeById($ScoreTypeId))) {
            return (new Danger("Bewertungssystem wurde nicht gefunden!", new Exclamation()));
        }

        return Grade::useFrontend()->loadScoreTypeSubjects($tblScoreType, $Data);
    }

    /**
     * @param $ScoreTypeId
     *
     * @return Pipeline
     */
    public static function pipelineSaveScoreTypeEdit($ScoreTypeId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ScoreTypeSubjectsContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveScoreTypeEdit'
        ));
        $ModalEmitter->setPostPayload(array(
            'ScoreTypeId' => $ScoreTypeId
        ));
        $ModalEmitter->setLoadingMessage("Wird bearbeitet");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ScoreTypeId
     * @param $Data
     *
     * @return string
     */
    public function saveScoreTypeEdit($ScoreTypeId, $Data): string
    {
        if (!($tblScoreType = Grade::useService()->getScoreTypeById($ScoreTypeId))) {
            return (new Danger("Bewertungssystem wurde nicht gefunden!", new Exclamation()));
        }
        if (!($tblSchoolType = Type::useService()->getTypeById($Data['SchoolType']))) {
            return (new Danger("Schulart wurde nicht gefunden!", new Exclamation()));
        }

        $createList = array();
        $updateList = array();
        $removeList = array();
        $keepList = array();

        if (($tblScoreTypeSubjectList = $tblScoreType->getScoreTypeSubjects($tblSchoolType))) {
            foreach ($tblScoreTypeSubjectList as $tblScoreTypeSubject) {
                if (($tblSubject = $tblScoreTypeSubject->getServiceTblSubject())) {
                    // löschen
                    if (!isset($Data['Subjects'][$tblScoreTypeSubject->getLevel()][$tblSubject->getId()])) {
                        $removeList[] = $tblScoreTypeSubject;
                    } else {
                        $keepList[$tblScoreTypeSubject->getLevel()][$tblSubject->getId()] = $tblScoreTypeSubject;
                    }
                }
            }
        }

        if (isset($Data['Subjects'])) {
            foreach ($Data['Subjects'] as $level => $subjectList) {
                foreach ($subjectList as $subjectId => $value) {
                    if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                        if (isset($keepList[$level][$subjectId])) {
                            continue;
                        // update
                        } elseif (($tblScoreTypeSubject = Grade::useService()->getScoreTypeSubjectBySchoolTypeAndLevelAndSubject($tblSchoolType, $level, $tblSubject))) {
                            $tblScoreTypeSubject->setTblScoreType($tblScoreType);
                            $updateList[] = $tblScoreTypeSubject;
                        // neu
                        } else {
                            $createList[] = new TblScoreTypeSubject($tblSchoolType, $level, $tblSubject, $tblScoreType);
                        }
                    }
                }
            }
        }

        if (!empty($createList)) {
            Grade::useService()->createEntityListBulk($createList);
        }
        if (!empty($updateList)) {
            Grade::useService()->updateEntityListBulk($updateList);
        }
        if (!empty($removeList)) {
            Grade::useService()->deleteEntityListBulk($removeList);
        }

        return new Success("Daten wurde erfolgreich gespeichert.")
            . new Redirect('/Education/Graduation/Grade/ScoreType', Redirect::TIMEOUT_SUCCESS);
    }
}