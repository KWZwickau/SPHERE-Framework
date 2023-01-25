<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourseMember;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiTeacherGroup;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Frontend\FrontendMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\ResizeVertical;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;

abstract class FrontendTeacherGroup extends FrontendTask
{
    /**
     * @return string
     */
    public function loadViewTeacherGroups(): string
    {
        $tblType = DivisionCourse::useService()->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_TEACHER_GROUP);
        if (($tblPerson = Account::useService()->getPersonByLogin())
            && ($tblYear = Grade::useService()->getYear())
        ) {
            $dataList = array();
            if (($tblDivisionCourseList = DivisionCourse::useService()->getTeacherGroupListByTeacherAndYear($tblPerson, $tblYear))) {
                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                    $dataList[] = array(
                        'Name' => $tblDivisionCourse->getName(),
                        'Description' => $tblDivisionCourse->getDescription(),
                        'Subject' => $tblDivisionCourse->getSubjectName(),
                        'Students' => $tblDivisionCourse->getCountStudents(),
                        'Option' =>
                            (new Standard('', ApiTeacherGroup::getEndpoint(), new Pen(), array(), 'Lerngruppe bearbeiten'))
                                ->ajaxPipelineOnClick(ApiTeacherGroup::pipelineLoadViewTeacherGroupEdit($tblDivisionCourse->getId()))
                            . (new Standard('', ApiTeacherGroup::getEndpoint(), new ResizeVertical(), array(), 'Schüler sortieren'))
                                ->ajaxPipelineOnClick(ApiTeacherGroup::pipelineLoadViewTeacherGroupSort($tblDivisionCourse->getId()))
                            . (new Standard('', ApiTeacherGroup::getEndpoint(), new Remove(), array(), 'Lerngruppe Löschen'))
                                ->ajaxPipelineOnClick(ApiTeacherGroup::pipelineLoadViewTeacherGroupDelete($tblDivisionCourse->getId()))
                    );
                }
            }

            $content =
                (new Primary("{$tblType->getName()} hinzufügen", ApiTeacherGroup::getEndpoint(), new Plus()))
                    ->ajaxPipelineOnClick(ApiTeacherGroup::pipelineLoadViewTeacherGroupEdit())
                . new TableData(
                    $dataList,
                    null,
                    array(
                        'Name' => 'Kursname',
                        'Description' => 'Beschreibung',
                        'Subject' => 'Fach',
                        'Students' => 'Schüler',
                        'Option' => '',
                    ),
                    array(
                        'columnDefs' => array(
                            array('type' => 'natural', 'targets' => 0),
                            array('orderable' => false, 'width' => '98px', 'targets' => -1),
                        ),
                        'order'      => array(array(0, 'asc'), array(1, 'asc')),
                        'responsive' => false
                    )
                );
        } else {
            $content = new Danger("Keine Person zum Benutzerkonto gefunden", new Exclamation());
        }

        return new Title("Lerngruppen", "Verwalten") . $content;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadViewTeacherGroupEdit($DivisionCourseId): string
    {
        return $this->getTeacherGroupEdit($this->formTeacherGroup($DivisionCourseId, true), $DivisionCourseId);
    }

    /**
     * @param $form
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function getTeacherGroupEdit($form, $DivisionCourseId = null): string
    {
        $tblType = DivisionCourse::useService()->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_TEACHER_GROUP);
        if ($DivisionCourseId) {
            $title = new Title(new Edit() . " {$tblType->getName()} bearbeiten");
        } else {
            $title = new Title(new Plus() . " {$tblType->getName()} hinzufügen");
        }

        return $title
            . new Well($form);
    }

    /**
     * @param null $DivisionCourseId
     * @param bool $setPost
     * @param null $Data
     *
     * @return Form
     */
    public function formTeacherGroup($DivisionCourseId = null, bool $setPost = false, $Data = null): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        $tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId);
        if ($setPost && $tblDivisionCourse) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Name'] = $tblDivisionCourse->getName();
            $Global->POST['Data']['Description'] = $tblDivisionCourse->getDescription();
            if (($tblStudentList = $tblDivisionCourse->getStudents())) {
                foreach ($tblStudentList as $tblStudent) {
                    $Global->POST['Data']['Students'][$tblStudent->getId()] = 1;
                }
            }

            $Global->savePost();
        }

        $tblSubjectList = array();
        $subjectId = '';
        if ($tblDivisionCourse) {
            $tblYear = $tblDivisionCourse->getServiceTblYear();
            if (($tblSubject = $tblDivisionCourse->getServiceTblSubject())) {
                $subjectId = $tblSubject->getId();
            }
        } else {
            $tblYear = Grade::useService()->getYear();
        }
        if (!$tblDivisionCourse && $tblYear && ($tblPerson = Account::useService()->getPersonByLogin())) {
            $tblSubjectList = DivisionCourse::useService()->getSubjectListByTeacherAndYear($tblPerson, $tblYear);
        }

        return (new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn($tblDivisionCourse
                    ? new Panel('Fach', $tblDivisionCourse->getSubjectName(), Panel::PANEL_TYPE_INFO)
                    : (new SelectBox('Data[Subject]', 'Fach', array('{{ DisplayName }}' => $tblSubjectList)))
                        ->setRequired()
                        ->ajaxPipelineOnChange(ApiTeacherGroup::pipelineLoadTeacherGroupStudentSelect(null, null, $Data))
                )
            )),
            new FormRow(array(
                new FormColumn(
                    (new TextField('Data[Name]', '', 'Name', new Pen()))->setRequired()
                    , 6),
                new FormColumn(
                    new TextField('Data[Description]', '', 'Beschreibung', new Pen())
                    , 6),
            )),
            new FormRow(array(
                new FormColumn(
                    ApiTeacherGroup::receiverBlock($this->loadTeacherGroupStudentSelect($subjectId, $DivisionCourseId, $Data), 'TeacherGroupStudentSelect')
                )
            )),
            new FormRow(array(
                new FormColumn(array(
                    (new Primary('Speichern', ApiTeacherGroup::getEndpoint(), new Save()))
                        ->ajaxPipelineOnClick(ApiTeacherGroup::pipelineSaveTeacherGroupEdit($DivisionCourseId)),
                    (new Standard('Abbrechen', ApiTeacherGroup::getEndpoint(), new Disable()))
                        ->ajaxPipelineOnClick(ApiTeacherGroup::pipelineLoadViewTeacherGroups())
                ))
            ))
        ))))->disableSubmitAction();
    }

    /**
     * @param $SubjectId
     * @param $DivisionCourseId
     * @param $Data
     *
     * @return Warning|string
     */
    public function loadTeacherGroupStudentSelect($SubjectId, $DivisionCourseId, $Data)
    {
        if (isset($Data['Students'])) {
            foreach ($Data['Students'] as $personId => $value) {
                $global = $this->getGlobal();
                $global->POST['Data']['Students'][$personId] = $value;
                $global->savePost();
            }
        }

        if (($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
            if (($tblPerson = Account::useService()->getPersonByLogin())
                && ($tblYear = Grade::useService()->getYear())
                && ($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPerson, null, $tblSubject))
            ) {
                $size = 3;
                $columnList = array();
                $tblTeacherLectureshipList = $this->getSorter($tblTeacherLectureshipList)->sortObjectBy('Sort');
                /** @var TblTeacherLectureship $tblTeacherLectureship */
                foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                    if (($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())) {
                        // SekII-Kurse nicht mit anzeigen
                        if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                            continue;
                        }

                        $contentPanel = array();
                        if (($tblStudentList = $tblDivisionCourse->getStudents())) {
                            foreach ($tblStudentList as $tblStudent) {
                                // prüfen ob der Schüler das Fach hat
                                if (DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject($tblStudent, $tblYear, $tblSubject)) {
                                    $groupList = array();
                                    // prüfen ob der Schüler in weiteren Lerngruppen für das Fach ist
                                    if (($tblTeacherGroupList = DivisionCourse::useService()->getTeacherGroupListByStudentAndYearAndSubject(
                                        $tblStudent, $tblYear, $tblSubject
                                    ))) {
                                        foreach ($tblTeacherGroupList as $tblDivisionCourseStudent) {
                                            if (!$DivisionCourseId || $tblDivisionCourseStudent->getId() != $DivisionCourseId) {
                                                $groupList[] = new ToolTip($tblDivisionCourseStudent->getDisplayName(), $tblDivisionCourseStudent->getDivisionTeacherNameListString(', '));
                                            }
                                        }
                                    }
                                    $contentPanel[] = new PullClear(
                                        (new Container(
                                            new CheckBox("Data[Students][{$tblStudent->getId()}]", $tblStudent->getLastFirstNameWithCallNameUnderline(), 1
                                            )))->setStyle(array("float: left;"))
                                        . (empty($groupList) ? '' : ' ' . new PullRight(new Muted(implode(' | ', $groupList))))
                                    );
                                }
                            }
                        }

                        $columnList[] = new LayoutColumn(new Panel($tblDivisionCourse->getDisplayName(), $contentPanel, Panel::PANEL_TYPE_INFO), $size);
                    }
                }

                return new Layout(new LayoutGroup(
                    Grade::useService()->getLayoutRowsByLayoutColumnList($columnList, $size),
                    new Title("Verfügbare Schüler")
                ));
            }
        } else {
            return new Warning("Bitte wählen Sie zunächst ein Fach aus.", new Exclamation());
        }

        return '';
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadViewTeacherGroupDelete($DivisionCourseId): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Die Lerngruppe wurde nicht gefunden', new Exclamation());
        }

        $countStudents = 0;
        $countDivisionTeachers = 0;
        if (($students = $tblDivisionCourse->getStudents())) {
            $countStudents = count($students);
        }
        if (($divisionTeachers = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))) {
            $countDivisionTeachers = count($divisionTeachers);
        }

        return new Title(new Remove() . ' Lerngruppe löschen')
            . new Well(new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diese Lerngruppe wirklich löschen?',
                                array(
                                    'Schuljahr: ' . new Bold($tblDivisionCourse->getYearName()),
                                    'Typ: ' . $tblDivisionCourse->getTypeName(),
                                    'Fach: ' . $tblDivisionCourse->getDisplayName(),
                                    'Name: ' . new Bold($tblDivisionCourse->getName()),
                                    'Schüler: ' . ($countStudents ? new \SPHERE\Common\Frontend\Text\Repository\Danger($countStudents) : '0'),
                                    $tblDivisionCourse->getDivisionTeacherName()  .  ': '
                                    . ($countDivisionTeachers ? new \SPHERE\Common\Frontend\Text\Repository\Danger($countDivisionTeachers) : '0'),
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', ApiTeacherGroup::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(ApiTeacherGroup::pipelineSaveTeacherGroupDelete($DivisionCourseId))
                            . (new Standard('Nein', ApiTeacherGroup::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(ApiTeacherGroup::pipelineLoadViewTeacherGroups())
                        )
                    )
                )
            ));
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadViewTeacherGroupSort($DivisionCourseId): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $MemberTypeIdentifier = TblDivisionCourseMemberType::TYPE_STUDENT;
            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());
            $content = new Title('Schüler sortieren', 'der ' . $text . ' im Schuljahr ' . new Bold($tblDivisionCourse->getYearName()));

            $buttonList[] = (new Standard('Zurück', ApiTeacherGroup::getEndpoint(), new ChevronLeft()))
                ->ajaxPipelineOnClick(ApiTeacherGroup::pipelineLoadViewTeacherGroups());
            $buttonList[] = (new Standard('Sortierung alphabetisch', ApiDivisionCourseMember::getEndpoint(), new ResizeVertical()))
                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineOpenSortMemberModal($tblDivisionCourse->getId(), $MemberTypeIdentifier,  'Sortierung alphabetisch'));
            $buttonList[] = (new Standard('Sortierung Geschlecht (alphabetisch)', ApiDivisionCourseMember::getEndpoint(), new ResizeVertical()))
                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineOpenSortMemberModal($tblDivisionCourse->getId(), $MemberTypeIdentifier, 'Sortierung Geschlecht (alphabetisch)'));

            $content .=
                ApiDivisionCourseMember::receiverModal()
                . DivisionCourse::useService()->getDivisionCourseHeader($tblDivisionCourse)
                . new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn($buttonList),
                            new LayoutColumn(
                                ApiDivisionCourseMember::receiverBlock(
                                    (new FrontendMember())->loadSortMemberContent($DivisionCourseId, $MemberTypeIdentifier), 'SortMemberContent'
                                )
                            )
                        ))
                    ))
                ));

            return $content;
        }

        return '';
    }
}