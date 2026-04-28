<?php

namespace SPHERE\Application\Api\Platform\DataMaintenance;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Application\Platform\System\DataMaintenance\Frontend;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Extension;

class ApiConsumerLogin extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('openRoleModal');
        $Dispatcher->registerMethod('openDllpModal');
        $Dispatcher->registerMethod('openSswStopModal');
        $Dispatcher->registerMethod('saveRoleModal');
        $Dispatcher->registerMethod('saveDllpModal');
        $Dispatcher->registerMethod('saveSswStopModal');
        $Dispatcher->registerMethod('reloadTable');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Identifier
     *
     * @return ModalReceiver
     */
    public static function receiverModal(string $Identifier = ''): ModalReceiver
    {
        return (new ModalReceiver(null, new Close()))->setIdentifier($Identifier);
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
    public static function pipelineOpenRoleModal($ConsumerId, $RoleId): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverModal('Modal'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openRoleModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ConsumerId' => $ConsumerId,
            'RoleId' => $RoleId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenDllpModal($ConsumerId, $SystemName): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverModal('Modal'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDllpModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ConsumerId' => $ConsumerId,
            'SystemName' => $SystemName
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenSswStopModal($ConsumerId, $SystemName): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverModal('Modal'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openSswStopModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ConsumerId' => $ConsumerId,
            'SystemName' => $SystemName
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSaveRoleModal($ConsumerId, $RoleId): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverModal('Modal'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveRoleModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ConsumerId' => $ConsumerId,
            'RoleId' => $RoleId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSaveDllpModal($ConsumerId): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverModal('Modal'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDllpModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ConsumerId' => $ConsumerId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSaveSswStopModal($ConsumerId): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverModal('Modal'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveSswStopModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ConsumerId' => $ConsumerId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineReload(): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $BlockEmitter = new ServerEmitter(self::receiverBlock('', 'ConsumerLoginTable'), self::getEndpoint());
        $BlockEmitter->setGetPayload(array(
            self::API_TARGET => 'reloadTable',
        ));
        $Pipeline->appendEmitter($BlockEmitter);
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal('Modal')))->getEmitter());

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function openRoleModal($ConsumerId, $RoleId): string
    {

        $tblConsumer = Consumer::useService()->getConsumerById($ConsumerId);
        $tblRole = Access::useService()->getRoleById($RoleId);
        $tblRoleConsumer = Access::useService()->getRoleConsumerBy($tblRole, $tblConsumer);

        if($tblRoleConsumer){
            $_POST['Data']['Active'] = 1;
            $SelectBoxActive = array(1 => 'Aktiv', 2 => 'Deaktivieren');
        } else {
            $_POST['Data']['Active'] = 2;
            $SelectBoxActive = array(1 => 'Aktivieren', 2 => 'nicht Aktiv');
        }

        $EffectUserList = '';
        if($tblRoleConsumer){
            $UserList = array();
            if(($tblAccountList = Account::useService()->getAccountListByAuthorizationAndConsumer($tblRole, $tblConsumer))){
                foreach($tblAccountList as $tblAccount){
                    $UserList[] = $tblAccount->getUsername();
                }
            }
            $EffectUserList = new Listing($UserList);
            if(empty($UserList)){
                $EffectUserList = 'keine Benutzer betroffen';
            }
        }

        return
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new Headline($tblRole->getName())
                    , 4),
                new LayoutColumn(
                    new PullRight(new Headline($tblConsumer->getName(), $tblConsumer->getAcronym()))
                    , 8),
            ))))
            .new Well(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(new Form(new FormGroup(new FormRow(
                        new FormColumn(
                            new SelectBox('Data[Active]', $tblRole->getName().' Status', $SelectBoxActive)
                        , 12)))
                        , (new Primary('Speichern','#'))->ajaxPipelineOnClick(ApiConsumerLogin::pipelineSaveRoleModal($ConsumerId, $RoleId))
                    ), 6),
                    new LayoutColumn(
                        ($EffectUserList?new Title('Betroffene Benutzer ', 'die das Recht verlieren').$EffectUserList: '')
                    , 6),
                ))))

            );
    }

    /**
     * @return string
     */
    public function openDllpModal($ConsumerId, $SystemName): string
    {
        $tblConsumer = Consumer::useService()->getConsumerById($ConsumerId);
        $tblConsumerLogin = Consumer::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, $SystemName);
        if($tblConsumerLogin){
            $_POST['Data']['Active'] = 1;
            $_POST['Data']['ActiveButton'] = $tblConsumerLogin->getIsActiveAPI();
            $SelectBoxActive = array(1 => 'Aktiv', 2 => 'Deaktivieren');
        } else {
            $_POST['Data']['Active'] = 2;
            $SelectBoxActive = array(1 => 'Aktivieren', 2 => 'Inaktiv');
        }

        return
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new Headline($SystemName)
                    , 3),
                new LayoutColumn(
                    new PullRight(new Headline($tblConsumer->getName(), $tblConsumer->getAcronym()))
                    , 9),
            ))))
            .new Well(new Form(new FormGroup(array(new FormRow(array(
                new FormColumn(new SelectBox('Data[Active]', $SystemName.' Status', $SelectBoxActive), 4),
                new FormColumn((new CheckBox('Data[ActiveButton]', 'Buttons KelvinAPI', 1))->setPaddingTop(), 4),
            ))))
                , (new Primary('Speichern','#'))->ajaxPipelineOnClick(ApiConsumerLogin::pipelineSaveDllpModal($ConsumerId))
        ));
    }

    /**
     * @return string
     */
    public function openSswStopModal($ConsumerId, $SystemName): string
    {
        $tblConsumer = Consumer::useService()->getConsumerById($ConsumerId);
        $tblConsumerLogin = Consumer::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, $SystemName);

        // gedrehte Logik
        $_POST['Data']['Active'] = 2;
        $SelectBoxActive = array(1 => 'SSW Zugriff stoppen', 2 => 'SSW ist Aktiv');
        if($tblConsumerLogin){
            $_POST['Data']['Active'] = 1;
            $SelectBoxActive = array(1 => 'SSW Zugriff gestoppt', 2 => 'SSW Reaktivieren');
        }

        return new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(
                new Headline($SystemName)
                , 3),
            new LayoutColumn(
                new PullRight(new Headline($tblConsumer->getName(), $tblConsumer->getAcronym()))
                , 9),
        ))))
        .new Well(new Form(
            new FormGroup(array(new FormRow(array(
                new FormColumn(new SelectBox('Data[Active]', $SystemName.' Status', $SelectBoxActive), 4),
            ))))
            , (new Primary('Speichern','#'))->ajaxPipelineOnClick(ApiConsumerLogin::pipelineSaveSswStopModal($ConsumerId))
        ));
    }

    /**
     * @param $Status
     * @return string
     */
    public function saveRoleModal($ConsumerId, $RoleId, $Data): string
    {
        $tblConsumer = Consumer::useService()->getConsumerById($ConsumerId);
        $tblRole = Access::useService()->getRoleById($RoleId);

        if ($Data['Active'] == 1) {
            Access::useService()->createRoleConsumer($tblRole, $tblConsumer);
        } else {
            if(($tblRoleConsumer = Access::useService()->getRoleConsumerBy($tblRole, $tblConsumer))){
                Access::useService()->removeRoleConsumer($tblRoleConsumer);
                if(($tblAccountList = Account::useService()->getAccountListByAuthorizationAndConsumer($tblRole, $tblConsumer))){
                    foreach ($tblAccountList as $tblAccount) {
                        Account::useService()->removeAccountAuthorization($tblAccount, $tblRole);
                    }
                }
            }
        }
        return new Success('Einstellung wurde gespeichert')
            .ApiConsumerLogin::pipelinereload();
    }

    /**
     * @return string
     */
    public function saveDllpModal($ConsumerId, $Data): string
    {

        $tblConsumer = Consumer::useService()->getConsumerById($ConsumerId);
        $IsActive = $Data['Active'] == 1;
        $isButtonActive = isset($Data['ActiveButton']);
        $tblConsumerLogin = Consumer::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_DLLP);
        if (!$tblConsumerLogin) {
            if ($IsActive) {
                Consumer::useService()->createConsumerLogin($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_DLLP, $isButtonActive);
            }
        } else {
            if ($IsActive) {
                Consumer::useService()->updateConsumerLogin($tblConsumerLogin, $isButtonActive);
            } else {
                Consumer::useService()->removeConsumerLogin($tblConsumerLogin);
            }
        }
        return new Success('Einstellung wurde gespeichert')
            .ApiConsumerLogin::pipelinereload();
    }

    /**
     * @return string
     */
    public function saveSswStopModal($ConsumerId, $Data): string
    {
        $tblConsumer = Consumer::useService()->getConsumerById($ConsumerId);
        $tblConsumerLogin = Consumer::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_SSW_STOP);
        if (!$tblConsumerLogin) {
            Consumer::useService()->createConsumerLogin($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_SSW_STOP, false);
        } else {
            Consumer::useService()->removeConsumerLogin($tblConsumerLogin);
        }
        return new Success('Einstellung wurde gespeichert')
            .ApiConsumerLogin::pipelinereload();
    }

    public function reloadTable()
    {

        return Frontend::getConsumerLoginTable();
    }
}