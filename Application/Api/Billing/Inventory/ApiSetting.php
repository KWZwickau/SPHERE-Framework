<?php
namespace SPHERE\Application\Api\Billing\Inventory;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiSetting
 * @package SPHERE\Application\Api\Billing\Inventory
 */
class ApiSetting extends Extension implements IApiInterface
{

    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('showEdit');
        $Dispatcher->registerMethod('changeEdit');
        $Dispatcher->registerMethod('changeDisplay');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return InlineReceiver
     */
    public static function receiverDisplaySetting($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('ServiceReceiver'.$Identifier);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModalSetting()
    {

        return (new ModalReceiver())->setIdentifier('ModalReceiver');
    }

    /**
     * @param string $Identifier
     * @param string $FieldLabel
     *
     * @return Pipeline
     */
    public static function pipelineOpenSetting($Identifier, $FieldLabel = '')
    {
        $Receiver = self::receiverModalSetting();
        $ComparePasswordPipeline = new Pipeline();
        $ComparePasswordEmitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $ComparePasswordEmitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'showEdit'
        ));
//        $ComparePasswordEmitter->setLoadingMessage('Information gespeichert.');
        $ComparePasswordEmitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'FieldLabel' => $FieldLabel
        ));
        $ComparePasswordPipeline->appendEmitter($ComparePasswordEmitter);

        return $ComparePasswordPipeline;
    }

    /**
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineSaveSetting($Identifier)
    {
        $Receiver = self::receiverModalSetting();
        $SettingPipeline = new Pipeline();
        $SettingEmitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $SettingEmitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'changeEdit'
        ));
        $SettingEmitter->setPostPayload(array(
            'Identifier' => $Identifier
        ));
        $SettingPipeline->appendEmitter($SettingEmitter);
        $SettingPipeline->appendEmitter((new CloseModal(self::receiverModalSetting()))->getEmitter());
        $Receiver = self::receiverDisplaySetting('', $Identifier);
        $SettingEmitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $SettingEmitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'changeDisplay'
        ));
        $SettingEmitter->setPostPayload(array(
            'Identifier' => $Identifier
        ));
        $SettingPipeline->appendEmitter($SettingEmitter);

        return $SettingPipeline;
    }

    /**
     * @param $Identifier
     * @param $FieldLabel
     *
     * @return string
     */
    public function showEdit($Identifier, $FieldLabel)
    {

        $tblSetting = Setting::useService()->getSettingByIdentifier($Identifier);
        if($tblSetting){
            $Global = $this->getGlobal();
            $Global->POST[$Identifier] = $tblSetting->getValue();
            $Global->savePost();
        }
        return (new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new TextField($Identifier, '', $FieldLabel)
                    ),
                    new FormColumn(
                        (new Primary('Speichern', self::getEndpoint(), new Save()))
                        ->ajaxPipelineOnClick(ApiSetting::pipelineSaveSetting($Identifier))
                    )
                ))
            )
        ))->disableSubmitAction();
    }

    /**
     * @param $Identifier
     *
     * @return string
     */
    public function changeEdit($Identifier)
    {

        $Global = $this->getGlobal();
         if(($Value = $Global->POST[$Identifier])){
             Setting::useService()->createSetting($Identifier, $Value);

             return new Success('Erfolgreich');
         }
        return new Danger('Durch einen Fehler konnte die Einstellung nicht gespeichert werden.');
    }

    /**
     * @param $Identifier
     *
     * @return string
     */
    public function changeDisplay($Identifier)
    {

        // wait to make sure to get the correct answer
        sleep(1);
        if(($tblSetting = Setting::useService()->getSettingByIdentifier($Identifier))){
            return $tblSetting->getValue();
        } else {
            return '';
        }
    }
}