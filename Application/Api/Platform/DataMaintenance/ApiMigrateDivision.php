<?php

namespace SPHERE\Application\Api\Platform\DataMaintenance;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\History;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

class ApiMigrateDivision  extends Extension implements IApiInterface
{
    use ApiTrait;

    const STATUS_BUTTON = 'Button';
    const STATUS_WAITING = 'Waiting';
    const STATUS_FINISH = 'Finish';

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('status');
        $Dispatcher->registerMethod('migrateDivisions');
        $Dispatcher->registerMethod('migrateGroups');
        $Dispatcher->registerMethod('migrateYear');

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
     * @param $Status
     * @return Pipeline
     */
    public static function pipelineStatus($Status): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Status'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'status',
        ));
        $ModalEmitter->setPostPayload(array(
            'Status' => $Status
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Status
     * @return string
     */
    public function status($Status): string
    {
        switch ($Status) {
            case self::STATUS_BUTTON:
                return (new \SPHERE\Common\Frontend\Link\Repository\Danger('Daten migrieren', self::getEndpoint()))
                    ->ajaxPipelineOnClick(self::pipelineStatus(self::STATUS_WAITING));
            case self::STATUS_WAITING:
                return (new Warning('Bitte warten. Die Daten werden migriert.', new History()))
                    . self::pipelineMigrateDivisions();
            case self::STATUS_FINISH:
                return (new Success('Die Daten wurden erfolgreich migriert', new Select()));
        }

        return '';
    }

    /**
     * @return Pipeline
     */
    public static function pipelineMigrateDivisions(): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'MigrateDivisions'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'migrateDivisions',
        ));
        $ModalEmitter->setLoadingMessage('Klassen werden migriert.');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function migrateDivisions(): string
    {
        return new Success(DivisionCourse::useService()->migrateTblDivisionToTblDivisionCourse() . ' Klassen erfolgreich migriert.')
            . self::pipelineMigrateGroups();
    }

    /**
     * @return Pipeline
     */
    public static function pipelineMigrateGroups(): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'MigrateGroups'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'migrateGroups',
        ));
        $ModalEmitter->setLoadingMessage('Stammgruppen werden migriert.');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function migrateGroups(): string
    {
        return new Success(DivisionCourse::useService()->migrateTblGroupToTblDivisionCourse() . ' Stammgruppen erfolgreich migriert.')
            . (($tblNextYear = $this->getNextYear()) ? self::pipelineMigrateYear($tblNextYear->getId()) : '');
    }

    /**
     * @param $YearId
     *
     * @return Pipeline
     */
    public static function pipelineMigrateYear($YearId): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'MigrateYear_' . $YearId), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'migrateYear',
        ));
        $ModalEmitter->setPostPayload(array(
            'YearId' => $YearId
        ));
        $ModalEmitter->setLoadingMessage(($tblYear = Term::useService()->getYearById($YearId)) ? $tblYear->getDisplayName() . ' wird migriert.' : 'Schuljahr nicht gefunden');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $YearId
     *
     * @return string
     */
    public function migrateYear($YearId): string
    {
        if (($tblYear = Term::useService()->getYearById($YearId))) {
            $time = DivisionCourse::useService()->migrateYear($tblYear);
            return new Success(new Bold($tblYear->getDisplayName()) . ' erfolgreich migriert. ' . new PullRight($time . ' Sekunden'))
                . (($tblNextYear = $this->getNextYear($tblYear))
                    ? self::pipelineMigrateYear($tblNextYear->getId())
                    : self::pipelineStatus(self::STATUS_FINISH));
        }

        return new Danger('Schuljahr nicht gefunden', new Exclamation());
    }

    /**
     * @param TblYear|null $tblYear
     *
     * @return false|TblYear
     */
    private function getNextYear(TblYear $tblYear = null)
    {
        $hasFoundYear = false;
        if (($tblYearList = Term::useService()->getYearAll())) {
            $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('Id');
            /** @var TblYear $tblYearItem */
            foreach ($tblYearList as $tblYearItem) {
                if (!$tblYear || $hasFoundYear) {
                    return $tblYearItem;
                }

                $hasFoundYear = $tblYear->getId() == $tblYearItem->getId();
            }
        }

        return false;
    }
}