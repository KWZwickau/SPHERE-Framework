<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Frontend;

use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Api\Education\ClassRegister\ApiDigital;
use SPHERE\Application\Education\Certificate\Prepare\View;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Stage;

class FrontendSelectDivisionCourse extends FrontendMail
{
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
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendTeacherSelectDivision(bool $IsAllYears = false, $YearId = null): Stage
    {
        $Stage = new Stage('Digitales Klassenbuch', 'Kurs auswählen');

        $hasLastYearsTemp = ($tblSetting = ConsumerSetting::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'HasTeacherAccessToLastYearDigital'))
            && $tblSetting->getValue();

        Digital::useService()->setHeaderButtonList($Stage, View::TEACHER, self::BASE_ROUTE);
        $yearFilterList = array();
        $buttonList = Digital::useService()->setYearGroupButtonList(self::BASE_ROUTE . '/Teacher', $IsAllYears, $YearId, false, true, $yearFilterList, $hasLastYearsTemp, true);

        $table = false;
        if (($tblPerson = Account::useService()->getPersonByLogin())) {
            $dataList = array();
            $tblDivisionCourseList = array();
            $checkedDivisionCourseList = array();
            if ($yearFilterList) {
                foreach ($yearFilterList as $tblYear) {
                    // Klassenlehrer
                    if (($tempList = DivisionCourse::useService()->getDivisionCourseListByDivisionTeacher($tblPerson, $tblYear))) {
                        foreach ($tempList as $temp) {
                            if (!isset($tblDivisionCourseList[$temp->getId()])
                                && $temp->getIsDivisionOrCoreGroup()
                            ) {
                                $tblDivisionCourseList[$temp->getId()] = $temp;
                                $checkedDivisionCourseList[$temp->getId()] = $temp;
                            }
                        }
                    }

                    // Lehraufträge -> dann alle Schüler des Lehrauftrags -> alle Klassen und Stammgruppen der Schüler
                    if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPerson))) {
                        foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                            if (($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())
                                && !isset($tblDivisionCourseList[$tblDivisionCourse->getId()])
                                && !isset($checkedDivisionCourseList[$tblDivisionCourse->getId()])
                            ) {
                                // SekII-Kurse
                                if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                                    $tblDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                                    $checkedDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                                }

                                if (($tblDivisionCourseListFromStudents = DivisionCourse::useService()->getDivisionCourseListByStudentsInDivisionCourse($tblDivisionCourse))) {
                                    foreach ($tblDivisionCourseListFromStudents as $tblDivisionCourseStudent) {
                                        if (($tblDivisionCourseStudent->getIsDivisionOrCoreGroup()
                                                || $tblDivisionCourseStudent->getType()->getIsCourseSystem())
                                            && !isset($tblDivisionCourseList[$tblDivisionCourseStudent->getId()])
                                        ) {
                                            $tblDivisionCourseList[$tblDivisionCourseStudent->getId()] = $tblDivisionCourseStudent;
                                            $checkedDivisionCourseList[$tblDivisionCourseStudent->getId()] = $tblDivisionCourseStudent;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Eigene Lerngruppen mit Kursheft
                    if (($tblDivisionCourseListTeacherGroup = DivisionCourse::useService()->getTeacherGroupListByTeacherAndYear($tblPerson, $tblYear))) {
                        foreach ($tblDivisionCourseListTeacherGroup as $teacherGroup) {
                            if ($teacherGroup->getIsDigital()) {
                                $tblDivisionCourseList[$teacherGroup->getId()] = $teacherGroup;
                            }
                        }
                    }
                }
            }

            /** @var TblDivisionCourse $tblDivisionCourse */
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                if ($tblDivisionCourse->getType()->getIsCourseSystem()
                    || $tblDivisionCourse->getIsDigital()
                ) {
                    $route = self::BASE_ROUTE . '/CourseContent';
                } elseif (
                    // SSW-2850 Ausnahme für KG DKC
                    !str_contains($tblDivisionCourse->getName(), 'DKC')
                    && DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse)
                ) {
                    $route = self::BASE_ROUTE . '/SelectCourse';
                } else {
                    $route = self::BASE_ROUTE . '/LessonContent';
                }

                $teachers = $tblDivisionCourse->getDivisionTeacherNameListString();
                if (!$teachers && $tblDivisionCourse->getType()->getIsCourseSystem() && ($tblSubject = $tblDivisionCourse->getServiceTblSubject())) {
                    $teachers = DivisionCourse::useService()->getSubjectTeachers($tblDivisionCourse, $tblSubject);
                }

                $dataList[] = array(
                    'Year' => $tblDivisionCourse->getYearName(),
                    'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                    'DivisionCourseType' => $tblDivisionCourse->getTypeName(),
                    'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                    'Teachers' => $teachers,
                    'Option' => new Standard(
                        '',
                        $route,
                        new Select(),
                        array(
                            'DivisionCourseId' => $tblDivisionCourse->getId(),
                            'BasicRoute' => self::BASE_ROUTE . '/Teacher'
                        ),
                        'Auswählen'
                    )
                );
            }

            if (empty($dataList)) {
                $table = new Warning('Keine entsprechenden Lehraufträge vorhanden.', new Exclamation());
            } else {
                $table = new TableData($dataList, null, array(
                    'Year' => 'Schuljahr',
                    'DivisionCourse' => 'Kurs',
                    'DivisionCourseType' => 'Kurs-Typ',
                    'SchoolTypes' => 'Schularten',
                    'Teachers' => 'Leiter',
                    'Option' => ''
                ), array(
                    'order' => array(
                        array('0', 'desc'),
                        array('1', 'asc'),
                    ),
                    'columnDefs' => array(
                        array('type' => 'natural', 'targets' => 1),
                        array('orderable' => false, 'width' => '1%', 'targets' => -1),
                        array('searchable' => false, 'targets' => -1),
                    ),
                ));
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn($this->getButtons('/Education/ClassRegister/Digital/Teacher'))),
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        $table
                            ? new LayoutColumn(array($table))
                            : null
                    ))
                ))
            ))
        );

        return $Stage;
    }

    private function getButtons(string $Route): array
    {
        $buttons = [];
        $buttons[] = $this->getButton('Auswahl', '/Education/ClassRegister/Digital/Teacher', new Select(),
            $Route == '/Education/ClassRegister/Digital/Teacher');
        $buttons[] = $this->getButton('Fehlende Klassentagebuch-Einträge', '/Education/ClassRegister/Digital/TeacherView', new ListingTable(),
            $Route == '/Education/ClassRegister/Digital/TeacherView');
        $buttons[] = new Ruler();

        return $buttons;
    }

    private function getButton(string $name, string $route, $icon, bool $isSelected): Standard
    {
        return new Standard(
            $isSelected ? new Info(new Bold($name)) : $name,
            $route,
            $icon
        );
    }

    /**
     * @param $YearId
     *
     * @return Stage
     */
    public function frontendTeacherView($YearId = null): Stage
    {
        $Stage = new Stage('Digitales Klassenbuch', 'Fehlende Klassentagebuch-Einträge');

        $global = $this->getGlobal();
        $Filter = ['OnlyMissing' => 1];
        $global->POST['Filter']['OnlyMissing'] = 1;
        // Schulleitung und Support kann den Lehrer auswählen
        $hasRightHeadmaster = Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting');
        // Person eintragen, falls Person ein Lehrer ist
        if ($hasRightHeadmaster
            && ($tblPerson = Account::useService()->getPersonByLogin())
            && ($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER))
            && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)
        ) {
            $global->POST['Filter']['tblPerson'] = $tblPerson->getId();
            $Filter['tblPerson'] = $tblPerson->getId();
        }

        $global->savePost();
        // save filter as json
        Consumer::useService()->createAccountSetting('DigitalTeacherViewFilter', json_encode($Filter));

        Digital::useService()->setHeaderButtonList($Stage, View::TEACHER, self::BASE_ROUTE);
        $yearFilterList = array();
        $hasLastYearsTemp = ($tblSetting = ConsumerSetting::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'HasTeacherAccessToLastYearDigital'))
            && $tblSetting->getValue();
        $buttonList = Digital::useService()->setYearGroupButtonList(self::BASE_ROUTE . '/TeacherView', false, $YearId, false, true, $yearFilterList, $hasLastYearsTemp);

        $tblYear = Term::useService()->getYearById($YearId);

        $Stage->setContent(
            new Layout(new LayoutGroup(array(
                new LayoutRow(new LayoutColumn($this->getButtons('/Education/ClassRegister/Digital/TeacherView'))),
                new LayoutRow(new LayoutColumn($buttonList)),
                new LayoutRow(new LayoutColumn(
                    ApiDigital::receiverModal()
                    . ApiAbsence::receiverModal()
                    . new Panel(new Filter() . ' Filter', Digital::useFrontend()->formTeacherViewFilter($tblYear ?: null), Panel::PANEL_TYPE_INFO)
                    . ApiDigital::receiverBlock(Digital::useFrontend()->loadTeacherViewContent($YearId, $Filter), 'TeacherViewContent')
                ))
            )))
        );

        return $Stage;
    }

    /**
     * @param bool $IsAllYears
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendHeadmasterSelectDivision(bool $IsAllYears = false, $YearId = null): Stage
    {
        $Stage = new Stage('Digitales Klassenbuch', 'Kurs auswählen');
        Digital::useService()->setHeaderButtonList($Stage, View::HEADMASTER, self::BASE_ROUTE);

        $hasLastYearsTemp = ($tblSetting = ConsumerSetting::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'HasTeacherAccessToLastYearDigital'))
            && $tblSetting->getValue();

        $yearFilterList = array();
        // nur Schulleitung darf History (Alle Schuljahre) sehen
        $buttonList = Digital::useService()->setYearGroupButtonList(self::BASE_ROUTE . '/Headmaster',
            $IsAllYears, $YearId, Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting'), true, $yearFilterList, $hasLastYearsTemp, true);

        $dataList = array();
        $tblDivisionCourseList = Digital::useService()->getDivisionCourseListForDigital($yearFilterList, $IsAllYears, true);

        foreach ($tblDivisionCourseList as $tblDivisionCourse) {
            if ($tblDivisionCourse->getIsDigital()) {
                $route = self::BASE_ROUTE . '/CourseContent';
            } elseif (
                // SSW-2850 Ausnahme für KG DKC
                !str_contains($tblDivisionCourse->getName(), 'DKC')
                && DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse)
            ) {
                $route = self::BASE_ROUTE . '/SelectCourse';
            } else {
                $route = self::BASE_ROUTE . '/LessonContent';
            }

            $dataList[] = array(
                'Year' => $tblDivisionCourse->getYearName(),
                'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                'DivisionCourseType' => $tblDivisionCourse->getTypeName(),
                'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                'Teachers' => $tblDivisionCourse->getDivisionTeacherNameListString(),
                'Option' => new Standard(
                    '',
                    $route,
                    new Select(),
                    array(
                        'DivisionCourseId' => $tblDivisionCourse->getId(),
                        'BasicRoute' => self::BASE_ROUTE . '/Headmaster'
                    ),
                    'Auswählen'
                )
            );
        }

        $table = new TableData($dataList, null, array(
            'Year' => 'Schuljahr',
            'DivisionCourse' => 'Kurs',
            'DivisionCourseType' => 'Kurs-Typ',
            'SchoolTypes' => 'Schularten',
            'Teachers' => 'Leiter',
            'Option' => ''
        ), array(
            'order' => array(
                array('0', 'desc'),
                array('1', 'asc'),
            ),
            'columnDefs' => array(
                array('type' => 'natural', 'targets' => 1),
                array('orderable' => false, 'width' => '1%', 'targets' => -1),
                array('searchable' => false, 'targets' => -1),
            ),
        ));

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
}