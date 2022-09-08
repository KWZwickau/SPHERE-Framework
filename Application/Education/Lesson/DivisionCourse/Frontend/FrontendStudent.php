<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Frontend;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourseStudent;
use SPHERE\Application\Education\Lesson\Course\Course;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Transfer;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Stage;

class FrontendStudent extends FrontendMember
{
    /**
     * @param null $DivisionCourseId
     * @param null $Filter
     *
     * @return Stage
     */
    public function frontendDivisionCourseStudent($DivisionCourseId = null, $Filter = null): Stage
    {
        $stage = new Stage('Schüler', '');
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse/Show', new ChevronLeft(),
                array('DivisionCourseId' => $tblDivisionCourse->getId(), 'Filter' => $Filter))));
            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());
            $stage->setDescription('der ' . $text . ' Schuljahr ' . new Bold($tblDivisionCourse->getYearName()));
            if ($tblDivisionCourse->getDescription()) {
                $stage->setMessage($tblDivisionCourse->getDescription());
            }

            $stage->setContent(
                ApiDivisionCourseStudent::receiverModal()
                . DivisionCourse::useService()->getDivisionCourseHeader($tblDivisionCourse)
                . new Layout(new LayoutGroup(array(new LayoutRow(array(
                    new LayoutColumn(
                        new Title('Ausgewählte', 'Schüler')
                        . ApiDivisionCourseStudent::receiverBlock($this->loadRemoveStudentContent($DivisionCourseId), 'RemoveStudentContent')
                        , 6),
                    new LayoutColumn(
                        new Title('Verfügbare', 'Schüler')
                        . ApiDivisionCourseStudent::receiverBlock($this->loadAddStudentContent($DivisionCourseId, 'StudentSearch', null), 'AddStudentContent')
                        , 6)
                )))))
            );
        } else {
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse', new ChevronLeft())));
            $stage->setContent(new Warning('Kurs nicht gefunden', new Exclamation()));
        }

        return $stage;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param bool $setPost
     *
     * @return Form
     */
    public function formChangeDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson, TblYear $tblYear, bool $setPost = false): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
        if ($setPost && $tblStudentEducation) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Company'] = ($tblCompany = $tblStudentEducation->getServiceTblCompany()) ? $tblCompany->getId() : 0;
            $Global->POST['Data']['SchoolType'] = ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType()) ? $tblSchoolType->getId() : 0;
            $Global->POST['Data']['Level'] = $tblStudentEducation->getLevel();
            $Global->POST['Data']['Course'] = ($tblCourse = $tblStudentEducation->getServiceTblCourse()) ? $tblCourse->getId() : 0;
            $Global->POST['Data']['Division'] = ($tblDivision = $tblStudentEducation->getTblDivision()) ? $tblDivision->getId() : 0;
            $Global->POST['Data']['CoreGroup'] = ($tblCoreGroup = $tblStudentEducation->getTblCoreGroup()) ? $tblCoreGroup->getId() : 0;

            $Global->savePost();
        }

        $tblSchoolTypeAll = Type::useService()->getTypeAll();
        $tblCourseAll = Course::useService()->getCourseAll();
        $tblDivisionCourseDivisionList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_DIVISION);
        $tblDivisionCourseCoreGroupList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_CORE_GROUP);

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new NumberField('Data[Level]', '', 'Klassenstufe'))->setRequired()
                        , 6),
                    new FormColumn(
                        (new SelectBox('Data[SchoolType]', 'Schulart', array('{{ Name }} {{ Description }}' => $tblSchoolTypeAll), new Education()))->setRequired()
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        (new SelectBox('Data[Company]', 'Schule', array(
                            '{{ Name }} {{ ExtendedName }} {{ Description }}' => DivisionCourse::useService()->getSchoolListForStudentEducation()
                        )))->setRequired()
                        , 6),
                    new FormColumn(
                        (new SelectBox('Data[Course]', 'Bildungsgang', array('{{ Name }}' => $tblCourseAll)))
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        (new SelectBox('Data[Division]', 'Klasse', array('{{ Name }} {{ Description }}' => $tblDivisionCourseDivisionList)))
                        , 6),
                    new FormColumn(
                        (new SelectBox('Data[CoreGroup]', 'Stammgruppe', array('{{ Name }} {{ Description }}' => $tblDivisionCourseCoreGroupList)))
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        (new Primary('Speichern', ApiDivisionCourseStudent::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineChangeDivisionCourseSave($tblDivisionCourse->getId(), $tblPerson->getId()))
                    , 6),
                    new FormColumn(
                        (new Primary('Kein Wechsel im Schuljahr, Schüler entfernen', ApiDivisionCourseStudent::getEndpoint(), new MinusSign(), array(),  'Schüler entfernen'))
                            ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineRemoveStudent($tblDivisionCourse->getId(), $tblPerson->getId()))
                    , 6)
                )),
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadRemoveStudentContent($DivisionCourseId): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $selectedList = array();
            if (($tblMemberList =  DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, TblDivisionCourseMemberType::TYPE_STUDENT, true, false))) {
                $count = 0;
                foreach ($tblMemberList as $tblMember) {
                    if (($tblPerson = $tblMember->getServiceTblPerson())) {
                        $isInActive = $tblMember->isInActive();
                        $name = $tblPerson->getLastFirstName();
                        $address = ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt');

                        $option = (new Standard('', ApiDivisionCourseStudent::getEndpoint(), new MinusSign(), array(),  'Schüler entfernen'))
                            ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineRemoveStudent($tblDivisionCourse->getId(), $tblPerson->getId()));
                        // ist der Kurs eine Klasse oder Stammgruppe und im aktuellen Schuljahr und Schuljahr noch nicht älter als 1 Monat → Modal für Schülerwechsel öffnen
                        if (!$isInActive
                            && ($tblDivisionCourseType = $tblDivisionCourse->getType())
                            && ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION
                                || $tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)
                            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
                        ) {
                            $today = new DateTime('today');
                            /** @var DateTime $startDate */
                            list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
                            if ($startDate && $endDate
                                && $today > $startDate
                                && $today < $endDate
                                && ($firstMonthDate = clone $startDate)
                                && $today > ($firstMonthDate->add(new DateInterval('P1M')))
                            ) {
                                $option = (new Standard('', ApiDivisionCourseStudent::getEndpoint(), new Transfer(), array(), $tblDivisionCourse->getTypeName() . 'nwechsel'))
                                    ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineOpenChangeDivisionCourseModal($tblDivisionCourse->getId(), $tblPerson->getId()));
                            }
                        }

                        $selectedList[$tblPerson->getId()] = array(
                            'Number' => ++$count,
                            'Name' => $isInActive ? new Strikethrough($name) : $name,
                            'Address' => $isInActive ? new Strikethrough($address) : $address,
                            'Option' => $option
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
                $left = new Info('Keine Schüler ausgewählt');
            }

            return $left;
        }

        return new Danger('Kurs nicht gefunden!', new Exclamation());
    }

    /**
     * @param $DivisionCourseId
     * @param $AddStudentVariante
     * @param $SelectDivisionCourseId
     *
     * @return string
     */
    public function loadAddStudentContent($DivisionCourseId, $AddStudentVariante, $SelectDivisionCourseId): string
    {
        $buttons = (new Standard($AddStudentVariante == 'StudentSearch' ? new Bold('Schülersuche') : 'Schülersuche', ApiDivisionCourseStudent::getEndpoint(), new Search()))
                ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineLoadAddStudentContent($DivisionCourseId, 'StudentSearch'))
            . (new Standard($AddStudentVariante == 'CourseSelect' ? new Bold('Kurs-Schüler') : 'Kurs-Schüler', ApiDivisionCourseStudent::getEndpoint(), new Select()))
                ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineLoadAddStudentContent($DivisionCourseId, 'CourseSelect'))
            . (new Standard($AddStudentVariante == 'ProspectSearch' ? new Bold('Interessentensuche') : 'Interessentensuche', ApiDivisionCourseStudent::getEndpoint(), new Search()))
                ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineLoadAddStudentContent($DivisionCourseId, 'ProspectSearch'))
            . new Container('&nbsp;');

        switch ($AddStudentVariante) {
            case 'StudentSearch':
                return $buttons . new Panel(
                        'Schüler',
                        new Form(new FormGroup(new FormRow(new FormColumn(array(
                            (new TextField(
                                'Data[Search]',
                                '',
                                'Suche',
                                new Search()
                            ))->ajaxPipelineOnKeyUp(ApiDivisionCourseStudent::pipelineSearchPerson($DivisionCourseId))
                        )))))
                        . ApiDivisionCourseStudent::receiverBlock($this->loadPersonSearch($DivisionCourseId, ''), 'SearchPerson')
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

                if ($SelectDivisionCourseId) {
                    $global = $this->getGlobal();
                    $global->POST['Data']['DivisionCourseId'] = $SelectDivisionCourseId;
                    $global->savePost();
                }

                return $buttons . new Panel(
                        'Kursauswahl',
                        new Form(new FormGroup(new FormRow(new FormColumn(array(
                            (new SelectBox(
                                'Data[DivisionCourseId]',
                                'Kurs',
                                array('{{ Name }} {{ Description }} ' => $tblCourseList),
                                new Select()
                            ))->ajaxPipelineOnChange(ApiDivisionCourseStudent::pipelineSelectDivisionCourse($DivisionCourseId))
                        )))))
                        . ApiDivisionCourseStudent::receiverBlock('', 'SearchPerson')
                        , Panel::PANEL_TYPE_INFO
                    );
            case 'ProspectSearch':
                return $buttons . new Panel(
                        'Interessenten',
                        new Form(new FormGroup(new FormRow(new FormColumn(array(
                            (new TextField(
                                'Data[Search]',
                                '',
                                'Suche',
                                new Search()
                            ))->ajaxPipelineOnKeyUp(ApiDivisionCourseStudent::pipelineSearchProspect($DivisionCourseId))
                        )))))
                        . ApiDivisionCourseStudent::receiverBlock($this->loadProspectSearch($DivisionCourseId, ''), 'SearchPerson')
                        , Panel::PANEL_TYPE_INFO
                    );
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
                && ($tblPersonList = Person::useService()->getPersonListLike($Search))
            ) {
                $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
                foreach ($tblPersonList as $tblPerson) {
                    // nur nach Schülern suchen
                    if (Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                        if (($option = $this->getStudentAddOptionByPerson($tblPerson, $tblDivisionCourse, $tblYear, 'StudentSearch'))) {
                            $resultList[] = array(
                                'Name' => $tblPerson->getLastFirstName(),
                                'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                                'Option' => $option
                            );
                        }
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
     * @param TblPerson $tblPerson
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblYear $tblYear
     * @param $AddStudentVariante
     * @param null $SelectedDivisionCourseId
     *
     * @return false|string
     */
    private function getStudentAddOptionByPerson(TblPerson $tblPerson, TblDivisionCourse $tblDivisionCourse, TblYear $tblYear, $AddStudentVariante,
        $SelectedDivisionCourseId = null)
    {
        if (($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_DIVISION)
            && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblDivisionCourseDivision = $tblStudentEducation->getTblDivision())
        ) {
            // Schüler ist bereits im Kurs
            if ($tblDivisionCourseDivision->getId() == $tblDivisionCourse->getId()) {
                return false;
            }
            $option = new WarningText($tblDivisionCourseDivision->getName());
        } elseif (($tblDivisionCourse->getType()->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)
            && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblDivisionCourseCoreGroup = $tblStudentEducation->getTblCoreGroup())
        ) {
            // Schüler ist bereits im Kurs
            if ($tblDivisionCourseCoreGroup->getId() == $tblDivisionCourse->getId()) {
                return false;
            }
            $option = new WarningText($tblDivisionCourseCoreGroup->getName());
        } else {
            // Schüler ist bereits im Kurs
            if (DivisionCourse::useService()->getDivisionCourseMemberByPerson(
                $tblDivisionCourse,
                DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT),
                $tblPerson
            )) {
                return false;
            }
            $option = (new Standard('', ApiDivisionCourseStudent::getEndpoint(), new PlusSign(), array(),  'Schüler hinzufügen'))
                ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineAddStudent($tblDivisionCourse->getId(), $tblPerson->getId(), $AddStudentVariante,
                    $SelectedDivisionCourseId));
        }

        return $option;
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
                    if (($option = $this->getStudentAddOptionByPerson($tblPerson, $tblDivisionCourse, $tblYear, 'CourseSelect', $SelectedDivisionCourseId))) {
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
                        'pageLength' => 10,
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
     * @param $DivisionCourseId
     * @param $Search
     *
     * @return string
     */
    public function loadProspectSearch($DivisionCourseId, $Search): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden', new Exclamation());
        }

        if ($Search != '' && strlen($Search) > 2) {
            $resultList = array();
            $result = '';
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())
                && ($tblPersonList = Person::useService()->getPersonListLike($Search))
            ) {
                $tblGroup = Group::useService()->getGroupByMetaTable('PROSPECT');
                foreach ($tblPersonList as $tblPerson) {
                    // nur nach Schülern suchen
                    if (Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                        if (($option = $this->getStudentAddOptionByPerson($tblPerson, $tblDivisionCourse, $tblYear, 'ProspectSearch'))) {
                            if (($tblProspect = Prospect::useService()->getProspectByPerson($tblPerson))
                                && ($tblProspectReservation = $tblProspect->getTblProspectReservation())
                            ) {
                                $yearString = $tblProspectReservation->getReservationYear();
                                $level = $tblProspectReservation->getReservationDivision();
                            } else {
                                $yearString = '';
                                $level =  '';
                            }
                            $resultList[] = array(
                                'Name' => $tblPerson->getLastFirstName(),
                                'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                                'Year' => $yearString,
                                'Level' => $level,
                                'Option' => $option
                            );
                        }
                    }
                }

                $result = new TableData(
                    $resultList,
                    null,
                    array(
                        'Name' => 'Name',
                        'Address' => 'Adresse',
                        'Year' => 'Schuljahr',
                        'Level' => 'Klassenstufe',
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
                $result = new Warning('Es wurden keine entsprechenden Interessenten gefunden.', new Ban());
            }
        } else {
            $result =  new Warning('Bitte geben Sie mindestens 3 Zeichen in die Suche ein.', new Exclamation());
        }

        return $result;
    }
}