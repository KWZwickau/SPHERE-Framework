<?php
namespace SPHERE\Application\Api\Billing\Inventory;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title as FormTitle;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
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
        $Dispatcher->registerMethod('changePersonGroup');
        $Dispatcher->registerMethod('changeDisplay');
        $Dispatcher->registerMethod('changeDisplayPersonGroup');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param string $Identifier
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
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'showEdit'
        ));
//        $ComparePasswordEmitter->setLoadingMessage('Information gespeichert.');
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'FieldLabel' => $FieldLabel
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineSaveSetting($Identifier)
    {
        // Save Settings from Modal form
        $Receiver = self::receiverModalSetting();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'changeEdit'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier
        ));
        $Pipeline->appendEmitter($Emitter);
        // Close Modal
        $Pipeline->appendEmitter((new CloseModal(self::receiverModalSetting()))->getEmitter());
        // Reload Page Info
        $Receiver = self::receiverDisplaySetting('', $Identifier);
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'changeDisplay'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineSavePersonGroupSetting($Identifier)
    {
        // Save Settings from Modal form
        $Receiver = self::receiverModalSetting();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'changePersonGroup'
        ));
        $Pipeline->appendEmitter($Emitter);
        // Close Modal
        $Pipeline->appendEmitter((new CloseModal(self::receiverModalSetting()))->getEmitter());
        // Reload Page Info
        $Receiver = self::receiverDisplaySetting('', $Identifier);
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'changeDisplayPersonGroup'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param $Identifier
     * @param $FieldLabel
     *
     * @return string
     */
    public function showEdit($Identifier, $FieldLabel)
    {

        // available PersonGroups
        if('PersonGroup' === $FieldLabel){
            if(($tblSettingGroupList = Setting::useService()->getSettingGroupPersonAll())) {
                $Global = $this->getGlobal();
                foreach($tblSettingGroupList as $tblSettingGroup){
                    if(($tblGroup = $tblSettingGroup->getServiceTblGroupPerson())){
                        $Global->POST['PersonGroup'][$tblGroup->getId()] = $tblGroup->getId();
                    }
                }
                $Global->savePost();
            }
            $LeftList = $RightList = array();
            if(($tblGroupAll = Group::useService()->getGroupAll())){
                $tblGroupAll = $this->getSorter($tblGroupAll)->sortObjectBy('Name');
                // sort left Standard, right Individual
                array_walk($tblGroupAll, function (TblGroup $tblGroup) use (&$LeftList, &$RightList){
                    if($tblGroup->getMetaTable()){
                        $LeftList[] = new CheckBox('PersonGroup['.$tblGroup->getId().']', $tblGroup->getName(), $tblGroup->getId());
                    } else {
                        $RightList[] = new CheckBox('PersonGroup['.$tblGroup->getId().']', $tblGroup->getName(), $tblGroup->getId());
                    }
                });

                return (new Form(
                    new FormGroup(
                        new FormRow(array(
                            new FormColumn(new FormTitle('Standard Personengruppen'), 6),
                            new FormColumn(new FormTitle('Individuelle Personengruppen'), 6),
                            new FormColumn($LeftList, 6),
                            new FormColumn($RightList, 6),
                            new FormColumn(
                                (new Primary('Speichern', self::getEndpoint(), new Save()))
                                    ->ajaxPipelineOnClick(ApiSetting::pipelineSavePersonGroupSetting($Identifier))
                            )
                        ))
                    )
                ));
            }
        }

        // other Setting's
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
     * @return string
     */
    public function changePersonGroup()
    {

        $Global = $this->getGlobal();
         if(isset($Global->POST['PersonGroup'])
             && ($GroupIdList = $Global->POST['PersonGroup'])){
             // clear all PersonGroup that exists but not be selected
             $tblSettingGroupPersonExist = Setting::useService()->getSettingGroupPersonAll();
             foreach($tblSettingGroupPersonExist as $tblSettingGroupPerson){
                 $tblGroup = $tblSettingGroupPerson->getServiceTblGroupPerson();
                 if(!in_array($tblGroup->getId(),$GroupIdList)){
                     Setting::useService()->removeSettingGroupPerson($tblSettingGroupPerson);
                 }
             }
             foreach($GroupIdList as $GroupId){
                 $tblGroup = Group::useService()->getGroupById($GroupId);
                 Setting::useService()->createSettingGroupPerson($tblGroup);
             }
             return new Success('Erfolgreich');
         } else {
             return new Danger('Bearbeitung ohne Personengruppen nicht möglich!');
         }

    }

    /**
     * @param $Identifier
     *
     * @return string
     */
    public function changeDisplay($Identifier)
    {

        // wait to make sure to get the correct answer
        $this->refreshWait(400);

        if(($tblSetting = Setting::useService()->getSettingByIdentifier($Identifier))){
            return $tblSetting->getValue();
        } else {
            return new DangerText('Einstellung konnte nicht geladen werden!');
        }
    }

    /**
     * @return string
     */
    public function changeDisplayPersonGroup()
    {

        // wait to make sure to get the correct answer
        $this->refreshWait(400);

        return Setting::useFrontend()->displayPersonGroupLoad();
    }

    private function refreshWait($MilliSeconds)
    {
        return usleep($MilliSeconds * 1000);
    }
}