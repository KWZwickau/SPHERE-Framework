<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourse;
use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourseMember;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
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
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
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
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    const PAGE_LENGTH = 15;

    private function getInteractiveLeft(): array
    {
        return array(
            'columnDefs' => array(
                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                array('orderable' => false, 'width' => '1%', 'targets' => -1),
            ),
            'pageLength' => self::PAGE_LENGTH,
            'responsive' => false
        );
    }

    private function getInteractiveRight(): array
    {
        return array(
            'columnDefs' => array(
                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                array('orderable' => false, 'width' => '50%', 'targets' => -1),
            ),
            'pageLength' => self::PAGE_LENGTH,
            'responsive' => false
        );
    }

    /**
     * @return Stage
     */
    public function frontendDivisionCourse(): Stage
    {
        $stage = new Stage('Kurs', 'Übersicht');

        $Filter['Year'] = -1;
        $stage->setContent(
            ApiDivisionCourse::receiverModal()
            . new Panel(new Filter() . ' Filter', $this->formFilter(), Panel::PANEL_TYPE_INFO)
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
                if (($tblSubCourseList = DivisionCourse::useService()->getSubDivisionCourseListByDivisionCourse($tblDivisionCourse))) {
                    $subCourses = array();
                    foreach ($tblSubCourseList as $tblSubCourse) {
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
                            new EyeOpen(), array('DivisionCourseId' => $tblDivisionCourse->getId()), 'Kurs einsehen')
                        . (new Standard('', ApiDivisionCourse::getEndpoint(), new Pen(), array(), 'Name des Kurses bearbeiten'))
                            ->ajaxPipelineOnClick(ApiDivisionCourse::pipelineOpenEditDivisionCourseModal($tblDivisionCourse->getId(), $Filter))
                        . (new Standard('', ApiDivisionCourse::getEndpoint(), new LinkIcon(), array(), 'Unter-Kurse verknüpfen'))
                            ->ajaxPipelineOnClick(ApiDivisionCourse::pipelineOpenLinkDivisionCourseModal($tblDivisionCourse->getId(), $Filter))
                        . (new Standard('', ApiDivisionCourse::getEndpoint(), new Remove(), array(), 'Kurs löschen'))
                            ->ajaxPipelineOnClick(ApiDivisionCourse::pipelineOpenDeleteDivisionCourseModal($tblDivisionCourse->getId(), $Filter))
                );

                if ($showExtraInfo) {
                    list($students, $genders) = DivisionCourse::useService()->getStudentInfoByDivisionCourse($tblDivisionCourse);
                    $item['Students'] = $students;
                    $item['Genders'] = $genders;

                    $item['Teachers'] = '';
                    if (($tblTeacherList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))) {
                        foreach ($tblTeacherList as $tblPersonTeacher) {
                            if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPersonTeacher))
                                && ($acronym = $tblTeacher->getAcronym())
                            ) {
                                $name = $tblPersonTeacher->getLastName() . ' (' . $acronym . ')';
                            } else {
                                $name = $tblPersonTeacher->getLastName();
                            }
                            $item['Teachers'] .= ($item['Teachers'] ? '<br/>' : '') . $name;
                        }
                    }

                    $item['Visibility'] = '';
                    if ($tblDivisionCourse->getIsShownInPersonData()) {
                        $item['Visibility'] = 'Stammdaten';
                    }
                    if ($tblDivisionCourse->getIsReporting()) {
                        $item['Visibility'] .= ($item['Visibility'] ? '<br/>' : '') . 'Auswertung';
                    }
                    if ($tblDivisionCourse->getIsUcs()) {
                        $item['Visibility'] .= ($item['Visibility'] ? '<br/>' : '') . 'UCS';
                    }
                } else {
                    $countActive = $tblDivisionCourse->getCountStudents();
                    $countInActive = $tblDivisionCourse->getCountInActiveStudents();
                    $toolTip = $countInActive . ($countInActive == 1 ? ' deaktivierter Schüler' : ' deaktivierte Schüler');
                    $students = $countActive . ($countInActive > 0 ? ' + '
                        . new ToolTip('(' . $countInActive . new \SPHERE\Common\Frontend\Icon\Repository\Info() . ')', $toolTip) : '');
                    $item['Students'] = $students;
                }

                $dataList[] = $item;
            }

            $columns = array(
                'Year' => 'Schuljahr',
                'Name' => 'Name',
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
     * @return Form
     */
    public function formFilter(): Form
    {
        $tblTypeAll = DivisionCourse::useService()->getDivisionCourseTypeAll();
        $tblYearAll = Term::useService()->getYearAll();
        if ($tblYearAll && Term::useService()->getYearByNow()) {

            $tblYearAll[] = new SelectBoxItem(-1, 'Aktuelle Übersicht');

            $Global = $this->getGlobal();
            $Global->POST['Filter']['Year'] = -1;
            $Global->savePost();
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
                    (new TextField('Filter[CourseName]', '7a', 'Kursname'))
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
     *
     * @return Form
     */
    public function formDivisionCourse($DivisionCourseId = null,$Filter = null, bool $setPost = false): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        $tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId);
        if ($setPost && $tblDivisionCourse) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Name'] = $tblDivisionCourse->getName();
            $Global->POST['Data']['Description'] = $tblDivisionCourse->getDescription();
            $Global->POST['Data']['IsShownInPersonData'] = $tblDivisionCourse->getIsShownInPersonData();
            $Global->POST['Data']['IsReporting'] = $tblDivisionCourse->getIsReporting();
            $Global->POST['Data']['IsUcs'] = $tblDivisionCourse->getIsUcs();
            $Global->savePost();
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
        $tblTypeAll = DivisionCourse::useService()->getDivisionCourseTypeAll();

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn($tblDivisionCourse
                        ? new Panel('Schuljahr', $tblDivisionCourse->getYearName(), Panel::PANEL_TYPE_INFO)
                        : (new SelectBox('Data[Year]', 'Schuljahr', array('{{ Name }} {{ Description }}' => $tblYearAll), new Education()))->setRequired()
                    , 6),
                    new FormColumn($tblDivisionCourse
                        ? new Panel('Typ', $tblDivisionCourse->getTypeName(), Panel::PANEL_TYPE_INFO)
                        : (new SelectBox('Data[Type]', 'Typ', array('{{ Name }}' => $tblTypeAll)))->setRequired()
                    , 6)
                )),
                new FormRow(array(
                    new FormColumn(
                        (new AutoCompleter('Data[Name]', 'Name', 'z.B: 7a', $courseNameList, new Pen()))->setRequired()
                    , 6),
                    new FormColumn(
                        new TextField('Data[Description]', 'zb: für Fortgeschrittene', 'Beschreibung', new Pen())
                    , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        new CheckBox('Data[IsShownInPersonData]', 'Kurs bei den Personenstammdaten anzeigen', 1)
                        , 4),
                    new FormColumn(
                        new CheckBox('Data[IsReporting]', 'Kurs wird bei festen Auswertungen angezeigt', 1)
                        , 4),
                    new FormColumn(
                        new CheckBox('Data[IsUcs]', 'Kurs wird ins UCS übertragen', 1)
                        , 4),
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
     *
     * @return Stage
     */
    public function frontendDivisionCourseShow($DivisionCourseId = null): Stage
    {
        $stage = new Stage('Kursansicht', '');
        $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse', new ChevronLeft())));

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());
            $stage->setDescription('Übersicht ' . $text . ' Schuljahr ' . new Bold($tblDivisionCourse->getYearName()));
            if ($tblDivisionCourse->getDescription()) {
                $stage->setMessage($tblDivisionCourse->getDescription());
            }

            $studentList = array();
            if (($tblStudentMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse,
                TblDivisionCourseMemberType::TYPE_STUDENT, true, false))
            ) {
                foreach ($tblStudentMemberList as $tblStudentMember) {
                    // todo verknüpfte Kurse
                    // todo weiter infos , bildungsgang bei berufsbildende Schule, fachrichtung
                    // todo Fächer, kurse sekII
                    if (($tblPerson = $tblStudentMember->getServiceTblPerson())) {
                        $isInActive = $tblStudentMember->isInActive();
                        $fullName = $tblPerson->getLastFirstName();
                        $address = ($tblAddress = $tblPerson->fetchMainAddress())
                            ? $tblAddress->getGuiString()
                            : new WarningText('Keine Adresse hinterlegt');

                        // todo bildungsgang aus TblStudentEducation und nicht Schülerakte
                        $tblCourse = Student::useService()->getCourseByPerson($tblPerson);
                        $course = $tblCourse ? $tblCourse->getName() : '';

                        $birthday = '';
                        $gender = '';
                        if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                            if ($tblCommon->getTblCommonBirthDates()) {
                                $birthday = $tblCommon->getTblCommonBirthDates()->getBirthday();
                                if ($tblGender = $tblCommon->getTblCommonBirthDates()->getTblCommonGender()) {
                                    $gender = $tblGender->getShortName();
                                }
                            }
                        }

                        if ($isInActive) {
                            $status = new ToolTip(new \SPHERE\Common\Frontend\Text\Repository\Danger(new Disable()), 'Deaktivierung: ' . $tblStudentMember->getLeaveDate());
//                            if ($tblYear && !Student::useService()->getMainDivisionByPersonAndYear($tblPerson, $tblYear)) {
//                                $option =  StudentStatus::receiverModal()
//                                    . (new \SPHERE\Common\Frontend\Link\Repository\Link('aktivieren', '#'))->ajaxPipelineOnClick(StudentStatus::pipelineActivateStudentSave(
//                                        $tblDivision->getId(),
//                                        $tblPerson->getId())
//                                    );
//                            } else {
//                                $option = '';
//                            }
                        } else {
                            $status = new SuccessText(new \SPHERE\Common\Frontend\Icon\Repository\Success());
//                            $option = StudentStatus::receiverModal()
//                                . (new Link('deaktivieren', '#'))->ajaxPipelineOnClick(StudentStatus::pipelineOpenDeactivateStudentModal(
//                                    $tblDivision->getId(),
//                                    $tblPerson->getId())
//                                );
                        }

                        $item = array(
                            'FullName' => $isInActive ? new Strikethrough($fullName) : $fullName,
                            'Address' => $isInActive ? new Strikethrough($address) : $address,
                            'Gender' => $isInActive ? new Strikethrough($gender) : $gender,
                            'Birthday' => $isInActive ? new Strikethrough($birthday) : $birthday,
                            'Course' => $isInActive ? new Strikethrough($course) : $course,
                            'Status' => $status,
//                            'Option' => $option
                        );

                        $studentList[] = $item;
                    }
                }
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

            $studentColumnList = array(
                'FullName' => 'Schüler',
                'Gender'   => 'Ge&shy;schlecht',
                'Birthday' => 'Geburts&shy;datum',
                'Address'  => 'Adresse',
                'Course'   => 'Bildungsgang'
            );
//            if ($IsSekTwo) {
//                $studentColumnList['AdvancedCourse1'] = '1. LK';
//                $studentColumnList['AdvancedCourse2'] = '2. LK';
//                $studentColumnList['BasicCourses'] = 'Grundkurse';
//            } else {
//                $studentColumnList['Subjects'] =  'Fächer';
//            }
            $studentColumnList['Status'] = 'Status';
//            $studentColumnList['Option'] = ' ';

            $memberColumnList = array(
                'FullName' => 'Name',
                'Description' => 'Beschreibung'
            );

            // todo TableCustom und keine Sortierung
//            $interactiveMember = array(
//                'columnDefs' => array(
//                    array('orderable' => false, 'width' => '40%', 'targets' => 0),
//                    array('orderable' => false, 'targets' => 1),
//                ),
//                'pageLength' => -1,
//                'paging' => false,
//                'info' => false,
//                'searching' => false,
//                'responsive' => false
//            );
            $interactiveMember = false;

            $stage->setContent(
                DivisionCourse::useService()->getDivisionCourseHeader($tblDivisionCourse)
                . new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            empty($studentList)
                                ? new Warning('Keine Schüler dem Kurs zugewiesen')
                                : new TableData($studentList, null, $studentColumnList, false)
                        ))
                    ), new \SPHERE\Common\Frontend\Layout\Repository\Title(new PersonGroup() . ' Schüler in der ' . $text
                        . new Link('Bearbeiten', '/Education/Lesson/DivisionCourse/Student', new Pen(), array('DivisionCourseId' => $tblDivisionCourse->getId()))
                        . ' | '
                        . new Link('Sortieren', '/Education/Lesson/DivisionCourse/Member/Sort', new ResizeVertical(),
                            array('DivisionCourseId' => $tblDivisionCourse->getId(), 'MemberTypeIdentifier' => TblDivisionCourseMemberType::TYPE_STUDENT))
                    )),

                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            empty($divisionTeacherList)
                                ? new Warning('Keine ' . $tblDivisionCourse->getDivisionTeacherName() . ' dem Kurs zugewiesen')
                                : (new TableData($divisionTeacherList, null, $memberColumnList, $interactiveMember))->setHash(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER)
                        ))
                    ), new \SPHERE\Common\Frontend\Layout\Repository\Title(new Person() . ' ' . $tblDivisionCourse->getDivisionTeacherName() . ' in der ' . $text
                        . new Link('Bearbeiten', '/Education/Lesson/DivisionCourse/DivisionTeacher', new Pen(), array('DivisionCourseId' => $tblDivisionCourse->getId()))
                        . ' | '
                        . new Link('Sortieren', '/Education/Lesson/DivisionCourse/Member/Sort', new ResizeVertical(),
                            array('DivisionCourseId' => $tblDivisionCourse->getId(), 'MemberTypeIdentifier' => TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))
                    )),

                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            empty($representativeList)
                                ? new Warning('Keine Schülersprecher dem Kurs zugewiesen')
                                : (new TableData($representativeList, null, $memberColumnList, $interactiveMember))->setHash(TblDivisionCourseMemberType::TYPE_REPRESENTATIVE)
                        ))
                    ), new \SPHERE\Common\Frontend\Layout\Repository\Title(new PersonGroup() . ' Schülersprecher in der ' . $text
                        . new Link('Bearbeiten', '/Education/Lesson/DivisionCourse/Representative', new Pen(), array('DivisionCourseId' => $tblDivisionCourse->getId()))
                        . ' | '
                        . new Link('Sortieren', '/Education/Lesson/DivisionCourse/Member/Sort', new ResizeVertical(),
                            array('DivisionCourseId' => $tblDivisionCourse->getId(), 'MemberTypeIdentifier' => TblDivisionCourseMemberType::TYPE_REPRESENTATIVE))
                    )),

                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            empty($custodyList)
                                ? new Warning('Keine Elternvertreter dem Kurs zugewiesen')
                                : (new TableData($custodyList, null, $memberColumnList, $interactiveMember))->setHash(TblDivisionCourseMemberType::TYPE_CUSTODY)
                        ))
                    ), new \SPHERE\Common\Frontend\Layout\Repository\Title(new PersonParent() . ' Elternvertreter in der ' . $text
                        . new Link('Bearbeiten', '/Education/Lesson/DivisionCourse/Custody', new Pen(), array('DivisionCourseId' => $tblDivisionCourse->getId()))
                        . ' | '
                        . new Link('Sortieren', '/Education/Lesson/DivisionCourse/Member/Sort', new ResizeVertical(),
                            array('DivisionCourseId' => $tblDivisionCourse->getId(), 'MemberTypeIdentifier' => TblDivisionCourseMemberType::TYPE_CUSTODY))
                    )),
                ))
            );


        } else {
            $stage->setContent(new Warning('Kurs nicht gefunden', new Exclamation()));
        }

        return $stage;
    }

    /**
     * @param null $DivisionCourseId
     *
     * @return Stage
     */
    public function frontendDivisionCourseStudent($DivisionCourseId = null): Stage
    {
        $stage = new Stage('Schüler', '');
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse/Show', new ChevronLeft(), array('DivisionCourseId' => $tblDivisionCourse->getId()))));
            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());
            $stage->setDescription('der ' . $text . ' Schuljahr ' . new Bold($tblDivisionCourse->getYearName()));
            if ($tblDivisionCourse->getDescription()) {
                $stage->setMessage($tblDivisionCourse->getDescription());
            }

            $stage->setContent(
                DivisionCourse::useService()->getDivisionCourseHeader($tblDivisionCourse)
                . ApiDivisionCourseMember::receiverBlock($this->loadStudentContent($DivisionCourseId, 'StudentSearch'), 'StudentContent')
            );
        } else {
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse', new ChevronLeft())));
            $stage->setContent(new Warning('Kurs nicht gefunden', new Exclamation()));
        }

        return $stage;
    }

    /**
     * @param $DivisionCourseId
     * @param $AddStudentVariante
     *
     * @return string
     */
    public function loadStudentContent($DivisionCourseId, $AddStudentVariante): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $text = 'Schüler';
            $selectedList = array();
            if (($tblMemberList =  DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, TblDivisionCourseMemberType::TYPE_STUDENT, true, false))) {
                $count = 0;
                foreach ($tblMemberList as $tblMember) {
                    if (($tblPerson = $tblMember->getServiceTblPerson())) {
                        $isInActive = $tblMember->isInActive();
                        $name = $tblPerson->getLastFirstName();
                        $address = ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt');
                        $selectedList[$tblPerson->getId()] = array(
                            'Number' => ++$count,
                            'Name' => $isInActive ? new Strikethrough($name) : $name,
                            'Address' => $isInActive ? new Strikethrough($address) : $address,
                            // todo deaktivieren für Klassenwechsel im Schuljahr
                            'Option' => (new Standard('', ApiDivisionCourseMember::getEndpoint(), new MinusSign(), array(),  $text . ' entfernen'))
                                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineRemoveStudent($tblDivisionCourse->getId(), $tblPerson->getId(), $AddStudentVariante))
                        );
                    }
                }
            }

            $columns = array(
                'Number' => '#',
                'Name' => 'Name',
                'Address' => 'Adresse',
                'Option' => ''
            );
            if ($selectedList) {
                $left = (new TableData($selectedList, null, $columns, array(
                    'columnDefs' => array(
                        array('type' => 'natural', 'targets' => 0),
                        array('orderable' => false, 'width' => '1%', 'targets' => -1),
                    ),
                    'pageLength' => self::PAGE_LENGTH,
                    'responsive' => false
                )))->setHash(__NAMESPACE__ . 'StudentSelected');
            } else {
                $left = new Info('Keine ' . $text . ' ausgewählt');
            }

            return new Layout(new LayoutGroup(array(new LayoutRow(array(
                new LayoutColumn(
                    new \SPHERE\Common\Frontend\Layout\Repository\Title('Ausgewählte', $text)
                    . $left
                    , 6),
                new LayoutColumn(
                    new \SPHERE\Common\Frontend\Layout\Repository\Title('Verfügbare', 'Schüler')
                    . (new Standard('Schülersuche', ApiDivisionCourseMember::getEndpoint(), new Search()))
                        ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineLoadAddStudentContent($DivisionCourseId, 'StudentSearch'))
                    . (new Standard('Kurs-Schüler', ApiDivisionCourseMember::getEndpoint(), new Select()))
                        ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineLoadAddStudentContent($DivisionCourseId, 'CourseSelect'))
                    . (new Standard('Interessentensuche', ApiDivisionCourseMember::getEndpoint(), new Search()))
                        ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineLoadAddStudentContent($DivisionCourseId, 'ProspectSearch'))
                    . new Container('&nbsp;')
                    . ApiDivisionCourseMember::receiverBlock($this->loadAddStudentContent($DivisionCourseId, $AddStudentVariante), 'AddStudentContent')
                    , 6)
            )))));
        }

        return new Danger('Kurs nicht gefunden!', new Exclamation());
    }

    /**
     * @param $DivisionCourseId
     * @param $AddStudentVariante
     *
     * @return string
     */
    public function loadAddStudentContent($DivisionCourseId, $AddStudentVariante): string
    {
        switch ($AddStudentVariante) {
            case 'StudentSearch':
                return new Panel(
                    'Schüler',
                    new Form(new FormGroup(new FormRow(new FormColumn(array(
                        (new TextField(
                            'Data[Search]',
                            '',
                            'Suche',
                            new Search()
                        ))->ajaxPipelineOnKeyUp(ApiDivisionCourseMember::pipelineSearchPerson($DivisionCourseId))
                    )))))
                    . ApiDivisionCourseMember::receiverBlock($this->loadPersonSearch($DivisionCourseId, ''), 'SearchPerson')
                    , Panel::PANEL_TYPE_INFO
                );
            case 'CourseSelect':
                if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
                    && ($tblYear = $tblDivisionCourse->getServiceTblYear())
                ) {
                    $tblCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear);
                } else {
                    $tblCourseList = false;
                }

                return new Panel(
                    'Kursauswahl',
                    new Form(new FormGroup(new FormRow(new FormColumn(array(
                        (new SelectBox(
                            'Data[DivisionCourseId]',
                            'Kurs',
                            array('{{ Name }} {{ Description }} ' => $tblCourseList),
                            new Select()
                        ))->ajaxPipelineOnChange(ApiDivisionCourseMember::pipelineSelectDivisionCourse($DivisionCourseId))
                    )))))
                    . ApiDivisionCourseMember::receiverBlock($this->loadSelectDivisionCourse($DivisionCourseId, null), 'SearchPerson')
                    , Panel::PANEL_TYPE_INFO
                );
            case 'ProspectSearch':
                return 'Interessenten suche';
        }

        return '';
    }

    /**
     * @param $DivisionCourseId
     * @param $Search
     *
     * @return string
     */
    public function loadPersonSearch($DivisionCourseId, $Search): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }

        if ($Search != '' && strlen($Search) > 2) {
            $resultList = array();
            $result = '';
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())
                && ($tblPersonList = \SPHERE\Application\People\Person\Person::useService()->getPersonListLike($Search))
            ) {
                $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
                foreach ($tblPersonList as $tblPerson) {
                    // nur nach Schülern suchen
                    if (Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                        if (($tblDivisionCourse->getType()->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION)
                            && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                            && ($tblDivisionCourseDivision = $tblStudentEducation->getTblDivision())
                        ) {
                            // Schüler ist bereits im Kurs
                            if ($tblDivisionCourseDivision->getId() == $tblDivisionCourse->getId()) {
                                continue;
                            }
                            $option = new WarningText($tblDivisionCourseDivision->getName());
                        } elseif (($tblDivisionCourse->getType()->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)
                            && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                            && ($tblDivisionCourseCoreGroup = $tblStudentEducation->getTblCoreGroup())
                        ) {
                            // Schüler ist bereits im Kurs
                            if ($tblDivisionCourseCoreGroup->getId() == $tblDivisionCourse->getId()) {
                                continue;
                            }
                            $option = new WarningText($tblDivisionCourseCoreGroup->getName());
                        } else {
                            // Schüler ist bereits im Kurs
                            if (DivisionCourse::useService()->getDivisionCourseMemberByPerson(
                                $tblDivisionCourse,
                                DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT),
                                $tblPerson
                            )) {
                                continue;
                            }
                            $option = (new Standard('', ApiDivisionCourseMember::getEndpoint(), new PlusSign(), array(),  'Schüler hinzufügen'))
                                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineAddStudent($tblDivisionCourse->getId(), $tblPerson->getId(), 'StudentSearch'));
                        }

                        $resultList[] = array(
                            'Name' => $tblPerson->getLastFirstName(),
                            'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                            'Option' => $option
                        );
                    }
                }

                $result = new TableData(
                    $resultList,
                    null,
                    array(
                        'Name' => 'Name',
                        'Address' => 'Adresse',
                        'Option' => ''
                    ),
                    array(
                        'columnDefs' => array(
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                        ),
                        'pageLength' => -1,
                        'paging' => false,
                        'info' => false,
                        'searching' => false,
                        'responsive' => false
                    )
                );
            }

            if (empty($resultList)) {
                $result = new Warning('Es wurden keine entsprechenden Schüler gefunden.', new Ban());
            }
        } else {
            $result =  new Warning('Bitte geben Sie mindestens 3 Zeichen in die Suche ein.', new Exclamation());
        }

        return $result;
    }

    /**
     * @param $DivisionCourseId
     * @param $SelectedDivisionCourseId
     *
     * @return string
     */
    public function loadSelectDivisionCourse($DivisionCourseId, $SelectedDivisionCourseId): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }

        if (($tblSelectedDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($SelectedDivisionCourseId))
            && ($tblYear = $tblSelectedDivisionCourse->getServiceTblYear())
        ) {
            $resultList = array();
            $result = '';
            if (($tblPersonList =  DivisionCourse::useService()->getDivisionCourseMemberListBy($tblSelectedDivisionCourse, TblDivisionCourseMemberType::TYPE_STUDENT))) {
                foreach ($tblPersonList as $tblPerson) {
                    // todo als methode auslagern
                    if (($tblDivisionCourse->getType()->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION)
                        && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                        && ($tblDivisionCourseDivision = $tblStudentEducation->getTblDivision())
                    ) {
                        // Schüler ist bereits im Kurs
                        if ($tblDivisionCourseDivision->getId() == $tblDivisionCourse->getId()) {
                            continue;
                        }
                        $option = new WarningText($tblDivisionCourseDivision->getName());
                    } elseif (($tblDivisionCourse->getType()->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)
                        && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                        && ($tblDivisionCourseCoreGroup = $tblStudentEducation->getTblCoreGroup())
                    ) {
                        // Schüler ist bereits im Kurs
                        if ($tblDivisionCourseCoreGroup->getId() == $tblDivisionCourse->getId()) {
                            continue;
                        }
                        $option = new WarningText($tblDivisionCourseCoreGroup->getName());
                    } else {
                        // Schüler ist bereits im Kurs
                        if (DivisionCourse::useService()->getDivisionCourseMemberByPerson(
                            $tblDivisionCourse,
                            DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT),
                            $tblPerson
                        )) {
                            continue;
                        }
                        $option = (new Standard('', ApiDivisionCourseMember::getEndpoint(), new PlusSign(), array(),  'Schüler hinzufügen'))
                            ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineAddStudent($tblDivisionCourse->getId(), $tblPerson->getId(), 'CourseSelect'));
                    }

                    $resultList[] = array(
                        'Name' => $tblPerson->getLastFirstName(),
                        'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                        'Option' => $option
                    );
                }

                $result = new TableData(
                    $resultList,
                    null,
                    array(
                        'Name' => 'Name',
                        'Address' => 'Adresse',
                        'Option' => ''
                    ),
                    array(
                        'columnDefs' => array(
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                        ),
                        'pageLength' => -1,
                        'paging' => false,
                        'info' => false,
                        'searching' => false,
                        'responsive' => false
                    )
                );
            }
            if (empty($resultList)) {
                $result = new Warning('Es wurden keine entsprechenden Schüler gefunden.', new Ban());
            }
        } else {
            $result =  new Warning('Bitte wählen Sie einen Kurs aus.', new Exclamation());
        }

        return $result;
    }

    /**
     * @param null $DivisionCourseId
     *
     * @return Stage
     */
    public function frontendDivisionCourseDivisionTeacher($DivisionCourseId = null): Stage
    {
        $stage = new Stage('Klassenlehrer', '');
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $stage->setTitle($tblDivisionCourse->getDivisionTeacherName());
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse/Show', new ChevronLeft(), array('DivisionCourseId' => $tblDivisionCourse->getId()))));
            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());
            $stage->setDescription('der ' . $text . ' Schuljahr ' . new Bold($tblDivisionCourse->getYearName()));
            if ($tblDivisionCourse->getDescription()) {
                $stage->setMessage($tblDivisionCourse->getDescription());
            }

            $stage->setContent(
                DivisionCourse::useService()->getDivisionCourseHeader($tblDivisionCourse)
                . ApiDivisionCourseMember::receiverBlock($this->loadDivisionTeacherContent($DivisionCourseId), 'DivisionTeacherContent')
            );
        } else {
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse', new ChevronLeft())));
            $stage->setContent(new Warning('Kurs nicht gefunden', new Exclamation()));
        }

        return $stage;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadDivisionTeacherContent($DivisionCourseId): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $text = $tblDivisionCourse->getDivisionTeacherName();
            $selectedList = array();
            if (($tblMemberList =  DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse,
                TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER, false, false))
            ) {
                foreach ($tblMemberList as $tblMember) {
                    if (($tblPerson = $tblMember->getServiceTblPerson())) {
                        $selectedList[$tblPerson->getId()] = array(
                            'Name' => $tblPerson->getLastFirstName(),
                            'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                            'Description' => $tblMember->getDescription(),
                            'Option' => (new Standard('', ApiDivisionCourseMember::getEndpoint(), new MinusSign(), array(),  $text . ' entfernen'))
                                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineRemoveDivisionTeacher($tblDivisionCourse->getId(), $tblMember->getId()))
                        );
                    }
                }
            }

            $availableList = array();
            if (($tblGroup = Group::useService()->getGroupByMetaTable('TEACHER'))
                && ($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))
            ) {
                foreach ($tblPersonList as $tblPerson) {
                    if (!isset($selectedList[$tblPerson->getId()])) {
                        $availableList[$tblPerson->getId()] = array(
                            'Name' => $tblPerson->getLastFirstName(),
                            'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                            'Option' => (new Form(
                                new FormGroup(
                                    new FormRow(array(
                                        new FormColumn(
                                            new TextField('Data[Description]', 'z.B.: Stellvertreter')
                                            , 9),
                                        new FormColumn(
                                            (new Standard('', ApiDivisionCourseMember::getEndpoint(), new PlusSign(), array(), 'Hinzufügen'))
                                                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineAddDivisionTeacher($DivisionCourseId, $tblPerson->getId()))
                                            , 3)
                                    ))
                                )
                            ))->__toString()
                        );
                    }
                }
            }

            $columns = array(
                'Name' => 'Name',
                'Address' => 'Adresse',
                'Description' => 'Beschreibung',
                'Option' => ''
            );
            if ($selectedList) {
                $left = (new TableData($selectedList, new Title('Ausgewählte', $text), $columns, $this->getInteractiveLeft()))
                    ->setHash(__NAMESPACE__ . 'DivisionTeacherSelected');
            } else {
                $left = new Info('Keine ' . $text . ' ausgewählt');
            }
            if ($availableList) {
                unset($columns['Description']);
                $right = (new TableData($availableList, new Title('Verfügbare', $text), $columns, $this->getInteractiveRight()))
                    ->setHash(__NAMESPACE__ . 'DivisionTeacherAvailable');
            } else {
                $right = new Info('Keine weiteren ' . $text . ' verfügbar');
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
     *
     * @return Stage
     */
    public function frontendDivisionCourseRepresentative($DivisionCourseId = null): Stage
    {
        $stage = new Stage('Schülersprecher', '');
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse/Show', new ChevronLeft(), array('DivisionCourseId' => $tblDivisionCourse->getId()))));
            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());
            $stage->setDescription('der ' . $text . ' Schuljahr ' . new Bold($tblDivisionCourse->getYearName()));
            if ($tblDivisionCourse->getDescription()) {
                $stage->setMessage($tblDivisionCourse->getDescription());
            }

            $stage->setContent(
                DivisionCourse::useService()->getDivisionCourseHeader($tblDivisionCourse)
                . ApiDivisionCourseMember::receiverBlock($this->loadRepresentativeContent($DivisionCourseId), 'RepresentativeContent')
            );
        } else {
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse', new ChevronLeft())));
            $stage->setContent(new Warning('Kurs nicht gefunden', new Exclamation()));
        }

        return $stage;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadRepresentativeContent($DivisionCourseId): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $text = 'Schülersprecher';
            $selectedList = array();
            if (($tblMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse,
                TblDivisionCourseMemberType::TYPE_REPRESENTATIVE, false, false))
            ) {
                foreach ($tblMemberList as $tblMember) {
                    if (($tblPerson = $tblMember->getServiceTblPerson())) {
                        $selectedList[$tblPerson->getId()] = array(
                            'Name' => $tblPerson->getLastFirstName(),
                            'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                            'Description' => $tblMember->getDescription(),
                            'Option' => (new Standard('', ApiDivisionCourseMember::getEndpoint(), new MinusSign(), array(),  $text . ' entfernen'))
                                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineRemoveRepresentative($tblDivisionCourse->getId(), $tblMember->getId()))
                        );
                    }
                }
            }

            $availableList = array();
            if (($tblPersonList = $tblDivisionCourse->getStudents())) {
                foreach ($tblPersonList as $tblPerson) {
                    if (!isset($selectedList[$tblPerson->getId()])) {
                        $availableList[$tblPerson->getId()] = array(
                            'Name' => $tblPerson->getLastFirstName(),
                            'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                            'Option' => (new Form(
                                new FormGroup(
                                    new FormRow(array(
                                        new FormColumn(
                                            new TextField('Data[Description]', 'z.B.: Stellvertreter')
                                            , 9),
                                        new FormColumn(
                                            (new Standard('', ApiDivisionCourseMember::getEndpoint(), new PlusSign(), array(), 'Hinzufügen'))
                                                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineAddRepresentative($DivisionCourseId, $tblPerson->getId()))
                                            , 3)
                                    ))
                                )
                            ))->__toString()
                        );
                    }
                }
            }

            $columns = array(
                'Name' => 'Name',
                'Address' => 'Adresse',
                'Description' => 'Beschreibung',
                'Option' => ''
            );
            if ($selectedList) {
                $left = (new TableData($selectedList, new Title('Ausgewählte', $text), $columns, $this->getInteractiveLeft()))
                    ->setHash(__NAMESPACE__ . 'RepresentativeSelected');
            } else {
                $left = new Info('Keine ' . $text . ' ausgewählt');
            }
            if ($availableList) {
                unset($columns['Description']);
                $right = (new TableData($availableList, new Title('Verfügbare', $text), $columns, $this->getInteractiveRight()))
                    ->setHash(__NAMESPACE__ . 'RepresentativeAvailable');
            } else {
                $right = new Info('Keine weiteren ' . $text . ' verfügbar');
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
     *
     * @return Stage
     */
    public function frontendDivisionCourseCustody($DivisionCourseId = null): Stage
    {
        $stage = new Stage('Elternvertreter', '');
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse/Show', new ChevronLeft(), array('DivisionCourseId' => $tblDivisionCourse->getId()))));
            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());
            $stage->setDescription('der ' . $text . ' Schuljahr ' . new Bold($tblDivisionCourse->getYearName()));
            if ($tblDivisionCourse->getDescription()) {
                $stage->setMessage($tblDivisionCourse->getDescription());
            }

            $stage->setContent(
                DivisionCourse::useService()->getDivisionCourseHeader($tblDivisionCourse)
                . ApiDivisionCourseMember::receiverBlock($this->loadCustodyContent($DivisionCourseId), 'CustodyContent')
            );
        } else {
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse', new ChevronLeft())));
            $stage->setContent(new Warning('Kurs nicht gefunden', new Exclamation()));
        }

        return $stage;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadCustodyContent($DivisionCourseId): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $text = 'Elternvertreter';
            $selectedList = array();
            if (($tblMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse,
                TblDivisionCourseMemberType::TYPE_CUSTODY, false, false))
            ) {
                foreach ($tblMemberList as $tblMember) {
                    if (($tblPerson = $tblMember->getServiceTblPerson())) {
                        $selectedList[$tblPerson->getId()] = array(
                            'Name' => $tblPerson->getLastFirstName(),
                            'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                            'Description' => $tblMember->getDescription(),
                            'Option' => (new Standard('', ApiDivisionCourseMember::getEndpoint(), new MinusSign(), array(),  $text . ' entfernen'))
                                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineRemoveCustody($tblDivisionCourse->getId(), $tblMember->getId()))
                        );
                    }
                }
            }

            $availableList = array();
            if (($tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt'))
                && ($tblPersonList = $tblDivisionCourse->getStudents())
            ) {
                foreach ($tblPersonList as $tblPerson) {
                    if (($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType))) {
                        foreach ($tblRelationshipList as $tblToPerson) {
                            if (($tblPersonCustody = $tblToPerson->getServiceTblPersonFrom())) {
                                if (!isset($selectedList[$tblPersonCustody->getId()])) {
                                    $availableList[$tblPersonCustody->getId()] = array(
                                        'Name' => $tblPersonCustody->getLastFirstName(),
                                        'Address' => ($tblAddress = $tblPersonCustody->fetchMainAddress())
                                            ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                                        'Option' => (new Form(
                                            new FormGroup(
                                                new FormRow(array(
                                                    new FormColumn(
                                                        new TextField('Data[Description]', 'z.B.: Stellvertreter')
                                                        , 9),
                                                    new FormColumn(
                                                        (new Standard('', ApiDivisionCourseMember::getEndpoint(), new PlusSign(), array(), 'Hinzufügen'))
                                                            ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineAddCustody($DivisionCourseId, $tblPersonCustody->getId()))
                                                        , 3)
                                                ))
                                            )
                                        ))->__toString()
                                    );
                                }
                            }
                        }
                    }
                }
            }

            $columns = array(
                'Name' => 'Name',
                'Address' => 'Adresse',
                'Description' => 'Beschreibung',
                'Option' => ''
            );
            if ($selectedList) {
                $left = (new TableData($selectedList, new Title('Ausgewählte', $text), $columns, $this->getInteractiveLeft()))
                    ->setHash(__NAMESPACE__ . 'CustodySelected');
            } else {
                $left = new Info('Keine ' . $text . ' ausgewählt');
            }
            if ($availableList) {
                unset($columns['Description']);
                $right = (new TableData($availableList, new Title('Verfügbare', $text), $columns, $this->getInteractiveRight()))
                    ->setHash(__NAMESPACE__ . 'CustodyAvailable');
            } else {
                $right = new Info('Keine weiteren ' . $text . ' verfügbar');
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
     * @param string $MemberTypeIdentifier
     *
     * @return Stage
     */
    public function frontendMemberSort($DivisionCourseId = null, string $MemberTypeIdentifier = ''): Stage
    {
        if (($tblDivisionCourseMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier($MemberTypeIdentifier))) {
            $member = $tblDivisionCourseMemberType->getName();
        } else {
            $member = 'Mitglieder';
        }

        $stage = new Stage($member . ' sortieren', '');
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            if ($MemberTypeIdentifier == TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER) {
                $stage->setTitle($tblDivisionCourse->getDivisionTeacherName());
            }
            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());
            $stage->setDescription('der ' . $text . ' Schuljahr ' . new Bold($tblDivisionCourse->getYearName()));
            if ($tblDivisionCourse->getDescription()) {
                $stage->setMessage($tblDivisionCourse->getDescription());
            }

            $buttonList[] = (new Standard('Zurück', '/Education/Lesson/DivisionCourse/Show', new ChevronLeft(), array('DivisionCourseId' => $tblDivisionCourse->getId())));
            $buttonList[] = (new Standard(
                'Sortierung alphabetisch', ApiDivisionCourseMember::getEndpoint(), new ResizeVertical()))
                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineOpenSortMemberModal($tblDivisionCourse->getId(), $MemberTypeIdentifier,  'Sortierung alphabetisch'));
            $buttonList[] = (new Standard(
                'Sortierung Geschlecht (alphabetisch)', ApiDivisionCourseMember::getEndpoint(), new ResizeVertical()))
                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineOpenSortMemberModal($tblDivisionCourse->getId(), $MemberTypeIdentifier, 'Sortierung Geschlecht (alphabetisch)'));

            $stage->setContent(
                ApiDivisionCourseMember::receiverModal()
                . DivisionCourse::useService()->getDivisionCourseHeader($tblDivisionCourse)
                . new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn($buttonList),
                            new LayoutColumn(
                                ApiDivisionCourseMember::receiverBlock($this->loadSortMemberContent($DivisionCourseId, $MemberTypeIdentifier), 'SortMemberContent')
                            )
                        ))
                    ))
                ))
            );
        } else {
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse', new ChevronLeft())));
            $stage->setContent(new Warning('Kurs nicht gefunden', new Exclamation()));
        }

        return $stage;
    }

    /**
     * @param $DivisionCourseId
     * @param string $MemberTypeIdentifier
     *
     * @return string
     */
    public function loadSortMemberContent($DivisionCourseId, string $MemberTypeIdentifier): string
    {
        $memberTable = array();
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && $tblMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, $MemberTypeIdentifier, true, false)
        ) {
            $count = 0;
            foreach ($tblMemberList as $tblMember) {
                if (($tblPerson = $tblMember->getServiceTblPerson())){
                    $count++;
                    $isInActive = $tblMember->isInActive();

                    $name = new ResizeVertical() . ' ' . $tblPerson->getLastFirstName();
                    $address = ($tblAddress = $tblPerson->fetchMainAddress())
                        ? $tblAddress->getGuiString()
                        : new WarningText('Keine Adresse hinterlegt');
                    $birthday = '';
                    $gender = '';
                    if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                        if ($tblCommon->getTblCommonBirthDates()) {
                            $birthday = $tblCommon->getTblCommonBirthDates()->getBirthday();
                            if ($tblGender = $tblCommon->getTblCommonBirthDates()->getTblCommonGender()) {
                                $gender = $tblGender->getShortName();
                            }
                        }
                    }

                    $description = $tblMember->getDescription();

                    $memberTable[] = array(
                        'Number' => $isInActive ? new Strikethrough($count) : $count,
                        'Name' => new PullClear(new PullLeft($isInActive ? new Strikethrough($name) : $name)),
                        'Description' => $isInActive ? new Strikethrough($description) : $description,
                        'Gender' => $isInActive ? new Strikethrough($gender) : $gender,
                        'Birthday' => $isInActive ? new Strikethrough($birthday) : $birthday,
                        'Address' => $isInActive ? new Strikethrough($address) : $address,
                    );
                }
            }
        }

        $columns = array(
            'Number'        => '#',
            'Name'          => 'Name',
            'Description'   => 'Be&shy;schreib&shy;ung',
            'Gender'        => 'Ge&shy;schlecht',
            'Birthday'      => 'Geburts&shy;datum',
            'Address'       => 'Adresse'
        );

        if ($MemberTypeIdentifier == TblDivisionCourseMemberType::TYPE_STUDENT) {
            unset($columns['Description']);
        }

        return new TableData($memberTable, null, $columns,
            array(
                'rowReorderColumn' => 1,
                'ExtensionRowReorder' => array(
                    'Enabled' => true,
                    'Url'     => '/Api/Education/ClassRegister/Reorder',
                    'Data'    => array('DivisionCourseId' => $DivisionCourseId, 'MemberTypeIdentifier' => $MemberTypeIdentifier)
                ),
                'columnDefs' => array(
                    array('type'  => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                    array('width' => '40%', 'targets' => -1),
                ),
                'pageLength' => -1,
                'paging' => false,
                'info' => false,
                'searching' => false,
                'responsive' => false
            )
        );
    }
}