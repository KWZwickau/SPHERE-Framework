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
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Muted;
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

//        $Dispatcher->registerMethod('openDeleteAbsenceModal');
//        $Dispatcher->registerMethod('saveDeleteAbsenceModal');

        $Dispatcher->registerMethod('loadAbsenceContent');
        $Dispatcher->registerMethod('searchPerson');
        $Dispatcher->registerMethod('loadLesson');
        $Dispatcher->registerMethod('loadType');

        $Dispatcher->registerMethod('generateOrganizerWeekly');

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
     *
     * @return Pipeline
     */
    public static function pipelineOpenCreateAbsenceModal($PersonId = null, $DivisionId = null)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateAbsenceModal',
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
    public function openCreateAbsenceModal($PersonId = null, $DivisionId = null)
    {
        return $this->getAbsenceModal(
            Absence::useFrontend()->formAbsence(null, $PersonId == null, '', null, $PersonId, $DivisionId),
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

        $now = new DateTime('now');

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
                . self::pipelineChangeWeek($now->format('W') , $now->format('Y'))
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

        $now = new DateTime('now');
        $tblPerson = $tblAbsence->getServiceTblPerson();
        $tblDivision = $tblAbsence->getServiceTblDivision();

        if (Absence::useService()->updateAbsenceService($tblAbsence, $Data)) {
            return new Success('Die Fehlzeit wurde erfolgreich gespeichert.')
                . self::pipelineChangeWeek($now->format('W') , $now->format('Y'))
                . self::pipelineLoadAbsenceContent($tblPerson ? $tblPerson->getId() : null, $tblDivision ? $tblDivision->getId() : null)
                . self::pipelineClose();
        } else {
            return new Danger('Die Fehlzeit konnte nicht gespeichert werden.') . self::pipelineClose();
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

        $Emitter = new ServerEmitter(self::receiverBlock('', 'CalendarContent'), self::getEndpoint());
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
        $ColumnDefinition = array();
        $ColumnContent = array();
        $TableContent = array();

        $organizerBaseData = self::convertOrganizerBaseData();
        $DayName = $organizerBaseData['dayName'];
        $MonthName = $organizerBaseData['monthNameShort'];
//        $EntryColor = $organizerBaseData['entryColor'];

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
                                self::setAbsenceContent($dataList, $tblPerson, $tblDivisionItem, $tblAbsence, $date->format('d.m.Y'));
                                $date = $date->modify('+1 day');
                            }
                        } elseif ($toDate == $fromDate) {
                            self::setAbsenceContent($dataList, $tblPerson, $tblDivisionItem, $tblAbsence, $tblAbsence->getFromDate());
                        }
                    } else {
                        self::setAbsenceContent($dataList, $tblPerson, $tblDivisionItem, $tblAbsence, $tblAbsence->getFromDate());
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

        // Tabelle vorbereiten
        $ColumnDefinition['Division']= '<div style="background-color: lightgrey; height: 56px; text-align: center; padding-top: 19px;">'
            . 'Klasse' . '</div>';

        // Kalender-Inhalt erzeugen
        // todo Schuljahreswechsel innerhalb der Woche
        if (($tblYearList = Term::useService()->getYearAllByDate($startDate))) {
            foreach ($tblYearList as $tblYear) {
                if (($tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear))) {
                    $tblDivisionList = (new Extension())->getSorter($tblDivisionList)
                        ->sortObjectBy('DisplayName', new StringNaturalOrderSorter());

                    // Content der je Klasse erstellen
                    foreach ($tblDivisionList as $tblDivision) {
                        $StartDayPerson = new DateTime(date('d.m.Y', strtotime("$Year-W{$Week}")));
                        $EndDayPerson = new DateTime(date('d.m.Y', strtotime("$Year-W{$Week}-7")));

                        // todo versuchen dynamisch über TableNoPadding zu setzen
                        if (isset($personCountList[$tblDivision->getId()]) && ($countPersons = $personCountList[$tblDivision->getId()]) > 1) {
                            $height = ($countPersons * 19) . 'px';
                            if ($countPersons == 2) {
                                $padding = '8px';
                            } else {
                                $padding = ($countPersons * 6) . 'px';
                            }
                        } else {
                            $height = '30px';
                            $padding = '5px';
                        }

                        $ColumnContent['Division'] = '<div style="font-weight: bold; text-align: center; 
                                background-color: lightgrey; padding: ' . $padding . ' 0; height: ' . $height . ';">'
                            . $tblDivision->getDisplayName()
                            . '</div>';
                        if ($StartDayPerson && $EndDayPerson) {
                            while ($StartDayPerson <= $EndDayPerson) {
                                $DayAtWeek = $StartDayPerson->format('w');
                                $Day = (int)$StartDayPerson->format('d');
                                $Month = (int)$StartDayPerson->format('m');

                                $isHoliday = Term::useService()->getHolidayByDay($tblYear, $StartDayPerson);

                                if ($DayName[$DayAtWeek] == '(Sa)' || $DayName[$DayAtWeek] == '(So)' || $isHoliday) {
                                    $ColumnEntry = '<div style="background-color: lightgrey; opacity: 0.5; padding: ' . $padding . ' 0; height: ' . $height . ';">';
                                } elseif (isset($dataList[$tblDivision->getId()][$StartDayPerson->format('d.m.Y')])) {
                                    $ColumnEntry = implode('<br>', $dataList[$tblDivision->getId()][$StartDayPerson->format('d.m.Y')]);
                                } else {
                                    $ColumnEntry = '&nbsp;';
                                }

                                $ColumnContent['Day' . $Day] = new Center($ColumnEntry);

                                if (!isset($ColumnDefinition['Day' . $Day])) {
                                    $ColumnDefinition['Day' . $Day] = new Center(new Muted($DayName[$DayAtWeek]) . '<br>' . $Day . '. <br>' . $MonthName[$Month]);
                                    if ((int)$currentDate->format('d') == $Day && (int)$currentDate->format('m') == $Month && $currentDate->format('Y') == $Year) {
                                        $ColumnDefinition['Day' . $Day] = '<span style="color: darkorange;">' . new Center(new Muted($DayName[$DayAtWeek]) . '<br>' . $Day . '.<br>' . $MonthName[$Month]) . '</span>';
                                    }
                                    if ($DayName[$DayAtWeek] == '(Sa)' || $DayName[$DayAtWeek] == '(So)' || $isHoliday) {
                                        $ColumnDefinition['Day' . $Day] = '<div style="background-color: lightgrey; opacity: 0.5; color: black; height: 56px;">' .
                                            new Center($DayName[$DayAtWeek] . '<br>' . $Day . '.<br>' . $MonthName[$Month]) . '</div>';
                                    }
                                }

                                // Tag weiterzählen
                                $StartDayPerson->modify('+1 day');
                            }
                        }

                        array_push($TableContent, $ColumnContent);
                    }
                }
            }
        }

        // Inhalt zusammenbasteln
        $Content = new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn(''
//                                    new Refresh().
//                                    (new ToolTipNeu(
//                                        (new Link(' Monat', self::getEndpoint()))
//                                            ->ajaxPipelineOnClick(self::pipelineChangeOrganizerOptions($tblcurrentPerson->getId(), 'Month'))
//                                        , htmlspecialchars('<span style="color: black;">auf Monatsansicht wechseln</span>')))->enableHtml()
//                                    .' | '.
//                                    (new ToolTipNeu(
//                                        (new Link(' Tag +', self::getEndpoint()))
//                                            ->ajaxPipelineOnClick(self::pipelineChangeOrganizerOptions($tblcurrentPerson->getId(), 'Today'))
//                                        , htmlspecialchars('<span style="color: black;">Ansicht wechseln, beginnend ab Heute +30 Tage</span>')))->enableHtml()
                                    , 3),
                                new LayoutColumn(
                                    new Center(
                                        (new Link(new ChevronLeft(), self::getEndpoint(), null, array(), 'KW' . $WeekBefore))
                                            ->ajaxPipelineOnClick(self::pipelineChangeWeek($WeekBefore, $YearBefore))
                                    )
                                    , 1),
                                new LayoutColumn(
                                    new Center(new Bold('KW' . $WeekNumber. ' ')) // . new Small(new Muted($Year)))
                                    , 4),
                                new LayoutColumn(
                                    new Center(
                                        (new Link(new ChevronRight(), self::getEndpoint(), null, array(), 'KW' . $WeekNext))
                                            ->ajaxPipelineOnClick(self::pipelineChangeWeek($WeekNext, $YearNext))
                                    )
                                    , 1),
                                new LayoutColumn(''
//                                    new PullRight((new Link(' Download', self::getEndpoint(), new Download(), array(), 'Download der Daten vorbereiten'))
//                                        ->ajaxPipelineOnClick(self::pipelineOpenDownloadEdit($PersonId))
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
                        new TableData($TableContent, null, $ColumnDefinition, false, false, false)
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
    private static function setAbsenceContent(
        &$dataList,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblAbsence $tblAbsence,
        $date
    ) {
        // bei Unterrichtseinheiten dahinter in Klammern (1.UE)
        // E entschuldig, U unentschuldig
        // wie kombination eventuell ein Gyphicon
        // T Theorie, P Praxis
        // [Vorname] [Nachname] ( [[UE]] / [T/P] / [U/E])

        $lesson = Absence::useService()->getLessonStringByAbsence($tblAbsence);
        $type = $tblAbsence->getTypeDisplayShortName();

        $dataList[$tblDivision->getId()][$date][$tblPerson->getId()] = (new Link(
            $tblPerson->getFullName()
                . ' (' . ($lesson ? $lesson . ' / ': '')
                . ($type ? $type . ' / ': '')
                . $tblAbsence->getStatusDisplayShortName() . ')',
            ApiAbsence::getEndpoint(),
            null,
            array(),
            'Bearbeiten'
        ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenEditAbsenceModal($tblAbsence->getId()));
    }

    /**
     * @return array
     */
    public static function convertOrganizerBaseData(){
        $data['dayName'] = array(
            '0' => '(So)',
            '1' => '(Mo)',
            '2' => '(Di)',
            '3' => '(Mi)',
            '4' => '(Do)',
            '5' => '(Fr)',
            '6' => '(Sa)',
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
            '12' =>"Dezember");

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
            '12' =>"Dez");

        $data['entryColor'] = array(
            'A' => '<div style="background-color: lightskyblue; color: black; padding-top: 5px; padding-bottom: 5px; height: 30px;">',
            'U' => '<div style="background-color: greenyellow; color: black; padding-top: 5px; padding-bottom: 5px; height: 30px;">',
            'T' => '<div style="background-color: darkgrey; color: black; padding-top: 5px; padding-bottom: 5px; height: 30px;">',
            'K' => '<div style="background-color: orangered; color: black; padding-top: 5px; padding-bottom: 5px; height: 30px;">',
            'S' => '<div style="background-color: orange; color: black; padding-top: 5px; padding-bottom: 5px; height: 30px;">',
            'W' => '<div style="background-color: lightskyblue; color: black; padding-top: 5px; padding-bottom: 5px; height: 30px;">',
            'AFont' => '<span style="color: cornflowerblue;">',
            'UFont' => '<span style="color: limegreen;">',
            'TFont' => '<span style="color: darkgrey;">',
            'KFont' => '<span style="color: orangered;">',
            'SFont' => '<span style="color: orange;">',
            'WFont' => '<span style="color: lightskyblue;">'
        );

        return $data;
    }
}