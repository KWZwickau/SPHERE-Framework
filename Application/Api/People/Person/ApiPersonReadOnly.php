<?php

namespace SPHERE\Application\Api\People\Person;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Frontend\FrontendBasic;
use SPHERE\Application\People\Person\Frontend\FrontendClub;
use SPHERE\Application\People\Person\Frontend\FrontendCommon;
use SPHERE\Application\People\Person\Frontend\FrontendCustody;
use SPHERE\Application\People\Person\Frontend\FrontendProspect;
use SPHERE\Application\People\Person\Frontend\FrontendStudent;
use SPHERE\Application\People\Person\Frontend\FrontendStudentGeneral;
use SPHERE\Application\People\Person\Frontend\FrontendStudentIntegration;
use SPHERE\Application\People\Person\Frontend\FrontendStudentMedicalRecord;
use SPHERE\Application\People\Person\Frontend\FrontendStudentSubject;
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
        $Dispatcher->registerMethod('loadProspectContent');
        $Dispatcher->registerMethod('loadTeacherContent');
        $Dispatcher->registerMethod('loadCustodyContent');
        $Dispatcher->registerMethod('loadClubContent');

        $Dispatcher->registerMethod('loadIntegrationTitle');
        $Dispatcher->registerMethod('loadIntegrationContent');

        $Dispatcher->registerMethod('loadStudentTitle');
        $Dispatcher->registerMethod('loadStudentContent');
        $Dispatcher->registerMethod('loadStudentBasicContent');
        $Dispatcher->registerMethod('loadStudentTransferContent');
        $Dispatcher->registerMethod('loadStudentMedicalRecordContent');
        $Dispatcher->registerMethod('loadStudentGeneralContent');
        $Dispatcher->registerMethod('loadStudentSubjectContent');

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
    public static function pipelineLoadBasicContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'BasicContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadBasicContent',
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
    public static function pipelineLoadStudentTitle($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentTitle',
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
    public static function pipelineLoadStudentContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'StudentContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentContent',
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
     * @param null $PersonId
     *
     * @return string
     */
    public function loadBasicContent($PersonId = null)
    {

        return FrontendBasic::getBasicContent($PersonId);
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
    public function loadProspectContent($PersonId = null)
    {

        return FrontendProspect::getProspectContent($PersonId);
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
    public function loadStudentTitle($PersonId = null)
    {

        return FrontendStudent::getStudentTitle($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadStudentContent($PersonId = null)
    {

        return FrontendStudent::getStudentContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadStudentBasicContent($PersonId = null)
    {

        return FrontendStudent::getStudentBasicContent($PersonId);
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
    public function loadStudentSubjectContent($PersonId = null)
    {

        return FrontendStudentSubject::getStudentSubjectContent($PersonId);
    }
}