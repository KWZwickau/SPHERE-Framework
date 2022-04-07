<?php

namespace SPHERE\Application\Education\ClassRegister\Digital;

use DateInterval;
use DateTime;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Api\Education\ClassRegister\ApiDigital;
use SPHERE\Application\Education\Certificate\Prepare\View;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
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
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Home;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
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
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

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
                            array('3', 'asc'),
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
                    array('3', 'asc'),
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
        $stage = new Stage('Digitales Klassenbuch', 'Klassentagebuch');

        $stage->addButton(new Standard(
            'Zurück', $BasicRoute, new ChevronLeft()
        ));

        $tblYear = null;
        $tblPerson = Account::useService()->getPersonByLogin();
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        // Auswahl der SEKII-Kurshefte
        if ($tblDivision
            && ($tblLevel = $tblDivision->getTblLevel())
            && ($tblSchoolType = $tblLevel->getServiceTblType())
            && (($tblSchoolType->getShortName() == 'Gy' && preg_match('!(11|12)!is', $tblLevel->getName()))
                || ($tblSchoolType->getShortName() == 'BGy' && preg_match('!(12|13)!is', $tblLevel->getName())))
        ) {
            // Klassenlehrer sieht alle Kurshefte
            if ($tblPerson && (Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblDivision, $tblPerson))) {
                $isTeacher = false;
            } else {
                // Fachlehrer
                $isTeacher = strpos($BasicRoute, 'Teacher');
            }

            $subjectGroupList = array();
            if (($tblDivisionSubjectAllByDivision = Division::useService()->getDivisionSubjectByDivision($tblDivision))
                && $tblPerson
            ) {
                foreach ($tblDivisionSubjectAllByDivision as $tblDivisionSubject) {
                    if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())
                        && ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
                        && $tblDivisionSubject->getHasGrading()
                    ) {
                        // Fachlehrer benötigt einen Lehrauftrag
                        if ($isTeacher && !Division::useService()->existsSubjectTeacher($tblPerson, $tblDivisionSubject)) {
                            continue;
                        }

                        $subjectGroupList[] = array(
                            'Subject' => $tblSubject->getDisplayName(),
                            'SubjectGroup' => $tblSubjectGroup->getName(),
                            'Option' => new Standard(
                                '', self::BASE_ROUTE . '/CourseContent', new Select(),
                                array(
                                    'DivisionId' => $tblDivision->getId(),
                                    'SubjectId' => $tblSubject->getId(),
                                    'SubjectGroupId' => $tblSubjectGroup->getId(),
                                    'BasicRoute' => $BasicRoute
                                ),
                                'Auswählen'
                            )
                        );
                    }
                }
            }

            $stage->setContent(
                new Layout(new LayoutGroup(array(
                    Digital::useService()->getHeadLayoutRow($tblDivision, null, $tblYear),
                    Digital::useService()->getHeadButtonListLayoutRow($tblDivision, $tblGroup ?: null,
                        '/Education/ClassRegister/Digital/LessonContent', $BasicRoute)
                )))
                . new Container('&nbsp;')
                . new Panel(
                    'SEKII-Kurshefte',
                    new TableData(
                        $subjectGroupList,
                        null,
                        array(
                            'Subject' => 'Fach',
                            'SubjectGroup' => 'Fach-Gruppe',
                            'Option' => ''
                        ),
                        array(
                            'order' => array(
                                array('0', 'asc'),
                                array('1', 'asc'),
                            ),
                            'columnDefs' => array(
                                array('type' => 'natural', 'targets' => 2),
                                array('orderable' => false, 'width' => '1%', 'targets' => -1)
                            ),
                        )
                    ),
                    Panel::PANEL_TYPE_PRIMARY
                )
            );

        // Klassenbuch Ansicht
        } elseif ($tblDivision || $tblGroup) {
            // View speichern
            Consumer::useService()->createAccountSetting('LessonContentView', 'Day');

            $stage->setContent(
                ApiDigital::receiverModal()
                . ApiAbsence::receiverModal()
                . new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow(
                            $tblDivision ?: null, $tblGroup ?: null, $tblYear
                        ),
                        Digital::useService()->getHeadButtonListLayoutRow($tblDivision ?: null, $tblGroup ?: null,
                            '/Education/ClassRegister/Digital/LessonContent', $BasicRoute)
                    )),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        ApiDigital::receiverBlock($this->loadLessonContentTable($tblDivision ?: null, $tblGroup ?: null), 'LessonContentContent')
                    )), new Title(new Book() . ' Klassentagebuch')),
                ))
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
            new Plus() . ' Thema/Hausaufgaben hinzufügen',
            ApiDigital::getEndpoint()
        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionId, $GroupId));

        if ($tblDivision) {
            $Type = 'Division';
            $TypeId = $DivisionId;
        } elseif ($tblGroup) {
            $Type = 'Group';
            $TypeId = $tblGroup->getId();
        } else {
            $Type = null;
            $TypeId = null;
        }

        $Date = $DateString == 'today' ? (new DateTime('today'))->format('d.m.Y') : $DateString;

        if ($View == 'Day') {
            $buttons .= (new Primary(
                new Plus() . ' Fehlzeit hinzufügen',
                ApiAbsence::getEndpoint()
            ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal(null, null, $Date, $Type, $TypeId));

            $content = $this->getDayViewContent($DateString, $tblDivision, $tblGroup);
            $link = (new Link('Wochenansicht', ApiDigital::getEndpoint(), null, array(), false, null, AbstractLink::TYPE_WHITE_LINK))
                ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent($DivisionId, $GroupId, $DateString, 'Week'));
        } else {
            $content =  $this->getWeekViewContent($DateString, $tblDivision, $tblGroup);
            $link = (new Link('Tagesansicht', ApiDigital::getEndpoint(), null, array(), false, null, AbstractLink::TYPE_WHITE_LINK))
                ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent($DivisionId, $GroupId, $DateString, 'Day'));
        }

        $form = (new Form(new FormGroup(new FormRow(array(
            new FormColumn(
                new PullRight(new DatePicker('Data[Date]', '', '', new Calendar()))
                , 7),
            new FormColumn(
                new PullRight((new Primary('Datum auswählen', '', new Select()))->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent(
                    $DivisionId, $GroupId, $DateString, $View
                )))
                , 5)
        )))));

        $layout = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn($buttons, $View == 'Day' ? 7 : 8),
                new LayoutColumn($form, $View == 'Day' ? 5 : 4)
            ))))
            . new Container('&nbsp;')
            . new Panel(
                new Book() . ' Klassenbuch' . new PullRight($link),
                $content,
                Panel::PANEL_TYPE_PRIMARY
            );

        if ($View == 'Day') {
            $layout = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn($this->getStudentPanel($tblDivision, $tblGroup), 2),
                new LayoutColumn($layout, 10),
            ))));
        }

        return $layout;
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
        $dayAtWeek = $date->format('w');
        $addDays = 1;
        $subDays = 1;
        // nur zwischen Wochentagen springen
        switch ($dayAtWeek) {
            case 0: $subDays = 2; break;
            case 1: $subDays = 3; break;
            case 5: $addDays = 3; break;
            case 6: $addDays = 2; break;
        }
        $nextDate = new DateTime($DateString);
        $nextDate = $nextDate->add(new DateInterval('P'. $addDays . 'D'));
        $previewsDate = new DateTime($DateString);
        $previewsDate = $previewsDate->sub(new DateInterval('P' . $subDays . 'D'));
        $dayName = array(
            '0' => 'Sonntag',
            '1' => 'Montag',
            '2' => 'Dienstag',
            '3' => 'Mittwoch',
            '4' => 'Donnerstag',
            '5' => 'Freitag',
            '6' => 'Samstag',
        );

        if ($tblDivision) {
            $tblYear = $tblDivision->getServiceTblYear();
            if ($tblDivision->getServiceTblCompany()) {
                $tblCompanyList[] = $tblDivision->getServiceTblCompany();
            } else {
                $tblCompanyList = array();
            }
            $Type = 'Division';
            $TypeId = $DivisionId;
        } elseif ($tblGroup) {
            $tblYear = $tblGroup->getCurrentYear();
            $tblCompanyList = $tblGroup->getCurrentCompanyList();
            $Type = 'Group';
            $TypeId = $tblGroup->getId();
        } else {
            $tblYear = false;
            $tblCompanyList = array();
            $Type = null;
            $TypeId = null;
        }
        // Ferien, Feiertage
        $isHoliday = false;
        if ($tblYear) {
            if ($tblCompanyList) {
                foreach ($tblCompanyList as $tblCompany) {
                    if (($isHoliday = Term::useService()->getHolidayByDay($tblYear, $date, $tblCompany))) {
                        break;
                    }
                }
            } else {
                $isHoliday = Term::useService()->getHolidayByDay($tblYear, $date, null);
            }

            // Prüfung ob das Datum innerhalb des Schuljahres liegt.
            list($startDateSchoolYear, $endDateSchoolYear) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            if ($startDateSchoolYear && $endDateSchoolYear) {
                if ($date < $startDateSchoolYear || $date > $endDateSchoolYear) {
                    return new Warning('Das ausgewählte Datum: ' . $DateString . ' befindet sich außerhalb des Schuljahres.', new Exclamation());
                }
                if ($previewsDate < $startDateSchoolYear) {
                    $previewsDate = false;
                }
                if ($nextDate > $endDateSchoolYear) {
                    $nextDate = false;
                }
            } else {
                return new Warning('Das Schuljahr besitzt keinen Zeitraum', new Exclamation());
            }
        } else {
            return new Warning('Kein Schuljahr gefunden', new Exclamation());
        }
        // aktueller Tag
        $isCurrentDay = (new DateTime('today'))->format('d.m.Y') ==  $date->format('d.m.Y');

        $headerList['Lesson'] = $this->getTableHeadColumn(new ToolTip('UE', 'Unterrichtseinheit'), '30px');
        $headerList['Subject'] = $this->getTableHeadColumn('Fach', '80px');
        $headerList['Room'] = $this->getTableHeadColumn('Raum', '50px');
        $headerList['Teacher'] = $this->getTableHeadColumn('Lehrer', '50px');
        $headerList['Content'] = $this->getTableHeadColumn('Thema');
        $headerList['Homework'] = $this->getTableHeadColumn('Hausaufgaben');
        $headerList['Absence'] = $this->getTableHeadColumn('Fehlzeiten');

        $maxLesson = 10;
        $bodyList = array();
        $bodyBackgroundList = array();
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
                    ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenEditAbsenceModal($tblAbsence->getId(), $Type, $TypeId));

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
                $bodyList[-1] = array(
                    'Lesson' => new ToolTip(new Bold('GT'), 'Ganztägig'),
                    'Subject' => '',
                    'Room' => '',
                    'Teacher' => '',
                    'Content' => '',
                    'Homework' => '',
                    'Absence' => implode(' - ', $absenceContent['Day'])
                );
            }

            if (isset($absenceContent[0])) {
                $bodyList[0] = array(
                    'Lesson' => new ToolTip(new Center(new Bold('0')), '0. Unterrichtseinheit'),
                    'Subject' => '',
                    'Room' => '',
                    'Teacher' => '',
                    'Content' => '',
                    'Homework' => '',
                    'Absence' => implode(' - ', $absenceContent[0])
                );
            }
        }

        if (($tblLessonContentList = Digital::useService()->getLessonContentAllByDate($date, $tblDivision ?: null,
            $tblGroup ?: null))) {
            foreach ($tblLessonContentList as $tblLessonContent) {
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
                    'Subject' => $this->getLessonsEditLink($tblLessonContent->getDisplaySubject(true), $lessonContentId, $lesson),
                    'Room' => $this->getLessonsEditLink($tblLessonContent->getRoom(), $lessonContentId, $lesson),
                    'Teacher' => $this->getLessonsEditLink($tblLessonContent->getTeacherString(), $lessonContentId, $lesson),
                    'Content' => $this->getLessonsEditLink($tblLessonContent->getContent(), $lessonContentId, $lesson),
                    'Homework' => $this->getLessonsEditLink($tblLessonContent->getHomework(), $lessonContentId, $lesson),

                    'Absence' => isset($absenceContent[$lesson]) ? implode(' - ', $absenceContent[$lesson]) : ''
                );

                $bodyBackgroundList[$index] = true;
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
                    $i . '. Thema/Hausaufgaben hinzufügen',
                    null,
                    AbstractLink::TYPE_MUTED_LINK
                ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionId, $GroupId,
                    $date->format('d.m.Y'), $i));

                $link = (new Link(
                    '<div style="height: 22px"></div>',
                    ApiDigital::getEndpoint(),
                    null,
                    array(),
                    $i . '. Thema/Hausaufgaben hinzufügen'
                ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionId, $GroupId,
                    $date->format('d.m.Y'), $i));

                $bodyList[$i * 10] = array(
                    'Lesson' => $linkLesson,
                    'Subject' => $link,
                    'Room' => $link,
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
            $count = 0;
            foreach ($columnList as $column) {
                $columns[] = (new TableColumn($column))
                    ->setVerticalAlign('middle')
                    ->setMinHeight('30px')
                    ->setPadding('3')
                    ->setBackgroundColor($key == -1 || $key == 0 || (isset($bodyBackgroundList[$key]) && $count == 0) ? '#E0F0FF' : '');
                $count++;
            }
            $rows[] = new TableRow($columns);
        }
        $tableBody = new TableBody($rows);
        $table = new Table($tableHead, $tableBody, null, false, null, 'TableCustom');

        $dayText = new Bold($dayName[$dayAtWeek] . ', den ' . $date->format('d.m.Y'));
        if ($isHoliday) {
            $dayText = $this->getTextColor($dayText, 'lightgray');
        } elseif ($isCurrentDay) {
            $dayText = $this->getTextColor($dayText, 'darkorange');
        }

        $content = new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn('&nbsp;', 3),
                                new LayoutColumn(
                                    new Center(
                                        $previewsDate
                                            ? (new Link(new ChevronLeft(), ApiDigital::getEndpoint(), null, array(),
                                                $dayName[$previewsDate->format('w')] . ', den ' . $previewsDate->format('d.m.Y')))
                                                ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent(
                                                    $DivisionId, $GroupId, $previewsDate->format('d.m.Y'), 'Day'))
                                            : ''
                                    )
                                    , 1),
                                new LayoutColumn(
                                    new Center($dayText)
                                    , 4),
                                new LayoutColumn(
                                    new Center(
                                        $nextDate
                                            ? (new Link(new ChevronRight(), ApiDigital::getEndpoint(), null, array(),
                                                $dayName[$nextDate->format('w')] . ', den ' . $nextDate->format('d.m.Y')))
                                                ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent(
                                                    $DivisionId, $GroupId, $nextDate->format('d.m.Y'), 'Day'))
                                            : ''
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

        return $content . Digital::useService()->getCanceledSubjectOverview($date, $tblDivision, $tblGroup) . ' ';
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

        if ($tblDivision) {
            $tblYear = $tblDivision->getServiceTblYear();
            if ($tblDivision->getServiceTblCompany()) {
                $tblCompanyList[] = $tblDivision->getServiceTblCompany();
            } else {
                $tblCompanyList = array();
            }
        } elseif ($tblGroup) {
            $tblYear = $tblGroup->getCurrentYear();
            $tblCompanyList = $tblGroup->getCurrentCompanyList();
        } else {
            $tblYear = false;
            $tblCompanyList = array();
        }

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

        $maxLesson = 10;
        $headerList = array();
        $headerList['Lesson'] = $this->getTableHeadColumn(new ToolTip('UE', 'Unterrichtseinheit'), '5%');
        $bodyList = array();
        $dateStringList = array();
        $holidayList = array();

        $year = $date->format('Y');
        $week = str_pad($currentWeek, 2, '0', STR_PAD_LEFT);
        $startDate  = new DateTime(date('d.m.Y', strtotime("$year-W{$week}")));

        // Prüfung ob das Datum innerhalb des Schuljahres liegt.
        if ($tblYear) {
            list($startDateSchoolYear, $endDateSchoolYear) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            if ($startDateSchoolYear && $endDateSchoolYear) {
                if ($date < $startDateSchoolYear || $date > $endDateSchoolYear) {
                    return new Warning('Das ausgewählte Datum: ' . $DateString . ' befindet sich außerhalb des Schuljahres.', new Exclamation());
                }
                if ($previewsWeekDate < $startDateSchoolYear) {
                    $previewsWeekDate = false;
                }
                if ($nextWeekDate > $endDateSchoolYear) {
                    $nextWeekDate = false;
                }
            } else {
                return new Warning('Das Schuljahr besitzt keinen Zeitraum', new Exclamation());
            }
        } else {
            return new Warning('Kein Schuljahr gefunden', new Exclamation());
        }

        for ($day = 1; $day < 6; $day++) {
            // Ferien, Feiertage
            $isHoliday = false;
            if ($tblYear) {
                if ($tblCompanyList) {
                    foreach ($tblCompanyList as $tblCompany) {
                        if (($isHoliday = Term::useService()->getHolidayByDay($tblYear, $startDate, $tblCompany))) {
                            break;
                        }
                    }
                } else {
                    $isHoliday = Term::useService()->getHolidayByDay($tblYear, $startDate, null);
                }
            }
            if ($isHoliday) {
                $holidayList[$day] = true;
            }

            // aktueller Tag
            $isCurrentDay = (new DateTime('today'))->format('d.m.Y') ==  $startDate->format('d.m.Y');

            $headerContent = $dayName[$day] . new Muted(', den ' . $startDate->format('d.m.Y'));
            $headerList[$day] = $this->getTableHeadColumn(
                $isCurrentDay ? $this->getTextColor($headerContent, 'darkorange') : $headerContent,
                '19%',
                $isHoliday ? 'lightgray' : '#E0F0FF'
            );
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
                        $tblLessonContent->getDisplaySubject(false)
                        . ($teacher ? ' (' . $teacher . ')' : '')
                        . ($tblLessonContent->getContent() ? new Container('Inhalt: ' . $tblLessonContent->getContent())  : '')
                        . ($tblLessonContent->getHomework() ? new Container('Hausaufgaben: ' . $tblLessonContent->getHomework())  : '')
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
                $isHoliday = isset($holidayList[$j]);
                if (isset($bodyList[$i][$j])) {
                    $cell = $bodyList[$i][$j];
                } elseif ($isHoliday) {
                    $cell = new Center(new Muted('f'));
                } else {
                    $cell = (new Link(
                        '<div style="height: 22px"></div>',
                        ApiDigital::getEndpoint(),
                        null,
                        array(),
                        $i . '. Thema/Hausaufgaben hinzufügen'
                    ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionId, $GroupId,
                        $dateStringList[$j], $i));
                }
                $columns[] = (new TableColumn($cell))
                    ->setBackgroundColor($isHoliday ? 'lightgray' : '')
                    ->setOpacity($isHoliday ? '0.5' : '1.0')
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
                                    $previewsWeekDate
                                        ? (new Link(new ChevronLeft(), ApiDigital::getEndpoint(), null, array(), 'KW' . $previewsWeek))
                                            ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent(
                                                $DivisionId, $GroupId, $previewsWeekDate->format('d.m.Y'), 'Week'))
                                        : ''
                                )
                                , 1),
                            new LayoutColumn(
                                new Center(new Bold('KW' . $currentWeek. ' '))
                                , 4),
                            new LayoutColumn(
                                new Center(
                                    $nextWeekDate
                                        ? (new Link(new ChevronRight(), ApiDigital::getEndpoint(), null, array(), 'KW' . $nextWeek))
                                            ->ajaxPipelineOnClick(ApiDigital::pipelineLoadLessonContentContent(
                                                $DivisionId, $GroupId, $nextWeekDate->format('d.m.Y'), 'Week'))
                                        : ''
                                )
                                , 1),
                            new LayoutColumn('&nbsp;', 3),
                        ))))
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

        return $content . Digital::useService()->getCanceledSubjectOverview($date, $tblDivision, $tblGroup) . ' ';
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
            $Lesson . '. Thema/Hausaufgaben bearbeiten'
        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenEditLessonContentModal($LessonContentId));
    }

    /**
     * @param string $name
     * @param string $width
     * @param string $backgroundColor
     *
     * @return TableColumn
     */
    private function getTableHeadColumn(string $name, string $width = 'auto', string $backgroundColor = '#E0F0FF'): TableColumn
    {
        $size = 1;
        return (new TableColumn(new Center(new Bold($name)), $size, $width))
            ->setBackgroundColor($backgroundColor)
            ->setOpacity($backgroundColor == 'lightgray' ? '0.5' : '1.0')
            ->setVerticalAlign('middle')
            ->setMinHeight('35px');
    }

    /**
     * @param string $content
     * @param string $color
     *
     * @return string
     */
    private function getTextColor(string $content, string $color): string
    {
        return '<span style="color: ' . $color . ';">' . $content . '</span>';
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
            $Global->POST['Data']['serviceTblSubstituteSubject'] =
                ($tblSubstituteSubject = $tblLessonContent->getServiceTblSubstituteSubject()) ? $tblSubstituteSubject->getId() : 0;
            $Global->POST['Data']['IsCanceled'] = $tblLessonContent->getIsCanceled();
            $Global->POST['Data']['serviceTblPerson'] =
                ($tblPerson = $tblLessonContent->getServiceTblPerson()) ? $tblPerson->getId() : 0;
            $Global->POST['Data']['Content'] = $tblLessonContent->getContent(false);
            $Global->POST['Data']['Homework'] = $tblLessonContent->getHomework();
            $Global->POST['Data']['Room'] = $tblLessonContent->getRoom();

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
            // befüllen bei neuen Einträge aus dem importierten Stundenplan
            if ($tblDivision && $Date && $Lesson
                && ($tblTimetableNode = Timetable::useService()->getTimeTableNodeBy($tblDivision, new DateTime($Date), (int) $Lesson))
            ) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['serviceTblSubject'] = $tblTimetableNode->getServiceTblSubject() ? $tblTimetableNode->getServiceTblSubject()->getId() : 0;
                $Global->POST['Data']['Room'] =$tblTimetableNode->getRoom();
                $Global->savePost();
            }

            $saveButton = (new Primary('Speichern', ApiDigital::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDigital::pipelineCreateLessonContentSave(
                    $tblDivision ? $tblDivision->getId() : null,
                    $tblGroup ? $tblGroup->getId() : null
                ));
        }
        $buttonList[] = $saveButton;

//        $tblTeacherList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('TEACHER'));

        $tblSubjectList = Subject::useService()->getSubjectAll();

        for ($i = 0; $i < 11; $i++) {
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
                        new SelectBox('Data[serviceTblSubstituteSubject]', 'Vertretungsfach', array('{{ Acronym }} - {{ Name }}' => $tblSubjectList))
                        , 6),
//                    new FormColumn(
//                        new SelectBox('Data[serviceTblPerson]', 'Lehrer', array('{{ FullName }}' => $tblTeacherList))
//                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        new CheckBox('Data[IsCanceled]', 'Fach ist ausgefallen', 1)
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Content]', 'Thema', 'Thema', new Edit())
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Homework]', 'Hausaufgaben', 'Hausaufgaben', new Home())
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Room]', 'Raum', 'Raum', new MapMarker())
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

    /**
     * @param null $DivisionId
     * @param null $SubjectId
     * @param null $SubjectGroupId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendCourseContent(
        $DivisionId = null,
        $SubjectId = null,
        $SubjectGroupId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('SekII-Kurs-Heft', 'Übersicht');

        $stage->addButton(new Standard(
            'Zurück', '/Education/ClassRegister/Digital/LessonContent', new ChevronLeft(), array(
                'DivisionId' => $DivisionId,
                'BasicRoute' => $BasicRoute
            )
        ));

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblSubject = Subject::useService()->getSubjectById($SubjectId);
        $tblSubjectGroup = Division::useService()->getSubjectGroupById($SubjectGroupId);

        if ($tblDivision && $tblSubject && $tblSubjectGroup
            && ($tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                $tblDivision, $tblSubject, $tblSubjectGroup
            ))
        ) {
            $tblYear = $tblDivision->getServiceTblYear();
            $content[] = 'Klasse: ' . $tblDivision->getDisplayName();
            $content[] = 'Fach: ' . $tblSubject->getDisplayName();
            $content[] = 'Kurs: ' . $tblSubjectGroup->getName();

            $stage->setContent(
                ApiDigital::receiverModal()
                . ApiAbsence::receiverModal()
                . new Layout(new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(new Panel('SekII-Kurs', $content, Panel::PANEL_TYPE_INFO), 6),
                        new LayoutColumn(new Panel('Schuljahr', $tblYear ? $tblYear->getDisplayName() : '', Panel::PANEL_TYPE_INFO), 6)
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            (new Primary(
                                new Plus() . ' Thema/Hausaufgaben hinzufügen',
                                ApiDigital::getEndpoint()
                            ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateCourseContentModal($DivisionId, $SubjectId, $SubjectGroupId))
                            . (new Primary(
                                new Plus() . ' Fehlzeit hinzufügen',
                                ApiAbsence::getEndpoint()
                            ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal(null, $DivisionId, null,
                                'DivisionSubject', $tblDivisionSubject->getId()))
                        )
                    ))
                )))
                . new Container('&nbsp;')
                . ApiDigital::receiverBlock($this->loadCourseContentTable($tblDivision, $tblSubject, $tblSubjectGroup),
                    'CourseContentContent')
            );
        } else {
            return new Danger('SekII-Kurs nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return string
     */
    public function loadCourseContentTable(TblDivision $tblDivision, TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup): string
    {
        $dataList = array();
        $divisionList = array('0' => $tblDivision);
        if (($tblCourseContentList = Digital::useService()->getCourseContentListBy($tblDivision, $tblSubject, $tblSubjectGroup))) {
            $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                $tblDivision, $tblSubject, $tblSubjectGroup
            );
            foreach ($tblCourseContentList as $tblCourseContent) {
                $absenceList = array();
                $lessonArray = array();
                if (($AbsenceList = Absence::useService()->getAbsenceAllByDay(new DateTime($tblCourseContent->getDate()),
                    null, null, $divisionList, array(), $hasTypeOption, null))
                ) {
                    $lesson = $tblCourseContent->getLesson();
                    $lessonArray[$lesson] = $lesson;
                    if ($tblCourseContent->getIsDoubleLesson()) {
                        $lesson++;
                        $lessonArray[$lesson] = $lesson;
                    }
                    foreach ($AbsenceList as $Absence) {
                        if (($tblAbsence = Absence::useService()->getAbsenceById($Absence['AbsenceId']))) {
                            $isAdd = false;
                            if (($tblAbsenceLessonList = Absence::useService()->getAbsenceLessonAllByAbsence($tblAbsence))) {
                                foreach ($tblAbsenceLessonList as $tblAbsenceLesson) {
                                    if (isset($lessonArray[$tblAbsenceLesson->getLesson()])) {
                                        $isAdd = true;
                                        break;
                                    }
                                }
                            // ganztägig
                            } else {
                                $isAdd = true;
                            }

                            if ($isAdd) {
                                $lessonString = $tblAbsence->getLessonStringByAbsence();
                                $type = $tblAbsence->getTypeDisplayShortName();
                                $remark = $tblAbsence->getRemark();
                                $toolTip = ($lessonString ? $lessonString . ' / ' : '') . ($type ? $type . ' / ' : '') . $tblAbsence->getStatusDisplayShortName()
                                    . (($tblPersonStaff = $tblAbsence->getDisplayStaff()) ? ' - ' . $tblPersonStaff : '')
                                    . ($remark ? ' - ' . $remark : '');

                                $absenceList[] = new Container((new Link(
                                    $Absence['Person'],
                                    ApiAbsence::getEndpoint(),
                                    null,
                                    array(),
                                    $toolTip,
                                    null,
                                    $tblAbsence->getIsCertificateRelevant() ? AbstractLink::TYPE_LINK : AbstractLink::TYPE_MUTED_LINK
                                ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenEditAbsenceModal($tblAbsence->getId(),
                                    'DivisionSubject', $tblDivisionSubject ? $tblDivisionSubject->getId(): null)));
                            }
                        }
                    }
                }

                $dataList[] = array(
                    'Date' => $tblCourseContent->getDate(),
                    'Lesson' => new Center(implode(', ', $lessonArray)),
                    'IsDoubleLesson' => new Center($tblCourseContent->getIsDoubleLesson() ? 'X' : ''),
                    'Content' => $tblCourseContent->getContent(),
                    'Homework' => $tblCourseContent->getHomework(),
                    'Room' => $tblCourseContent->getRoom(),
                    'Absence' => implode(' ', $absenceList),
                    'Teacher' => $tblCourseContent->getTeacherString(),
                    'Option' =>
                        (new Standard(
                            '',
                            ApiDigital::getEndpoint(),
                            new Edit(),
                            array(),
                            'Bearbeiten'
                        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenEditCourseContentModal($tblCourseContent->getId()))
                        . (new Standard(
                            '',
                            ApiDigital::getEndpoint(),
                            new Remove(),
                            array(),
                            'Löschen'
                        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenDeleteCourseContentModal($tblCourseContent->getId()))
                );
            }
        }

        return new TableData(
            $dataList,
            null,
            array(
                'Date' => 'Datum',
                'Lesson' => new ToolTip('UE', 'Unterrichtseinheit'),
                'IsDoubleLesson' => 'Doppel&shy;stunde',
                'Room' => 'Raum',
                'Content' => 'Thema',
                'Homework' => 'Hausaufgaben',
                'Absence' => 'Fehlzeiten',
                'Teacher' => 'Lehrer',
                'Option' => ''
            ),
            array(
                'order' => array(
                    array(0, 'desc')
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => 0),
                    array('width' => '50px', 'targets' => 0),
                    array('width' => '25px', 'targets' => 1),
                    array('width' => '25px', 'targets' => 2),
                    array('width' => '25px', 'targets' => 3),
                    array('width' => '50px', 'targets' => 7),
                    array('width' => '60px', 'targets' => -1),
                ),
                'responsive' => false
            )
        );
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     * @param null $CourseContentId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formCourseContent(TblDivision $tblDivision, TblSubject $tblSubject, TblSubjectGroup $tblSubjectGroup,
        $CourseContentId = null, bool $setPost = false): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        if ($setPost && $CourseContentId
            && ($tblCourseContent = Digital::useService()->getCourseContentById($CourseContentId))
        ) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Date'] = $tblCourseContent->getDate();
            $Global->POST['Data']['Lesson'] = $tblCourseContent->getLesson();
            $Global->POST['Data']['serviceTblPerson'] =
                ($tblPerson = $tblCourseContent->getServiceTblPerson()) ? $tblPerson->getId() : 0;
            $Global->POST['Data']['Content'] = $tblCourseContent->getContent();
            $Global->POST['Data']['Homework'] = $tblCourseContent->getHomework();
            $Global->POST['Data']['Room'] = $tblCourseContent->getRoom();
            $Global->POST['Data']['IsDoubleLesson'] = $tblCourseContent->getIsDoubleLesson() ? 1 : 0;

            $Global->savePost();
        }

        if ($CourseContentId) {
            $saveButton = (new Primary('Speichern', ApiDigital::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDigital::pipelineEditCourseContentSave($CourseContentId));
        } else {
            $saveButton = (new Primary('Speichern', ApiDigital::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDigital::pipelineCreateCourseContentSave(
                    $tblDivision->getId(),
                    $tblSubject->getId(),
                    $tblSubjectGroup->getId()
                ));
        }
        $buttonList[] = $saveButton;

        for ($i = 0; $i < 11; $i++) {
            $lessons[] = new SelectBoxItem($i, $i . '. Unterrichtseinheit');
        }

        // Unterrichteinheit löchen
        if ($CourseContentId) {
            $buttonList[] = (new \SPHERE\Common\Frontend\Link\Repository\Danger(
                'Löschen',
                ApiDigital::getEndpoint(),
                new Remove(),
                array(),
                false
            ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenDeleteCourseContentModal($CourseContentId));
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
                        new CheckBox('Data[IsDoubleLesson]', 'Doppelstunde', 1)
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Content]', 'Thema', 'Thema', new Edit())
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Homework]', 'Hausaufgaben', 'Hausaufgaben', new Home())
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Room]', 'Raum', 'Raum', new MapMarker())
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

    /**
     * @param null $DivisionId
     * @param null $GroupId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendStudentList(
        $DivisionId = null,
        $GroupId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Schülerliste');

        $stage->addButton(new Standard(
            'Zurück', $BasicRoute, new ChevronLeft()
        ));
        $tblYear = null;
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        if ($tblDivision || $tblGroup) {
            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow(
                            $tblDivision ?: null, $tblGroup ?: null, $tblYear
                        ),
                        Digital::useService()->getHeadButtonListLayoutRow($tblDivision ?: null, $tblGroup ?: null,
                            '/Education/ClassRegister/Digital/Student', $BasicRoute)
                    )),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        Digital::useService()->getStudentTable($tblDivision ?: null, $tblGroup ?: null, $BasicRoute,
                            '/Education/ClassRegister/Digital/Student')
                    )), new Title(new PersonGroup() . ' Schülerliste'))
                ))
            );
        } else {
            return new Danger('Klasse oder Gruppe nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendDownload(
        $DivisionId = null,
        $GroupId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Download');

        $stage->addButton(new Standard(
            'Zurück', $BasicRoute, new ChevronLeft()
        ));
        $tblYear = null;
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        if ($tblDivision || $tblGroup) {
            if ($tblGroup) {
                $name = 'Stammgruppenliste';
            } else {
                $name = 'Klassenliste';
            }

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow(
                            $tblDivision ?: null, $tblGroup ?: null, $tblYear
                        ),
                        Digital::useService()->getHeadButtonListLayoutRow($tblDivision ?: null, $tblGroup ?: null,
                            '/Education/ClassRegister/Digital/Download', $BasicRoute)
                    )),
                    new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(
                            new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!',
                                new Exclamation())
                        ),
                        new LayoutColumn(
                            new Link(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), $name . ' Schülerliste'),
                                '/Api/Reporting/Standard/Person/ClassList/Download', null, array(
                                    'DivisionId' => $DivisionId,
                                    'GroupId'    => $GroupId
                                ))
                            , 3),
                        new LayoutColumn(
                            new Link(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), $name . ' Krankenakte'),
                                '/Api/Reporting/Standard/Person/MedicalRecordClassList/Download', null, array(
                                    'DivisionId' => $DivisionId,
                                    'GroupId'    => $GroupId
                                ))
                            , 3),
                        new LayoutColumn(
                            new Link(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), $name . ' Einverständniserklärung'),
                                '/Api/Reporting/Standard/Person/AgreementClassList/Download', null, array(
                                    'DivisionId' => $DivisionId,
                                    'GroupId'    => $GroupId
                                ))
                            , 3),
                        new LayoutColumn(
                            new Link(new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/SSWUser.png'), $name . ' zeugnisrelevante Fehlzeiten'),
                                '/Api/Reporting/Standard/Person/ClassRegister/Absence/Download', null, array(
                                    'DivisionId' => $DivisionId,
                                    'GroupId'    => $GroupId
                                ))
                            , 3),
                    )), new Title(new Download() . ' Download'))
                ))
            );
        } else {
            return new Danger('Klasse oder Gruppe nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param null $DivisionId
     * @param null $PersonId
     * @param string $BasicRoute
     * @param string $ReturnRoute
     * @param null $GroupId
     *
     * @return Stage
     */
    public function frontendIntegration($DivisionId = null, $PersonId = null, string $BasicRoute = '', string $ReturnRoute = '',
        $GroupId = null): Stage
    {

        $Stage = new Stage('Digitales Klassenbuch', 'Integration verwalten');

        if ($ReturnRoute) {
            $Stage->addButton(new Standard('Zurück', $ReturnRoute, new ChevronLeft(),
                array(
                    'DivisionId' => $GroupId ? null : $DivisionId,
                    'GroupId'    => $GroupId,
                    'BasicRoute' => $BasicRoute,
                ))
            );
        }

        $PersonPanel = '';
        if(($tblPerson = Person::useService()->getPersonById($PersonId))){
            $PersonPanel = new Panel('Person', $tblPerson->getLastFirstName(), Panel::PANEL_TYPE_INFO);
        }
        $DivisionPanel = '';
        if(($tblDivision = Division::useService()->getDivisionById($DivisionId))){
            $DivisionPanel = new Panel('Klasse, Schulart', $tblDivision->getDisplayName().', '.$tblDivision->getTypeName(), Panel::PANEL_TYPE_INFO);
        }


        if(($tblPerson = Person::useService()->getPersonById($PersonId))){
            $Content = (new Well(Student::useFrontend()->frontendIntegration($tblPerson)));
        } else {
            $Content = (new Warning('Person wurde nicht gefunden.'));
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $PersonPanel
                            , 6),
                        new LayoutColumn(
                            $DivisionPanel
                            , 6),
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            $Content
                        )
                    )
                ))
            )
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
    public function frontendLectureship(
        $DivisionId = null,
        $GroupId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Unterrichtete Fächer / Lehrer');

        $stage->addButton(new Standard(
            'Zurück', $BasicRoute, new ChevronLeft()
        ));
        $tblYear = null;
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        if ($tblDivision || $tblGroup) {
            if ($tblGroup) {
                $content = '';
                if (($tblDivisionList = $tblGroup->getCurrentDivisionList())) {
                    foreach ($tblDivisionList as $tblGroupDivision) {
                        $content .= Digital::useService()->getSubjectsAndLectureshipByDivision($tblGroupDivision);
                    }
                }
            } else {
                $content = Digital::useService()->getSubjectsAndLectureshipByDivision($tblDivision);
            }

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow(
                            $tblDivision ?: null, $tblGroup ?: null, $tblYear
                        ),
                        Digital::useService()->getHeadButtonListLayoutRow($tblDivision ?: null, $tblGroup ?: null,
                            '/Education/ClassRegister/Digital/Lectureship', $BasicRoute)
                    )),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        $content
                    )), new Title(new Listing() . ' Unterrichtete Fächer / Lehrer'))
                ))
            );
        } else {
            return new Danger('Klasse oder Gruppe nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    private function getStudentPanel(?TblDivision $tblDivision, ?TblGroup $tblGroup): string
    {
        $tblPersonList = false;
        $dataList = array();
        if ($tblDivision) {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        } elseif ($tblGroup) {
            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName', new StringNaturalOrderSorter());
            }
        }

        if ($tblPersonList) {
            $count = 0;
            foreach ($tblPersonList as $tblPerson) {
                $dataList[] = new PullLeft(++$count) . new PullRight($tblPerson->getLastFirstName());
            }
        }

        return new Panel(
            'Schüler',
//            new Table($tableHead, $tableBody, null, false, null, 'TableCustom'),
            $dataList,
            Panel::PANEL_TYPE_INFO
        );
    }
}