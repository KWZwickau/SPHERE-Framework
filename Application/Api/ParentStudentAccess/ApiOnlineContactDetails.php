<?php

namespace SPHERE\Application\Api\ParentStudentAccess;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\ParentStudentAccess\ContactDetails\ContactDetails;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\System\Extension\Extension;

class ApiOnlineContactDetails extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);

//        $Dispatcher->registerMethod('openCreateContactDetailsModal');
//        $Dispatcher->registerMethod('saveCreateContactDetailsModal');

        $Dispatcher->registerMethod('loadContactDetailsContent');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal(): ModalReceiver
    {
        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReceiver');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock(string $Content = '', string $Identifier = ''): BlockReceiver
    {

        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose(): Pipeline
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @param null $PersonId
     * @param null $DivisionId
     *
     * @return Pipeline
     */
    public static function pipelineLoadContactDetailsContent($PersonId = null, $DivisionId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ContactDetailsContent_' . $PersonId), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadContactDetailsContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'DivisionId' => $DivisionId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $PersonId
     * @param null $DivisionId
     *
     * @return string
     */
    public function loadContactDetailsContent($PersonId = null, $DivisionId = null): string
    {
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblPerson = Person::useService()->getPersonById($PersonId);

        if (!($tblDivision && $tblPerson)) {
            return new Danger('Die Klasse oder Person wurde nicht gefunden', new Exclamation());
        }

        return ContactDetails::useFrontend()->loadContactDetailsContent($tblPerson, $tblDivision);
    }

}