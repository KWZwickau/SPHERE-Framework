<?php

namespace SPHERE\Application\Api\People\Person;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Frontend\FrontendBasic;
use SPHERE\Application\People\Person\Frontend\FrontendClub;
use SPHERE\Application\People\Person\Frontend\FrontendCommon;
use SPHERE\Application\People\Person\Frontend\FrontendCustody;
use SPHERE\Application\People\Person\Frontend\FrontendProspect;
use SPHERE\Application\People\Person\Frontend\FrontendStudent;
use SPHERE\Application\People\Person\Frontend\FrontendStudentGeneral;
use SPHERE\Application\People\Person\Frontend\FrontendStudentMedicalRecord;
use SPHERE\Application\People\Person\Frontend\FrontendStudentSubject;
use SPHERE\Application\People\Person\Frontend\FrontendStudentTransfer;
use SPHERE\Application\People\Person\Frontend\FrontendTeacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
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

        $Dispatcher->registerMethod('saveCreatePersonContent');

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

        $Dispatcher->registerMethod('editStudentBasicContent');
        $Dispatcher->registerMethod('saveStudentBasicContent');

        $Dispatcher->registerMethod('editStudentTransferContent');
        $Dispatcher->registerMethod('saveStudentTransferContent');

        $Dispatcher->registerMethod('editStudentMedicalRecordContent');
        $Dispatcher->registerMethod('saveStudentMedicalRecordContent');

        $Dispatcher->registerMethod('editStudentGeneralContent');
        $Dispatcher->registerMethod('saveStudentGeneralContent');

        $Dispatcher->registerMethod('editStudentSubjectContent');
        $Dispatcher->registerMethod('saveStudentSubjectContent');

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
    public static function pipelineSaveCreatePersonContent()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'PersonContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreatePersonContent',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
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
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineEditStudentBasicContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentBasicContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editStudentBasicContent',
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
    public static function pipelineSaveStudentBasicContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentBasicContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveStudentBasicContent',
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
    public static function pipelineCancelStudentBasicContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'StudentBasicContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadStudentBasicContent',
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
    public static function pipelineEditStudentTransferContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentTransferContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editStudentTransferContent',
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
    public static function pipelineSaveStudentTransferContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentTransferContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveStudentTransferContent',
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
    public static function pipelineCancelStudentTransferContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'StudentTransferContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadStudentTransferContent',
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
    public static function pipelineEditStudentMedicalRecordContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentMedicalRecordContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editStudentMedicalRecordContent',
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
    public static function pipelineSaveStudentMedicalRecordContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentMedicalRecordContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveStudentMedicalRecordContent',
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
    public static function pipelineCancelStudentMedicalRecordContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'StudentMedicalRecordContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadStudentMedicalRecordContent',
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
    public static function pipelineEditStudentGeneralContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentGeneralContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editStudentGeneralContent',
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
    public static function pipelineSaveStudentGeneralContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentGeneralContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveStudentGeneralContent',
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
    public static function pipelineCancelStudentGeneralContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'StudentGeneralContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadStudentGeneralContent',
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
    public static function pipelineEditStudentSubjectContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentSubjectContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editStudentSubjectContent',
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
    public static function pipelineSaveStudentSubjectContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentSubjectContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveStudentSubjectContent',
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
    public static function pipelineCancelStudentSubjectContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'StudentSubjectContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadStudentSubjectContent',
        ));
        $emitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @return bool|Well|string
     */
    public function saveCreatePersonContent()
    {

        $Global = $this->getGlobal();
        $Person = $Global->POST['Person'];
        if (($form = (new FrontendBasic())->checkInputCreatePersonContent($Person))) {
            // display Errors on form
            return $form;
        }

        if (($tblPerson = Person::useService()->createPersonService($Person))) {
            if (isset($Global->POST['Meta'])) {
                $Meta = $Global->POST['Meta'];
                Common::useService()->updateMetaService($tblPerson, $Meta);
            }

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Person wurde erfolgreich erstellt')
                . new Redirect('/People/Person', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblPerson->getId())
                );
        } else {
            return new Danger(new Ban() . ' Die Person konnte nicht erstellt werden')
                . new Redirect('/People/Person', Redirect::TIMEOUT_ERROR);
        }
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
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadBasicContent($PersonId)
                . ApiPersonReadOnly::pipelineLoadProspectContent($PersonId)
                . ApiPersonReadOnly::pipelineLoadTeacherContent($PersonId)
                . ApiPersonReadOnly::pipelineLoadCustodyContent($PersonId)
                . ApiPersonReadOnly::pipelineLoadClubContent($PersonId)
                . ApiPersonReadOnly::pipelineLoadIntegrationTitle($PersonId)
                . ApiPersonReadOnly::pipelineLoadStudentTitle($PersonId);
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

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editStudentBasicContent($PersonId = null)
    {

        return (new FrontendStudent())->getEditStudentBasicContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function saveStudentBasicContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = $Global->POST['Meta'];

        if (Student::useService()->updateStudentBasic($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadStudentBasicContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editStudentTransferContent($PersonId = null)
    {

        return (new FrontendStudentTransfer())->getEditStudentTransferContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function saveStudentTransferContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = $Global->POST['Meta'];

        if (Student::useService()->updateStudentTransfer($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadStudentTransferContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editStudentMedicalRecordContent($PersonId = null)
    {

        return (new FrontendStudentMedicalRecord())->getEditStudentMedicalRecordContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function saveStudentMedicalRecordContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = $Global->POST['Meta'];

        if (Student::useService()->updateStudentMedicalRecord($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadStudentMedicalRecordContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editStudentGeneralContent($PersonId = null)
    {

        return (new FrontendStudentGeneral())->getEditStudentGeneralContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function saveStudentGeneralContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = $Global->POST['Meta'];

        if (Student::useService()->updateStudentGeneral($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadStudentGeneralContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editStudentSubjectContent($PersonId = null)
    {

        return (new FrontendStudentSubject())->getEditStudentSubjectContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function saveStudentSubjectContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = $Global->POST['Meta'];

        if (Student::useService()->updateStudentSubject($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadStudentSubjectContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }
}