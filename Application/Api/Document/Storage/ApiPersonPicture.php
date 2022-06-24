<?php
namespace SPHERE\Application\Api\Document\Storage;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Frontend\FrontendPersonPicture;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
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
     * @param null $PersonId
     *
     * @return string
     */
    public function loadPersonPictureContent($PersonId = null, $Group = null)
    {

        return // 'Test'.new RedirectScript('/People/Person', RedirectScript::TIMEOUT_SUCCESS, array('Id' => $PersonId, 'Group' => $Group));
        // ToDO Picture API ist beim erneuten holen nicht mehr aufrufbar...
        (new FrontendPersonPicture())->getPersonPictureContent($PersonId, $Group);
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
}