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
use SPHERE\Common\Frontend\Message\Repository\Success;
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

        $Dispatcher->registerMethod('loadEditStudentContent');
        $Dispatcher->registerMethod('saveEditStudentSkillRate');
        
        $Dispatcher->registerMethod('loadEditStudentSkillRateHistoryContent');
        $Dispatcher->registerMethod('saveEditStudentSkillRateHistoryContent');
        $Dispatcher->registerMethod('loadDeleteStudentSkillRateHistoryContent');
        $Dispatcher->registerMethod('saveDeleteStudentSkillRateHistoryContent');

        $Dispatcher->registerMethod('openRenameStudentSkillModal');
        $Dispatcher->registerMethod('loadRenameSkillContent');
        $Dispatcher->registerMethod('saveRenameSkill');

        $Dispatcher->registerMethod('openAddStudentSkillModal');
        $Dispatcher->registerMethod('saveAddStudentSkill');

        $Dispatcher->registerMethod('openCreateStudentSkillModal');
        $Dispatcher->registerMethod('saveCreateStudentSkill');

        // DivisionCourse
        $Dispatcher->registerMethod('checkInActive');
        $Dispatcher->registerMethod('loadViewDivisionCourseContent');
        $Dispatcher->registerMethod('loadEditDivisionCourseContent');
        $Dispatcher->registerMethod('loadEditDivisionCourseSkillRateContent');
        $Dispatcher->registerMethod('saveEditDivisionCourseSkillRate');

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
     * @param $SelectedYearId
     * @param string $Interdisciplinary
     *
     * @return Pipeline
     */
    public static function pipelineLoadEditStudentContent($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, string $Interdisciplinary = 'false'): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadEditStudentContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId,
            'SubjectId' => $SubjectId,
            'SelectedYearId' => $SelectedYearId,
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
     * @param $Interdisciplinary
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadEditStudentContent($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $Interdisciplinary): string
    {
        return SkillRate::useFrontend()->loadEditStudentContent($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $Interdisciplinary === 'true');
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param string $Interdisciplinary
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditStudentSkillRate($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, string $Interdisciplinary = 'false'): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditStudentSkillRate',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId,
            'SubjectId' => $SubjectId,
            'SelectedYearId' => $SelectedYearId,
            'Interdisciplinary' => $Interdisciplinary
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param $Interdisciplinary
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveEditStudentSkillRate($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $Interdisciplinary, $Data = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs nicht gefunden.', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }

        $IsInterdisciplinary = $Interdisciplinary === 'true';
        $tblSubject = null;
        if (!$IsInterdisciplinary) {
            $tblSubject = $SubjectId ? Subject::useService()->getSubjectById($SubjectId) : null;
        }

        return SkillRate::useService()->createStudentSkillRateList($tblDivisionCourse, $tblPerson, $tblSubject ?: null, $SelectedYearId, $SubjectId, $Data);
    }

    /**
     * @param $DivisionCourseId
     * @param $StudentSkillRateId
     * @param $SelectedYearId
     * @param $SubjectId
     *
     * @return Pipeline
     */
    public static function pipelineLoadEditStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillRateId, $SelectedYearId, $SubjectId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SkillRateHistoryContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadEditStudentSkillRateHistoryContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'StudentSkillRateId' => $StudentSkillRateId,
            'SelectedYearId' => $SelectedYearId,
            'SubjectId' => $SubjectId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $StudentSkillRateId
     * @param $SelectedYearId
     * @param $SubjectId
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadEditStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillRateId, $SelectedYearId, $SubjectId): string
    {
        return SkillRate::useFrontend()->loadEditStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillRateId, $SelectedYearId, $SubjectId);
    }

    /**
     * @param $DivisionCourseId
     * @param $StudentSkillRateId
     * @param $SelectedYearId
     * @param $SubjectId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillRateId, $SelectedYearId, $SubjectId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SkillRateHistoryContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditStudentSkillRateHistoryContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'StudentSkillRateId' => $StudentSkillRateId,
            'SelectedYearId' => $SelectedYearId,
            'SubjectId' => $SubjectId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $StudentSkillRateId
     * @param $SelectedYearId
     * @param $SubjectId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveEditStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillRateId, $SelectedYearId, $SubjectId, $Data = null): string
    {
        if (!($tblStudentSkillRate = SkillRate::useService()->getStudentSkillRateById($StudentSkillRateId))) {
            return new Danger('Kompetenzbewertung wurde nicht gefunden.', new Exclamation());
        }

        return SkillRate::useService()->updateStudentSkillRate($DivisionCourseId, $tblStudentSkillRate, $SelectedYearId, $SubjectId, $Data);
    }

    /**
     * @param $DivisionCourseId
     * @param $StudentSkillRateId
     * @param $SelectedYearId
     * @param $SubjectId
     *
     * @return Pipeline
     */
    public static function pipelineLoadDeleteStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillRateId, $SelectedYearId, $SubjectId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SkillRateHistoryContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDeleteStudentSkillRateHistoryContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'StudentSkillRateId' => $StudentSkillRateId,
            'SelectedYearId' => $SelectedYearId,
            'SubjectId' => $SubjectId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $StudentSkillRateId
     * @param $SelectedYearId
     * @param $SubjectId
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadDeleteStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillRateId, $SelectedYearId, $SubjectId): string
    {
        return SkillRate::useFrontend()->loadDeleteStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillRateId, $SelectedYearId, $SubjectId);
    }

    /**
     * @param $DivisionCourseId
     * @param $StudentSkillRateId
     * @param $SelectedYearId
     * @param $SubjectId
     *
     * @return Pipeline
     */
    public static function pipelineSaveDeleteStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillRateId, $SelectedYearId, $SubjectId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SkillRateHistoryContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteStudentSkillRateHistoryContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'StudentSkillRateId' => $StudentSkillRateId,
            'SelectedYearId' => $SelectedYearId,
            'SubjectId' => $SubjectId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $StudentSkillRateId
     * @param $SelectedYearId
     * @param $SubjectId
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveDeleteStudentSkillRateHistoryContent($DivisionCourseId, $StudentSkillRateId, $SelectedYearId, $SubjectId): string
    {
        if (!($tblStudentSkillRate = SkillRate::useService()->getStudentSkillRateById($StudentSkillRateId))) {
            return new Danger('Kompetenzbewertung wurde nicht gefunden.', new Exclamation());
        }

        return SkillRate::useService()->deleteStudentSkillRate($DivisionCourseId, $tblStudentSkillRate, $SelectedYearId, $SubjectId);
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     *
     * @return Pipeline
     */
    public static function pipelineOpenRenameStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openRenameStudentSkillModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId,
            'SubjectId' => $SubjectId,
            'SelectedYearId' => $SelectedYearId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     *
     * @return String
     * @noinspection PhpUnused
     */
    public function openRenameStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId): string
    {
        return SkillRate::useFrontend()->openRenameStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId);
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     *
     * @return Pipeline
     */
    public static function pipelineLoadRenameSkillContent($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'RenameSkillContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadRenameSkillContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId,
            'SubjectId' => $SubjectId,
            'SelectedYearId' => $SelectedYearId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadRenameSkillContent($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $Data = null): string
    {
        return SkillRate::useFrontend()->loadRenameSkillContent($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $Data);
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     *
     * @return Pipeline
     */
    public static function pipelineSaveRenameSkill($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveRenameSkill'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId,
            'SubjectId' => $SubjectId,
            'SelectedYearId' =>$SelectedYearId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveRenameSkill($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $Data = null): string
    {
        SkillRate::useService()->updateStudentSkill($DivisionCourseId, $PersonId, $SubjectId, $Data);

        return new Success('Kompetenz wurde erfolgreich umbenannt.')
            . self::pipelineClose()
            . ApiOnlineSkillRate::pipelineLoadViewStudentContent($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId);
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openAddStudentSkillModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId,
            'SubjectId' => $SubjectId,
            'SelectedYearId' => $SelectedYearId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     *
     * @return String
     * @noinspection PhpUnused
     */
    public function openAddStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId): string
    {
        return SkillRate::useFrontend()->openAddStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId);
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     *
     * @return Pipeline
     */
    public static function pipelineSaveAddStudentSkill($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveAddStudentSkill'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId,
            'SubjectId' => $SubjectId,
            'SelectedYearId' => $SelectedYearId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveAddStudentSkill($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $Data = null): string
    {
        return SkillRate::useService()->addStudentSkill($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $Data);
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateStudentSkillModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId,
            'SubjectId' => $SubjectId,
            'SelectedYearId' => $SelectedYearId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     *
     * @return String
     * @noinspection PhpUnused
     */
    public function openCreateStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId): string
    {
        return SkillRate::useFrontend()->openCreateStudentSkillModal($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId);
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     *
     * @return Pipeline
     */
    public static function pipelineSaveCreateStudentSkill($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateStudentSkill'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId,
            'SubjectId' => $SubjectId,
            'SelectedYearId' => $SelectedYearId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveCreateStudentSkill($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $Data = null): string
    {
        return SkillRate::useService()->createStudentSkill($DivisionCourseId, $PersonId, $SubjectId, $SelectedYearId, $Data);
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $SelectedYearId
     *
     * @return Pipeline
     */
    public static function pipelineCheckInActive($DivisionCourseId, $SubjectId, $SelectedYearId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'checkInActive',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'SubjectId' => $SubjectId,
            'SelectedYearId' => $SelectedYearId,
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function checkInActive($DivisionCourseId, $SubjectId, $SelectedYearId, $Data): string
    {
        return SkillRate::useFrontend()->loadViewDivisionCourseContent($DivisionCourseId, $SubjectId, $SelectedYearId, isset($Data['OptionInActive']));
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param string $Interdisciplinary
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewDivisionCourseContent($DivisionCourseId, $SubjectId, $SelectedYearId, string $Interdisciplinary = 'false'): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewDivisionCourseContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'SubjectId' => $SubjectId,
            'SelectedYearId' => $SelectedYearId,
            'Interdisciplinary' => $Interdisciplinary
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param $Interdisciplinary
     *
     * @return string
     *
     * @noinspection PhpUnused
     */
    public function loadViewDivisionCourseContent($DivisionCourseId, $SubjectId, $SelectedYearId, $Interdisciplinary): string
    {
        return SkillRate::useFrontend()->loadViewDivisionCourseContent($DivisionCourseId, $SubjectId, $SelectedYearId, false, $Interdisciplinary === 'true');
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param string $Interdisciplinary
     *
     * @return Pipeline
     */
    public static function pipelineLoadEditDivisionCourseContent($DivisionCourseId, $SubjectId, $SelectedYearId, string $Interdisciplinary = 'false'): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadEditDivisionCourseContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'SubjectId' => $SubjectId,
            'SelectedYearId' => $SelectedYearId,
            'Interdisciplinary' => $Interdisciplinary
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param $Interdisciplinary
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadEditDivisionCourseContent($DivisionCourseId, $SubjectId, $SelectedYearId, $Interdisciplinary): string
    {
        return SkillRate::useFrontend()->loadEditDivisionCourseContent($DivisionCourseId, $SubjectId, $SelectedYearId, $Interdisciplinary === 'true');
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param string $Interdisciplinary
     *
     * @return Pipeline
     */
    public static function pipelineLoadEditDivisionCourseSkillRateContent(
        $DivisionCourseId, $SubjectId, $SelectedYearId, string $Interdisciplinary = 'false'
    ): Pipeline {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SkillRateContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadEditDivisionCourseSkillRateContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'SubjectId' => $SubjectId,
            'SelectedYearId' => $SelectedYearId,
            'Interdisciplinary' => $Interdisciplinary
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param $Interdisciplinary
     * @param null $Data
     *
     * @return string
     *
     * @noinspection PhpUnused
     */
    public function loadEditDivisionCourseSkillRateContent($DivisionCourseId, $SubjectId, $SelectedYearId, $Interdisciplinary, $Data = null): string
    {
        return SkillRate::useFrontend()->loadEditDivisionCourseSkillRateContent(
            $DivisionCourseId, $SubjectId, $SelectedYearId, $Interdisciplinary === 'true', $Data);
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param string $Interdisciplinary
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditDivisionCourseSkillRate(
        $DivisionCourseId, $SubjectId, $SelectedYearId, string $Interdisciplinary = 'false'
    ): Pipeline {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SkillRateContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditDivisionCourseSkillRate',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'SubjectId' => $SubjectId,
            'SelectedYearId' => $SelectedYearId,
            'Interdisciplinary' => $Interdisciplinary
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param $Interdisciplinary
     * @param null $Data
     *
     * @return string
     *
     * @noinspection PhpUnused
     */
    public function saveEditDivisionCourseSkillRate($DivisionCourseId, $SubjectId, $SelectedYearId, $Interdisciplinary, $Data = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs nicht gefunden.', new Exclamation());
        }
        $tblSubject = $SubjectId ? Subject::useService()->getSubjectById($SubjectId) : null;

        return SkillRate::useService()->createDivisionCourseSkillRateList(
            $tblDivisionCourse, $tblSubject ?: null, $SelectedYearId, $Interdisciplinary === 'true', $Data);
    }
}