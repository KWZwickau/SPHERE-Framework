<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourse;
use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourseStudent;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\Frontend\FrontendYearChange;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Link as LinkIcon;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\PersonParent;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\ResizeVertical;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;

class Frontend extends FrontendYearChange
{
    /**
     * @param null $Filter
     *
     * @return Stage
     */
    public function frontendDivisionCourse($Filter = null): Stage
    {
        $stage = new Stage('Kurs', 'Übersicht');
        $stage->setContent(
            ApiDivisionCourse::receiverModal()
            . new Panel(new Filter() . ' Filter', $this->formFilter($Filter), Panel::PANEL_TYPE_INFO)
            . ApiDivisionCourse::receiverBlock($this->loadDivisionCourseTable($Filter), 'DivisionCourseContent')
        );

        return $stage;
    }

    /**
     * @param null $Filter
     *
     * @return string
     */
    public function loadDivisionCourseTable($Filter = null): string
    {
        $addLink = (new Primary('Kurs hinzufügen', ApiDivisionCourse::getEndpoint(), new Plus()))
            ->ajaxPipelineOnClick(ApiDivisionCourse::pipelineOpenCreateDivisionCourseModal($Filter));

        $typeFilter = null;
        if (isset($Filter['Type']) && ($tblCourseTypeFilter = DivisionCourse::useService()->getDivisionCourseTypeById($Filter['Type']))) {
            $typeFilter = $tblCourseTypeFilter->getIdentifier();
        } else {
            $tblCourseTypeFilter = false;
        }

        $tblDivisionCourseList = array();
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

            // Typ filtern
            if ($tblCourseTypeFilter && $tblDivisionCourseList) {
                $tempList = array();
                foreach ($tblDivisionCourseList as $item) {
                    if ($item->getType()->getId() != $tblCourseTypeFilter->getId()) {
                        continue;
                    }
                    $tempList[] = $item;
                }
                $tblDivisionCourseList = $tempList;
            }

        }
        // aktuelle Übersicht
        elseif (isset($Filter['Year']) && $Filter['Year'] == -1) {
            if (($tblYearList = Term::useService()->getYearByNow())) {
                foreach ($tblYearList as $tblYearItem) {
                    if (($tblDivisionCourseYearList = DivisionCourse::useService()->getDivisionCourseListBy($tblYearItem, $typeFilter))) {
                        $tblDivisionCourseList = array_merge($tblDivisionCourseYearList, $tblDivisionCourseList);
                    }
                }
            }
        // ausgewähltes Schuljahr
        } elseif (isset($Filter['Year']) && ($tblYear = Term::useService()->getYearById($Filter['Year']))) {
            $tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, $typeFilter);
        } else {
        // alle Schuljahre
            $tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy(null, $typeFilter);
        }

        if ($tblDivisionCourseList) {
            $dataList = array();
            $showExtraInfo = isset($Filter['ShowExtraInfo']);
            /** @var TblDivisionCourse $tblDivisionCourse */
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                // Lerngruppen der Lehrer überspringen
                if ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_TEACHER_GROUP) {
                    continue;
                }

                $countActive = 0;
                $countInActive = 0;
                $tblSubCourseList = array();
                DivisionCourse::useService()->getSubDivisionCourseRecursiveListByDivisionCourse($tblDivisionCourse, $tblSubCourseList);
                if ($tblSubCourseList) {
                    $subCourses = array();
                    foreach ($tblSubCourseList as $tblSubCourse) {
                        if (!$showExtraInfo) {
                            $countActive += $tblSubCourse->getCountStudents();
                            $countInActive += $tblSubCourse->getCountInActiveStudents();
                        }
                        $subCourses[] = $tblSubCourse->getName();
                    }
                    $subCourseText = implode(', ', $subCourses);
                } else {
                    $subCourseText = '';
                }

                $item = array(
                    'Year' => $tblDivisionCourse->getYearName(),
                    'Name' => $tblDivisionCourse->getName(),
                    'Description' => $tblDivisionCourse->getDescription(),
                    'Type' => $tblDivisionCourse->getTypeName(),
                    'SubCourses' => $subCourseText,
                    'Option' =>
                        new Standard('', '/Education/Lesson/DivisionCourse/Show',
                            new EyeOpen(), array('DivisionCourseId' => $tblDivisionCourse->getId(), 'Filter' => $Filter), 'Kurs einsehen')
                        . (new Standard('', ApiDivisionCourse::getEndpoint(), new Pen(), array(), 'Name des Kurses bearbeiten'))
                            ->ajaxPipelineOnClick(ApiDivisionCourse::pipelineOpenEditDivisionCourseModal($tblDivisionCourse->getId(), $Filter))
                        . (new Standard('', ApiDivisionCourse::getEndpoint(), new LinkIcon(), array(), 'Unter-Kurse verknüpfen'))
                            ->ajaxPipelineOnClick(ApiDivisionCourse::pipelineOpenLinkDivisionCourseModal($tblDivisionCourse->getId(), $Filter))
                        . (new Standard('', ApiDivisionCourse::getEndpoint(), new Remove(), array(), 'Kurs löschen'))
                            ->ajaxPipelineOnClick(ApiDivisionCourse::pipelineOpenDeleteDivisionCourseModal($tblDivisionCourse->getId(), $Filter))
                );

                if ($showExtraInfo) {
                    $tblSubCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                    list($students, $genders) = DivisionCourse::useService()->getStudentInfoByDivisionCourseList($tblSubCourseList);
                    if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                        $countStudentSubjectPeriod1 = DivisionCourse::useService()->getCountStudentsBySubjectDivisionCourseAndPeriod($tblDivisionCourse, 1);
                        $countStudentSubjectPeriod2 = DivisionCourse::useService()->getCountStudentsBySubjectDivisionCourseAndPeriod($tblDivisionCourse, 2);
                        $item['Students'] = '1. HJ: ' . $countStudentSubjectPeriod1 . ', 2. HJ: ' . $countStudentSubjectPeriod2;
                        $item['Genders'] = '';
                    } else {
                        $item['Students'] = $students;
                        $item['Genders'] = $genders;
                    }

                    $item['Teachers'] = $tblDivisionCourse->getDivisionTeacherNameListString();

                    $item['Visibility'] = '';
                    if ($tblDivisionCourse->getIsShownInPersonData()) {
                        $item['Visibility'] = 'Stammdaten';
                    }
                    if ($tblDivisionCourse->getIsReporting()) {
                        $item['Visibility'] .= ($item['Visibility'] ? '<br/>' : '') . 'Auswertung';
                    }
//                    if ($tblDivisionCourse->getIsUcs()) {
//                        $item['Visibility'] .= ($item['Visibility'] ? '<br/>' : '') . 'UCS';
//                    }
                } else {
                    if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                        $countStudentSubjectPeriod1 = DivisionCourse::useService()->getCountStudentsBySubjectDivisionCourseAndPeriod($tblDivisionCourse, 1);
                        $countStudentSubjectPeriod2 = DivisionCourse::useService()->getCountStudentsBySubjectDivisionCourseAndPeriod($tblDivisionCourse, 2);
                        $item['Students'] = '1. HJ: ' . $countStudentSubjectPeriod1 . ', 2. HJ: ' . $countStudentSubjectPeriod2;
                    } else {
                        $countActive += $tblDivisionCourse->getCountStudents();
                        $countInActive += $tblDivisionCourse->getCountInActiveStudents();

                        $toolTip = $countInActive . ($countInActive == 1 ? ' deaktivierter Schüler' : ' deaktivierte Schüler');
                        $students = $countActive . ($countInActive > 0 ? ' + '
                                . new ToolTip('(' . $countInActive . new \SPHERE\Common\Frontend\Icon\Repository\Info() . ')', $toolTip) : '');
                        $item['Students'] = $students;
                    }
                }

                $dataList[] = $item;
            }

            $columns = array(
                'Year' => 'Schuljahr',
                'Name' => 'Kursname',
                'Description' => 'Beschreibung',
                'Type' => 'Typ',
                'SubCourses' => 'Unter-Kurse',
                'Students' => 'Schüler',
            );
            if ($showExtraInfo) {
                $columns['Genders'] = 'Geschlecht';
                $columns['Teachers'] = 'Leiter';
                $columns['Visibility'] = 'Sichtbarkeit';
            }
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
    public function formFilter(&$Filter = null): Form
    {
        $tblTypeAll = DivisionCourse::useService()->getDivisionCourseTypeListWithoutTeacherGroup();
        $tblYearAll = Term::useService()->getYearAll();
        if ($tblYearAll && Term::useService()->getYearByNow()) {

            $tblYearAll[] = new SelectBoxItem(-1, 'Aktuelle Übersicht');

            if ($Filter == null) {
                $Filter['Year'] = -1;
                $Global = $this->getGlobal();
                $Global->POST['Filter']['Year'] = -1;
                $Global->savePost();
            }
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Filter[Year]', 'Schuljahr', array('{{ Name }} {{ Description }}' => $tblYearAll)))
                        ->ajaxPipelineOnChange(ApiDivisionCourse::pipelineLoadDivisionCourseContent())
                    , 4),
                new FormColumn(
                    (new SelectBox('Filter[Type]', 'Typ', array('{{ Name }}' => $tblTypeAll)))
                        ->ajaxPipelineOnChange(ApiDivisionCourse::pipelineLoadDivisionCourseContent())
                    , 4),
                new FormColumn(
                    (new TextField('Filter[CourseName]', '', 'Kursname'))
                        ->ajaxPipelineOnKeyUp(ApiDivisionCourse::pipelineLoadDivisionCourseContent())
                    , 4)
            )),
            new FormRow(array(
                new FormColumn(
                    (new CheckBox('Filter[ShowExtraInfo]', 'Weitere Informationen anzeigen', 1))
                        ->ajaxPipelineOnChange(ApiDivisionCourse::pipelineLoadDivisionCourseContent())
                    , 3),
            ))
        )));
    }

    /**
     * @param null $DivisionCourseId
     * @param null $Filter
     * @param bool $setPost
     * @param null $Data
     *
     * @return Form
     */
    public function formDivisionCourse($DivisionCourseId = null, $Filter = null, bool $setPost = false, $Data = null): Form
    {
        $Error = '';

        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        $tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId);
        if ($setPost && $tblDivisionCourse) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Name'] = $tblDivisionCourse->getName();
            $Global->POST['Data']['Description'] = $tblDivisionCourse->getDescription();
            $Global->POST['Data']['Subject'] = $tblDivisionCourse->getServiceTblSubject() ? $tblDivisionCourse->getServiceTblSubject()->getId() : 0;
            $Global->POST['Data']['IsShownInPersonData'] = $tblDivisionCourse->getIsShownInPersonData();
            $Global->POST['Data']['IsReporting'] = $tblDivisionCourse->getIsReporting();
//            $Global->POST['Data']['IsUcs'] = $tblDivisionCourse->getIsUcs();
            $Global->savePost();
        } elseif (!$tblDivisionCourse) {
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['IsReporting'] = 1;
                $Global->savePost();
            } else {
                if (isset($Data['Subject']) && !(Subject::useService()->getSubjectById($Data['Subject']))) {
                    $Error = 'Bitte wählen Sie ein Fach aus';
                }
            }
        }

        if ($DivisionCourseId) {
            $saveButton = (new Primary('Speichern', ApiDivisionCourse::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDivisionCourse::pipelineEditDivisionCourseSave($DivisionCourseId, $Filter));
        } else {
            $saveButton = (new Primary('Speichern', ApiDivisionCourse::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDivisionCourse::pipelineCreateDivisionCourseSave($Filter));
        }
        $buttonList[] = $saveButton;

        $tblYearAll = Term::useService()->getYearAllSinceYears(0);
        $tblCourseAll = DivisionCourse::useService()->getDivisionCourseAll();
        $courseNameList = array();
        if ($tblCourseAll) {
            array_walk($tblCourseAll, function (TblDivisionCourse $tblDivisionCourse) use (&$courseNameList) {
                if (!in_array($tblDivisionCourse->getName(), $courseNameList)) {
                    $courseNameList[] = $tblDivisionCourse->getName();
                }
            });
        }
        $tblTypeAll = DivisionCourse::useService()->getDivisionCourseTypeListWithoutTeacherGroup();

        $formRows[] = new FormRow(array(
            new FormColumn($tblDivisionCourse
                ? new Panel('Schuljahr', $tblDivisionCourse->getYearName(), Panel::PANEL_TYPE_INFO)
                : (new SelectBox('Data[Year]', 'Schuljahr', array('{{ Name }} {{ Description }}' => $tblYearAll), new Education()))->setRequired()
                , 6),
            new FormColumn($tblDivisionCourse
                ? new Panel('Typ', $tblDivisionCourse->getTypeName(), Panel::PANEL_TYPE_INFO)
                : (new SelectBox('Data[Type]', 'Typ', array('{{ Name }}' => $tblTypeAll)))
                    ->setRequired()
                    ->ajaxPipelineOnChange(ApiDivisionCourse::pipelineLoadSubjectSelectBox($Error, $Data))
                , 6)
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(
                (new AutoCompleter('Data[Name]', 'Name', 'z.B: 7a', $courseNameList, new Pen()))->setRequired()
                , 6),
            new FormColumn(
                new TextField('Data[Description]', 'zb: für Fortgeschrittene', 'Beschreibung', new Pen())
                , 6),
        ));
        if ($tblDivisionCourse) {
            if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                $formRows[] = new FormRow(array(
                    new FormColumn(array(
                        (new SelectBox('Data[Subject]', 'Fach', array('{{ Acronym }}-{{ Name }}' => Subject::useService()->getSubjectAll())))
                            ->setRequired()
                    ), 6),
                ));
            }
        } else {
            $formRows[] = new FormRow(array(
                new FormColumn(array(
                    ApiDivisionCourse::receiverBlock('', 'SubjectSelectBox')
                ), 6),
            ));
        }
        $formRows[] = new FormRow(array(
            new FormColumn(
                new CheckBox('Data[IsShownInPersonData]', 'Kurs bei den Personenstammdaten anzeigen', 1)
                , 6),
            new FormColumn(
                new CheckBox('Data[IsReporting]', 'Kurs wird bei festen Auswertungen angezeigt', 1)
                , 6),
//                    new FormColumn(
//                        new CheckBox('Data[IsUcs]', 'Kurs wird ins UCS übertragen', 1)
//                        , 4),
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(
                $buttonList
            )
        ));

        return (new Form(new FormGroup($formRows)))->disableSubmitAction();
    }

    /**
     * @param $Error
     * @param $Data
     *
     * @return SelectBox|null
     */
    public function loadSubjectSelectBox($Error, $Data): ?SelectBox
    {
        if (isset($Data['Type']) && ($tblType = DivisionCourse::useService()->getDivisionCourseTypeById($Data['Type']))) {
            if ($tblType->getIsCourseSystem()) {
                if (isset($Data['Subject'])) {
                    $global = $this->getGlobal();
                    $global->POST['Data']['Subject'] = $Data['Subject'];
                    $global->savePost();
                }

                $selectBox = (new SelectBox('Data[Subject]', 'Fach', array('{{ Acronym }}-{{ Name }}' => Subject::useService()->getSubjectAll())))
                    ->setRequired();
                if ($Error) {
                    $selectBox->setError($Error);
                }
                return $selectBox;
            }
        }

        return null;
    }

    /**
     * @param $DivisionCourseId
     * @param null $Filter
     *
     * @return string
     */
    public function loadDivisionCourseLinkContent($DivisionCourseId, $Filter = null): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $selectedList = array();
            if (($tblDivisionCourseList = DivisionCourse::useService()->getSubDivisionCourseListByDivisionCourse($tblDivisionCourse))) {
                foreach ($tblDivisionCourseList as $item) {
                    $selectedList[$item->getId()] = array(
                        'Name' => $item->getName() . (($description = $item->getDescription()) ? ' (' . $description . ')' : ''),
                        'Type' => $item->getTypeName(),
                        'Option' => (new Standard('', ApiDivisionCourse::getEndpoint(), new MinusSign(), array(), 'Kurs entfernen'))
                            ->ajaxPipelineOnClick(ApiDivisionCourse::pipelineRemoveDivisionCourse($tblDivisionCourse->getId(), $item->getId(), $Filter))
                    );
                }
            }

            $availableList = array();
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())
                && ($tblDivisionCourseAvailableList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear))
            ) {
                foreach ($tblDivisionCourseAvailableList as $tblDivisionCourseAvailable) {
                    // SekII-Kurse können nicht verknüpft werden, da diese anders funktionieren (Halbjahre + Schüler-Fächer)
                    if ($tblDivisionCourseAvailable->getType()->getIsCourseSystem()) {
                        continue;
                    }

                    // Lerngruppen der Lehrer überspringen
                    if ($tblDivisionCourseAvailable->getTypeIdentifier() == TblDivisionCourseType::TYPE_TEACHER_GROUP) {
                        continue;
                    }

                    if ($tblDivisionCourse->getId() != $tblDivisionCourseAvailable->getId() && !isset($selectedList[$tblDivisionCourseAvailable->getId()])) {
                        $availableList[$tblDivisionCourseAvailable->getId()] = array(
                            'Name' => $tblDivisionCourseAvailable->getName() . (($description = $tblDivisionCourseAvailable->getDescription()) ? ' (' . $description . ')' : ''),
                            'Type' => $tblDivisionCourseAvailable->getTypeName(),
                            'Option' => (new Standard('', ApiDivisionCourse::getEndpoint(), new PlusSign(), array(), 'Kurs hinzufügen'))
                                ->ajaxPipelineOnClick(ApiDivisionCourse::pipelineAddDivisionCourse($tblDivisionCourse->getId(), $tblDivisionCourseAvailable->getId(), $Filter))
                        );
                    }
                }
            }

            $columns = array(
                'Name' => 'Name',
                'Type' => 'Typ',
                'Option' => ''
            );
            $interactive = array(
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 0),
                    array('orderable' => false, 'width' => '1%', 'targets' => -1),
                ),
                'pageLength' => self::PAGE_LENGTH,
                'responsive' => false
            );
            if ($selectedList) {
                $left = (new TableData($selectedList, new Title('Ausgewählte', 'Unter-Kurse'), $columns, $interactive))
                    ->setHash(__NAMESPACE__ . 'DivisionCourseSelected');
            } else {
                $left = new Info('Keine Unter-Kurse ausgewählt');
            }
            if ($availableList) {
                $right = (new TableData($availableList, new Title('Verfügbare', 'Unter-Kurse'), $columns, $interactive))
                    ->setHash(__NAMESPACE__ . 'DivisionCourseAvailable');
            } else {
                $right = new Info('Keine weiteren Unter-Kurse verfügbar');
            }

            return new Layout(new LayoutGroup(array(new LayoutRow(array(
                new LayoutColumn($left, 6),
                new LayoutColumn($right, 6)
            )))));
        }

        return new Danger('Kurs nicht gefunden!', new Exclamation());
    }

    /**
     * @param null $DivisionCourseId
     * @param null $Filter
     *
     * @return Stage
     */
    public function frontendDivisionCourseShow($DivisionCourseId = null, $Filter = null): Stage
    {
        $stage = new Stage('Kursansicht', '');
        $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse', new ChevronLeft(), array('Filter' => $Filter))));

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());
            $stage->setDescription('Übersicht ' . $text . ' Schuljahr ' . new Bold($tblDivisionCourse->getYearName()));
            $text = $tblDivisionCourse->getType()->getIsCourseSystem() ? 'im ' . $text : 'in der ' . $text;
            if ($tblDivisionCourse->getDescription()) {
                $stage->setMessage($tblDivisionCourse->getDescription());
            }

            // Gruppenleiter
            $divisionTeacherList = array();
            if (($tblDivisionTeacherMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse,
                TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER, false, false))
            ) {
                foreach ($tblDivisionTeacherMemberList as $tblDivisionTeacherMember) {
                    if (($tblPerson = $tblDivisionTeacherMember->getServiceTblPerson())) {
                        $divisionTeacherList[] = array(
                            'FullName' => $tblPerson->getFullName(),
                            'Description' => $tblDivisionTeacherMember->getDescription()
                        );
                    }
                }
            }

            // Schülersprecher
            $representativeList = array();
            if (($tblRepresentativeMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse,
                TblDivisionCourseMemberType::TYPE_REPRESENTATIVE, false, false))
            ) {
                foreach ($tblRepresentativeMemberList as $tblRepresentativeMember) {
                    if (($tblPerson = $tblRepresentativeMember->getServiceTblPerson())) {
                        $representativeList[] = array(
                            'FullName' => $tblPerson->getFullName(),
                            'Description' => $tblRepresentativeMember->getDescription()
                        );
                    }
                }
            }

            // Elternvertreter
            $custodyList = array();
            if (($tblCustodyMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse,
                TblDivisionCourseMemberType::TYPE_CUSTODY, false, false))
            ) {
                foreach ($tblCustodyMemberList as $tblCustodyMember) {
                    if (($tblPerson = $tblCustodyMember->getServiceTblPerson())) {
                        $custodyList[] = array(
                            'FullName' => $tblPerson->getFullName(),
                            'Description' => $tblCustodyMember->getDescription()
                        );
                    }
                }
            }

            $backgroundColor = '#E0F0FF';
            $headerMemberColumnList[] = $this->getTableHeaderColumn('Name', $backgroundColor, '30%');
            $headerMemberColumnList[] = $this->getTableHeaderColumn('Beschreibung', $backgroundColor, '70%');

            $stage->setContent(
                ApiDivisionCourseStudent::receiverModal()
                . DivisionCourse::useService()->getDivisionCourseHeader($tblDivisionCourse)
                . new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            ApiDivisionCourseStudent::receiverBlock($this->loadDivisionCourseStudentContent($DivisionCourseId), 'DivisionCourseStudentContent')
                        ))
                    ), new \SPHERE\Common\Frontend\Layout\Repository\Title(new PersonGroup() . ' Schüler ' . $text .
                        ($tblDivisionCourse->getType()->getIsCourseSystem()
                            ? ''
                            : new Link('Bearbeiten', '/Education/Lesson/DivisionCourse/Student', new Pen(), array('DivisionCourseId' => $tblDivisionCourse->getId(), 'Filter' => $Filter))
                                . ' | '
                                . new Link('Sortieren', '/Education/Lesson/DivisionCourse/Member/Sort', new ResizeVertical(),
                                    array('DivisionCourseId' => $tblDivisionCourse->getId(), 'MemberTypeIdentifier' => TblDivisionCourseMemberType::TYPE_STUDENT, 'Filter' => $Filter))
                        )
                    )),

                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            ApiDivisionCourseStudent::receiverBlock($this->loadStudentSubjectContent($DivisionCourseId), 'StudentSubjectContent')
                        ))
                    )),

                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            empty($divisionTeacherList)
                                ? new Warning('Keine ' . $tblDivisionCourse->getDivisionTeacherName() . ' dem Kurs zugewiesen')
                                : $this->getTableCustom($headerMemberColumnList, $divisionTeacherList)
                        ))
                    ), new \SPHERE\Common\Frontend\Layout\Repository\Title(new Person() . ' ' . $tblDivisionCourse->getDivisionTeacherName() . ' ' . $text
                        . new Link('Bearbeiten', '/Education/Lesson/DivisionCourse/DivisionTeacher', new Pen(), array('DivisionCourseId' => $tblDivisionCourse->getId(), 'Filter' => $Filter))
                        . ' | '
                        . new Link('Sortieren', '/Education/Lesson/DivisionCourse/Member/Sort', new ResizeVertical(),
                            array('DivisionCourseId' => $tblDivisionCourse->getId(), 'MemberTypeIdentifier' => TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER, 'Filter' => $Filter))
                    )),

                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            empty($representativeList)
                                ? new Warning('Keine Schülersprecher dem Kurs zugewiesen')
                                : $this->getTableCustom($headerMemberColumnList, $representativeList)
                        ))
                    ), new \SPHERE\Common\Frontend\Layout\Repository\Title(new PersonGroup() . ' Schülersprecher ' . $text
                        . new Link('Bearbeiten', '/Education/Lesson/DivisionCourse/Representative', new Pen(), array('DivisionCourseId' => $tblDivisionCourse->getId(), 'Filter' => $Filter))
                        . ' | '
                        . new Link('Sortieren', '/Education/Lesson/DivisionCourse/Member/Sort', new ResizeVertical(),
                            array('DivisionCourseId' => $tblDivisionCourse->getId(), 'MemberTypeIdentifier' => TblDivisionCourseMemberType::TYPE_REPRESENTATIVE, 'Filter' => $Filter))
                    )),

                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            empty($custodyList)
                                ? new Warning('Keine Elternvertreter dem Kurs zugewiesen')
                                : $this->getTableCustom($headerMemberColumnList, $custodyList)
                        ))
                    ), new \SPHERE\Common\Frontend\Layout\Repository\Title(new PersonParent() . ' Elternvertreter ' . $text
                        . new Link('Bearbeiten', '/Education/Lesson/DivisionCourse/Custody', new Pen(), array('DivisionCourseId' => $tblDivisionCourse->getId(), 'Filter' => $Filter))
                        . ' | '
                        . new Link('Sortieren', '/Education/Lesson/DivisionCourse/Member/Sort', new ResizeVertical(),
                            array('DivisionCourseId' => $tblDivisionCourse->getId(), 'MemberTypeIdentifier' => TblDivisionCourseMemberType::TYPE_CUSTODY, 'Filter' => $Filter))
                    )),
                ))
            );


        } else {
            $stage->setContent(new Warning('Kurs nicht gefunden', new Exclamation()));
        }

        return $stage;
    }
}