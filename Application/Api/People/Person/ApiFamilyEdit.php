<?php

namespace SPHERE\Application\Api\People\Person;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Person\Frontend\FrontendFamily;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiFamilyEdit
 *
 * @package SPHERE\Application\Api\People\Person
 */
class ApiFamilyEdit extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadSimilarPersonContent');

        $Dispatcher->registerMethod('loadChildContent');

        $Dispatcher->registerMethod('changeSelectedGender');

        $Dispatcher->registerMethod('loadSiblingCheckBox');

        $Dispatcher->registerMethod('loadAddressContent');
        $Dispatcher->registerMethod('loadPhoneContent');
        $Dispatcher->registerMethod('loadMailContent');

        return $Dispatcher->callMethod($Method);
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
     * @param $key
     *
     * @return Pipeline
     */
    public static function pipelineLoadSimilarPersonContent($key)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SimilarPersonContent_' . $key), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadSimilarPersonContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'key' => $key
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function loadSimilarPersonContent($key)
    {
        $Global = $this->getGlobal();
        $Data = $Global->POST['Data'];

        $Person['FirstName'] = $Data[$key]['FirstName'];
        $Person['LastName'] = $Data[$key]['LastName'];

        return (new FrontendFamily())->loadSimilarPersonContent($Person, $key);
    }

    /**
     * @param $Ranking
     * @param $Data
     * @param $Errors
     *
     * @return Pipeline
     */
    public function pipelineLoadChildContent($Ranking, $Data, $Errors)
    {
//        $Global = $this->getGlobal();
//        $IsSibling = isset($Global->POST['IsSibling']);

        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ChildContent_' . $Ranking), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadChildContent',
        ));

        $ModalEmitter->setPostPayload(array(
            'Ranking' => $Ranking,
            'Data' => $Data,
            'Errors' => $Errors
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Ranking
     * @param $Data
     * @param $Errors
     *
     * @return string
     */
    public function loadChildContent($Ranking, $Data, $Errors)
    {
        return (new FrontendFamily())->getChildContent($Ranking, $Data, $Errors)
            . ($Ranking == '2' ? self::pipelineLoadSiblingCheckBox('1') : '');
    }

    /**
     * @param $Ranking
     *
     * @return Pipeline
     */
    public static function pipelineChangeSelectedGender($Ranking, $Type)
    {
        $Pipeline = new Pipeline(false);
        if($Type == 'C'){
            $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SelectedGenderChild' . $Ranking), self::getEndpoint());
        } else {
            $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SelectedGender' . $Ranking), self::getEndpoint());
        }
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'changeSelectedGender',
        ));
        $ModalEmitter->setPostPayload(array(
            'Ranking' => $Ranking,
            'Type' => $Type
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Ranking
     *
     * @return SelectBox
     */
    public function changeSelectedGender($Ranking, $Type = 'S')
    {

        $Global = $this->getGlobal();
        $Person = $Global->POST['Data'][$Type . $Ranking];

        $genderId = 0;
        if (isset($Person['Salutation'])
            && isset($Person['Gender'])
        ) {
            if (($tblSalutation = Person::useService()->getSalutationById($Person['Salutation']))) {
                if ($tblSalutation->getSalutation() == TblSalutation::VALUE_WOMAN
                || $tblSalutation->getSalutation() == TblSalutation::VALUE_STUDENT_FEMALE) {
                    $genderId = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                } elseif ($tblSalutation->getSalutation() == TblSalutation::VALUE_MAN
                    || $tblSalutation->getSalutation() == TblSalutation::VALUE_STUDENT) {
                    $genderId = TblCommonBirthDates::VALUE_GENDER_MALE;
                }
            }
        }

        return (new FrontendFamily())->getGenderSelectBox($genderId, $Ranking, $Type);
    }

    /**
     * @param $Ranking
     * @param $PersonIdList
     * @param $Data
     * @param $Errors
     *
     * @return Pipeline
     */
    public function pipelineLoadAddressContent($Ranking, $PersonIdList, $Data, $Errors)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'AddressContent_' . $Ranking), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadAddressContent',
        ));

        $ModalEmitter->setPostPayload(array(
            'Ranking' => $Ranking,
            'PersonIdList' => $PersonIdList,
            'Data' => $Data,
            'Errors' => $Errors
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Ranking
     * @param $PersonIdList
     * @param null $Data
     * @param null $Errors
     *
     * @return string
     */
    public function loadAddressContent($Ranking, $PersonIdList, $Data = null, $Errors = null)
    {
        return (new FrontendFamily())->getAddressContent($Ranking, $PersonIdList, $Data, $Errors);
    }

    /**
     * @param $Ranking
     *
     * @return Pipeline
     */
    public function pipelineLoadSiblingCheckBox($Ranking)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SiblingCheckBox_' . $Ranking), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadSiblingCheckBox',
        ));

        $ModalEmitter->setPostPayload(array(
            'Ranking' => $Ranking
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Ranking
     *
     * @return string
     */
    public function loadSiblingCheckBox($Ranking)
    {
        return (new FrontendFamily())->getSiblingCheckBox($Ranking);
    }

    /**
     * @param $Ranking
     * @param $PersonIdList
     * @param $Data
     * @param $Errors
     *
     * @return Pipeline
     */
    public function pipelineLoadPhoneContent($Ranking, $PersonIdList, $Data, $Errors)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'PhoneContent_' . $Ranking), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadPhoneContent',
        ));

        $ModalEmitter->setPostPayload(array(
            'Ranking' => $Ranking,
            'PersonIdList' => $PersonIdList,
            'Data' => $Data,
            'Errors' => $Errors
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Ranking
     * @param $PersonIdList
     * @param null $Data
     * @param null $Errors
     *
     * @return string
     */
    public function loadPhoneContent($Ranking, $PersonIdList, $Data = null, $Errors = null)
    {
        return (new FrontendFamily())->getPhoneContent($Ranking, $PersonIdList, $Data, $Errors);
    }

    /**
     * @param $Ranking
     * @param $PersonIdList
     * @param $Data
     * @param $Errors
     *
     * @return Pipeline
     */
    public function pipelineLoadMailContent($Ranking, $PersonIdList, $Data, $Errors)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'MailContent_' . $Ranking), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadMailContent',
        ));

        $ModalEmitter->setPostPayload(array(
            'Ranking' => $Ranking,
            'PersonIdList' => $PersonIdList,
            'Data' => $Data,
            'Errors' => $Errors
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Ranking
     * @param $PersonIdList
     * @param null $Data
     * @param null $Errors
     *
     * @return string
     */
    public function loadMailContent($Ranking, $PersonIdList, $Data = null, $Errors = null)
    {
        return (new FrontendFamily())->getMailContent($Ranking, $PersonIdList, $Data, $Errors);
    }
}