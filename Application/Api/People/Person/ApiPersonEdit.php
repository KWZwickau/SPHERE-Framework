<?php

namespace SPHERE\Application\Api\People\Person;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Frontend\FrontendBasic;
use SPHERE\Application\People\Person\Frontend\FrontendClub;
use SPHERE\Application\People\Person\Frontend\FrontendCommon;
use SPHERE\Application\People\Person\Frontend\FrontendCustody;
use SPHERE\Application\People\Person\Frontend\FrontendProspect;
use SPHERE\Application\People\Person\Frontend\FrontendTeacher;
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

        $Dispatcher->registerMethod('editCommonContent');
        $Dispatcher->registerMethod('saveCommonContent');

        $Dispatcher->registerMethod('editProspectContent');
        $Dispatcher->registerMethod('saveProspectContent');

        $Dispatcher->registerMethod('editTeacherContent');
        $Dispatcher->registerMethod('saveTeacherContent');

        $Dispatcher->registerMethod('editCustodyContent');
        $Dispatcher->registerMethod('saveCustodyContent');

        $Dispatcher->registerMethod('editClubContent');
        $Dispatcher->registerMethod('saveClubContent');

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
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineEditCommonContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CommonContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editCommonContent',
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
    public static function pipelineSaveCommonContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'CommonContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveCommonContent',
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
    public static function pipelineCancelCommonContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'CommonContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadCommonContent',
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
    public static function pipelineEditProspectContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ProspectContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editProspectContent',
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
    public static function pipelineSaveProspectContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'ProspectContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveProspectContent',
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
    public static function pipelineCancelProspectContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'ProspectContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadProspectContent',
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
    public static function pipelineEditTeacherContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'TeacherContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editTeacherContent',
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
    public static function pipelineSaveTeacherContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'TeacherContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveTeacherContent',
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
    public static function pipelineCancelTeacherContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'TeacherContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadTeacherContent',
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
    public static function pipelineEditCustodyContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CustodyContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editCustodyContent',
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
    public static function pipelineSaveCustodyContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'CustodyContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveCustodyContent',
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
    public static function pipelineCancelCustodyContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'CustodyContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadCustodyContent',
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
    public static function pipelineEditClubContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ClubContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editClubContent',
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
    public static function pipelineSaveClubContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'ClubContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveClubContent',
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
    public static function pipelineCancelClubContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'ClubContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadClubContent',
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

        return (new FrontendBasic())->getEditBasicContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function saveBasicContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Person = $Global->POST['Person'];
        if (($form = (new FrontendBasic())->checkInputBasicContent($tblPerson, $Person))) {
            // display Errors on form
            return $form;
        }

        if (Person::useService()->updatePersonService($tblPerson, $Person)) {
            // todo Meta-Daten neu laden
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadBasicContent($PersonId)
                . ApiPersonReadOnly::pipelineLoadProspectTitle($PersonId)
                . ApiPersonReadOnly::pipelineLoadTeacherTitle($PersonId)
                . ApiPersonReadOnly::pipelineLoadCustodyTitle($PersonId)
                . ApiPersonReadOnly::pipelineLoadClubTitle($PersonId)
                ;
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editCommonContent($PersonId = null)
    {

        return (new FrontendCommon())->getEditCommonContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function saveCommonContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = $Global->POST['Meta'];

        if (Common::useService()->updateMetaService($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadCommonContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editProspectContent($PersonId = null)
    {

        return (new FrontendProspect())->getEditProspectContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function saveProspectContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = $Global->POST['Meta'];

        if (Prospect::useService()->updateMetaService($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadProspectContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editTeacherContent($PersonId = null)
    {

        return (new FrontendTeacher())->getEditTeacherContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function saveTeacherContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = $Global->POST['Meta'];
        if (($form = (new FrontendTeacher())->checkInputTeacherContent($tblPerson, $Meta))) {
            // display Errors on form
            return $form;
        }

        if (Teacher::useService()->updateMetaService($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadTeacherContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editCustodyContent($PersonId = null)
    {

        return (new FrontendCustody())->getEditCustodyContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function saveCustodyContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = $Global->POST['Meta'];

        if (Custody::useService()->updateMetaService($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadCustodyContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editClubContent($PersonId = null)
    {

        return (new FrontendClub())->getEditClubContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function saveClubContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = $Global->POST['Meta'];

        if (Club::useService()->updateMetaService($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadClubContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }
}