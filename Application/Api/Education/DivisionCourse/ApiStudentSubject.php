<?php

namespace SPHERE\Application\Api\Education\DivisionCourse;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Extension;

class ApiStudentSubject extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadStudentSubjectContent');

        // SekI
        $Dispatcher->registerMethod('editStudentSubjectContent');
        $Dispatcher->registerMethod('loadCheckSubjectsContent');
        $Dispatcher->registerMethod('saveStudentSubjectList');

        // SekII
        $Dispatcher->registerMethod('editStudentSubjectDivisionCourseContent');
        $Dispatcher->registerMethod('loadCheckSubjectDivisionCoursesContent');
        $Dispatcher->registerMethod('saveStudentSubjectDivisionCourseList');

        // SekII kopieren vom 1. HJ zum 2. HJ
        $Dispatcher->registerMethod('openCopySubjectDivisionCourseModal');
        $Dispatcher->registerMethod('saveCopySubjectDivisionCourseModal');

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
     * @return ModalReceiver
     */
    public static function receiverModal(): ModalReceiver
    {
        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReceiver');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose(): Pipeline
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineLoadStudentSubjectContent($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentSubjectContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentSubjectContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadStudentSubjectContent($DivisionCourseId): string
    {
        return DivisionCourse::useFrontend()->loadStudentSubjectContent($DivisionCourseId);
    }

    /**
     * @param $DivisionCourseId
     * @param null $SubjectId
     *
     * @return Pipeline
     */
    public static function pipelineEditStudentSubjectContent($DivisionCourseId, $SubjectId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentSubjectContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editStudentSubjectContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'SubjectId' => $SubjectId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     *
     * @return string
     */
    public function editStudentSubjectContent($DivisionCourseId, $SubjectId): string
    {
        return DivisionCourse::useFrontend()->editStudentSubjectContent($DivisionCourseId, $SubjectId);
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineLoadCheckSubjectsContent($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CheckSubjectsContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadCheckSubjectsContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param null $Data
     *
     * @return string
     */
    public function loadCheckSubjectsContent($DivisionCourseId, $Data = null): string
    {
        return DivisionCourse::useFrontend()->loadCheckSubjectsContent($DivisionCourseId, $Data);
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineSaveStudentSubjectList($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentSubjectContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveStudentSubjectList',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param null $Data
     *
     * @return string
     */
    public function saveStudentSubjectList($DivisionCourseId, $Data = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }

        if (DivisionCourse::useService()->createStudentSubjectList($tblDivisionCourse, $Data)) {
            return new Success('Die Schüler-Fächer wurden erfolgreich gespeichert.')
                . self::pipelineLoadStudentSubjectContent($DivisionCourseId);
        } else {
            return new Danger('Die Schüler-Fächer konnten nicht gespeichert werden.');
        }
    }

    /**
     * @param $DivisionCourseId
     * @param $Period
     * @param null $SubjectDivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineEditStudentSubjectDivisionCourseContent($DivisionCourseId, $Period, $SubjectDivisionCourseId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentSubjectContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editStudentSubjectDivisionCourseContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Period' => $Period,
            'SubjectDivisionCourseId' => $SubjectDivisionCourseId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $Period
     * @param $SubjectDivisionCourseId
     *
     * @return string
     */
    public function editStudentSubjectDivisionCourseContent($DivisionCourseId, $Period, $SubjectDivisionCourseId): string
    {
        return DivisionCourse::useFrontend()->editStudentSubjectDivisionCourseContent($DivisionCourseId, $Period, $SubjectDivisionCourseId);
    }

    /**
     * @param $DivisionCourseId
     * @param $Period
     *
     * @return Pipeline
     */
    public static function pipelineLoadCheckSubjectDivisionCoursesContent($DivisionCourseId, $Period): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CheckSubjectDivisionCoursesContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadCheckSubjectDivisionCoursesContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Period' => $Period,
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $Period
     * @param null $Data
     *
     * @return string
     */
    public function loadCheckSubjectDivisionCoursesContent($DivisionCourseId, $Period, $Data = null): string
    {
        return DivisionCourse::useFrontend()->loadCheckSubjectDivisionCoursesContent($DivisionCourseId, $Period, $Data);
    }

    /**
     * @param $DivisionCourseId
     * @param $Period
     *
     * @return Pipeline
     */
    public static function pipelineSaveStudentSubjectDivisionCourseList($DivisionCourseId, $Period): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentSubjectContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveStudentSubjectDivisionCourseList',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Period' => $Period,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $Period
     * @param null $Data
     *
     * @return string
     */
    public function saveStudentSubjectDivisionCourseList($DivisionCourseId, $Period, $Data = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }

        if (DivisionCourse::useService()->createStudentSubjectDivisionCourseList($tblDivisionCourse, $Period, $Data)) {
            return new Success('Die Schüler-SekII-Kurse wurden erfolgreich gespeichert.')
                . self::pipelineLoadStudentSubjectContent($DivisionCourseId);
        } else {
            return new Danger('Die Schüler-SekII-Kurse konnten nicht gespeichert werden.');
        }
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCopySubjectDivisionCourseModal($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCopySubjectDivisionCourseModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function openCopySubjectDivisionCourseModal($DivisionCourseId): string
    {
        return DivisionCourse::useFrontend()->copySubjectDivisionCourse($DivisionCourseId);
    }

    /**
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineCopySubjectDivisionCourseSave($DivisionCourseId = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCopySubjectDivisionCourseModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function saveCopySubjectDivisionCourseModal($DivisionCourseId): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && DivisionCourse::useService()->copySubjectDivisionCourse($tblDivisionCourse)
        ) {
            return new Success('Die SekII-Kurse wurde erfolgreich ins 2. Halbjahr kopiert.')
                . self::pipelineLoadStudentSubjectContent($DivisionCourseId)
                . self::pipelineClose();
        } else {
            return new Danger('Die SekII-Kurse konnten nicht ins 2. Halbjahr kopiert werden.', new Exclamation())
                . self::pipelineClose();
        }
    }
}