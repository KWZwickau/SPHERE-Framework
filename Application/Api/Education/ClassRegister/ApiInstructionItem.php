<?php

namespace SPHERE\Application\Api\Education\ClassRegister;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\ClassRegister\Instruction\Instruction;
use SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity\TblInstruction;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
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

class ApiInstructionItem extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadInstructionItemContent');
        $Dispatcher->registerMethod('openCreateInstructionItemModal');
        $Dispatcher->registerMethod('saveCreateInstructionItemModal');
        $Dispatcher->registerMethod('openEditInstructionItemModal');
        $Dispatcher->registerMethod('saveEditInstructionItemModal');
        $Dispatcher->registerMethod('openDeleteInstructionItemModal');
        $Dispatcher->registerMethod('saveDeleteInstructionItemModal');

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
     *
     * @return Pipeline
     */
    public static function pipelineLoadInstructionItemContent(string $DivisionCourseId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'InstructionItemContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadInstructionItemContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionCourseId
     *
     * @return string
     */
    public function loadInstructionItemContent(string $DivisionCourseId = null): string
    {
        if (!(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId)))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        return Instruction::useFrontend()->loadInstructionItemTable($tblDivisionCourse);
    }

    /**
     * @param string|null $DivisionCourseId
     * @param string|null $InstructionId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateInstructionItemModal(string $DivisionCourseId = null, string $InstructionId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateInstructionItemModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'InstructionId' => $InstructionId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string|null $DivisionCourseId
     * @param string|null $InstructionId
     *
     * @return string
     */
    public function openCreateInstructionItemModal(string $DivisionCourseId = null, string $InstructionId = null): string
    {
        if (!(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId)))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (!($tblInstruction = Instruction::useService()->getInstructionById($InstructionId))) {
            return new Danger('Die Belehrung wurde nicht gefunden', new Exclamation());
        }

        return $this->getInstructionItemModal(Instruction::useFrontend()->formInstructionItem($tblDivisionCourse, $tblInstruction, null, false), $tblInstruction);
    }

    /**
     * @param $form
     * @param TblInstruction $tblInstruction
     * @param string|null $InstructionItemId
     *
     * @return string
     */
    private function getInstructionItemModal($form, TblInstruction $tblInstruction, string $InstructionItemId = null): string
    {
        if ($InstructionItemId) {
            $title = new Title(new Edit() . ' Belehrung bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Belehrung hinzufügen');
        }

        return $title
            . new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Thema', $tblInstruction->getSubject(), Panel::PANEL_TYPE_INFO)
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                $form
                            )
                        )
                    )
                ))
            ));
    }

    /**
     * @param string $DivisionCourseId
     * @param string $InstructionId
     *
     * @return Pipeline
     */
    public static function pipelineCreateInstructionItemSave(string $DivisionCourseId, string $InstructionId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateInstructionItemModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'InstructionId' => $InstructionId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $DivisionCourseId
     * @param string $InstructionId
     * @param array $Data
     *
     * @return Danger|string
     */
    public function saveCreateInstructionItemModal(string $DivisionCourseId, string $InstructionId, array $Data)
    {
        if (!(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId)))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (!($tblInstruction = Instruction::useService()->getInstructionById($InstructionId))) {
            return new Danger('Die Belehrung wurde nicht gefunden', new Exclamation());
        }

        if (($form = Instruction::useService()->checkFormInstructionItem($Data, $tblDivisionCourse, $tblInstruction, null))) {
            // display Errors on form
            return $this->getInstructionItemModal($form, $tblInstruction);
        }

        if (Instruction::useService()->createInstructionItem($Data, $tblInstruction, $tblDivisionCourse)) {
            return new Success('Die Belehrung wurde erfolgreich gespeichert.')
                . self::pipelineLoadInstructionItemContent($DivisionCourseId)
                . self::pipelineClose();
        } else {
            return new Danger('Die Belehrung konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string $InstructionItemId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditInstructionItemModal(string $InstructionItemId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditInstructionItemModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'InstructionItemId' => $InstructionItemId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $InstructionItemId
     *
     * @return Pipeline
     */
    public static function pipelineEditInstructionItemSave(string $InstructionItemId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditInstructionItemModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'InstructionItemId' => $InstructionItemId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $InstructionItemId
     *
     * @return string
     */
    public function openEditInstructionItemModal($InstructionItemId)
    {
        if (!($tblInstructionItem = Instruction::useService()->getInstructionItemById($InstructionItemId))) {
            return new Danger('Die Belehrung wurde nicht gefunden', new Exclamation());
        }
        if (!(($tblDivisionCourse = $tblInstructionItem->getServiceTblDivisionCourse()))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }
        if (!($tblInstruction = $tblInstructionItem->getTblInstruction())) {
            return new Danger('Die Belehrung wurde nicht gefunden', new Exclamation());
        }

        return $this->getInstructionItemModal(Instruction::useFrontend()->formInstructionItem(
            $tblDivisionCourse, $tblInstruction, $InstructionItemId, true
        ), $tblInstruction, $InstructionItemId);
    }

    /**
     * @param $InstructionItemId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveEditInstructionItemModal($InstructionItemId, $Data)
    {
        if (!($tblInstructionItem = Instruction::useService()->getInstructionItemById($InstructionItemId))) {
            return new Danger('Die Belehrung wurde nicht gefunden', new Exclamation());
        }
        if (!(($tblDivisionCourse = $tblInstructionItem->getServiceTblDivisionCourse()))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }
        if (!($tblInstruction = $tblInstructionItem->getTblInstruction())) {
            return new Danger('Die Belehrung wurde nicht gefunden', new Exclamation());
        }

        if (($form = Instruction::useService()->checkFormInstructionItem($Data, $tblDivisionCourse, $tblInstruction, $tblInstructionItem))) {
            // display Errors on form
            return $this->getInstructionItemModal($form, $tblInstruction, $InstructionItemId);
        }

        if (Instruction::useService()->updateInstructionItem($tblInstructionItem, $Data)) {
            return new Success('Die Belehrung wurde erfolgreich gespeichert.')
                . self::pipelineLoadInstructionItemContent($tblDivisionCourse)
                . self::pipelineClose();
        } else {
            return new Danger('Die Belehrung konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string $InstructionItemId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteInstructionItemModal(string $InstructionItemId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteInstructionItemModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'InstructionItemId' => $InstructionItemId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $InstructionItemId
     *
     * @return string
     */
    public function openDeleteInstructionItemModal(string $InstructionItemId)
    {
        if (!($tblInstructionItem = Instruction::useService()->getInstructionItemById($InstructionItemId))) {
            return new Danger('Die Belehrung wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Die Belehrung löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diese Die Belehrung wirklich löschen?',
                                array(
                                    ($tblInstruction = $tblInstructionItem->getTblInstruction()) ? $tblInstruction->getSubject() : '',
                                    $tblInstructionItem->getDate(),
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteInstructionItemSave($InstructionItemId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param string $InstructionItemId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteInstructionItemSave(string $InstructionItemId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteInstructionItemModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'InstructionItemId' => $InstructionItemId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $InstructionItemId
     *
     * @return Danger|string
     */
    public function saveDeleteInstructionItemModal(string $InstructionItemId)
    {
        if (!($tblInstructionItem = Instruction::useService()->getInstructionItemById($InstructionItemId))) {
            return new Danger('Die Belehrung wurde nicht gefunden', new Exclamation());
        }
        if (!(($tblDivisionCourse = $tblInstructionItem->getServiceTblDivisionCourse()))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        if (Instruction::useService()->destroyInstructionItem($tblInstructionItem)) {
            return new Success('Die Belehrung wurde erfolgreich gelöscht.')
                . self::pipelineLoadInstructionItemContent($tblDivisionCourse)
                . self::pipelineClose();
        } else {
            return new Danger('Die Belehrung konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }
}