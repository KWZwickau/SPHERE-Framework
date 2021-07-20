<?php

namespace SPHERE\Application\Api\Setting\Authorization;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiAccount
 *
 * @package SPHERE\Application\Api\Setting\Authorization
 */
class ApiAccount  extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadMassReplaceContent');
        $Dispatcher->registerMethod('loadMessageContent');

        $Dispatcher->registerMethod('openMassReplaceModal');
        $Dispatcher->registerMethod('saveMassReplaceModal');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {
        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReceiver');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock($Content = '', $Identifier = '')
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
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
     * @param null $RoleId
     * @param null $PersonGroupId
     *
     * @return Pipeline
     */
    public static function pipelineLoadMassReplaceContent($RoleId = null, $PersonGroupId = null)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'MassReplaceContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadMassReplaceContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'RoleId' => $RoleId,
            'PersonGroupId' => $PersonGroupId
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $RoleId
     * @param null $PersonGroupId
     *
     * @return string
     */
    public function loadMassReplaceContent($RoleId = null, $PersonGroupId = null)
    {
        return Account::useFrontend()->loadMassReplaceContent($RoleId, $PersonGroupId)
            . self::pipelineLoadMessageContent($RoleId);
    }

    /**
     * @param null $RoleId
     *
     * @return Pipeline
     */
    public static function pipelineLoadMessageContent($RoleId = null)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'MessageContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadMessageContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'RoleId' => $RoleId
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $RoleId
     *
     * @return string
     */
    public function loadMessageContent($RoleId = null)
    {
        return Account::useFrontend()->loadMessageContent($RoleId);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenMassReplaceModal()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openMassReplaceModal',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Danger|string
     */
    public function openMassReplaceModal()
    {
        return Account::useFrontend()->openMassReplaceModal();
    }

    /**
     * @param $RoleId
     * @param $Accounts
     *
     * @return Pipeline
     */
    public static function pipelineMassReplaceSave($RoleId, $Accounts)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveMassReplaceModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'RoleId' => $RoleId,
            'Accounts' => $Accounts
        ));

        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $RoleId
     * @param $Accounts
     *
     * @return Danger|string
     */
    public function saveMassReplaceModal($RoleId, $Accounts)
    {
        if (($tblRole = Access::useService()->getRoleById($RoleId))) {
            $tblAccountList = array();
            if ($Accounts) {
                foreach ($Accounts as $AccountId => $value) {
                    if (($tblAccount = Account::useService()->getAccountById($AccountId))) {
                        // bei Rolle nur für Hardware-Token, Benutzerkonten ohne entsprechenden Ausfiltern
                        // wird auch schon über das Frontend ausgefiltert
                        if ($tblRole->isSecure()
                            && ($tblIdentification = $tblAccount->getServiceTblIdentification())
                        ) {
                            switch ($tblIdentification->getName()) {
                                case 'AuthenticatorApp':
                                    $isAdd = true;
                                    break;
                                case 'Token':
                                    // Token muss gesetzt sein
                                    if ($tblAccount->getServiceTblToken()) {
                                        $isAdd = true;
                                    } else {
                                        $isAdd = false;
                                    }
                                    break;
                                default : $isAdd = false;
                            }
                        } else {
                            $isAdd = true;
                        }

                        if ($isAdd) {
                            $tblAccountList[] = $tblAccount;
                        }
                    }
                }
            }

            if (!empty($tblAccountList)) {
                $count = \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account::useService()->bulkAddAccountAuthorization(
                    $tblRole,
                    $tblAccountList
                );
            } else {
                $count = 0;
            }

            if ($count > 0) {
                return new Success('Es wurde für ' . $count . ' Benutzerkonto das Benutzerrecht: '
                    . new Bold($tblRole->getName()) . ' hinzugefügt.')
                    // todo tabelle dynamisch neu laden
//                    . self::pipelineAccountListContent()
                    . self::pipelineClose();
            } else {
                return new Warning('Es wurden für kein Benutzerkonto das Benutzerrecht: '
                        . new Bold($tblRole->getName()) . ' hinzugefügt.')
                    // todo tabelle dynamisch neu laden
//                    . self::pipelineAccountListContent()
                    . self::pipelineClose();
            }
        } else {
            return new Danger('Das Benutzerrecht wurde nicht gefunden. Die Daten konnten nicht gespeichert werden') . self::pipelineClose();
        }
    }
}