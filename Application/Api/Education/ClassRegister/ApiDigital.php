<?php

namespace SPHERE\Application\Api\Education\ClassRegister;

use DateTime;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
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
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiDigital
 *
 * @package SPHERE\Application\Api\Education\ClassRegister
 */
class ApiDigital extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadLessonContentContent');
        $Dispatcher->registerMethod('openCreateLessonContentModal');
        $Dispatcher->registerMethod('saveCreateLessonContentModal');
        $Dispatcher->registerMethod('openEditLessonContentModal');
        $Dispatcher->registerMethod('saveEditLessonContentModal');
        $Dispatcher->registerMethod('openDeleteLessonContentModal');
        $Dispatcher->registerMethod('saveDeleteLessonContentModal');

        $Dispatcher->registerMethod('loadLessonContentLinkPanel');

        $Dispatcher->registerMethod('loadLessonWeekContent');
        $Dispatcher->registerMethod('saveLessonWeekCheck');
        $Dispatcher->registerMethod('openEditLessonWeekRemarkModal');
        $Dispatcher->registerMethod('saveEditLessonWeekRemarkModal');

        $Dispatcher->registerMethod('loadCourseContentContent');
        $Dispatcher->registerMethod('openCreateCourseContentModal');
        $Dispatcher->registerMethod('saveCreateCourseContentModal');
        $Dispatcher->registerMethod('openEditCourseContentModal');
        $Dispatcher->registerMethod('saveEditCourseContentModal');
        $Dispatcher->registerMethod('openDeleteCourseContentModal');
        $Dispatcher->registerMethod('saveDeleteCourseContentModal');

        $Dispatcher->registerMethod('loadCourseMissingStudentContent');

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
     * @param string|null $DivisionCourseId
     * @param string $Date
     * @param string $View
     *
     * @return Pipeline
     */
    public static function pipelineLoadLessonContentContent(string $DivisionCourseId = null, string $Date = 'today', string $View = 'Day'): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'LessonContentContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadLessonContentContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Date' => $Date,
            'View' => $View
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionCourseId
     * @param string $Date
     * @param string $View
     * @param null $Data
     *
     * @return string
     */
    public function loadLessonContentContent(string $DivisionCourseId = null, string $Date = 'today', string $View = 'Day', $Data = null) : string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (isset($Data['Date'])) {
            $Date = $Data['Date'];
        }

        // View speichern
        Consumer::useService()->createAccountSetting('LessonContentView', $View);

        return Digital::useFrontend()->loadLessonContentTable($tblDivisionCourse, $Date, $View);
    }

    /**
     * @param string|null $DivisionCourseId
     * @param string|null $Date
     * @param string|null $Lesson
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateLessonContentModal(string $DivisionCourseId = null, string $Date = null, string $Lesson = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateLessonContentModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Date' => $Date,
            'Lesson' => $Lesson
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionCourseId
     * @param string|null $Date
     * @param string|null $Lesson
     *
     * @return string
     */
    public function openCreateLessonContentModal(string $DivisionCourseId = null, string $Date = null, string $Lesson = null): string
    {
        if (!(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId)))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        return $this->getLessonContentModal(Digital::useFrontend()->formLessonContent($tblDivisionCourse, null, false, $Date, $Lesson));
    }

    /**
     * @param $form
     * @param string|null $LessonContentId
     *
     * @return string
     */
    private function getLessonContentModal($form, string $LessonContentId = null): string
    {
        if ($LessonContentId) {
            $title = new Title(new Edit() . ' Thema/Hausaufgaben bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Thema/Hausaufgaben hinzufügen');
        }

        return $title
            . Digital::useService()->getLessonContentLinkedDisplayPanel($LessonContentId)
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
    public static function pipelineCreateLessonContentSave(string $DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateLessonContentModal'
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
    public function saveCreateLessonContentModal(string $DivisionCourseId, array $Data = null)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Die Klasse oder Gruppe wurde nicht gefunden', new Exclamation());
        }

        if (($form = Digital::useService()->checkFormLessonContent($Data, $tblDivisionCourse))) {
            // display Errors on form
            return $this->getLessonContentModal($form);
        }

        $lesson = intval($Data['Lesson']);
        // key -1 bei 0. UE
        if ($lesson == -1) {
            $lesson = 0;
        }
        if (($tblLessonContent = Digital::useService()->createLessonContent($Data, $lesson, $tblDivisionCourse))) {
            // bei Doppelstunde die Daten auch für die nächste UE speichern
            if (isset($Data['IsDoubleLesson']) && isset($Data['Lesson'])) {
                $lessonDouble = $lesson + 1;
                $tblLessonContentDouble = Digital::useService()->createLessonContent($Data, $lessonDouble, $tblDivisionCourse);
            } else {
                $lessonDouble = false;
                $tblLessonContentDouble = false;
            }

            // Thema/Hausaufgaben verknüpfen
            if (isset($Data['Link'])) {
                $LinkId = Digital::useService()->getNextLinkId();
                Digital::useService()->createLessonContentLink($tblLessonContent, $LinkId);

                // Doppelstunde -> extra Link
                if ($tblLessonContentDouble) {
                    $LinkDoubleId = $LinkId + 1;
                    Digital::useService()->createLessonContentLink($tblLessonContentDouble, $LinkDoubleId);
                } else {
                    $LinkDoubleId = false;
                }

                foreach ($Data['Link'] as $courseAddId => $value) {
                    if (($tblDivisionCourseToLink = DivisionCourse::useService()->getDivisionCourseById($courseAddId))
                        && ($tblLessonContentToLink = Digital::useService()->createLessonContent($Data, $lesson, $tblDivisionCourseToLink))
                    ) {
                        Digital::useService()->createLessonContentLink($tblLessonContentToLink, $LinkId);
                        // Doppelstunde
                        if ($tblLessonContentDouble
                            && ($tblLessonContentDoubleToLink = Digital::useService()->createLessonContent($Data, $lessonDouble, $tblDivisionCourseToLink))
                        ) {
                            Digital::useService()->createLessonContentLink($tblLessonContentDoubleToLink, $LinkDoubleId);
                        }
                    }
                }
            }

            return new Success('Thema/Hausaufgaben wurde erfolgreich gespeichert.')
                . self::pipelineLoadLessonContentContent($DivisionCourseId, $Data['Date'],
                    ($View = Consumer::useService()->getAccountSettingValue('LessonContentView')) ? $View : 'Day')
                . self::pipelineClose();
        } else {
            return new Danger('Thema/Hausaufgaben konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string $LessonContentId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditLessonContentModal(string $LessonContentId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditLessonContentModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'LessonContentId' => $LessonContentId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $LessonContentId
     *
     * @return Pipeline
     */
    public static function pipelineEditLessonContentSave(string $LessonContentId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditLessonContentModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'LessonContentId' => $LessonContentId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $LessonContentId
     *
     * @return string
     */
    public function openEditLessonContentModal($LessonContentId)
    {
        if (!($tblLessonContent = Digital::useService()->getLessonContentById($LessonContentId))) {
            return new Danger('Thema/Hausaufgaben wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivisionCourse = $tblLessonContent->getServiceTblDivisionCourse())) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }

        return $this->getLessonContentModal(Digital::useFrontend()->formLessonContent($tblDivisionCourse, $LessonContentId, true), $LessonContentId);
    }

    /**
     * @param $LessonContentId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveEditLessonContentModal($LessonContentId, $Data)
    {
        if (!($tblLessonContent = Digital::useService()->getLessonContentById($LessonContentId))) {
            return new Danger('Thema/Hausaufgaben wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivisionCourse = $tblLessonContent->getServiceTblDivisionCourse())) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }

        if (($form = Digital::useService()->checkFormLessonContent($Data, $tblDivisionCourse, $tblLessonContent))) {
            // display Errors on form
            return $this->getLessonContentModal($form, $LessonContentId);
        }

        if (Digital::useService()->updateLessonContent($tblLessonContent, $Data)) {
            if (($tblLessonContentLinkedList = $tblLessonContent->getLinkedLessonContentAll())) {
                foreach ($tblLessonContentLinkedList as $tblLessonContentItem) {
                    Digital::useService()->updateLessonContent($tblLessonContentItem, $Data);
                }
            }
            return new Success('Thema/Hausaufgaben wurde erfolgreich gespeichert.')
                . self::pipelineLoadLessonContentContent($tblDivisionCourse->getId(), $Data['Date'],
                    ($View = Consumer::useService()->getAccountSettingValue('LessonContentView')) ? $View : 'Day')
                . self::pipelineClose();
        } else {
            return new Danger('Thema/Hausaufgaben konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string $LessonContentId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteLessonContentModal(string $LessonContentId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteLessonContentModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'LessonContentId' => $LessonContentId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $LessonContentId
     *
     * @return string
     */
    public function openDeleteLessonContentModal(string $LessonContentId)
    {
        if (!($tblLessonContent = Digital::useService()->getLessonContentById($LessonContentId))) {
            return new Danger('Thema/Hausaufgaben wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Thema/Hausaufgaben löschen')
            . (($linkedPanel = Digital::useService()->getLessonContentLinkedDisplayPanel($LessonContentId)) ? : '')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diese Thema/Hausaufgaben wirklich löschen?',
                                array(
                                    $tblLessonContent->getDate(),
                                    $tblLessonContent->getLessonDisplay(),
                                    $tblLessonContent->getDisplaySubject(false),
                                    ($tblPerson = $tblLessonContent->getServiceTblPerson())
                                        ? $tblPerson->getFullName() : '',
                                    $tblLessonContent->getContent(),
                                    $tblLessonContent->getHomework(),
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . ($linkedPanel ? new Warning('Verknüpfte Thema/Hausaufgaben werden mit gelöscht.', new Exclamation()) : '')
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteLessonContentSave($LessonContentId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param string $LessonContentId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteLessonContentSave(string $LessonContentId): Pipeline
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteLessonContentModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'LessonContentId' => $LessonContentId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $LessonContentId
     *
     * @return Danger|string
     */
    public function saveDeleteLessonContentModal(string $LessonContentId)
    {
        if (!($tblLessonContent = Digital::useService()->getLessonContentById($LessonContentId))) {
            return new Danger('Thema/Hausaufgaben wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivisionCourse = $tblLessonContent->getServiceTblDivisionCourse())) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }
        $date = $tblLessonContent->getDate();

        if (Digital::useService()->destroyLessonContent($tblLessonContent)) {
            return new Success('Thema/Hausaufgaben wurde erfolgreich gelöscht.')
                . self::pipelineLoadLessonContentContent($tblDivisionCourse->getId(), $date,
                    ($View = Consumer::useService()->getAccountSettingValue('LessonContentView')) ? $View : 'Day')
                . self::pipelineClose();
        } else {
            return new Danger('Thema/Hausaufgaben konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string|null $DivisionCourseId
     * @param string|null $SubjectId
     *
     * @return Pipeline
     */
    public static function pipelineLoadLessonContentLinkPanel(string $DivisionCourseId, string $SubjectId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'LessonContentLinkPanel'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadLessonContentLinkPanel',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'SubjectId' => $SubjectId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $DivisionCourseId
     * @param string $SubjectId
     * @param null $Data
     *
     * @return string|null
     */
    public function loadLessonContentLinkPanel(string $DivisionCourseId, string $SubjectId, $Data = null)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }

        if (isset($Data['serviceTblSubject'])) {
            $tblSubject = Subject::useService()->getSubjectById($Data['serviceTblSubject']);
        } else {
            $tblSubject = Subject::useService()->getSubjectById($SubjectId);
        }

        if ($tblSubject) {
            return Digital::useService()->getLessonContentLinkPanel($tblDivisionCourse, $tblSubject);
        }

        return null;
    }

    /**
     * @param string $DivisionCourseId
     * @param string|null $hasDivisionTeacherRight
     * @param string|null $hasHeadmasterRight
     * @param string|null $Date
     *
     * @return Pipeline
     */
    public static function pipelineLoadLessonWeekContent(string $DivisionCourseId, string $hasDivisionTeacherRight = null,
        string $hasHeadmasterRight = null, string $Date = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'LessonWeekContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadLessonWeekContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Date' => $Date,
            'hasDivisionTeacherRight' => $hasDivisionTeacherRight,
            'hasHeadmasterRight' => $hasHeadmasterRight,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $DivisionCourseId
     * @param string|null $hasDivisionTeacherRight
     * @param string|null $hasHeadmasterRight
     * @param string|null $Date
     *
     * @return string
     */
    public function loadLessonWeekContent(string $DivisionCourseId, string $hasDivisionTeacherRight = null,
        string $hasHeadmasterRight = null, string $Date = null) : string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        return Digital::useFrontend()->loadLessonWeekTable($tblDivisionCourse, $hasDivisionTeacherRight == '1', $hasHeadmasterRight == '1', $Date);
    }

    /**
     * @param string|null $DivisionCourseId
     * @param string $Date
     * @param string $Type
     * @param string $Direction
     * @param string|null $hasDivisionTeacherRight
     * @param string|null $hasHeadmasterRight
     * @return Pipeline
     */
    public static function pipelineSaveLessonWeekCheck(string $DivisionCourseId, string $Date = '', string $Type = '',
        string $Direction = '', string $hasDivisionTeacherRight = null, string $hasHeadmasterRight = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'LessonWeekContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveLessonWeekCheck',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Date' => $Date,
            'Type' => $Type,
            'Direction' => $Direction,
            'hasDivisionTeacherRight' => $hasDivisionTeacherRight,
            'hasHeadmasterRight' => $hasHeadmasterRight
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $DivisionCourseId
     * @param string $Date
     * @param string $Type
     * @param string $Direction
     * @param string|null $hasDivisionTeacherRight
     * @param string|null $hasHeadmasterRight
     *
     * @return Pipeline
     */
    public function saveLessonWeekCheck(string $DivisionCourseId, string $Date = '', string $Type = '',
        string $Direction = '', string $hasDivisionTeacherRight = null, string $hasHeadmasterRight = null): Pipeline
    {
        $tblPerson = Account::useService()->getPersonByLogin();
        $Date = new DateTime($Date);
        $now = new DateTime('now');

        $tblLessonWeek = false;
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $tblLessonWeek = Digital::useService()->getLessonWeekByDate($tblDivisionCourse, $Date);
        }

        if ($Type == 'DivisionTeacher') {
            if ($Direction == 'SET') {
                $serviceTblPersonDivisionTeacher = $tblPerson;
                $DateDivisionTeacher = $now->format('d.m.Y');
            } else {
                // Bestätigung rückgängig machen
                $serviceTblPersonDivisionTeacher = null;
                $DateDivisionTeacher = '';
            }

            if ($tblLessonWeek) {
                $serviceTblPersonHeadmaster = $tblLessonWeek->getServiceTblPersonHeadmaster();
                $DateHeadmaster = $tblLessonWeek->getDateHeadmaster();
            } else {
                $serviceTblPersonHeadmaster = null;
                $DateHeadmaster = '';
            }
        } else {
            if ($Direction == 'SET') {
                $serviceTblPersonHeadmaster = $tblPerson;
                $DateHeadmaster = $now->format('d.m.Y');
            } else {
                // Bestätigung rückgängig machen
                $serviceTblPersonHeadmaster = null;
                $DateHeadmaster = '';
            }

            if ($tblLessonWeek) {
                $serviceTblPersonDivisionTeacher = $tblLessonWeek->getServiceTblPersonDivisionTeacher();
                $DateDivisionTeacher = $tblLessonWeek->getDateDivisionTeacher();
            } else {
                $serviceTblPersonDivisionTeacher = null;
                $DateDivisionTeacher = '';
            }
        }

        if ($tblLessonWeek) {
            Digital::useService()->updateLessonWeek($tblLessonWeek, $tblLessonWeek->getRemark(), $DateDivisionTeacher, $serviceTblPersonDivisionTeacher ?: null,
                $DateHeadmaster, $serviceTblPersonHeadmaster ?: null);
        } else {
            if ($tblDivisionCourse) {
                Digital::useService()->createLessonWeek($tblDivisionCourse, $Date->format('d.m.Y'), '', $DateDivisionTeacher,
                    $serviceTblPersonDivisionTeacher ?: null, $DateHeadmaster, $serviceTblPersonHeadmaster ?: null);
            }
        }

        return self::pipelineLoadLessonWeekContent($DivisionCourseId, $hasDivisionTeacherRight == '1', $hasHeadmasterRight == '1');
    }

    /**
     * @param string|null $DivisionCourseId
     * @param string|null $Date
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditLessonWeekRemarkModal(string $DivisionCourseId, string $Date = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditLessonWeekRemarkModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Date' => $Date,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionCourseId
     * @param string|null $Date
     *
     * @return string
     */
    public function openEditLessonWeekRemarkModal(string $DivisionCourseId = null, string $Date = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        return new Well(Digital::useFrontend()->formLessonWeekRemark($tblDivisionCourse, new DateTime($Date)));
    }

    /**
     * @param string|null $DivisionCourseId
     * @param string|null $Date
     *
     * @return Pipeline
     */
    public static function pipelineEditLessonWeekRemarkSave(string $DivisionCourseId = null, string $Date = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditLessonWeekRemarkModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Date' => $Date,
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }


    /**
     * @param string|null $DivisionCourseId
     * @param string|null $Date
     * @param $Data
     *
     * @return string
     */
    public function saveEditLessonWeekRemarkModal(string $DivisionCourseId = null, string $Date = null, $Data = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (($tblLessonWeek = Digital::useService()->getLessonWeekByDate($tblDivisionCourse, new DateTime($Date)))) {
            Digital::useService()->updateLessonWeekRemark($tblLessonWeek, $Data['Remark']);
        } else {
            Digital::useService()->createLessonWeek($tblDivisionCourse, $Date, $Data['Remark'], null, null, null, null);
        }

        return new Success('Wochenbemerkung wurde erfolgreich gespeichert.')
            . self::pipelineLoadLessonContentContent($tblDivisionCourse, $Date,
                ($View = Consumer::useService()->getAccountSettingValue('LessonContentView')) ? $View : 'Day')
            . self::pipelineClose();
    }

    /**
     * @param string|null $DivisionCourseId
     * @param string $IsControl
     *
     * @return Pipeline
     */
    public static function pipelineLoadCourseContentContent(string $DivisionCourseId = null, string $IsControl = 'false'): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CourseContentContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadCourseContentContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'IsControl' => $IsControl
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionCourseId
     * @param string $IsControl
     *
     * @return string
     */
    public function loadCourseContentContent(string $DivisionCourseId = null, string $IsControl = 'false') : string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der SekII-Kurs wurde nicht gefunden', new Exclamation());
        }

        return Digital::useFrontend()->loadCourseContentTable($tblDivisionCourse, $IsControl == 'true');
    }

    /**
     * @param string|null $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateCourseContentModal(string $DivisionCourseId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateCourseContentModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionCourseId
     *
     * @return string
     */
    public function openCreateCourseContentModal(string $DivisionCourseId = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der SekII-Kurs wurde nicht gefunden', new Exclamation());
        }

        return $this->getCourseContentModal(Digital::useFrontend()->formCourseContent($tblDivisionCourse, null, true));
    }

    /**
     * @param $form
     * @param string|null $CourseContentId
     *
     * @return string
     */
    private function getCourseContentModal($form, string $CourseContentId = null): string
    {
        if ($CourseContentId) {
            $title = new Title(new Edit() . ' Thema/Hausaufgaben bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Thema/Hausaufgaben hinzufügen');
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
    public static function pipelineCreateCourseContentSave(string $DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateCourseContentModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId
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
    public function saveCreateCourseContentModal(string $DivisionCourseId, array $Data = null)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der SekII-Kurs wurde nicht gefunden', new Exclamation());
        }

        if (($form = Digital::useService()->checkFormCourseContent($Data, $tblDivisionCourse))) {
            // display Errors on form
            return $this->getCourseContentModal($form);
        }

        if (Digital::useService()->createCourseContent($Data, $tblDivisionCourse)) {
            return new Success('Thema/Hausaufgaben wurde erfolgreich gespeichert.')
                . self::pipelineLoadCourseContentContent($DivisionCourseId)
                . self::pipelineClose();
        } else {
            return new Danger('Thema/Hausaufgaben konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string $CourseContentId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditCourseContentModal(string $CourseContentId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditCourseContentModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'CourseContentId' => $CourseContentId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $CourseContentId
     *
     * @return string
     */
    public function openEditCourseContentModal($CourseContentId)
    {
        if (!($tblCourseContent = Digital::useService()->getCourseContentById($CourseContentId))) {
            return new Danger('Thema/Hausaufgaben wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivisionCourse = $tblCourseContent->getServiceTblDivisionCourse())) {
            return new Danger('Der SekII-Kurs wurde nicht gefunden', new Exclamation());
        }

        return $this->getCourseContentModal(
            Digital::useFrontend()->formCourseContent($tblDivisionCourse, $CourseContentId, true),
            $CourseContentId
        );
    }

    /**
     * @param string $CourseContentId
     *
     * @return Pipeline
     */
    public static function pipelineEditCourseContentSave(string $CourseContentId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditCourseContentModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'CourseContentId' => $CourseContentId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $CourseContentId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveEditCourseContentModal($CourseContentId, $Data)
    {
        if (!($tblCourseContent = Digital::useService()->getCourseContentById($CourseContentId))) {
            return new Danger('Thema/Hausaufgaben wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivisionCourse = $tblCourseContent->getServiceTblDivisionCourse())) {
            return new Danger('Der SekII-Kurs wurde nicht gefunden', new Exclamation());
        }

        if (($form = Digital::useService()->checkFormCourseContent($Data, $tblDivisionCourse, $tblCourseContent))) {
            // display Errors on form
            return $this->getCourseContentModal($form, $CourseContentId);
        }

        if (Digital::useService()->updateCourseContent($tblCourseContent, $Data)) {
            return new Success('Thema/Hausaufgaben wurde erfolgreich gespeichert.')
                . self::pipelineLoadCourseContentContent($tblDivisionCourse->getId())
                . self::pipelineClose();
        } else {
            return new Danger('Thema/Hausaufgaben konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string $CourseContentId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteCourseContentModal(string $CourseContentId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteCourseContentModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'CourseContentId' => $CourseContentId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $CourseContentId
     *
     * @return string
     */
    public function openDeleteCourseContentModal(string $CourseContentId)
    {
        if (!($tblCourseContent = Digital::useService()->getCourseContentById($CourseContentId))) {
            return new Danger('Thema/Hausaufgaben wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Thema/Hausaufgaben löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diese Thema/Hausaufgaben wirklich löschen?',
                                array(
                                    $tblCourseContent->getDate(),
                                    $tblCourseContent->getLessonDisplay(),
                                    ($tblPerson = $tblCourseContent->getServiceTblPerson())
                                        ? $tblPerson->getFullName() : '',
                                    $tblCourseContent->getContent(),
                                    $tblCourseContent->getHomework(),
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteCourseContentSave($CourseContentId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param string $CourseContentId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteCourseContentSave(string $CourseContentId): Pipeline
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteCourseContentModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'CourseContentId' => $CourseContentId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $CourseContentId
     *
     * @return Danger|string
     */
    public function saveDeleteCourseContentModal(string $CourseContentId)
    {
        if (!($tblCourseContent = Digital::useService()->getCourseContentById($CourseContentId))) {
            return new Danger('Thema/Hausaufgaben wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivisionCourse = $tblCourseContent->getServiceTblDivisionCourse())) {
            return new Danger('Der SekII-Kurs wurde nicht gefunden', new Exclamation());
        }

        if (Digital::useService()->destroyCourseContent($tblCourseContent)) {
            return new Success('Thema/Hausaufgaben wurde erfolgreich gelöscht.')
                . self::pipelineLoadCourseContentContent($tblDivisionCourse->getId())
                . self::pipelineClose();
        } else {
            return new Danger('Thema/Hausaufgaben konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineLoadCourseMissingStudentContent($DivisionCourseId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CourseMissingStudentContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadCourseMissingStudentContent',
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
    public function loadCourseMissingStudentContent($DivisionCourseId) : string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der SekII-Kurs wurde nicht gefunden', new Exclamation());
        }

        return Digital::useFrontend()->loadCourseMissingStudentContent($tblDivisionCourse);
    }
}