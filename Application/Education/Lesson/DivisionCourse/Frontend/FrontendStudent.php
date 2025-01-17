<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Frontend;

use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourseStudent;
use SPHERE\Application\Api\Education\DivisionCourse\MassReplaceStudentEducation;
use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Api\People\Person\ApiPersonReadOnly;
use SPHERE\Application\Education\Lesson\Course\Course;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Person\Frontend\FrontendStudentProcess;
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
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Transfer;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
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
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Frontend\Table\Structure\TableBody;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
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
     * @param TblPerson $tblPerson
     * @param TblDivisionCourse|null $tblDivisionCourse
     * @param TblStudentEducation|null $tblStudentEducation
     * @param bool $setPost
     *
     * @return Form
     */
    public function formEditStudentEducation(TblPerson $tblPerson, ?TblDivisionCourse $tblDivisionCourse,
        ?TblStudentEducation $tblStudentEducation, bool $setPost = false): Form
    {
        if ($tblDivisionCourse && ($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
        }
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        if ($setPost && $tblStudentEducation) {
            $Global = $this->getGlobal();
            $Global->POST['StudentEducationData']['Year'] = ($tblYear = $tblStudentEducation->getServiceTblYear()) ? $tblYear->getId() : 0;
            $Global->POST['StudentEducationData']['Company'] = ($tblCompany = $tblStudentEducation->getServiceTblCompany()) ? $tblCompany->getId() : 0;
            $Global->POST['StudentEducationData']['SchoolType'] = ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType()) ? $tblSchoolType->getId() : 0;
            $Global->POST['StudentEducationData']['Level'] = $tblStudentEducation->getLevel();
            $Global->POST['StudentEducationData']['Course'] = ($tblCourse = $tblStudentEducation->getServiceTblCourse()) ? $tblCourse->getId() : 0;

            $Global->savePost();
        }

        $NodeProcess = 'Schülertransfer - Aktueller Schulverlauf';
        $tblSchoolTypeAll = Type::useService()->getTypeAll();
        $tblCourseAll = Course::useService()->getCourseAll();

        if ($tblDivisionCourse) {
            $formRows[] = new FormRow(array(
                new FormColumn(
                    new Panel('Schüler', $tblPerson->getLastFirstName(), Panel::PANEL_TYPE_INFO)
                    , 6),
                new FormColumn(
                    new Panel('Schuljahr', $tblDivisionCourse->getYearName(), Panel::PANEL_TYPE_INFO)
                    , 6)
            ));
        }
        $formRows[] = new FormRow(array(
            new FormColumn(array(
                ApiMassReplace::receiverField((
                    $Field = (new SelectBox('StudentEducationData[SchoolType]', 'Schulart', array('{{ Name }} {{ Description }}' => $tblSchoolTypeAll), new Education()))
                        ->setRequired()
                        ->configureLibrary(SelectBox::LIBRARY_SELECT2)
                )),
                ApiMassReplace::receiverModal($Field, 'Schülertransfer - Aktueller Schulverlauf'),
                new PullRight((new Link('Massen-Änderung',
                    ApiMassReplace::getEndpoint(), null, array(
                        ApiMassReplace::SERVICE_CLASS       => MassReplaceStudentEducation::CLASS_MASS_REPLACE_STUDENT_EDUCATION,
                        ApiMassReplace::SERVICE_METHOD      => MassReplaceStudentEducation::METHOD_REPLACE_SCHOOL_TYPE,
                        'Id'                                => $tblPerson->getId(),
                        'StudentEducationId'                => $tblStudentEducation->getId(),
                    )))->ajaxPipelineOnClick(ApiMassReplace::pipelineOpen($Field, $NodeProcess)
                )),
            ), 6),
            new FormColumn(array(
                ApiMassReplace::receiverField((
                $Field = (new SelectBox('StudentEducationData[Company]', 'Schule', array('{{ Name }} {{ ExtendedName }} {{ Description }}'
                => DivisionCourse::useService()->getSchoolListForStudentEducation())))
                    ->setRequired()
                    ->configureLibrary(SelectBox::LIBRARY_SELECT2)
                )),
                ApiMassReplace::receiverModal($Field, 'Schülertransfer - Aktueller Schulverlauf'),
                new PullRight((new Link('Massen-Änderung',
                    ApiMassReplace::getEndpoint(), null, array(
                        ApiMassReplace::SERVICE_CLASS       => MassReplaceStudentEducation::CLASS_MASS_REPLACE_STUDENT_EDUCATION,
                        ApiMassReplace::SERVICE_METHOD      => MassReplaceStudentEducation::METHOD_REPLACE_COMPANY,
                        'Id'                                => $tblPerson->getId(),
                        'StudentEducationId'                => $tblStudentEducation->getId(),
                    )))->ajaxPipelineOnClick(ApiMassReplace::pipelineOpen($Field, $NodeProcess)
                )),
            ), 6),
        ));
        $formRows[] =  new FormRow(array(
            new FormColumn(array(
                ApiMassReplace::receiverField(($Field = (new TextField('StudentEducationData[Level]', '', 'Klassenstufe'))->setRequired())),
                ApiMassReplace::receiverModal($Field, 'Schülertransfer - Aktueller Schulverlauf'),
                new PullRight((new Link('Massen-Änderung',
                    ApiMassReplace::getEndpoint(), null, array(
                        ApiMassReplace::SERVICE_CLASS       => MassReplaceStudentEducation::CLASS_MASS_REPLACE_STUDENT_EDUCATION,
                        ApiMassReplace::SERVICE_METHOD      => MassReplaceStudentEducation::METHOD_REPLACE_LEVEL,
                        'Id'                                => $tblPerson->getId(),
                        'StudentEducationId'                => $tblStudentEducation->getId(),
                    )))->ajaxPipelineOnClick(ApiMassReplace::pipelineOpen($Field, $NodeProcess)
                )),
            ), 6),
            new FormColumn(array(
                ApiMassReplace::receiverField((
                    $Field = (new SelectBox('StudentEducationData[Course]', 'Bildungsgang', array('{{ Name }}' => $tblCourseAll)))
                        ->configureLibrary(SelectBox::LIBRARY_SELECT2)
                )),
                ApiMassReplace::receiverModal($Field, 'Schülertransfer - Aktueller Schulverlauf'),
                new PullRight((new Link('Massen-Änderung',
                    ApiMassReplace::getEndpoint(), null, array(
                        ApiMassReplace::SERVICE_CLASS       => MassReplaceStudentEducation::CLASS_MASS_REPLACE_STUDENT_EDUCATION,
                        ApiMassReplace::SERVICE_METHOD      => MassReplaceStudentEducation::METHOD_REPLACE_COURSE,
                        'Id'                                => $tblPerson->getId(),
                        'StudentEducationId'                => $tblStudentEducation->getId(),
                    )))->ajaxPipelineOnClick(ApiMassReplace::pipelineOpen($Field, $NodeProcess)
                )),
            ), 6),
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(array(
                (new Primary('Speichern', ApiDivisionCourseStudent::getEndpoint(), new Save()))
                    ->ajaxPipelineOnClick($tblDivisionCourse
                        ? ApiDivisionCourseStudent::pipelineEditStudentEducationSave($tblPerson->getId(), $tblDivisionCourse->getId(), $tblStudentEducation ? $tblStudentEducation->getId() : null)
                        : ApiPersonEdit::pipelineEditStudentProcessSave($tblPerson->getId(), $tblStudentEducation ? $tblStudentEducation->getId() : null)
                    ),
                (new Primary('Abbrechen', ApiDivisionCourseStudent::getEndpoint(), new Disable()))
                    ->ajaxPipelineOnClick($tblDivisionCourse
                        ? ApiDivisionCourseStudent::pipelineLoadDivisionCourseStudentContent($tblDivisionCourse->getId())
                        : ApiPersonReadOnly::pipelineLoadStudentProcessContent($tblPerson->getId())
                    )
            )),
        ));

        return (new Form(new FormGroup($formRows)))->disableSubmitAction();
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return Form
     */
    public function formCreateStudentEducation(TblPerson $tblPerson): Form
    {
        // es sollen nur Schuljahre zur Auswahl stehen, wo noch keine SchülerBildung angelegt wurde
        $yearSelectList = array();
        if (($tblYearList = Term::useService()->getYearAll())) {
            foreach ($tblYearList as $tblYear) {
                if (!DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear)) {
                    $yearSelectList[$tblYear->getId()] = $tblYear;
                }
            }
        }

        $tblSchoolTypeAll = Type::useService()->getTypeAll();
        $tblCourseAll = Course::useService()->getCourseAll();

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new SelectBox('Data[Year]', 'Schuljahr', array('{{ Name }} {{ Description }}' => $yearSelectList)))->setRequired()
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        (new SelectBox('Data[SchoolType]', 'Schulart', array('{{ Name }} {{ Description }}' => $tblSchoolTypeAll), new Education()))->setRequired()
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        (new SelectBox('Data[Company]', 'Schule', array(
                            '{{ Name }} {{ ExtendedName }} {{ Description }}' => DivisionCourse::useService()->getSchoolListForStudentEducation(false)
                        )))->setRequired()
                    )
                )),
                new FormRow(array(
                    new FormColumn(
                        (new NumberField('Data[Level]', '', 'Klassenstufe'))->setRequired()
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        (new SelectBox('Data[Course]', 'Bildungsgang', array('{{ Name }}' => $tblCourseAll)))
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        (new Primary('Speichern', ApiDivisionCourseStudent::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineCreateStudentEducationSave($tblPerson->getId()))
                    ),
                )),
            ))
        ))->disableSubmitAction();
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
//                    new FormColumn(
//                        (new Primary('Kein Wechsel im Schuljahr, Schüler entfernen', ApiDivisionCourseStudent::getEndpoint(), new MinusSign(), array(),  'Schüler entfernen'))
//                            ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineRemoveStudent($tblDivisionCourse->getId(), $tblPerson->getId()))
//                    , 6)
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
                        if ($tblDivisionCourse->getIsDivisionOrCoreGroup() && !$isInActive) {
                            $option .= (new Standard('', ApiDivisionCourseStudent::getEndpoint(), new Transfer(), array(), $tblDivisionCourse->getTypeName()
                                . 'nwechsel im Schuljahr / Schüler deaktivieren'))
                                    ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineOpenChangeDivisionCourseModal($tblDivisionCourse->getId(), $tblPerson->getId()));
                        }

//                        // ist der Kurs eine Klasse oder Stammgruppe und im aktuellen Schuljahr und Schuljahr noch nicht älter als 1 Monat → Modal für Schülerwechsel öffnen
//                        if (!$isInActive
//                            && ($tblDivisionCourseType = $tblDivisionCourse->getType())
//                            && ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION
//                                || $tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)
//                            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
//                        ) {
//                            $today = new DateTime('today');
//                            /** @var DateTime $startDate */
//                            list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
//                            if ($startDate && $endDate
//                                && $today > $startDate
//                                && $today < $endDate
//                                && ($firstMonthDate = clone $startDate)
//                                && $today > ($firstMonthDate->add(new DateInterval('P1M')))
//                            ) {
//                                $option = (new Standard('', ApiDivisionCourseStudent::getEndpoint(), new Transfer(), array(), $tblDivisionCourse->getTypeName() . 'nwechsel'))
//                                    ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineOpenChangeDivisionCourseModal($tblDivisionCourse->getId(), $tblPerson->getId()));
//                            }
//                        }

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
                        array('orderable' => false, 'width' => $tblDivisionCourse->getIsDivisionOrCoreGroup() ? '60px' : '1%', 'targets' => -1),
                    ),
                    'paging' => false,
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
        $tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId);

        $buttons = (new Standard($AddStudentVariante == 'StudentSearch' ? new Bold('Schülersuche') : 'Schülersuche', ApiDivisionCourseStudent::getEndpoint(), new Search()))
                ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineLoadAddStudentContent($DivisionCourseId, 'StudentSearch'))
            . (new Standard($AddStudentVariante == 'CourseSelect' ? new Bold('Kurs-Schüler') : 'Kurs-Schüler', ApiDivisionCourseStudent::getEndpoint(), new Select()))
                ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineLoadAddStudentContent($DivisionCourseId, 'CourseSelect'));
        if ($tblDivisionCourse && $tblDivisionCourse->getIsDivisionOrCoreGroup()) {
            $buttons .= (new Standard(
                $AddStudentVariante == 'StudentWithout' ? new Bold('Schüler ohne ' . $tblDivisionCourse->getTypeName()) : 'Schüler ohne ' . $tblDivisionCourse->getTypeName(),
                ApiDivisionCourseStudent::getEndpoint(),
                new Select())
            )
                ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineLoadAddStudentContent($DivisionCourseId, 'StudentWithout'));
        }
        $buttons .= (new Standard($AddStudentVariante == 'ProspectSearch' ? new Bold('Interessenten') : 'Interessenten', ApiDivisionCourseStudent::getEndpoint(), new Select()))
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
                if ($tblDivisionCourse
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

            case 'StudentWithout':
                $resultList = array();
                if ($tblDivisionCourse
                    && ($tblYear = $tblDivisionCourse->getServiceTblYear())
                    && ($tblStudentGroup = Group::useService()->getGroupByMetaTable('STUDENT'))
                    && ($tblGroupPersonList = Group::useService()->getPersonAllByGroup($tblStudentGroup))
                ) {
                    $isCoreGroup = $tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP;
                    foreach ($tblGroupPersonList as $tblPerson) {
                        $isAdd = false;
                        if ($isCoreGroup) {
                            if (!(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                                && $tblStudentEducation->getTblCoreGroup())
                            ) {
                                $isAdd = true;
                            }
                        } else {
                            if (!(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                                && $tblStudentEducation->getTblDivision())
                            ) {
                                $isAdd = true;
                            }
                        }

                        if ($isAdd) {
                            $resultList[$tblPerson->getId()] = array(
                                'Name' => $tblPerson->getLastFirstName(),
                                'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                                'Option' => (new Standard('', ApiDivisionCourseStudent::getEndpoint(), new PlusSign(), array(), 'Schüler hinzufügen'))
                                    ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineAddStudent($tblDivisionCourse->getId(), $tblPerson->getId(), $AddStudentVariante))
                            );
                        }
                    }
                }

                if (empty($resultList)) {
                    $result = new Warning('Es wurden keine entsprechenden Schüler gefunden.', new Ban());
                } else {
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
                            'paging' => false,
                            'responsive' => false
                        )
                    );
                }

                return $buttons . new Panel(
                        'Schüler ohne ' . ($tblDivisionCourse ? $tblDivisionCourse->getTypeName() : 'Kurs'),
                        ApiDivisionCourseStudent::receiverBlock($result, 'SearchPerson'),
                        Panel::PANEL_TYPE_INFO
                    );

            case 'ProspectSearch':
                $resultList = array();
                if ($tblDivisionCourse
                    && ($tblYear = $tblDivisionCourse->getServiceTblYear())
                    && ($tblProspectGroup = Group::useService()->getGroupByMetaTable('PROSPECT'))
                    && ($tblGroupPersonList = Group::useService()->getPersonAllByGroup($tblProspectGroup))
                ) {
                    foreach ($tblGroupPersonList as $tblPerson) {
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
                        'paging' => false,
                        'responsive' => false
                    )
                );

                return $buttons . new Panel(
                        'Interessenten',
                        ApiDivisionCourseStudent::receiverBlock($result, 'SearchPerson'),
                        Panel::PANEL_TYPE_INFO
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
            $Search = str_replace(',', '', $Search);
            $Search = str_replace('.', '', $Search);
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
                        'paging' => false,
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
                    // nur nach Interessenten suchen
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

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadDivisionCourseStudentContent($DivisionCourseId): string
    {
        $isCourseSystem = false;
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $tblDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
            DivisionCourse::useService()->getSubDivisionCourseRecursiveListByDivisionCourse($tblDivisionCourse, $tblDivisionCourseList);
            $hasSubDivisionCourse = count($tblDivisionCourseList) > 1;

            $studentList = array();
            $courseList = array();
            foreach ($tblDivisionCourseList as $tblDivisionCourseItem) {
                // SekII-Kurs
                if ($tblDivisionCourseItem->getType()->getIsCourseSystem()) {
                    $isCourseSystem = true;
                    $count = 0;
                    if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListBySubjectDivisionCourse($tblDivisionCourseItem))) {
                        foreach ($tblStudentSubjectList as $tblStudentSubject) {
                            $item = array();
                            if (($tblDivisionCourseSekII = $tblStudentSubject->getTblDivisionCourse())) {
                                if (($list = explode('/', $tblStudentSubject->getPeriodIdentifier()))
                                    && isset($list[1])
                                    && ($period = $list[1])
                                    && ($tblPerson = $tblStudentSubject->getServiceTblPerson())
                                ) {
                                    if (!isset($courseList[$tblPerson->getId()])) {
                                        $item['Number'] = ++$count;
                                        $item['FullName'] = $tblPerson->getLastFirstName();
                                        $courseList[$tblPerson->getId()] = $item;
                                    }
                                    $courseList[$tblPerson->getId()]['Period' . $period] = $tblDivisionCourseSekII->getServiceTblSubject() ? $tblDivisionCourseSekII->getServiceTblSubject()->getAcronym() : '';
                                }
                            }
                        }
                    }
                } elseif ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_TEACHER_GROUP) {
                    if (($tblStudentMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourseItem, TblDivisionCourseMemberType::TYPE_STUDENT))) {
                        $count = 0;
                        foreach ($tblStudentMemberList as $tblPerson) {
                            $fullName = $tblPerson->getLastFirstName();
                            $division = '';
                            $coreGroup = '';
                            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                                $level = $tblStudentEducation->getLevel() ?: new WarningText('Keine Klassenstufe hinterlegt');
                                $schoolType = ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                                    ? $tblSchoolType->getName() : new WarningText('Keine Schulart hinterlegt');

                                $warningCourse = '';
                                if ($tblSchoolType && $tblSchoolType->getShortName() == 'OS' && (!($level instanceof WarningText) && intval($level) > 6)) {
                                    $warningCourse = new WarningText('Keine Bildungsgang hinterlegt');
                                }
                                $course = ($tblCourse = $tblStudentEducation->getServiceTblCourse()) ? $tblCourse->getName() : $warningCourse;
                                if (($tblDivision = $tblStudentEducation->getTblDivision())) {
                                    $division = $tblDivision->getName();
                                }
                                if (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())) {
                                    $coreGroup = $tblCoreGroup->getName();
                                }
                            } else {
                                $level = new WarningText('Keine Klassenstufe hinterlegt');
                                $schoolType = new WarningText('Keine Schulart hinterlegt');
                                $course = '';
                            }

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

                            $item['Number'] = ++$count;
                            $item['FullName'] = $fullName;
                            $item['Gender'] = $gender;
                            $item['Birthday'] = $birthday;
                            $item['SchoolType'] = $schoolType;
                            $item['Level'] = $level;
                            $item['Course'] = $course;
                            $item['Division'] = $division;
                            $item['CoreGroup'] = $coreGroup;
                            $item['Subject'] = $tblDivisionCourse->getSubjectName();

                            $studentList[] = $item;
                        }
                    }
                } else {
                    if (($tblStudentMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourseItem,
                        TblDivisionCourseMemberType::TYPE_STUDENT, true, false))
                    ) {
                        $count = 0;
                        foreach ($tblStudentMemberList as $tblStudentMember) {
                            if (($tblPerson = $tblStudentMember->getServiceTblPerson())) {
                                $isInActive = $tblStudentMember->isInActive();
                                $fullName = $tblPerson->getLastFirstName();

//                              $address = ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt');

                                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                                    $company = ($tblCompany = $tblStudentEducation->getServiceTblCompany())
                                        ? $tblCompany->getDisplayName() : new WarningText('Keine Schule hinterlegt');
                                    $level = $tblStudentEducation->getLevel() ?: new WarningText('Keine Klassenstufe hinterlegt');
                                    $schoolType = ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                                        ? $tblSchoolType->getName() : new WarningText('Keine Schulart hinterlegt');

                                    $warningCourse = '';
                                    if ($tblSchoolType && $tblSchoolType->getShortName() == 'OS' && (!($level instanceof WarningText) && intval($level) > 6)) {
                                        $warningCourse = new WarningText('Keine Bildungsgang hinterlegt');
                                    }
                                    $course = ($tblCourse = $tblStudentEducation->getServiceTblCourse()) ? $tblCourse->getName() : $warningCourse;
                                } else {
                                    $company = new WarningText('Keine Schule hinterlegt');
                                    $level = new WarningText('Keine Klassenstufe hinterlegt');
                                    $schoolType = new WarningText('Keine Schulart hinterlegt');
                                    $course = '';
                                }

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

                                $item['Number'] = $isInActive ? '' : ++$count;
                                $item['FullName'] = $isInActive ? new ToolTip(new Strikethrough($fullName),
                                    'Deaktivierung: ' . $tblStudentMember->getLeaveDate()) : $fullName;
                                if ($hasSubDivisionCourse) {
                                    $item['DivisionCourse'] = $isInActive ? new Strikethrough($tblDivisionCourseItem->getName()) : $tblDivisionCourseItem->getName();;
                                }
                                $item['Gender'] = $isInActive ? new Strikethrough($gender) : $gender;
                                $item['Birthday'] = $isInActive ? new Strikethrough($birthday) : $birthday;
//                            $item['Address'] = $isInActive ? new Strikethrough($address) : $address;
                                $item['SchoolType'] = $isInActive ? new Strikethrough($schoolType) : $schoolType;
                                $item['Company'] = $isInActive ? new Strikethrough($company) : $company;
                                $item['Level'] = $isInActive ? new Strikethrough($level) : $level;
                                $item['Course'] = $isInActive ? new Strikethrough($course) : $course;

                                if ($isInActive) {
                                    $item['Option'] = '';
                                } elseif ($tblStudentEducation) {
                                    $item['Option'] = (new Link('Bearbeiten', ApiDivisionCourseStudent::getEndpoint(), new Pen()))
                                        ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineEditDivisionCourseStudentContent(
                                            $tblStudentEducation->getId(), $tblPerson->getId(), $DivisionCourseId
                                        ));
                                } else {
                                    $item['Option'] = '';
//                                    $item['Option'] = (new Link('Bearbeiten', ApiDivisionCourseStudent::getEndpoint(), new Pen()))
//                                        ->ajaxPipelineOnClick(ApiDivisionCourseStudent::pipelineOpenCreateStudentEducationModal(
//                                            $tblPerson->getId()
//                                        ));
                                }


                                $studentList[] = $item;
                            }
                        }
                    }
                }
            }

            $backgroundColor = '#E0F0FF';
            if ($isCourseSystem) {
                // leere Halbjahre bei Schülern
                if (!empty($courseList)) {
                    foreach ($courseList as $student) {
                        $studentList[] = array(
                            'Number' => $student['Number'],
                            'FullName' => $student['FullName'],
                            'Period1' => isset($student['Period1']) ? $student['Period1'] : '&nbsp;',
                            'Period2' => isset($student['Period2']) ? $student['Period2'] : '&nbsp;',
                        );
                    }
                }

                $headerStudentColumnList[] = $this->getTableHeaderColumn('#', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Schüler', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('1. Halbjahr', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('2. Halbjahr', $backgroundColor);
            } elseif ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_TEACHER_GROUP) {
                $headerStudentColumnList[] = $this->getTableHeaderColumn('#', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Schüler', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Ge&shy;schlecht', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Geburts&shy;datum', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Schul&shy;art', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Klassen&shy;stufe', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Bildungs&shy;gang', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Klasse', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Stammgruppe', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Fach', $backgroundColor);
            } else {
                $headerStudentColumnList[] = $this->getTableHeaderColumn('#', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Schüler', $backgroundColor);
                if ($hasSubDivisionCourse) {
                    $headerStudentColumnList[] = $this->getTableHeaderColumn('Kurs', $backgroundColor);
                }
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Ge&shy;schlecht', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Geburts&shy;datum', $backgroundColor);
//            $headerStudentColumnList[] = $this->getTableHeaderColumn('Adresse', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Schul&shy;art', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Schule', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Klassen&shy;stufe', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('Bildungs&shy;gang', $backgroundColor);
                $headerStudentColumnList[] = $this->getTableHeaderColumn('&nbsp; ', $backgroundColor, '95px');
            }

            return empty($studentList)
                ? new Warning('Keine Schüler dem Kurs zugewiesen')
                : $this->getTableCustom($headerStudentColumnList, $studentList);
        }

        return '';
    }

    /**
     * @param $StudentEducationId
     * @param $PersonId
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function editDivisionCourseStudentContent($StudentEducationId, $PersonId, $DivisionCourseId): string
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return (!$DivisionCourseId ? FrontendStudentProcess::getEditStudentProcessTitle($tblPerson) : '')
                . new Well($this->formEditStudentEducation(
                    $tblPerson,
                    (DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId)) ?: null,
                    (DivisionCourse::useService()->getStudentEducationById($StudentEducationId)) ?: null,
                    true
                ));
        }

        return '';
    }

    /**
     * @param string $name
     * @param string $backgroundColor
     * @param string $width
     * @param int $size
     *
     * @return TableColumn
     */
    public function getTableHeaderColumn(string $name, string $backgroundColor, string $width = 'auto', int $size = 1): TableColumn
    {
        return (new TableColumn($name, $size, $width))
            ->setBackgroundColor($backgroundColor)
            ->setPadding('5px')
            ->setVerticalAlign('middle');
    }

    /**
     * @param string $content
     *
     * @return TableColumn
     */
    public function getTableBodyColumn(string $content): TableColumn
    {
        return (new TableColumn($content))
            ->setPadding('5px')
            ->setVerticalAlign('middle');
    }

    /**
     * @param array $headerColumnList
     * @param array $bodyColumnList
     *
     * @return Table
     */
    public function getTableCustom(array $headerColumnList, array $bodyColumnList): Table
    {
        $tableHead = new TableHead(new TableRow($headerColumnList));
        $rows = array();
        foreach ($bodyColumnList as $columnList) {
            $columns = array();
            foreach ($columnList as $item) {
                $columns[] = $this->getTableBodyColumn($item);
            }
            $rows[] = new TableRow($columns);
        }
        $tableBody = new TableBody($rows);

        return new Table($tableHead, $tableBody, null, false, null, 'TableCustom');
    }
}