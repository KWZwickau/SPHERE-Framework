<?php

namespace SPHERE\Application\Education\ClassRegister\Digital;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\People\Meta\Agreement\ApiAgreement;
use SPHERE\Application\Api\People\Meta\MedicalRecord\MedicalRecordReadOnly;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Certificate\Prepare\View;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContent;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableNode;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableReplacement;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Reporting\Standard\Person\Person;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Commodity;
use SPHERE\Common\Frontend\Icon\Repository\CommodityItem;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Extern;
use SPHERE\Common\Frontend\Icon\Repository\History;
use SPHERE\Common\Frontend\Icon\Repository\Holiday;
use SPHERE\Common\Frontend\Icon\Repository\Hospital;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

abstract class ServiceTabs extends ServiceForgotten
{
    /**
     * @param Stage $Stage
     * @param $view
     * @param $Route
     */
    public function setHeaderButtonList(Stage $Stage, $view, $Route)
    {
        $hasTeacherRight = Access::useService()->hasAuthorization($Route . '/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization($Route . '/Headmaster');

        $countRights = 0;
        if ($hasTeacherRight) {
            $countRights++;
        }
        if ($hasHeadmasterRight) {
            $countRights++;
        }

        if ($countRights > 1) {
            if ($hasTeacherRight) {
                if ($view == View::TEACHER) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Lehrer')),
                        $Route . '/Teacher', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Lehrer',
                        $Route . '/Teacher'));
                }
            }
            if ($hasHeadmasterRight) {
                if ($view == View::HEADMASTER) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Alle Klassenbücher')),
                        $Route . '/Headmaster', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Alle Klassenbücher',
                        $Route . '/Headmaster'));
                }
            }
        }
    }

    /**
     * @param $Route
     * @param $IsAllYears
     * @param $YearId
     * @param $HasAllYears
     * @param $HasCurrentYears
     * @param $yearFilterList
     * @param bool $hasLastYearsTemp
     * @param bool $hasFutureYear
     *
     * @return array
     */
    public function setYearGroupButtonList($Route, $IsAllYears, $YearId, $HasAllYears, $HasCurrentYears, &$yearFilterList, bool $hasLastYearsTemp = false,
        bool $hasFutureYear = false): array
    {
        $tblYear = false;
        $tblYearList = Term::useService()->getYearByNow();
        if ($YearId) {
            $tblYear = Term::useService()->getYearById($YearId);
        } elseif (!$IsAllYears && $tblYearList && !$HasCurrentYears) {
            $tblYear = end($tblYearList);
        }
        $isCurrentYears = $HasCurrentYears && !$IsAllYears && !$YearId;

        $buttonList = array();
        if ($tblYearList) {
            if ($HasCurrentYears) {
                if ($isCurrentYears) {
                    $buttonList[] = (new Standard(new Info(new Bold('Aktuelles Schuljahr')),
                        $Route, new Edit()));
                } else {
                    $buttonList[] = (new Standard('Aktuelles Schuljahr', $Route, null));
                }
            }

            if ($hasLastYearsTemp) {
                $date = new DateTime('now');
                // springt das neue Jahr in den Juli muss weniger als 1 Jahr abgezogen werden,
                // um das letzte Jahr zu bekommen, wenn es regulär am 01.08.xxxx oder später begonnen hat
                $isChange = false;
                if($date->format('m') == '07'){
                    $date = $date->sub(new DateInterval('P11M'));
                    $isChange = true;
                }
                // Standard
                 if(!$isChange) {
                    $date = $date->sub(new DateInterval('P1Y'));
                }
                if (($tblLastYearList = Term::useService()->getYearAllByDate($date))) {
                    foreach ($tblLastYearList as $tblLastYear) {
                        if ($tblYear && $tblYear->getId() == $tblLastYear->getId()) {
                            $buttonList[$tblLastYear->getId()] = (new Standard(new Info(new Bold($tblLastYear->getDisplayName())), $Route, new Edit(), array('YearId' => $tblLastYear->getId())));
                            $yearFilterList[$tblLastYear->getId()] = $tblLastYear;
                        } else {
                            $buttonList[$tblLastYear->getId()] = (new Standard($tblLastYear->getDisplayName(), $Route, null, array('YearId' => $tblLastYear->getId())));
                        }
                    }
                }
            }

            $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('DisplayName');
            /** @var TblYear $tblYearItem */
            foreach ($tblYearList as $tblYearItem) {
                if ($tblYear && $tblYear->getId() == $tblYearItem->getId()) {
                    $buttonList[$tblYearItem->getId()] = (new Standard(new Info(new Bold($tblYearItem->getDisplayName())),
                        $Route, new Edit(), array('YearId' => $tblYearItem->getId())));
                    $yearFilterList[$tblYearItem->getId()] = $tblYearItem;
                } else {
                    if ($isCurrentYears) {
                        $yearFilterList[$tblYearItem->getId()] = $tblYearItem;
                    }
                    $buttonList[$tblYearItem->getId()] = (new Standard($tblYearItem->getDisplayName(), $Route, null, array('YearId' => $tblYearItem->getId())));
                }
            }

            if ($hasFutureYear && !$HasAllYears) {
                $date = new DateTime('now');
                $date = $date->add(new DateInterval('P1Y'));
                if (($tblFutureYearList = Term::useService()->getYearAllByDate($date))) {
                    foreach ($tblFutureYearList as $tblFutureYear) {
                        if ($tblYear && $tblYear->getId() == $tblFutureYear->getId()) {
                            $buttonList[$tblFutureYear->getId()] = (new Standard(new Info(new Bold($tblFutureYear->getDisplayName())), $Route, new Edit(), array('YearId' => $tblFutureYear->getId())));
                            $yearFilterList[$tblFutureYear->getId()] = $tblFutureYear;
                        } else {
                            $buttonList[$tblFutureYear->getId()] = (new Standard($tblFutureYear->getDisplayName(), $Route, null, array('YearId' => $tblFutureYear->getId())));
                        }
                    }
                }
            }

            if ($HasAllYears) {
                if ($IsAllYears) {
                    $buttonList[] = (new Standard(new Info(new Bold('Alle Schuljahre')),
                        $Route, new Edit(), array('IsAllYears' => true)));
                }  else {
                    $buttonList[] = (new Standard('Alle Schuljahre', $Route, null,
                        array('IsAllYears' => true)));
                }
            }

            // Abstandszeile
            $buttonList[] = new Container('&nbsp;');
        }

        return $buttonList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return string
     */
    public function getHeadContent(TblDivisionCourse $tblDivisionCourse): string
    {
        $content[] = $tblDivisionCourse->getTypeName() . ': ' . $tblDivisionCourse->getDisplayName();

        // SekII-Kurs
        if ($tblDivisionCourse->getType()->getIsCourseSystem()
            && ($tblSubject = $tblDivisionCourse->getServiceTblSubject())
        ) {
            $content[] = 'Fach: ' . $tblSubject->getDisplayName();
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())
                && ($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, null, $tblDivisionCourse, $tblSubject))
            ) {
                $subjectTeacherList = array();
                foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                    if (($tblPersonTeacher = $tblTeacherLectureship->getServiceTblPerson())) {
                        $subjectTeacherList[] = $tblPersonTeacher->getFullName();
                    }
                }
                if ($subjectTeacherList) {
                    $content[] = 'Fachlehrer: ' . implode(', ', $subjectTeacherList);
                }
            }
        }

        // Gruppenlehrer
        $divisionTeacherList = array();
        if (($tblCustodyMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy(
            $tblDivisionCourse, TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER, false, false
        ))) {
            /** @var TblDivisionCourseMember $tblDivisionTeacher */
            foreach ($tblCustodyMemberList as $tblDivisionTeacher) {
                if (($tblPersonDivisionTeacher = $tblDivisionTeacher->getServiceTblPerson())) {
                    $divisionTeacherList[] = $tblPersonDivisionTeacher->getFullName()
                        . ($tblDivisionTeacher->getDescription() ? ' ' . $tblDivisionTeacher->getDescription() : '');
                }
            }
        }
        if ($divisionTeacherList) {
            $content[] = $tblDivisionCourse->getDivisionTeacherName() . ': ' . implode(', ', $divisionTeacherList);
        }

        // Elternsprecher
        $custodyList = array();
        if (($tblCustodyMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy(
            $tblDivisionCourse, TblDivisionCourseMemberType::TYPE_CUSTODY, false, false
        ))) {
            /** @var TblDivisionCourseMember $tblCustody */
            foreach ($tblCustodyMemberList as $tblCustody) {
                if (($tblPersonCustody = $tblCustody->getServiceTblPerson())) {
                    $custodyList[] = $tblPersonCustody->getFullName()
                        . ($tblCustody->getDescription() ? ' ' . $tblCustody->getDescription() : '');
                }
            }
        }
        if ($custodyList) {
            $content[] = 'Elternsprecher: ' . implode(', ', $custodyList);
        }

        // Klassensprecher
        $representativeList = array();
        if (($tblRepresentativeMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy(
            $tblDivisionCourse, TblDivisionCourseMemberType::TYPE_REPRESENTATIVE, false, false
        ))) {
            /** @var TblDivisionCourseMember $tblRepresentative */
            foreach ($tblRepresentativeMemberList as $tblRepresentative) {
                if (($tblPersonRepresentative = $tblRepresentative->getServiceTblPerson())) {
                    $representativeList[] = $tblPersonRepresentative->getFirstSecondName() . ' ' . $tblPersonRepresentative->getLastName()
                        . ($tblRepresentative->getDescription() ? ' ' . $tblRepresentative->getDescription() : '');
                }
            }
        }
        if ($representativeList) {
            $content[] = 'Klassensprecher: ' . implode(', ', $representativeList);
        }

        return new Layout(new LayoutGroup(
            new LayoutRow(array(
                new LayoutColumn(new Panel($tblDivisionCourse->getTypeName(), $content, Panel::PANEL_TYPE_INFO), 6),
                new LayoutColumn(new Panel('Schuljahr', ($tblYear = $tblDivisionCourse->getServiceTblYear()) ? $tblYear->getDisplayName() : '', Panel::PANEL_TYPE_INFO), 6)
            ))
        ));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param string $Route
     * @param string $BasicRoute
     *
     * @return string
     */
    public function getHeadButtonList(TblDivisionCourse $tblDivisionCourse,
        string $Route = '/Education/ClassRegister/Digital/LessonContent', string $BasicRoute = ''): string
    {
        $isCourseSystem = DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse);
        $DivisionCourseId = $tblDivisionCourse->getId();

        $buttonList[] = Digital::useFrontend()->getBackButton($tblDivisionCourse, null, $BasicRoute);
        if ($isCourseSystem) {
            $buttonList[] = $this->getButton('Kursheft auswählen', '/Education/ClassRegister/Digital/SelectCourse', new Book(),
                $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/SelectCourse');
        } else {
            $buttonList[] = $this->getButton('Klassentagebuch', '/Education/ClassRegister/Digital/LessonContent', new Book(),
                $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/LessonContent');
        }

        $buttonList[] = $this->getButton('Kontrolle FL', '/Education/ClassRegister/Digital/TeacherControl', new Ok(),
            $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/TeacherControl');

        // Klassentagebuch Kontrolle: nur für Klassenlehrer, Tudor oder Schulleitung
        if ((($tblPerson = Account::useService()->getPersonByLogin())
                && ($tblDivisionCourseMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))
                && (DivisionCourse::useService()->getDivisionCourseMemberByPerson($tblDivisionCourse, $tblDivisionCourseMemberType, $tblPerson))
            )
            || Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting')
        ) {
            // Klassentagebuch Kontrolle: nicht bei Kurssystemen
            if (!$isCourseSystem) {
                $buttonList[] = $this->getButton('Kontrolle KL / SL', '/Education/ClassRegister/Digital/LessonWeek', new Ok(),
                    $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/LessonWeek');
            }
        }

        $buttonList[] = $this->getButton('Schülerliste', '/Education/ClassRegister/Digital/Student', new PersonGroup(),
            $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Student');

        // Fehlzeiten (Kalenderansicht) nur bei Klassen anzeigen
        $buttonList[] = $this->getButton('Fehlzeiten (Kalenderansicht)', '/Education/ClassRegister/Digital/AbsenceMonth',
            new Calendar(), $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/AbsenceMonth');

        // Belehrungen: nicht bei Kurssystemen → Belehrungen direkt im Kursheft
        if (!$isCourseSystem) {
            $buttonList[] = $this->getButton('Belehrungen', '/Education/ClassRegister/Digital/Instruction',
                new CommodityItem(), $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Instruction');
        }
        $buttonList[] = $this->getButton('Unterrichtete Fächer / Lehrer', '/Education/ClassRegister/Digital/Lectureship',
            new Listing(), $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Lectureship');
        $buttonList[] = $this->getButton('Ferien', '/Education/ClassRegister/Digital/Holiday',
            new Holiday(), $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Holiday');
        if (!$isCourseSystem) {
            $buttonList[] = $this->getButton('Vergessene AM / HA', '/Education/ClassRegister/Digital/Forgotten',
                new History(), $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Forgotten');
        }
        $buttonList[] = $this->getButton('Download', '/Education/ClassRegister/Digital/Download',
            new Download(), $DivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Download');

        return implode(' ', $buttonList);
    }

    /**
     * @param string $name
     * @param string $route
     * @param $icon
     * @param $DivisionCourseId
     * @param $BasicRoute
     * @param bool $isSelected
     *
     * @return Standard
     */
    private function getButton(string $name, string $route, $icon, $DivisionCourseId, $BasicRoute, bool $isSelected = false): Standard
    {
        return new Standard(
            $isSelected ? new Info(new Bold($name)) : $name,
            $route,
            $icon,
            array(
                'DivisionCourseId' => $DivisionCourseId,
                'BasicRoute' => $BasicRoute
            )
        );
    }

    /**
     * @param string $name
     * @param string $route
     * @param $icon
     * @param $DivisionCourseId
     * @param $BackDivisionCourseId
     * @param $BasicRoute
     * @param bool $isSelected
     *
     * @return Standard
     */
    private function getButtonCourseSystem(string $name, string $route, $icon, $DivisionCourseId, $BackDivisionCourseId,
        $BasicRoute, bool $isSelected = false): Standard
    {
        return new Standard(
            $isSelected ? new Info(new Bold($name)) : $name,
            $route,
            $icon,
            array(
                'DivisionCourseId' => $DivisionCourseId,
                'BackDivisionCourseId' => $BackDivisionCourseId,
                'BasicRoute' => $BasicRoute
            )
        );
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param string $Route
     * @param string $BasicRoute
     * @param null $BackDivisionCourseId
     *
     * @return string
     */
    public function getHeadButtonListForCourseSystem(TblDivisionCourse $tblDivisionCourse,
        string $Route = '/Education/ClassRegister/Digital/CourseContent', string $BasicRoute = '', $BackDivisionCourseId = null): string
    {
        $buttonList[] = Digital::useFrontend()->getBackButton($tblDivisionCourse, $BackDivisionCourseId, $BasicRoute);

        $DivisionCourseId = $tblDivisionCourse->getId();
        $buttonList[] = $this->getButtonCourseSystem('Kursheft', '/Education/ClassRegister/Digital/CourseContent', new Book(),
            $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/CourseContent');

        // Kursheft Kontrolle: nur für Schulleitung
        if (Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting')) {
            $buttonList[] = $this->getButtonCourseSystem('Kursheft Kontrolle', '/Education/ClassRegister/Digital/CourseControl', new Ok(),
                $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/CourseControl');
        }

        $buttonList[] = $this->getButtonCourseSystem('Schülerliste', '/Education/ClassRegister/Digital/Student', new PersonGroup(),
            $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Student');
        $buttonList[] = $this->getButtonCourseSystem('Fehlzeiten (Kalenderansicht)', '/Education/ClassRegister/Digital/AbsenceMonth', new Calendar(),
            $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/AbsenceMonth');
        $buttonList[] = $this->getButtonCourseSystem('Belehrungen', '/Education/ClassRegister/Digital/Instruction', new CommodityItem(),
            $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Instruction');
//        $buttonList[] = $this->getButtonCourseSystem('Unterrichtete Fächer / Lehrer', '/Education/ClassRegister/Digital/Lectureship', new Listing(),
//            $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Lectureship');
        $buttonList[] = $this->getButtonCourseSystem('Ferien', '/Education/ClassRegister/Digital/Holiday', new Holiday(),
            $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Holiday');
        $buttonList[] = $this->getButtonCourseSystem('Vergessene AM / HA', '/Education/ClassRegister/Digital/Forgotten', new History(),
            $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Forgotten');
        $buttonList[] = $this->getButtonCourseSystem('Download', '/Education/ClassRegister/Digital/Download', new Download(),
            $DivisionCourseId, $BackDivisionCourseId, $BasicRoute, $Route == '/Education/ClassRegister/Digital/Download');

        return implode(' ', $buttonList);
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $IsToolTip
     *
     * @return string
     */
    public function getTeacherString(TblPerson $tblPerson, bool $IsToolTip = true): string
    {
        if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))
            && ($acronym = $tblTeacher->getAcronym())
        ) {
            $teacher = $acronym;
        } else {
            $teacher = $tblPerson->getLastName();
            if (strlen($teacher) > 5) {
                // bei normalen substr können Umlaute getrennt werden, wodurch dann z.B. die DataTable leer bleibt
                $teacher = mb_substr($teacher, 0, 5) . '.';
            }
        }

        return $IsToolTip ? new ToolTip($teacher, $tblPerson->getFullName()) : $teacher;
    }

    /**
     * @param $DivisionCourseId
     * @param string $BasicRoute
     * @param string $ReturnRoute
     *
     * @return string
     */
    public function getStudentTable($DivisionCourseId, string $BasicRoute, string $ReturnRoute): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses(false, true, new DateTime('today')))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && (list($fromDate, $tillDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear))
            && $fromDate
            && $tillDate
        ) {
            $studentTable = array();
            $count = 0;
            $hasColumnCourse = false;
            $hasDivision = false;
            $hasCoreGroup = false;
            $hasSchoolAttendanceYear = false;
            foreach ($tblPersonList as $tblPerson) {
                $schoolType = '';
                $level = '';
                $divisionName = '';
                $divisionTeacher = '';
                $coreGroupName = '';
                $coreGroupTeacher = '';
                $schoolAttendanceYear = '';
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);

                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                    $tblCompany = $tblStudentEducation->getServiceTblCompany();
                    if (($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())) {
                        $schoolType = $tblSchoolType->getShortName();
                        // Schulbesuchsjahr bei Förderschulen anzeigen
                        if ($tblSchoolType->getShortName() == 'FöS') {
                            $hasSchoolAttendanceYear = true;
                            $schoolAttendanceYear = $tblStudent->getSchoolAttendanceYear(false);
                        }
                    }
                    $tblCourse = $tblStudentEducation->getServiceTblCourse();
                    $level = $tblStudentEducation->getLevel();
                    if (($tblDivision = $tblStudentEducation->getTblDivision())) {
                        $hasDivision = true;
                        $divisionName = $tblDivision->getName();
                        $divisionTeacher = $tblDivision->getDivisionTeacherNameListString(', ');
                    }
                    if (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())) {
                        $hasCoreGroup = true;
                        $coreGroupName = $tblCoreGroup->getName();
                        $coreGroupTeacher = $tblCoreGroup->getDivisionTeacherNameListString(', ');
                    }
                } else {
                    $tblCompany = false;
                    $tblSchoolType = false;
                    $tblCourse = false;
                }

                $birthday = '';
                $Gender = '';
                if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                    if ($tblCommon->getTblCommonBirthDates()) {
                        $birthday = $tblCommon->getTblCommonBirthDates()->getBirthday();
                        $tblGender = $tblCommon->getTblCommonBirthDates()->getTblCommonGender();
                        if ($tblGender) {
                            $Gender = $tblGender->getShortName();
                        }
                    }
                }
                $PersonPicture = '';
                if(($tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson))){
                    $PersonPicture = new Center((new Link($tblPersonPicture->getPicture('50px', '10px'), $tblPerson->getId()))
                        ->ajaxPipelineOnClick(ApiPersonPicture::pipelineShowPersonPicture($tblPerson->getId())));
                }

                if ($tblSchoolType && $tblSchoolType->isTechnical()) {
                    $courseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                } else {
                    $courseName = $tblCourse ? $tblCourse->getName() : '';
                }
                if (!$hasColumnCourse && $courseName) {
                    $hasColumnCourse = true;
                }

                $medicalRecord = '';
                $agreement = '';
                $integration = '';

                if ($tblStudent) {
                    if (($tblMedicalRecord = $tblStudent->getTblStudentMedicalRecord())
                        && ($tblMedicalRecord->getDisease()
                            || $tblMedicalRecord->getMedication()
                            || $tblMedicalRecord->getAttendingDoctor())
                    ) {
                        $medicalRecord = (new Standard('', MedicalRecordReadOnly::getEndpoint(), new Hospital(), array(), 'Krankenakte'))
                            ->ajaxPipelineOnClick(MedicalRecordReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
                    }

                    if (Student::useService()->getStudentAgreementAllByStudent($tblStudent)) {
                        $agreement = (new Standard('', ApiAgreement::getEndpoint(), new Check(), array(), 'Einverständniserklärung'))
                            ->ajaxPipelineOnClick(ApiAgreement::pipelineOpenOverViewModal($tblPerson->getId()));
                    }
                }

                if (Student::useService()->getIsSupportByPerson($tblPerson)) {
                    $integration = (new Standard('', ApiSupportReadOnly::getEndpoint(), new Tag(), array(), 'Inklusion'))
                        ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
                }

                // Kontakt-Daten
                $contacts = array();
                $contacts = Person::useService()->getContactDataFromPerson($tblPerson, $contacts);

                // Fehlzeiten
                $unExcusedLessons = 0;
                $excusedLessons = 0;
                $excusedDays = Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null,
                    $fromDate, $tillDate, $excusedLessons);
                $unExcusedDays = Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null,
                    $fromDate, $tillDate, $unExcusedLessons);
                $absenceDays = ($excusedDays + $unExcusedDays) . ' (' . new Success($excusedDays) . ', '
                    . new Warning($unExcusedDays) . ')';
                $absenceLessons = ($excusedLessons + $unExcusedLessons) . ' (' . new Success($excusedLessons) . ', '
                    . new Warning($unExcusedLessons) . ')';

                $name = new Bold($tblPerson->getLastFirstNameWithCallNameUnderline(true));
                $studentTable[] = array(
                    'Number'        => ++$count,
                    'Name'          => $name,
                    'NameSecond'    => $name,
                    'Picture'       => $PersonPicture,
                    'Info'          => $integration . $medicalRecord . $agreement,
                    'Gender'        => $Gender,
                    'Address'       => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiTwoRowString() : '',
                    'Phone'         => $contacts['PhoneFixed'] ?? '',
                    'Mail'          => $contacts['MailFrontendListFixed'] ?? '',
                    'Birthday'      => $birthday,
                    'SchoolType'    => $schoolType,
                    'Level'         => $level,
                    'Course'        => $courseName,
                    'DivisionName'  => $divisionName,
                    'DivisionTeacher' => $divisionTeacher,
                    'CoreGroupName' => $coreGroupName,
                    'CoreGroupTeacher' => $coreGroupTeacher,
                    'SchoolAttendanceYear' => $schoolAttendanceYear,
                    'AbsenceDays'   => $absenceDays,
                    'AbsenceLessons'=> $absenceLessons,
                    'Option'        =>
                        (new Standard(
                            '', '/Education/ClassRegister/Digital/AbsenceStudent', new Time(),
                            array(
                                'DivisionCourseId' => $tblDivisionCourse->getId(),
                                'PersonId'   => $tblPerson->getId(),
                                'BasicRoute' => $BasicRoute,
                                'ReturnRoute'=> $ReturnRoute
                            ),
                            'Fehlzeiten des Schülers verwalten'
                        ))
                        . (new Standard(
                            '', '/Education/ClassRegister/Digital/Integration', new Commodity(),
                            array(
                                'DivisionCourseId' => $tblDivisionCourse->getId(),
                                'PersonId'   => $tblPerson->getId(),
                                'BasicRoute' => $BasicRoute,
                                'ReturnRoute'=> $ReturnRoute,
                            ),
                            'Inklusion des Schülers verwalten'
                        ))
                );
            }

            $columns['Number'] = '#';
            $columns['Name'] = 'Name';
            $columns['Picture'] = 'Foto';
            $columns['Info'] = 'Info';
            $columns['Gender'] = 'Ge&shy;schlecht';
            $columns['Birthday'] = 'Geburts&shy;datum';
            $columns['SchoolType'] = 'Schul&shy;art';
            $columns['Level'] = 'Klassen&shy;stufe';
            if ($hasColumnCourse) {
                $columns['Course'] = 'Bildungs&shy;gang';
            }
            if ($hasDivision) {
                $columns['DivisionName'] = 'Klasse';
                $columns['DivisionTeacher'] = 'Klassen&shy;lehrer';
            }
            if ($hasCoreGroup) {
                $columns['CoreGroupName'] = 'Stamm&shy;gruppe';
                $columns['CoreGroupTeacher'] = 'Tutor';
            }
            if ($hasSchoolAttendanceYear) {
                $columns['SchoolAttendanceYear'] = 'SBJ';
            }

            $columns['NameSecond'] = 'Name';
            $columns['Address'] = 'Adresse';
            $columns['Phone'] = new ToolTip('Telefon '. new InfoIcon(), 'p=Privat; g=Geschäftlich; n=Notfall; f=Fax; Bev.=Bevollmächtigt; Vorm.=Vormund; NK=Notfallkontakt');
            $columns['Mail'] = 'E-Mail';
            $columns['AbsenceDays'] = 'Zeugnis&shy;relevante Fehlzeiten Tage<br>(E, U)';
            $columns['AbsenceLessons'] = 'Zeugnis&shy;relevante Fehlzeiten UE<br>(E, U)';
            $columns['Option'] = '';

            return
                ApiSupportReadOnly::receiverOverViewModal()
                . MedicalRecordReadOnly::receiverOverViewModal()
                . ApiAgreement::receiverOverViewModal()
                . ApiPersonPicture::receiverModal()
                . (($inActivePanel = Person::useFrontend()->getInActiveStudentPanel($tblDivisionCourse, true, $BasicRoute, $ReturnRoute)) ? $inActivePanel : '')
                . (new TableData($studentTable, null, $columns,
                    array(
                        'paging' => false,
                        'columnDefs' => array(
                            array('type'  => Consumer::useService()->getGermanSortBySetting(), 'targets' => array(1, -7)),
                            array('type' => 'natural', 'targets' => array(-2, -3)),
                            array('width' => '60px', 'targets' => 3),
                            array('type' => 'de_date', 'targets' => 5),
                            array('width' => '60px', 'targets' => -2),
                            array('width' => '60px', 'targets' => -3),
                            array('width' => '180px', 'targets' => -6),
                            array('orderable' => false, 'width' => '60px', 'targets' => -1),
                        ),
                        'responsive' => false
                    )
                ));
        }

        return '';
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function getSubjectsAndLectureshipByDivisionCourse($DivisionCourseId): string
    {
        $dataList = array();
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse)
        ) {
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
                $tempList = array();
                if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByStudentDivisionCourseAndPeriod($tblDivisionCourse, 1))) {
                    foreach ($tblStudentSubjectList as $tblStudentSubject) {
                        if (($tblDivisionCourseSubject = $tblStudentSubject->getTblDivisionCourse())
                            && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
                            && !isset($tempList[$tblSubject->getId()][$tblDivisionCourseSubject->getId()])
                        ) {
                            $tempList[$tblSubject->getId()][$tblDivisionCourseSubject->getId()] = 1;
                        }
                    }
                }

                foreach ($tempList as $subjectId => $courseIdList) {
                    if (($tblSubjectItem = Subject::useService()->getSubjectById($subjectId))) {
                        $listing = array();
                        foreach ($courseIdList as $courseId => $value) {
                            if (($tblDivisionCourseItem = DivisionCourse::useService()->getDivisionCourseById($courseId))) {
                                if (($teacherNameList = $this->getSubjectTeacherNameListByDivisionCourse($tblDivisionCourseItem, $tblSubjectItem, $tblYear))) {
                                    $listing[] = new PullClear($tblDivisionCourseItem->getDisplayName() . new PullRight(implode(', ', $teacherNameList)));
                                }
                            }
                        }

                        $dataList[] = array(
                            'Subject' => $tblSubjectItem->getDisplayName(),
                            'Teacher' => empty($listing) ? '' : new \SPHERE\Common\Frontend\Layout\Repository\Listing($listing)
                        );
                    }
                }
            }
        } else {
            if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByDivisionCourse($tblDivisionCourse, false))
                && ($tblDivisionCourseListStudents = DivisionCourse::useService()->getDivisionCourseListByStudentsInDivisionCourse($tblDivisionCourse))
                && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            ) {
                $tblDivisionCourseListStudents = $this->getSorter($tblDivisionCourseListStudents)->sortObjectBy('Name', new StringNaturalOrderSorter());
                foreach ($tblSubjectList as $tblSubject) {
                    $listing = array();
                    /** @var TblDivisionCourse $tblDivisionCourseStudent */
                    foreach ($tblDivisionCourseListStudents as $tblDivisionCourseStudent) {
                        if (($teacherNameList = $this->getSubjectTeacherNameListByDivisionCourse($tblDivisionCourseStudent, $tblSubject, $tblYear))) {
                            $listing[] = new PullClear($tblDivisionCourseStudent->getDisplayName() . new PullRight(implode(', ', $teacherNameList)));
                        }
                    }

                    $dataList[] = array(
                        'Subject' => $tblSubject->getDisplayName(),
                        'Teacher' => empty($listing) ? '' : new \SPHERE\Common\Frontend\Layout\Repository\Listing($listing)
                    );
                }
            }
        }

        $columns = array(
            'Subject' => 'Unterrichtsfach',
            'Teacher' => 'Kurs' . new PullRight('Lehrer')
        );

        return (new TableData($dataList, new Title($tblDivisionCourse->getTypeName() . ' ' . $tblDivisionCourse->getDisplayName()), $columns, null))
            ->setHash('Table_Division_' . $tblDivisionCourse->getId());
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     * @param TblYear $tblYear
     *
     * @return array
     */
    private function getSubjectTeacherNameListByDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject, TblYear $tblYear): array
    {
        $teacherList = array();
        if ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_TEACHER_GROUP
            && $tblDivisionCourse->getServiceTblSubject()
            && $tblDivisionCourse->getServiceTblSubject()->getId() == $tblSubject->getId()
            && ($tblPersonTeacher = $tblDivisionCourse->getFirstDivisionTeacher())
        ) {
            $teacherList[] = $tblPersonTeacher->getFullName();
        } elseif (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, null, $tblDivisionCourse, $tblSubject))) {
            foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                if (($tblPersonTeacher = $tblTeacherLectureship->getServiceTblPerson())) {
                    $teacherAcronym = '';
                    if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPersonTeacher))) {
                        $teacherAcronym = $tblTeacher->getAcronym();
                    }

                    // Fach // Kurse -> Lehrer
                    $teacherList[$tblPersonTeacher->getId()] = $tblPersonTeacher->getFullName() . ($teacherAcronym ? ' (' . $teacherAcronym . ')' : '');
                }
            }
        }

        return $teacherList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function getSubjectsAndLectureshipByDivisionForDownload(TblDivisionCourse $tblDivisionCourse): array
    {
        $dataList = array();

        if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByDivisionCourse($tblDivisionCourse, false))
            && ($tblDivisionCourseListStudents = DivisionCourse::useService()->getDivisionCourseListByStudentsInDivisionCourse($tblDivisionCourse))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $tblDivisionCourseListStudents = $this->getSorter($tblDivisionCourseListStudents)->sortObjectBy('Name', new StringNaturalOrderSorter());
            foreach ($tblSubjectList as $tblSubject) {
                $teacherNameList = array();
                /** @var TblDivisionCourse $tblDivisionCourseStudent */
                foreach ($tblDivisionCourseListStudents as $tblDivisionCourseStudent) {
                    if (($tempList = $this->getSubjectTeacherNameListByDivisionCourse($tblDivisionCourseStudent, $tblSubject, $tblYear))) {
                        foreach ($tempList as $personId => $name) {
                            if (!isset($teacherNameList[$personId])) {
                                $teacherNameList[$personId] = $name;
                            }
                        }
                    }
                }

                $dataList[$tblSubject->getAcronym()] = array(
                    'Subject' => $tblSubject->getDisplayName(),
                    'TeacherArray' => $teacherNameList
                );
            }

            ksort($dataList);
        }

        return $dataList;
    }

    /**
     * @param array $tblLessonContentList
     * @param array $tblYearList
     * @param TblPerson $tblPerson
     * @param TblDivisionCourse|null $tblDivisionCourseFilter
     * @param TblSubject|null $tblSubjectFilter
     * @param DateTime|null $useStartDate
     * @param bool $isBreakByFoundMissing
     *
     * @return bool
     */
    public function addMissingLessonContentList(array &$tblLessonContentList, array $tblYearList, TblPerson $tblPerson,
        ?TblDivisionCourse $tblDivisionCourseFilter = null, ?TblSubject $tblSubjectFilter = null,
        ?DateTime $useStartDate = null, bool $isBreakByFoundMissing = false): bool
    {
        foreach ($tblYearList as $tblYear) {
            $beforeToday = (new DateTime('today'))->modify('-1 day');
            if ($useStartDate) {
                $startDate = clone $useStartDate;
                $endDate = $beforeToday;
            } else {
                /** @var DateTime $startDate */
                list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);

                if ($beforeToday < $endDate) {
                    $endDate = $beforeToday;
                }
            }

            $companies = [];
            $tblDivisionCourseList = [];
            if ($tblDivisionCourseFilter) {
                $tblDivisionCourseList[$tblDivisionCourseFilter->getId()] = $tblDivisionCourseFilter;
                $this->setCompanies($tblDivisionCourseFilter, $companies);
            }

            $tblTimetableList = Timetable::useService()->getTimetableListBetween($startDate, $endDate);
            $timetables = [];
            foreach ($tblTimetableList as $tblTimetable) {
                for ($day = 1; $day < 7; $day++) {
                    if (($tblTimeTableNodeList = Timetable::useService()->getTimeTableNodeListByTeacher($tblPerson, $tblTimetable,
                        $day, $tblDivisionCourseFilter, $tblSubjectFilter))
                    ) {
                        // php 8.3 ??
                        if (!isset($timetables[$day])) {
                            $timetables[$day] = [];
                        }

                        $tblDivisionCourseListForDay = [];
                        foreach ($tblTimeTableNodeList as $tblTimeTableNodeTemp) {
                            if (($tblDivisionCourseTemp = $tblTimeTableNodeTemp->getServiceTblCourse())) {
                                // Klassen hinzufügen für Schule (Ferien)
                                if (!isset($tblDivisionCourseList[$tblDivisionCourseTemp->getId()])) {
                                    $tblDivisionCourseList[$tblDivisionCourseTemp->getId()] = $tblDivisionCourseTemp;
                                    $this->setCompanies($tblDivisionCourseTemp, $companies);
                                }

                                // Klassenliste für den Wochentag für Vertretungsplan
                                if (!isset($tblDivisionCourseListForDay[$tblDivisionCourseTemp->getId()])) {
                                    $tblDivisionCourseListForDay[$tblDivisionCourseTemp->getId()] = $tblDivisionCourseTemp;
                                }
                            }
                        }

                        $timetables[$day][$tblTimetable->getId()] = [
                            'FromDate' => $tblTimetable->getDateFrom(true),
                            'ToDate' => $tblTimetable->getDateTo(true),
                            'tblTimetableNodeList' => $tblTimeTableNodeList,
                            'tblTimetable' => $tblTimetable,
                            'tblDivisionCourseListForDay' => $tblDivisionCourseListForDay
                        ];
                    }
                }
            }

            $foundMissing = $this->runDays($tblLessonContentList, $startDate, $endDate, $tblPerson, $tblYear, $timetables, $companies, $isBreakByFoundMissing);
            if ($isBreakByFoundMissing && $foundMissing) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $tblLessonContentList
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param array $timetables
     * @param array $companies
     * @param bool $isBreakByFoundMissing
     *
     * @return bool
     */
    private function runDays(array &$tblLessonContentList, DateTime $startDate, DateTime $endDate,
        TblPerson $tblPerson, TblYear $tblYear, array $timetables, array $companies, bool $isBreakByFoundMissing): bool
    {
        $intervall = new DateInterval('P1D');
        while ($startDate <= $endDate) {
            $dayOfWeek = (int)$startDate->format('w');
            if (isset($timetables[$dayOfWeek])) {
                $holidays = [];
                $fullTimes = [];
                foreach ($timetables[$dayOfWeek] as $timetableArray) {
                    if ($timetableArray['FromDate'] <= $startDate && $timetableArray['ToDate'] >= $startDate) {
                        // vertretungsplan für Tag abhängig von Klassen an dem Tag
                        $tblTimetableReplacementList = [];
                        // zusätzlich Stunden im vertretungsplan
                        $tblTimetableReplacementAdditionalList = [];
                        foreach ($timetableArray['tblDivisionCourseListForDay'] as $tblDivisionCourseTemp) {
                            if (($tempList = Timetable::useService()->getTimetableReplacementByTime($startDate, null, $tblDivisionCourseTemp))) {
                                foreach ($tempList as $item) {
                                    $tblTimetableReplacementList[$item->getIdentifier()] = $item;
                                    // zusätzlich Stunden im vertretungsplan
                                    if (($tblPersonReplacement = $item->getServiceTblPerson())
                                        && $tblPersonReplacement->getId() == $tblPerson->getId()
                                    ) {
                                        $tblTimetableReplacementAdditionalList[$item->getIdentifier()] = $item;
                                    }
                                }
                            }
                        }

                        /** @var TblTimetableNode $tblTimetableNode */
                        foreach ($timetableArray['tblTimetableNodeList'] as $tblTimetableNode) {
                            if (($tblDivisionCourse = $tblTimetableNode->getServiceTblCourse())
                                && ($tblSubject = $tblTimetableNode->getServiceTblSubject())
                            ) {
                                // SekII-Kurse ignorieren
                                if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                                    continue;
                                }

                                // ganztägig prüfen
                                if (!isset($fullTimes[$tblDivisionCourse->getId()])) {
                                    $fullTimes[$tblDivisionCourse->getId()] = Digital::useService()->getFullTimeContentListByDivisionCourseAndDate(
                                        $tblDivisionCourse, $startDate);
                                }
                                if ($fullTimes[$tblDivisionCourse->getId()]) {
                                    continue;
                                }

                                // Ferien können erst über die Schulart geprüft werden
                                // bereits geprüfte Klassen speichern in liste
                                if (!isset($holidays[$tblDivisionCourse->getId()])) {
                                    $holidays[$tblDivisionCourse->getId()] = Term::useService()->getHolidayByDay(
                                        $tblYear, $startDate, $companies[$tblDivisionCourse->getId()] ?? null);
                                }
                                if ($holidays[$tblDivisionCourse->getId()]) {
                                    continue;
                                }

                                // Woche prüfen
                                if ($tblTimetableNode->getWeek()
                                    && !Timetable::useService()->getTimetableWeekByTimeTableAndWeekAndDate(
                                        $timetableArray['tblTimetable'], $tblTimetableNode->getWeek(), Timetable::useService()->getStartDateOfWeek($startDate))
                                ) {
                                    continue;
                                }

                                // prüfen, ob es den Eintrag gibt
                                $identifier = $startDate->format('d.m.Y') . '_' . $tblDivisionCourse->getId() . '_' . $tblSubject->getId() . '_' . $tblTimetableNode->getHour();
                                $isMissing = !isset($tblLessonContentList[$identifier]);
                                $isReplacement = isset($tblTimetableReplacementList[$identifier]);
                                // ist keine zusätzliche Stunde im vertretungsplan
                                unset($tblTimetableReplacementAdditionalList[$identifier]);

                                if ($isMissing) {
                                    $tblPerson = $tblTimetableNode->getServiceTblPerson();

                                    $tblLessonContent = new TblLessonContent();
                                    // muss eigene DateTime sein
                                    $tblLessonContent->setDate(clone $startDate);
                                    $tblLessonContent->setServiceTblDivisionCourse($tblDivisionCourse);
                                    $tblLessonContent->setLesson($tblTimetableNode->getHour());
                                    $tblLessonContent->setServiceTblPerson($tblPerson ?: null);
                                    $tblLessonContent->setHomework('');
                                    $tblLessonContent->setContent('');

                                    // normaler Stundenplan
                                    if (!$isReplacement) {
                                        $tblLessonContent->setServiceTblSubject($tblSubject);
                                        $tblLessonContent->setRoom($tblTimetableNode->getRoom());

                                        $tblLessonContentList[] = $tblLessonContent;
                                        if ($isBreakByFoundMissing) {
                                            return true;
                                        }
                                    // Vertretungsplan
                                    } else {
                                        /** @var TblTimetableReplacement $tblTimetableReplacement */
                                        $tblTimetableReplacement = $tblTimetableReplacementList[$identifier];
                                        $tblPersonReplacement = $tblTimetableReplacement->getServiceTblPerson();
                                        if ($tblPersonReplacement && $tblPerson && $tblPersonReplacement->getId() == $tblPerson->getId()) {
                                            $tblLessonContent->setServiceTblSubject($tblTimetableReplacement->getServiceTblSubject() ?: null);
                                            $tblLessonContent->setRoom($tblTimetableReplacement->getRoom());

                                            $tblLessonContent->setServiceTblSubstituteSubject($tblTimetableReplacement->getServiceTblSubstituteSubject() ?: null);
                                            $tblLessonContent->setIsCanceled((bool) $tblTimetableReplacement->getIsCanceled());

                                            $tblLessonContentList[] = $tblLessonContent;
                                            if ($isBreakByFoundMissing) {
                                                return true;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // zusätzliche Stunden im Vertretungsplan hinzufügen
                        foreach ($tblTimetableReplacementAdditionalList as $tblTimetableReplacement) {
                            // SekII-Kurse ignorieren
                            if (($tblDivisionCourseReplacement = $tblTimetableReplacement->getServiceTblCourse())
                                && $tblDivisionCourseReplacement->getType()->getIsCourseSystem()
                            ) {
                                continue;
                            }

                            $tblLessonContent = new TblLessonContent();
                            // muss eigene DateTime sein
                            $tblLessonContent->setDate(clone $startDate);
                            $tblLessonContent->setServiceTblDivisionCourse($tblTimetableReplacement->getServiceTblCourse() ?: null);
                            $tblLessonContent->setLesson($tblTimetableReplacement->getHour());
                            $tblLessonContent->setServiceTblPerson($tblPerson ?: null);
                            $tblLessonContent->setHomework('');
                            $tblLessonContent->setContent('');
                            $tblLessonContent->setServiceTblSubject($tblTimetableReplacement->getServiceTblSubject() ?: null);
                            $tblLessonContent->setRoom($tblTimetableReplacement->getRoom());
                            $tblLessonContent->setServiceTblSubstituteSubject($tblTimetableReplacement->getServiceTblSubstituteSubject() ?: null);
                            $tblLessonContent->setIsCanceled((bool) $tblTimetableReplacement->getIsCanceled());

                            $tblLessonContentList[] = $tblLessonContent;
                            if ($isBreakByFoundMissing) {
                                return true;
                            }
                        }
                    }
                }
            }

            $startDate = $startDate->add($intervall);
        }

        return false;
    }

    private function setCompanies(TblDivisionCourse $tblDivisionCourse, &$companies): void
    {
        // was ist bei mehreren Schulen in einer Klasse?
        if (($tblCompanyList = $tblDivisionCourse->getCompanyListFromStudents())) {
            $tblCompany = reset($tblCompanyList);
            $companies[$tblDivisionCourse->getId()] = $tblCompany;
        }
    }

    /**
     * @param array $tblLessonContentList
     *
     * @return array
     */
    public function getLessonContentListWithIdentifier(array $tblLessonContentList): array
    {
        $list = [];
        /** @var TblLessonContent $tblLessonContent */
        foreach ($tblLessonContentList as $tblLessonContent) {
            $list[$tblLessonContent->getIdentifier()] = $tblLessonContent;
        }

        return $list;
    }

    /**
     * @param int $days
     *
     * @return string
     */
    public function getMissingDigital(int $days): string
    {
        $today = new DateTime('today');
        $endDate = clone $today;
        $startDate = $today->sub(new DateInterval('P' . $days . 'D'));

        if (($tblPerson = Account::useService()->getPersonByLogin())
            && ($tblYearList = Term::useService()->getYearByNow())
        ) {
            $tblLessonContentList = Digital::useService()->getLessonContentAllByTeacherAndBetween($tblPerson, $startDate, $endDate);

            // setze Identifier für Ermittlung fehlender Einträge
            $tblLessonContentList = Digital::useService()->getLessonContentListWithIdentifier($tblLessonContentList);

            // ergänzt fehlende Einträge an Hand vom Stundenplan und Vertretungsplan
            if (Digital::useService()->addMissingLessonContentList($tblLessonContentList, $tblYearList, $tblPerson, null, null, $startDate, true)) {
                $link = new Standard('', '/Education/ClassRegister/Digital/TeacherView',
                    new Extern(), [], 'Zu Fehlende Klassentagebuch-Einträge wechseln');

                return new WarningMessage('Sie haben im Digitalen Klassenbuch fehlende Einträge in den letzten ' . $days . ' Tagen. '
                    . $link, new Exclamation());
            }
        }

        return '';
    }
}