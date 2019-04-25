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
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Icon\Repository\ChevronUp;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
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
        // SepaInfo
        $Dispatcher->registerMethod('showSepaInfo');

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
     * @param string $Idenifier
     *
     * @return BlockReceiver
     */
    public static function receiverSetting($Content = '', $Idenifier = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('SettingReceiver'.$Idenifier);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver())->setIdentifier('ShowModal');
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
     * @param string $Category
     *
     * @return Pipeline
     */
    public static function pipelineShowSetting($Category)
    {
        $Receiver = self::receiverSetting('', $Category);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'showSetting'
        ));
        $Emitter->setPostPayload(array(
            'Category' => $Category
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Category
     *
     * @return Pipeline
     */
    public static function pipelineShowFormSetting($Category)
    {

        $Receiver = self::receiverSetting('', $Category);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'showFormSetting'
        ));
        $Emitter->setPostPayload(array(
            'Category' => $Category
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSaveSetting($Category)
    {
        $Receiver = self::receiverSetting('', $Category);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'changeSetting'
        ));
        $Emitter->setPostPayload(array(
            'Category' => $Category
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineShowSepaInfo()
    {
        $Receiver = self::receiverModal();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiSetting::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiSetting::API_TARGET => 'showSepaInfo'
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
     * @return string
     */
    public function showFormPersonGroup()
    {

        return Setting::useFrontend()->formPersonGroup();
    }

    /**
     * @return string
     */
    public function showSepaInfo()
    {

        $Content = new Headline('Welche Auswirkungen hat diese Option?');
        $Content .= new Ruler();
        $Content .= new Container(new Minus().' in Verwendung der Bezahlart "SEPA-Lastschrift" werden folgende Felder zu Pflichtangaben:');
        $Content .= new Container('&nbsp;&nbsp;'.new ChevronUp().' Kontodaten');
        $Content .= new Container('&nbsp;&nbsp;'.new ChevronUp().' Mandatsreferenznummer');
        $Content .= new Container(new Minus().' Ermöglicht den Download einer XML-Datei (SEPA) für externe Programme.');
        $Content .= new Container(new Minus().' Weitere Anpassungen werden noch vorgenommen.');
        return $Content;
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
                    Setting::useService()->destroySettingGroupPerson($tblSettingGroupPerson);
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
     * @param string $Category
     *
     * @return Layout
     */
    public function showSetting($Category)
    {

        return Setting::useFrontend()->displaySetting($Category);
    }

    /**
     * @param $Category
     *
     * @return Layout
     */
    public function showFormSetting($Category)
    {

        return Setting::useFrontend()->formSetting($Category);
    }

    /**
     * @param array  $Setting
     * @param string $Category
     *
     * @return Layout
     */
    public function changeSetting($Setting, $Category)
    {

        switch($Category){
            case TblSetting::CATEGORY_REGULAR:
                $DebtorNumberCount = (isset($Setting[TblSetting::IDENT_DEBTOR_NUMBER_COUNT]) ? $Setting[TblSetting::IDENT_DEBTOR_NUMBER_COUNT] : 7);
                Setting::useService()->createSetting(TblSetting::IDENT_DEBTOR_NUMBER_COUNT, $DebtorNumberCount);
                $IsDebtorNumberNeed = (isset($Setting[TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED]) ? true : false);
                Setting::useService()->createSetting(TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED, $IsDebtorNumberNeed);
                $IsAutoDebtorNumber = (isset($Setting[TblSetting::IDENT_IS_AUTO_DEBTOR_NUMBER]) ? true : false);
                Setting::useService()->createSetting(TblSetting::IDENT_IS_AUTO_DEBTOR_NUMBER, $IsAutoDebtorNumber);
                $IsAutoReferenceNumber = (isset($Setting[TblSetting::IDENT_IS_AUTO_REFERENCE_NUMBER]) ? true : false);
                Setting::useService()->createSetting(TblSetting::IDENT_IS_AUTO_REFERENCE_NUMBER, $IsAutoReferenceNumber);
            break;
            case TblSetting::CATEGORY_SEPA:
                $IsSepaAccountNeed = (isset($Setting[TblSetting::IDENT_IS_SEPA]) ? true : false);
                Setting::useService()->createSetting(TblSetting::IDENT_IS_SEPA, $IsSepaAccountNeed);
                $Adviser = (isset($Setting[TblSetting::IDENT_ADVISER]) ? $Setting[TblSetting::IDENT_ADVISER] : '');
                Setting::useService()->createSetting(TblSetting::IDENT_ADVISER, $Adviser);
                $numberLength = (isset($Setting[TblSetting::IDENT_SEPA_ACCOUNT_NUMBER_LENGTH]) ? $Setting[TblSetting::IDENT_SEPA_ACCOUNT_NUMBER_LENGTH] : 6);
                Setting::useService()->createSetting(TblSetting::IDENT_SEPA_ACCOUNT_NUMBER_LENGTH, $numberLength);
                $IsWorkerAcronym = (isset($Setting[TblSetting::IDENT_IS_WORKER_ACRONYM]) ? true : false);
                Setting::useService()->createSetting(TblSetting::IDENT_IS_WORKER_ACRONYM, $IsWorkerAcronym);
            break;
        }

        return Setting::useFrontend()->displaySetting($Category);
    }
}