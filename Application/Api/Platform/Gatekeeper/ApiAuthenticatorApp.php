<?php

namespace SPHERE\Application\Api\Platform\Gatekeeper;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authentication\TwoFactorApp\TwoFactorApp;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiAuthenticatorApp
 *
 * @package SPHERE\Application\Api\Platform\Gatekeeper
 */
class ApiAuthenticatorApp extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('openShowQrCodeModal');

        $Dispatcher->registerMethod('openResetQrCodeModal');
        $Dispatcher->registerMethod('saveResetQrCodeModal');

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
     * @param int $AccountId
     *
     * @return Pipeline
     */
    public static function pipelineOpenShowQrCodeModal($AccountId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openShowQrCodeModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'AccountId' => $AccountId
        ));

        $Pipeline->setLoadingMessage('QR-Code wird erstellt. Bitte warten ...');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $AccountId
     *
     * @return string
     */
    public function openShowQrCodeModal($AccountId)
    {
        if (!($tblAccount = Account::useService()->getAccountById($AccountId))) {
            return new Danger('Das Benutzerkonto wurde nicht gefunden', new Exclamation());
        }

        if (!($secret = $tblAccount->getAuthenticatorAppSecret())) {
            return new Danger('Es wurde noch kein Secret für dieses Benutzerkonto erstellt!', new Exclamation());
        }

        $twoFactorApp = new TwoFactorApp();

        return
            new Title('QR-Code für ' . $tblAccount->getUsername())
//            . new Center('<img src="' . $twoFactorApp->getQRCodeImageAsDataUri($secret) . '">');
                . new Center($twoFactorApp->getBaconQrCode($secret));
    }

    /**
     * @param int $AccountId
     *
     * @return Pipeline
     */
    public static function pipelineOpenResetQrCodeModal($AccountId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openResetQrCodeModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'AccountId' => $AccountId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $AccountId
     *
     * @return string
     */
    public function openResetQrCodeModal($AccountId)
    {
        if (!($tblAccount = Account::useService()->getAccountById($AccountId))) {
            return new Danger('Das Benutzerkonto wurde nicht gefunden', new Exclamation());
        }

        return // new Title(new Repeat() . ' QR-Code zurücksetzen') .
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Wollen Sie den QR-Code wirklich zurücksetzen?',
                                array(
                                    new Exclamation() . ' Wenn der QR-Code zurückgesetzt wird, kann der Benutzer sich 
                                        nicht mehr mit der zuvor registrierten Authentificator App anmelden. '
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineResetQrCodeSave($AccountId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                ))
            );
    }

    /**
     * @param int $AccountId
     *
     * @return Pipeline
     */
    public static function pipelineResetQrCodeSave($AccountId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveResetQrCodeModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'AccountId' => $AccountId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $AccountId
     *
     * @return Danger|string
     */
    public function saveResetQrCodeModal($AccountId)
    {
        if (!($tblAccount = Account::useService()->getAccountById($AccountId))) {
            return new Danger('Das Benutzerkonto wurde nicht gefunden', new Exclamation());
        }

        $twoFactorApp = new TwoFactorApp();
        if (Account::useService()->changeAuthenticatorAppSecret($tblAccount, $twoFactorApp->createSecret())) {
            return new Success('Für das Benutzerkonto wurde erfolgreich ein neuer QR-Code erzeugt.')
                . self::pipelineClose();
        } else {
            return new Danger('Die Fehlzeit konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }
}