<?php
namespace SPHERE\Application\Api\Setting\UserAccount;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Setting\User\Account\Account;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Info as InfoMessage;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\System\Extension\Extension;

/**
 * Class SerialLetter
 * @package SPHERE\Application\Api\Setting\UserAccount
 */
class ApiUserAccount extends Extension implements IApiInterface
{

    use ApiTrait;

    const SERVICE_CLASS = 'ServiceClass';
    const SERVICE_METHOD = 'ServiceMethod';

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('openAccountModal');
        $Dispatcher->registerMethod('serviceAccount');
        $Dispatcher->registerMethod('openAccountModalResult');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverAccountModal()
    {

        return (new ModalReceiver('Erstellen der '.new Bold('Benutzer'), null, false))
            ->setIdentifier('Loadingscreen');
    }

    /**
     * @return InlineReceiver
     */
    public static function receiverAccountService()
    {

        return (new InlineReceiver(''))
            ->setIdentifier('AccountService');
    }

    /**
     * @param string $Type (S = Student; C = Custody)
     *
     * @return Pipeline
     */
    public static function pipelineSaveAccount($Type = 'S')
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverAccountModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openAccountModal'
        ));
        $Pipeline->appendEmitter($Emitter);

        $Emitter = new ServerEmitter(self::receiverAccountService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'serviceAccount'
        ));
        $Emitter->setPostPayload(array(
            'Type' => $Type
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    public function openAccountModal()
    {

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new InfoMessage('Dieser Vorgang kann einige Zeit in Anspruch nehmen'
                            .new Container((new ProgressBar(0, 100, 0, 10))
                                ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS))
                        )
                    ),
                    new LayoutColumn(self::receiverAccountService())
                ))
            )
        );
    }

    public function serviceAccount($PersonIdArray = array(), $Type)
    {

        $result = Account::useService()->createAccount($PersonIdArray, $Type);
        return self::pipelineSaveAccountResult($result);
    }

    /**
     * @var array $result
     *
     * @return Pipeline
     */
    public static function pipelineSaveAccountResult($result = array())
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverAccountModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openAccountModalResult'
        ));
        $Emitter->setPostPayload(array(
            'result' => $result
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    public function openAccountModalResult($result = array())
    {

        $Content = '';
        $Time = false;
        if (isset($result['Time']) && $result['Time']) {
            $Time = $result['Time'];
        }

        if (isset($result['AccountExistCount']) && $result['AccountExistCount'] > 0) {
            $Content .= new WarningMessage($result['AccountExistCount'].' Personen haben bereits einen Account (ignoriert)');
        }
        if (isset($result['AddressMissCount']) && $result['AddressMissCount'] > 0) {
            $Content .= new WarningMessage($result['AddressMissCount'].' Personen ohne Hauptadresse (ignoriert)');
        }
        if (isset($result['SuccessCount']) && $result['SuccessCount'] > 0) {
            $Content .= new SuccessMessage($result['SuccessCount'].' Benutzer wurden erfolgreich angelegt.');
        }
        if ($Content == '') {
            $Content = new DangerMessage('Es wurden keine Benutzer angelegt')
                .new Container(new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(new Center(new Standard('Zurück', '/Setting/User/Account/Student/Add')))
                        )
                    )
                ));
        } else {
            $Content .= new Container(new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Center(new Standard('Zurück', '/Setting/User/Account/Student/Add')
                            .new Standard('Export', '/Setting/User/Account/Export', null, array('Time' => $Time))))
                    )
                )
            ));
        }

        return $Content;
//            .new Code(print_r($result, true));
    }
}