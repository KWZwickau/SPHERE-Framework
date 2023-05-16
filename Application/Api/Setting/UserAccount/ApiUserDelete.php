<?php
namespace SPHERE\Application\Api\Setting\UserAccount;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as ConsumerGatekeeper;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Application\Setting\Authorization\Account\Account as AccountAuth;
use SPHERE\Application\Setting\User\Account\Account;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\ToggleCheckbox;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Info as InfoMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiUserDelete
 * @package SPHERE\Application\Api\Setting\UserAccount
 */
class ApiUserDelete extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('getModalContent');
        $Dispatcher->registerMethod('loadingScreen');
        $Dispatcher->registerMethod('deleteAccounts');
        $Dispatcher->registerMethod('getTableContent');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverAccountModal($Title = '')
    {

        return (new ModalReceiver($Title, new Close()))
            ->setIdentifier('Delete');
    }

    /**
     * @return InlineReceiver
     */
    public static function receiverAccountService()
    {

        return (new InlineReceiver(''))
            ->setIdentifier('Service');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverTable($Content = '')
    {

        return (new BlockReceiver($Content))
            ->setIdentifier('Table');
    }

    /**
     *
     * @param string $GroupByTime
     *
     * @return Pipeline
     */
    public static function pipelineOpenModal($Type = 'STUDENT')
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverAccountModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getModalContent'
        ));
        $Emitter->setPostPayload(array(
            'Type' => $Type
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadingScreen($Type)
    {

        $Pipeline = new Pipeline();
        //Loading
        $Emitter = new ServerEmitter(self::receiverAccountModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'loadingScreen'
        ));
        $Emitter->setPostPayload(array(
            'Type' => $Type
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    public function pipelineDeleteAccounts($Data, $Type)
    {

        $Pipeline = new Pipeline();
        //Delete
        $Emitter = new ServerEmitter(self::receiverAccountService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'deleteAccounts'
        ));
        $Emitter->setPostPayload(array('Data' => $Data));
        $Pipeline->appendEmitter($Emitter);
        //Refresh Table
        $Emitter = new ServerEmitter(self::receiverTable(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getTableContent'
        ));
        $Emitter->setPostPayload(array(
            'Type' => $Type
        ));
        $Pipeline->appendEmitter($Emitter);
        // Close Modal
        $Pipeline->appendEmitter((new CloseModal(self::receiverAccountModal()))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param null   $Data
     *
     * @return string
     */
    public function getModalContent($Data, $Type)
    {

        if($Type == 'STUDENT'){
            $Content = Account::useFrontend()->getStudentTable(true);
        } else {
            $Content = Account::useFrontend()->getCustodyTable(true);
        }
        $showDeleting = true;
        if($Content instanceof Warning){
            $showDeleting = false;
        }

        $Danger = new Danger('Löschen', '#', new Remove(), $Data, 'Löschen ist unwiderruflich');
        $DangerText = 'Hiermit werden die ausgewählten Accounts dauerhaft gelöscht';
        if($Type == 'STUDENT'){
            $DangerText = 'Hiermit werden die ausgewählten Schüler-Accounts dauerhaft gelöscht';
            // nur bei Schülern
            $IsUCSMandant = false;
            if(($tblConsumer = ConsumerGatekeeper::useService()->getConsumerBySession())){
                if(ConsumerGatekeeper::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_UCS)){
                    $IsUCSMandant = true;
                }
            }
            if($IsUCSMandant){
                $DangerText .= new Container('Nach dem Löschen der Accounts in der Schulsoftware werden diese auch über die UCS Schnittstelle aus dem DLLP Projekt gelöscht.');
            }
        } elseif($Type == 'CUSTODY'){
            $DangerText = 'Hiermit werden die ausgewählten Sorgeberechtigten-Accounts dauerhaft gelöscht';
        }

        $DangerText = new DangerMessage($DangerText);

        $form = new Form(new FormGroup(new FormRow(array(
            new FormColumn(
                $Content
            ),
            new FormColumn(
                ($showDeleting ? $DangerText : new WarningText(''))
            ),
            new FormColumn(
                ($showDeleting ? $Danger->ajaxPipelineOnClick(ApiUserDelete::pipelineLoadingScreen($Type)) : new WarningText(''))
            ),
        ))));

        $InfoText = new Info('Es werden nur Einträge für das vereinfachte löschen angezeigt, welche durch in Infosymbol auffällig sind');
        if($Type == 'STUDENT'){
            $InfoText = new Info('Auflistung von ehemaliger Schülern, welche nicht mehr in der Personengruppe Schüler sind');
        } elseif($Type == 'CUSTODY'){
            $InfoText = new Info('Auflistung von Sorgeberechtigten, deren Kinder nicht mehr in der Personengruppe Schüler sind');
        }
        $ToggleButton = '';
        if($showDeleting){
            $ToggleButton = new ToggleCheckbox('Alle auswählen/abwählen', $form);
        }

        return $InfoText.$ToggleButton.$form;
    }

    /**
     * @param array $Data
     * @param $Type
     *
     * @return Layout
     */
    public function loadingScreen($Data, $Type)
    {

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new InfoMessage('Lädt'
                            .new Container((new ProgressBar(0, 100, 0, 10))
                                ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS))
                        )
                    ),
//                    new LayoutColumn(
//                    // ermöglicht das schneller runterscrollen bei Ajax-Click
//                        '<div style="height: 780px">&nbsp;</div>'
//                    ),
//                    new LayoutColumn(
//                        print_r($Data, true)
//                    )
                    new LayoutColumn(
                        self::pipelineDeleteAccounts($Data, $Type)
                    ),
                ))
            )
        );
    }

//    /**
//     * @return Layout
//     */
    public function deleteAccounts($Data)
    {

        if(!empty($Data)){
            foreach($Data as $UserAccountId => $AccountId){
                $tblUserAccount = Account::useService()->getUserAccountById($UserAccountId);
                $tblAccount = AccountAuth::useService()->getAccountById($AccountId);
                Account::useService()->removeUserAccount($tblUserAccount);
                AccountAuth::useService()->destroyAccount($tblAccount);
            }
        }
        return '';
    }

    /**
     * @return Warning|TableData
     */
    public function getTableContent($Type)
    {
        if($Type == 'STUDENT'){
            return Account::useFrontend()->getStudentTable();
        } else {
            return Account::useFrontend()->getCustodyTable();
        }

    }
}