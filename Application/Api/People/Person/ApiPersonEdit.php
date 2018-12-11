<?php

namespace SPHERE\Application\Api\People\Person;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiPersonEdit
 *
 * @package SPHERE\Application\Api\People\Person
 */
class ApiPersonEdit extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('editBasicContent');
        $Dispatcher->registerMethod('saveBasicContent');

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
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineEditBasicContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'BasicContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editBasicContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineSaveBasicContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'BasicContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveBasicContent',
        ));
        $emitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineCancelBasicContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'BasicContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadBasicContent',
        ));
        $emitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editBasicContent($PersonId = null)
    {

        return Person::useFrontend()->getEditBasicContent($PersonId);
    }

    public function saveBasicContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Person = $Global->POST['Person'];
        if (($form = Person::useFrontend()->checkInputBasicContent($tblPerson, $Person))) {
            // display Errors on form
            return $form;
        }

        if (Person::useService()->updatePersonService($tblPerson, $Person)) {
            // todo Meta-Daten neu laden
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadBasicContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }
}