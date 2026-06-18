<?php

namespace SPHERE\Application\Api\Education\Competence;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Competence\SkillRate\SkillRate;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\ParentStudentAccess\OnlineCompetence\OnlineCompetence;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\System\Extension\Extension;

class ApiOnlineSkillRate extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param $Method
     *
     * @return string
     * @noinspection PhpMissingParamTypeInspection
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('loadViewStudentContent');
        $Dispatcher->registerMethod('openStudentSkillRateHistoryModal');
        $Dispatcher->registerMethod('loadViewStudentSkillRateHistoryContent');

        $Dispatcher->registerMethod('loadStudentContent');
        $Dispatcher->registerMethod('loadSubjectContent');

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
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param string $OldYears
     * @param string $Interdisciplinary
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewStudentContent($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId,
        string $OldYears = 'false', string $Interdisciplinary = 'false'): Pipeline
    {
        // todo methode eventuell wieder in ApiSkillRate verschieben

        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewStudentContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId,
            'SubjectId' => $SubjectId,
            'SelectedYearId' => $SelectedYearId,
            'OldYears' => $OldYears,
            'Interdisciplinary' => $Interdisciplinary
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param $OldYears
     * @param $Interdisciplinary
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadViewStudentContent($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $OldYears, $Interdisciplinary): string
    {
        return SkillRate::useFrontend()->loadViewStudentContent(
            $DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $OldYears === 'true', $Interdisciplinary === 'true');
    }

    /**
     * @param $DivisionCourseId
     * @param $StudentSkillId
     * @param $SelectedYearId
     * @param $SubjectId
     *
     * @return Pipeline
     */
    public static function pipelineOpenStudentSkillRateHistoryModal($DivisionCourseId, $StudentSkillId, $SelectedYearId, $SubjectId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openStudentSkillRateHistoryModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'StudentSkillId' => $StudentSkillId,
            'SelectedYearId' => $SelectedYearId,
            'SubjectId' => $SubjectId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $StudentSkillId
     * @param $SelectedYearId
     * @param $SubjectId
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function openStudentSkillRateHistoryModal($DivisionCourseId, $StudentSkillId, $SelectedYearId, $SubjectId): string
    {
        return SkillRate::useFrontend()->openStudentSkillRateHistoryModal($DivisionCourseId, $StudentSkillId, $SelectedYearId, $SubjectId);
    }

    /**
     * @param $DivisionCourseId
     * @param $StudentSkillId
     * @param $SelectedYearId
     * @param $SubjectId
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillId, $SelectedYearId, $SubjectId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SkillRateHistoryContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewStudentSkillRateHistoryContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'StudentSkillId' => $StudentSkillId,
            'SelectedYearId' => $SelectedYearId,
            'SubjectId' => $SubjectId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $StudentSkillId
     * @param $SelectedYearId
     * @param $SubjectId
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadViewStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillId, $SelectedYearId, $SubjectId): string
    {
        return SkillRate::useFrontend()->loadViewStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillId, $SelectedYearId, $SubjectId);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadStudentContent(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentContent',
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     *
     * @return string
     *
     * @noinspection PhpUnused
     */
    public function loadStudentContent($Data = null): string
    {
        return OnlineCompetence::useFrontend()->loadStudentContent(Person::useService()->getPersonById($Data['PersonId'] ?? 0) ?: null);
    }

    /**
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineLoadSubjectContent($PersonId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SubjectContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadSubjectContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param null $Data
     *
     * @return string
     *
     * @noinspection PhpUnused
     *
     */
    public function loadSubjectContent($PersonId, $Data = null): string
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person wurde nicht gefunden.', new Exclamation());
        }

        // todo fächerübergreifend
        $subjectId = $Data['SubjectId'] ?? 0;
        $tblSubject = null;
        $isInterdisciplinary = false;
        if ($subjectId == -1) {
            $isInterdisciplinary = true;
        } else {
            $tblSubject = Subject::useService()->getSubjectById($subjectId) ?: null;
        }

        return OnlineCompetence::useFrontend()->loadSubjectContent($tblPerson, $tblSubject, $isInterdisciplinary);
    }
}