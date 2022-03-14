<?php

namespace SPHERE\Application\Education\ClassRegister\Digital;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Api\Education\ClassRegister\ApiDigital;
use SPHERE\Application\Education\Certificate\Prepare\View;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Home;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
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
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Frontend\Table\Structure\TableBody;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    const BASE_ROUTE = '/Education/ClassRegister/Digital';

    /**
     * @return Stage
     */
    public function frontendSelectDivision(): Stage
    {
        $hasHeadmasterRight = Access::useService()->hasAuthorization(self::BASE_ROUTE . '/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization(self::BASE_ROUTE . '/Teacher');

        if ($hasHeadmasterRight) {
            if ($hasTeacherRight) {
                return $this->frontendTeacherSelectDivision();
            } else {
                return $this->frontendHeadmasterSelectDivision();
            }
        } else {
            return $this->frontendTeacherSelectDivision();
        }
    }

    /**
     * @param bool $IsAllYears
     * @param bool $IsGroup
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendTeacherSelectDivision(bool $IsAllYears = false, bool $IsGroup = false, $YearId = null): Stage
    {

        $Stage = new Stage('Digitales Klassenbuch', 'Klasse auswählen');
        Digital::useService()->setHeaderButtonList($Stage, View::TEACHER, self::BASE_ROUTE);

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        $yearFilterList = array();
        $buttonList = Digital::useService()->setYearGroupButtonList(self::BASE_ROUTE . '/Teacher',
            $IsAllYears, $IsGroup, $YearId, false, true, $yearFilterList);

        $table = false;
        $divisionTable = array();
        if ($tblPerson) {
            $divisionList = array();

            // Klassenlehrer
            if (($tblDivisionList = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson))) {
                foreach ($tblDivisionList as $tblDivisionTeacher) {
                    if (($tblDivision = $tblDivisionTeacher->getTblDivision())
                        && ($tblYear = $tblDivision->getServiceTblYear())
                    ) {
                        if ($yearFilterList && !isset($yearFilterList[$tblYear->getId()])) {
                            continue;
                        }

                        $divisionList[$tblDivision->getId()] = $tblDivision;
                    }
                }
            }

            // Fachlehrer
            if (($tblSubjectTeacherAllByTeacher = Division::useService()->getSubjectTeacherAllByTeacher($tblPerson))) {
                foreach ($tblSubjectTeacherAllByTeacher as $tblSubjectTeacher) {
                    if (($tblDivisionSubject = $tblSubjectTeacher->getTblDivisionSubject())
                        && ($tblDivisionItem = $tblDivisionSubject->getTblDivision())
                        && ($tblYearItem = $tblDivisionItem->getServiceTblYear())
                    ) {
                        if ($yearFilterList && !isset($yearFilterList[$tblYearItem->getId()])) {
                            continue;
                        }

                        $divisionList[$tblDivisionItem->getId()] = $tblDivisionItem;
                    }
                }
            }

            if ($IsGroup) {
                if (($tblTudorGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TUDOR))
                    && Group::useService()->existsGroupPerson($tblTudorGroup, $tblPerson)
                ) {
                    $isTudor = true;
                } else {
                    $isTudor = false;
                }

                if (($tblGroupAll = Group::useService()->getTudorGroupAll())) {
                    foreach ($tblGroupAll as $tblGroup) {
                        // ist Tudor in Stammgruppe
                        $addGroup = false;
                        if ($isTudor && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                            $addGroup = true;
                        // oder ist Klassenlehrer oder Fachlehrer
                        } else {
                            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                                foreach ($tblPersonList as $tblPersonStudent) {
                                    if (($tblStudent = $tblPersonStudent->getStudent())
                                        && ($tblDivisionMain = $tblStudent->getCurrentMainDivision())
                                        && isset($divisionList[$tblDivisionMain->getId()])
                                    ) {
                                        $addGroup = true;
                                        break;
                                    }
                                }
                            }
                        }

                        if ($addGroup) {
                            $divisionTable[] = array(
                                'Group' => $tblGroup->getName(),
                                'Option' => new Standard(
                                    '', self::BASE_ROUTE . '/LessonContent', new Select(),
                                    array(
                                        'GroupId' => $tblGroup->getId(),
                                        'BasicRoute' => self::BASE_ROUTE . '/Teacher'
                                    ),
                                    'Auswählen'
                                )
                            );
                        }
                    }
                }

                if (empty($divisionTable)) {
                    $table = new Warning('Keine entsprechenden Lehraufträge vorhanden.', new Exclamation());
                } else {
                    $table = new TableData($divisionTable, null, array(
                        'Group' => 'Gruppe',
                        'Option' => ''
                    ), array(
                        'order' => array(
                            array('0', 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'natural', 'targets' => 0),
                            array('orderable' => false, 'width' => '1%', 'targets' => -1)
                        ),
                    ));
                }
            } else {
                foreach ($divisionList as $item) {
                    $divisionTable[] = array(
                        'Year' => $item->getServiceTblYear() ? $item->getServiceTblYear()->getDisplayName() : '',
                        'Type' => $item->getTypeName(),
                        'Division' => $item->getDisplayName(),
                        'Option' => new Standard(
                            '', self::BASE_ROUTE . '/LessonContent', new Select(),
                            array(
                                'DivisionId' => $item->getId(),
                                'BasicRoute' => self::BASE_ROUTE . '/Teacher'
                            ),
                            'Auswählen'
                        )
                    );
                }

                if (empty($divisionTable)) {
                    $table = new Warning('Keine entsprechenden Lehraufträge vorhanden.', new Exclamation());
                } else {
                    $table = new TableData($divisionTable, null, array(
                        'Year' => 'Schuljahr',
                        'Type' => 'Schulart',
                        'Division' => 'Klasse',
                        'Option' => ''
                    ), array(
                        'order' => array(
                            array('0', 'desc'),
                            array('1', 'asc'),
                            array('2', 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'natural', 'targets' => 2),
                            array('orderable' => false, 'width' => '1%', 'targets' => -1)
                        ),
                    ));
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        $table
                            ? new LayoutColumn(array($table))
                            : null
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param bool $IsAllYears
     * @param bool $IsGroup
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendHeadmasterSelectDivision(bool $IsAllYears = false, bool $IsGroup = false, $YearId = null): Stage
    {

        $Stage = new Stage('Digitales Klassenbuch', 'Klasse auswählen');
        Digital::useService()->setHeaderButtonList($Stage, View::HEADMASTER, self::BASE_ROUTE);

        $tblDivisionList = Division::useService()->getDivisionAll();

        $yearFilterList = array();
        $buttonList = Digital::useService()->setYearGroupButtonList(self::BASE_ROUTE . '/Headmaster',
            $IsAllYears, $IsGroup, $YearId, true, true, $yearFilterList);

        $divisionTable = array();
        if ($IsGroup) {
            // tudorGroups
            if (($tblGroupAll = Group::useService()->getTudorGroupAll())) {
                foreach ($tblGroupAll as $tblGroup) {
                    $divisionTable[] = array(
                        'Group' => $tblGroup->getName(),
                        'Option' => new Standard(
                            '', self::BASE_ROUTE . '/LessonContent', new Select(),
                            array(
                                'GroupId' => $tblGroup->getId(),
                                'BasicRoute' => self::BASE_ROUTE . '/Headmaster'
                            ),
                            'Auswählen'
                        )
                    );
                }
            }

            $table = new TableData($divisionTable, null, array(
                'Group' => 'Gruppe',
                'Option' => ''
            ), array(
                'order' => array(
                    array('0', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 0),
                    array('orderable' => false, 'width' => '1%', 'targets' => -1)
                ),
            ));
        } else {
            if ($tblDivisionList) {
                foreach ($tblDivisionList as $tblDivision) {
                    if (($tblYear = $tblDivision->getServiceTblYear())) {
                        // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                        if ($yearFilterList && !isset($yearFilterList[$tblYear->getId()])) {
                            continue;
                        }

                        $divisionTable[] = array(
                            'Year' => $tblYear->getDisplayName(),
                            'Type' => $tblDivision->getTypeName(),
                            'Division' => $tblDivision->getDisplayName(),
                            'Option' => new Standard(
                                '', self::BASE_ROUTE . '/LessonContent', new Select(),
                                array(
                                    'DivisionId' => $tblDivision->getId(),
                                    'BasicRoute' => self::BASE_ROUTE . '/Headmaster'
                                ),
                                'Auswählen'
                            )
                        );
                    }
                }
            }

            $table = new TableData($divisionTable, null, array(
                'Year' => 'Schuljahr',
                'Type' => 'Schulart',
                'Division' => 'Klasse',
                'Option' => ''
            ), array(
                'order' => array(
                    array('0', 'desc'),
                    array('1', 'asc'),
                    array('2', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 2),
                    array('orderable' => false, 'width' => '1%', 'targets' => -1)
                ),
            ));
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        new LayoutColumn($table)
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendLessonContent(
        $DivisionId = null,
        $GroupId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Übersicht');

        $stage->addButton(new Standard(
            'Zurück', $BasicRoute, new ChevronLeft()
        ));

        $tblYear = null;
//        $tblPerson = Account::useService()->getPersonByLogin();
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        // View speichern
        Consumer::useService()->createAccountSetting('LessonContentView', 'Day');

        if ($tblDivision || $tblGroup) {
            $stage->setContent(
                ApiDigital::receiverModal()
                . ApiAbsence::receiverModal()
                . new Layout(new LayoutGroup(array(
                    Digital::useService()->getHeadColumnRow(
                        $tblDivision ?: null, $tblGroup ?: null, $tblYear
                    ),
//                    new LayoutRow(array(
//                        new LayoutColumn(
//                            (new Primary(
//                                new Plus() . ' Unterrichtseinheit hinzufügen',
//                                ApiDigital::getEndpoint()
//                            ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionId, $GroupId))
//                            . (new Primary(
//                                new Plus() . ' Fehlzeit hinzufügen',
//                                ApiAbsence::getEndpoint()
//                            ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal(null, $DivisionId))
//                        )
//                    ))
                )))
//                . new Container('&nbsp;')
                . ApiDigital::receiverBlock($this->loadLessonContentTable($tblDivision ?: null, $tblGroup ?: null), 'LessonContentContent')
            );
        } else {
            return new Danger('Klasse oder Gruppe nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param string $DateString
     * @param string $View
     *
     * @return string
     */
    public function loadLessonContentTable(TblDivision $tblDivision = null, TblGroup $tblGroup = null,
        string $DateString = 'today', string $View = 'Day'): string
    {
        $DivisionId = $tblDivision ? $tblDivision->getId() : null;
        $GroupId = $tblGroup ? $tblGroup->getId() : null;

        $buttons = (new Primary(
            new Plus() . ' Unterrichtseinheit hinzufügen',
            ApiDigital::getEndpoint()
        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionId, $GroupId));

        if ($View == 'Day') {
            $buttons .= (new Primary(
                new Plus() . ' Fehlzeit hinzufügen',
                ApiAbsence::getEndpoint()
            // todo GroupId?
            ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal(null, $DivisionId));
            $content = $this->getDayViewContent($DateString, $tblDivision, $tblGroup);
            $link = (new Link('Wochenansicht', ApiDigital::getEndpoint(), null, array(), false, null, AbstractLink::TYPE_WHITE_LINK))
                ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent($DivisionId, $GroupId, $DateString, 'Week'));
        } else {
            $content =  $this->getWeekViewContent($DateString, $tblDivision, $tblGroup);
            $link = (new Link('Tagesansicht', ApiDigital::getEndpoint(), null, array(), false, null, AbstractLink::TYPE_WHITE_LINK))
                ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent($DivisionId, $GroupId, $DateString, 'Day'));
        }

        return $buttons
            . new Container('&nbsp;')
            . new Panel(
                new Book() . ' Klassenbuch' . new PullRight($link),
                $content,
                Panel::PANEL_TYPE_PRIMARY
            );
    }

    /**
     * @param string $DateString
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return string
     */
    private function getDayViewContent(
        string $DateString,
        ?TblDivision $tblDivision,
        ?TblGroup $tblGroup
    ): string {
        $DivisionId = $tblDivision ? $tblDivision->getId() : null;
        $GroupId = $tblGroup ? $tblGroup->getId() : null;
        $date = new DateTime($DateString);
        $nextDate = new DateTime($DateString);
        $nextDate = $nextDate->add(new DateInterval('P1D'));
        $previewsDate = new DateTime($DateString);
        $previewsDate = $previewsDate->sub(new DateInterval('P1D'));
        $dayAtWeek = $date->format('w');
        $dayName = array(
            '0' => 'Sonntag',
            '1' => 'Montag',
            '2' => 'Dienstag',
            '3' => 'Mittwoch',
            '4' => 'Donnerstag',
            '5' => 'Freitag',
            '6' => 'Samstag',
        );

        $headerList['Lesson'] = $this->getTableHeadColumn(new ToolTip('UE', 'Unterrichtseinheit'), '30px');
        $headerList['Subject'] = $this->getTableHeadColumn('Fach', '50px');
        $headerList['Teacher'] = $this->getTableHeadColumn('Lehrer', '50px');
        $headerList['Content'] = $this->getTableHeadColumn('Thema / Inhalt');
        $headerList['Homework'] = $this->getTableHeadColumn('Hausaufgaben');
        $headerList['Absence'] = $this->getTableHeadColumn('Fehlzeiten');

        $maxLesson = 6;
        $bodyList = array();
        $divisionList = $tblDivision ? array('0' => $tblDivision) : array();
        $groupList = $tblGroup ? array('0' => $tblGroup) : array();
        $absenceContent = array();
        if (($AbsenceList = Absence::useService()->getAbsenceAllByDay($date, null, null, $divisionList, $groupList,
            $hasTypeOption, null)
        )) {
            foreach ($AbsenceList as $Absence) {
                if (($tblAbsence = Absence::useService()->getAbsenceById($Absence['AbsenceId']))) {
                    $lesson = $tblAbsence->getLessonStringByAbsence();
                    $type = $tblAbsence->getTypeDisplayShortName();
                    $remark = $tblAbsence->getRemark();
                    $toolTip = ($lesson ? $lesson . ' / ' : '') . ($type ? $type . ' / ' : '') . $tblAbsence->getStatusDisplayShortName()
                        . (($tblPersonStaff = $tblAbsence->getDisplayStaff()) ? ' - ' . $tblPersonStaff : '')
                        . ($remark ? ' - ' . $remark : '');

                    $item = (new Link(
                        $Absence['Person'],
                        ApiAbsence::getEndpoint(),
                        null,
                        array(),
                        $toolTip,
                        null,
                        $tblAbsence->getIsCertificateRelevant() ? AbstractLink::TYPE_LINK : AbstractLink::TYPE_MUTED_LINK
                    ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenEditAbsenceModal($tblAbsence->getId()));

                    if (($tblAbsenceLessonList = Absence::useService()->getAbsenceLessonAllByAbsence($tblAbsence))) {
                        foreach ($tblAbsenceLessonList as $tblAbsenceLesson) {
                            if (!isset($absenceContent[$tblAbsenceLesson->getLesson()])) {
                                $absenceContent[$tblAbsenceLesson->getLesson()] = array('0' => $item);
                            } else {
                                $absenceContent[$tblAbsenceLesson->getLesson()][] = $item;
                            }
                        }
                    } else {
                        if (!isset($absenceContent['Day'])) {
                            $absenceContent['Day'] = array('0' => $item);
                        } else {
                            $absenceContent['Day'][] = $item;
                        }
                    }
                }
            }

            if (isset($absenceContent['Day'])) {
                $bodyList[0] = array(
                    'Lesson' => new ToolTip(new Bold('GT'), 'Ganztägig'),
                    'Subject' => '',
                    'Teacher' => '',
                    'Content' => '',
                    'Homework' => '',
                    'Absence' => implode(' - ', $absenceContent['Day'])
                );
            }
        }

        if (($tblLessonContentList = Digital::useService()->getLessonContentAllByDate($date, $tblDivision ?: null,
            $tblGroup ?: null))) {
            foreach ($tblLessonContentList as $tblLessonContent) {
                $teacher = '';
                if (($tblPerson = $tblLessonContent->getServiceTblPerson())) {
                    if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))
                        && ($acronym = $tblTeacher->getAcronym())
                    ) {
                        $teacher = $acronym;
                    } else {
                        if (strlen($tblPerson->getLastName()) > 5) {
                            $teacher = substr($tblPerson->getLastName(), 0, 5) . '.';
                        }
                    }
                    $teacher = new ToolTip($teacher, $tblPerson->getFullName());
                }

                $lesson = $tblLessonContent->getLesson();
                if ($lesson > $maxLesson) {
                    $maxLesson = $lesson;
                }
                // es können mehrere Einträge zur selben Unterrichtseinheit vorhanden sein
                $index = $lesson * 10;
                if (isset($bodyList[$index])) {
                    $index++;
                }

                $lessonContentId = $tblLessonContent->getId();
                $bodyList[$index] = array(
                    'Lesson' => $this->getLessonsEditLink(new Bold(new Center($lesson)), $lessonContentId, $lesson),
                    'Subject' => $this->getLessonsEditLink(
                        ($tblSubject = $tblLessonContent->getServiceTblSubject()) ? $tblSubject->getAcronym() : '',
                        $lessonContentId, $lesson),
                    'Teacher' => $this->getLessonsEditLink($teacher, $lessonContentId, $lesson),
                    'Content' => $this->getLessonsEditLink($tblLessonContent->getContent(), $lessonContentId, $lesson),
                    'Homework' => $this->getLessonsEditLink($tblLessonContent->getHomework(), $lessonContentId,
                        $lesson),

                    'Absence' => isset($absenceContent[$lesson]) ? implode(' - ', $absenceContent[$lesson]) : ''
                );
            }
        }

        // leere Einträge bis $maxLesson auffüllen
        for ($i = 1; $i <= $maxLesson; $i++) {
            if (!isset($bodyList[$i * 10])) {
                $linkLesson = (new Link(
                    new Center($i),
                    ApiDigital::getEndpoint(),
                    null,
                    array(),
                    $i . '. Unterrichtseinheit hinzufügen',
                    null,
                    AbstractLink::TYPE_MUTED_LINK
                ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionId, $GroupId,
                    $date->format('d.m.Y'), $i));

                $link = (new Link(
                    '<div style="height: 22px"></div>',
                    ApiDigital::getEndpoint(),
                    null,
                    array(),
                    $i . '. Unterrichtseinheit hinzufügen'
                ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionId, $GroupId,
                    $date->format('d.m.Y'), $i));

                $bodyList[$i * 10] = array(
                    'Lesson' => $linkLesson,
                    'Subject' => $link,
                    'Teacher' => $link,
                    'Content' => $link,
                    'Homework' => $link,
                    'Absence' => isset($absenceContent[$i]) ? implode(' - ', $absenceContent[$i]) : ''
                );
            }
        }
        ksort($bodyList);

        $tableHead = new TableHead(new TableRow($headerList));
        $rows = array();
        foreach ($bodyList as $key => $columnList) {
//            $rows[] = new TableRow($columnList);

            $columns = array();
            foreach ($columnList as $column) {
                $columns[] = (new TableColumn($column))
                    ->setVerticalAlign('middle')
                    ->setMinHeight('30px')
                    ->setPadding('3')
                    ->setBackgroundColor($key == 0 ? '#E0F0FF' : '');
            }
            $rows[] = new TableRow($columns);
        }
        $tableBody = new TableBody($rows);
        $table = new Table($tableHead, $tableBody, null, false, null, 'TableCustom');

        $content = new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn('&nbsp;', 3),
                                new LayoutColumn(
                                    new Center(
                                        (new Link(new ChevronLeft(), ApiDigital::getEndpoint(), null, array(),
                                            $dayName[$previewsDate->format('w')] . ', den ' . $previewsDate->format('d.m.Y')))
                                            ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent(
                                                $DivisionId, $GroupId, $previewsDate->format('d.m.Y'), 'Day'))
                                    )
                                    , 1),
                                new LayoutColumn(
                                    new Center(new Bold($dayName[$dayAtWeek] . ', den ' . $date->format('d.m.Y')))
                                    , 4),
                                new LayoutColumn(
                                    new Center(
                                        (new Link(new ChevronRight(), ApiDigital::getEndpoint(), null, array(),
                                            $dayName[$nextDate->format('w')] . ', den ' . $nextDate->format('d.m.Y')))
                                            ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent(
                                                $DivisionId, $GroupId, $nextDate->format('d.m.Y'), 'Day'))
                                    )
                                    , 1),
                                new LayoutColumn('&nbsp;', 3),
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

        return $content . ' ';
    }

    /**
     * @param string $DateString
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return string
     */
    private function getWeekViewContent(
        string $DateString,
        ?TblDivision $tblDivision,
        ?TblGroup $tblGroup
    ): string {
        $DivisionId = $tblDivision ? $tblDivision->getId() : null;
        $GroupId = $tblGroup ? $tblGroup->getId() : null;
        $date = new DateTime($DateString);

        $currentWeek =  (int) $date->format('W');

        $nextWeekDate = new DateTime($DateString);
        $nextWeekDate = $nextWeekDate->add(new DateInterval('P7D'));
        $nextWeek = $nextWeekDate->format('W');

        $previewsWeekDate = new DateTime($DateString);
        $previewsWeekDate = $previewsWeekDate->sub(new DateInterval('P7D'));
        $previewsWeek = $previewsWeekDate->format('W');

        $dayName = array(
            '0' => 'Sonntag',
            '1' => 'Montag',
            '2' => 'Dienstag',
            '3' => 'Mittwoch',
            '4' => 'Donnerstag',
            '5' => 'Freitag',
            '6' => 'Samstag',
        );

        $maxLesson = 6;
        $headerList = array();
        $headerList['Lesson'] = $this->getTableHeadColumn(new ToolTip('UE', 'Unterrichtseinheit'), '5%');
        $bodyList = array();
        $dateStringList = array();

        $year = $date->format('Y');
        $week = str_pad($currentWeek, 2, '0', STR_PAD_LEFT);
        $startDate  = new DateTime(date('d.m.Y', strtotime("$year-W{$week}")));

        for ($day = 1; $day < 6; $day++) {
            $headerList[$day] = $this->getTableHeadColumn($dayName[$day], '19%');
            $dateStringList[$day] = $startDate->format('d.m.Y');
            if (($tblLessonContentList = Digital::useService()->getLessonContentAllByDate($startDate, $tblDivision ?: null,
                $tblGroup ?: null))
            ) {
                foreach ($tblLessonContentList as $tblLessonContent) {
                    $teacher = '';
                    if (($tblPerson = $tblLessonContent->getServiceTblPerson())) {
                        if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))
                            && ($acronym = $tblTeacher->getAcronym())
                        ) {
                            $teacher = $acronym;
                        } else {
                            if (strlen($tblPerson->getLastName()) > 5) {
                                $teacher = substr($tblPerson->getLastName(), 0, 5) . '.';
                            }
                        }
                    }

                    $lesson = $tblLessonContent->getLesson();
                    if ($lesson > $maxLesson) {
                        $maxLesson = $lesson;
                    }

                    $item = $this->getLessonsEditLink(
                        (($tblSubject = $tblLessonContent->getServiceTblSubject()) ? $tblSubject->getDisplayName() : '')
                        . ($teacher ? ' (' . $teacher . ')' : '')
                        . ($tblLessonContent->getContent() ? new Container($tblLessonContent->getContent())  : '')
                        . ($tblLessonContent->getHomework() ? new Container($tblLessonContent->getHomework())  : '')
                    , $tblLessonContent->getId(), $lesson);

                    if (isset($bodyList[$lesson][$day])) {
                        $bodyList[$lesson][$day] .= new Container(new Center('--------------------')) . new Container($item);
                    } else {
                        $bodyList[$lesson][$day] = $item;
                    }
                }
            }

            $startDate->modify('+1  day');
        }

        $tableHead = new TableHead(new TableRow($headerList));
        $rows = array();
        for ($i = 1; $i <= $maxLesson; $i++) {
            $columns = array();
            $columns[] = (new TableColumn(new Center($i)))
                ->setVerticalAlign('middle')
                ->setMinHeight('30px')
                ->setPadding('3');
            for ($j = 1; $j< 6; $j++ ) {
                if (isset($bodyList[$i][$j])) {
                    $cell = $bodyList[$i][$j];
                } else {
                    $cell = (new Link(
                        '<div style="height: 22px"></div>',
                        ApiDigital::getEndpoint(),
                        null,
                        array(),
                        $i . '. Unterrichtseinheit hinzufügen'
                    ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionId, $GroupId,
                        $dateStringList[$j], $i));
                }
                $columns[] = (new TableColumn($cell))
                    ->setVerticalAlign('middle')
                    ->setMinHeight('30px')
                    ->setPadding('3');
            }
            $rows[] = new TableRow($columns);
        }
        $tableBody = new TableBody($rows);
        $table = new Table($tableHead, $tableBody, null, false, null, 'TableCustom');

        $content = new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn('&nbsp;', 3),
                                new LayoutColumn(
                                    new Center(
                                        (new Link(new ChevronLeft(), ApiDigital::getEndpoint(), null, array(), 'KW' . $previewsWeek))
                                            ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent(
                                                $DivisionId, $GroupId, $previewsWeekDate->format('d.m.Y'), 'Week'))
                                    )
                                    , 1),
                                new LayoutColumn(
                                    new Center(new Bold('KW' . $currentWeek. ' '))
                                    , 4),
                                new LayoutColumn(
                                    new Center(
                                        (new Link(new ChevronRight(), ApiDigital::getEndpoint(), null, array(), 'KW' . $nextWeek))
                                            ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent(
                                                $DivisionId, $GroupId, $nextWeekDate->format('d.m.Y'), 'Week'))
                                    )
                                    , 1),
                                new LayoutColumn('&nbsp;', 3),
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

        return $content . ' ';
    }

    /**
     * @param string $name
     * @param int $LessonContentId
     * @param int $Lesson
     *
     * @return Link
     */
    private function getLessonsEditLink(string $name, int $LessonContentId, int $Lesson): Link
    {
        return (new Link(
            $name  == '' ? '<div style="height: 22px"></div>' : $name,
            ApiDigital::getEndpoint(),
            null,
            array(),
            $Lesson . '. Unterrichtseinheit bearbeiten'
        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenEditLessonContentModal($LessonContentId));
    }

    /**
     * @param string $name
     * @param string $width
     *
     * @return TableColumn
     */
    private function getTableHeadColumn(string $name, string $width = 'auto'): TableColumn
    {
        $backgroundColor = '#E0F0FF';
        $size = 1;
        return (new TableColumn(new Center(new Bold($name)), $size, $width))
            ->setBackgroundColor($backgroundColor)
            ->setVerticalAlign('middle')
            ->setMinHeight('35px');
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param null $LessonContentId
     * @param bool $setPost
     * @param string|null $Date
     * @param string|null $Lesson
     *
     * @return Form
     */
    public function formLessonContent(TblDivision $tblDivision = null, TblGroup $tblGroup = null, $LessonContentId = null,
        bool $setPost = false, string $Date = null, string $Lesson = null): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        if ($setPost && $LessonContentId
            && ($tblLessonContent = Digital::useService()->getLessonContentById($LessonContentId))
        ) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Date'] = $tblLessonContent->getDate();
            $Global->POST['Data']['Lesson'] = $tblLessonContent->getLesson();
            $Global->POST['Data']['serviceTblSubject'] =
                ($tblSubject = $tblLessonContent->getServiceTblSubject()) ? $tblSubject->getId() : 0;
            $Global->POST['Data']['serviceTblPerson'] =
                ($tblPerson = $tblLessonContent->getServiceTblPerson()) ? $tblPerson->getId() : 0;
            $Global->POST['Data']['Content'] = $tblLessonContent->getContent();
            $Global->POST['Data']['Homework'] = $tblLessonContent->getHomework();

            $Global->savePost();
        } elseif ($Date || $Lesson) {
            // hinzufügen mit Startwerten
            $Global = $this->getGlobal();
            $Global->POST['Data']['Date'] = $Date;
            $Global->POST['Data']['Lesson'] = $Lesson;
            $Global->savePost();
        }

        if ($LessonContentId) {
            $saveButton = (new Primary('Speichern', ApiDigital::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDigital::pipelineEditLessonContentSave($LessonContentId));
        } else {
            // todo befüllen bei neuen Einträge aus dem importierten Stundenplan
            // todo eingeloggten Lehrer vorsetzen falls er das Fach unterrichtet

            $saveButton = (new Primary('Speichern', ApiDigital::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDigital::pipelineCreateLessonContentSave(
                    $tblDivision ? $tblDivision->getId() : null,
                    $tblGroup ? $tblGroup->getId() : null
                ));
        }
        $buttonList[] = $saveButton;

        // todo Gruppen auswahl?

        // todo Lehrer vorauswahl -> eventuell dynamische abhängig
        $tblTeacherList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('TEACHER'));

        // todo Fächer eingrenzen
        $tblSubjectList = Subject::useService()->getSubjectAll();

        for ($i = 0; $i < 13; $i++) {
            $lessons[] = new SelectBoxItem($i, $i . '. Unterrichtseinheit');
        }

        // Unterrichteinheit löchen
        if ($LessonContentId) {
            $buttonList[] = (new \SPHERE\Common\Frontend\Link\Repository\Danger(
                'Löschen',
                ApiDigital::getEndpoint(),
                new Remove(),
                array(),
                false
            ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenDeleteLessonContentModal($LessonContentId));
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired()
                        , 6),
                    new FormColumn(
                        (new SelectBox('Data[Lesson]', 'Unterrichtseinheit', array('{{ Name }}' => $lessons)))->setRequired()
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('Data[serviceTblSubject]', 'Fach', array('{{ Acronym }} - {{ Name }}' => $tblSubjectList))
                        , 6),
                    new FormColumn(
                        new SelectBox('Data[serviceTblPerson]', 'Lehrer', array('{{ FullName }}' => $tblTeacherList))
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Content]', 'Thema/Inhalt', 'Thema/Inhalt', new Edit())
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Homework]', 'Hausaufgaben', 'Hausaufgaben', new Home())
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        $buttonList
                    )
                )),
            ))
        ))->disableSubmitAction();
    }
}