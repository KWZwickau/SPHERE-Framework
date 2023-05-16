<?php

namespace SPHERE\Application\Api\Platform\DataMaintenance;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Instruction\Instruction;
use SPHERE\Application\Education\Diary\Diary;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Check;
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

    const TYPE_DIVISION_COURSE = 'DIVISION_COURSE';
    const TYPE_TEST = 'TEST';
    const TYPE_TASK = 'TASK';
    const TYPE_COURSE_CONTENT = 'COURSE_CONTENT';
    const TYPE_INSTRUCTION_ITEM = 'INSTRUCTION_ITEM';
    const TYPE_DIARY = 'DIARY';
    const TYPE_ABSENCE = 'ABSENCE';

    const MAX_DIVISION_COUNT = 5;

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
        $Dispatcher->registerMethod('migrateScoreRules');
        $Dispatcher->registerMethod('migrateMinimumGradeCounts');
        $Dispatcher->registerMethod('migrateStudentSubjectLevels');
        $Dispatcher->registerMethod('migrateYear');
        $Dispatcher->registerMethod('migrateYearItem');

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
        list($count, $time) = DivisionCourse::useService()->migrateTblDivisionToTblDivisionCourse();
        return new Success("$count Klassen erfolgreich migriert." . new PullRight("$time Sekunden"))
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
        list($count, $time) = DivisionCourse::useService()->migrateTblGroupToTblDivisionCourse();
        return new Success("$count Stammgruppen erfolgreich migriert." . new PullRight("$time Sekunden"))
            . self::pipelineMigrateScoreRules();
    }

    /**
     * @return Pipeline
     */
    public static function pipelineMigrateScoreRules(): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'MigrateScoreRules'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'migrateScoreRules',
        ));
        $ModalEmitter->setLoadingMessage('Berechnungsvorschriften werden migriert.');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function migrateScoreRules(): string
    {
        list($count, $time) = Grade::useService()->migrateScoreRules();
        return new Success("$count Berechnungsvorschriften erfolgreich migriert." . new PullRight("$time Sekunden"))
            . self::pipelineMigrateMinimumGradeCounts();
    }

    /**
     * @return Pipeline
     */
    public static function pipelineMigrateMinimumGradeCounts(): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'MigrateMinimumGradeCounts'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'migrateMinimumGradeCounts',
        ));
        $ModalEmitter->setLoadingMessage('Berechnungsvorschriften werden migriert.');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function migrateMinimumGradeCounts(): string
    {
        list($count, $time) = Grade::useService()->migrateMinimumGradeCounts();
        return new Success("$count Mindestnoten erfolgreich migriert." . new PullRight("$time Sekunden"))
            . self::pipelineMigrateStudentSubjectLevels();
    }

    /**
     * @return Pipeline
     */
    public static function pipelineMigrateStudentSubjectLevels(): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'MigrateStudentSubjectLevels'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'migrateStudentSubjectLevels',
        ));
        $ModalEmitter->setLoadingMessage('Klassenstufen der Fremdsprachen werden migriert.');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function migrateStudentSubjectLevels(): string
    {
        list($count, $time) = Student::useService()->migrateStudentSubjectLevels();
        return new Success("$count Klassenstufen der Fremdsprachen erfolgreich migriert." . new PullRight("$time Sekunden"))
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
        ini_set('memory_limit', '2G');

        if (Term::useService()->getYearById($YearId)) {
            $result = self::receiverBlock(new Warning('Bitte warten. Die Klassen-Inhalte werden migriert.', new History()), 'MigrateYearItem_' . $YearId . '_' . self::TYPE_DIVISION_COURSE)
                . self::receiverBlock(new Warning('Bitte warten. Die Leistungsüberprüfungen werden migriert.', new History()), 'MigrateYearItem_' . $YearId . '_' . self::TYPE_TEST)
                . self::receiverBlock(new Warning('Bitte warten. Die Notenaufträge werden migriert.', new History()), 'MigrateYearItem_' . $YearId . '_' . self::TYPE_TASK)
                . self::receiverBlock(new Warning('Bitte warten. Die Kursbücher-Einträge werden migriert.', new History()), 'MigrateYearItem_' . $YearId . '_' . self::TYPE_COURSE_CONTENT)
                . self::receiverBlock(new Warning('Bitte warten. Die Belehrungseinträge werden migriert.', new History()), 'MigrateYearItem_' . $YearId . '_' . self::TYPE_INSTRUCTION_ITEM)
                . self::receiverBlock(new Warning('Bitte warten. Die Pädagogischen Tagebücher werden migriert.', new History()), 'MigrateYearItem_' . $YearId . '_' . self::TYPE_DIARY)
                . self::receiverBlock(new Warning('Bitte warten. Die Fehlzeiten werden migriert.', new History()), 'MigrateYearItem_' . $YearId . '_' . self::TYPE_ABSENCE);

            return $result . self::pipelineMigrateYearItem($YearId, self::TYPE_DIVISION_COURSE);
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

    /**
     * @param $YearId
     * @param $Type
     * @param $StartId
     *
     * @return Pipeline
     */
    public static function pipelineMigrateYearItem($YearId, $Type, $StartId = null): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'MigrateYearItem_' . $YearId . '_' . $Type . ($StartId ? '_' . $StartId : '')), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'migrateYearItem',
        ));
        $ModalEmitter->setPostPayload(array(
            'YearId' => $YearId,
            'Type' => $Type,
            'StartId' => $StartId
        ));

        switch ($Type) {
            case self::TYPE_DIVISION_COURSE: $message = 'Klasseninhalte'; break;
            case self::TYPE_TEST: $message = 'Leistungsüberprüfungen für Klassen ab Id=' . $StartId; break;
            case self::TYPE_TASK: $message = 'Notenaufträge'; break;
            case self::TYPE_COURSE_CONTENT: $message = 'Kursbücher-Einträge'; break;
            case self::TYPE_INSTRUCTION_ITEM: $message = 'Belehrungseinträge'; break;
            case self::TYPE_DIARY: $message = 'Pädagogische Tagebücher Elemente'; break;
            case self::TYPE_ABSENCE: $message = 'Fehlzeiten'; break;
            default: $message = $Type;
        }

        $ModalEmitter->setLoadingMessage(
            ($tblYear = Term::useService()->getYearById($YearId))
                ? new Bold($tblYear->getDisplayName()) . ' (' . $message . ')' . ' wird migriert.'
                : 'Schuljahr nicht gefunden'
        );
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $YearId
     * @param $Type
     * @param $StartId
     *
     * @return string
     */
    public function migrateYearItem($YearId, $Type, $StartId): string
    {
        ini_set('memory_limit', '2G');

        if (($tblYear = Term::useService()->getYearById($YearId))) {
            switch ($Type) {
                case self::TYPE_DIVISION_COURSE:
                    $startValue = 0;
                    return new Success('Klassen-Inhalte erfolgreich migriert' . new PullRight(DivisionCourse::useService()->migrateYear($tblYear) . ' Sekunden'), new Check())
                        . self::receiverBlock('', 'MigrateYearItem_' . $YearId . '_' . self::TYPE_TEST . '_' . $startValue)
                        . self::pipelineMigrateYearItem($YearId, self::TYPE_TEST, $startValue);
                case self::TYPE_TEST:
                    $startValue = $StartId === null ? 0 : intval($StartId);
                    if (($tblDivisionList = Division::useService()->getDivisionListByStartIdAndMaxCount($tblYear, $startValue, self::MAX_DIVISION_COUNT))) {
                        $count = count($tblDivisionList);
                        $lastId = (end($tblDivisionList))->getId();
                        return new Success(
                                "Leistungsüberprüfungen für $count Klassen erfolgreich migriert"
                                    . new PullRight(Grade::useService()->migrateTests($tblYear, $tblDivisionList) . ' Sekunden'),
                                new Check()
                            )
                            . self::receiverBlock('', 'MigrateYearItem_' . $YearId . '_' . self::TYPE_TEST . '_' . $lastId)
                            . self::pipelineMigrateYearItem($YearId, self::TYPE_TEST, $lastId);
                    } else {
                        return new Success('Alle Leistungsüberprüfungen des Schuljahres erfolgreich migriert' , new Check())
                            . self::pipelineMigrateYearItem($YearId, self::TYPE_TASK);
                    }
                case self::TYPE_TASK:
                    return new Success('Notenaufträge erfolgreich migriert' . new PullRight(Grade::useService()->migrateTasks($tblYear) . ' Sekunden'), new Check())
                        . self::pipelineMigrateYearItem($YearId, self::TYPE_COURSE_CONTENT);
                case self::TYPE_COURSE_CONTENT:
                    list ($count, $time) = Digital::useService()->migrateYear($tblYear);
                    return new Success($count . ' Kursbücher-Einträge erfolgreich migriert' . new PullRight($time . ' Sekunden'), new Check())
                        . self::pipelineMigrateYearItem($YearId, self::TYPE_INSTRUCTION_ITEM);
                case self::TYPE_INSTRUCTION_ITEM:
                    list ($count, $time) = Instruction::useService()->migrateYear($tblYear);
                    return new Success($count . ' Belehrungseinträge erfolgreich migriert' . new PullRight($time . ' Sekunden'), new Check())
                        . self::pipelineMigrateYearItem($YearId, self::TYPE_DIARY);
                case self::TYPE_DIARY:
                    list ($count, $time) = Diary::useService()->migrateYear($tblYear);
                    return new Success($count . ' Pädagogische Tagebücher Elemente erfolgreich migriert' . new PullRight($time . ' Sekunden'), new Check())
                        . self::pipelineMigrateYearItem($YearId, self::TYPE_ABSENCE);
                case self::TYPE_ABSENCE:
                    $startValue = $StartId === null ? 0 : intval($StartId);
                    if (($tblDivisionList = Division::useService()->getDivisionListByStartIdAndMaxCount($tblYear, $startValue, self::MAX_DIVISION_COUNT))) {
                        $count = count($tblDivisionList);
                        $lastId = (end($tblDivisionList))->getId();
                        return new Success(
                                "Fehlzeiten für $count Klassen erfolgreich migriert"
                                . new PullRight(Absence::useService()->migrateYear($tblYear, $tblDivisionList) . ' Sekunden'),
                                new Check()
                            )
                            . self::receiverBlock('', 'MigrateYearItem_' . $YearId . '_' . self::TYPE_ABSENCE . '_' . $lastId)
                            . self::pipelineMigrateYearItem($YearId, self::TYPE_ABSENCE, $lastId);
                    } else {
                        return new Success('Alle Fehlzeiten des Schuljahres erfolgreich migriert', new Check())
                            . new Success(new Bold($tblYear->getDisplayName()) . ' erfolgreich migriert. ')
                            . (($tblNextYear = $this->getNextYear($tblYear))
                                ? self::pipelineMigrateYear($tblNextYear->getId())
                                : self::pipelineStatus(self::STATUS_FINISH));
                    }
            }
        }

        return new Danger('Schuljahr nicht gefunden', new Exclamation());
    }
}