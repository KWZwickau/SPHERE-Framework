<?php

namespace SPHERE\Application\Api\People\Person;

use DateTime;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourseStudent;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Agreement\Agreement;
use SPHERE\Application\People\Meta\Child\Child;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Masern\Masern;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Frontend\FrontendBasic;
use SPHERE\Application\People\Person\Frontend\FrontendChild;
use SPHERE\Application\People\Person\Frontend\FrontendClub;
use SPHERE\Application\People\Person\Frontend\FrontendCommon;
use SPHERE\Application\People\Person\Frontend\FrontendCustody;
use SPHERE\Application\People\Person\Frontend\FrontendPersonAgreement;
use SPHERE\Application\People\Person\Frontend\FrontendPersonMasern;
use SPHERE\Application\People\Person\Frontend\FrontendProspect;
use SPHERE\Application\People\Person\Frontend\FrontendProspectTransfer;
use SPHERE\Application\People\Person\Frontend\FrontendStudentAgreement;
use SPHERE\Application\People\Person\Frontend\FrontendStudentBasic;
use SPHERE\Application\People\Person\Frontend\FrontendStudentGeneral;
use SPHERE\Application\People\Person\Frontend\FrontendStudentMedicalRecord;
use SPHERE\Application\People\Person\Frontend\FrontendStudentProcess;
use SPHERE\Application\People\Person\Frontend\FrontendStudentSpecialNeeds;
use SPHERE\Application\People\Person\Frontend\FrontendStudentSubject;
use SPHERE\Application\People\Person\Frontend\FrontendStudentTechnicalSchool;
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
        $Dispatcher->registerMethod('changeSelectedGender');
        $Dispatcher->registerMethod('loadSimilarPersonContent');
        $Dispatcher->registerMethod('loadSimilarPersonMessage');

        $Dispatcher->registerMethod('editBasicContent');
        $Dispatcher->registerMethod('saveBasicContent');

        $Dispatcher->registerMethod('editCommonContent');
        $Dispatcher->registerMethod('saveCommonContent');

        $Dispatcher->registerMethod('editPersonAgreementContent');
        $Dispatcher->registerMethod('savePersonAgreementContent');

        $Dispatcher->registerMethod('editPersonMasernContent');
        $Dispatcher->registerMethod('savePersonMasernContent');

        $Dispatcher->registerMethod('editProspectContent');
        $Dispatcher->registerMethod('saveProspectContent');

        $Dispatcher->registerMethod('editProspectTransferContent');
        $Dispatcher->registerMethod('saveProspectTransferContent');

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

        $Dispatcher->registerMethod('editStudentAgreementContent');
        $Dispatcher->registerMethod('saveStudentAgreementContent');

        $Dispatcher->registerMethod('editStudentSubjectContent');
        $Dispatcher->registerMethod('saveStudentSubjectContent');

        $Dispatcher->registerMethod('editStudentSpecialNeedsContent');
        $Dispatcher->registerMethod('saveStudentSpecialNeedsContent');

        $Dispatcher->registerMethod('editStudentTechnicalSchoolContent');
        $Dispatcher->registerMethod('saveStudentTechnicalSchoolContent');

        $Dispatcher->registerMethod('editChildContent');
        $Dispatcher->registerMethod('saveChildContent');

        $Dispatcher->registerMethod('editStudentProcessContent');
        $Dispatcher->registerMethod('saveEditStudentProcess');

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
     * @return Pipeline
     */
    public static function pipelineChangeSelectedGender()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SelectedGender'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'changeSelectedGender',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
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
     * @param $PersonId
     * @param $GroupId
     *
     * @return Pipeline
     */
    public static function pipelineEditBasicContent($PersonId, $GroupId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'BasicContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editBasicContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'GroupId' => $GroupId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $GroupId
     *
     * @return Pipeline
     */
    public static function pipelineSaveBasicContent($PersonId, $GroupId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'BasicContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveBasicContent',
        ));
        $emitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'GroupId' => $GroupId
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
    public static function pipelineEditPersonAgreementContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'PersonAgreementContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editPersonAgreementContent',
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
    public static function pipelineSavePersonAgreementContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'PersonAgreementContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'savePersonAgreementContent',
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
    public static function pipelineCancelPersonAgreementContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'PersonAgreementContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadPersonAgreementContent',
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
    public static function pipelineEditPersonMasernContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'PersonMasernContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editPersonMasernContent',
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
    public static function pipelineSavePersonMasernContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'PersonMasernContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'savePersonMasernContent',
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
    public static function pipelineCancelPersonMasernContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'PersonMasernContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadPersonMasernContent',
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
    public static function pipelineEditProspectTransferContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ProspectTransferContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editProspectTransferContent',
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
    public static function pipelineSaveProspectTransferContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'ProspectTransferContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveProspectTransferContent',
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
    public static function pipelineCancelProspectTransferContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'ProspectTransferContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadProspectTransferContent',
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
    public static function pipelineEditStudentAgreementContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentAgreementContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editStudentAgreementContent',
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
    public static function pipelineSaveStudentAgreementContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentAgreementContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveStudentAgreementContent',
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
    public static function pipelineCancelStudentAgreementContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'StudentAgreementContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadStudentAgreementContent',
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
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineEditStudentSpecialNeedsContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentSpecialNeedsContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editStudentSpecialNeedsContent',
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
    public static function pipelineSaveStudentSpecialNeedsContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentSpecialNeedsContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveStudentSpecialNeedsContent',
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
    public static function pipelineCancelStudentSpecialNeedsContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'StudentSpecialNeedsContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadStudentSpecialNeedsContent',
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
    public static function pipelineEditStudentTechnicalSchoolContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentTechnicalSchoolContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editStudentTechnicalSchoolContent',
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
    public static function pipelineSaveStudentTechnicalSchoolContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentTechnicalSchoolContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveStudentTechnicalSchoolContent',
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
    public static function pipelineCancelStudentTechnicalSchoolContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        // Grunddaten neu laden
        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'StudentTechnicalSchoolContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadStudentTechnicalSchoolContent',
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
    public static function pipelineEditChildContent($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ChildContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editChildContent',
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
    public static function pipelineSaveChildContent($PersonId)
    {

        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(self::receiverBlock('', 'ChildContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveChildContent',
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
    public static function pipelineCancelChildContent($PersonId)
    {
        $pipeline = new Pipeline(true);

        $emitter = new ServerEmitter(ApiPersonReadOnly::receiverBlock('', 'ChildContent'), ApiPersonReadOnly::getEndpoint());
        $emitter->setGetPayload(array(
            ApiPersonReadOnly::API_TARGET => 'loadChildContent',
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
     * @return \SPHERE\Common\Frontend\Form\Repository\Field\SelectBox
     */
    public function changeSelectedGender()
    {

        $Global = $this->getGlobal();
        $Person = $Global->POST['Person'];
        $Meta = $Global->POST['Meta'];

        $genderId = 0;
        if (isset($Person['Salutation'])
            && isset($Meta['BirthDates']['Gender'])
        ) {
            if (($tblSalutation = Person::useService()->getSalutationById($Person['Salutation']))) {
                if ($tblSalutation->getSalutation() == 'Frau' || $tblSalutation->getSalutation() == 'Schülerin') {
                    $genderId = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                } elseif ($tblSalutation->getSalutation() == 'Herr' || $tblSalutation->getSalutation() == 'Schüler') {
                    $genderId = TblCommonBirthDates::VALUE_GENDER_MALE;
                }
            }
        }

        return (new FrontendCommon())->getGenderSelectBox($genderId);
    }

    /**
     * @return string
     */
    public function loadSimilarPersonContent()
    {

        $Global = $this->getGlobal();
        $Person = $Global->POST['Person'];

        return (new FrontendBasic())->loadSimilarPersonContent($Person);
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

        return FrontendBasic::getSimilarPersonMessage($countSimilarPerson, $name, $hash);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editBasicContent($PersonId = null, $GroupId = null)
    {

        return (new FrontendBasic())->getEditBasicContent($PersonId, $GroupId);
    }

    /**
     * @param $PersonId
     * @param $GroupId
     *
     * @return bool|Danger|string
     */
    public function saveBasicContent($PersonId, $GroupId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Person = $Global->POST['Person'];
        if (($form = (new FrontendBasic())->checkInputBasicContent($tblPerson, $Person, $GroupId))) {
            // display Errors on form
            return $form;
        }

        if (Person::useService()->updatePersonService($tblPerson, $Person)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
//                . ApiPersonReadOnly::pipelineLoadBasicContent($PersonId)
//                . ApiPersonReadOnly::pipelineLoadCommonContent($PersonId)
//                . ApiPersonReadOnly::pipelineLoadChildContent($PersonId)
//                . ApiPersonReadOnly::pipelineLoadProspectContent($PersonId)
//                . ApiPersonReadOnly::pipelineLoadPersonAgreementContent($PersonId)
//                . ApiPersonReadOnly::pipelineLoadTeacherContent($PersonId)
//                . ApiPersonReadOnly::pipelineLoadCustodyContent($PersonId)
//                . ApiPersonReadOnly::pipelineLoadClubContent($PersonId)
//                . ApiPersonReadOnly::pipelineLoadIntegrationTitle($PersonId)
//                . ApiPersonReadOnly::pipelineLoadStudentTitle($PersonId)
//                . ApiAddressToPerson::pipelineLoadAddressToPersonContent($PersonId)
//                . ApiPhoneToPerson::pipelineLoadPhoneToPersonContent($PersonId)
//                . ApiMailToPerson::pipelineLoadMailToPersonContent($PersonId)
//                . ApiRelationshipToPerson::pipelineLoadRelationshipToPersonContent($PersonId);
                . new Redirect('/People/Person', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblPerson->getId(), 'Group' => $GroupId)
                );
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
        $Meta = array();
        if(isset($Global->POST['Meta'])){
            $Meta = $Global->POST['Meta'];
        }

        if (($form = (new FrontendCommon())->checkInputCommonContent($tblPerson, $Meta))) {
            // display Errors on form
            return $form;
        }

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
    public function editPersonAgreementContent($PersonId = null)
    {

        return (new FrontendPersonAgreement())->getEditPersonAgreementContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function savePersonAgreementContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = array();
        if(isset($Global->POST['Meta'])){
            $Meta = $Global->POST['Meta'];
        }

        if (Agreement::useService()->updatePersonAgreement($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadPersonAgreementContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }



    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editPersonMasernContent($PersonId = null)
    {

        return (new FrontendPersonMasern())->getEditPersonMasernContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function savePersonMasernContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = array();
        if(isset($Global->POST['Meta'])){
            $Meta = $Global->POST['Meta'];
        }

        $MasernDate = $MasernDocumentType = $MasernCreatorType = null;
        if(isset($Meta['Masern']['Date']) && $Meta['Masern']['Date']){
            $MasernDate = new DateTime($Meta['Masern']['Date']);
        }
        if(isset($Meta['Masern']['DocumentType']) && $Meta['Masern']['DocumentType']){
            $MasernDocumentType = Student::useService()->getStudentMasernInfoById($Meta['Masern']['DocumentType']);
        }
        if(isset($Meta['Masern']['CreatorType']) && $Meta['Masern']['CreatorType']){
            $MasernCreatorType = Student::useService()->getStudentMasernInfoById($Meta['Masern']['CreatorType']);
        }
        if(($tblPersonMasern = Masern::useService()->getPersonMasernByPerson($tblPerson))){
            if (Masern::useService()->updatePersonMasern($tblPersonMasern, $tblPerson, $MasernDate, $MasernDocumentType, $MasernCreatorType)) {
                return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . ApiPersonReadOnly::pipelineLoadPersonMasernContent($PersonId);
            }
        } else {
            if(Masern::useService()->createPersonMasern($tblPerson, $MasernDate, $MasernDocumentType, $MasernCreatorType)) {
                return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . ApiPersonReadOnly::pipelineLoadPersonMasernContent($PersonId);
            }
        }
        return new Danger('Die Daten konnten nicht gespeichert werden');
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
     * @param null $PersonId
     *
     * @return string
     */
    public function editProspectTransferContent($PersonId = null)
    {

        return (new FrontendProspectTransfer())->getEditProspectTransferContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function saveProspectTransferContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = $Global->POST['Meta'];

        if (Student::useService()->updateStudentTransferArrive($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . ApiPersonReadOnly::pipelineLoadProspectTransferContent($PersonId)
            . ApiPersonReadOnly::pipelineLoadStudentTransferContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
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

        if (($form = (new FrontendClub())->checkInputCreatePersonContent($Meta, $tblPerson))) {
            // display Errors on form
            return $form;
        }

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

        return (new FrontendStudentBasic())->getEditStudentBasicContent($PersonId);
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
                . ApiPersonReadOnly::pipelineLoadStudentTransferContent($PersonId)
                . ApiPersonReadOnly::pipelineLoadProspectTransferContent($PersonId);
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
    public function editStudentAgreementContent($PersonId = null)
    {

        return (new FrontendStudentAgreement())->getEditStudentAgreementContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function saveStudentAgreementContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = array();
        if(isset($Global->POST['Meta'])){
            $Meta = $Global->POST['Meta'];
        }

        if (Student::useService()->updateStudentAgreement($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadStudentAgreementContent($PersonId);
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
        $Meta = array();
        if(isset($Global->POST['Meta'])){
            $Meta = $Global->POST['Meta'];
        }

        if (Student::useService()->updateStudentSubject($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadStudentSubjectContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editStudentSpecialNeedsContent($PersonId = null)
    {

        return (new FrontendStudentSpecialNeeds())->getEditStudentSpecialNeedsContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function saveStudentSpecialNeedsContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = $Global->POST['Meta'];

        if (Student::useService()->updateStudentSpecialNeeds($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadStudentSpecialNeedsContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editStudentTechnicalSchoolContent($PersonId = null)
    {
        return (new FrontendStudentTechnicalSchool())->getEditStudentTechnicalSchoolContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function saveStudentTechnicalSchoolContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = $Global->POST['Meta'];

        if (Student::useService()->updateStudentTechnicalSchool($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadStudentTechnicalSchoolContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function editChildContent($PersonId = null)
    {

        return (new FrontendChild())->getEditChildContent($PersonId);
    }

    /**
     * @param $PersonId
     *
     * @return bool|Danger|string
     */
    public function saveChildContent($PersonId)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Person nicht gefunden', new Exclamation());
        }

        $Global = $this->getGlobal();
        $Meta = $Global->POST['Meta'];
        if (($form = (new FrontendChild())->checkInputChildContent($tblPerson, $Meta))) {
            // display Errors on form
            return $form;
        }

        if (Child::useService()->updateMetaService($tblPerson, $Meta)) {
            return new Success('Die Daten wurden erfolgreich gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . ApiPersonReadOnly::pipelineLoadChildContent($PersonId);
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden');
        }
    }

    /**
     * @param $PersonId
     * @param $StudentEducationId
     *
     * @return Pipeline
     */
    public static function pipelineEditStudentProcessContent($PersonId, $StudentEducationId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentProcessContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'editStudentProcessContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'StudentEducationId' => $StudentEducationId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     * @param $StudentEducationId
     *
     * @return string
     */
    public function editStudentProcessContent($PersonId, $StudentEducationId): string
    {
        return (new ApiDivisionCourseStudent())->editDivisionCourseStudentContent($StudentEducationId, $PersonId, null);
    }

    /**
     * @param $PersonId
     * @param $StudentEducationId
     *
     * @return Pipeline
     */
    public static function pipelineEditStudentProcessSave($PersonId, $StudentEducationId): Pipeline
    {
        $pipeline = new Pipeline();
        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentProcessContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'saveEditStudentProcess'
        ));
        $emitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'StudentEducationId' => $StudentEducationId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param $PersonId
     * @param $StudentEducationId
     * @param $StudentEducationData
     *
     * @return Danger|string
     */
    public function saveEditStudentProcess($PersonId, $StudentEducationId, $StudentEducationData)
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden', new Exclamation());
        }

        $tblStudentEducation = DivisionCourse::useService()->getStudentEducationById($StudentEducationId);

        if (($form = DivisionCourse::useService()->checkFormEditStudentEducation($StudentEducationData, $tblPerson, null, $tblStudentEducation ?: null))) {
            // display Errors on form
//            return $this->getEditStudentEducationModal($form, $tblPerson, $tblDivisionCourse ?: null, $tblStudentEducation ?: null);
            return FrontendStudentProcess::getEditStudentProcessTitle($tblPerson) . new Well($form);
        }

        if ($tblStudentEducation && DivisionCourse::useService()->updateStudentEducation($tblStudentEducation, $StudentEducationData)) {
            return new Success('Die Schüler-Bildung wurde erfolgreich gespeichert.')
                . ApiPersonReadOnly::pipelineLoadStudentProcessContent($PersonId);
        } else {
            return new Danger('Die Schüler-Bildung konnte nicht gespeichert werden.');
        }
    }
}