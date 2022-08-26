<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourse;
use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourseMember;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
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
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
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
        }

        $tblDivisionCourseList = array();
        // aktuelle Übersicht
        if (isset($Filter['Year']) && $Filter['Year'] == -1) {
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

                $dataList[] = array(
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
            }

            return $addLink . new TableData(
                $dataList,
                null,
                array(
                    'Year' => 'Schuljahr',
                    'Name' => 'Name',
                    'Description' => 'Beschreibung',
                    'Type' => 'Typ',
                    'SubCourses' => 'Unter-Kurse',
                    'Option' => '&nbsp;'
                ),
                array(
                    'columnDefs' => array(
                        array('type' => 'natural', 'targets' => 1),
                    ),
                    'order'      => array(array(0, 'asc'), array(1, 'asc')),
//                    'pageLength' => -1,
//                    'paging'     => false,
//                    'info'       => false,
//                    'searching'  => false,
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
                    , 6),
                new FormColumn(
                    (new SelectBox('Filter[Type]', 'Typ', array('{{ Name }}' => $tblTypeAll)))
                        ->ajaxPipelineOnChange(ApiDivisionCourse::pipelineLoadDivisionCourseContent())
                    , 6)
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
                    array_push($courseNameList, $tblDivisionCourse->getName());
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
            if (($tblStudentMemberList = DivisionCourse::useService()->getDivisionCourseMemberBy($tblDivisionCourse,
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

                        $birthday = $tblPerson->getBirthday();

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
            if (($tblDivisionTeacherMemberList = DivisionCourse::useService()->getDivisionCourseMemberBy($tblDivisionCourse,
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
            if (($tblRepresentativeMemberList = DivisionCourse::useService()->getDivisionCourseMemberBy($tblDivisionCourse,
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
            if (($tblCustodyMemberList = DivisionCourse::useService()->getDivisionCourseMemberBy($tblDivisionCourse,
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
                'Address'  => 'Adresse',
                'Birthday' => 'Geburtsdatum',
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

            $stage->setContent(
                DivisionCourse::useService()->getDivisionCourseHeader($tblDivisionCourse)
                . new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            empty($studentList)
                                ? new Warning('Keine Schüler dem Kurs zugewiesen')
                                : new TableData($studentList, null, $studentColumnList, false)
                        ))
                    ), new \SPHERE\Common\Frontend\Layout\Repository\Title(new PersonGroup() . ' Schüler in der ' . $text)),
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            empty($divisionTeacherList)
                                ? new Warning('Keine ' . $tblDivisionCourse->getDivisionTeacherName() . ' dem Kurs zugewiesen')
                                : new TableData($divisionTeacherList, null, $memberColumnList, false) // todo interactive
                        ))
                    ), new \SPHERE\Common\Frontend\Layout\Repository\Title(new Person() . ' ' . $tblDivisionCourse->getDivisionTeacherName() . ' in der ' . $text
                        . new Link('Bearbeiten', '/Education/Lesson/DivisionCourse/DivisionTeacher', new Pen(), array('DivisionCourseId' => $tblDivisionCourse->getId())))),
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            empty($representativeList)
                                ? new Warning('Keine Schülersprecher dem Kurs zugewiesen')
                                : new TableData($representativeList, null, $memberColumnList, false)
                        ))
                    ), new \SPHERE\Common\Frontend\Layout\Repository\Title(new PersonGroup() . ' Schülersprecher in der ' . $text
                        . new Link('Bearbeiten', '/Education/Lesson/DivisionCourse/Representative', new Pen(), array('DivisionCourseId' => $tblDivisionCourse->getId())))),
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            empty($custodyList)
                                ? new Warning('Keine Elternvertreter dem Kurs zugewiesen')
                                : new TableData($custodyList, null, $memberColumnList, false)
                        ))
                    ), new \SPHERE\Common\Frontend\Layout\Repository\Title(new PersonParent() . ' Elternvertreter in der ' . $text)),
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
    public function frontendDivisionCourseDivisionTeacher($DivisionCourseId = null): Stage
    {
        $stage = new Stage('Klassenlehrer', '');
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
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
            if (($tblMemberList =  DivisionCourse::useService()->getDivisionCourseMemberBy($tblDivisionCourse,
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
            if (($tblMemberList = DivisionCourse::useService()->getDivisionCourseMemberBy($tblDivisionCourse,
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
            if (($tblPersonList =DivisionCourse::useService()->getDivisionCourseMemberBy($tblDivisionCourse, TblDivisionCourseMemberType::TYPE_STUDENT))) {
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
}