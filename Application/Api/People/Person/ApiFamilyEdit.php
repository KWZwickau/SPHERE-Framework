<?php

namespace SPHERE\Application\Api\People\Person;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Person\Frontend\FrontendFamily;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
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
        $Dispatcher->registerMethod('loadSimilarPersonMessage');

        $Dispatcher->registerMethod('loadChildContent');

        $Dispatcher->registerMethod('changeSelectedGender');

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
     * @return Pipeline
     */
    public static function pipelineLoadSimilarPersonContent()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SimilarPersonContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadSimilarPersonContent',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function loadSimilarPersonContent()
    {
        $Global = $this->getGlobal();
        $Data = $Global->POST['Data'];

        $Person['FirstName'] = $Data['Child']['FirstName'];
        $Person['LastName'] = $Data['Child']['LastName'];

        return (new FrontendFamily())->loadSimilarPersonContent($Person);
    }

    /**
     * @param $countSimilarPerson
     * @param $name
     * @param $hash
     *
     * @return Pipeline
     */
    public static function pipelineLoadSimilarPersonMessage($countSimilarPerson, $name, $hash)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SimilarPersonMessage'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadSimilarPersonMessage',
        ));

        $ModalEmitter->setPostPayload(array(
            'countSimilarPerson' => intval($countSimilarPerson),
            'name' => $name,
            'hash' => $hash
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param integer $countSimilarPerson
     * @param string $name
     * @param $hash
     *
     * @return Danger|Success
     */
    public function loadSimilarPersonMessage($countSimilarPerson, $name, $hash)
    {
        // todo kommt nicht hier an
//        var_dump('hallo');
        return FrontendFamily::getSimilarPersonMessage($countSimilarPerson, $name, $hash);
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
        return (new FrontendFamily())->getChildContent($Ranking, $Data, $Errors);
    }

    /**
     * @param $Ranking
     *
     * @return Pipeline
     */
    public static function pipelineChangeSelectedGender($Ranking)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SelectedGender' . $Ranking), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'changeSelectedGender',
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
     * @return SelectBox
     */
    public function changeSelectedGender($Ranking)
    {

        $Global = $this->getGlobal();
        $Person = $Global->POST['Data']['S' . $Ranking];

        $genderId = 0;
        if (isset($Person['Salutation'])
            && isset($Person['Gender'])
        ) {
            if (($tblSalutation = Person::useService()->getSalutationById($Person['Salutation']))) {
                if ($tblSalutation->getSalutation() == 'Frau') {
                    $genderId = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                } elseif ($tblSalutation->getSalutation() == 'Herr') {
                    $genderId = TblCommonBirthDates::VALUE_GENDER_MALE;
                }
            }
        }

        return (new FrontendFamily())->getGenderSelectBox($genderId, $Ranking);
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