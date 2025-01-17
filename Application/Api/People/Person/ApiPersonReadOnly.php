<?php

namespace SPHERE\Application\Api\People\Person;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Frontend\FrontendBasic;
use SPHERE\Application\People\Person\Frontend\FrontendChild;
use SPHERE\Application\People\Person\Frontend\FrontendClub;
use SPHERE\Application\People\Person\Frontend\FrontendCommon;
use SPHERE\Application\People\Person\Frontend\FrontendCustody;
use SPHERE\Application\People\Person\Frontend\FrontendPersonAgreement;
use SPHERE\Application\People\Person\Frontend\FrontendPersonMasern;
use SPHERE\Application\People\Person\Frontend\FrontendProspect;
use SPHERE\Application\People\Person\Frontend\FrontendProspectGroup;
use SPHERE\Application\People\Person\Frontend\FrontendProspectTransfer;
use SPHERE\Application\People\Person\Frontend\FrontendStaffGroup;
use SPHERE\Application\People\Person\Frontend\FrontendStudentAgreement;
use SPHERE\Application\People\Person\Frontend\FrontendStudentBasic;
use SPHERE\Application\People\Person\Frontend\FrontendStudentGeneral;
use SPHERE\Application\People\Person\Frontend\FrontendStudentGroup;
use SPHERE\Application\People\Person\Frontend\FrontendStudentIntegration;
use SPHERE\Application\People\Person\Frontend\FrontendStudentMedicalRecord;
use SPHERE\Application\People\Person\Frontend\FrontendStudentProcess;
use SPHERE\Application\People\Person\Frontend\FrontendStudentSpecialNeeds;
use SPHERE\Application\People\Person\Frontend\FrontendStudentSubject;
use SPHERE\Application\People\Person\Frontend\FrontendStudentTechnicalSchool;
use SPHERE\Application\People\Person\Frontend\FrontendStudentTransfer;
use SPHERE\Application\People\Person\Frontend\FrontendTeacher;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiPersonReadOnly
 *
 * @package SPHERE\Application\Api\People\Person
 */
class ApiPersonReadOnly extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadBasicContent');
        $Dispatcher->registerMethod('loadCommonContent');
        //Staff
        $Dispatcher->registerMethod('loadStaffGroupTitle');
        $Dispatcher->registerMethod('loadStaffGroupContent');
        $Dispatcher->registerMethod('loadTeacherContent');
        $Dispatcher->registerMethod('loadPersonMasernContent');
        $Dispatcher->registerMethod('loadPersonAgreementContent');
        //Prospect
        $Dispatcher->registerMethod('loadProspectGroupTitle');
        $Dispatcher->registerMethod('loadProspectGroupContent');
        $Dispatcher->registerMethod('loadProspectContent');
        $Dispatcher->registerMethod('loadProspectTransferContent');

        $Dispatcher->registerMethod('loadCustodyContent');
        $Dispatcher->registerMethod('loadClubContent');
        $Dispatcher->registerMethod('loadChildContent');

        $Dispatcher->registerMethod('loadIntegrationTitle');
        $Dispatcher->registerMethod('loadIntegrationContent');

        $Dispatcher->registerMethod('loadStudentGroupTitle');
        $Dispatcher->registerMethod('loadStudentGroupContent');
        $Dispatcher->registerMethod('loadStudentBasicContent');
        $Dispatcher->registerMethod('loadStudentTransferContent');
        $Dispatcher->registerMethod('loadStudentProcessContent');
        $Dispatcher->registerMethod('loadStudentMedicalRecordContent');
        $Dispatcher->registerMethod('loadStudentGeneralContent');
        $Dispatcher->registerMethod('loadStudentAgreementContent');
        $Dispatcher->registerMethod('loadStudentSubjectContent');
        $Dispatcher->registerMethod('loadStudentSpecialNeedsContent');
        $Dispatcher->registerMethod('loadStudentTechnicalSchoolContent');

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
     * @param $PersonId
     * @param $GroupId
     *
     * @return Pipeline
     */
    public static function pipelineLoadBasicContent($PersonId, $GroupId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'BasicContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadBasicContent',
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
    public static function pipelineLoadCommonContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'CommonContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadCommonContent',
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
    public static function pipelineLoadProspectGroupTitle($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'ProspectGroupContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadProspectGroupTitle',
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
    public static function pipelineLoadProspectGroupContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'ProspectGroupContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadProspectGroupContent',
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
    public static function pipelineLoadProspectContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'ProspectContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadProspectContent',
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
    public static function pipelineLoadProspectTransferContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'ProspectTransferContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadProspectTransferContent',
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
    public static function pipelineLoadStaffGroupTitle($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StaffGroup'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadStaffGroupTitle',
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
    public static function pipelineLoadStaffGroupContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StaffGroup'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadStaffGroupContent',
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
    public static function pipelineLoadTeacherContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'TeacherContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadTeacherContent',
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
    public static function pipelineLoadPersonMasernContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'PersonMasernContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadPersonMasernContent',
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
    public static function pipelineLoadPersonAgreementContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'PersonAgreementContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadPersonAgreementContent',
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
    public static function pipelineLoadCustodyContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'CustodyContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadCustodyContent',
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
    public static function pipelineLoadClubContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'ClubContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadClubContent',
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
    public static function pipelineLoadIntegrationTitle($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'IntegrationContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadIntegrationTitle',
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
    public static function pipelineLoadIntegrationContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'IntegrationContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadIntegrationContent',
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
    public static function pipelineLoadStudentGroupTitle($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentGroupContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentGroupTitle',
        ));
        $emitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param int $PersonId
     * @param int $AllowEdit
     *
     * @return Pipeline
     */
    public static function pipelineLoadStudentGroupContent($PersonId, $AllowEdit = 1)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentGroupContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentGroupContent',
        ));
        $emitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'AllowEdit' => $AllowEdit,
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineLoadStudentBasicContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentBasicContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentBasicContent',
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
    public static function pipelineLoadStudentTransferContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentTransferContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentTransferContent',
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
    public static function pipelineLoadStudentProcessContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentProcessContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentProcessContent',
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
    public static function pipelineLoadStudentMedicalRecordContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentMedicalRecordContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentMedicalRecordContent',
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
    public static function pipelineLoadStudentGeneralContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentGeneralContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentGeneralContent',
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
    public static function pipelineLoadStudentAgreementContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentAgreementContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentAgreementContent',
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
    public static function pipelineLoadStudentSubjectContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentSubjectContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentSubjectContent',
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
    public static function pipelineLoadStudentSpecialNeedsContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentSpecialNeedsContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentSpecialNeedsContent',
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
    public static function pipelineLoadStudentTechnicalSchoolContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentTechnicalSchoolContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentTechnicalSchoolContent',
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
    public static function pipelineLoadChildContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'ChildContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadChildContent',
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
    public function loadBasicContent($PersonId = null, $GroupId = null)
    {

        return FrontendBasic::getBasicContent($PersonId, $GroupId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadCommonContent($PersonId = null)
    {

        return FrontendCommon::getCommonContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadPersonAgreementContent($PersonId = null)
    {

        return FrontendPersonAgreement::getPersonAgreementContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadProspectGroupTitle($PersonId = null)
    {

        return FrontendProspectGroup::getProspectGroupTitle($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadProspectGroupContent($PersonId = null)
    {

        return FrontendProspectGroup::getProspectGroupContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadProspectContent($PersonId = null)
    {

        return FrontendProspect::getProspectContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadProspectTransferContent($PersonId = null)
    {

        return FrontendProspectTransfer::getProspectTransferContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadStaffGroupTitle($PersonId = null)
    {

        return FrontendStaffGroup::getStaffGroupTitle($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadStaffGroupContent($PersonId = null)
    {

        return FrontendStaffGroup::getStaffGroupContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadTeacherContent($PersonId = null)
    {

        return FrontendTeacher::getTeacherContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadCustodyContent($PersonId = null)
    {

        return FrontendCustody::getCustodyContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadClubContent($PersonId = null)
    {

        return FrontendClub::getClubContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadIntegrationTitle($PersonId = null)
    {

        return FrontendStudentIntegration::getIntegrationTitle($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadIntegrationContent($PersonId = null)
    {

        return FrontendStudentIntegration::getIntegrationContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadStudentGroupTitle($PersonId = null)
    {

        return FrontendStudentGroup::getStudentGroupTitle($PersonId);
    }

    /**
     * @param null $PersonId
     * @param int  $AllowEdit
     *
     * @return string
     */
    public function loadStudentGroupContent($PersonId = null, $AllowEdit = 1)
    {

        return FrontendStudentGroup::getStudentGroupContent($PersonId, $AllowEdit);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadStudentBasicContent($PersonId = null)
    {

        return FrontendStudentBasic::getStudentBasicContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadStudentTransferContent($PersonId = null)
    {

        return FrontendStudentTransfer::getStudentTransferContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadStudentProcessContent($PersonId = null)
    {

        return FrontendStudentProcess::getStudentProcessContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadStudentMedicalRecordContent($PersonId = null)
    {

        return FrontendStudentMedicalRecord::getStudentMedicalRecordContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadStudentGeneralContent($PersonId = null)
    {

        return FrontendStudentGeneral::getStudentGeneralContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadStudentAgreementContent($PersonId = null)
    {

        return FrontendStudentAgreement::getStudentAgreementContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadPersonMasernContent($PersonId = null)
    {

        return FrontendPersonMasern::getPersonMasernContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadStudentSubjectContent($PersonId = null)
    {

        return FrontendStudentSubject::getStudentSubjectContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadStudentSpecialNeedsContent($PersonId = null)
    {
        return FrontendStudentSpecialNeeds::getStudentSpecialNeedsContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadStudentTechnicalSchoolContent($PersonId = null)
    {
        return FrontendStudentTechnicalSchool::getStudentTechnicalSchoolContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadChildContent($PersonId = null)
    {
        return FrontendChild::getChildContent($PersonId);
    }
}