<?php

namespace SPHERE\Application\Api\Education\Graduation\Grade;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Grade\Frontend;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\System\Extension\Extension;

class ApiGradeBook extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('changeYear');
        $Dispatcher->registerMethod('changeRole');
        $Dispatcher->registerMethod('loadHeader');

        $Dispatcher->registerMethod('loadViewGradeBookSelect');
        $Dispatcher->registerMethod('loadViewGradeBookContent');

        return $Dispatcher->callMethod($Method);
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
    public static function pipelineChangeYear(): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ChangeYear'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'changeYear',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     *
     * @return string
     */
    public function changeYear($Data = null): string
    {
        if (isset($Data["Year"]) && ($tblYear = Term::useService()->getYearById($Data["Year"]))) {
            $gradeBookSelectedYearId = Consumer::useService()->getAccountSettingValue("GradeBookSelectedYearId");
            if (!$gradeBookSelectedYearId || $gradeBookSelectedYearId != $tblYear->getId()) {
                Consumer::useService()->createAccountSetting("GradeBookSelectedYearId", $tblYear->getId());

                return ""
                    . self::pipelineLoadHeader(Frontend::VIEW_GRADE_BOOK_SELECT)
                    . self::pipelineLoadViewGradeBookSelect();
            }
        }

        return "";
    }

    /**
     * @return Pipeline
     */
    public static function pipelineChangeRole(): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ChangeRole'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'changeRole',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     *
     * @return string
     */
    public function changeRole($Data = null): string
    {
        if (isset($Data["IsHeadmaster"])) {
            $role = "Headmaster";
        } elseif (isset($Data["IsAllReadonly"])) {
            $role = "AllReadonly";
        } else {
            $role = "Teacher";
        }

        $gradeBookRole = Consumer::useService()->getAccountSettingValue("GradeBookRole");
        if (!$gradeBookRole || $gradeBookRole != $role) {
            Consumer::useService()->createAccountSetting("GradeBookRole", $role);

            return ""
                . self::pipelineLoadHeader(Frontend::VIEW_GRADE_BOOK_SELECT)
                . self::pipelineLoadViewGradeBookSelect();
        }

        return "";
    }

    /**
     * @param $View
     *
     * @return Pipeline
     */
    public static function pipelineLoadHeader($View): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Header'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadHeader',
        ));
        $ModalEmitter->setPostPayload(array(
            'View' => $View
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $View
     *
     * @return string
     */
    public function loadHeader($View): string
    {
        return Grade::useFrontend()->getHeader($View);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadViewGradeBookSelect(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewGradeBookSelect',
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function loadViewGradeBookSelect(): string
    {
        return Grade::useFrontend()->loadViewGradeBookSelect();
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewGradeBookContent($DivisionCourseId, $SubjectId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewGradeBookContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'SubjectId' => $SubjectId,
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     *
     * @return string
     */
    public function loadViewGradeBookContent($DivisionCourseId, $SubjectId, $Filter): string
    {
        return Grade::useFrontend()->loadViewGradeBookContent($DivisionCourseId, $SubjectId, $Filter);
    }
}