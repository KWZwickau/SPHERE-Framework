<?php

namespace SPHERE\Application\Api\Education\Competence;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Competence\SkillRate\SkillRate;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\System\Extension\Extension;

class ApiSkillRate extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('changeYearOrRole');
        $Dispatcher->registerMethod('loadViewSelect');
        $Dispatcher->registerMethod('changeShowDivisionTeacher');

        $Dispatcher->registerMethod('loadViewStudentContent');
        $Dispatcher->registerMethod('loadEditStudentContent');
        $Dispatcher->registerMethod('saveEditStudentSkillRate');
        $Dispatcher->registerMethod('openStudentSkillRateHistoryModal');

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
     * @param $SchoolTypeId
     *
     * @return Pipeline
     */
    public static function pipelineChangeYearOrRole($SchoolTypeId): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'changeYearOrRole',
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $SchoolTypeId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function changeYearOrRole($SchoolTypeId = null, $Data = null): string
    {
        if (isset($Data["IsHeadmaster"])) {
            $role = "Headmaster";
        } elseif (isset($Data["IsAllReadonly"])) {
            $role = "AllReadonly";
        } else {
            $role = "Teacher";
        }
        $skillRateRole = Consumer::useService()->getAccountSettingValue("SkillRateRole");
        if (!$skillRateRole || $skillRateRole != $role) {
            Consumer::useService()->createAccountSetting("SkillRateRole", $role);
        }

        $tblYear = null;
        if (isset($Data['SelectedYearId']) && $Data['SelectedYearId'] > 0) {
            $tblYear = Term::useService()->getYearById($Data['SelectedYearId']) ?: null;
        }

        return self::pipelineLoadViewSelect($tblYear ? $tblYear->getId() : null, $SchoolTypeId);
    }

    /**
     * @param null $YearId
     * @param null $SchoolTypeId
     * @param string $DontShowDivisionTeacher
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewSelect($YearId = null, $SchoolTypeId = null, string $DontShowDivisionTeacher = 'null'): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewSelect',
        ));
        $ModalEmitter->setPostPayload(array(
            'YearId' => $YearId,
            'SchoolTypeId' => $SchoolTypeId,
            'DontShowDivisionTeacher' => $DontShowDivisionTeacher,
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $YearId
     * @param $SchoolTypeId
     * @param $DontShowDivisionTeacherGradeBooks
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadViewSelect($YearId, $SchoolTypeId, $DontShowDivisionTeacherGradeBooks): string
    {
        if ($DontShowDivisionTeacherGradeBooks == 'true') {
            $boolean = false;
        } elseif ($DontShowDivisionTeacherGradeBooks == 'false') {
            $boolean = true;
        } else {
            $boolean = null;
        }

        return SkillRate::useFrontend()->loadViewSelect($YearId, $SchoolTypeId, $boolean);
    }

    /**
     * @param $SelectedYearId
     *
     * @return Pipeline
     */
    public static function pipelineChangeShowDivisionTeacher($SelectedYearId): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'changeShowDivisionTeacher',
        ));
        $ModalEmitter->setPostPayload(array(
            'SelectedYearId' => $SelectedYearId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $SelectedYearId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function changeShowDivisionTeacher($SelectedYearId = null, $Data = null): string
    {
        $show = isset($Data['ShowDivisionTeacher']);

        $value = Consumer::useService()->getAccountSettingValue("DontShowDivisionTeacherSkillRates");
        if ($value == $show) {
            Consumer::useService()->createAccountSetting("DontShowDivisionTeacherSkillRates", !$value);

            return ""
                . self::pipelineLoadViewSelect($SelectedYearId, null, $value ? 'true' : 'false');
        }

        return "";
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewStudentContent($DivisionCourseId, $PersonId, $SubjectId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewStudentContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId,
            'SubjectId' => $SubjectId
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadViewStudentContent($DivisionCourseId, $PersonId, $SubjectId): string
    {
        return SkillRate::useFrontend()->loadViewStudentContent($DivisionCourseId, $PersonId, $SubjectId);
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     *
     * @return Pipeline
     */
    public static function pipelineLoadEditStudentContent($DivisionCourseId, $PersonId, $SubjectId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadEditStudentContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId,
            'SubjectId' => $SubjectId
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadEditStudentContent($DivisionCourseId, $PersonId, $SubjectId): string
    {
        return SkillRate::useFrontend()->loadEditStudentContent($DivisionCourseId, $PersonId, $SubjectId);
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditStudentSkillRate($DivisionCourseId, $PersonId, $SubjectId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditStudentSkillRate',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId,
            'SubjectId' => $SubjectId
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveEditStudentSkillRate($DivisionCourseId, $PersonId, $SubjectId, $Data = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs nicht gefunden.', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }
        $tblSubject = $SubjectId ? Subject::useService()->getSubjectById($SubjectId) : null;

        return SkillRate::useService()->createStudentSkillRateList($tblDivisionCourse, $tblPerson, $tblSubject ?: null, $Data);
    }

    /**
     * @param $StudentSkillId
     *
     * @return Pipeline
     */
    public static function pipelineOpenStudentSkillRateHistoryModal($StudentSkillId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openStudentSkillRateHistoryModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'StudentSkillId' => $StudentSkillId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $StudentSkillId
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function openStudentSkillRateHistoryModal($StudentSkillId): string
    {
        return SkillRate::useFrontend()->openStudentSkillRateHistoryModal($StudentSkillId);
    }
}