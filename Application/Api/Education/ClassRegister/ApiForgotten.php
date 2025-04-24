<?php

namespace SPHERE\Application\Api\Education\ClassRegister;

use DateTime;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Extension;

class ApiForgotten extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadDueDateHomeworkContent');
        $Dispatcher->registerMethod('loadHomeworkSelectBox');

        $Dispatcher->registerMethod('loadForgottenContent');
        $Dispatcher->registerMethod('openCreateForgottenModal');
        $Dispatcher->registerMethod('saveCreateForgottenModal');
        $Dispatcher->registerMethod('openEditForgottenModal');
        $Dispatcher->registerMethod('saveEditForgottenModal');
        $Dispatcher->registerMethod('openDeleteForgottenModal');
        $Dispatcher->registerMethod('saveDeleteForgottenModal');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal(): ModalReceiver
    {
        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReceiver');
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
     * @param $SubjectId
     *
     * @return Pipeline
     */
    public static function pipelineLoadDueDateHomeworkContent($DivisionCourseId, $SubjectId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'DueDateHomeworkContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDueDateHomeworkContent',
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
     * @param null $Data
     *
     * @return string
     */
    public function loadDueDateHomeworkContent($DivisionCourseId, $SubjectId, $Data = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (isset($Data['serviceTblSubstituteSubject']) && ($tblSubject = Subject::useService()->getSubjectById($Data['serviceTblSubstituteSubject']))) {

        } elseif (isset($Data['serviceTblSubject'])) {
            $tblSubject = Subject::useService()->getSubjectById($Data['serviceTblSubject']);
        } else {
            $tblSubject = Subject::useService()->getSubjectById($SubjectId);
        }

        $Date = null;
        if (isset($Data['Date']))
        {
            $Date = $Data['Date'];
        }

        return Digital::useFrontend()->loadDueDateHomeworkListBySubject($tblDivisionCourse, $tblSubject ?: null, $Date ? new DateTime($Date) : null);
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Date
     * @param $LessonContentId
     * @param $CourseContentId
     *
     * @return Pipeline
     */
    public static function pipelineLoadHomeworkSelectBox($DivisionCourseId, $SubjectId, $Date, $LessonContentId, $CourseContentId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'HomeworkSelectBox'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadHomeworkSelectBox',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'SubjectId' => $SubjectId,
            'Date' => $Date,
            'LessonContentId' => $LessonContentId,
            'CourseContentId' => $CourseContentId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Date
     * @param $LessonContentId
     * @param $CourseContentId
     * @param null $Data
     *
     * @return string
     */
    public function loadHomeworkSelectBox($DivisionCourseId, $SubjectId, $Date, $LessonContentId, $CourseContentId, $Data = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (isset($Data['serviceTblSubject'])) {
            $tblSubject = Subject::useService()->getSubjectById($Data['serviceTblSubject']);
        } else {
            $tblSubject = Subject::useService()->getSubjectById($SubjectId);
        }

        $dateTime = $Date ? new DateTime($Date) : null;

        return Digital::useFrontend()->loadHomeworkSelectBox($tblDivisionCourse, $tblSubject ?: null, $dateTime, $LessonContentId, $CourseContentId);
    }

    /**
     * @param $DivisionCourseId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineLoadForgottenContent($DivisionCourseId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ForgottenContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadForgottenContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Filter' => $Filter
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param null $Filter
     *
     * @return string
     */
    public function loadForgottenContent($DivisionCourseId, $Filter = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        return Digital::useFrontend()->loadForgottenTable($tblDivisionCourse, $Filter);
    }

    /**
     * @param string|null $DivisionCourseId
     * @param string|null $Date
     * @param null $Filter
     * @param string|null $SubjectId
     * @param string|null $LessonContentId
     * @param string|null $CourseContentId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateForgottenModal(
        string $DivisionCourseId = null, string $Date = null, $Filter = null, string $SubjectId = null, string $LessonContentId = null, string $CourseContentId = null
    ): Pipeline {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateForgottenModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Date' => $Date,
            'Filter' => $Filter,
            'SubjectId' => $SubjectId,
            'LessonContentId' => $LessonContentId,
            'CourseContentId' => $CourseContentId
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionCourseId
     * @param null $Filter
     * @param string|null $Date
     * @param string|null $SubjectId
     * @param string|null $LessonContentId
     * @param string|null $CourseContentId
     *
     * @return string
     */
    public function openCreateForgottenModal(
        string $DivisionCourseId = null, $Filter = null, string $Date = null, string $SubjectId = null, string $LessonContentId = null, string $CourseContentId = null
    ): string {
        if (!(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId)))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        return $this->getForgottenModal(Digital::useFrontend()->formForgotten($tblDivisionCourse, $Filter, null, false, $Date, $SubjectId, $LessonContentId, $CourseContentId));
    }

    /**
     * @param $form
     * @param string|null $ForgottenId
     *
     * @return string
     */
    private function getForgottenModal($form, string $ForgottenId = null): string
    {
        if ($ForgottenId) {
            $title = new Title(new Edit() . ' Vergessene Arbeitsmittel/Hausaufgaben bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Vergessene Arbeitsmittel/Hausaufgaben hinzufügen');
        }

        return $title
            . new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    $form
                                )
                            )
                        )
                    ))
            );
    }

    /**
     * @param string $DivisionCourseId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineCreateForgottenSave(string $DivisionCourseId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateForgottenModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $DivisionCourseId
     * @param null $Filter
     * @param array|null $Data
     *
     * @return string
     */
    public function saveCreateForgottenModal(string $DivisionCourseId, $Filter = null, array $Data = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (($form = Digital::useService()->checkFormForgotten($Data, $tblDivisionCourse, $Filter))) {
            // display Errors on form
            return $this->getForgottenModal($form);
        }

        if (Digital::useService()->createForgotten($Data, $tblDivisionCourse)) {

            return new Success('Vergessene Arbeitsmittel/Hausaufgaben wurde erfolgreich gespeichert.')
                . self::pipelineLoadForgottenContent($DivisionCourseId, $Filter)
                . self::pipelineClose();
        } else {
            return new Danger('Vergessene Arbeitsmittel/Hausaufgaben konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string $ForgottenId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditForgottenModal(string $ForgottenId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditForgottenModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ForgottenId' => $ForgottenId,
            'Filter' => $Filter
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ForgottenId
     * @param $Filter
     *
     * @return string
     */
    public function openEditForgottenModal($ForgottenId, $Filter): string
    {
        if (!($tblForgotten = Digital::useService()->getForgottenById($ForgottenId))) {
            return new Danger('Vergessene Arbeitsmittel/Hausaufgaben wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivisionCourse = $tblForgotten->getServiceTblDivisionCourse())) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }

        return $this->getForgottenModal(Digital::useFrontend()->formForgotten($tblDivisionCourse, $Filter, $ForgottenId, true), $ForgottenId);
    }

    /**
     * @param string $ForgottenId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineEditForgottenSave(string $ForgottenId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditForgottenModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'ForgottenId' => $ForgottenId,
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ForgottenId
     * @param $Filter
     * @param null $Data
     *
     * @return string
     */
    public function saveEditForgottenModal($ForgottenId, $Filter, $Data = null): string
    {
        if (!($tblForgotten = Digital::useService()->getForgottenById($ForgottenId))) {
            return new Danger('Vergessene Arbeitsmittel/Hausaufgaben wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivisionCourse = $tblForgotten->getServiceTblDivisionCourse())) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }

        if (($form = Digital::useService()->checkFormForgotten($Data, $tblDivisionCourse, $Filter, $tblForgotten))) {
            // display Errors on form
            return $this->getForgottenModal($form, $ForgottenId);
        }

        if (Digital::useService()->updateForgotten($tblForgotten, $Data)) {

            return new Success('Vergessene Arbeitsmittel/Hausaufgaben wurde erfolgreich gespeichert.')
                . self::pipelineLoadForgottenContent($tblDivisionCourse->getId(), $Filter)
                . self::pipelineClose();
        } else {
            return new Danger('Vergessene Arbeitsmittel/Hausaufgaben konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string $ForgottenId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteForgottenModal(string $ForgottenId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteForgottenModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ForgottenId' => $ForgottenId,
            'Filter' => $Filter
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $ForgottenId
     * @param $Filter
     *
     * @return string
     */
    public function openDeleteForgottenModal(string $ForgottenId, $Filter): string
    {
        if (!($tblForgotten = Digital::useService()->getForgottenById($ForgottenId))) {
            return new Danger('Vergessene Arbeitsmittel/Hausaufgaben wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Vergessene Arbeitsmittel/Hausaufgaben löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diese Vergessene Arbeitsmittel/Hausaufgaben wirklich löschen?',
                                array(
                                    $tblForgotten->getDate(),
                                    ($tblSubject = $tblForgotten->getServiceTblSubject()) ? $tblSubject->getDisplayName() : null,
                                    $tblForgotten->getDisplayType(),
                                    $tblForgotten->getRemark(),
                                    $tblForgotten->getDisplayForgottenStudents(),
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteForgottenSave($ForgottenId, $Filter))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param string $ForgottenId
     * @param $Filter
     *
     * @return Pipeline
     */
    public static function pipelineDeleteForgottenSave(string $ForgottenId, $Filter): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteForgottenModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'ForgottenId' => $ForgottenId,
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $ForgottenId
     * @param $Filter
     *
     * @return string
     */
    public function saveDeleteForgottenModal(string $ForgottenId, $Filter): string
    {
        if (!($tblForgotten = Digital::useService()->getForgottenById($ForgottenId))) {
            return new Danger('Vergessene Arbeitsmittel/Hausaufgaben wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivisionCourse = $tblForgotten->getServiceTblDivisionCourse())) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }

        if (Digital::useService()->destroyForgotten($tblForgotten)) {
            return new Success('Vergessene Arbeitsmittel/Hausaufgaben wurde erfolgreich gelöscht.')
                . self::pipelineLoadForgottenContent($tblDivisionCourse->getId(), $Filter)
                . self::pipelineClose();
        } else {
            return new Danger('Vergessene Arbeitsmittel/Hausaufgaben konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }
}