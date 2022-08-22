<?php

namespace SPHERE\Application\Api\Education\ClassRegister;

use DateTime;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
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
     * @param string|null $DivisionId
     * @param string|null $GroupId
     * @param string $Date
     * @param string $View
     *
     * @return Pipeline
     */
    public static function pipelineLoadLessonContentContent(string $DivisionId = null, string $GroupId = null,
        string $Date = 'today', string $View = 'Day'): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'LessonContentContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadLessonContentContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'GroupId' => $GroupId,
            'Date' => $Date,
            'View' => $View
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $GroupId
     * @param string $Date
     * @param string $View
     *
     * @return string
     */
    public function loadLessonContentContent(string $DivisionId = null, string $GroupId = null,
        string $Date = 'today', string $View = 'Day', $Data = null) : string
    {
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        if (!($tblDivision || $tblGroup)) {
            return new Danger('Die Klasse oder Gruppe wurde nicht gefunden', new Exclamation());
        }

        if (isset($Data['Date'])) {
            $Date = $Data['Date'];
        }

        // View speichern
        Consumer::useService()->createAccountSetting('LessonContentView', $View);

        return Digital::useFrontend()->loadLessonContentTable($tblDivision ?: null, $tblGroup ?: null, $Date, $View);
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $GroupId
     * @param string|null $Date
     * @param string|null $Lesson
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateLessonContentModal(string $DivisionId = null, string $GroupId = null,
        string $Date = null, string $Lesson = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateLessonContentModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'GroupId' => $GroupId,
            'Date' => $Date,
            'Lesson' => $Lesson
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $GroupId
     * @param string|null $Date
     * @param string|null $Lesson
     *
     * @return string
     */
    public function openCreateLessonContentModal(string $DivisionId = null, string $GroupId = null,
        string $Date = null, string $Lesson = null): string
    {
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        if (!($tblDivision || $tblGroup)) {
            return new Danger('Die Klasse oder Gruppe wurde nicht gefunden', new Exclamation());
        }

        return $this->getLessonContentModal(Digital::useFrontend()->formLessonContent($tblDivision ?: null,
            $tblGroup ?: null, null, false, $Date, $Lesson));
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
     * @param string|null $DivisionId
     * @param string|null $GroupId
     *
     * @return Pipeline
     */
    public static function pipelineCreateLessonContentSave(string $DivisionId = null, string $GroupId = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateLessonContentModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'GroupId' => $GroupId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $GroupId
     * @param array|null $Data
     *
     * @return Danger|string
     */
    public function saveCreateLessonContentModal(string $DivisionId = null, string $GroupId = null, array $Data = null)
    {
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        if (!($tblDivision || $tblGroup)) {
            return new Danger('Die Klasse oder Gruppe wurde nicht gefunden', new Exclamation());
        }

        if (($form = Digital::useService()->checkFormLessonContent($Data, $tblDivision ?: null, $tblGroup ?: null))) {
            // display Errors on form
            return $this->getLessonContentModal($form);
        }

        $lesson = intval($Data['Lesson']);
        // key -1 bei 0. UE
        if ($lesson == -1) {
            $lesson = 0;
        }
        if (($tblLessonContent = Digital::useService()->createLessonContent($Data, $lesson, $tblDivision ?: null, $tblGroup ?: null))) {
            // bei Doppelstunde die Daten auch für die nächste UE speichern
            if (isset($Data['IsDoubleLesson']) && isset($Data['Lesson'])) {
                $lessonDouble = $lesson + 1;
                $tblLessonContentDouble = Digital::useService()->createLessonContent($Data, $lessonDouble, $tblDivision ?: null, $tblGroup ?: null);
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
                    if ($tblDivision) {
                        if (($tblDivisionToLink = Division::useService()->getDivisionById($courseAddId))
                            && ($tblLessonContentToLink = Digital::useService()->createLessonContent($Data, $lesson, $tblDivisionToLink, null))
                        ) {
                            Digital::useService()->createLessonContentLink($tblLessonContentToLink, $LinkId);
                            // Doppelstunde
                            if ($tblLessonContentDouble
                                && ($tblLessonContentDoubleToLink = Digital::useService()->createLessonContent($Data, $lessonDouble, $tblDivisionToLink, null))
                            ) {
                                Digital::useService()->createLessonContentLink($tblLessonContentDoubleToLink, $LinkDoubleId);
                            }
                        }
                    } elseif ($tblGroup) {
                        if (($tblGroupToLink = Group::useService()->getGroupById($courseAddId))
                            && ($tblLessonContentToLink = Digital::useService()->createLessonContent($Data, $lesson, null, $tblGroupToLink))
                        ) {
                            Digital::useService()->createLessonContentLink($tblLessonContentToLink, $LinkId);
                            // Doppelstunde
                            if ($tblLessonContentDouble
                                && ($tblLessonContentDoubleToLink = Digital::useService()->createLessonContent($Data, $lessonDouble, null, $tblGroupToLink))
                            ) {
                                Digital::useService()->createLessonContentLink($tblLessonContentDoubleToLink, $LinkDoubleId);
                            }
                        }
                    }
                }
            }

            return new Success('Thema/Hausaufgaben wurde erfolgreich gespeichert.')
                . self::pipelineLoadLessonContentContent($DivisionId, $GroupId, $Data['Date'],
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
        $tblDivision = $tblLessonContent->getServiceTblDivision();
        $tblGroup = $tblLessonContent->getServiceTblGroup();

        return $this->getLessonContentModal(Digital::useFrontend()->formLessonContent(
            $tblDivision ?: null, $tblGroup ?: null, $LessonContentId, true
        ), $LessonContentId);
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
        $tblDivision = $tblLessonContent->getServiceTblDivision();
        $tblGroup = $tblLessonContent->getServiceTblGroup();

        if (($form = Digital::useService()->checkFormLessonContent($Data, $tblDivision ?: null, $tblGroup ?: null, $tblLessonContent))) {
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
                . self::pipelineLoadLessonContentContent($tblDivision ? $tblDivision->getId() : null,
                    $tblGroup ? $tblGroup->getId() : null, $Data['Date'],
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
        $date = $tblLessonContent->getDate();
        $tblDivision = $tblLessonContent->getServiceTblDivision();
        $tblGroup = $tblLessonContent->getServiceTblGroup();

        if (Digital::useService()->destroyLessonContent($tblLessonContent)) {
            return new Success('Thema/Hausaufgaben wurde erfolgreich gelöscht.')
                . self::pipelineLoadLessonContentContent($tblDivision ? $tblDivision->getId() : null,
                    $tblGroup ? $tblGroup->getId() : null, $date,
                    ($View = Consumer::useService()->getAccountSettingValue('LessonContentView')) ? $View : 'Day')
                . self::pipelineClose();
        } else {
            return new Danger('Thema/Hausaufgaben konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $GroupId
     * @param string|null $SubjectId
     *
     * @return Pipeline
     */
    public static function pipelineLoadLessonContentLinkPanel(string $DivisionId = null, string $GroupId = null, string $SubjectId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'LessonContentLinkPanel'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadLessonContentLinkPanel',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'GroupId' => $GroupId,
            'SubjectId' => $SubjectId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $GroupId
     * @param string|null $SubjectId
     * @param null $Data
     *
     * @return string|null
     */
    public function loadLessonContentLinkPanel(string $DivisionId = null, string $GroupId = null, string $SubjectId = null, $Data = null)
    {
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        if (!($tblDivision || $tblGroup)) {
            return new Danger('Die Klasse oder Gruppe wurde nicht gefunden', new Exclamation());
        }

        if (isset($Data['serviceTblSubject'])) {
            $tblSubject = Subject::useService()->getSubjectById($Data['serviceTblSubject']);
        } else {
            $tblSubject = Subject::useService()->getSubjectById($SubjectId);
        }

        if ($tblSubject) {
            return Digital::useService()->getLessonContentLinkPanel($tblDivision ?: null, $tblGroup ?: null, $tblSubject);
        }

        return null;
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $GroupId
     * @param string|null $hasDivisionTeacherRight
     * @param string|null $hasHeadmasterRight
     * @param string|null $Date
     *
     * @return Pipeline
     */
    public static function pipelineLoadLessonWeekContent(string $DivisionId = null, string $GroupId = null, string $hasDivisionTeacherRight = null,
        string $hasHeadmasterRight = null, string $Date = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'LessonWeekContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadLessonWeekContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'GroupId' => $GroupId,
            'Date' => $Date,
            'hasDivisionTeacherRight' => $hasDivisionTeacherRight,
            'hasHeadmasterRight' => $hasHeadmasterRight,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $GroupId
     * @param string|null $hasDivisionTeacherRight
     * @param string|null $hasHeadmasterRight
     * @param string|null $Date
     *
     * @return string
     */
    public function loadLessonWeekContent(string $DivisionId = null, string $GroupId = null, string $hasDivisionTeacherRight = null,
        string $hasHeadmasterRight = null, string $Date = null) : string
    {
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        if (!($tblDivision || $tblGroup)) {
            return new Danger('Die Klasse oder Gruppe wurde nicht gefunden', new Exclamation());
        }

        return Digital::useFrontend()->loadLessonWeekTable($tblDivision ?: null, $tblGroup ?: null, $hasDivisionTeacherRight == '1',
            $hasHeadmasterRight == '1', $Date);
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $GroupId
     * @param string|null $YearId
     * @param string $Date
     * @param string $Type
     * @param string $Direction
     * @param string|null $hasDivisionTeacherRight
     * @param string|null $hasHeadmasterRight
     * @return Pipeline
     */
    public static function pipelineSaveLessonWeekCheck(string $DivisionId = null, string $GroupId = null, string $YearId = null, string $Date = '',
        string $Type = '', string $Direction = '', string $hasDivisionTeacherRight = null, string $hasHeadmasterRight = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'LessonWeekContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveLessonWeekCheck',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'GroupId' => $GroupId,
            'YearId' => $YearId,
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
     * @param string|null $DivisionId
     * @param string|null $GroupId
     * @param string|null $YearId
     * @param string $Date
     * @param string $Type
     * @param string $Direction
     * @param string|null $hasDivisionTeacherRight
     * @param string|null $hasHeadmasterRight
     *
     * @return Pipeline
     */
    public function saveLessonWeekCheck(string $DivisionId = null, string $GroupId = null, string $YearId = null, string $Date = '', string $Type = '',
        string $Direction = '', string $hasDivisionTeacherRight = null, string $hasHeadmasterRight = null): Pipeline
    {
        $tblPerson = Account::useService()->getPersonByLogin();
        $Date = new DateTime($Date);
        $now = new DateTime('now');

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);
        $tblYear = Term::useService()->getYearById($YearId);
        $tblLessonWeek = Digital::useService()->getLessonWeekByDate($tblDivision ?: null, $tblGroup ?: null, $Date);

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
            Digital::useService()->createLessonWeek($tblDivision ?: null, $tblGroup ?: null, $tblYear, $Date->format('d.m.Y'), '', $DateDivisionTeacher,
                $serviceTblPersonDivisionTeacher ?: null, $DateHeadmaster, $serviceTblPersonHeadmaster ?: null);
        }

        return self::pipelineLoadLessonWeekContent($DivisionId, $GroupId, $hasDivisionTeacherRight == '1', $hasHeadmasterRight == '1');
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $GroupId
     * @param string|null $Date
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditLessonWeekRemarkModal(string $DivisionId = null, string $GroupId = null, string $Date = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditLessonWeekRemarkModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'GroupId' => $GroupId,
            'Date' => $Date,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $GroupId
     * @param string|null $Date
     *
     * @return string
     */
    public function openEditLessonWeekRemarkModal(string $DivisionId = null, string $GroupId = null, string $Date = null): string
    {
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        if (!($tblDivision || $tblGroup)) {
            return new Danger('Die Klasse oder Gruppe wurde nicht gefunden', new Exclamation());
        }

        return new Well(Digital::useFrontend()->formLessonWeekRemark($tblDivision ?: null, $tblGroup ?: null, new DateTime($Date)));
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $GroupId
     * @param string|null $Date
     *
     * @return Pipeline
     */
    public static function pipelineEditLessonWeekRemarkSave(string $DivisionId = null, string $GroupId = null, string $Date = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditLessonWeekRemarkModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'GroupId' => $GroupId,
            'Date' => $Date,
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }


    /**
     * @param string|null $DivisionId
     * @param string|null $GroupId
     * @param string|null $Date
     * @param $Data
     *
     * @return string
     */
    public function saveEditLessonWeekRemarkModal(string $DivisionId = null, string $GroupId = null, string $Date = null, $Data = null): string
    {
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        if (!($tblDivision || $tblGroup)) {
            return new Danger('Die Klasse oder Gruppe wurde nicht gefunden', new Exclamation());
        }

        if ($tblDivision) {
            $tblYear = $tblDivision->getServiceTblYear();
        } else {
            $tblYear = $tblGroup->getCurrentYear();
        }

        if ($tblYear) {
            if (($tblLessonWeek = Digital::useService()->getLessonWeekByDate($tblDivision ?: null, $tblGroup ?: null, new DateTime($Date)))) {
                Digital::useService()->updateLessonWeekRemark($tblLessonWeek, $Data['Remark']);
            } else {
                Digital::useService()->createLessonWeek($tblDivision ?: null, $tblGroup ?: null, $tblYear, $Date, $Data['Remark'], null, null, null, null);
            }
        }

        return new Success('Wochenbemerkung wurde erfolgreich gespeichert.')
            . self::pipelineLoadLessonContentContent($tblDivision ? $tblDivision->getId() : null, $tblGroup ? $tblGroup->getId() : null, $Date,
                ($View = Consumer::useService()->getAccountSettingValue('LessonContentView')) ? $View : 'Day')
            . self::pipelineClose();
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $SubjectId
     * @param string|null $SubjectGroupId
     * @param string $IsControl
     *
     * @return Pipeline
     */
    public static function pipelineLoadCourseContentContent(string $DivisionId = null, string $SubjectId = null, string $SubjectGroupId = null,
        string $IsControl = 'false'): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CourseContentContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadCourseContentContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'SubjectId' => $SubjectId,
            'SubjectGroupId' => $SubjectGroupId,
            'IsControl' => $IsControl
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $SubjectId
     * @param string|null $SubjectGroupId
     * @param string $IsControl
     *
     * @return string
     */
    public function loadCourseContentContent(string $DivisionId = null, string $SubjectId = null, string $SubjectGroupId = null, string $IsControl = 'false') : string
    {
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblSubject = Subject::useService()->getSubjectById($SubjectId);
        $tblSubjectGroup = Division::useService()->getSubjectGroupById($SubjectGroupId);

        if (!($tblDivision || $tblSubjectGroup || $tblSubject)) {
            return new Danger('Der SekII-Kurs wurde nicht gefunden', new Exclamation());
        }

        return Digital::useFrontend()->loadCourseContentTable($tblDivision, $tblSubject, $tblSubjectGroup, $IsControl == 'true');
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $SubjectId
     * @param string|null $SubjectGroupId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateCourseContentModal(string $DivisionId = null, string $SubjectId = null,
        string $SubjectGroupId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateCourseContentModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'SubjectGroupId' => $SubjectGroupId,
            'SubjectId' => $SubjectId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $SubjectId
     * @param string|null $SubjectGroupId
     *
     * @return string
     */
    public function openCreateCourseContentModal(string $DivisionId = null, string $SubjectId = null,
        string $SubjectGroupId = null): string
    {
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblSubject = Subject::useService()->getSubjectById($SubjectId);
        $tblSubjectGroup = Division::useService()->getSubjectGroupById($SubjectGroupId);

        if (!($tblDivision || $tblSubjectGroup || $tblSubject)) {
            return new Danger('Der SekII-Kurs wurde nicht gefunden', new Exclamation());
        }

        return $this->getCourseContentModal(Digital::useFrontend()->formCourseContent($tblDivision, $tblSubject,
            $tblSubjectGroup, null, true));
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
     * @param string|null $DivisionId
     * @param string|null $SubjectId
     * @param string|null $SubjectGroupId
     *
     * @return Pipeline
     */
    public static function pipelineCreateCourseContentSave(string $DivisionId = null, string $SubjectId = null,
        string $SubjectGroupId = null): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateCourseContentModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'SubjectGroupId' => $SubjectGroupId,
            'SubjectId' => $SubjectId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionId
     * @param string|null $SubjectId
     * @param string|null $SubjectGroupId
     * @param array|null $Data
     *
     * @return Danger|string
     */
    public function saveCreateCourseContentModal(string $DivisionId = null, string $SubjectId = null,
        string $SubjectGroupId = null, array $Data = null)
    {
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblSubject = Subject::useService()->getSubjectById($SubjectId);
        $tblSubjectGroup = Division::useService()->getSubjectGroupById($SubjectGroupId);

        if (!($tblDivision || $tblSubject || $tblSubjectGroup)) {
            return new Danger('Der SekII-Kurs wurde nicht gefunden', new Exclamation());
        }

        if (($form = Digital::useService()->checkFormCourseContent($Data, $tblDivision, $tblSubject, $tblSubjectGroup))) {
            // display Errors on form
            return $this->getCourseContentModal($form);
        }

        if (Digital::useService()->createCourseContent($Data, $tblDivision, $tblSubject, $tblSubjectGroup)) {
            return new Success('Thema/Hausaufgaben wurde erfolgreich gespeichert.')
                . self::pipelineLoadCourseContentContent($DivisionId, $SubjectId, $SubjectGroupId)
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

        $tblDivision = $tblCourseContent->getServiceTblDivision();
        $tblSubject = $tblCourseContent->getServiceTblSubject();
        $tblSubjectGroup = $tblCourseContent->getServiceTblSubjectGroup();
        if (!($tblDivision || $tblSubject || $tblSubjectGroup)) {
            return new Danger('Der SekII-Kurs wurde nicht gefunden', new Exclamation());
        }

        return $this->getCourseContentModal(Digital::useFrontend()->formCourseContent(
            $tblDivision, $tblSubject, $tblSubjectGroup, $CourseContentId, true
        ), $CourseContentId);
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

        $tblDivision = $tblCourseContent->getServiceTblDivision();
        $tblSubject = $tblCourseContent->getServiceTblSubject();
        $tblSubjectGroup = $tblCourseContent->getServiceTblSubjectGroup();
        if (!($tblDivision || $tblSubject || $tblSubjectGroup)) {
            return new Danger('Der SekII-Kurs wurde nicht gefunden', new Exclamation());
        }

        if (($form = Digital::useService()->checkFormCourseContent($Data, $tblDivision, $tblSubject, $tblSubjectGroup, $tblCourseContent))) {
            // display Errors on form
            return $this->getCourseContentModal($form, $CourseContentId);
        }

        if (Digital::useService()->updateCourseContent($tblCourseContent, $Data)) {
            return new Success('Thema/Hausaufgaben wurde erfolgreich gespeichert.')
                . self::pipelineLoadCourseContentContent($tblDivision->getId(), $tblSubject->getId(), $tblSubjectGroup->getId())
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
                                    ($tblSubject = $tblCourseContent->getServiceTblSubject())
                                        ? $tblSubject->getDisplayName() : '',
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

        $tblDivision = $tblCourseContent->getServiceTblDivision();
        $tblSubject = $tblCourseContent->getServiceTblSubject();
        $tblSubjectGroup = $tblCourseContent->getServiceTblSubjectGroup();
        if (!($tblDivision || $tblSubject || $tblSubjectGroup)) {
            return new Danger('Der SekII-Kurs wurde nicht gefunden', new Exclamation());
        }

        if (Digital::useService()->destroyCourseContent($tblCourseContent)) {
            return new Success('Thema/Hausaufgaben wurde erfolgreich gelöscht.')
                . self::pipelineLoadCourseContentContent($tblDivision->getId(), $tblSubject->getId(), $tblSubjectGroup->getId())
                . self::pipelineClose();
        } else {
            return new Danger('Thema/Hausaufgaben konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string|null $DivisionSubjectId
     *
     * @return Pipeline
     */
    public static function pipelineLoadCourseMissingStudentContent(string $DivisionSubjectId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CourseMissingStudentContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadCourseMissingStudentContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionSubjectId' => $DivisionSubjectId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionSubjectId
     *
     * @return string
     */
    public function loadCourseMissingStudentContent(string $DivisionSubjectId = null) : string
    {
        if (!($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            return new Danger('Der SekII-Kurs wurde nicht gefunden', new Exclamation());
        }

        return Digital::useFrontend()->loadCourseMissingStudentContent($tblDivisionSubject);
    }
}