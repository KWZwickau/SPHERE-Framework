<?php

namespace SPHERE\Application\Api\Education\ClassRegister;

use DateTime;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Frontend\Table\Structure\TableBody;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

/**
 * Class ApiAbsence
 *
 * @package SPHERE\Application\Api\Education\ClassRegister
 */
class ApiAbsence extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('openCreateAbsenceModal');
        $Dispatcher->registerMethod('saveCreateAbsenceModal');

        $Dispatcher->registerMethod('openEditAbsenceModal');
        $Dispatcher->registerMethod('saveEditAbsenceModal');

        $Dispatcher->registerMethod('openDeleteAbsenceModal');
        $Dispatcher->registerMethod('saveDeleteAbsenceModal');

        $Dispatcher->registerMethod('loadAbsenceContent');
        $Dispatcher->registerMethod('searchPerson');
        $Dispatcher->registerMethod('loadLesson');
        $Dispatcher->registerMethod('loadType');

        $Dispatcher->registerMethod('generateOrganizerWeekly');
        $Dispatcher->registerMethod('generateOrganizerMonthly');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReciever');
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
    public static function pipelineClose()
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @param null $PersonId
     * @param null $DivisionId
     * @param null $Date
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateAbsenceModal($PersonId = null, $DivisionId = null, $Date = null)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateAbsenceModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'DivisionId' => $DivisionId,
            'Date' => $Date
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $PersonId
     * @param null $DivisionId
     * @param null $Date
     *
     * @return string
     */
    public function openCreateAbsenceModal($PersonId = null, $DivisionId = null, $Date = null)
    {
        return $this->getAbsenceModal(
            Absence::useFrontend()->formAbsence(null, $PersonId == null, '', null, $PersonId, $DivisionId, null, null, $Date),
            null,
            $PersonId,
            $DivisionId,
            $PersonId == null
        );
    }

    /**
     * @param $form
     * @param null $AbsenceId
     * @param null $PersonId
     * @param null $DivisionId
     * @param bool $hasSearch
     *
     * @return string
     */
    private function getAbsenceModal($form,  $AbsenceId = null, $PersonId = null, $DivisionId = null, $hasSearch = false)
    {
        $tblPerson = false;
        $tblDivision = false;
        if ($AbsenceId) {
            $title = new Title(new Edit() . ' Fehlzeit bearbeiten');
            if (($tblAbsence = Absence::useService()->getAbsenceById($AbsenceId))) {
                $tblPerson = $tblAbsence->getServiceTblPerson();
                $tblDivision = $tblAbsence->getServiceTblDivision();
            }
        } else {
            $title = new Title(new Plus() . ' Fehlzeit hinzufügen');
            if ($PersonId) {
                $tblPerson = Person::useService()->getPersonById($PersonId);
            }
            if ($DivisionId) {
                $tblDivision = Division::useService()->getDivisionById($DivisionId);
            }
        }

        return $title
            . new Layout(array(
                    new LayoutGroup(array(
                        !$hasSearch && $tblPerson && $tblDivision ? new LayoutRow(array(
                            new LayoutColumn(new Panel(
                                'Schüler',
                                $tblPerson->getFullName(),
                                Panel::PANEL_TYPE_INFO
                            ), 6),
                            new LayoutColumn(new Panel(
                                'Klasse',
                                $tblDivision->getDisplayName(),
                                Panel::PANEL_TYPE_INFO
                            ), 6)
                        )) : null,
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    $form
                                )
                            )
                        )
                    )))
            );
    }

    /**
     * @param null $PersonId
     * @param null $DivisionId
     *
     * @param null $hasSearch
     * @return Pipeline
     */
    public static function pipelineCreateAbsenceSave($PersonId = null, $DivisionId = null, $hasSearch = null)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateAbsenceModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'DivisionId' => $DivisionId,
            'hasSearch' => $hasSearch
        ));

        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Data
     * @param $Search
     * @param null $PersonId
     * @param null $DivisionId
     * @param null $hasSearch
     *
     * @return string
     */
    public function saveCreateAbsenceModal($Data, $Search, $PersonId = null, $DivisionId = null, $hasSearch = null)
    {
        $hasSearch = $hasSearch == 'true';
        if (($form = Absence::useService()->checkFormAbsence($Data, $Search, null, $PersonId, $DivisionId, $hasSearch))) {
            // display Errors on form
            return $this->getAbsenceModal($form, null, $PersonId, $DivisionId, $hasSearch);
        }

        $date = new DateTime(isset($Data['FromDate']) ? $Data['FromDate'] : 'now');

        $tblPerson = false;
        $tblDivision = false;
        if (!$hasSearch) {
            if ($PersonId) {
                $tblPerson = Person::useService()->getPersonById($PersonId);
            }
            if ($DivisionId) {
                $tblDivision = Division::useService()->getDivisionById($DivisionId);
            }
        }

        if (Absence::useService()->createAbsenceService(
            $Data,
            $tblPerson ? $tblPerson :  null,
            $tblDivision ? $tblDivision : null
        )) {
            return new Success('Die Fehlzeit wurde erfolgreich gespeichert.')
                . self::pipelineChangeWeek($date->format('W') , $date->format('Y'))
                . ($tblDivision ? self::pipelineChangeMonth($tblDivision->getId(), $date->format('m') , $date->format('Y')) : '')
                . self::pipelineLoadAbsenceContent($tblPerson ? $tblPerson->getId() : null, $tblDivision ? $tblDivision->getId() : null)
                . self::pipelineClose();
        } else {
            return new Danger('Die Fehlzeit konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param int $AbsenceId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditAbsenceModal($AbsenceId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditAbsenceModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'AbsenceId' => $AbsenceId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $AbsenceId
     *
     * @return Danger|string
     */
    public function openEditAbsenceModal($AbsenceId)
    {
        if (!($tblAbsence = Absence::useService()->getAbsenceById($AbsenceId))) {
            return new Danger('Die Fehlzeit wurde nicht gefunden', new Exclamation());
        }

        $tblPerson = $tblAbsence->getServiceTblPerson();
        $tblDivision = $tblAbsence->getServiceTblDivision();

        return $this->getAbsenceModal(Absence::useFrontend()->formAbsence(
            $AbsenceId, false, '', null, $tblPerson ? $tblPerson->getId() : null,
            $tblDivision ? $tblDivision->getId() : null
        ), $AbsenceId);
    }

    /**
     * @param $AbsenceId
     *
     * @return Pipeline
     */
    public static function pipelineEditAbsenceSave($AbsenceId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditAbsenceModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'AbsenceId' => $AbsenceId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $AbsenceId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveEditAbsenceModal($AbsenceId, $Data)
    {
        if (!($tblAbsence = Absence::useService()->getAbsenceById($AbsenceId))) {
            return new Danger('Die Fehlzeit wurde nicht gefunden', new Exclamation());
        }

        if (($form = Absence::useService()->checkFormAbsence($Data, '', $tblAbsence))) {
            // display Errors on form
            return $this->getAbsenceModal($form, $AbsenceId);
        }

        $date = new DateTime(isset($Data['FromDate']) ? $Data['FromDate'] : 'now');
        $tblPerson = $tblAbsence->getServiceTblPerson();
        $tblDivision = $tblAbsence->getServiceTblDivision();

        if (Absence::useService()->updateAbsenceService($tblAbsence, $Data)) {
            return new Success('Die Fehlzeit wurde erfolgreich gespeichert.')
                . self::pipelineChangeWeek($date->format('W') , $date->format('Y'))
                . ($tblDivision ? self::pipelineChangeMonth($tblDivision->getId(), $date->format('m') , $date->format('Y')) : '')
                . self::pipelineLoadAbsenceContent($tblPerson ? $tblPerson->getId() : null, $tblDivision ? $tblDivision->getId() : null)
                . self::pipelineClose();
        } else {
            return new Danger('Die Fehlzeit konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param int $AbsenceId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteAbsenceModal($AbsenceId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteAbsenceModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'AbsenceId' => $AbsenceId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $AbsenceId
     *
     * @return string
     */
    public function openDeleteAbsenceModal($AbsenceId)
    {
        if (!($tblAbsence = Absence::useService()->getAbsenceById($AbsenceId))) {
            return new Danger('Die Fehlzeit wurde nicht gefunden', new Exclamation());
        }

        $tblPerson = $tblAbsence->getServiceTblPerson();
        $tblDivision = $tblAbsence->getServiceTblDivision();

        return new Title(new Remove() . ' Fehlzeit löschen')
            . new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(new Panel(
                            'Schüler',
                            $tblPerson ? $tblPerson->getFullName() : '',
                            Panel::PANEL_TYPE_INFO
                        ), 6),
                        new LayoutColumn(new Panel(
                            'Klasse',
                            $tblDivision ? $tblDivision->getDisplayName() : '',
                            Panel::PANEL_TYPE_INFO
                        ), 6)
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diese Fehlzeit wirklich löschen?',
                                array(
                                    $tblAbsence->getDateSpan(),
                                    $tblAbsence->getStatusDisplayName()
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteAbsenceSave($AbsenceId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                ))
            );
    }

    /**
     * @param $AbsenceId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteAbsenceSave($AbsenceId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteAbsenceModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'AbsenceId' => $AbsenceId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $AbsenceId
     *
     * @return Danger|string
     */
    public function saveDeleteAbsenceModal($AbsenceId)
    {
        if (!($tblAbsence = Absence::useService()->getAbsenceById($AbsenceId))) {
            return new Danger('Die Fehlzeit wurde nicht gefunden', new Exclamation());
        }
        $tblDivision = $tblAbsence->getServiceTblDivision();
        $tblPerson = $tblAbsence->getServiceTblPerson();

        if (Absence::useService()->destroyAbsence($tblAbsence)) {
            return new Success('Die Fehlzeit wurde erfolgreich gelöscht.')
                . self::pipelineLoadAbsenceContent($tblPerson ? $tblPerson->getId() : null, $tblDivision ? $tblDivision->getId() : null)
                . self::pipelineClose();
        } else {
            return new Danger('Die Fehlzeit konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }

    /**
     * @param null $PersonId
     * @param null $DivisionId
     *
     * @return Pipeline
     */
    public static function pipelineLoadAbsenceContent($PersonId = null, $DivisionId = null)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'AbsenceContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadAbsenceContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId,
            'DivisionId' => $DivisionId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $PersonId
     * @param null $DivisionId
     *
     * @return string
     */
    public function loadAbsenceContent($PersonId = null, $DivisionId = null)
    {
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblPerson = Person::useService()->getPersonById($PersonId);

        if (!($tblDivision && $tblPerson)) {
            return new Danger('Die Klasse oder Person wurde nicht gefunden', new Exclamation());
        }

        return Absence::useFrontend()->loadAbsenceTable($tblPerson, $tblDivision);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSearchPerson()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SearchPerson'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'searchPerson',
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Search
     *
     * @return string
     */
    public function searchPerson($Search = null)
    {
        return Absence::useFrontend()->loadPersonSearch(trim($Search));
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadLesson()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'loadLesson'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadLesson',
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Layout|null
     */
    public function loadLesson()
    {
        return Absence::useFrontend()->loadLesson(isset($_POST['Data']['IsFullDay']));
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadType()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'loadType'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadType',
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return SelectBox|null
     */
    public function loadType()
    {
        return Absence::useFrontend()->loadType(isset($_POST['Data']['PersonId']) ? $_POST['Data']['PersonId'] : null);
    }

    /**
     * @param $WeekNumber
     * @param $Year
     *
     * @return Pipeline
     */
    public static function pipelineChangeWeek($WeekNumber, $Year){
        $Pipeline = new Pipeline(false);

        $Emitter = new ServerEmitter(self::receiverBlock('', 'CalendarWeekContent'), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'generateOrganizerWeekly',
            'WeekNumber' => $WeekNumber,
            'Year' => $Year
        ));

        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param string $WeekNumber
     * @param string $Year
     *
     * @return string
     */
    public static function generateOrganizerWeekly($WeekNumber = '', $Year = '')
    {
        // Definition
        $currentDate = new DateTime('now');

        if ($WeekNumber == '') {
            $WeekNumber = (int)(new DateTime('now'))->format('W');
        }
        if ($Year == '') {
            $Year = (int)$currentDate->format('Y');
        }

        $headerList = array();
        $bodyList = array();

        $organizerBaseData = self::convertOrganizerBaseData();
        $DayName = $organizerBaseData['dayName'];
        $MonthName = $organizerBaseData['monthNameShort'];

        // Kalenderwoche ermitteln
        $WeekNext = $WeekNumber + 1;
        $WeekBefore = $WeekNumber - 1;
        $YearNext = $Year;
        $YearBefore = $Year;
        $lastWeek = date('W', strtotime("31.12." . $Year));
        $countWeek = ($lastWeek == 1) ? 52 : $lastWeek;
        if ($WeekNumber == $countWeek) {
            $WeekNext = 1;
            $YearNext = $Year + 1;
        }
        if ($WeekNumber == 1) {
            $WeekBefore = $countWeek;
            $YearBefore = $Year - 1;
        }

        // Start-/Endtag der Woche ermitteln
        $Week = $WeekNumber;
        if ($WeekNumber < 10) {
            $Week = '0' . $WeekNumber;
        }
        $startDate = new DateTime(date('d.m.Y', strtotime("$Year-W{$Week}")));
        $endDate = new DateTime(date('d.m.Y', strtotime("$Year-W{$Week}-7")));

        $dataList = array();
        if (($tblAbsenceList = Absence::useService()->getAbsenceAllBetween($startDate, $endDate))) {
            foreach ($tblAbsenceList as $tblAbsence) {
                if (($tblPerson = $tblAbsence->getServiceTblPerson())
                    && ($tblDivisionItem = $tblAbsence->getServiceTblDivision())
                ) {
                    $fromDate = new DateTime($tblAbsence->getFromDate());
                    if ($tblAbsence->getToDate()) {
                        $toDate = new DateTime($tblAbsence->getToDate());
                        if ($toDate > $fromDate) {
                            $date = $fromDate;
                            while ($date <= $toDate) {
                                self::setAbsenceWeekContent($dataList, $tblPerson, $tblDivisionItem, $tblAbsence, $date->format('d.m.Y'));
                                $date = $date->modify('+1 day');
                            }
                        } elseif ($toDate == $fromDate) {
                            self::setAbsenceWeekContent($dataList, $tblPerson, $tblDivisionItem, $tblAbsence, $tblAbsence->getFromDate());
                        }
                    } else {
                        self::setAbsenceWeekContent($dataList, $tblPerson, $tblDivisionItem, $tblAbsence, $tblAbsence->getFromDate());
                    }
                }
            }
        }

        // get max Person count
        $personCountList = array();
        foreach ($dataList as $key => $data) {
            $personCountList[$key] = 0;
            foreach ($data as $day => $personArray) {
                $count = count($personArray);
                if ($count > $personCountList[$key]) {
                    $personCountList[$key] = $count;
                }
            }
        }

        $backgroundColor = '#E0F0FF';
        $minHeightHeader = '56px';
        $minHeightBody = '38px';
        $padding = '3px';

        $headerList['Division'] = (new TableColumn(new Center(new Bold('Klasse'))))
            ->setBackgroundColor($backgroundColor)
            ->setVerticalAlign('middle')
            ->setMinHeight($minHeightHeader)
            ->setPadding($padding);

        // Kalender-Inhalt erzeugen
        if (($tblYearList = Term::useService()->getYearAllByDate($startDate))) {
            foreach ($tblYearList as $tblYear) {
                if (($tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear))) {
                    $tblDivisionList = (new Extension())->getSorter($tblDivisionList)
                        ->sortObjectBy('DisplayName', new StringNaturalOrderSorter());

                    // Content der je Klasse erstellen
                    foreach ($tblDivisionList as $tblDivision) {
                        $startDate = new DateTime(date('d.m.Y', strtotime("$Year-W{$Week}")));
                        $endDate = new DateTime(date('d.m.Y', strtotime("$Year-W{$Week}-7")));

                        $bodyList[$tblDivision->getId()]['Division'] = (new TableColumn(new Center(new Bold($tblDivision->getDisplayName()))))
                            ->setBackgroundColor($backgroundColor)
                            ->setVerticalAlign('middle')
                            ->setMinHeight($minHeightBody)
                            ->setPadding($padding);

                        if ($startDate && $endDate) {
                            while ($startDate <= $endDate) {
                                $DayAtWeek = $startDate->format('w');
                                $Day = (int)$startDate->format('d');
                                $Month = (int)$startDate->format('m');

                                $isWeekend = $DayAtWeek == 0 || $DayAtWeek == 6;
                                $isHoliday = Term::useService()->getHolidayByDay($tblYear, $startDate);

                                if (!isset($headerList['Day' . $Day])) {
                                    $columnHeader = (new TableColumn(new Center(
                                        $DayName[$DayAtWeek] . new Container($Day) . new Container($MonthName[$Month])
                                    )))
                                        ->setMinHeight($minHeightHeader)
                                        ->setPadding($padding);

                                    if ((int)$currentDate->format('d') == $Day && (int)$currentDate->format('m') == $Month && $currentDate->format('Y') == $Year) {
                                        $columnHeader
                                            ->setColor('darkorange');
                                    }
                                    if ($isWeekend || $isHoliday) {
                                        $columnHeader->setBackgroundColor('lightgray')
                                            ->setOpacity(0.5);
                                    } else {
                                        $columnHeader->setBackgroundColor($backgroundColor);
                                    }

                                    $headerList['Day' . $Day] = $columnHeader;
                                }

                                if ($isWeekend || $isHoliday) {
                                    $columnBody = (new TableColumn(new Center($isWeekend ? new Muted(new Small('w')) : new Muted(new Small('f')))))
                                        ->setBackgroundColor('lightgrey')
                                        ->setVerticalAlign('middle')
                                        ->setOpacity(0.5);
                                } else {
                                    $columnBody = new TableColumn(new Center(
                                        isset($dataList[$tblDivision->getId()][$startDate->format('d.m.Y')])
                                            ? implode('<br>', $dataList[$tblDivision->getId()][$startDate->format('d.m.Y')])
                                            : '&nbsp;'
                                    ));
                                }

                                $bodyList[$tblDivision->getId()]['Day' . $Day] = $columnBody
                                    ->setMinHeight($minHeightBody)
                                    ->setPadding($padding);

                                $startDate->modify('+1 day');
                            }
                        }
                    }
                }
            }
        }

        $tableHead = new TableHead(new TableRow($headerList));
        $rows = array();
        foreach ($bodyList as $columnList) {
            $rows[] = new TableRow($columnList);
        }
        $tableBody = new TableBody($rows);
        $table = new Table($tableHead, $tableBody, null, false, null, 'TableCustom');

        // Inhalt zusammenbasteln
        $Content = new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn('&nbsp;', 3),
                                new LayoutColumn(
                                    new Center(
                                        (new Link(new ChevronLeft(), self::getEndpoint(), null, array(), 'KW' . $WeekBefore))
                                            ->ajaxPipelineOnClick(self::pipelineChangeWeek($WeekBefore, $YearBefore))
                                    )
                                    , 1),
                                new LayoutColumn(
                                    new ToolTip(new Center(new Bold('KW' . $WeekNumber. ' ')), $Year)
                                    , 4),
                                new LayoutColumn(
                                    new Center(
                                        (new Link(new ChevronRight(), self::getEndpoint(), null, array(), 'KW' . $WeekNext))
                                            ->ajaxPipelineOnClick(self::pipelineChangeWeek($WeekNext, $YearNext))
                                    )
                                    , 1),
                                new LayoutColumn(
                                    '&nbsp;'
//                                    new PullRight((new Link(' Download', self::getEndpoint(), new Download(), array(), 'Download der Daten vorbereiten'))
//                                        ->ajaxPipelineOnClick(self::pipelineOpenDownloadEdit($PersonId))
//                                    )
                                    , 3)
                            )))
                        )
                        . '<div style="height: 5px;"></div>'
                        , 12)
                ),
                new LayoutRow(
                    new LayoutColumn(
                        $table
                    )
                )
            ))
        );

        return $Content . ' ';
    }

    /**
     * @param $dataList
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblAbsence $tblAbsence
     * @param $date string
     */
    private static function setAbsenceWeekContent(
        &$dataList,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblAbsence $tblAbsence,
        $date
    ) {
        // bei Unterrichtseinheiten dahinter in Klammern (1.UE)
        // E entschuldig, U unentschuldig
        // T Theorie, P Praxis
        // [Vorname] [Nachname] ( [[UE]] / [T/P] / [U/E])

        $lesson = $tblAbsence->getLessonStringByAbsence();
        $type = $tblAbsence->getTypeDisplayShortName();

        $dataList[$tblDivision->getId()][$date][$tblPerson->getId()] = (new Link(
            $tblPerson->getFullName()
                . ' (' . ($lesson ? $lesson . ' / ': '')
                . ($type ? $type . ' / ': '')
                . $tblAbsence->getStatusDisplayShortName() . ')',
            ApiAbsence::getEndpoint(),
            null,
            array(),
            'Fehlzeit bearbeiten'
        ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenEditAbsenceModal($tblAbsence->getId()));
    }

    /**
     * @param $DivisionId
     * @param $Month
     * @param $Year
     *
     * @return Pipeline
     */
    public static function pipelineChangeMonth($DivisionId, $Month, $Year)
    {
        $Pipeline = new Pipeline(false);

        $Emitter = new ServerEmitter(self::receiverBlock('', 'CalendarMonthContent'), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'generateOrganizerMonthly',
            'DivisionId' => $DivisionId,
            'Month' => $Month,
            'Year' => $Year
        ));

        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param $DivisionId
     * @param string $Month
     * @param string $Year
     *
     * @return string
     */
    public static function generateOrganizerMonthly($DivisionId, $Month = '', $Year = '')
    {
        // Definitionen
        $currentDate = new DateTime('now');

        if ($Month == '') {
            $Month = (int)$currentDate->format('m');
        } else {
            $Month = (int)$Month;
        }
        if ($Year == '') {
            $Year = (int)$currentDate->format('Y');
        } else {
            $Year = (int)$Year;
        }

        $ColumnDefinition = array();
        $ColumnDefinitionStatic = array();
        $ColumnContent = array();
        $TableContent = array();
        $TableContentStatic = array();

        $organizerBaseData = self::convertOrganizerBaseData();
        $DayName = $organizerBaseData['dayName'];
        $MonthName = $organizerBaseData['monthName'];

        $MonthNext = (int)$Month + 1;
        $MonthBefore = (int)$Month - 1;
        $YearNext = (int)$Year;
        $YearBefore = (int)$Year;
        // falls Dezember -> Jahreswechsel erzeugen für Folgemonat
        if ($Month == '12'){
            $MonthNext = '1';
            $YearNext = (int)$Year + 1;
        }
        // falls Januar -> Jahreswechsel erzeugen für vorherigen Monat
        if ($Month == '1'){
            $MonthBefore = '12';
            $YearBefore = (int)$Year - 1;
        }

        // Tagesanzahl im aktuellen Monat ermitteln
        $DayCounter = cal_days_in_month(CAL_GREGORIAN, $Month, $Year);

        $startDateSchoolYear = new DateTime('01.' . $Month . '.' . $Year);
        $endDateSchoolYear = new DateTime($DayCounter . '.' . $Month . '.' . $Year);

        $dataList = array();
        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))
            && ($tblAbsenceList = Absence::useService()->getAbsenceAllBetweenByDivision($startDateSchoolYear, $endDateSchoolYear, $tblDivision))
        ) {
            foreach ($tblAbsenceList as $tblAbsence) {
                if (($tblPersonItem = $tblAbsence->getServiceTblPerson())
                    && ($tblDivisionItem = $tblAbsence->getServiceTblDivision())
                ) {
                    $fromDate = new DateTime($tblAbsence->getFromDate());
                    if ($tblAbsence->getToDate()) {
                        $toDate = new DateTime($tblAbsence->getToDate());
                        if ($toDate > $fromDate) {
                            $date = $fromDate;
                            while ($date <= $toDate) {
                                self::setAbsenceMonthContent($dataList, $tblPersonItem, $tblAbsence, $date->format('d.m.Y'));
                                $date = $date->modify('+1 day');
                            }
                        } elseif ($toDate == $fromDate) {
                            self::setAbsenceMonthContent($dataList, $tblPersonItem, $tblAbsence, $tblAbsence->getFromDate());
                        }
                    } else {
                        self::setAbsenceMonthContent($dataList, $tblPersonItem, $tblAbsence, $tblAbsence->getFromDate());
                    }
                }
            }
        }

        $height = '30px';
        $padding = '5px';

//        $color = 'lightgrey';
//        $color = '#D0E9F6';
        $color = '#E0F0FF';

        $hasMonthBefore = true;
        $hasMonthNext = true;

        // Tabelle vorbereiten
        $ColumnDefinitionStatic['Person']= '<div style="background-color: ' . $color . '; height: 36px; text-align: center; padding-top: 9px;">'
            . new PersonGroup() . 'Schüler</div>';

        // Einträge für alle ausgewählten Personen anzeigen
        if ($tblDivision
            && ($tblYear = $tblDivision->getServiceTblYear())
            && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
        ) {
            // Begrenzung auf den Zeitraum des aktuellen Schuljahres
            list($startDateSchoolYear, $endDateSchoolYear) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            /** @var DateTime $startDateSchoolYear */
            if ($startDateSchoolYear && $endDateSchoolYear) {
                $startDateSchoolYear = new DateTime('01.' . $startDateSchoolYear->format('m') . '.' . $startDateSchoolYear->format('Y'));
                $startDateMonth = new DateTime('01.' . ($Month <= 9 ? '0'.$Month : $Month) . '.' . $Year);
                if ($startDateMonth <= $startDateSchoolYear) {
                    $hasMonthBefore = false;
                }

                $endDateSchoolYear = new DateTime('01.' . $endDateSchoolYear->format('m') . '.' . $endDateSchoolYear->format('Y'));
                if ($startDateMonth >= $endDateSchoolYear) {
                    $hasMonthNext = false;
                }
            }

            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson){
                $ColumnContentStatic['Person']= '<div style="font-weight: bold; text-align: center; background-color: ' . $color . '; padding: 5px 0; height: 30px;">'
                    . new ToolTip(
                        (new Link('<span>' . $tblPerson->getLastFirstName() .' </span>', self::getEndpoint()))
                            ->ajaxPipelineOnClick(self::pipelineOpenCreateAbsenceModal($tblPerson->getId(), $tblDivision->getId()))
                        , 'Eine neue Fehlzeit für ' . $tblPerson->getFullName() . ' hinzufügen.'
                    ). '</div>';
                if ($DayCounter) {
                    $Day = 1;
                    while($Day <= $DayCounter){
                        $fetchedDate = new DateTime($Day . '.' . ($Month <= 9 ? '0'.$Month : $Month) . '.' . $Year);
                        $fetchedDateString = $fetchedDate->format('d.m.Y');
                        $DayAtWeek = (new DateTime(($Day < 10 ? '0'.$Day : $Day).'.'.$Month.'.'.$Year))->format('w');

                        $isWeekend = $DayAtWeek == 0 || $DayAtWeek == 6;
                        $isHoliday = Term::useService()->getHolidayByDay($tblYear, $fetchedDate);

                        if ($isWeekend || $isHoliday) {
                            $ColumnEntry = '<div style="background-color: lightgrey; opacity: 0.5; padding: ' . $padding . ' 0; height: ' . $height . ';">'
                                . ($isWeekend ? new Muted(new Small('w')) : new Muted(new Small('f'))) . '</div>';
                        } elseif (isset($dataList[$tblPerson->getId()][$fetchedDateString])) {
                            $ColumnEntry = $dataList[$tblPerson->getId()][$fetchedDateString];
                        } else {
                            $ColumnEntry = (new Link('<div style="padding-top: 5px; padding-bottom: 5px; height: 30px;"><span style="visibility: hidden">'.new Plus().'</span></div>',
                                self::getEndpoint(),
                                null,
                                array(),
                                'Eine neue Fehlzeit für ' . $tblPerson->getFullName() . ' für den '
                                    . $fetchedDateString . ' hinzufügen.'))
                                ->ajaxPipelineOnClick(self::pipelineOpenCreateAbsenceModal($tblPerson->getId(), $tblDivision->getId(), $fetchedDateString));
                        }

                        $ColumnContent['Day'.$Day]= new Center($ColumnEntry);

                        if (!isset($ColumnDefinition['Day' . $Day])) {
                            $ColumnDefinition['Day'.$Day]= '<div style="">'.new Center($Day).new Center(new Muted($DayName[$DayAtWeek])).'</div>';
                            if ((int)$currentDate->format('d') == $Day && (int)$currentDate->format('m') == $Month && $currentDate->format('Y') == $Year){
                                $ColumnDefinition['Day'.$Day]= '<div style=""><span id="OrganizerDay" style="color: darkorange;">'.new Center($Day).'</span>'.new Center(new Muted($DayName[$DayAtWeek])).'</div>';
                            }
                            if ($isWeekend || $isHoliday) {
                                $ColumnDefinition['Day'.$Day]= '<div style="background-color: lightgrey; opacity: 0.5; color: black;">'.
                                    new Center($Day.'<br>'.$DayName[$DayAtWeek]).'</div>';
                            }
                        }

                        $Day++;
                    }
                }

                array_push($TableContentStatic, $ColumnContentStatic);
                array_push($TableContent, $ColumnContent);
            }
        }

        $Content = new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn(''
//                                    new Refresh().
//                                    (new ToolTipNeu(
//                                        (new Link(' Woche', self::getEndpoint()))
//                                            ->ajaxPipelineOnClick(self::pipelineChangeOrganizerOptions($tblDivision->getId(), 'Week'))
//                                        , htmlspecialchars('<span style="color: black;">auf Kalenderwochenansicht wechseln</span>')))->enableHtml()
//                                    .' | '.
//                                    (new ToolTipNeu(
//                                        (new Link(' Tag +', self::getEndpoint()))
//                                            ->ajaxPipelineOnClick(self::pipelineChangeOrganizerOptions($tblDivision->getId(), 'Today'))
//                                        , htmlspecialchars('<span style="color: black;">Ansicht wechseln, beginnend ab Heute +30 Tage</span>')))->enableHtml()
                                    , 3),
                                new LayoutColumn(
                                    $hasMonthBefore
                                        ? new Center(
                                            (new Link(new ChevronLeft(), self::getEndpoint(), null, array(), $MonthName[$MonthBefore] . ' ' . $YearBefore))
                                                ->ajaxPipelineOnClick(self::pipelineChangeMonth($DivisionId, $MonthBefore, $YearBefore))
                                            )
                                        : ''
                                    , 1),
                                new LayoutColumn(
                                    new Center('<b>' . $MonthName[$Month] . ' ' . $Year . '</b>')
                                    , 4),
                                new LayoutColumn(
                                    $hasMonthNext
                                        ? new Center(
                                                (new Link(new ChevronRight(), self::getEndpoint(), null, array(), $MonthName[$MonthNext].' '.$YearNext))
                                                    ->ajaxPipelineOnClick(self::pipelineChangeMonth($DivisionId, $MonthNext, $YearNext))
                                            )
                                        : ''
                                    , 1),
                                new LayoutColumn(''
//                                    new PullRight((new Link(' Download', self::getEndpoint(), new Download(), array(), 'Download der Daten vorbereiten'))
//                                        ->ajaxPipelineOnClick(self::pipelineOpenDownloadEdit($DivisionId))
//                                    )
                                    , 3)
                            )))
                        )
                        //                        . new Small('<br>')
                        . '<div style="height: 5px;"></div>'
                        , 12)
                ),
                new LayoutRow(
                    new LayoutColumn(
                        '<div style="float: left;">'.
                        new TableData($TableContentStatic, null, $ColumnDefinitionStatic, false, false)
                        .'</div>'.
                        '<div id="OrganizerTable" style="overflow-x: scroll;">'.
                        new TableData($TableContent, null, $ColumnDefinition, false, false).
                        '</div>'.(($Month == (int)$currentDate->format('m') &&
                            $Year == (int)$currentDate->format('Y')) ?
                            '<script>
                                tableSelector = "div#OrganizerTable";
                                $(tableSelector).scrollLeft( $("span#OrganizerDay").offset().left - ( $(tableSelector).offset().left + ( $(tableSelector).width() / 2 ) ) )
                            </script>' : '')
                    )
                )
            ))
        );

        return $Content.' ';
    }

    /**
     * @param $dataList
     * @param TblPerson $tblPerson
     * @param TblAbsence $tblAbsence
     * @param $date
     */
    private static function setAbsenceMonthContent(
        &$dataList,
        TblPerson $tblPerson,
        TblAbsence $tblAbsence,
        $date
    ) {
        $lesson = $tblAbsence->getLessonStringByAbsence();
        $type = $tblAbsence->getTypeDisplayShortName();

        $backgroundColor = '#E0F0FF';
        $fontColor = '#337ab7';

        $dataList[$tblPerson->getId()][$date] = (new Link(
            '<div style="background-color: ' .  $backgroundColor . '; color: ' . $fontColor . ';">
                <div style="padding-bottom: 5px; padding-top: 5px; height: 30px;">'
                . $tblAbsence->getStatusDisplayShortName()
                . '</div>
            </div>',
            ApiAbsence::getEndpoint(),
            null,
            array(),
//            $tblPerson->getFullName() . ' (' .
            ($lesson ? $lesson . ' / ': '')
            . ($type ? $type . ' / ': '')
            . $tblAbsence->getStatusDisplayShortName()  //. ')'
        ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenEditAbsenceModal($tblAbsence->getId()));
    }

    /**
     * @return array
     */
    public static function convertOrganizerBaseData()
    {
//        $data['dayName'] = array(
//            '0' => '(So)',
//            '1' => '(Mo)',
//            '2' => '(Di)',
//            '3' => '(Mi)',
//            '4' => '(Do)',
//            '5' => '(Fr)',
//            '6' => '(Sa)',
//        );

        $data['dayName'] = array(
            '0' => 'So',
            '1' => 'Mo',
            '2' => 'Di',
            '3' => 'Mi',
            '4' => 'Do',
            '5' => 'Fr',
            '6' => 'Sa',
        );

        $data['monthName'] = array(
            '1' =>"Januar",
            '2' =>"Februar",
            '3' =>"März",
            '4' =>"April",
            '5' =>"Mai",
            '6' =>"Juni",
            '7' =>"Juli",
            '8' =>"August",
            '9' =>"September",
            '10' =>"Oktober",
            '11' =>"November",
            '12' =>"Dezember"
        );

        $data['monthNameShort'] = array(
            '1' =>"Jan",
            '2' =>"Feb",
            '3' =>"März",
            '4' =>"Apr",
            '5' =>"Mai",
            '6' =>"Jun",
            '7' =>"Jul",
            '8' =>"Aug",
            '9' =>"Sept",
            '10' =>"Okt",
            '11' =>"Nov",
            '12' =>"Dez"
        );

        return $data;
    }
}