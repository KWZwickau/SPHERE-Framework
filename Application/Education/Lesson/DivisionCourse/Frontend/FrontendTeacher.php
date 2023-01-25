<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Frontend;

use SPHERE\Application\Api\Education\DivisionCourse\ApiTeacherLectureship;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Stage;

class FrontendTeacher extends FrontendSubjectTable
{
    /**
     * @param null $Filter
     *
     * @return Stage
     */
    public function frontendTeacherLectureship($Filter = null): Stage
    {
        $stage = new Stage('Lehrauftrag', 'Übersicht');
        $stage->setContent(
            new Panel(new Filter() . ' Filter', $this->formTeacherLectureshipFilter($Filter), Panel::PANEL_TYPE_PRIMARY)
            . ApiTeacherLectureship::receiverBlock($this->loadTeacherLectureshipTable($Filter), 'TeacherLectureshipContent')
        );

        return $stage;
    }

    /**
     * @param null $Filter
     *
     * @return string
     */
    public function loadTeacherLectureshipTable($Filter = null): string
    {
        $hasFilter = false;
        $tblSubjectFilter = Subject::useService()->getSubjectById($Filter['Subject']);
        $tblTeacherFilter = Person::useService()->getPersonById($Filter['Teacher']);

        $tblTeacherLectureshipList = array();
        // Name like
        if (isset($Filter['CourseName']) && $Filter['CourseName'] != '') {
            $hasFilter = true;
            if (isset($Filter['Year']) && $Filter['Year'] == -1) {
                $tblYearList = Term::useService()->getYearByNow();
                $tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByLikeName($Filter['CourseName'], $tblYearList ?: null);
            } elseif (isset($Filter['Year']) && ($tblYear = Term::useService()->getYearById($Filter['Year']))) {
                $tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByLikeName($Filter['CourseName'], array($tblYear));
            } else {
                return (new Warning('Bitte wählen Sie ein Schuljahr aus', new Exclamation()));
            }

            if ($tblDivisionCourseList) {
                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                    if (($tblTeacherLectureshipDivisionCourseList = DivisionCourse::useService()->getTeacherLectureshipListBy(
                        null, $tblTeacherFilter ?: null, $tblDivisionCourse, $tblSubjectFilter ?: null
                    ))) {
                        $tblTeacherLectureshipList = array_merge($tblTeacherLectureshipDivisionCourseList, $tblTeacherLectureshipList);
                    }
                }
            }
        } elseif ($tblSubjectFilter || $tblTeacherFilter) {
            $hasFilter = true;
            if (isset($Filter['Year']) && $Filter['Year'] == -1) {
                if (($tblYearList = Term::useService()->getYearByNow())) {
                    foreach ($tblYearList as $tblYearItem) {
                        if (($tblTeacherLectureshipYearList = DivisionCourse::useService()->getTeacherLectureshipListBy(
                            $tblYearItem, $tblTeacherFilter ?: null, null, $tblSubjectFilter ?: null
                        ))) {
                            $tblTeacherLectureshipList = array_merge($tblTeacherLectureshipYearList, $tblTeacherLectureshipList);
                        }
                    }
                }
                // ausgewähltes Schuljahr
            } elseif (isset($Filter['Year']) && ($tblYearFilter = Term::useService()->getYearById($Filter['Year']))) {
                $tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy(
                    $tblYearFilter, $tblTeacherFilter ?: null, null, $tblSubjectFilter ?: null
                );
            } else {
                return (new Warning('Bitte wählen Sie ein Schuljahr aus', new Exclamation()));
            }
        }

        $personList = array();
        // bei Filterung, nur Lehrer mit entsprechendem Lehrauftrag anzeigen
        if ($hasFilter) {
            if ($tblTeacherLectureshipList) {
                $tblTeacherLectureshipList = $this->getSorter($tblTeacherLectureshipList)->sortObjectBy('Sort');
                foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                    if (($tblPerson = $tblTeacherLectureship->getServiceTblPerson())
                        && ($tblSubject = $tblTeacherLectureship->getServiceTblSubject())
                        && ($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())
                    ) {
                        $personList[$tblPerson->getId()][$tblSubject->getId()][$tblDivisionCourse->getId()] = $tblDivisionCourse->getName()
                            . (($groupName = $tblTeacherLectureship->getGroupName()) ? ' (' . $groupName . ')' : '');
                    }
                }
            }

            if ($tblTeacherFilter && !isset($personList[$tblTeacherFilter->getId()])) {
                $personList[$tblTeacherFilter->getId()] = false;
            }
        // kein Filter, dann alle Lehrer anzeigen
        } else {
            $tblYearList = false;
            if (isset($Filter['Year']) && $Filter['Year'] == -1) {
                $tblYearList = Term::useService()->getYearByNow();
            } elseif (isset($Filter['Year']) && ($tblYearFilter = Term::useService()->getYearById($Filter['Year']))) {
                $tblYearList = array($tblYearFilter);
            }

            if (($tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('TEACHER')))) {
                $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
                foreach ($tblPersonList as $tblPerson) {
                    $tblTeacherLectureshipList = array();
                    if ($tblYearList) {
                        foreach ($tblYearList as $tblYear) {
                            if (($tblTeacherLectureshipYearList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPerson))) {
                                $tblTeacherLectureshipList = array_merge($tblTeacherLectureshipYearList, $tblTeacherLectureshipList);
                            }
                        }
                    }
                    if ($tblTeacherLectureshipList) {
                        $tblTeacherLectureshipList = $this->getSorter($tblTeacherLectureshipList)->sortObjectBy('Sort');
                        foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                            if (($tblSubject = $tblTeacherLectureship->getServiceTblSubject())
                                && ($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())
                            ) {
                                $personList[$tblPerson->getId()][$tblSubject->getId()][$tblDivisionCourse->getId()] = $tblDivisionCourse->getName()
                                    . (($groupName = $tblTeacherLectureship->getGroupName()) ? ' (' . $groupName . ')' : '');
                            }
                        }
                    } else {
                        $personList[$tblPerson->getId()] = false;
                    }
                }
            }
        }

        if ($personList) {
            $layoutGroups = array();
            foreach ($personList as $personId => $subjectList) {
                if (($tblPerson = Person::useService()->getPersonById($personId))) {
                    $layoutColumns = array();
                    if ($subjectList) {
                        foreach ($subjectList as $subjectId => $divisionCourseList) {
                            if (($tblSubjectItem = Subject::useService()->getSubjectById($subjectId))) {
                                $layoutColumns[] = new LayoutColumn(
                                    new Panel(
                                        new PullClear($tblSubjectItem->getDisplayName() . new PullRight(new Link('', '/Education/Lesson/TeacherLectureship/Edit', new Pen(),
                                            array('PersonId' => $tblPerson->getId(), 'SubjectId' => $subjectId, 'Filter' => $Filter)))),
                                        implode(', ', $divisionCourseList), Panel::PANEL_TYPE_INFO)
                                    , 3);
                            }
                        }
                    }
                    if (empty($layoutColumns)) {
                        $layoutColumns = new LayoutColumn(new Warning('Keine Lehraufträge vorhanden', new Exclamation()));
                    }

                    $layoutGroups[] = new LayoutGroup(new LayoutRow($layoutColumns), new Title(
                        $tblPerson->getLastFirstName()
                        . (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson)) ? ' (' . $tblTeacher->getAcronym() . ')' : '')
                        . new Link('Bearbeiten', '/Education/Lesson/TeacherLectureship/Edit', new Pen(), array('PersonId' => $tblPerson->getId(), 'Filter' => $Filter))
                    ));
                }
            }

            return new Layout($layoutGroups);
        }

        return (new Warning('Keine entsprechende Lehraufträge gefunden', new Exclamation()));
    }

    /**
     * @param null $Filter
     *
     * @return Form
     */
    public function formTeacherLectureshipFilter(&$Filter = null): Form
    {
        $tblYearAll = Term::useService()->getYearAll();
        if ($tblYearAll && Term::useService()->getYearByNow()) {
            $tblYearAll[] = new SelectBoxItem(-1, 'Aktuelle Übersicht');
            if ($Filter == null) {
                $Filter['Year'] = -1;
                $Filter['Subject'] = 0;
                $Filter['Teacher'] = 0;
                $Global = $this->getGlobal();
                $Global->POST['Filter']['Year'] = -1;
                $Global->savePost();
            }
        }

        $tblSubjectAll = Subject::useService()->getSubjectAll();
        $tblTeacherList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('TEACHER'));

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Filter[Year]', 'Schuljahr', array('{{ Name }} {{ Description }}' => $tblYearAll), null, false))
                        ->setRequired()
                        ->ajaxPipelineOnChange(ApiTeacherLectureship::pipelineLoadTeacherLectureshipContent())
                    , 3),
                new FormColumn(
                    (new SelectBox('Filter[Teacher]', 'Lehrer', array('{{ LastFirstName }}' => $tblTeacherList)))
                        ->ajaxPipelineOnChange(ApiTeacherLectureship::pipelineLoadTeacherLectureshipContent())
                    , 3),
                new FormColumn(
                    (new TextField('Filter[CourseName]', '', 'Kursname'))
                        ->ajaxPipelineOnKeyUp(ApiTeacherLectureship::pipelineLoadTeacherLectureshipContent())
                    , 3),
                new FormColumn(
                    (new SelectBox('Filter[Subject]', 'Fach', array('{{ Acronym }}-{{ Name }}' => $tblSubjectAll)))
                        ->ajaxPipelineOnChange(ApiTeacherLectureship::pipelineLoadTeacherLectureshipContent())
                    , 3)
            ))
        )));
    }

    /**
     * @param null $Filter
     * @param null $PersonId
     * @param null $SubjectId
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendEditTeacherLectureship($Filter = null, $PersonId = null, $SubjectId = null, $Data = null): Stage
    {
        $stage = new Stage('Lehrauftrag', 'Bearbeiten');
        $stage->addButton((new Standard('Zurück', '/Education/Lesson/TeacherLectureship', new ChevronLeft(), array('Filter' => $Filter))));

        $tblYearList = false;
        $tblSelectedYear = false;
        if (isset($Filter['Year'])) {
            if ($Filter['Year'] == -1) {
                $tblYearList = Term::useService()->getYearByNow();
            } elseif (($tblSelectedYear = Term::useService()->getYearById($Filter['Year']))) {
                $tblYearList[] = $tblSelectedYear;
            }
        }

        if ($SubjectId) {
            $global = $this->getGlobal();
            $global->POST['Data']['Subject'] = $SubjectId;
            $Data['Subject'] = $SubjectId;
            $global->savePost();
        }

        if (!empty($tblYearList) && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $stage->setContent(
                new Layout(array(new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(new Panel('Lehrer',
                            $tblPerson->getLastFirstName() . (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson)) ? ' (' . $tblTeacher->getAcronym() . ')' : ''),
                            Panel::PANEL_TYPE_INFO
                        ), 6),
                        new LayoutColumn(new Panel('Schuljahr', $tblSelectedYear ? $tblSelectedYear->getDisplayName() : 'Aktuelle Übersicht', Panel::PANEL_TYPE_INFO), 6)
                    ))
                ))))
                . new Well((new Form(array(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(new Panel(
                                'Fach',
                                (new SelectBox('Data[Subject]', '', array('{{ Acronym }}-{{ Name }}' => Subject::useService()->getSubjectAll())))
                                    ->setRequired()
                                    ->ajaxPipelineOnChange(ApiTeacherLectureship::pipelineLoadCheckCoursesContent($Filter, $PersonId)),
                                Panel::PANEL_TYPE_INFO
                            ), 12)
                        )),
                        new FormRow(new FormColumn(
                            ApiTeacherLectureship::receiverBlock($SubjectId
                                ? $this->loadCheckCoursesContent($Filter, $PersonId, $Data)
                                : new Warning('Bitte wählen Sie zunächst ein Fach aus.'), 'CheckCoursesContent')
                        ))
                    )),
                )))->disableSubmitAction())
            );
        } else {
            $stage->setContent(new Danger('Lehrer oder Schuljahr nicht gefunden', new Exclamation()));
        }

        return $stage;
    }

    /**
     * @param $Filter
     * @param $PersonId
     * @param $Data
     *
     * @return string
     */
    public function loadCheckCoursesContent($Filter, $PersonId, $Data): string
    {
        $tblYearList = false;
        if (isset($Filter['Year'])) {
            if ($Filter['Year'] == -1) {
                $tblYearList = Term::useService()->getYearByNow();
            } elseif (($tblSelectedYear = Term::useService()->getYearById($Filter['Year']))) {
                $tblYearList[] = $tblSelectedYear;
            }
        }

        if (!empty($tblYearList) && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            if (isset($Data['Subject']) && ($tblSubject = Subject::useService()->getSubjectById($Data['Subject']))) {
                $global = $this->getGlobal();
                $global->POST['Data']['Courses'] = null;
                foreach ($tblYearList as $tblYear) {
                    if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPerson, null, $tblSubject))) {
                        foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                            if (($tblDivisionCourseByTeacher = $tblTeacherLectureship->getTblDivisionCourse())) {
                                $global->POST['Data']['Courses'][$tblDivisionCourseByTeacher->getId()] = 1;
                            }
                        }
                    }
                }
                $global->savePost();
            } else {
                return new Warning('Bitte wählen Sie zunächst ein Fach aus.');
            }

            $typeDivisionId = DivisionCourse::useService()->getDivisionCourseTypeByIdentifier('DIVISION')->getId();
            $typeCoreGroupId = DivisionCourse::useService()->getDivisionCourseTypeByIdentifier('CORE_GROUP')->getId();
            $typeTeachingGroupId = DivisionCourse::useService()->getDivisionCourseTypeByIdentifier('TEACHING_GROUP')->getId();
            $dataList = array();
            foreach ($tblYearList as $tblYear) {
                if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear))) {
                    $tblDivisionCourseList = $this->getSorter($tblDivisionCourseList)->sortObjectBy('Name');
                    /** @var TblDivisionCourse $tblDivisionCourse */
                    foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                        if (($tblType = $tblDivisionCourse->getType())) {
                            if ($tblType->getIdentifier() == TblDivisionCourseType::TYPE_ADVANCED_COURSE || $tblType->getIdentifier() == TblDivisionCourseType::TYPE_BASIC_COURSE) {
                                // nur SEKII-Kurse mit dem entsprechenden Fach anzeigen
                                if (($tblSubjectByDivisionCourse = $tblDivisionCourse->getServiceTblSubject())
                                    && $tblSubject->getId() == $tblSubjectByDivisionCourse->getId()
                                ) {
                                    $typeId = -1;
                                } else {
                                    continue;
                                }
                            } else {
                                $typeId = $tblType->getId();
                            }

                            $dataList[$typeId][$tblDivisionCourse->getId()] =
                                new CheckBox('Data[Courses][' . $tblDivisionCourse->getId() . ']', $tblDivisionCourse->getDisplayName(), 1);
                        }
                    }
                }
            }

            $columnList = array();
            if (isset($dataList[$typeDivisionId])) {
                $columnList[] = new LayoutColumn(new Panel('Klasse', $dataList[$typeDivisionId], Panel::PANEL_TYPE_INFO), 3);
            }
            if (isset($dataList[$typeCoreGroupId])) {
                $columnList[] = new LayoutColumn(new Panel('Stammgruppe', $dataList[$typeCoreGroupId], Panel::PANEL_TYPE_INFO), 3);
            }
            if (isset($dataList[$typeTeachingGroupId])) {
                $columnList[] = new LayoutColumn(new Panel('Unterrichtsgruppe', $dataList[$typeTeachingGroupId], Panel::PANEL_TYPE_INFO), 3);
            }
            // Leistungskurse und Grundkurse als ein Panel
            if (isset($dataList[-1])) {
                $columnList[] = new LayoutColumn(new Panel('SekII-Kurs', $dataList[-1], Panel::PANEL_TYPE_INFO), 3);
            }

            if ($columnList) {
                return new Layout(new LayoutGroup(array(
                    new LayoutRow($columnList),
                    new LayoutRow(new LayoutColumn(array(
                        (new Primary('Speichern', ApiTeacherLectureship::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiTeacherLectureship::pipelineSaveTeacherLectureship($Filter, $PersonId)),
                        new Standard('Abbrechen', '/Education/Lesson/TeacherLectureship', new Disable(), array('Filter' => $Filter))
                    )))
                )));
            } else {
                return new Warning('Keine Kurse für das Schuljahr gefunden', new Exclamation());
            }
        }

        return new Danger('Lehrer oder Schuljahr nicht gefunden', new Exclamation());
    }
}