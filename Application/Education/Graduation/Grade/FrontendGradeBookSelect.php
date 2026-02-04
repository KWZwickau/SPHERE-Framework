<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;

abstract class FrontendGradeBookSelect extends FrontendExamGrade
{
    /**
     * @param null $Filter
     * @param bool|null $DontShowDivisionTeacherGradeBooks
     *
     * @return string
     */
    public function loadViewGradeBookSelect($Filter = null, ?bool $DontShowDivisionTeacherGradeBooks = null): string
    {
        $role = Grade::useService()->getRole();
        $isTeacher = $role == "Teacher";
        $form = null;
        if (($tblYearList = Grade::useService()->getSelectedYearList())) {
            // Lehrer
            if ($isTeacher) {
                if ($DontShowDivisionTeacherGradeBooks === null) {
                    $ShowDivisionTeacherGradeBooks = !Consumer::useService()->getAccountSettingValue("DontShowDivisionTeacherGradeBooks");
                } else {
                    $ShowDivisionTeacherGradeBooks = !$DontShowDivisionTeacherGradeBooks;
                }
                $content = $this->getSelectGradeBookTeacher($tblYearList, $ShowDivisionTeacherGradeBooks);
                if ($ShowDivisionTeacherGradeBooks) {
                    $global = $this->getGlobal();
                    $global->POST['Data']['ShowDivisionTeacherGradeBooks'] = 1;
                    $global->savePost();
                }

                $form = (new Form(new FormGroup(new FormRow(new FormColumn(
                    (new CheckBox('Data[ShowDivisionTeacherGradeBooks]', new Bold('Notenbücher über Kursleiter mit anzeigen'), 1))
                        ->ajaxPipelineOnChange(array(ApiGradeBook::pipelineChangeShowDivisionTeacherGradeBooks()))
                )))))->disableSubmitAction();

            // Schulleitung, Integrationsbeauftragte
            } else {
                $content = $this->getSelectGradeBookHeadmaster($Filter);
            }
        } else {
            $content = new Danger("Schuljahr nicht gefunden", new Exclamation());
        }

        return new Title("Notenbuch", "Auswählen")
            . ($form ?: '')
            . $content;
    }

    /**
     * @param $Filter
     *
     * @return string
     */
    private function getSelectGradeBookHeadmaster($Filter): string
    {
        // Filter bei mehr als einer Mandanten-Schulart anzeigen
        $filter = '';
        if (($tblSchoolTypeList = School::useService()->getConsumerSchoolTypeAll())) {
            if (count($tblSchoolTypeList) == 1) {
                if (empty($Filter)) {
                    $Filter = array();
                    $Filter['SchoolType'] = (current($tblSchoolTypeList))->getId();
                }
            } else {
                $filter = new Panel(
                    new Filter() . " Filter",
                    $this->formFilter($Filter),
                    Panel::PANEL_TYPE_INFO
                );
            }
        }

        return
            $filter
            . ApiGradeBook::receiverBlock($this->loadGradeBookSelectFilterContent($Filter), "GradeBookSelectFilterContent");
    }

    /**
     * @param null $Filter
     *
     * @return Form
     */
    private function formFilter($Filter = null): Form
    {
        if ($Filter) {
            $global = $this->getGlobal();
            if (isset($Filter["SchoolType"])) {
                $global->POST["Filter"]["SchoolType"] = $Filter["SchoolType"];
            }
            $global->savePost();
        }

        $tblSchoolTypeList = School::useService()->getConsumerSchoolTypeAll();

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Filter[SchoolType]', 'Schulart', array('{{ Name }}' => $tblSchoolTypeList)))
                        ->ajaxPipelineOnChange(ApiGradeBook::pipelineLoadGradeBookSelectFilterContent($Filter))
                    , 12),
            )),
        )));
    }

    /**
     * @param $Filter
     *
     * @return string
     */
    public function loadGradeBookSelectFilterContent($Filter): string
    {
        $tblSchoolType = isset($Filter["SchoolType"]) ? Type::useService()->getTypeById($Filter["SchoolType"]) : false;
        if ($tblSchoolType
            && ($tblYearList = Grade::useService()->getSelectedYearList())
        ) {
            $dataList = array();
            foreach ($tblYearList as $tblYear) {
                if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear))) {
                    foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                        if (!($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())
                            || !isset($tblSchoolTypeList[$tblSchoolType->getId()])
                        ) {
                            continue;
                        }

                        $this->setDivisionCourseSelectDataList($dataList, $tblDivisionCourse, $tblYear, null, null, $Filter);
                    }
                }
            }

            // bei der DataTable dürfen als Key nur Zahlen verwenden
            $dataList = array_values($dataList);
            $content = $this->getTable($dataList);

        } else {
            $content = new Warning("Bitte filtern Sie nach einer Schulart.", new Exclamation());
        }

        return $content;
    }

    /**
     * @param array $dataList
     *
     * @return TableData
     */
    private function getTable(array $dataList): TableData
    {
        return new TableData(
            $dataList,
            null,
            array(
                'Year' => 'Schuljahr',
                'DivisionCourse' => 'Kurs',
                'CourseType' => 'Kurs-Typ',
                'Subject' => 'Fach',
                'SubjectTeachers' => 'Fachlehrer',
                'Option' => ''
            ),
            array(
                'order' => array(
                    array('0', 'desc'),
                    array('1', 'asc'),
                    array('3', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 1),
                    array('orderable' => false, 'width' => '30px', 'targets' => -1),
                    array('searchable' => false, 'targets' => -1),
                )
            )
        );
    }

    /**
     * @param $tblYearList
     * @param bool $showDivisionTeacherGradeBooks
     *
     * @return string
     */
    private function getSelectGradeBookTeacher($tblYearList, bool $showDivisionTeacherGradeBooks): string
    {
        if (($tblPersonLogin = Account::useService()->getPersonByLogin())) {
            $divisionCourseSubjectList = array();
            $dataList = array();
            foreach ($tblYearList as $tblYear) {
                if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPersonLogin))) {
                    // Lehraufträge
                    foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                        $this->setTeacherLectureshipSelectData($dataList, $tblTeacherLectureship, $divisionCourseSubjectList);
                    }

                    // Klassenlehrer aus den Lehraufträgen der Lehrer
                    if ($showDivisionTeacherGradeBooks
                        && ($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByDivisionTeacher($tblPersonLogin, $tblYear, true))
                    ) {
                        foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                            if (DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse)) {
                                // SekII
                                if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByStudentDivisionCourseAndPeriod($tblDivisionCourse,
                                    1))) {
                                    foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                        if (($tblDivisionCourseSubject = $tblStudentSubject->getTblDivisionCourse())
                                            && !isset($divisionCourseSubjectList[$tblDivisionCourseSubject->getId()])
                                        ) {
                                            $this->setDivisionCourseSelectDataList($dataList, $tblDivisionCourseSubject, $tblYear, $tblPersonLogin,
                                                $divisionCourseSubjectList);
                                        }
                                    }
                                }
                            } else {
                                // SekI
                                $this->setDivisionCourseSelectDataList($dataList, $tblDivisionCourse, $tblYear, $tblPersonLogin, $divisionCourseSubjectList);
                            }
                        }
                    }
                }
            }
            // bei der DataTable dürfen als Key nur Zahlen verwenden
            $dataList = array_values($dataList);
            $content = $this->getTable($dataList);
        } else {
            $content = new Warning("Keine Lehraufträge vorhanden", new Exclamation());
        }

        return $content;
    }

    /**
     * @param array $dataList
     * @param TblTeacherLectureship $tblTeacherLectureship
     * @param $divisionCourseSubjectList
     * @param null $Filter
     */
    private function setTeacherLectureshipSelectData(array &$dataList, TblTeacherLectureship $tblTeacherLectureship, &$divisionCourseSubjectList, $Filter = null)
    {
        if (($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())
            && ($tblSubject = $tblTeacherLectureship->getServiceTblSubject())
        ) {
            // prüfen, ob ein Schüler überhaupt das Fach mit Benotung hat nicht bei Schulleitung aus Performance gründen
            if (is_array($divisionCourseSubjectList) && !isset($divisionCourseSubjectList[$tblDivisionCourse->getId()])) {
                $divisionCourseSubjectList[$tblDivisionCourse->getId()] = DivisionCourse::useService()->getSubjectListByDivisionCourse($tblDivisionCourse);
            }
            if ($divisionCourseSubjectList == null
                || isset($divisionCourseSubjectList[$tblDivisionCourse->getId()][$tblSubject->getId()])
                // Mandanten-Einstellung: Bei Kopfnotenaufträgen können auch Kopfnoten für Fächer vergeben werden, welche nicht benotet werden
                || (($tblSetting = Consumer::useService()->getSetting(
                        'Education', 'Graduation', 'Evaluation', 'HasBehaviorGradesForSubjectsWithNoGrading'
                    ))
                    && $tblSetting->getValue()
                    && Grade::useService()->getBehaviorTaskListByDivisionCourse($tblDivisionCourse)
                )
            ) {
                $key = $tblDivisionCourse->getId() . '_' . $tblSubject->getId();
                if (!isset($dataList[$key])) {
                    $dataList[$key] = array(
                        'Year' => $tblTeacherLectureship->getYearName(),
                        'DivisionCourse' => $tblTeacherLectureship->getCourseName(),
                        'CourseType' => $tblDivisionCourse->getTypeName(),
                        'Subject' => $tblTeacherLectureship->getSubjectName(),
                        'SubjectTeachers' => $tblTeacherLectureship->getSubjectTeachers(),
                        'Option' => (new Standard("", ApiGradeBook::getEndpoint(), new Check(), array(), "Auswählen"))
                            ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewGradeBookContent($tblDivisionCourse->getId(), $tblSubject->getId(), $Filter))
                    );
                }
            }
        }
    }

    /**
     * @param array $dataList
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     * @param null $Filter
     */
    private function setDivisionCourseSelectData(array &$dataList, TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject, $Filter = null)
    {
        $key = $tblDivisionCourse->getId() . '_' . $tblSubject->getId();
        if (!isset($dataList[$key])) {
            $dataList[$key] = array(
                'Year' => $tblDivisionCourse->getYearName(),
                'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                'CourseType' => $tblDivisionCourse->getTypeName(),
                'Subject' => $tblSubject->getDisplayName(),
                'SubjectTeachers' => $tblDivisionCourse->getDivisionTeacherNameListString(', '),
                'Option' => (new Standard("", ApiGradeBook::getEndpoint(), new Check(), array(), "Auswählen"))
                    ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewGradeBookContent($tblDivisionCourse->getId(), $tblSubject->getId(), $Filter))
            );
        }
    }

    /**
     * @param array $dataList
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblYear $tblYear
     * @param TblPerson|null $tblPerson
     * @param null $Filter
     *
     * @return void
     */
    private function setDivisionCourseSelectDataList(
        array &$dataList,
        TblDivisionCourse $tblDivisionCourse,
        TblYear $tblYear,
        ?TblPerson $tblPerson = null,
        $divisionCourseSubjectList = null,
        $Filter = null
    ) {
        // Lerngruppe
        if (($tblSubject = $tblDivisionCourse->getServiceTblSubject())
            && $tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_TEACHER_GROUP
        ) {
            $this->setDivisionCourseSelectData($dataList, $tblDivisionCourse, $tblSubject, $Filter);
            // alle Lehraufträge des Kurses
        } elseif (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, null, $tblDivisionCourse))) {
            foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                // eigene Lehraufträge bei Klassenlehrern ignorieren
                if (($tblTeacher = $tblTeacherLectureship->getServiceTblPerson()) && $tblPerson
                    && $tblTeacher->getId() == $tblPerson->getId()
                ) {
                    continue;
                }

                $this->setTeacherLectureshipSelectData($dataList, $tblTeacherLectureship, $divisionCourseSubjectList, $Filter);
            }
        }
    }
}