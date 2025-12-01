<?php

namespace SPHERE\Application\Education\Lesson\LeaveStudent;

use DateTime;
use SPHERE\Application\Api\Education\Lesson\ApiLeaveStudent;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Group\Group as CorporationGroup;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\ToggleCheckbox;
use SPHERE\Common\Frontend\Message\IMessageInterface;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /** @noinspection PhpUnused */
    public function frontendLeaveStudent(): Stage
    {
        $stage = new Stage('Schulabgänger');
        $stage->setMessage(
            new Container('Es werden die Schulabgänger automatisch ermittelt, wenn mindestens <b>eine</b> der folgenden Bedingungen erfüllt ist, 
                zusätzlich können Schüler manuell hinzugefügt werden: ')
            . new Container('&nbsp;&nbsp;&nbsp;&#8226; Schüler hat die maximale Klassenstufe seiner Schulart erreicht (Abschlussklassen)')
            . new Container('&nbsp;&nbsp;&nbsp;&#8226; Schüler besitzt ein in der Schulsoftware gedrucktes Abschluss- bzw. Abgangszeugnis')
            . new Container('&nbsp;&nbsp;&nbsp;&#8226; Schüler-Bildung wurde im Schuljahr deaktiviert')
            . new Container('&nbsp;&nbsp;&nbsp;&#8226; Für den Schüler wurde ein Abgangsdatum gesetzt')
            . new Container('Weiterhin werden bereits erfasste Schulabgänger nicht mehr berücksichtigt, 
                als erfasster Schulabgänger gilt man, wenn <b>alle</b> der folgenden Bedingungen erfüllt sind:')
            . new Container('&nbsp;&nbsp;&nbsp;&#8226; Person ist nicht mehr in der festen Personen-Gruppe: "Schüler"')
            . new Container('&nbsp;&nbsp;&nbsp;&#8226; Person ist bereits in der festen Personen-Gruppe: "Ehemalige (Archiv)"')
            . new Container('&nbsp;&nbsp;&nbsp;&#8226; Für die Person ist ein Abgangsdatum gesetzt')
            . new Container('&nbsp;')
            . new Container('Für <b>ausgewählte</b> Schulabgänger werden die folgenden Aktionen beim Speichern ausgeführt:')
            . new Container('&nbsp;&nbsp;&nbsp;1. Schulabgänger wird aus der festen Personen-Gruppe: Schüler entfernt')
            . new Container('&nbsp;&nbsp;&nbsp;2. Schulabgänger wird in die feste Personen-Gruppe: "Ehemalige (Archiv)" hinzugefügt')
            . new Container('&nbsp;&nbsp;&nbsp;3. Schulabgänger wird optional in eine ausgewählte individuelle Personen-Gruppe hinzugefügt')
            . new Container('&nbsp;&nbsp;&nbsp;4. Für Schulabgänger wird das ausgewählte Abgangsdatum gesetzt')
            . new Container('&nbsp;&nbsp;&nbsp;5. Für Schulabgänger wird optional die ausgewählte Aufnehmende Schule gesetzt')
        );

        $stage->setContent(
            new Panel(new Filter() . ' Filter', $this->formFilter(), Panel::PANEL_TYPE_INFO)
            . ApiLeaveStudent::receiverModal()
            . ApiLeaveStudent::receiverBlock($this->loadContent(), 'Content')
        );

        return $stage;
    }

    /**
     * @return Form
     */
    public function formFilter(): Form
    {
        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Data[SchoolType]', 'Schulart', array('{{ Name }}' => School::useService()->getConsumerSchoolTypeAll())))
                        ->ajaxPipelineOnChange(ApiLeaveStudent::pipelineLoadContent())
                        ->setRequired()
                    , 6),
                new FormColumn(
                    (new SelectBox('Data[Year]', 'Schuljahr', array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(1))))
                        ->ajaxPipelineOnChange(ApiLeaveStudent::pipelineLoadContent())
                        ->setRequired()
                    , 6),
            )),
        )));
    }

    /**
     * @param $Data
     *
     * @return string
     */
    public function loadContent($Data = null): string
    {
        $content = '';
        $tblSchoolType = false;
        $tblYear = false;
        if (!isset($Data['SchoolType']) || !($tblSchoolType = Type::useService()->getTypeById($Data['SchoolType']))) {
            $content .= new Warning('Bitte wählen Sie zunächst eine Schulart aus.', new Exclamation());
        }

        if (!isset($Data['Year']) || !($tblYear = Term::useService()->getYearById($Data['Year']))) {
            $content .= new Warning('Bitte wählen Sie zunächst ein Schuljahr aus.', new Exclamation());
        }

        if ($content) {
            return $content;
        }

        $dataList = [];
        if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYear, $tblSchoolType, null, null, null, false))) {
            list(, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            $tblSchoolTypeList = School::useService()->getConsumerSchoolTypeAll();
            $hasSecondarySchool = isset($tblSchoolTypeList['OS']) || isset($tblSchoolTypeList['Gy']);
            $tblGroupStudent = Group::useService()->getGroupByMetaTable('STUDENT');
            $tblGroupArchive = Group::useService()->getGroupByMetaTable('ARCHIVE');
            $tblGroupsCustom = Group::useService()->getGroupAllSorted(true);
            $tblCompanies = CorporationGroup::useService()->getCompanyAllByGroup(CorporationGroup::useService()->getGroupByMetaTable('SCHOOL'));

            $tblStudentEducationList = $this->getSorter($tblStudentEducationList)->sortObjectBy('Sort');

            // gespeicherte Daten laden
            $formData = [];
            if (($tblLeaveStudent = LeaveStudent::useService()->getLeaveStudentBy($tblSchoolType, $tblYear))) {
                $formData = $tblLeaveStudent->getData();
            }

            // automatische Schulabgänger ermitteln
            /** @var TblStudentEducation $tblStudentEducation */
            foreach ($tblStudentEducationList as $tblStudentEducation) {
                if (($tblPerson = $tblStudentEducation->getServiceTblPerson())) {
                    if (($personData = $this->getPersonData($tblSchoolType, $tblYear, $tblPerson, $tblStudentEducation, $formData[$tblPerson->getId()] ?? [],
                        false, $hasSecondarySchool, $endDate, $tblGroupStudent, $tblGroupArchive, $tblGroupsCustom, $tblCompanies))
                    ) {
                        $dataList[$tblPerson->getId()] = $personData;
                    }
                }
            }

            // zusätzlich die händischen Schulabgänger ergänzen
            foreach ($formData as $personId => $item) {
                if (isset($item['Added'])) {
                    if (($tblPerson = Person::useService()->getPersonById($personId))) {
                        $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYearAndDateWithLeaved($tblPerson, $tblYear);
                        if (($personData = $this->getPersonData($tblSchoolType, $tblYear, $tblPerson, $tblStudentEducation ?: null,
                            $formData[$tblPerson->getId()] ?? [], true, $hasSecondarySchool, $endDate, $tblGroupStudent, $tblGroupArchive, $tblGroupsCustom,
                            $tblCompanies))
                        ) {
                            $dataList[$tblPerson->getId()] = $personData;
                        }
                    }
                }
            }

            if ($tblSchoolType->getShortName() == 'GS' && $hasSecondarySchool) {
                $content .= new Warning('Schüler mit der Schulart: Grundschule und der Klassenstufe: 4 werden in einem Schulzentrum als 
                    Schulabgänger automatisch hinzugefügt, sind allerdings nicht vorausgewählt. Bitte wählen Sie händisch die Schüler der Klassenstufe: 4 aus, 
                    welche Ihr Schulzentrum verlassen.', new Exclamation());
            }
        }

        $frontendDivisionCourse = DivisionCourse::useFrontend();
        $backgroundColor = '#E0F0FF';

        $headerColumnList = [];
        $headerColumnList['Select'] = $frontendDivisionCourse->getTableHeaderColumn('Auswahl', $backgroundColor);
        $headerColumnList['Name'] = $frontendDivisionCourse->getTableHeaderColumn('Name', $backgroundColor);
        $headerColumnList['DivisionCourse'] = $frontendDivisionCourse->getTableHeaderColumn('Aktueller Kurs', $backgroundColor);
        $headerColumnList['LeaveDate'] = $frontendDivisionCourse->getTableHeaderColumn(
            'Abgangsdatum' . $this->getEditButton($tblSchoolType, $tblYear, 'LeaveDate'),
            $backgroundColor
        );
        $headerColumnList['Company'] = $frontendDivisionCourse->getTableHeaderColumn(
            'Aufnehmende Schule'  . $this->getEditButton($tblSchoolType, $tblYear, 'Company'),
            $backgroundColor
        );
        $headerColumnList['GroupArchive'] = $frontendDivisionCourse->getTableHeaderColumn('In Gruppe: "Ehemalige (Archiv)" verschieben', $backgroundColor);
        $headerColumnList['GroupIndividual'] = $frontendDivisionCourse->getTableHeaderColumn(
            'In individuelle Gruppe verschieben' . $this->getEditButton($tblSchoolType, $tblYear, 'GroupIndividual'),
            $backgroundColor
        );

        // Sortierung nach mehreren Properties
        usort($dataList, function ($a, $b) {
            // natural sort
            $cmp = strnatcmp($a['DivisionCourse'], $b['DivisionCourse']);
            if ($cmp !== 0) {
                return $cmp;
            }

            return $a['Name'] <=> $b['Name'];
        });

        $content .= new Form(new FormGroup([
            new FormRow(new FormColumn(
                (new Primary('Schulabgänger hinzufügen', ApiLeaveStudent::getEndpoint(), new Plus()))
                    ->ajaxPipelineOnClick(ApiLeaveStudent::pipelineOpenAddStudentModal($tblSchoolType->getId(), $tblYear->getId()))
            )),
            new FormRow(new FormColumn(
                new Container('&nbsp;')
            )),
            new FormRow(new FormColumn(
                DivisionCourse::useFrontend()->getTableCustom($headerColumnList, $dataList)
            )),
            new FormRow(new FormColumn([
                (new Danger('Unwiderruflich Speichern', ApiLeaveStudent::getEndpoint(), new Save()))
                    ->ajaxPipelineOnClick(ApiLeaveStudent::pipelineSaveLeaveStudent($tblSchoolType->getId(), $tblYear->getId())),
                (new Standard('Abbrechen', ApiLeaveStudent::getEndpoint(), new Remove()))
                    ->ajaxPipelineOnClick(ApiLeaveStudent::pipelineCancelLeaveStudent($tblSchoolType->getId(), $tblYear->getId()))
            ])),
        ]));

        return $content;
    }

    private function getEditButton(TblType $tblSchoolType, TblYear $tblYear, string $identifier): string
    {
        return ' ' . (new Standard('Alle bearbeiten', ApiLeaveStudent::getEndpoint()))
            ->ajaxPipelineOnClick(ApiLeaveStudent::pipelineOpenEditModal($tblSchoolType->getId(), $tblYear->getId(), $identifier));
    }

    private function getPersonData(TblType $tblSchoolType, TblYear $tblYear, TblPerson $tblPerson, ?TblStudentEducation $tblStudentEducation,
        array $data, bool $isAdd, bool $hasSecondarySchool, $endDate, $tblGroupStudent, $tblGroupArchive, $tblGroupsCustom, $tblCompanies): ?array
    {
        $leaveDate = null;
        $company = null;
        if (($tblStudent = $tblPerson->getStudent())
            && ($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::LEAVE))
            && ($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))
        ) {
            if ($tblStudentTransfer->getTransferDate()) {
                $leaveDate = new DateTime($tblStudentTransfer->getTransferDate());
            }
            if ($tblStudentTransfer->getServiceTblCompany()) {
                $company = $tblStudentTransfer->getServiceTblCompany();
            }
        }

        // wann nicht mehr anzeigen? ist nicht mehr in der Gruppe Schüler + ist in feste Gruppe (Ehemalige Archiv) + hat ein Abgangsdatum
        if (!Group::useService()->existsGroupPerson($tblGroupStudent, $tblPerson)
            && Group::useService()->existsGroupPerson($tblGroupArchive, $tblPerson)
            && ($leaveDate && $leaveDate <= $endDate)
        ) {
            return null;
        }

        $isSelected = true;
        // maximale Klassenstufe für Schulart, beachte Spezialfall GS
        if (!$isAdd && $tblStudentEducation && ($level = $tblStudentEducation->getLevel()) && $level >= $tblSchoolType->getMaxLevel()) {
            $isAdd = true;
            if ($tblSchoolType->getShortName() == 'GS'
                && $hasSecondarySchool
                && (!$leaveDate || $leaveDate > $endDate)
            ) {
                $isSelected = false;
            }
        }

        // Schüler mit gedrucktem Abgangszeugnis oder Abschlusszeugnis
        if (!$isAdd && Prepare::useService()->getIsLeaveOrDiplomaStudent($tblPerson, $tblYear)) {
            $isAdd = true;
        }

        // Schülerbildung ist deaktiviert, beachte Klassenwechsel im Schuljahr → es gibt beide Einträge bzw. mehrere Einträge
        if (!$isAdd
            && $tblStudentEducation && $tblStudentEducation->getLeaveDate()
            && !DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear)
        ) {
            $isAdd = true;
        }

        // Abgangsdatum erreicht
        if (!$isAdd && $leaveDate && $leaveDate <= $endDate) {
            $isAdd = true;
        }

        if ($isAdd) {
            $post = $this->getGlobal();

            $post->POST['Data'][$tblPerson->getId()]['Select'] = isset($data['Select']) ?: $isSelected;
            $post->POST['Data'][$tblPerson->getId()]['LeaveDate'] = $data['LeaveDate']
                ?? ($leaveDate ? $leaveDate->format('d.m.Y') : $endDate->format('d.m.Y'));
            $post->POST['Data'][$tblPerson->getId()]['Company'] = $data['Company']
                ?? ($company ? $company->getId() : null);
            if (isset($data['GroupIndividual'])) {
                $post->POST['Data'][$tblPerson->getId()]['GroupIndividual'] = $data['GroupIndividual'];
            }

            $post->savePost();



            return [
                'Select' => new CheckBox(@"Data[{$tblPerson->getId()}][Select]", ' ', 1),
                'Name' => $tblPerson->getLastFirstNameWithCallNameUnderline(true) . (isset($data['Added']) ? new HiddenField(@"Data[{$tblPerson->getId()}][Added]") : ''),
                'DivisionCourse' => $this->getDivisionCourseName($tblStudentEducation ?: null),
                'LeaveDate' => new DatePicker(@"Data[{$tblPerson->getId()}][LeaveDate]", '', '', new Calendar()),
                'Company' => new SelectBox(@"Data[{$tblPerson->getId()}][Company]", '', ['{{ Name }}' => $tblCompanies]),
                'GroupArchive' => (new CheckBox(@"Data[{$tblPerson->getId()}][GroupArchive]", ' ', 1))->setChecked()->setDisabled(),
                'GroupIndividual' => new SelectBox(@"Data[{$tblPerson->getId()}][GroupIndividual]", '', ['{{ Name }}' => $tblGroupsCustom]),
            ];
        }

        return null;
    }

    private function getDivisionCourseName(?TblStudentEducation $tblStudentEducation): string
    {
        $divisionCourse = '';
        if ($tblStudentEducation) {
            if (($tblDivision = $tblStudentEducation->getTblDivision())) {
                $divisionCourse = $tblDivision->getName();
            } elseif (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())) {
                $divisionCourse = $tblCoreGroup->getName();
            }
        }

        return $divisionCourse;
    }

    /**
     * @param $SchoolTypeId
     * @param $YearId
     *
     * @return string
     */
    public function loadAddStudentContent($SchoolTypeId, $YearId): string
    {
        return new Title(new Plus() . ' Schulabgänger hinzufügen')
            . (new Form(new FormGroup(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel(
                            'Schüler',
                            (new TextField(
                                'Search',
                                '',
                                'Suche',
                                new Search()
                            ))->ajaxPipelineOnKeyUp(ApiLeaveStudent::pipelineSearchPerson($SchoolTypeId, $YearId))
                            . ApiLeaveStudent::receiverBlock(LeaveStudent::useFrontend()->loadPersonSearch($SchoolTypeId, $YearId, ''), 'SearchPerson'),
                            Panel::PANEL_TYPE_INFO
                        )
                    ))
                ))
            )))->disableSubmitAction();
    }

    /**
     * @param $SchoolTypeId
     * @param $YearId
     * @param $Search
     * @param IMessageInterface|null $message
     *
     * @return string
     */
    public function loadPersonSearch($SchoolTypeId, $YearId, $Search, IMessageInterface $message = null): string
    {
        $Data = [];
        if (($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))
            && ($tblYear = Term::useService()->getYearById($YearId))
            && ($tblLeaveStudent = LeaveStudent::useService()->getLeaveStudentBy($tblSchoolType, $tblYear))
        ) {
            $Data = $tblLeaveStudent->getData();
        }

        if ($Search != '' && strlen($Search) > 2) {
            $resultList = array();
            $result = '';
            if (($tblPersonList = Person::useService()->getPersonListLike($Search))
                && ($tblYear = Term::useService()->getYearById($YearId))
            ) {
                $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
                foreach ($tblPersonList as $tblPerson) {
                    // nur nach Schülern suchen
                    if (Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                        $schoolType = '';
                        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYearAndDateWithLeaved($tblPerson, $tblYear))) {
                            $schoolType = ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType()) ? $tblSchoolType->getName() : '';
                        }

                        $resultList[] = array(
                            'FirstName' => $tblPerson->getFirstSecondName(),
                            'LastName' => $tblPerson->getLastName(),
                            'SchoolType' => $schoolType,
                            'DivisionCourse' => $this->getDivisionCourseName($tblStudentEducation ?: null),
                            'Option' => isset($Data[$tblPerson->getId()])
                                ? new \SPHERE\Common\Frontend\Text\Repository\Warning('bereits enthalten')
                                : (new Standard('', ApiLeaveStudent::getEndpoint(), new PlusSign()))
                                    ->ajaxPipelineOnClick(ApiLeaveStudent::pipelineAddStudentSave($SchoolTypeId, $YearId, $tblPerson->getId()))
                        );
                    }
                }

                $result = new TableData(
                    $resultList,
                    null,
                    array(
                        'LastName' => 'Nachname',
                        'FirstName' => 'Vorname',
                        'SchoolType' => 'Schulart',
                        'DivisionCourse' => 'Kurs',
                        'Option' => ''
                    ),
                    array(
                        'order' => array(
                            array(0, 'asc'),
                        ),
                        'columnDefs' => array(
                            array('orderable' => false, 'targets' => -1),
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

        return $result . ($message ?: '');
    }

    /**
     * @param $SchoolTypeId
     * @param $YearId
     * @param string $Identifier
     * @param array $Data
     * @param array|null $EditData
     *
     * @return string
     */
    public function loadEditModalContent($SchoolTypeId, $YearId, string $Identifier, array $Data, ?array $EditData = null): string
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        if (!($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))
            || !($tblYear = Term::useService()->getYearById($YearId))
        ) {
            return '';
        }

        $name = match ($Identifier) {
            'LeaveDate' => 'Abgangsdatum',
            'GroupIndividual' => 'Individuelle Gruppe',
            'Company' => 'Aufnehmende Schule',
            default => ''
        };

        $frontendDivisionCourse = DivisionCourse::useFrontend();
        $backgroundColor = '#E0F0FF';

        $headerColumnList = [];
        $headerColumnList['Select'] = $frontendDivisionCourse->getTableHeaderColumn('Auswahl', $backgroundColor);
        $headerColumnList['Name'] = $frontendDivisionCourse->getTableHeaderColumn('Name', $backgroundColor);
        $headerColumnList['DivisionCourse'] = $frontendDivisionCourse->getTableHeaderColumn('Aktueller Kurs', $backgroundColor);
        $headerColumnList[$Identifier] = $frontendDivisionCourse->getTableHeaderColumn($name, $backgroundColor);

        $dataList = [];
        $global = $this->getGlobal();
        foreach ($Data as $personId => $item) {
            if (($tblPerson = Person::useService()->getPersonById($personId))) {
                $global->POST['EditData']['Persons'][$personId] = 1;
                $global->savePost();

                $content = match($Identifier) {
                    'LeaveDate' => $item['LeaveDate'] ?? '',
                    'GroupIndividual' => $item['GroupIndividual']
                        ? (($tblGroup = Group::useService()->getGroupById($item['GroupIndividual']))
                            ? $tblGroup->getName()
                            : ''
                        )
                        : '',
                    'Company' => $item['Company']
                        ? (($tblCompany = Company::useService()->getCompanyById($item['Company']))
                            ? $tblCompany->getName()
                            : ''
                        )
                        : '',
                    default => ''
                };

                $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);

                $dataList[] = [
                    'Select' => new CheckBox(@"EditData[Persons][{$tblPerson->getId()}]", ' ', 1),
                    'Name' => $tblPerson->getLastFirstNameWithCallNameUnderline(true),
                    'DivisionCourse' => $this->getDivisionCourseName($tblStudentEducation ?: null),
                    $Identifier => $content,
                ];
            }
        }

        $field = match ($Identifier) {
            'LeaveDate' => new DatePicker('EditData[LeaveDate]', '', '', new Calendar()),
            'GroupIndividual' => new SelectBox('EditData[GroupIndividual]', '',
                ['{{ Name }}' => Group::useService()->getGroupAllSorted(true)]),
            'Company' => new SelectBox('EditData[Company]', '',
                ['{{ Name }}' => CorporationGroup::useService()->getCompanyAllByGroup(CorporationGroup::useService()->getGroupByMetaTable('SCHOOL'))]),
            default => ''
        };

        $form = new Form(new FormGroup([
            new FormRow(new FormColumn(
                DivisionCourse::useFrontend()->getTableCustom($headerColumnList, $dataList)
            )),
            new FormRow(new FormColumn(
                new Panel($name, $field, Panel::PANEL_TYPE_INFO)
            )),
            new FormRow(new FormColumn(
                (new Primary('Speichern', ApiLeaveStudent::getEndpoint(), new Save()))
                    ->ajaxPipelineOnClick(ApiLeaveStudent::pipelineEditModalSave($SchoolTypeId, $YearId, $Identifier, $EditData))
            )),
        ]));

        return new Title(new Edit() . ' Massenänderung für: ' . $name)
            . new ToggleCheckbox('Alle auswählen/abwählen', $form)
            . $form;
    }
}