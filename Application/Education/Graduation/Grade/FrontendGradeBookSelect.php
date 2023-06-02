<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\School\School;
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

abstract class FrontendGradeBookSelect extends FrontendBasic
{
    /**
     * @param null $Filter
     *
     * @return string
     */
    public function loadViewGradeBookSelect($Filter = null): string
    {
        $role = Grade::useService()->getRole();
        $isTeacher = $role == "Teacher";
        if (($tblYear = Grade::useService()->getYear())) {
            // Lehrer
            if ($isTeacher) {
                $content = $this->getSelectGradeBookTeacher($tblYear);
                // Schulleitung, Integrationsbeauftragte
            } else {
                $content = $this->getSelectGradeBookHeadmaster($Filter);
            }
        } else {
            $content = new Danger("Schuljahr nicht gefunden", new Exclamation());
        }

        return new Title("Notenbuch", "Auswählen") . $content;
    }

    /**
     * @param $Filter
     *
     * @return string
     */
    private function getSelectGradeBookHeadmaster($Filter): string
    {
        return
            new Panel(
                new Filter() . " Filter",
                $this->formFilter($Filter),
                Panel::PANEL_TYPE_INFO
            )
            . ApiGradeBook::receiverBlock($Filter == null ? $this->loadGradeBookSelectFilterContent($Filter) : "", "GradeBookSelectFilterContent");
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
            && ($tblYear = Grade::useService()->getYear())
        ) {
            $dataList = array();
            if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear))) {
                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                    if (!($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())
                        || !isset($tblSchoolTypeList[$tblSchoolType->getId()])
                    ) {
                        continue;
                    }

                    $this->setDivisionCourseSelectDataList($dataList, $tblDivisionCourse, $tblYear, null, $Filter);
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
                )
            )
        );
    }

    /**
     * @param TblYear $tblYear
     *
     * @return string
     */
    private function getSelectGradeBookTeacher(TblYear $tblYear): string
    {
        if (($tblPersonLogin = Account::useService()->getPersonByLogin())
            && ($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPersonLogin))
        ) {
            $dataList = array();
            // Lehraufträge
            foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                $this->setTeacherLectureshipSelectData($dataList, $tblTeacherLectureship);
            }

            // Klassenlehrer aus den Lehraufträgen der Lehrer
            if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByDivisionTeacher($tblPersonLogin, $tblYear, true))) {
                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                    $this->setDivisionCourseSelectDataList($dataList, $tblDivisionCourse, $tblYear, $tblPersonLogin);
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
     * @param null $Filter
     */
    private function setTeacherLectureshipSelectData(array &$dataList, TblTeacherLectureship $tblTeacherLectureship, $Filter = null)
    {
        if (($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())
            && ($tblSubject = $tblTeacherLectureship->getServiceTblSubject())
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
    private function setDivisionCourseSelectDataList(array &$dataList, TblDivisionCourse $tblDivisionCourse, TblYear $tblYear, ?TblPerson $tblPerson = null,
        $Filter = null)
    {
        // Lerngruppe oder SekII-Kurs
        if (($tblSubject = $tblDivisionCourse->getServiceTblSubject())) {
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

                $this->setTeacherLectureshipSelectData($dataList, $tblTeacherLectureship, $Filter);
            }
        }
    }
}