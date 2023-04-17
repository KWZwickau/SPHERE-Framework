<?php

namespace SPHERE\Application\Education\Absence;

use DateTime;
use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Education\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\AbstractLink;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
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
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class FrontendClassRegister extends Extension implements IFrontendInterface
{
    /**
     * @param null $DivisionCourseId
     * @param null $PersonId
     * @param string $BasicRoute
     * @param string $ReturnRoute
     *
     * @return string
     */
    public function frontendAbsenceStudent($DivisionCourseId = null, $PersonId = null, string $BasicRoute = '', string $ReturnRoute = '') : string
    {
        $Stage = new Stage('Digitales Klassenbuch', 'Fehlzeiten Übersicht des Schülers');
        if ($ReturnRoute) {
            $Stage->addButton(new Standard('Zurück', $ReturnRoute, new ChevronLeft(),
                array(
                    'DivisionCourseId' => $DivisionCourseId,
                    'BasicRoute' => $BasicRoute,
                ))
            );
        }

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        ) {
            $Stage->setContent(
                new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Schüler',
                                        $tblPerson->getLastFirstNameWithCallNameUnderline(),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ), 6),
                                new LayoutColumn(array(
                                    new Panel(
                                        'Kurs',
                                        $tblDivisionCourse->getTypeName() . ': ' . $tblDivisionCourse->getDisplayName(),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ), 6)
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    ApiAbsence::receiverModal()
                                    . (new PrimaryLink(
                                        new Plus() . ' Fehlzeit hinzufügen',
                                        ApiAbsence::getEndpoint()
                                    ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal($PersonId, $DivisionCourseId)),
                                    new Container('&nbsp;')
                                ))
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    ApiAbsence::receiverBlock(
                                        $this->loadAbsenceTable($tblPerson, $tblDivisionCourse),
                                        'AbsenceContent'
                                    )
                                ))
                            ))
                        )) //, new Title(new ListingTable() . ' Übersicht')),
                    )
                )
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Person nicht gefunden.', new Ban());
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return string
     */
    public function loadAbsenceTable(TblPerson $tblPerson, TblDivisionCourse $tblDivisionCourse): string
    {
        $hasAbsenceTypeOptions = false;
        $tableData = array();
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && (list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear))
            && $startDate
            && $endDate
            && ($tblAbsenceList = Absence::useService()->getAbsenceAllBetweenByPerson($tblPerson, $startDate, $endDate))
        ) {
            $tblCompany = false;
            $tblSchoolType = false;
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                $tblCompany = $tblStudentEducation->getServiceTblCompany();
                $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                $hasAbsenceTypeOptions = $tblSchoolType && $tblSchoolType->isTechnical();
            }

            foreach ($tblAbsenceList as $tblAbsence) {
                $status = '';
                if ($tblAbsence->getStatus() == TblAbsence::VALUE_STATUS_EXCUSED) {
                    $status = new Success('entschuldigt');
                } elseif ($tblAbsence->getStatus() == TblAbsence::VALUE_STATUS_UNEXCUSED) {
                    $status = new \SPHERE\Common\Frontend\Text\Repository\Danger('unentschuldigt');
                }

                $isOnlineAbsence = $tblAbsence->getIsOnlineAbsence();

                $item = array(
                    'FromDate' => $isOnlineAbsence ? '<span style="color:darkorange">' . $tblAbsence->getFromDate() . '</span>' : $tblAbsence->getFromDate(),
                    'ToDate' => $isOnlineAbsence ? '<span style="color:darkorange">' . $tblAbsence->getToDate() . '</span>' : $tblAbsence->getToDate(),
                    'Days' => ($days = $tblAbsence->getDays($tblYear, null, $tblCompany ?: null, $tblSchoolType ?: null)) == 1
                        ? $days . ' ' . new Small(new Muted($tblAbsence->getWeekDay()))
                        : $days,
                    'Lessons' => $tblAbsence->getLessonStringByAbsence(),
                    'Remark' => $tblAbsence->getRemark(),
                    'Status' => $status,
                    'IsCertificateRelevant' => $tblAbsence->getIsCertificateRelevant() ? 'ja' : 'nein',
                    'PersonCreator' => $tblAbsence->getDisplayPersonCreator(false),
                    'PersonStaff' => $tblAbsence->getDisplayStaff(),
                    'Option' =>
                        (new Standard(
                            '',
                            ApiAbsence::getEndpoint(),
                            new Edit(),
                            array(),
                            'Bearbeiten'
                        ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenEditAbsenceModal($tblAbsence->getId(), $tblDivisionCourse->getId()))
                        . (new Standard(
                            '',
                            ApiAbsence::getEndpoint(),
                            new Remove(),
                            array(),
                            'Löschen'
                        ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenDeleteAbsenceModal($tblAbsence->getId(), $tblDivisionCourse->getId()))
                );

                if ($hasAbsenceTypeOptions) {
                    $item['Type'] = $tblAbsence->getTypeDisplayName();
                }

                $tableData[] = $item;
            }
        }

        if ($hasAbsenceTypeOptions) {
            $columns = array(
                'FromDate' => 'Datum von',
                'ToDate' => 'Datum bis',
                'Days' => 'Tage',
                'Lessons' => 'Unterrichts&shy;einheiten',
                'Type' => 'Typ',
                'Remark' => 'Bemerkung',
                'PersonCreator' => 'Ersteller',
                'PersonStaff' => 'Bearbeiter',
                'IsCertificateRelevant' => 'Zeugnisrelevant',
                'Status' => 'Status',
                'Option' => ''
            );
        } else {
            $columns = array(
                'FromDate' => 'Datum von',
                'ToDate' => 'Datum bis',
                'Days' => 'Tage',
                'Lessons' => 'Unterrichts&shy;einheiten',
                'Remark' => 'Bemerkung',
                'PersonCreator' => 'Ersteller',
                'PersonStaff' => 'Bearbeiter',
                'IsCertificateRelevant' => 'Zeugnisrelevant',
                'Status' => 'Status',
                'Option' => ''
            );
        }
        // name Downloadfile
        $FileName = 'Fehlzeiten '.$tblPerson->getLastName().' '.$tblPerson->getFirstName().' '.(new DateTime())->format('d-m-Y');

        return new TableData(
            $tableData,
            null,
            $columns,
            array(
                'order' => array(
                    array(0, 'desc')
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => 0),
                    array('type' => 'de_date', 'targets' => 1),
                    array('orderable' => false, 'width' => '60px', 'targets' => -1)
                ),
                'responsive' => false,
//                'ExtensionColVisibility' => array('Enabled' => true),
                'ExtensionDownloadExcel' => array(
                    'Enabled' => true,
                    'FileName' => $FileName,
                    'Columns' => '0,1,2,3,4,5,6,7,8',
                )
            )
        );
    }

    /**
     * @param null $DivisionCourseId
     * @param null $BackDivisionCourseId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendAbsenceMonth(
        $DivisionCourseId = null,
        $BackDivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Fehlzeiten (Kalenderansicht)');

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $stage->addButton(Digital::useFrontend()->getBackButton($tblDivisionCourse, $BackDivisionCourseId, $BasicRoute));
            $currentDate = new DateTime('now');
            // wenn der aktuelle Tag im Schuljahr ist dann diesen Anzeigen, ansonsten erster Tag des Schuljahres
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
                list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
                if ($startDate && $endDate
                    && ($currentDate < $startDate || $currentDate > $endDate)
                ) {
                    $currentDate = $startDate;
                }
            }

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow($tblDivisionCourse),
                        $tblDivisionCourse->getType()->getIsCourseSystem()
                            ? Digital::useService()->getHeadButtonListLayoutRowForCourseSystem($tblDivisionCourse, '/Education/ClassRegister/Digital/AbsenceMonth',
                                $BasicRoute, $BackDivisionCourseId)
                            : Digital::useService()->getHeadButtonListLayoutRow($tblDivisionCourse, '/Education/ClassRegister/Digital/AbsenceMonth', $BasicRoute)
                    )),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        ApiAbsence::receiverModal()
                        . ApiAbsence::receiverBlock(
                            Consumer::useService()->getAccountSettingValue('AbsenceView') == 'Month'
                                ? ApiAbsence::generateOrganizerMonthly($tblDivisionCourse->getId(), $currentDate->format('m'), $currentDate->format('Y'))
                                : ApiAbsence::generateOrganizerForDivisionWeekly($tblDivisionCourse->getId(), $currentDate->format('W'), $currentDate->format('Y')),
                            'CalendarContent'
                        )
                    )), new Title(new Calendar() . ' Fehlzeiten (Kalenderansicht)'))
                ))
            );
        } else {
            return new Danger('Klasse nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param $DivisionId
     * @param $Month
     * @param $Year
     *
     * @return string
     */
    public function generateOrganizerMonthly($DivisionId, $Month, $Year): string
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

        $headerListStatic = array();
        $bodyListStatic = array();
        $headerList = array();
        $bodyList = array();

        $organizerBaseData = Absence::useFrontend()->convertOrganizerBaseData();
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

        $startDateMonth = new DateTime('01.' . ($Month <= 9 ? '0' . $Month : $Month) . '.' . $Year);
        $endDateMonth = new DateTime($DayCounter . '.' . $Month . '.' . $Year);

        $dataList = array();
        $hasTypeOptions = false;
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionId))
            && ($absenceList = Absence::useService()->getAbsenceAllByDay($startDateMonth, $endDateMonth, null, array(0 => $tblDivisionCourse), $hasTypeOptions, null))
        ) {
            foreach ($absenceList as $item) {
                if (($tblAbsence = Absence::useService()->getAbsenceById($item['AbsenceId']))
                    && ($tblPersonItem = $tblAbsence->getServiceTblPerson())
                ) {
                    $fromDate = $tblAbsence->getFromDateTime();
                    if ($tblAbsence->getToDateTime()) {
                        $toDate = $tblAbsence->getToDateTime();
                        if ($toDate > $fromDate) {
                            $date = $fromDate;
                            while ($date <= $toDate) {
                                self::setAbsenceMonthContent($dataList, $tblPersonItem, $tblAbsence, $date->format('d.m.Y'), $tblDivisionCourse);
                                $date = $date->modify('+1 day');
                            }
                        } elseif ($toDate == $fromDate) {
                            self::setAbsenceMonthContent($dataList, $tblPersonItem, $tblAbsence, $tblAbsence->getFromDate(), $tblDivisionCourse);
                        }
                    } else {
                        self::setAbsenceMonthContent($dataList, $tblPersonItem, $tblAbsence, $tblAbsence->getFromDate(), $tblDivisionCourse);
                    }
                }
            }
        }

        $backgroundColor = '#E0F0FF';
        $minHeightHeader = '44px';
        $minHeightBody = '30px';
        $padding = '3px';

        $hasMonthBefore = true;
        $hasMonthNext = true;

        $headerListStatic['Person'] = (new TableColumn(new Center(new Bold(new PersonGroup() . 'Schüler'))))
            ->setBackgroundColor($backgroundColor)
            ->setVerticalAlign('middle')
            ->setMinHeight($minHeightHeader)
            ->setPadding($padding);

        // Einträge für alle ausgewählten Personen anzeigen
        if ($tblDivisionCourse
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            // Begrenzung auf den Zeitraum des aktuellen Schuljahres
            list($startDateSchoolYear, $endDateSchoolYear) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            /** @var DateTime $startDateSchoolYear */
            if ($startDateSchoolYear && $endDateSchoolYear) {
                $startDateSchoolYear = new DateTime('01.' . $startDateSchoolYear->format('m') . '.' . $startDateSchoolYear->format('Y'));

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
                $tblCompany = false;
                $tblSchoolType = false;
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                    $tblCompany = $tblStudentEducation->getServiceTblCompany();
                    $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                }
                $hasSaturdayLessons = $tblSchoolType && Digital::useService()->getHasSaturdayLessonsBySchoolType($tblSchoolType);

                $bodyListStatic[$tblPerson->getId()]['Person'] = (new TableColumn(new Center(new Bold(
                    new ToolTip(
                        (new Link($tblPerson->getLastFirstName(), ApiAbsence::getEndpoint()))
                            ->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal($tblPerson->getId(), $tblDivisionCourse->getId()))
                        , 'Eine neue Fehlzeit für ' . $tblPerson->getFullName() . ' hinzufügen.'
                    )
                ))))
                    ->setBackgroundColor($backgroundColor)
                    ->setVerticalAlign('middle')
                    ->setMinHeight($minHeightBody)
                    ->setPadding($padding);

                if ($DayCounter) {
                    $Day = 1;
                    while($Day <= $DayCounter){
                        $fetchedDate = new DateTime($Day . '.' . ($Month <= 9 ? '0'.$Month : $Month) . '.' . $Year);
                        $fetchedDateString = $fetchedDate->format('d.m.Y');
                        $DayAtWeek = (new DateTime(($Day < 10 ? '0'.$Day : $Day).'.'.$Month.'.'.$Year))->format('w');

                        if ($hasSaturdayLessons) {
                            $isWeekend = $DayAtWeek == 0;
                        } else {
                            $isWeekend = $DayAtWeek == 0 || $DayAtWeek == 6;
                        }
                        $isHoliday = Term::useService()->getHolidayByDay($tblYear, $fetchedDate, $tblCompany ?: null);

                        if (!isset($headerList['Day' . $Day])) {
                            if (($isCurrentDate = ((int)$currentDate->format('d') == $Day
                                && (int)$currentDate->format('m') == $Month
                                && $currentDate->format('Y') == $Year))
                            ) {
                                // scrollen zum aktuellen Tag
                                $content = '<span id="OrganizerDay" style="color: darkorange;">'
                                    . new Center ($DayName[$DayAtWeek] . new Container($Day))
                                    . '</span>';
                            } else {
                                $content = new Center ($DayName[$DayAtWeek] . new Container($Day));
                            }

                            $columnHeader = (new TableColumn(new Center(
                                $content
                            )))
                                ->setMinHeight($minHeightHeader)
                                ->setPadding($padding);

                            if ($isCurrentDate) {
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
                                ->setOpacity(0.5)
                                ->setPadding($padding);
                        } elseif (isset($dataList[$tblPerson->getId()][$fetchedDateString])) {
                            $columnBody = (new TableColumn(new Center(
                                $dataList[$tblPerson->getId()][$fetchedDateString]['Content']
                            )))
                                ->setBackgroundColor($dataList[$tblPerson->getId()][$fetchedDateString]['BackgroundColor'])
                                ->setPadding($padding);
                        } else {
                            $columnBody = (new TableColumn((new Link(
                                '<div style="height: 28px"><span style="visibility: hidden">'.new Plus().'</span></div>',
                                ApiAbsence::getEndpoint(),
                                null,
                                array(),
                                'Eine neue Fehlzeit für ' . $tblPerson->getFullName() . ' für den '
                                . $fetchedDateString . ' hinzufügen.'))
                                ->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal($tblPerson->getId(), $tblDivisionCourse->getId(), $fetchedDateString))))
                                ->setPadding('0');
                        }

                        $bodyList[$tblPerson->getId()]['Day' . $Day] = $columnBody
                            ->setMinHeight($minHeightBody)
                            ->setVerticalAlign('middle');

                        $Day++;
                    }
                }
            }
        }

        // table Static
        $tableHeadStatic = new TableHead(new TableRow($headerListStatic));
        $rowsStatic = array();
        foreach ($bodyListStatic as $columnListStatic) {
            $rowsStatic[] = new TableRow($columnListStatic);
        }
        $tableBodyStatic = new TableBody($rowsStatic);
        $tableStatic = new Table($tableHeadStatic, $tableBodyStatic, null, false, null, 'TableCustom');

        // table float
        $tableHead = new TableHead(new TableRow($headerList));
        $rows = array();
        foreach ($bodyList as $columnList) {
            $rows[] = new TableRow($columnList);
        }
        $tableBody = new TableBody($rows);
        $table = new Table($tableHead, $tableBody, null, false, null, 'TableCustom');

        $Content = new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn('&nbsp;', 3),
                            new LayoutColumn(
                                $hasMonthBefore
                                    ? new Center(
                                    (new Link(new ChevronLeft(), ApiAbsence::getEndpoint(), null, array(), $MonthName[$MonthBefore] . ' ' . $YearBefore))
                                        ->ajaxPipelineOnClick(ApiAbsence::pipelineChangeMonth($DivisionId, $MonthBefore, $YearBefore))
                                )
                                    : ''
                                , 1),
                            new LayoutColumn(
                                new Center(new Bold($MonthName[$Month] . ' ' . $Year))
                                , 4),
                            new LayoutColumn(
                                $hasMonthNext
                                    ? new Center(
                                    (new Link(new ChevronRight(), ApiAbsence::getEndpoint(), null, array(), $MonthName[$MonthNext].' '.$YearNext))
                                        ->ajaxPipelineOnClick(ApiAbsence::pipelineChangeMonth($DivisionId, $MonthNext, $YearNext))
                                )
                                    : ''
                                , 1),
                            new LayoutColumn(
                                '&nbsp;'
//                                    new PullRight((new Link(' Download', self::getEndpoint(), new Download(), array(), 'Download der Daten vorbereiten'))
//                                        ->ajaxPipelineOnClick(self::pipelineOpenDownloadEdit($DivisionId))
//                                    )
                                , 3)
                        ))))
                        . '<div style="height: 5px;"></div>'
                        , 12)
                ),
                new LayoutRow(
                    new LayoutColumn(
                        '<div style="float: left;">'
                        . $tableStatic
                        .'</div>'
                        . '<div id="OrganizerTable" style="overflow-x: auto;">'
                        . $table
                        . '</div>'
                        . (($Month == (int)$currentDate->format('m') && $Year == (int)$currentDate->format('Y'))
                            ? '<script>
                                tableSelector = "div#OrganizerTable";
                                $(tableSelector).scrollLeft( $("span#OrganizerDay").offset().left - ( $(tableSelector).offset().left + ( $(tableSelector).width() / 2 ) ) )
                            </script>'
                            : ''
                        )
                    )
                )
            ))
        );

        return new Panel(
            new Calendar() . ' Kalender'
            . new PullRight(
                (new Link('Wochenansicht', ApiAbsence::getEndpoint(), null, array(), false, null, AbstractLink::TYPE_WHITE_LINK))
                    ->ajaxPipelineOnClick(ApiAbsence::pipelineChangeWeekForDivision($DivisionId, '', ''))
            ),
            $Content,
            Panel::PANEL_TYPE_PRIMARY
        );
    }

    /**
     * @param $dataList
     * @param TblPerson $tblPerson
     * @param TblAbsence $tblAbsence
     * @param $date
     * @param TblDivisionCourse|null $tblDivisionCourse
     * @param bool $hasToolTip
     */
    private static function setAbsenceMonthContent(
        &$dataList,
        TblPerson $tblPerson,
        TblAbsence $tblAbsence,
        $date,
        ?TblDivisionCourse $tblDivisionCourse = null,
        bool $hasToolTip = true
    ) {
        $lesson = $tblAbsence->getLessonStringByAbsence();
        $type = $tblAbsence->getTypeDisplayShortName();
        $remark = $tblAbsence->getRemark();

        $isWhiteLink = false;

        if ($tblAbsence->getIsOnlineAbsence()) {
            $backgroundColor = 'orange';
            $isWhiteLink = true;
        } elseif (($tblAbsenceType = $tblAbsence->getType())) {
            if ($tblAbsenceType == TblAbsence::VALUE_TYPE_THEORY) {
                $backgroundColor = '#E0F0FF';
            } else {
                $backgroundColor = '#337ab7';
                $isWhiteLink = true;
            }
        } else {
            if ($tblAbsence->getIsCertificateRelevant()) {
                $backgroundColor = '#E0F0FF';
            } else {
                $backgroundColor = '#FFFFFF';
            }
        }

        if ($hasToolTip) {
            $toolTip = ($lesson ? $lesson . ' / ': '') . ($type ? $type . ' / ': '') . $tblAbsence->getStatusDisplayShortName()
                . (($tblPersonStaff = $tblAbsence->getDisplayStaffToolTip()) ? ' - ' . $tblPersonStaff : '')
                . ($remark ? ' - ' . $remark : '');
            $name = $tblAbsence->getStatusDisplayShortName();
        } else {
            $toolTip = $remark ?: false;
            $name = $tblAbsence->getStatusDisplayShortName()
                .($lesson ? ' - ' . $lesson : '') . ($type ? ' - ' . $type : '')
                . (($tblPersonStaff = $tblAbsence->getDisplayStaff()) ? ' - ' . $tblPersonStaff : '');
        }

        $dataList[$tblPerson->getId()][$date]['Content'] = (new Link(
            $name,
            ApiAbsence::getEndpoint(),
            null,
            array(),
            $toolTip,
            null,
            $isWhiteLink
                ? AbstractLink::TYPE_WHITE_LINK
                : ($tblAbsence->getIsCertificateRelevant() ? AbstractLink::TYPE_LINK : AbstractLink::TYPE_MUTED_LINK)
        ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenEditAbsenceModal($tblAbsence->getId(), $tblDivisionCourse ? $tblDivisionCourse->getId() : null));

        $dataList[$tblPerson->getId()][$date]['BackgroundColor'] = $backgroundColor;
    }

    /**
     * @param $DivisionId
     * @param string $WeekNumber
     * @param string $Year
     *
     * @return string
     */
    public function generateOrganizerForDivisionWeekly($DivisionId, string $WeekNumber = '', string $Year = '')
    {
        // Definition
        $currentDate = new DateTime('now');

        if ($WeekNumber == '') {
            $WeekNumber = (int)(new DateTime('now'))->format('W');
        } else {
            $WeekNumber = (int) $WeekNumber;
        }

        if ($Year == '') {
            $Year = (int)$currentDate->format('Y');
        } else {
            $Year = (int) $Year;
        }

        $headerList = array();
        $bodyList = array();

        $organizerBaseData = Absence::useFrontend()->convertOrganizerBaseData();
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
        $hasTypeOptions = false;
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionId))
            && ($absenceList = Absence::useService()->getAbsenceAllByDay($startDate, $endDate, null, array(0 => $tblDivisionCourse), $hasTypeOptions, null))
        ) {
            foreach ($absenceList as $item) {
                if (($tblAbsence = Absence::useService()->getAbsenceById($item['AbsenceId']))
                    && ($tblPersonItem = $tblAbsence->getServiceTblPerson())
                ) {
                    $fromDate = new DateTime($tblAbsence->getFromDate());
                    if ($tblAbsence->getToDate()) {
                        $toDate = new DateTime($tblAbsence->getToDate());
                        if ($toDate > $fromDate) {
                            $date = $fromDate;
                            while ($date <= $toDate) {
                                self::setAbsenceMonthContent($dataList, $tblPersonItem, $tblAbsence, $date->format('d.m.Y'), $tblDivisionCourse, false);
                                $date = $date->modify('+1 day');
                            }
                        } elseif ($toDate == $fromDate) {
                            self::setAbsenceMonthContent($dataList, $tblPersonItem, $tblAbsence, $tblAbsence->getFromDate(), $tblDivisionCourse, false);
                        }
                    } else {
                        self::setAbsenceMonthContent($dataList, $tblPersonItem, $tblAbsence, $tblAbsence->getFromDate(), $tblDivisionCourse, false);
                    }
                }
            }
        }

        $backgroundColor = '#E0F0FF';
        $minHeightHeader = '56px';
        $minHeightBody = '38px';
        $padding = '3px';

        $headerList['Person'] = (new TableColumn(new Center(new Bold(new PersonGroup() . 'Schüler'))))
            ->setBackgroundColor($backgroundColor)
            ->setVerticalAlign('middle')
            ->setMinHeight($minHeightHeader)
            ->setPadding($padding);

        // Kalender-Inhalt erzeugen
        if ($tblDivisionCourse
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {
                $tblCompany = false;
                $tblSchoolType = false;
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                    $tblCompany = $tblStudentEducation->getServiceTblCompany();
                    $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                }
                $hasSaturdayLessons = $tblSchoolType && Digital::useService()->getHasSaturdayLessonsBySchoolType($tblSchoolType);

                $bodyList[$tblPerson->getId()]['Person'] = (new TableColumn(new Center(new Bold(
                    new ToolTip(
                        (new Link($tblPerson->getLastFirstName(), ApiAbsence::getEndpoint()))
                            ->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal($tblPerson->getId(), $tblDivisionCourse->getId()))
                        , 'Eine neue Fehlzeit für ' . $tblPerson->getFullName() . ' hinzufügen.'
                    )
                ))))
                    ->setBackgroundColor($backgroundColor)
                    ->setVerticalAlign('middle')
                    ->setMinHeight($minHeightBody)
                    ->setPadding($padding);
                $startDate = new DateTime(date('d.m.Y', strtotime("$Year-W{$Week}")));

                if ($startDate && $endDate) {
                    while ($startDate <= $endDate) {
                        $DayAtWeek = $startDate->format('w');
                        $Day = (int)$startDate->format('d');
                        $Month = (int)$startDate->format('m');

                        if ($hasSaturdayLessons) {
                            $isWeekend = $DayAtWeek == 0;
                        } else {
                            $isWeekend = $DayAtWeek == 0 || $DayAtWeek == 6;
                        }
                        $isHoliday = Term::useService()->getHolidayByDay($tblYear, $startDate, $tblCompany ?: null);

                        $fetchedDateString = $startDate->format('d.m.Y');

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
                                ->setOpacity(0.5)
                                ->setPadding($padding);
                        } elseif (isset($dataList[$tblPerson->getId()][$fetchedDateString])) {
                            $columnBody = (new TableColumn(new Center(
                                $dataList[$tblPerson->getId()][$fetchedDateString]['Content']
                            )))
                                ->setBackgroundColor($dataList[$tblPerson->getId()][$fetchedDateString]['BackgroundColor'])
                                ->setPadding($padding);
                        } else {
                            $columnBody = (new TableColumn((new Link(
                                '<div style="height: 28px"><span style="visibility: hidden">'.new Plus().'</span></div>',
                                ApiAbsence::getEndpoint(),
                                null,
                                array(),
                                'Eine neue Fehlzeit für ' . $tblPerson->getFullName() . ' für den '
                                . $fetchedDateString . ' hinzufügen.'))
                                ->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal($tblPerson->getId(), $tblDivisionCourse->getId(), $fetchedDateString))))
                                ->setPadding('0');
                        }


                        $bodyList[$tblPerson->getId()]['Day' . $Day] = $columnBody
                            ->setMinHeight($minHeightBody)
                            ->setVerticalAlign('middle')
                            ->setPadding($padding);

                        $startDate->modify('+1 day');
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

//        $startDate = new DateTime(date('d.m.Y', strtotime("$Year-W{$Week}")));

        // Inhalt zusammenbasteln
        $Content = new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn('&nbsp;', 3),
                                new LayoutColumn(
                                    new Center(
                                        (new Link(new ChevronLeft(), ApiAbsence::getEndpoint(), null, array(), 'KW' . $WeekBefore))
                                            ->ajaxPipelineOnClick(ApiAbsence::pipelineChangeWeekForDivision($DivisionId, $WeekBefore, $YearBefore))
                                    )
                                    , 1),
                                new LayoutColumn(
                                    new ToolTip(new Center(new Bold('KW' . $WeekNumber. ' ')), $Year)
                                    , 4),
                                new LayoutColumn(
                                    new Center(
                                        (new Link(new ChevronRight(), ApiAbsence::getEndpoint(), null, array(), 'KW' . $WeekNext))
                                            ->ajaxPipelineOnClick(ApiAbsence::pipelineChangeWeekForDivision($DivisionId, $WeekNext, $YearNext))
                                    )
                                    , 1),
                                new LayoutColumn(
                                    ''
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

        return new Panel(
            new Calendar() . ' Kalender'
            . new PullRight(
                (new Link('Monatsansicht', ApiAbsence::getEndpoint(), null, array(), false, null, AbstractLink::TYPE_WHITE_LINK))
                    ->ajaxPipelineOnClick(ApiAbsence::pipelineChangeMonth($DivisionId, '', ''))
            ),
            $Content,
            Panel::PANEL_TYPE_PRIMARY
        );
    }
}