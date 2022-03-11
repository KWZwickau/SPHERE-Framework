<?php

namespace SPHERE\Application\Api\Education\ClassRegister;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
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
        string $Date = 'today', string $View = 'Day') : string
    {
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        if (!($tblDivision || $tblGroup)) {
            return new Danger('Die Klasse oder Gruppe wurde nicht gefunden', new Exclamation());
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
            $title = new Title(new Edit() . ' Unterrichtseinheit bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Unterrichtseinheit hinzufügen');
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

        if (Digital::useService()->createLessonContent($Data, $tblDivision ?: null, $tblGroup ?: null)) {
            return new Success('Die Unterrichtseinheit wurde erfolgreich gespeichert.')
                . self::pipelineLoadLessonContentContent($DivisionId, $GroupId, $Data['Date'],
                    ($View = Consumer::useService()->getAccountSettingValue('LessonContentView')) ? $View : 'Day')
                . self::pipelineClose();
        } else {
            return new Danger('Die Unterrichtseinheit konnte nicht gespeichert werden.') . self::pipelineClose();
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
            return new Danger('Die Unterrichtseinheit wurde nicht gefunden', new Exclamation());
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
            return new Danger('Die Unterrichtseinheit wurde nicht gefunden', new Exclamation());
        }
        $tblDivision = $tblLessonContent->getServiceTblDivision();
        $tblGroup = $tblLessonContent->getServiceTblGroup();

        if (($form = Digital::useService()->checkFormLessonContent($Data, $tblDivision ?: null, $tblGroup ?: null, $tblLessonContent))) {
            // display Errors on form
            return $this->getLessonContentModal($form, $LessonContentId);
        }

        if (Digital::useService()->updateLessonContent($tblLessonContent, $Data)) {
            return new Success('Die Unterrichtseinheit wurde erfolgreich gespeichert.')
                . self::pipelineLoadLessonContentContent($tblDivision ? $tblDivision->getId() : null,
                    $tblGroup ? $tblGroup->getId() : null, $Data['Date'],
                    ($View = Consumer::useService()->getAccountSettingValue('LessonContentView')) ? $View : 'Day')
                . self::pipelineClose();
        } else {
            return new Danger('Die Unterrichtseinheit konnte nicht gespeichert werden.') . self::pipelineClose();
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
            return new Danger('Die Unterrichtseinheit wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Unterrichtseinheit löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diese Unterrichtseinheit wirklich löschen?',
                                array(
                                    $tblLessonContent->getDate(),
                                    $tblLessonContent->getLessonDisplay(),
                                    ($tblSubject = $tblLessonContent->getServiceTblSubject())
                                        ? $tblSubject->getDisplayName() : '',
                                    ($tblPerson = $tblLessonContent->getServiceTblPerson())
                                        ? $tblPerson->getFullName() : '',
                                    $tblLessonContent->getContent(),
                                    $tblLessonContent->getHomework(),
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
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
            return new Danger('Die Unterrichtseinheit wurde nicht gefunden', new Exclamation());
        }
        $date = $tblLessonContent->getDate();
        $tblDivision = $tblLessonContent->getServiceTblDivision();
        $tblGroup = $tblLessonContent->getServiceTblGroup();

        if (Digital::useService()->destroyLessonContent($tblLessonContent)) {
            return new Success('Die Unterrichtseinheit wurde erfolgreich gelöscht.')
                . self::pipelineLoadLessonContentContent($tblDivision ? $tblDivision->getId() : null,
                    $tblGroup ? $tblGroup->getId() : null, $date,
                    ($View = Consumer::useService()->getAccountSettingValue('LessonContentView')) ? $View : 'Day')
                . self::pipelineClose();
        } else {
            return new Danger('Die Unterrichtseinheit konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }
}