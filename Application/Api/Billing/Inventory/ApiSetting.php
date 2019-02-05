<?php
namespace SPHERE\Application\Api\Billing\Inventory;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
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
        // PersonGroup
        $Dispatcher->registerMethod('showPersonGroup');
        $Dispatcher->registerMethod('showFormPersonGroup');
        $Dispatcher->registerMethod('changePersonGroup');
        //Other Setting's
        $Dispatcher->registerMethod('showSetting');
        $Dispatcher->registerMethod('showFormSetting');
        $Dispatcher->registerMethod('changeSetting');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverPersonGroup($Content = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('PersonGroupReceiver');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverSetting($Content = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('SettingReceiver');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineShowPersonGroup()
    {
        $Receiver = self::receiverPersonGroup();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'showPersonGroup'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineShowFormPersonGroup()
    {
        $Receiver = self::receiverPersonGroup();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'showFormPersonGroup'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSavePersonGroup()
    {
        $Receiver = self::receiverPersonGroup();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'changePersonGroup'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineShowSetting()
    {
        $Receiver = self::receiverSetting();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'showSetting'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineShowFormSetting()
    {
        $Receiver = self::receiverSetting();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'showFormSetting'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSaveSetting()
    {
        $Receiver = self::receiverSetting();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'changeSetting'
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Layout
     */
    public function showPersonGroup()
    {

        return Setting::useFrontend()->displayPersonGroup();
    }

    /**
     * @return Layout
     */
    public function showFormPersonGroup()
    {

        return Setting::useFrontend()->formPersonGroup();
    }

    /**
     * @param array $PersonGroup
     *
     * @return Layout|string
     */
    public function changePersonGroup($PersonGroup)
    {

        if(isset($PersonGroup)
            && !empty($PersonGroup)
            && ($GroupIdList = $PersonGroup)){
            // clear all PersonGroup that exists but not be selected
            $tblSettingGroupPersonExist = Setting::useService()->getSettingGroupPersonAll();
            foreach($tblSettingGroupPersonExist as $tblSettingGroupPerson) {
                $tblGroup = $tblSettingGroupPerson->getServiceTblGroupPerson();
                if(!in_array($tblGroup->getId(), $GroupIdList)){
                    Setting::useService()->removeSettingGroupPerson($tblSettingGroupPerson);
                }
            }
            foreach($GroupIdList as $GroupId) {
                $tblGroup = Group::useService()->getGroupById($GroupId);
                Setting::useService()->createSettingGroupPerson($tblGroup);
            }
        }
        return Setting::useFrontend()->displayPersonGroup();
    }

    /**
     * @return Layout
     */
    public function showSetting()
    {

        return Setting::useFrontend()->displaySetting();
    }

    /**
     * @return Layout
     */
    public function showFormSetting()
    {

        return Setting::useFrontend()->formSetting();
    }

    /**
     * @param $Setting
     *
     * @return Layout
     */
    public function changeSetting($Setting)
    {

        $DebtorNumberCount = (isset($Setting['DebtorNumberCount']) ? $Setting['DebtorNumberCount'] : 7);
        Setting::useService()->createSetting(TblSetting::IDENT_DEBTOR_NUMBER_COUNT, $DebtorNumberCount);
        $IsDebtorNumberNeed = (isset($Setting['IsDebtorNumberNeed']) ? true : false);
        Setting::useService()->createSetting(TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED, $IsDebtorNumberNeed);
        $IsSepaAccountNeed = (isset($Setting['IsSepaAccountNeed']) ? true : false);
        Setting::useService()->createSetting(TblSetting::IDENT_IS_SEPA_ACCOUNT_NEED, $IsSepaAccountNeed);

        return Setting::useFrontend()->displaySetting();
    }
}