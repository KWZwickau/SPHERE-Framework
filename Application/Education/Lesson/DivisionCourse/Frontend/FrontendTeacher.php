<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Frontend;

use SPHERE\Application\Api\Education\DivisionCourse\ApiTeacherLectureship;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;

class FrontendTeacher extends FrontendStudent
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
            ApiTeacherLectureship::receiverModal()
            . new Panel(new Filter() . ' Filter', $this->formTeacherLectureshipFilter($Filter), Panel::PANEL_TYPE_INFO)
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
        $addLink = (new Primary('Lehrauftrag hinzufügen', ApiTeacherLectureship::getEndpoint(), new Plus()))
            ->ajaxPipelineOnClick(ApiTeacherLectureship::pipelineOpenCreateTeacherLectureshipModal($Filter));

        $tblSubjectFilter = Subject::useService()->getSubjectById($Filter['Subject']);
        $tblTeacherFilter = Person::useService()->getPersonById($Filter['Teacher']);

        $tblTeacherLectureshipList = array();
        // Name like
        if (isset($Filter['CourseName']) && $Filter['CourseName'] != '') {
            if (isset($Filter['Year']) && $Filter['Year'] == -1) {
                $tblYearList = Term::useService()->getYearByNow();
                $tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByLikeName($Filter['CourseName'], $tblYearList ?: null);
            } elseif (isset($Filter['Year']) && ($tblYear = Term::useService()->getYearById($Filter['Year']))) {
                $tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByLikeName($Filter['CourseName'], array($tblYear));
            } else {
                $tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByLikeName($Filter['CourseName']);
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
        } elseif (isset($Filter['Year']) && $Filter['Year'] == -1) {
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
            // alle Schuljahre
            $tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy(
                null, $tblTeacherFilter ?: null, null, $tblSubjectFilter ?: null
            );
        }

        if ($tblTeacherLectureshipList) {
            $dataList = array();
            foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                if (($tblPerson = $tblTeacherLectureship->getServiceTblPerson())
                    && ($tblSubject = $tblTeacherLectureship->getServiceTblSubject())
                    && ($tblYear = $tblTeacherLectureship->getServiceTblYear())
                    && ($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())
                ) {
                    $dataList[] = array(
                        'Year' => $tblYear->getDisplayName(),
                        'Name' => $tblPerson->getFullName(),
                        'DivisionCourse' => $tblDivisionCourse->getName(),
                        'Subject' => $tblSubject->getDisplayName(),
                        'GroupName' => $tblTeacherLectureship->getGroupName()
                    );
                }
            }

            $columns = array(
                'Year' => 'Schuljahr',
                'Name' => 'Lehrer',
                'DivisionCourse' => 'Kurs',
                'Subject' => 'Fach',
                'GroupName' => 'Gruppe'
            );
            $columns['Option'] = '&nbsp;';

            return $addLink . new TableData(
                    $dataList,
                    null,
                    $columns,
                    array(
                        'columnDefs' => array(
                            array('type' => 'natural', 'targets' => 1),
                            array('orderable' => false, 'width' => '140px', 'targets' => -1),
                        ),
                        'order'      => array(array(0, 'asc'), array(1, 'asc')),
                        'responsive' => false
                    )
                );
        }

        return $addLink . '';
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
                    (new SelectBox('Filter[Year]', 'Schuljahr', array('{{ Name }} {{ Description }}' => $tblYearAll)))
                        ->ajaxPipelineOnChange(ApiTeacherLectureship::pipelineLoadTeacherLectureshipContent())
                    , 3),
                new FormColumn(
                    (new SelectBox('Filter[Teacher]', 'Lehrer', array('{{ FullName }}' => $tblTeacherList)))
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
     * @param null $TeacherLectureshipId
     * @param null $Filter
     * @param bool $setPost
     *
     * @return Form
     */
    public function formTeacherLectureship($TeacherLectureshipId = null,$Filter = null, bool $setPost = false): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        $tblTeacherLectureship = DivisionCourse::useService()->getTeacherLectureshipById($TeacherLectureshipId);
        if ($setPost && $tblTeacherLectureship) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['DivisionCourse'] = ($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse()) ? $tblDivisionCourse->getId() : null;
            $Global->POST['Data']['Subject'] = ($tblSubject = $tblTeacherLectureship->getServiceTblSubject()) ? $tblSubject->getId() : null;
            $Global->POST['Data']['GroupName'] = $tblTeacherLectureship->getGroupName();
            $Global->savePost();
        } elseif ($setPost && !$tblTeacherLectureship && ($tblYearByNowList = Term::useService()->getYearByNow())) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Year'] = (current($tblYearByNowList))->getId();
            $Global->savePost();
        }

        if ($TeacherLectureshipId) {
            $saveButton = (new Primary('Speichern', ApiTeacherLectureship::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiTeacherLectureship::pipelineEditTeacherLectureshipSave($TeacherLectureshipId, $Filter));
        } else {
            $saveButton = (new Primary('Speichern', ApiTeacherLectureship::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiTeacherLectureship::pipelineCreateTeacherLectureshipSave($Filter));
        }
        $buttonList[] = $saveButton;

        $tblYearAll = Term::useService()->getYearAllSinceYears(0);

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn($tblTeacherLectureship
                        ? new Panel('Schuljahr', $tblTeacherLectureship->getYearName(), Panel::PANEL_TYPE_INFO)
                        : (new SelectBox('Data[Year]', 'Schuljahr', array('{{ Name }} {{ Description }}' => $tblYearAll), new Education()))
                            ->setRequired()
                            ->ajaxPipelineOnChange(ApiTeacherLectureship::pipelineLoadDivisionCoursesSelectBox())
                        , 6),
                    new FormColumn($tblTeacherLectureship
                        ? new Panel('Lehrer', $tblTeacherLectureship->getTeacherName(), Panel::PANEL_TYPE_INFO)
                        : (new SelectBox('Data[Teacher]', 'Lehrer', array('{{ FullName }}'
                            => Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('TEACHER')))))->setRequired()
                        , 6)
                )),
                new FormRow(array(
                    new FormColumn(
                        ApiTeacherLectureship::receiverBlock('', 'DivisionCoursesSelectBox')
                        , 6),
                    new FormColumn(
                        (new SelectBox('Data[Subject]', 'Fach', array('{{ DisplayName }}' => Subject::useService()->getSubjectAll())))->setRequired()
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Data[GroupName]', '', 'Fachgruppe'))
                        , 6),
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
     * @param $Data
     *
     * @return SelectBox
     */
    public function loadDivisionCoursesSelectBox($Data): ?SelectBox
    {
        $tblDivisionCourseDivisionList = false;
        if (isset($Data['Year']) && ($tblYear = Term::useService()->getYearById($Data['Year']))) {
            $tblDivisionCourseDivisionList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear);
        }

        return (new SelectBox('Data[DivisionCourse]', 'Kurs', array('Name' => $tblDivisionCourseDivisionList)))->setRequired();
    }
}