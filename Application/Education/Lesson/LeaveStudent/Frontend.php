<?php

namespace SPHERE\Application\Education\Lesson\LeaveStudent;

use DateTime;
use SPHERE\Application\Api\Education\Lesson\ApiLeaveStudent;
use SPHERE\Application\Corporation\Group\Group as CorporationGroup;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
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
        if ($tblSchoolType
            && $tblYear
            && ($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYear, $tblSchoolType, null, null, null, false))
        ) {
            list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            $tblSchoolTypeList = School::useService()->getConsumerSchoolTypeAll();
            $hasSecondarySchool = isset($tblSchoolTypeList['OS']) || isset($tblSchoolTypeList['Gy']);
            $tblGroupStudent = Group::useService()->getGroupByMetaTable('STUDENT');
            $tblGroupArchive = Group::useService()->getGroupByMetaTable('ARCHIVE');
            $tblGroupsCustom = Group::useService()->getGroupAllSorted(true);
            $tblCompanies = CorporationGroup::useService()->getCompanyAllByGroup(CorporationGroup::useService()->getGroupByMetaTable('SCHOOL'));

            $tblStudentEducationList = $this->getSorter($tblStudentEducationList)->sortObjectBy('Sort');

            /** @var TblStudentEducation $tblStudentEducation */
            foreach ($tblStudentEducationList as $tblStudentEducation) {
                if (($tblPerson = $tblStudentEducation->getServiceTblPerson())
                    && ($level = $tblStudentEducation->getLevel())
                ) {
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
                        continue;
                    }

                    $isAdd = false;
                    $isSelected = true;
                    // maximale Klassenstufe für Schulart, beachte Spezialfall GS
                    if ($level >= $tblSchoolType->getMaxLevel()) {
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
                        && $tblStudentEducation->getLeaveDate() && !DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear)
                    ) {
                        $isAdd = true;
                    }

                    // Abgangsdatum erreicht
                    if (!$isAdd && $leaveDate && $leaveDate <= $endDate) {
                        $isAdd = true;
                    }

                    if ($isAdd) {
                        $post = $this->getGlobal();
                        $post->POST['Data'][$tblPerson->getId()]['Select'] = $isSelected;
                        $post->POST['Data'][$tblPerson->getId()]['LeaveDate'] = $leaveDate ? $leaveDate->format('d.m.Y') : $endDate->format('d.m.Y');
                        $post->POST['Data'][$tblPerson->getId()]['Company'] = $company ? $company->getId() : null;

                        $post->savePost();

                        $divisionCourse = '';
                        if (($tblDivision = $tblStudentEducation->getTblDivision())) {
                            $divisionCourse  = $tblDivision->getName();
                        } elseif (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())) {
                            $divisionCourse  = $tblCoreGroup->getName();
                        }

                        $dataList[$tblPerson->getId()] = [
                            'Select' => new CheckBox(@"Data[{$tblPerson->getId()}][Select]", ' ', 1),
                            'Name' => $tblPerson->getLastFirstNameWithCallNameUnderline(true),
                            'DivisionCourse' => $divisionCourse,
                            'LeaveDate' => new DatePicker(@"Data[{$tblPerson->getId()}][LeaveDate]", '', '', new Calendar()),
                            'GroupArchive' => (new CheckBox(@"Data[{$tblPerson->getId()}][GroupArchive]", ' ', 1))->setChecked()->setDisabled(),
                            'GroupIndividual' => new SelectBox(@"Data[{$tblPerson->getId()}][GroupIndividual]", '', ['{{ Name }}' => $tblGroupsCustom]),
                            'Company' => new SelectBox(@"Data[{$tblPerson->getId()}][Company]", '', ['{{ Name }}' => $tblCompanies]),
                        ];
                    }
                }
            }

            if ($tblSchoolType->getShortName() == 'GS' && $hasSecondarySchool) {
                $content .= new Warning('Schüler mit der Schulart: Grundschule und der Klassenstufe: 4 werden in einem Schulzentrum als 
                    Schulabgänger automatisch hinzugefügt, sind allerdings nicht vorausgewählt. Bitte wählen Sie händisch die Schüler der Klassenstufe: 4 aus, 
                    welche Ihr Schulzentrum verlassen.', new Exclamation());
            }
        }

        // TODO: bearbeiten von ganzen Spalten z.B: Abgangsdatum

        $columns = [
            'Select' => 'Auswahl',
            'Name' => 'Name',
            'DivisionCourse' => 'Aktueller Kurs',
            'LeaveDate' => 'Abgangsdatum',
            'GroupArchive' => 'In Gruppe: "Ehemalige (Archiv)" verschieben',
            'GroupIndividual' => 'In individuelle Gruppe verschieben',
            'Company' => 'Aufnehmende Schule'
        ];

        usort($dataList, function ($a, $b) {
            // natural sort
            $cmp = strnatcmp($a['DivisionCourse'], $b['DivisionCourse']);
            if ($cmp !== 0) {
                return $cmp;
            }

            return $a['Name'] <=> $b['Name'];
        });

        // TODO oder die eigne CustomTable
        $content .= new Form(new FormGroup(new FormRow(new FormColumn(
            new TableData($dataList, null, $columns, false)
        ))));

        return $content;

        // TODO: Schülerbildung fürs kommende Jahr löschen falls vorhanden
    }
}