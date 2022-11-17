<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiTeacherGroup;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
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
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    const VIEW_GRADE_BOOK_SELECT = "VIEW_GRADE_BOOK_SELECT";
    const VIEW_GRADE_BOOK_CONTENT = "VIEW_GRADE_BOOK_CONTENT";
    const VIEW_TEACHER_GROUP = "VIEW_TEACHER_GROUP";

    /**
     * @return Stage
     */
    public function frontendGradeBook(): Stage
    {
        $stage = new Stage();

        $hasHeadmasterRole = Access::useService()->hasAuthorization('/Education/Graduation/Grade/GradeBook/Headmaster');
        $hasTeacherRole = Access::useService()->hasAuthorization('/Education/Graduation/Grade/GradeBook/Teacher');
        $hasAllReadonlyRole = Access::useService()->hasAuthorization('/Education/Graduation/Grade/GradeBook/AllReadOnly');

        if (($roleValue = Grade::useService()->getRole())) {
            if ($roleValue == "Headmaster") {
                $global = $this->getGlobal();
                $global->POST["Data"]["IsHeadmaster"] = 1;
                $global->savePost();
            }
            if ($roleValue == "AllReadonly") {
                $global = $this->getGlobal();
                $global->POST["Data"]["IsAllReadonly"] = 1;
                $global->savePost();
            }
        }

        $roleChange = "";
        if ($hasHeadmasterRole && $hasTeacherRole) {
            $roleChange =
                (new Form(new FormGroup(new FormRow(new FormColumn(
                    (new CheckBox('Data[IsHeadmaster]', new Bold('Schulleitung'), 1))
                        ->ajaxPipelineOnChange(array(ApiGradeBook::pipelineChangeRole()))
                )))))->disableSubmitAction();
        } elseif ($hasAllReadonlyRole && $hasTeacherRole) {
            $roleChange =
                (new Form(new FormGroup(new FormRow(new FormColumn(
                    (new CheckBox('Data[IsAllReadonly]', new Bold('Integrationsbeauftragter'), 1))
                        ->ajaxPipelineOnChange(array(ApiGradeBook::pipelineChangeRole()))
                )))))->disableSubmitAction();
        }

        if (($tblYear =Grade::useService()->getYear())) {
            $global = $this->getGlobal();
            $global->POST["Data"]["Year"] = $tblYear->getId();
            $global->savePost();
        }

        $stage->setContent(
            new Container("&nbsp;")
            . new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        ApiGradeBook::receiverBlock($this->getHeader(self::VIEW_GRADE_BOOK_SELECT), 'Header')
                    , 8),
                    new LayoutColumn(
                        new PullRight(ApiGradeBook::receiverBlock("", "ChangeRole") . $roleChange)
                    , 2),
                    new LayoutColumn(array(
                        ApiGradeBook::receiverBlock("", "ChangeYear"),
                        (new Form(new FormGroup(new FormRow(new FormColumn(
                            (new SelectBox('Data[Year]', '', array("{{ DisplayName }}" => Term::useService()->getYearAll())))
                                ->ajaxPipelineOnChange(array(ApiGradeBook::pipelineChangeYear()))
                        )))))->disableSubmitAction()
                    ), 2)
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        ApiGradeBook::receiverBlock($this->loadViewGradeBookSelect(), 'Content')
                    )
                ))
            )))
        );

        return $stage;
    }

    /**
     * @param string $View
     *
     * @return string
     */
    public function getHeader(string $View): string
    {
        $role = Grade::useService()->getRole();

        $textGradeBook = $View == self::VIEW_GRADE_BOOK_SELECT || $View == self::VIEW_GRADE_BOOK_CONTENT
            ? new Info(new Edit() . new Bold(" Notenbuch"))
            : "Notenbuch";

        $textTeacherGroup = $View == self::VIEW_TEACHER_GROUP
            ? new Info(new Edit() . new Bold(" Lerngruppen"))
            : "Lerngruppen";

        return
            (new Standard($textGradeBook, ApiGradeBook::getEndpoint()))
                ->ajaxPipelineOnClick(array(
                    ApiGradeBook::pipelineLoadHeader(self::VIEW_GRADE_BOOK_SELECT),
                    ApiGradeBook::pipelineLoadViewGradeBookSelect()
                ))
            . ($role == "Teacher"
                ? (new Standard($textTeacherGroup, ApiTeacherGroup::getEndpoint()))
                    ->ajaxPipelineOnClick(array(
                        ApiGradeBook::pipelineLoadHeader(self::VIEW_TEACHER_GROUP),
                        ApiTeacherGroup::pipelineLoadViewTeacherGroups()
                    ))
                : "")
            ;
    }

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
                            (new Standard('', ApiTeacherGroup::getEndpoint(), new Pen(), array(), 'Bearbeiten'))
                                ->ajaxPipelineOnClick(ApiTeacherGroup::pipelineLoadViewTeacherGroupEdit($tblDivisionCourse->getId()))
                            . (new Standard('', ApiTeacherGroup::getEndpoint(), new Remove(), array(), 'Löschen'))
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
                            array('orderable' => false, 'width' => '60px', 'targets' => -1),
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
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }

        $countStudents = 0;
        $countDivisionTeachers = 0;
        if (($students = $tblDivisionCourse->getStudents())) {
            $countStudents = count($students);
        }
        if (($divisionTeachers = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))) {
            $countDivisionTeachers = count($divisionTeachers);
        }

        return new Title(new Remove() . ' Kurs löschen')
            . new Well(new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diesen Kurs wirklich löschen?',
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

        $tblSchoolTypeList = School::useService()->getConsumerSchoolTypeCommonAll();

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

                    $this->setDivisionCourseSelectDataList($dataList, $tblDivisionCourse, $tblYear);
                }
            }

            $content = new TableData(
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

        } else {
            $content = new Warning("Bitte filtern Sie nach einer Schulart.", new Exclamation());
        }

        return $content;
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
                if (($dataItem = $this->getTeacherLectureshipSelectData($tblTeacherLectureship))) {
                    $dataList[] = $dataItem;
                }
            }

            // Klassenlehrer aus den Lehraufträgen der Lehrer
            if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByDivisionTeacher($tblPersonLogin, $tblYear))) {
                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                    $this->setDivisionCourseSelectDataList($dataList, $tblDivisionCourse, $tblYear, $tblPersonLogin);
                }
            }

            $content = new TableData(
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

        } else {
            $content = new Warning("Keine Lehraufträge vorhanden", new Exclamation());
        }

        return $content;
    }

    /**
     * @param TblTeacherLectureship $tblTeacherLectureship
     *
     * @return array|false
     */
    private function getTeacherLectureshipSelectData(TblTeacherLectureship $tblTeacherLectureship)
    {
        if (($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())
            && ($tblSubject = $tblTeacherLectureship->getServiceTblSubject())
        ) {
            return array(
                'Year' => $tblTeacherLectureship->getYearName(),
                'DivisionCourse' => $tblTeacherLectureship->getCourseName(),
                'CourseType' => $tblDivisionCourse->getTypeName(),
                'Subject' => $tblTeacherLectureship->getSubjectName(),
                'SubjectTeachers' => $tblTeacherLectureship->getSubjectTeachers(),
                'Option' => (new Standard("", ApiGradeBook::getEndpoint(), new Check(), array(), "Auswählen"))
                    ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewGradeBookContent($tblDivisionCourse->getId(), $tblSubject->getId()))
            );
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     *
     * @return array
     */
    private function getDivisionCourseSelectData(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject): array
    {
        return array(
            'Year' => $tblDivisionCourse->getYearName(),
            'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
            'CourseType' => $tblDivisionCourse->getTypeName(),
            'Subject' => $tblSubject->getDisplayName(),
            'SubjectTeachers' => $tblDivisionCourse->getDivisionTeacherNameListString(', '),
            'Option' => (new Standard("", ApiGradeBook::getEndpoint(), new Check(), array(), "Auswählen"))
                ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewGradeBookContent($tblDivisionCourse->getId(), $tblSubject->getId()))
        );
    }

    /**
     * @param array $dataList
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblYear $tblYear
     * @param TblPerson|null $tblPerson
     *
     * @return void
     */
    private function setDivisionCourseSelectDataList(array &$dataList, TblDivisionCourse $tblDivisionCourse, TblYear $tblYear, ?TblPerson $tblPerson = null)
    {
        // Lerngruppe oder SekII-Kurs
        if (($tblSubject = $tblDivisionCourse->getServiceTblSubject())) {
            $dataList[] = $this->getDivisionCourseSelectData($tblDivisionCourse, $tblSubject);
            // alle Lehraufträge des Kurses
        } elseif (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, null, $tblDivisionCourse))) {
            foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                // eigene Lehraufträge bei Klassenlehrern ignorieren
                if (($tblTeacher = $tblTeacherLectureship->getServiceTblPerson()) && $tblPerson
                    && $tblTeacher->getId() == $tblPerson->getId()
                ) {
                    continue;
                }

                if (($dataItem = $this->getTeacherLectureshipSelectData($tblTeacherLectureship))) {
                    $dataList[] = $dataItem;
                }
            }
        }
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param null $Filtern
     *
     * @return string
     */
    public function loadViewGradeBookContent($DivisionCourseId, $SubjectId, $Filtern = null): string
    {
        $isReadonly = false;
        $textKurs = "";
        $textSubject = "";
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblSubject = Subject::useService()->getSubjectById($SubjectId))
        ) {
            $textKurs = new Bold($tblDivisionCourse->getDisplayName());
            $textSubject = new Bold($tblSubject->getDisplayName());
            $content = "";

            // prüfen bei Lehrer ob er auch die Lehraufträge für alle schüler noch hat
        } else {
            $content = new Danger("Kurse oder Fach nicht gefunden.", new Exclamation());
        }

        return new Title("Notenbuch" . new Muted(new Small(" für Kurs: ")) . $textKurs . new Muted(new Small(" im Fach: ")) . $textSubject) . $content;
    }
}