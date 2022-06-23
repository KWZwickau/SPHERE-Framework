<?php
namespace SPHERE\Application\Api\Document\Storage;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Frontend\FrontendPersonPicture;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiPersonPicture
 *
 * @package SPHERE\Application\Api\Document\Storage
 */
class ApiPersonPicture extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadPersonPictureContent');
        $Dispatcher->registerMethod('editPersonPictureContent');
        $Dispatcher->registerMethod('savePersonPicture');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverBlock($Content = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('PersonPictureService');
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver())->setIdentifier('PersonPicture');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadPersonPictureContent()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadPersonPictureContent',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenModalPersonPicture($PersonId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editPersonPictureContent',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadPersonPictureContent($PersonId = null)
    {

        return (new FrontendPersonPicture())->getPersonPictureModalContent($PersonId);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSavePersonPicture($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'PersonContent'), self::getEndpoint());
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'savePersonPicture',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }
    /**
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineCloseModal()
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(ApiPersonPicture::receiverModal()))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editPersonPictureContent($PersonId = null)
    {

        return (new FrontendPersonPicture())->getPersonPictureModalContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function savePersonPicture($PersonId, $FileUpload)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        if(!isset($FileUpload) || !$FileUpload){
            $Content = (new FrontendPersonPicture())->getPersonPictureContent();
            $Content.= new Danger('Es wird ein Bild für die Speicherung benötigt');
            return $Content;
        }

        Storage::useService()->createPersonPicture($PersonId, $FileUpload);

        return new Success('Bild erfolgreich erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . ApiPersonPicture::pipelineLoadPersonPictureContent()
            . ApiPersonPicture::pipelineCloseModal();
    }
}