<?php

namespace SPHERE\Application\Api\Education\ClassRegister;

use DateTime;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
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
use SPHERE\Common\Frontend\Message\Repository\Warning;
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

        $Dispatcher->registerMethod('openCreateForgottenModal');
        $Dispatcher->registerMethod('saveCreateForgottenModal');
//        $Dispatcher->registerMethod('openEditForgottenModal');
//        $Dispatcher->registerMethod('saveEditForgottenModal');
//        $Dispatcher->registerMethod('openDeleteForgottenModal');
//        $Dispatcher->registerMethod('saveDeleteForgottenModal');


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
     *
     * @return Pipeline
     */
    public static function pipelineLoadHomeworkSelectBox($DivisionCourseId, $SubjectId, $Date): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'HomeworkSelectBox'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadHomeworkSelectBox',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'SubjectId' => $SubjectId,
            'Date' => $Date
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Date
     * @param null $Data
     *
     * @return string
     */
    public function loadHomeworkSelectBox($DivisionCourseId, $SubjectId, $Date, $Data = null): string
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

        return Digital::useFrontend()->loadHomeworkSelectBox($tblDivisionCourse, $tblSubject ?: null, $dateTime);
    }

    /**
     * @param string|null $DivisionCourseId
     * @param string|null $Date
     * @param string|null $SubjectId
     * @param string|null $LessonContentId
     * @param string|null $CourseContentId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateForgottenModal(
        string $DivisionCourseId = null, string $Date = null, string $SubjectId = null, string $LessonContentId = null, string $CourseContentId = null
    ): Pipeline {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateForgottenModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Date' => $Date,
            'SubjectId' => $SubjectId,
            'LessonContentId' => $LessonContentId,
            'CourseContentId' => $CourseContentId
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionCourseId
     * @param string|null $Date
     * @param string|null $SubjectId
     * @param string|null $LessonContentId
     * @param string|null $CourseContentId
     *
     * @return string
     */
    public function openCreateForgottenModal(
        string $DivisionCourseId = null, string $Date = null, string $SubjectId = null, string $LessonContentId = null, string $CourseContentId = null
    ): string {
        if (!(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId)))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        return $this->getForgottenModal(Digital::useFrontend()->formForgotten($tblDivisionCourse, null, false, $Date, $SubjectId, $LessonContentId, $CourseContentId));
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
     *
     * @return Pipeline
     */
    public static function pipelineCreateForgottenSave(string $DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateForgottenModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $DivisionCourseId
     * @param array|null $Data
     *
     * @return Danger|string
     */
    public function saveCreateForgottenModal(string $DivisionCourseId, array $Data = null)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (($form = Digital::useService()->checkFormForgotten($Data, $tblDivisionCourse))) {
            // display Errors on form
            return $this->getForgottenModal($form);
        }

        if (($tblForgotten = Digital::useService()->createForgotten($Data, $tblDivisionCourse))) {

            return new Success('Vergessene Arbeitsmittel/Hausaufgaben wurde erfolgreich gespeichert.')
                . self::pipelineClose();
        } else {
            return new Danger('Vergessene Arbeitsmittel/Hausaufgaben konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string $ForgottenId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditForgottenModal(string $ForgottenId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditForgottenModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ForgottenId' => $ForgottenId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $ForgottenId
     *
     * @return Pipeline
     */
    public static function pipelineEditForgottenSave(string $ForgottenId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditForgottenModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'ForgottenId' => $ForgottenId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

//    /**
//     * @param $ForgottenId
//     *
//     * @return string
//     */
//    public function openEditForgottenModal($ForgottenId)
//    {
//        if (!($tblForgotten = Digital::useService()->getForgottenById($ForgottenId))) {
//            return new Danger('Thema/Hausaufgaben wurde nicht gefunden', new Exclamation());
//        }
//        if (!($tblDivisionCourse = $tblForgotten->getServiceTblDivisionCourse())) {
//            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
//        }
//
//        return $this->getForgottenModal(Digital::useFrontend()->formForgotten($tblDivisionCourse, $ForgottenId, true), $ForgottenId);
//    }
//
//    /**
//     * @param $ForgottenId
//     * @param $Data
//     *
//     * @return Danger|string
//     */
//    public function saveEditForgottenModal($ForgottenId, $Data)
//    {
//        if (!($tblForgotten = Digital::useService()->getForgottenById($ForgottenId))) {
//            return new Danger('Thema/Hausaufgaben wurde nicht gefunden', new Exclamation());
//        }
//        if (!($tblDivisionCourse = $tblForgotten->getServiceTblDivisionCourse())) {
//            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
//        }
//
//        if (($form = Digital::useService()->checkFormForgotten($Data, $tblDivisionCourse, $tblForgotten))) {
//            // display Errors on form
//            return $this->getForgottenModal($form, $ForgottenId);
//        }
//
//        if (Digital::useService()->updateForgotten($tblForgotten, $Data)) {
//            if (($tblForgottenLinkedList = $tblForgotten->getLinkedForgottenAll())) {
//                foreach ($tblForgottenLinkedList as $tblForgottenItem) {
//                    Digital::useService()->updateForgotten($tblForgottenItem, $Data);
//                }
//            }
//            return new Success('Thema/Hausaufgaben wurde erfolgreich gespeichert.')
//                . self::pipelineLoadForgottenContent($tblDivisionCourse->getId(), $Data['Date'],
//                    ($View = Consumer::useService()->getAccountSettingValue('ForgottenView')) ? $View : 'Day')
//                . self::pipelineClose();
//        } else {
//            return new Danger('Thema/Hausaufgaben konnte nicht gespeichert werden.') . self::pipelineClose();
//        }
//    }
//
//    /**
//     * @param string $ForgottenId
//     *
//     * @return Pipeline
//     */
//    public static function pipelineOpenDeleteForgottenModal(string $ForgottenId): Pipeline
//    {
//        $Pipeline = new Pipeline(false);
//        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
//        $ModalEmitter->setGetPayload(array(
//            self::API_TARGET => 'openDeleteForgottenModal',
//        ));
//        $ModalEmitter->setPostPayload(array(
//            'ForgottenId' => $ForgottenId
//        ));
//        $Pipeline->appendEmitter($ModalEmitter);
//
//        return $Pipeline;
//    }
//
//    /**
//     * @param string $ForgottenId
//     *
//     * @return string
//     */
//    public function openDeleteForgottenModal(string $ForgottenId)
//    {
//        if (!($tblForgotten = Digital::useService()->getForgottenById($ForgottenId))) {
//            return new Danger('Thema/Hausaufgaben wurde nicht gefunden', new Exclamation());
//        }
//
//        return new Title(new Remove() . ' Thema/Hausaufgaben löschen')
//            . (($linkedPanel = Digital::useService()->getForgottenLinkedDisplayPanel($ForgottenId)) ? : '')
//            . new Layout(
//                new LayoutGroup(
//                    new LayoutRow(
//                        new LayoutColumn(
//                            new Panel(
//                                new Question() . ' Diese Thema/Hausaufgaben wirklich löschen?',
//                                array(
//                                    $tblForgotten->getDate(),
//                                    $tblForgotten->getLessonDisplay(),
//                                    $tblForgotten->getDisplaySubject(false),
//                                    ($tblPerson = $tblForgotten->getServiceTblPerson())
//                                        ? $tblPerson->getFullName() : '',
//                                    $tblForgotten->getContent(),
//                                    $tblForgotten->getHomework(),
//                                ),
//                                Panel::PANEL_TYPE_DANGER
//                            )
//                            . ($linkedPanel ? new Warning('Verknüpfte Thema/Hausaufgaben werden mit gelöscht.', new Exclamation()) : '')
//                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
//                                ->ajaxPipelineOnClick(self::pipelineDeleteForgottenSave($ForgottenId))
//                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
//                                ->ajaxPipelineOnClick(self::pipelineClose())
//                        )
//                    )
//                )
//            );
//    }
//
//    /**
//     * @param string $ForgottenId
//     *
//     * @return Pipeline
//     */
//    public static function pipelineDeleteForgottenSave(string $ForgottenId): Pipeline
//    {
//
//        $Pipeline = new Pipeline();
//        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
//        $ModalEmitter->setGetPayload(array(
//            self::API_TARGET => 'saveDeleteForgottenModal'
//        ));
//        $ModalEmitter->setPostPayload(array(
//            'ForgottenId' => $ForgottenId
//        ));
//        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
//        $Pipeline->appendEmitter($ModalEmitter);
//
//        return $Pipeline;
//    }
//
//    /**
//     * @param string $ForgottenId
//     *
//     * @return Danger|string
//     */
//    public function saveDeleteForgottenModal(string $ForgottenId)
//    {
//        if (!($tblForgotten = Digital::useService()->getForgottenById($ForgottenId))) {
//            return new Danger('Thema/Hausaufgaben wurde nicht gefunden', new Exclamation());
//        }
//        if (!($tblDivisionCourse = $tblForgotten->getServiceTblDivisionCourse())) {
//            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
//        }
//        $date = $tblForgotten->getDate();
//
//        if (Digital::useService()->destroyForgotten($tblForgotten)) {
//            return new Success('Thema/Hausaufgaben wurde erfolgreich gelöscht.')
//                . self::pipelineLoadForgottenContent($tblDivisionCourse->getId(), $date,
//                    ($View = Consumer::useService()->getAccountSettingValue('ForgottenView')) ? $View : 'Day')
//                . self::pipelineClose();
//        } else {
//            return new Danger('Thema/Hausaufgaben konnte nicht gelöscht werden.') . self::pipelineClose();
//        }
//    }
}