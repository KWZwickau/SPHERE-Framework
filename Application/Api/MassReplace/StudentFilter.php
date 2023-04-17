<?php

namespace SPHERE\Application\Api\MassReplace;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\ToggleCheckbox;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Extension\Extension;

class StudentFilter extends Extension
{
    const STUDENT_FILTER = 'StudentFilter';

    /**
     * @param $modalField
     * @param $Data
     *
     * @return Form
     */
    public function formStudentFilter($modalField, $Data): Form
    {
        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        if ($Data) {
            $global = $this->getGlobal();
            $global->POST['Data']['Year'] = $Data['Year'];
            $global->POST['Data']['SchoolType'] = $Data['SchoolType'];
            $global->POST['Data']['Level'] = $Data['Level'];
            $global->POST['Data']['Division'] = $Data['Division'];
            $global->POST['Data']['CoreGroup'] = $Data['CoreGroup'];

            $global->savePost();
        }

        $tblYearList = Term::useService()->getYearAllSinceYears(1);
        if (isset($Data['Year']) && ($tblYear = Term::useService()->getYearById($Data['Year']))) {
            if ($tblYearList && !isset($tblYearList[$Data['Year']])) {
                $tblYearList[$tblYear->getId()] = $tblYear;
            } elseif (!$tblYearList) {
                $tblYearList = array($tblYear->getId() => $tblYear);
            }
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        (new SelectBox('Data[Year]', 'Schuljahr '.new DangerText('*'), array('{{ Name }} {{ Description }}' => $tblYearList)))
                            ->ajaxPipelineOnChange(array(ApiMassReplace::pipelineLoadDivisionsSelectBox($Data), ApiMassReplace::pipelineLoadCoreGroupsSelectBox($Data)))
                    ), 3),
                    new FormColumn(array(
                        new SelectBox('Data[SchoolType]', 'Schulart', array('Name' => Type::useService()->getTypeAll()))
                    ), 3),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        new NumberField('Data[Level]', '', 'Klassenstufe')
                    ), 3),
                    new FormColumn(array(
                        ApiMassReplace::receiverBlock('', 'DivisionsSelectBox')
                    ), 3),
                    new FormColumn(array(
                        ApiMassReplace::receiverBlock('', 'CoreGroupsSelectBox')
                    ), 3),
                )),
                new FormRow(
                    new FormColumn(
                        (new Primary('Filtern',
                            ApiMassReplace::getEndpoint(),
                            null,
                            $this->getGlobal()->POST))->ajaxPipelineOnClick(ApiMassReplace::pipelineOpen($Field))
                    )
                ),
//                new FormRow(
//                    new FormColumn(
//                        new DangerText('*'.new Small('Pflichtfeld'))
//                    )
//                )
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param $Data
     *
     * @return SelectBox|null
     */
    public function loadDivisionsSelectBox($Data): ?SelectBox
    {
        if (isset($Data['Year']) && ($tblYear = Term::useService()->getYearById($Data['Year']))) {
            if (($tblDivisionCourseDivisionList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_DIVISION))) {
                return new SelectBox('Data[Division]', 'Klasse', array('Name' => $tblDivisionCourseDivisionList));
            }
        }

        return null;
    }

    /**
     * @param $Data
     *
     * @return SelectBox|null
     */
    public function loadCoreGroupsSelectBox($Data): ?SelectBox
    {
        if (isset($Data['Year']) && ($tblYear = Term::useService()->getYearById($Data['Year']))) {
            if (($tblDivisionCourseCoreGroupList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_CORE_GROUP))) {
                return new SelectBox('Data[CoreGroup]', 'Stammgruppe', array('Name' => $tblDivisionCourseCoreGroupList));
            }
        }

        return null;
    }

    /**
     * // Content for OpenModal -> ApiMassReplace
     *
     * @param $modalField
     * @param $Node
     * @param $Data
     *
     * @return string
     */
    public function getFrontendStudentFilter($modalField, $Node, $Data): string
    {
        /** @var SelectBox|TextField $Field */
        $Field = unserialize(base64_decode($modalField));
        $CloneField = (new ApiMassReplace())->cloneField($Field, 'CloneField', 'Auswahl/Eingabe ' . new SuccessText($Node).' - '.$Field->getLabel());

        $TableContent = $this->getStudentFilterResult($Field, $Data);

        $Table = (new TableData($TableContent, null,
            array(
                'Check'         => 'Auswahl',
                'Name'          => 'Name',
                'StudentNumber' => 'Schüler&shy;nummer',
                'Level'         => 'Stufe',
                'Division'      => 'Klasse',
                'CoreGroup'     => 'Stammgruppe',
                'Edit'          => $Field->getLabel(),
            ), array(
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 3),
                ),
                'order'      => array(array(1, 'asc')),
                'pageLength' => -1,
                'paging'     => false,
                'info'       => false,
                'searching'  => false,
                'responsive' => false
            )))->setHash('MassReplaceStudent' . $Node . $Field->getLabel());

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Danger('Achtung: Die Massenänderung kann nicht automatisch rückgängig gemacht werden!')
                    ),
                    new LayoutColumn(new Well(
                        ApiMassReplace::receiverFilter('Filter', $this->formStudentFilter($modalField, $Data))
                    )),
                    new LayoutColumn(new Well(
                        (new Form(
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn(
                                        new Panel('Weitere Personen ('.new Bold(count($TableContent)).' nach Filterung):',
                                            (!empty($TableContent)
                                                ? new ToggleCheckbox('Alle wählen/abwählen', $Table).$Table
                                                : new Warning('Keine Personen gefunden '.
                                                    new ToolTip(new Info(), 'Das Schuljahr ist ein Pflichtfeld'))),
                                            Panel::PANEL_TYPE_INFO
                                        )
                                    ),
                                    new FormColumn(
                                        $CloneField
                                    )
                                )),
                                new FormRow(
                                    new FormColumn(
                                        (new Primary('Speichern', ApiMassReplace::getEndpoint(), new Save(),
                                            $this->getGlobal()->POST))->ajaxPipelineOnClick(ApiMassReplace::pipelineSave($Field))
                                    )
                                )
                            ))
                        ))->disableSubmitAction()
                    ))
                ))
            )
        );
    }

    /**
     * @param AbstractField $Field
     * @param $Data
     *
     * @return array
     */
    private function getStudentFilterResult(AbstractField $Field, $Data): array
    {
        /** @var SelectBox|TextField $Field */
        $Label = $Field->getLabel();

        $tblStudentTransferType = false;
        if (preg_match('!([Meta]*)(\[[Transfer]*\])\[([\d]*)\](\[[\w]*\])!is', $Field->getName(), $matches)) {
//            return new Code(print_r($Matches, true));
            if (isset($matches[3])) {
                $tblStudentTransferType = Student::useService()->getStudentTransferTypeById($matches[3]);
            }
        }

        $SearchResult = array();
        if ($Data) {
            if (($tblYear = Term::useService()->getYearById($Data['Year']))
                && ($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy(
                    $tblYear,
                    Type::useService()->getTypeById($Data['SchoolType']) ?: null,
                    $Data['Level'] ?: null,
                    DivisionCourse::useService()->getDivisionCourseById($Data['Division']) ?: null,
                    DivisionCourse::useService()->getDivisionCourseById($Data['CoreGroup']) ?: null,
                ))
            ) {
                foreach ($tblStudentEducationList as $tblStudentEducation) {
                    if (($tblPerson = $tblStudentEducation->getServiceTblPerson())) {
                        $DataPerson = array();
                        $DataPerson['Edit'] = ''; // get content by Field->getLabel()

                        $DataPerson['Check'] = (new CheckBox('PersonIdArray[' . $tblPerson->getId() . ']', ' ',
                            $tblPerson->getId()
                            , array($tblPerson->getId())))->setChecked();
                        $DataPerson['Name'] = $tblPerson->getLastFirstName();

                        $DataPerson['Level'] = $tblStudentEducation->getLevel();
                        $DataPerson['Division'] = ($tblDivision = $tblStudentEducation->getTblDivision()) ? $tblDivision->getName() : '';
                        $DataPerson['CoreGroup'] = ($tblCoreGroup = $tblStudentEducation->getTblCoreGroup()) ? $tblCoreGroup->getName() : '';

                        if (strpos($Field->getName(), 'StudentEducationData') !== false) {
                            switch ($Label) {
                                case 'Klassenstufe': $DataPerson['Edit'] = $tblStudentEducation->getLevel(); break;
                                case 'Schulart': $DataPerson['Edit'] = ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType()) ? $tblSchoolType->getName() : ''; break;
                                case 'Schule': $DataPerson['Edit'] = ($tblCompany = $tblStudentEducation->getServiceTblCompany()) ? $tblCompany->getName() : ''; break;
                                case 'Bildungsgang': $DataPerson['Edit'] = ($tblCourse = $tblStudentEducation->getServiceTblCourse()) ? $tblCourse->getName() : ''; break;
                            }
                        }

                        if (($tblStudent = $tblPerson->getStudent())) {
                            $DataPerson['StudentNumber'] = $tblStudent->getIdentifierComplete();

                            // Grunddaten
                            if ($Label == 'Prefix') {
                                $DataPerson['Edit'] = $tblStudent->getPrefix();
                            }
                            if ($Label == 'Beginnt am') {
                                $DataPerson['Edit'] = $tblStudent->getSchoolAttendanceStartDate();
                            }
                            // Transfer
                            if ($tblStudentTransferType) {
    //                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                    $tblStudentTransferType);
                                if ($tblStudentTransfer) {
                                    // Ersteinschulung
                                    if (($tblCompany = $tblStudentTransfer->getServiceTblCompany()) && $Label == 'Schule'
                                        && $tblStudentTransferType->getIdentifier() == 'ENROLLMENT'
                                    ) {
                                        $DataPerson['Edit'] = $tblCompany->getName();
                                    }
                                    if (($tblType = $tblStudentTransfer->getServiceTblType()) && $Label == 'Schulart'
                                        && $tblStudentTransferType->getIdentifier() == 'ENROLLMENT'
                                    ) {
                                        $DataPerson['Edit'] = $tblType->getName();
                                    }
                                    if (($tblStudentSchoolEnrollmentType = $tblStudentTransfer->getTblStudentSchoolEnrollmentType())
                                        && $Label == 'Einschulungsart'
                                        && $tblStudentTransferType->getIdentifier() == 'ENROLLMENT'
                                    ) {
                                        $DataPerson['Edit'] = $tblStudentSchoolEnrollmentType->getName();
                                    }
                                    if (($tblCourse = $tblStudentTransfer->getServiceTblCourse()) && $Label == 'Bildungsgang'
                                        && $tblStudentTransferType->getIdentifier() == 'ENROLLMENT'
                                    ) {
                                        $DataPerson['Edit'] = $tblCourse->getName();
                                    }
                                    if (($transferDate = $tblStudentTransfer->getTransferDate()) && $Label == 'Datum'
                                        && $tblStudentTransferType->getIdentifier() == 'ENROLLMENT'
                                    ) {
                                        $DataPerson['Edit'] = $transferDate;
                                    }

                                    // Schüler - Aufnahme
                                    if (($tblCompany = $tblStudentTransfer->getServiceTblCompany()) && $Label == 'Abgebende Schule / Kita'
                                        && $tblStudentTransferType->getIdentifier() == 'ARRIVE'
                                    ) {
                                        $DataPerson['Edit'] = $tblCompany->getName();
                                    }
                                    if (($tblStateCompany = $tblStudentTransfer->getServiceTblStateCompany()) && $Label == 'Staatliche Stammschule'
                                        && $tblStudentTransferType->getIdentifier() == 'ARRIVE'
                                    ) {
                                        $DataPerson['Edit'] = $tblStateCompany->getName();
                                    }
                                    if (($tblType = $tblStudentTransfer->getServiceTblType()) && $Label == 'Letzte Schulart'
                                        && $tblStudentTransferType->getIdentifier() == 'ARRIVE'
                                    ) {
                                        $DataPerson['Edit'] = $tblType->getName();
                                    }
                                    if (($tblCourse = $tblStudentTransfer->getServiceTblCourse()) && $Label == 'Letzter Bildungsgang'
                                        && $tblStudentTransferType->getIdentifier() == 'ARRIVE'
                                    ) {
                                        $DataPerson['Edit'] = $tblCourse->getName();
                                    }
                                    if (($transferDate = $tblStudentTransfer->getTransferDate()) && $Label == 'Datum'
                                        && $tblStudentTransferType->getIdentifier() == 'ARRIVE'
                                    ) {
                                        $DataPerson['Edit'] = $transferDate;
                                    }

                                    // Schüler - Abgabe
                                    if (($tblCompany = $tblStudentTransfer->getServiceTblCompany()) && $Label == 'Aufnehmende Schule'
                                        && $tblStudentTransferType->getIdentifier() == 'LEAVE'
                                    ) {
                                        $DataPerson['Edit'] = $tblCompany->getName();
                                    }
                                    if (($tblType = $tblStudentTransfer->getServiceTblType()) && $Label == 'Letzte Schulart'
                                        && $tblStudentTransferType->getIdentifier() == 'LEAVE'
                                    ) {
                                        $DataPerson['Edit'] = $tblType->getName();
                                    }
                                    if (($tblCourse = $tblStudentTransfer->getServiceTblCourse()) && $Label == 'Letzter Bildungsgang'
                                        && $tblStudentTransferType->getIdentifier() == 'LEAVE'
                                    ) {
                                        $DataPerson['Edit'] = $tblCourse->getName();
                                    }
                                    if (($transferDate = $tblStudentTransfer->getTransferDate()) && $Label == 'Datum'
                                        && $tblStudentTransferType->getIdentifier() == 'LEAVE'
                                    ) {
                                        $DataPerson['Edit'] = $transferDate;
                                    }

                                    // Schulverlauf
//                                    if (($tblCompany = $tblStudentTransfer->getServiceTblCompany()) && $Label == 'Aktuelle Schule'
//                                        && $tblStudentTransferType->getIdentifier() == 'PROCESS'
//                                    ) {
//                                        $DataPerson['Edit'] = $tblCompany->getName();
//                                    }
//                                    if (($tblCourse = $tblStudentTransfer->getServiceTblCourse()) && $Label == 'Aktueller Bildungsgang'
//                                        && $tblStudentTransferType->getIdentifier() == 'PROCESS'
//                                    ) {
//                                        $DataPerson['Edit'] = $tblCourse->getName();
//                                    }
    //                                if(( $tblType = $tblStudentTransfer->getServiceTblType()) && $Label == 'Aktuelle Schulart'){
    //                                $DataPerson['Edit'] = $tblType->getName();
    //                                }
                                }
                            }
                            // Subject
                            if ($Label == 'Religion') {
                                $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION');
                                $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier('1');
                                $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                                    $tblStudentSubjectType, $tblStudentSubjectRanking);
                                if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                    $DataPerson['Edit'] = new Muted('(' . $tblSubject->getAcronym() . ') ') . $tblSubject->getName();
                                }
                            }
                            if ($Label == 'Profil') {
                                $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE');
                                $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier('1');
                                $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                                    $tblStudentSubjectType, $tblStudentSubjectRanking);
                                if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                    $DataPerson['Edit'] = new Muted('(' . $tblSubject->getAcronym() . ') ') . $tblSubject->getName();
                                }
                            }
                            $tblStudentSubjectTypeOrientation = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION');
                            if ($Label == $tblStudentSubjectTypeOrientation->getName()) {
                                $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier('1');
                                $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                                    $tblStudentSubjectTypeOrientation, $tblStudentSubjectRanking);
                                if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                    $DataPerson['Edit'] = new Muted('(' . $tblSubject->getAcronym() . ') ') . $tblSubject->getName();
                                }
                            }
                            for ($i = 1; $i < 6; $i++) {
                                if ($Label == $i . '. Fremdsprache') {
                                    $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                                    $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                                    $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                                        $tblStudentSubjectType, $tblStudentSubjectRanking);
                                    if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                        $DataPerson['Edit'] = new Muted('(' . $tblSubject->getAcronym() . ') ') . $tblSubject->getName();
                                    }
                                }
                                if ($Label == $i . '. Wahlfach') {
                                    $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ELECTIVE');
                                    $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                                    $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                                        $tblStudentSubjectType, $tblStudentSubjectRanking);
                                    if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                        $DataPerson['Edit'] = new Muted('(' . $tblSubject->getAcronym() . ') ') . $tblSubject->getName();
                                    }
                                }
                                if ($Label == $i . '. Arbeitsgemeinschaft') {
                                    $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('TEAM');
                                    $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                                    $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                                        $tblStudentSubjectType, $tblStudentSubjectRanking);
                                    if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                        $DataPerson['Edit'] = new Muted('(' . $tblSubject->getAcronym() . ') ') . $tblSubject->getName();
                                    }
                                }
                                if ($Label == new Muted(new Small($i . '. Fremdsprache von Klasse'))) {
                                    $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                                    $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                                    $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                                        $tblStudentSubjectType, $tblStudentSubjectRanking);
                                    if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                        $DataPerson['Edit'] = ($tblStudentSubject->getLevelFrom() ? $tblStudentSubject->getLevelFrom() . ' ' : '')
                                            . new Muted('(' . $tblSubject->getAcronym() . ') ');
                                    }
                                }
                                if ($Label == new Muted(new Small($i . '. Fremdsprache bis Klasse'))) {
                                    $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                                    $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                                    $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                                        $tblStudentSubjectType, $tblStudentSubjectRanking);
                                    if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                                        $DataPerson['Edit'] = ($tblStudentSubject->getLevelTill() ? $tblStudentSubject->getLevelTill() . ' ' : '')
                                            . new Muted('(' . $tblSubject->getAcronym() . ') ');
                                    }
                                }
                            }

                            // MedicalRecord
                            if (($tblMedicalRecord = $tblStudent->getTblStudentMedicalRecord())) {
                                if ($Label == 'Datum (vorgelegt am)') {
                                    $DataPerson['Edit'] = $tblMedicalRecord->getMasernDate();
                                }
                                if ($Label == 'Art der Bescheinigung') {
                                    if (($MasernInfo = $tblMedicalRecord->getMasernDocumentType())) {
                                        $DataPerson['Edit'] = $MasernInfo->getTextShort();
                                    } else {
                                        $DataPerson['Edit'] = '';
                                    }

                                }
                                if ($Label == 'Bescheinigung, dass der Nachweis bereits vorgelegt wurde, durch') {
                                    if (($MasernInfo = $tblMedicalRecord->getMasernCreatorType())) {
                                        $DataPerson['Edit'] = $MasernInfo->getTextShort();
                                    } else {
                                        $DataPerson['Edit'] = '';
                                    }
                                }
                            }

                            // TechnicalSchool
                            if (($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())) {
                                if ($Label == 'Bildungsgang / Berufsbezeichnung / Ausbildung') {
                                    if (($tblTechnicalCourse = $tblStudentTechnicalSchool->getServiceTblTechnicalCourse())) {
                                        $DataPerson['Edit'] = $tblTechnicalCourse->getName();
                                    }
                                }
                                if ($Label == 'Fachrichtung') {
                                    if (($tblTechnicalSubjectArea = $tblStudentTechnicalSchool->getServiceTblTechnicalSubjectArea())) {
                                        $DataPerson['Edit'] = $tblTechnicalSubjectArea->getName();
                                    }
                                }
                                if ($Label == 'Zeitform des Unterrichts') {
                                    if (($tblStudentTenseOfLesson = $tblStudentTechnicalSchool->getTblStudentTenseOfLesson())) {
                                        $DataPerson['Edit'] = $tblStudentTenseOfLesson->getName();
                                    }
                                }
                                if ($Label == 'Ausbildungsstatus') {
                                    if (($tblStudentTrainingStatus = $tblStudentTechnicalSchool->getTblStudentTrainingStatus())) {
                                        $DataPerson['Edit'] = $tblStudentTrainingStatus->getName();
                                    }
                                }
                            }

                        }

                        $SearchResult[$tblPerson->getId()] = $DataPerson;
                    }
                }
            }
        }

        return $SearchResult;
    }
}