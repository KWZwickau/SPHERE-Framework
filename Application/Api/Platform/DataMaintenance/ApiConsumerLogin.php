<?php

namespace SPHERE\Application\Api\Platform\DataMaintenance;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account as AccountAuthorization;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSetting;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Application\Platform\System\DataMaintenance\Frontend;
use SPHERE\Application\Transfer\Indiware\ErrorLog\ErrorLog;
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
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
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
        $Dispatcher->registerMethod('openIndiwareModal');
        $Dispatcher->registerMethod('saveRoleModal');
        $Dispatcher->registerMethod('saveDllpModal');
        $Dispatcher->registerMethod('saveSswStopModal');
        $Dispatcher->registerMethod('saveIndiwareModal');
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
     * @param $ConsumerId
     * @param $RoleId
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
     * @param $ConsumerId
     * @return Pipeline
     */
    public static function pipelineOpenDllpModal($ConsumerId): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverModal('Modal'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDllpModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ConsumerId' => $ConsumerId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ConsumerId
     * @return Pipeline
     */
    public static function pipelineOpenSswStopModal($ConsumerId): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverModal('Modal'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openSswStopModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ConsumerId' => $ConsumerId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ConsumerId
     * @return Pipeline
     */
    public static function pipelineOpenIndiwareModal($ConsumerId): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverModal('Modal'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openIndiwareModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ConsumerId' => $ConsumerId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $ConsumerId
     * @param $RoleId
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
     * @param $ConsumerId
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
     * @param $ConsumerId
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
     * @param $ConsumerId
     * @return Pipeline
     */
    public static function pipelineSaveIndiwareModal($ConsumerId): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverModal('Modal'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveIndiwareModal',
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
                    new PullRight(new Headline(new Bold(new DangerText($tblConsumer->getAcronym())), $tblConsumer->getName()))
                    , 8),
            ))))
            .new Well(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(array(
                        new Muted('<div style="height: 13px"></div>'),
                        new Form(new FormGroup(new FormRow(
                            new FormColumn(
                                new SelectBox('Data[Active]', $tblRole->getName().' Status', $SelectBoxActive)
                                , 12)))
                        , (new Primary('Speichern','#'))->ajaxPipelineOnClick(ApiConsumerLogin::pipelineSaveRoleModal($ConsumerId, $RoleId)))
                    ), 6),
                    new LayoutColumn(
                        ($EffectUserList?new Title('Betroffene Benutzer ', 'die das Recht verlieren').$EffectUserList: '')
                        .(new Well('Benutzerrecht gesteuerter Zugang'))->setPadding('5px')
                    , 6),
                ))))

            );
    }

    /**
     * @return string
     */
    public function openDllpModal($ConsumerId): string
    {

        $tblConsumer = Consumer::useService()->getConsumerById($ConsumerId);
        $tblConsumerLogin = Consumer::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_DLLP);
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
                    new Headline(TblConsumerLogin::VALUE_SYSTEM_DLLP)
                    , 4),
                new LayoutColumn(
                    new PullRight(new Headline(new Bold(new DangerText($tblConsumer->getAcronym())), $tblConsumer->getName()))
                    , 8),
            ))))
            .new Well(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(new Form(new FormGroup(new FormRow(array(
                            new FormColumn(array(
                                new Muted('<div style="height: 2px"></div>'),
                                new SelectBox('Data[Active]', TblConsumerLogin::VALUE_SYSTEM_DLLP.' Status', $SelectBoxActive)
                            ), 6),
                            new FormColumn(
                                (new CheckBox('Data[ActiveButton]', 'Buttons KelvinAPI', 1))->setPaddingTop()
                            , 6),
                        )))
                        , (new Primary('Speichern','#'))->ajaxPipelineOnClick(ApiConsumerLogin::pipelineSaveDllpModal($ConsumerId))
                    ), 5),
                    new LayoutColumn((new Well('setzt tblConsumerLogin mit Zusatzoption -> API Transferbuttons'
                    .new Container('&nbsp;')
                    .new Container(
                        (new Primary('Benutzer anlegen', '', new Plus()))->setDisabled()
                        .(new Primary('Benutzer anpassen', '', new Edit()))->setDisabled()
                        .(new DangerLink('Benutzer löschen', '', new Disable()))->setDisabled()
                        )))->setPadding('10px'), 7),
                )))));
    }

    /**
     * @return string
     */
    public function openSswStopModal($ConsumerId): string
    {

        $tblConsumer = Consumer::useService()->getConsumerById($ConsumerId);
        $tblConsumerLogin = Consumer::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_SSW_STOP);

        // gedrehte Logik
        $_POST['Data']['Active'] = 2;
        $SelectBoxActive = array(1 => 'SSW Zugriff stoppen', 2 => 'SSW ist Aktiv');
        if($tblConsumerLogin){
            $_POST['Data']['Active'] = 1;
            $SelectBoxActive = array(1 => 'SSW Zugriff gestoppt', 2 => 'SSW Reaktivieren');
        }
        $CountAccountList = AccountAuthorization::useService()->getAccountCountByConsumer($tblConsumer);
        $CountArray = array();
        $isSystem = false;
        foreach($CountAccountList as $CountAccount){
            $Name = $CountAccount['Name'];
            $Anzahl = $CountAccount['countAccount'];
            if($Name == 'System'){
                $isSystem = true;
//                continue;
            }
            $CountArray[] = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn($Name.': ', 3),
                new LayoutColumn($Anzahl, 9),
            ))));
        }

        return new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(
                new Headline('Zugang Schulsoftware')
                , 4),
            new LayoutColumn(
                new PullRight(new Headline(new Bold(new DangerText($tblConsumer->getAcronym())), $tblConsumer->getName()))
                , 8),
        ))))
        .new Well(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new Form(
                        new FormGroup(array(new FormRow(array(
                            new FormColumn(array(
                                new Muted('<div style="height: 13px"></div>'),
                                new SelectBox('Data[Active]', TblConsumerLogin::VALUE_SYSTEM_SSW_STOP.' Status', $SelectBoxActive)
                            ), 12),
                        ))))
                        , (new Primary('Speichern','#'))->ajaxPipelineOnClick(ApiConsumerLogin::pipelineSaveSswStopModal($ConsumerId))
                    )
                , 4),
                new LayoutColumn(($CountArray
                    ? new Title('Benutzer nach Identification:')
                    .($isSystem
                        ? (new Well('System-Accounts können sich auch auf gesperrten Mandanten anmelden'))->setPadding('10px')->setMarginBottom('5px')
                        : '')
                     .new Listing($CountArray)
                    : ''
                )
                , 8),
            ))))
            );
    }

    /**
     * @return string
     */
    public function openIndiwareModal($ConsumerId): string
    {

        $tblConsumer = Consumer::useService()->getConsumerById($ConsumerId);
        $Code = '';
        if(($tblAccount = Account::useService()->getAccountByUsername($tblConsumer->getAcronym().'-Indiware'))){
            if(($tblSetting = Account::useService()->getSettingByAccount($tblAccount, TblSetting::ATTR_INDIWARE_CODE))){
                $Code = $tblSetting->getValue();
            }
        }

        if($Code){
            $_POST['Data']['Active'] = 1;
            $SelectBoxActive = array(1 => 'Aktiv', 2 => 'Deaktivieren');
        } else {
            $_POST['Data']['Active'] = 2;
            $SelectBoxActive = array(1 => 'Aktivieren', 2 => 'Inaktiv');
        }

        return new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(
                new Headline('Indiware API - Vertretungsplan')
                , 4),
            new LayoutColumn(
                new PullRight(new Headline(new Bold(new DangerText($tblConsumer->getAcronym())), $tblConsumer->getName()))
                , 8),
        ))))
        .new Well(new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(
                new Form(
                    new FormGroup(array(new FormRow(array(
                        new FormColumn(new SelectBox('Data[Active]', 'Indiware API - Status', $SelectBoxActive), 12),
                    ))))
                    , (new Primary('Speichern','#'))->ajaxPipelineOnClick(ApiConsumerLogin::pipelineSaveIndiwareModal($ConsumerId))
                )
            , 3),
            new LayoutColumn(
                ($Code
                    ? new Info(
                        new Container(new DangerText('Hinterlegter Code(GUID) wird beim deaktivieren gelöscht!'))
                        .new Container('Der Service-Account '.$tblConsumer->getAcronym().'-Indiware wird nicht weiter angefasst.')
                        .new Container('&nbsp;')
                        .new Container('Code: '.$Code)
                    )
                    : new Info(
                        new Container('Aktivieren legt ein '.$tblConsumer->getAcronym().'-Indiware Service-Account an. '.new Muted(new Small('(Wenn nicht vorhanden)')))
                        .new Container('Das Passwort ist zufällig generiert '.new Muted(new Small('Bsp.:'.new Italic('b"Û¯ã>C»º»RB¡╬â 2‗üÄ­+"'))))
                        .new Container('Ein manueller Login für den Service-Account ist nicht vorgesehen.')
                        .new Container('Der Service-Account besitzt '.new Bold('keine Rechte').'.')
                        .new Container('Bei der Aktivierung wird ein neuer Sicherheitsschlüssel'.new Muted(new Small('(GUID)')).' als Account-Setting erzeugt.')
                        .new Container('Freischaltung kann hier oder direkt im Datentransfer "Api Logfile" erfolgen.')
                    )
                )
            , 9)
        )))));
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

    /**
     * @return string
     */
    public function saveIndiwareModal($ConsumerId, $Data): string
    {
        $tblConsumer = Consumer::useService()->getConsumerById($ConsumerId);
        $setActive = $Data['Active'] == 1;

        if($setActive){
            // create
            $Code = ErrorLog::createGUID();
            if($Code && Account::useService()->getSettingByUniqueValue($Code)){
                return new Danger('Code '.$Code.' bereits in Verwendung!'
                    .new Container('Versuche es nochmal, der Code wurde neu erzeugt.'));
            }

            $consumerAcronym = $tblConsumer->getAcronym();
            if(!($tblAccount = Account::useService()->getAccountByUsername($consumerAcronym.'-Indiware'))){
                $tblAccount = Account::useService()->createServiceAccount($consumerAcronym.'-Indiware', $tblConsumer);
            }
            Account::useService()->setSettingByAccount($tblAccount, TblSetting::ATTR_INDIWARE_CODE, $Code);
        }else {
            // delete
            if(($tblAccount = Account::useService()->getAccountByUsername($tblConsumer->getAcronym().'-Indiware'))){
                if(($tblSetting = Account::useService()->getSettingByAccount($tblAccount, TblSetting::ATTR_INDIWARE_CODE))){
                    Account::useService()->destroySetting($tblSetting);
                }
            }
        }

        return new Success('Einstellung wurde gespeichert')
            .ApiConsumerLogin::pipelinereload();
    }

    public function reloadTable()
    {

        return Frontend::getConsumerLoginTable();
    }
}