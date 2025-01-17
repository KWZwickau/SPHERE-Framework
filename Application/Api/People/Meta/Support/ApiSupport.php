<?php

namespace SPHERE\Application\Api\People\Meta\Support;


use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
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
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiSupport
 *
 * @package SPHERE\Application\Api\People\Meta\Support
 */
class ApiSupport extends Extension implements IApiInterface
{

    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('openCreateSupportModal');
        $Dispatcher->registerMethod('openCreateSpecialModal');
        $Dispatcher->registerMethod('openCreateHandyCapModal');
        $Dispatcher->registerMethod('openEditSupportModal');
        $Dispatcher->registerMethod('openEditSpecialModal');
        $Dispatcher->registerMethod('openEditHandyCapModal');
        $Dispatcher->registerMethod('openDeleteSupportModal');
        $Dispatcher->registerMethod('openDeleteSpecialModal');
        $Dispatcher->registerMethod('openDeleteHandyCapModal');
        $Dispatcher->registerMethod('saveCreateSupportModal');
        $Dispatcher->registerMethod('saveCreateSpecialModal');
        $Dispatcher->registerMethod('saveCreateHandyCapModal');
        $Dispatcher->registerMethod('saveUpdateSupportModal');
        $Dispatcher->registerMethod('saveUpdateSpecialModal');
        $Dispatcher->registerMethod('saveUpdateHandyCapModal');
        $Dispatcher->registerMethod('deleteSupportService');
        $Dispatcher->registerMethod('deleteSpecialService');
        $Dispatcher->registerMethod('deleteHandyCapService');
        $Dispatcher->registerMethod('loadSupportTable');
        $Dispatcher->registerMethod('loadSpecialTable');
        $Dispatcher->registerMethod('loadHandyCapTable');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReciever');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverTableBlock($Content = '', $Identifier = '')
    {

        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverInline($Content = '', $Identifier = '')
    {

        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    public static function pipelineLoadTable($PersonId)
    {

        $TablePipeline = new Pipeline(false);
        $TableEmitter = new ServerEmitter(ApiSupport::receiverTableBlock('', 'SupportTable'), ApiSupport::getEndpoint());
        $TableEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'loadSupportTable',
        ));
        $TableEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $TablePipeline->appendEmitter($TableEmitter);
        $TableEmitter = new ServerEmitter(ApiSupport::receiverTableBlock('', 'SpecialTable'), ApiSupport::getEndpoint());
        $TableEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'loadSpecialTable',
        ));
        $TableEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $TablePipeline->appendEmitter($TableEmitter);
        $TableEmitter = new ServerEmitter(ApiSupport::receiverTableBlock('', 'HandyCapTable'), ApiSupport::getEndpoint());
        $TableEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'loadHandyCapTable',
        ));
        $TableEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $TablePipeline->appendEmitter($TableEmitter);

        return $TablePipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateSupportModal($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), ApiSupport::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'openCreateSupportModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateSpecialModal($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), ApiSupport::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'openCreateSpecialModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateHandyCapModal($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), ApiSupport::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'openCreateHandyCapModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $SupportId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditSupportModal($PersonId, $SupportId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), ApiSupport::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'openEditSupportModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'SupportId' => $SupportId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $SpecialId
     * @param bool $IsInit
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditSpecialModal($PersonId, $SpecialId, $IsInit = false)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), ApiSupport::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'openEditSpecialModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'SpecialId' => $SpecialId,
            'IsInit' => $IsInit ? '1' : '0'
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $HandyCapId
     * @param bool $IsInit
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditHandyCapModal($PersonId, $HandyCapId, $IsInit = false)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), ApiSupport::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiSupport::API_TARGET => 'openEditHandyCapModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'HandyCapId' => $HandyCapId,
            'IsInit' => $IsInit ? '1' : '0'
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineCreateSupportSave($PersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateSupportModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineCreateSpecialSave($PersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateSpecialModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineCreateHandyCapSave($PersonId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateHandyCapModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $SupportId
     *
     * @return Pipeline
     */
    public static function pipelineUpdateSupportSave($PersonId, $SupportId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveUpdateSupportModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'SupportId' => $SupportId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $SpecialId
     *
     * @return Pipeline
     */
    public static function pipelineUpdateSpecialSave($PersonId, $SpecialId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveUpdateSpecialModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'SpecialId' => $SpecialId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $HandyCapId
     *
     * @return Pipeline
     */
    public static function pipelineUpdateHandyCapSave($PersonId, $HandyCapId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveUpdateHandyCapModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'HandyCapId' => $HandyCapId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $SupportId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteSupport($PersonId, $SupportId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteSupportModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'SupportId' => $SupportId
        ));
//        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $SpecialId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteSpecial($PersonId, $SpecialId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteSpecialModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'SpecialId' => $SpecialId
        ));
//        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $HandyCapId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteHandyCap($PersonId, $HandyCapId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteHandyCapModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'HandyCapId' => $HandyCapId
        ));
//        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $SupportId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteSupport($PersonId, $SupportId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'deleteSupportService'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'SupportId' => $SupportId
        ));
//        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $SpecialId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteSpecial($PersonId, $SpecialId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'deleteSpecialService'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'SpecialId' => $SpecialId
        ));
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $HandyCapId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteHandyCap($PersonId, $HandyCapId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(ApiSupport::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'deleteHandyCapService'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'HandyCapId' => $HandyCapId
        ));
//        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);
        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openCreateSupportModal($PersonId)
    {

        return new Title('Förderantrag/ Förderbescheid hinzufügen')
        .new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Well(
                            Student::useFrontend()->formSupport($PersonId)
                        )
                    )
                )
            )
        );
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openCreateSpecialModal($PersonId)
    {
        $global =  $this->getGlobal();
        $IsCanceled = isset($global->POST['Data']['IsCanceled']);

        $form = Student::useFrontend()->formSpecial($PersonId, null, $IsCanceled);

        return new Title('Entwicklungsbesonderheiten hinzufügen')
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                $form
                            )
                        )
                    )
                )
            );
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openCreateHandyCapModal($PersonId)
    {

        $global =  $this->getGlobal();
        $IsCanceled = isset($global->POST['Data']['IsCanceled']);

        return new Title('Nachteilsausgleich hinzufügen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Student::useFrontend()->formHandyCap($PersonId, null, $IsCanceled)
                            )
                        )
                    )
                )
            );
    }

    /**
     * @param int $PersonId
     * @param int $SupportId
     *
     * @return string
     */
    public function openEditSupportModal($PersonId, $SupportId)
    {

        return new Title('Förderantrag/ Förderbescheid bearbeiten')
        .new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Well(
                            Student::useFrontend()->formSupport($PersonId, $SupportId)
                        )
                    )
                )
            )
        );
    }

    /**
     * @param int $PersonId
     * @param int $SpecialId
     * @param bool $IsInit
     *
     * @return string
     */
    public function openEditSpecialModal($PersonId, $SpecialId, $IsInit)
    {
        $IsCanceled = false;
        $global =  $this->getGlobal();
        if ($IsInit) {
            if (($tblSpecial = Student::useService()->getSpecialById($SpecialId))) {
                $IsCanceled = $tblSpecial->isCanceled();
            }

        } else {
            $IsCanceled = isset($global->POST['Data']['IsCanceled']);
        }

        return new Title('Entwicklungsbesonderheiten bearbeiten')
        .new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Well(
                            Student::useFrontend()->formSpecial($PersonId, $SpecialId, $IsCanceled, $IsInit)
                        )
                    )
                )
            )
        );
    }

    /**
     * @param int $PersonId
     * @param int $HandyCapId
     * @param bool $IsInit
     *
     * @return string
     */
    public function openEditHandyCapModal($PersonId, $HandyCapId, $IsInit)
    {
        $IsCanceled = false;
        $global =  $this->getGlobal();
        if ($IsInit) {
            if (($tblHandyCap = Student::useService()->getHandyCapById($HandyCapId))) {
                $IsCanceled = $tblHandyCap->isCanceled();
            }

        } else {
            $IsCanceled = isset($global->POST['Data']['IsCanceled']);
        }

        return new Title('Nachteilsausgleich bearbeiten')
        .new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Well(
                            Student::useFrontend()->formHandyCap($PersonId, $HandyCapId, $IsCanceled, $IsInit)
                        )
                    )
                )
            )
        );
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose()
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function saveCreateSupportModal($PersonId)
    {

        $Global = $this->getGlobal();
        $Data = $Global->POST['Data'];
        if (($form = Student::useService()->checkInputSupport($PersonId, $Data))) {
            // display Errors on form
            return $form;
        }

        if (Student::useService()->createSupport($PersonId, $Data)) {
             return new Success('Förderantrag wurde erfolgreich gespeichert.')
                 .self::pipelineLoadTable($PersonId)
                 .self::pipelineClose();
        } else {
            return new Danger('Förderantrag konnte nicht gespeichert werden.').self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function saveCreateSpecialModal($PersonId)
    {

        $Global = $this->getGlobal();
        $Data = $Global->POST['Data'];

        if (($form = Student::useService()->checkInputSpecial($PersonId, $Data))) {
            // display Errors on form
            return $form;
        }

        // do service
        if (Student::useService()->createSpecial($PersonId, $Data)
        ) {
            return new Success('Entwicklungsbesonderheiten wurde erfolgreich gespeichert.')
                .self::pipelineLoadTable($PersonId)
                .self::pipelineClose();
        } else {
            return new Danger('Entwicklungsbesonderheiten konnte nicht gespeichert werden.').self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function saveCreateHandyCapModal($PersonId)
    {

        $Global = $this->getGlobal();
        $Data = $Global->POST['Data'];

        if (($form = Student::useService()->checkInputHandyCap($PersonId, $Data))) {
            // display Errors on form
            return $form;
        }

        // do service
        if (Student::useService()->createHandyCap($PersonId, $Data)
        ) {
            return new Success('Nachteilsausgleich wurde erfolgreich gespeichert.')
                .self::pipelineLoadTable($PersonId)
                .self::pipelineClose();
        } else {
            return new Danger('Nachteilsausgleich konnte nicht gespeichert werden.').self::pipelineClose();
        }
    }

    /**
     * @param int $PersonId
     * @param int $SupportId
     *
     * @return string
     */
    public function saveUpdateSupportModal($PersonId, $SupportId)
    {

        $Global = $this->getGlobal();
        $Data = $Global->POST['Data'];
        if (($form = Student::useService()->checkInputSupport($PersonId, $Data, $SupportId))) {
            // display Errors on form
            return $form;
        }
        // do service
        if (Student::useService()->updateSupport($PersonId, $SupportId, $Data)
        ) {
            return new Success('Förderantrag wurde erfolgreich gespeichert.')
                .self::pipelineLoadTable($PersonId)
                .self::pipelineClose();
        } else {
            return new Danger('Förderantrag konnte nicht gespeichert werden.').self::pipelineClose();
        }
    }

    /**
     * @param int $PersonId
     * @param int $SpecialId
     *
     * @return string
     */
    public function saveUpdateSpecialModal($PersonId, $SpecialId)
    {

        $Global = $this->getGlobal();
        $Data = $Global->POST['Data'];
        if (($form = Student::useService()->checkInputSpecial($PersonId, $Data, $SpecialId))) {
            // display Errors on form
            return $form;
        }
        // do service
        if (Student::useService()->updateSpecial($PersonId, $SpecialId, $Data)
        ) {
            return new Success('Entwicklungsbesonderheiten wurde erfolgreich gespeichert.')
                .self::pipelineLoadTable($PersonId)
                .self::pipelineClose();
        } else {
            return new Danger('Entwicklungsbesonderheiten konnte nicht gespeichert werden.').self::pipelineClose();
        }
    }

    /**
     * @param int $PersonId
     * @param int $HandyCapId
     *
     * @return string
     */
    public function saveUpdateHandyCapModal($PersonId, $HandyCapId)
    {

        $Global = $this->getGlobal();
        $Data = $Global->POST['Data'];

        if (($form = Student::useService()->checkInputHandyCap($PersonId, $Data, $HandyCapId))) {
            // display Errors on form
            return $form;
        }

        // do service
        if (Student::useService()->updateHandyCap($PersonId, $HandyCapId, $Data)
        ) {
            return new Success('Entwicklungsbesonderheiten wurde erfolgreich gespeichert.')
                .self::pipelineLoadTable($PersonId)
                .self::pipelineClose();
        } else {
            return new Danger('Entwicklungsbesonderheiten konnte nicht gespeichert werden.').self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     *
     * @return Warning|TableData
     */
    public function loadSupportTable($PersonId)
    {

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if($tblPerson){
            return Student::useFrontend()->getSupportTable($tblPerson);
        }
        return new Warning('Person nicht gefunden');
    }

    /**
     * @param $PersonId
     *
     * @return Warning|TableData
     */
    public function loadSpecialTable($PersonId)
    {

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if($tblPerson){
            return Student::useFrontend()->getSpecialTable($tblPerson);
        }
        return new Warning('Person nicht gefunden');
    }

    /**
     * @param $PersonId
     *
     * @return Warning|TableData
     */
    public function loadHandyCapTable($PersonId)
    {

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if($tblPerson){
            return Student::useFrontend()->getHandyCapTable($tblPerson);
        }
        return new Warning('Person nicht gefunden');
    }

    /**
     * @param int $PersonId
     * @param int $SupportId
     *
     * @return Danger|string
     */
    public function openDeleteSupportModal($PersonId, $SupportId)
    {
        $tblSupport = Student::useService()->getSupportById($SupportId);
        if(!$tblSupport){
            return new Danger('Eintrag nicht gefunden.');
        }

        $SupportType = '';
        if(($tblSupportType = $tblSupport->getTblSupportType())){
            $SupportType = $tblSupportType->getName() ;
        }
        $FocusList = array();
        $tblFocusType = Student::useService()->getPrimaryFocusBySupport($tblSupport);
        if($tblFocusType){
            $FocusList[] = new Bold('Primär: '.$tblFocusType->getName());
        }
        $tblFocusTypeList = Student::useService()->getFocusListBySupport($tblSupport);
        if($tblFocusTypeList){
            foreach($tblFocusTypeList as $tblFocusTypeSingle){
                $FocusList[] = $tblFocusTypeSingle->getName();
            }
        }

        $Person = '';
        if(($tblPerson = $tblSupport->getServiceTblPerson())){
            $Person = $tblPerson->getLastFirstName();
        }

        $Focus = implode(new Ruler(), $FocusList);

        $Content = new Listing(array(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Person:', 3),
                new LayoutColumn($Person, 9),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Datum:', 3),
                    new LayoutColumn($tblSupport->getDate(), 9),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Vorgang:', 3),
                    new LayoutColumn($SupportType, 9),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Schwerpunkte:', 3),
                    new LayoutColumn($Focus, 9),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Förderschule:', 3),
                    new LayoutColumn($tblSupport->getCompany(), 9),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Schulbegleitung:', 3),
                    new LayoutColumn($tblSupport->getPersonSupport(), 9),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Stundenbedarf:', 3),
                    new LayoutColumn($tblSupport->getSupportTime(), 9),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Bemerkung:', 3),
                    new LayoutColumn($tblSupport->getRemark(), 9),
            ))))
        ));

        return new Title('Förderantrag entfernen')
        .new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Panel('Soll der Eintrag wirklich gelöscht werden?',
                            $Content, Panel::PANEL_TYPE_DANGER
                        )
                        .(new DangerLink('Ja', '#', new Ok()))
                            ->ajaxPipelineOnClick(ApiSupport::pipelineDeleteSupport($PersonId, $SupportId))
                        .(new Standard('Nein', '#', new Remove()))
                            ->ajaxPipelineOnClick(ApiSupport::pipelineClose())
                    )
                )
            )
        );
    }

    /**
     * @param int $PersonId
     * @param int $SpecialId
     *
     * @return Danger|string
     */
    public function openDeleteSpecialModal($PersonId, $SpecialId)
    {
        $tblSpecial = Student::useService()->getSpecialById($SpecialId);
        if(!$tblSpecial){
            return new Danger('Eintrag nicht gefunden.');
        }

        $DisorderList = array();
        $tblSpecialDisorderTypeList = Student::useService()->getSpecialDisorderTypeAllBySpecial($tblSpecial);
        if($tblSpecialDisorderTypeList){
            foreach($tblSpecialDisorderTypeList as $tblSpecialDisorderType){
                $DisorderList[] = $tblSpecialDisorderType->getName();
            }
        }

        $Person = '';
        if(($tblPerson = $tblSpecial->getServiceTblPerson())){
            $Person = $tblPerson->getLastFirstName();
        }

        $Disorder = implode(new Ruler(), $DisorderList);

        $Content = new Listing(array(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Person:', 3),
                new LayoutColumn($Person, 9),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Datum:', 3),
                    new LayoutColumn($tblSpecial->getDate(), 9),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Entwicklungsbesonderheiten:', 3),
                    new LayoutColumn($Disorder, 9),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Bemerkung:', 3),
                    new LayoutColumn($tblSpecial->getRemark(), 9),
            ))))
        ));

        return new Title('Entwicklungsbesonderheiten entfernen')
        .new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Panel('Soll der Eintrag wirklich gelöscht werden?',
                            $Content, Panel::PANEL_TYPE_DANGER
                        )
                        .(new DangerLink('Ja', '#', new Ok()))
                            ->ajaxPipelineOnClick(ApiSupport::pipelineDeleteSpecial($PersonId, $SpecialId))
                        .(new Standard('Nein', '#', new Remove()))
                            ->ajaxPipelineOnClick(ApiSupport::pipelineClose())
                    )
                )
            )
        );
    }

    /**
     * @param int $PersonId
     * @param int $HandyCapId
     *
     * @return Danger|string
     */
    public function openDeleteHandyCapModal($PersonId, $HandyCapId)
    {
        $tblHandyCap = Student::useService()->getHandyCapById($HandyCapId);
        if(!$tblHandyCap){
            return new Danger('Eintrag nicht gefunden.');
        }

        $Person = '';
        if(($tblPerson = $tblHandyCap->getServiceTblPerson())){
            $Person = $tblPerson->getLastFirstName();
        }

        $Content = new Listing(array(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Person:', 4),
                new LayoutColumn($Person, 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Datum:', 4),
                new LayoutColumn($tblHandyCap->getDate(), 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Rechtliche Grundlage:', 4),
                    new LayoutColumn($tblHandyCap->getLegalBasis(), 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Lernziel:', 4),
                    new LayoutColumn($tblHandyCap->getLearnTarget(), 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Besonderheiten im Unterricht:', 4),
                    new LayoutColumn($tblHandyCap->getRemarkLesson(), 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn('Besonderheiten bei Leistungsbewertungen:', 4),
                    new LayoutColumn($tblHandyCap->getRemarkRating(), 8),
            )))),
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Besonderheiten in der Zeugnisvorbereitung:', 4),
                new LayoutColumn($tblHandyCap->getRemarkCertificate(), 8),
            ))))
        ));

        return new Title('Nachteilsausgleich entfernen')
        .new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Panel('Soll der Eintrag wirklich gelöscht werden?',
                            $Content, Panel::PANEL_TYPE_DANGER
                        )
                        .(new DangerLink('Ja', '#', new Ok()))
                            ->ajaxPipelineOnClick(ApiSupport::pipelineDeleteHandyCap($PersonId, $HandyCapId))
                        .(new Standard('Nein', '#', new Remove()))
                            ->ajaxPipelineOnClick(ApiSupport::pipelineClose())
                    )
                )
            )
        );
    }

    /**
     * @param $PersonId
     * @param $SupportId
     *
     * @return Danger|string
     */
    public function deleteSupportService($PersonId, $SupportId)
    {

        if(!($tblSupport = Student::useService()->getSupportById($SupportId))) {
            return new Danger('Der Förderantrag konnte nicht gefunden werden.');
        }
        if (Student::useService()->deleteSupport($tblSupport)
        ) {
            return new Success('Der Förderantrag wurde erfolgreich gelöscht.')
                .self::pipelineLoadTable($PersonId)
                .self::pipelineClose();
        } else {
            return new Danger('Der Förderantrag konnte nicht gelöscht werden.')
                .self::pipelineLoadTable($PersonId)
                .self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     * @param $SpecialId
     *
     * @return Danger|string
     */
    public function deleteSpecialService($PersonId, $SpecialId)
    {

        if(!($tblSpecial = Student::useService()->getSpecialById($SpecialId))) {
            return new Danger('Die Entwicklungsbesonderheit konnte nicht gefunden werden.');
        }
        if (Student::useService()->deleteSpecial($tblSpecial)
        ) {
            return new Success('Die Entwicklungsbesonderheit wurde erfolgreich gelöscht.')
                .self::pipelineLoadTable($PersonId)
                .self::pipelineClose();
        } else {
            return new Danger('Die Entwicklungsbesonderheit konnte nicht gelöscht werden.')
                .self::pipelineLoadTable($PersonId)
                .self::pipelineClose();
        }
    }

    /**
     * @param $PersonId
     * @param $HandyCapId
     *
     * @return Danger|string
     */
    public function deleteHandyCapService($PersonId, $HandyCapId)
    {

        if(!($tblHandyCap = Student::useService()->getHandyCapById($HandyCapId))) {
            return new Danger('Der Nachteilsausgleich konnte nicht gefunden werden.');
        }
        if (Student::useService()->deleteHandyCap($tblHandyCap)
        ) {
            return new Success('Der Nachteilsausgleich wurde erfolgreich gelöscht.')
                .self::pipelineLoadTable($PersonId)
                .self::pipelineClose();
        } else {
            return new Danger('Der Nachteilsausgleich konnte nicht gelöscht werden.')
                .self::pipelineLoadTable($PersonId)
                .self::pipelineClose();
        }
    }
}