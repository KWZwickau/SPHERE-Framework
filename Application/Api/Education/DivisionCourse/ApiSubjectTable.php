<?php

namespace SPHERE\Application\Api\Education\DivisionCourse;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\School\Type\Type;
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

class ApiSubjectTable extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadSubjectTableContent');

        $Dispatcher->registerMethod('openCreateSubjectTableModal');
        $Dispatcher->registerMethod('saveCreateSubjectTableModal');
        $Dispatcher->registerMethod('openEditSubjectTableModal');
        $Dispatcher->registerMethod('saveEditSubjectTableModal');
        $Dispatcher->registerMethod('openDeleteSubjectTableModal');
        $Dispatcher->registerMethod('saveDeleteSubjectTableModal');

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
     * @param null $SchoolTypeId
     *
     * @return Pipeline
     */
    public static function pipelineLoadSubjectTableContent($SchoolTypeId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SubjectTableContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadSubjectTableContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $SchoolTypeId
     *
     * @return string
     */
    public function loadSubjectTableContent($SchoolTypeId = null): string
    {
        return DivisionCourse::useFrontend()->loadSubjectTableContent($SchoolTypeId);
    }

    /**
     * @param $SchoolTypeId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateSubjectTableModal($SchoolTypeId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateSubjectTableModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SchoolTypeId
     *
     * @return string
     */
    public function openCreateSubjectTableModal($SchoolTypeId = null): string
    {
        return $this->getSubjectTableModal(DivisionCourse::useFrontend()->formSubjectTable(null, $SchoolTypeId, false));
    }

    /**
     * @param $form
     * @param string|null $SubjectTableId
     *
     * @return string
     */
    private function getSubjectTableModal($form, string $SubjectTableId = null): string
    {
        if ($SubjectTableId) {
            $title = new Title(new Edit() . ' Eintrag bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Eintrag hinzufügen');
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
     * @param null $SchoolTypeId
     *
     * @return Pipeline
     */
    public static function pipelineCreateSubjectTableSave($SchoolTypeId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateSubjectTableModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SchoolTypeId
     * @param array|null $Data
     *
     * @return string
     */
    public function saveCreateSubjectTableModal($SchoolTypeId, array $Data = null): string
    {
        if (!($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))) {
            return new Danger('Schulart nicht gefunden', new Exclamation()) . self::pipelineClose();
        }

        if (($form = DivisionCourse::useService()->checkFormSubjectTable($SchoolTypeId, $Data))) {
            // display Errors on form
            return $this->getSubjectTableModal($form);
        }

        if (DivisionCourse::useService()->createSubjectTable($tblSchoolType, $Data)) {
            return new Success('Eintrag wurde erfolgreich gespeichert.')
                . self::pipelineLoadSubjectTableContent($SchoolTypeId)
                . self::pipelineClose();
        } else {
            return new Danger('Eintrag konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $SubjectTableId
     * @param $SchoolTypeId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditSubjectTableModal($SubjectTableId, $SchoolTypeId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditSubjectTableModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'SubjectTableId' => $SubjectTableId,
            'SchoolTypeId' => $SchoolTypeId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SubjectTableId
     * @param $SchoolTypeId
     *
     * @return string
     */
    public function openEditSubjectTableModal($SubjectTableId, $SchoolTypeId)
    {
        if (!(DivisionCourse::useService()->getSubjectTableById($SubjectTableId))) {
            return new Danger('Der Eintrag wurde nicht gefunden', new Exclamation());
        }

        return $this->getSubjectTableModal(DivisionCourse::useFrontend()->formSubjectTable($SubjectTableId, $SchoolTypeId, true), $SubjectTableId);
    }

    /**
     * @param $SubjectTableId
     * @param $SchoolTypeId
     *
     * @return Pipeline
     */
    public static function pipelineEditSubjectTableSave($SubjectTableId, $SchoolTypeId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditSubjectTableModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'SubjectTableId' => $SubjectTableId,
            'SchoolTypeId' => $SchoolTypeId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SubjectTableId
     * @param $SchoolTypeId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveEditSubjectTableModal($SubjectTableId, $SchoolTypeId, $Data)
    {
        if (!($tblSubjectTable = DivisionCourse::useService()->getSubjectTableById($SubjectTableId))) {
            return new Danger('Der Eintrag wurde nicht gefunden', new Exclamation());
        }

        if (($form = DivisionCourse::useService()->checkFormSubjectTable($SchoolTypeId, $Data, $tblSubjectTable))) {
            // display Errors on form
            return $this->getSubjectTableModal($form, $SubjectTableId);
        }

        if (DivisionCourse::useService()->updateSubjectTable($tblSubjectTable, $Data)) {
            return new Success('Der Eintrag wurde erfolgreich gespeichert.')
                . self::pipelineLoadSubjectTableContent($SchoolTypeId)
                . self::pipelineClose();
        } else {
            return new Danger('Der Eintrag konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param $SubjectTableId
     * @param $SchoolTypeId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteSubjectTableModal($SubjectTableId, $SchoolTypeId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteSubjectTableModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'SubjectTableId' => $SubjectTableId,
            'SchoolTypeId' => $SchoolTypeId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SubjectTableId
     * @param $SchoolTypeId
     *
     * @return string
     */
    public function openDeleteSubjectTableModal($SubjectTableId, $SchoolTypeId)
    {
        if (!($tblSubjectTable = DivisionCourse::useService()->getSubjectTableById($SubjectTableId))) {
            return new Danger('Der Eintrag wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Eintrag löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diesen Eintrag wirklich löschen?',
                                array(
                                    'Klassenstufe: ' . $tblSubjectTable->getLevel(),
                                    'Typ: ' . $tblSubjectTable->getTypeName(),
                                    'Fach: ' . $tblSubjectTable->getSubjectName(),
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteSubjectTableSave($SubjectTableId, $SchoolTypeId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $SubjectTableId
     * @param $SchoolTypeId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteSubjectTableSave($SubjectTableId, $SchoolTypeId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteSubjectTableModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'SubjectTableId' => $SubjectTableId,
            'SchoolTypeId' => $SchoolTypeId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SubjectTableId
     * @param $SchoolTypeId
     *
     * @return string
     */
    public function saveDeleteSubjectTableModal($SubjectTableId, $SchoolTypeId): string
    {
        if (!($tblSubjectTable = DivisionCourse::useService()->getSubjectTableById($SubjectTableId))) {
            return new Danger('Der Eintrag wurde nicht gefunden', new Exclamation());
        }

        if (DivisionCourse::useService()->destroySubjectTable($tblSubjectTable)) {
            return new Success('Der Eintrag wurde erfolgreich gelöscht.')
                . self::pipelineLoadSubjectTableContent($SchoolTypeId)
                . self::pipelineClose();
        } else {
            return new Danger('Der Eintrag konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }
}