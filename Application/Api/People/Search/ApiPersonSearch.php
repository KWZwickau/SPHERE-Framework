<?php

namespace SPHERE\Application\Api\People\Search;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Search\Search;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\System\Extension\Extension;

class ApiPersonSearch  extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('searchPerson');
        $Dispatcher->registerMethod('selectGroup');
        $Dispatcher->registerMethod('loadDashboard');
        $Dispatcher->registerMethod('loadGroupSelectBox');
        $Dispatcher->registerMethod('loadSearchTextInput');

        $Dispatcher->registerMethod('openYearStudentCountModal');

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
     * @return ModalReceiver
     */
    public static function receiverModal(): ModalReceiver
    {
        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReceiver');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSearchPerson(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SearchContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'searchPerson',
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen.');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     *
     * @return string
     */
    public function searchPerson($Data = null): string
    {
        return Search::useFrontend()->loadPersonSearch(isset($Data['Search']) ? trim($Data['Search']) : '');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSelectGroup(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SearchContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'selectGroup',
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen.');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     *
     * @return string
     */
    public function selectGroup($Data = null): string
    {
        return self::pipelineLoadSearchTextInput('')
            . Search::useFrontend()->loadGroup($Data['Id'] ?? '');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadDashboard(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SearchContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDashboard',
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen.');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function loadDashboard(): string
    {
        return Search::useFrontend()->loadDashboard();
    }

    /**
     * @param $YearId
     *
     * @return Pipeline
     */
    public static function pipelineOpenYearStudentCountModal($YearId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openYearStudentCountModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'YearId' => $YearId,
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen ...');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $YearId
     *
     * @return string
     */
    public function openYearStudentCountModal($YearId)
    {
        if (!($tblYear = Term::useService()->getYearById($YearId))) {
            return new Danger('Das Schuljahr wurde nicht gefunden', new Exclamation());
        }

        return DivisionCourse::useService()->getCountStudentsDetailsByYear($tblYear);
    }

    /**
     * @param $SelectId
     *
     * @return Pipeline
     */
    public static function pipelineLoadGroupSelectBox($SelectId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'GroupSelectBox'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadGroupSelectBox',
        ));
        $ModalEmitter->setPostPayload(array(
            'SelectId' => $SelectId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SelectId
     *
     * @return string
     */
    public function loadGroupSelectBox($SelectId): string
    {
        return Search::useFrontend()->getPanelSelectGroupOrDivisionCourse($SelectId);
    }

    /**
     * @param $Search
     *
     * @return Pipeline
     */
    public static function pipelineLoadSearchTextInput($Search): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SearchTextInput'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadSearchTextInput',
        ));
        $ModalEmitter->setPostPayload(array(
            'Search' => $Search,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Search
     *
     * @return string
     */
    public function loadSearchTextInput($Search): string
    {
        return Search::useFrontend()->getPanelSearchPerson($Search);
    }
}