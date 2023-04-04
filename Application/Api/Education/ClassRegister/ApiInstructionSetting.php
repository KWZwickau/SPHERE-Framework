<?php

namespace SPHERE\Application\Api\Education\ClassRegister;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Instruction\Instruction;
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

class ApiInstructionSetting extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadInstructionContent');
        $Dispatcher->registerMethod('openCreateInstructionModal');
        $Dispatcher->registerMethod('saveCreateInstructionModal');
        $Dispatcher->registerMethod('openEditInstructionModal');
        $Dispatcher->registerMethod('saveEditInstructionModal');
        $Dispatcher->registerMethod('openDeleteInstructionModal');
        $Dispatcher->registerMethod('saveDeleteInstructionModal');
        $Dispatcher->registerMethod('saveActivateInstruction');

        $Dispatcher->registerMethod('loadInstructionReportingContent');

        $Dispatcher->registerMethod('saveHeadmasterNoticed');

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
     * @return Pipeline
     */
    public static function pipelineLoadInstructionContent(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'InstructionContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadInstructionContent',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function loadInstructionContent() : string
    {
        return Instruction::useFrontend()->loadInstructionSettingTable();
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenCreateInstructionModal(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateInstructionModal',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function openCreateInstructionModal(): string
    {
        return $this->getInstructionModal(Instruction::useFrontend()->formInstruction());
    }

    /**
     * @param $form
     * @param string|null $InstructionId
     *
     * @return string
     */
    private function getInstructionModal($form, string $InstructionId = null): string
    {
        if ($InstructionId) {
            $title = new Title(new Edit() . ' Belehrung bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Belehrung hinzufügen');
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
     * @return Pipeline
     */
    public static function pipelineCreateInstructionSave(): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateInstructionModal'
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param array|null $Data
     *
     * @return string
     */
    public function saveCreateInstructionModal(array $Data = null): string
    {
        if (($form = Instruction::useService()->checkFormInstruction($Data))) {
            // display Errors on form
            return $this->getInstructionModal($form);
        }

        if (Instruction::useService()->createInstruction($Data)) {
            return new Success('Die Belehrung wurde erfolgreich gespeichert.')
                . self::pipelineLoadInstructionContent()
                . self::pipelineClose();
        } else {
            return new Danger('Die Belehrung konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string $InstructionId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditInstructionModal(string $InstructionId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditInstructionModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'InstructionId' => $InstructionId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $InstructionId
     *
     * @return Pipeline
     */
    public static function pipelineEditInstructionSave(string $InstructionId): Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditInstructionModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'InstructionId' => $InstructionId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $InstructionId
     *
     * @return string
     */
    public function openEditInstructionModal($InstructionId)
    {
        if (!(Instruction::useService()->getInstructionById($InstructionId))) {
            return new Danger('Die Belehrung wurde nicht gefunden', new Exclamation());
        }

        return $this->getInstructionModal(Instruction::useFrontend()->formInstruction($InstructionId, true), $InstructionId);
    }

    /**
     * @param $InstructionId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveEditInstructionModal($InstructionId, $Data)
    {
        if (!($tblInstruction = Instruction::useService()->getInstructionById($InstructionId))) {
            return new Danger('Die Belehrung wurde nicht gefunden', new Exclamation());
        }

        if (($form = Instruction::useService()->checkFormInstruction($Data, $tblInstruction))) {
            // display Errors on form
            return $this->getInstructionModal($form, $InstructionId);
        }

        if (Instruction::useService()->updateInstruction($tblInstruction, $Data)) {
            return new Success('Die Belehrung wurde erfolgreich gespeichert.')
                . self::pipelineLoadInstructionContent()
                . self::pipelineClose();
        } else {
            return new Danger('Die Belehrung konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string $InstructionId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteInstructionModal(string $InstructionId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteInstructionModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'InstructionId' => $InstructionId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $InstructionId
     *
     * @return string
     */
    public function openDeleteInstructionModal(string $InstructionId)
    {
        if (!($tblInstruction = Instruction::useService()->getInstructionById($InstructionId))) {
            return new Danger('Die Belehrung wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Belehrung löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diese Belehrung wirklich löschen?',
                                array(
                                    $tblInstruction->getSubject(),
                                    $tblInstruction->getContent()
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteInstructionSave($InstructionId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param string $InstructionId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteInstructionSave(string $InstructionId): Pipeline
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteInstructionModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'InstructionId' => $InstructionId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $InstructionId
     *
     * @return Danger|string
     */
    public function saveDeleteInstructionModal(string $InstructionId)
    {
        if (!($tblInstruction = Instruction::useService()->getInstructionById($InstructionId))) {
            return new Danger('Die Belehrung wurde nicht gefunden', new Exclamation());
        }

        if (Instruction::useService()->destroyInstruction($tblInstruction)) {
            return new Success('Die Belehrung wurde erfolgreich gelöscht.')
                . self::pipelineLoadInstructionContent()
                . self::pipelineClose();
        } else {
            return new Danger('Die Belehrung konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param string $InstructionId
     *
     * @return Pipeline
     */
    public static function pipelineActivateInstructionSave(string $InstructionId): Pipeline
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'InstructionContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveActivateInstruction'
        ));
        $ModalEmitter->setPostPayload(array(
            'InstructionId' => $InstructionId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $InstructionId
     *
     * @return Danger|string
     */
    public function saveActivateInstruction(string $InstructionId)
    {
        if (!($tblInstruction = Instruction::useService()->getInstructionById($InstructionId))) {
            return new Danger('Die Belehrung wurde nicht gefunden', new Exclamation());
        }

        $status = $tblInstruction->getIsActive() ? 'deaktiviert' : 'aktiviert';
        if (Instruction::useService()->activateInstruction($tblInstruction)) {
            return self::pipelineLoadInstructionContent();
        } else {
            return new Danger('Die Belehrung konnte nicht ' . $status . ' werden.') . self::pipelineClose();
        }
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadInstructionReportingContent(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'InstructionReportingContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadInstructionReportingContent',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param array|null $Data
     *
     * @return string
     */
    public function loadInstructionReportingContent(?array $Data) : string
    {
        return Instruction::useFrontend()->loadInstructionReportingTable($Data);
    }

    /**
     * @param string|null $DivisionCourseId
     *
     * @return Pipeline
     */
    public static function pipelineSaveHeadmasterNoticed(string $DivisionCourseId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CourseContentContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveHeadmasterNoticed',
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
     * @return Pipeline|Danger
     */
    public function saveHeadmasterNoticed(string $DivisionCourseId = null)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der SekII-Kurs wurde nicht gefunden', new Exclamation());
        }

        Digital::useService()->updateBulkCourseContentHeadmaster($tblDivisionCourse);

        return ApiDigital::pipelineLoadCourseContentContent($DivisionCourseId, 'true');
    }
}