<?php
namespace SPHERE\Application\Api\Document\Storage;

use MOC\V\Core\FileSystem\FileSystem;
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
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Window\RedirectScript;
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
        $Dispatcher->registerMethod('removePersonPicture');

        $Dispatcher->registerMethod('showPersonPicture');

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

        return (new ModalReceiver(null, new Close()))->setIdentifier('showPersonPicture');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadPersonPictureContent($PersonId = null, $Group = null)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock(), self::getEndpoint());
        $ModalEmitter->setPostPayload(array(
            'PersonId'   => $PersonId,
            'Group'      => $Group
        ));
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadPersonPictureContent',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineEditPersonPicture($PersonId = null, $Group = null)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock(), self::getEndpoint());
        $ModalEmitter->setPostPayload(array(
            'PersonId'   => $PersonId,
            'Group'      => $Group
        ));
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editPersonPictureContent',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineRemovePersonPicture($PersonId = null, $Group = null)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock(), self::getEndpoint());
        $ModalEmitter->setPostPayload(array(
            'PersonId'   => $PersonId,
            'Group'      => $Group
        ));
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'removePersonPicture',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineShowPersonPicture($PersonId = null)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setPostPayload(array(
            'PersonId'   => $PersonId
        ));
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'showPersonPicture',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadPersonPictureContent($PersonId = null, $Group = null)
    {

        return (new FrontendPersonPicture())->getPersonPictureContent($PersonId, $Group);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editPersonPictureContent($PersonId = null, $Group = null)
    {
        return (new FrontendPersonPicture())->getEditPersonPictureContent($PersonId, $Group);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function removePersonPicture($PersonId = null, $Group = null)
    {
        $tblPersonPicture = false;
        if(($tblPerson = Person::useService()->getPersonById($PersonId))){
            $tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson);
        }
        if($tblPersonPicture){
            Storage::useService()->destroyPersonPicture($tblPersonPicture);
            return self::pipelineLoadPersonPictureContent();
        } else {
            return new Danger('Foto konnte nicht entfernt werden')
                .new RedirectScript('/People/Person', RedirectScript::TIMEOUT_ERROR, array('Id' => $PersonId, 'Group' => $Group));
        }
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function showPersonPicture($PersonId = null)
    {
        if(($tblPerson = Person::useService()->getPersonById($PersonId))){
            if(($tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson))){
                return new Center(
                    new Title($tblPerson->getFullName())
                    .$tblPersonPicture->getPicture('500px', '30px')
                );
            }
        }

        $PictureHeight = '150px';
        $File = FileSystem::getFileLoader('/Common/Style/Resource/SSWAbsence - Kopie.png');
        $Image = new Center('<img src="'.$File->getLocation().'" style="height: '.$PictureHeight.';">');

        return new Info('Kein Foto hinterlegt').$Image;
    }
}