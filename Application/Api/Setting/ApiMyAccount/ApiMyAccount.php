<?php

namespace SPHERE\Application\Api\Setting\ApiMyAccount;

use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\System\Extension\Extension;

class ApiMyAccount extends Extension implements IApiInterface
{

    const API_DISPATCHER = 'MethodName';

    public static function registerApi()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__, __CLASS__.'::ApiDispatcher'
        ));
    }

    /**
     * @param string $MethodName Callable Method
     *
     * @return string
     */
    public function ApiDispatcher($MethodName = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('FormUpdatePassword');
//        $Dispatcher->registerMethod('ServiceUpdatePassword');
        $Dispatcher->registerMethod('ComparePassword');

        return $Dispatcher->callMethod($MethodName);
    }

    /**
     * @return Route
     */
    public static function getRoute()
    {
        return new Route(__CLASS__);
    }

    /**
     * @param null|array $Receiver
     *
     * @return string
     */
    public function FormUpdatePassword($Receiver = null)
    {

        return (string)$this->pipelineFormUpdatePassword($Receiver);
    }

    /**
     * @param null|array $Receiver
     *
     * @return Form
     */
    private function pipelineFormUpdatePassword($Receiver)
    {
        $CreatePersonReceiver = new BlockReceiver();
        $CreatePersonReceiver->setIdentifier($Receiver['FormUpdatePassword']);

        $CreatePersonPipeline = new Pipeline();

        $CreatePersonEmitter = new ServerEmitter($CreatePersonReceiver, ApiMyAccount::getRoute());
        $CreatePersonEmitter->setGetPayload(array(
            ApiMyAccount::API_DISPATCHER => 'ServiceUpdatePassword'
        ));
        $CreatePersonEmitter->setPostPayload(array(
            'Receiver' => $Receiver
        ));
        $CreatePersonPipeline->appendEmitter($CreatePersonEmitter);

        $Form = $this->formPassword($Receiver);
        $Form->appendFormButton(new Primary('Speichern', new Save()));
//        $Form->ajaxPipelineOnSubmit($CreatePersonPipeline);

        return $Form;
    }

    /**
     * @return Form
     */
    private function formPassword($Receiver)
    {

        $ValidatePasswordReceiver = new BlockReceiver();
        $ValidatePasswordReceiver->setIdentifier($Receiver['ComparePassword']);
        $ValidatePasswordPipeline = new Pipeline();
//        $ValidatePasswordPipeline->setLoadingMessage('Suche ähnliche Personen');
        $ValidatePasswordEmitter = new ServerEmitter($ValidatePasswordReceiver, ApiMyAccount::getRoute());
        $ValidatePasswordEmitter->setGetPayload(array(
            ApiMyAccount::API_DISPATCHER => 'TableSimilarPerson'
        ));
        $ValidatePasswordEmitter->setPostPayload(array(
            'Receiver' => $Receiver
        ));
        $ValidatePasswordPipeline->appendEmitter($ValidatePasswordEmitter);

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Passwort', array(
                            new PasswordField('CredentialLock', 'Neues Passwort',
                                'Neues Passwort &nbsp;&nbsp;'
                                .new Small(new Warning('(Das Passwort muss mindestens 8 Zeichen lang sein.)')),
                                new Lock()),
                            new PasswordField('CredentialLockSafety', 'Passwort wiederholen',
                                'Passwort wiederholen',
                                new Repeat())
                        ), Panel::PANEL_TYPE_INFO)
                    ),
                ))
            )
        );
    }

//    private function ServiceUpdatePassword()
//    {
//
//        return false;
//    }

    /**
     * @return Panel
     */
    public function ComparePassword()
    {

        return new Panel('Passwort Richtlinien',
            array(
                'Das Passwort muss 3 von 4 Kriterien erfüllen und mindestens 8 Zeichen lang sein.',
                '1. mindestens ein kleinbuchstabe',
                '2. mindestens ein Großbuchstabe',
                '3. mindestens ein Sonderzeichen',
                '4. mindestens eine Zahl',
            ),
            Panel::PANEL_TYPE_SUCCESS);
    }
}