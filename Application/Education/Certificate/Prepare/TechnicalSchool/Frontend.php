<?php

namespace SPHERE\Application\Education\Certificate\Prepare\TechnicalSchool;

use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiGradesAllYears;
use SPHERE\Application\Api\Education\Prepare\ApiPrepare;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveComplexExam;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareComplexExam;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\Prepare\TechnicalSchool
 */
class Frontend extends Extension
{


    public function __construct()
    {
        // todo wird es überhaupt benötigt
        // Grades
        $this->selectListGrades[-1] = '';
        for ($i = 1; $i < 6; $i++) {
            $this->selectListGrades[$i] = (string)($i);
        }
        $this->selectListGrades[6] = 6;

        // GradeTexts
        if (($tblGradeTextList = Gradebook::useService()->getGradeTextAll())) {
            $this->selectListGradeTexts = $tblGradeTextList;
        }
    }

    /**
     * @param $panelName
     * @param $identifier
     * @param $isApproved
     *
     * @return Panel
     */
    public function getPanelWithoutInput($panelName, $identifier, $isApproved) : Panel
    {
        $grade = new SelectCompleter('Data[InformationList][' . $identifier . '_Grade]', 'Zensur', '', $this->selectListGrades);
        $gradeText = new SelectBox('Data[InformationList][' . $identifier . '_GradeText]', 'oder Zeugnistext',
            array(TblGradeText::ATTR_NAME => $this->selectListGradeTexts));

        if ($isApproved) {
            $grade->setDisabled();
            $gradeText->setDisabled();
        }

        return new Panel(
            $panelName,
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn($grade, 6),
                new LayoutColumn($gradeText, 6)
            )))),
            Panel::PANEL_TYPE_INFO
        );
    }

    /**
     * @param null $PrepareId
     * @param null $GroupId
     * @param null $CurrentTab
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendPrepareDiplomaTechnicalSetting(
        $PrepareId = null,
        $GroupId = null,
        $CurrentTab = null,
        $Data = null
    ) {
        if ($tblPrepare = Prepare::useService()->getPrepareById($PrepareId)) {
            // Grades
            $this->selectListGrades = array();
            $this->selectListGrades[-1] = '';
            for ($i = 1; $i < 6; $i++) {
                $this->selectListGrades[$i] = (string)($i);
            }
            $this->selectListGrades[6] = 6;

            // GradeTexts
            $this->selectListGradeTexts = array();
            if (($tblGradeTextList = Gradebook::useService()->getGradeTextAll())) {
                $this->selectListGradeTexts = $tblGradeTextList;
            }

            $tblDivision = false;
            $tblGroup = false;
            $tblPrepareList = false;
            $description = '';
            $tblType = false;
            $tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate();
            if ($GroupId && ($tblGroup = Group::useService()->getGroupById($GroupId))) {
                $description = 'Gruppe ' . $tblGroup->getName();
                if (($tblGenerateCertificate)) {
                    $tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
                }
            } else {
                if (($tblDivision = $tblPrepare->getServiceTblDivision())) {

                    $description = 'Klasse ' . $tblDivision->getDisplayName();
                    $tblPrepareList = array(0 => $tblPrepare);

                    $tblType = $tblDivision->getType();
                }
            }

            if ($CurrentTab == null) {
                $CurrentTab = 1;
            }

            $countInformationPages = 1;
            $additionalRemarkFhrTab = false;
            // Aufteilung der Sonstigen Informationen auf mehrere Seiten
            list($informationPageList) = Prepare::useService()->getCertificateInformationPages($tblPrepareList, $tblGroup, false);
            if (!empty($informationPageList)) {
                foreach ($informationPageList as $certificateId => $pageList) {
                    $countByCertificate = count($pageList);
                    $countByCertificate++;
                    if ($countByCertificate > $countInformationPages) {
                        $countInformationPages = $countByCertificate;
                    }

                    if (!$additionalRemarkFhrTab) {
                        foreach ($pageList as $page => $fieldList) {
                            if (isset($fieldList['AdditionalRemarkFhr'])) {
                                $additionalRemarkFhrTab = $page;

                                break;
                            }
                        }
                    }
                }
            }

            $tabs = $this->getTabsByType($tblType ? $tblType : null, $countInformationPages);

            $Stage = new Stage('Zeugnisvorbereitung', isset($tabs[$CurrentTab])
                ? $tabs[$CurrentTab]['StageName'] . ' festlegen' : '');

            if ($tblGroup) {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                    array(
                        'GroupId' => $tblGroup->getId(),
                        'Route' => 'Diploma'
                    )
                ));
            } else {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                    array(
                        'DivisionId' => $tblDivision ? $tblDivision->getId() : 0,
                        'Route' => 'Diploma'
                    )
                ));
            }

            $buttonList = $this->createExamsTechnicalButtonList($tblPrepare, $tblGroup ? $tblGroup : null, $CurrentTab, $tabs);

            $studentTable = array();
            $columnTable = array(
                'Number' => '#',
                'Name' => 'Name',
                'Course' => 'Bildungsgang',
            );

            if (isset($tabs[$CurrentTab])) {
                $tabIdentifier = $tabs[$CurrentTab]['Identifier'];
                if ($tabIdentifier == 'K1' || $tabIdentifier == 'K2' || $tabIdentifier == 'K3' || $tabIdentifier == 'K4'
                    || $tabIdentifier == 'P'
                ) {
                    // Komplexprüfungen
                    $columnTable['S1'] = '1. Fach';
                    $columnTable['S2'] = '2. Fach';
                    $columnTable['Grade'] = 'Zensur';
                    $columnTable['GradeText'] = 'oder Zeugnistext';
                }
            } else {
                $tabIdentifier = '';
            }

            $CertificateList = array();
            foreach ($tblPrepareList as $tblPrepareItem) {
                if (($tblDivisionItem = $tblPrepareItem->getServiceTblDivision())
                    && (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivisionItem)))
                ) {
                    foreach ($tblStudentList as $tblPerson) {
                        if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                            $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareItem, $tblPerson);

                            $studentTable[$tblPerson->getId()] = array(
                                'Number' => (count($studentTable) + 1) . ' '
                                    . ($tblPrepareStudent && $tblPrepareStudent->isApproved()
                                        ? new ToolTip(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Ban()),
                                            'Das Zeugnis des Schülers wurde bereits freigegeben und kann nicht mehr bearbeitet werden.')
                                        : new ToolTip(new Success(new Edit()), 'Das Zeugnis des Schülers kann bearbeitet werden.')),
                                'Name' => $tblPerson->getLastFirstName()
                                    . ($tblGroup ? new Small(new Muted(' (' . $tblDivisionItem->getDisplayName() . ')')) : '')
                            );
                            $courseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                            $studentTable[$tblPerson->getId()]['Course'] = $courseName ? $courseName
                                : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation()
                                    . ' Kein Bildungsgang hinterlegt');

                            $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareItem, $tblPerson);
                            if ($tblPrepareStudent) {
                                if ($tabIdentifier == 'K1' || $tabIdentifier == 'K2' || $tabIdentifier == 'K3' || $tabIdentifier == 'K4'
                                ) {
                                    // Schriftliche Komplexprüfung
                                    $ranking = substr($tabIdentifier, 1, 1);
                                    $this->setPrepareComplexExamContent($studentTable, $tblPerson, $tblPrepareStudent,
                                        TblPrepareComplexExam::IDENTIFIER_WRITTEN, $ranking);
                                } elseif ($tabIdentifier == 'P') {
                                    // Praktische Komplexprüfung
                                    $this->setPrepareComplexExamContent($studentTable, $tblPerson, $tblPrepareStudent,
                                        TblPrepareComplexExam::IDENTIFIER_PRAXIS, 1);
                                } else {
                                    // Sonstige Informationen
                                    $page = null;
                                    if (strlen($tabIdentifier) == 2) {
                                        $page = intval(substr($tabIdentifier, 1, 1));
                                    }

                                    $this->getTemplateInformation(
                                        $tblPrepare, $tblPerson, $studentTable, $columnTable, $Data, $CertificateList,
                                        $page == 1 ? null : $page, $informationPageList, $GroupId
                                    );
                                }
                            }

                            // leere Elemente auffühlen (sonst steht die Spaltennummer drin)
                            foreach ($columnTable as $columnKey => $columnName) {
                                foreach ($studentTable as $personId => $value) {
                                    if (!isset($studentTable[$personId][$columnKey])) {
                                        $studentTable[$personId][$columnKey] = '';
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $tableData = new TableData($studentTable, null, $columnTable,
                array(
                    "columnDefs" => array(
                        array(
                            "width" => "18px",
                            "targets" => 0
                        ),
                        array(
                            "width" => "200px",
                            "targets" => 1
                        ),
                    ),
                    'order' => array(
                        array('0', 'asc'),
                    ),
                    "paging" => false, // Deaktivieren Blättern
                    "iDisplayLength" => -1,    // Alle Einträge zeigen
                    "searching" => false, // Deaktivieren Suchen
                    "info" => false,  // Deaktivieren Such-Info
                    "sort" => false,
                    "responsive" => false
                ),
                true
            );

            $form = new Form(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(
                            $tableData
                        ),
                        new FormColumn(new HiddenField('Data[IsSubmit]'))
                    )),
                ))
                , new Primary('Speichern', new Save())
            );

            $nextTab = $CurrentTab + 1;
            if (!isset($tabs[$nextTab])) {
                $nextTab = null;
            }

            if ($tabIdentifier == 'K1' || $tabIdentifier == 'K2' || $tabIdentifier == 'K3' || $tabIdentifier == 'K4'
                || $tabIdentifier == 'P'
            ) {
                // Komplexprüfungen
                $service = Prepare::useService()->updatePrepareComplexExamList($form, $tblPrepare,
                    $tblGroup ? $tblGroup : null, $Data, $nextTab);
            } else {
                // Sonstige Informationen
                $hasAdditionalRemarkFhr = $additionalRemarkFhrTab && $tabIdentifier == ('I' . $additionalRemarkFhrTab);
                $service = Prepare::useService()->updateTechnicalDiplomaPrepareInformationList($form, $tblPrepare,
                    $tblGroup ? $tblGroup : null, $Data, $CertificateList, $nextTab, $hasAdditionalRemarkFhr);
            }

            $Stage->setContent(
                ApiPrepare::receiverModal()
                .new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnis',
                                    array(
                                        $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate()))
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    $tblGroup ? 'Gruppe' : 'Klasse',
                                    $description,
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn($buttonList),
                        )),
                    )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
//                                    !$tblTestList
//                                        ? new Warning('Die aktuelle Klasse ist nicht in dem ausgewählten Stichttagsnotenauftrag enthalten.'
//                                        , new Exclamation())
//                                        : null,
                                    $service
                                ))
                            ))
                        ))
                ))
            );

            return $Stage;
        }

        $Stage = new Stage('Zeugnisvorbereitung', 'Einstellungen');

        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare', new ChevronLeft()
        ));

        return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblGroup|null $tblGroup
     * @param null $currentTab
     * @param array $tabs
     *
     * @return Standard[]
     */
    private function createExamsTechnicalButtonList(
        TblPrepareCertificate $tblPrepare,
        TblGroup $tblGroup = null,
        $currentTab = null,
        $tabs = array()
    ) {
        $buttonList = array();
        foreach ($tabs as $key => $value) {
            if ($currentTab == $key) {
                $icon = new Edit();
                $name = new Info(new Bold($value['TabName']));
            } else {
                $icon = null;
                $name = $value['TabName'];
            }

            $buttonList[] = new Standard($name,
                '/Education/Certificate/Prepare/Prepare/Diploma/Technical/Setting', $icon, array(
                    'PrepareId' => $tblPrepare->getId(),
                    'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                    'CurrentTab' => $key
                )
            );
        }

        return $buttonList;
    }

    /**
     * @param TblType|null $tblType
     * @param int $countInformationPages
     *
     * @return array
     */
    public function getTabsByType(TblType $tblType = null, $countInformationPages = 1)
    {
        $tabs = array();
        $count = 1;
        if ($tblType->getName() == 'Fachschule') {
            $tabs = array(
                $count++ => array(
                    'Identifier' => 'K1',
                    'TabName' => 'K1',
                    'StageName' => 'Schriftliche Komplexprüfung 1',
                ),
                $count++ => array(
                    'Identifier' => 'K2',
                    'TabName' => 'K2',
                    'StageName' => 'Schriftliche Komplexprüfung 2',
                ),
                $count++ => array(
                    'Identifier' => 'K3',
                    'TabName' => 'K3',
                    'StageName' => 'Schriftliche Komplexprüfung 3',
                ),
                $count++ => array(
                    'Identifier' => 'K4',
                    'TabName' => 'K4',
                    'StageName' => 'Schriftliche Komplexprüfung 4',
                ),
                $count++ => array(
                    'Identifier' => 'P',
                    'TabName' => 'P',
                    'StageName' => 'Praktische Komplexprüfung',
                )
            );
        }

        // Sonstige Informationen
        $informationTabName = $tblType->getName() == 'Fachschule' ?  'Sonstige Info' : 'Sonstige Informationen';
        for ($i = 1; $i <= $countInformationPages; $i++) {
            $tabs[$count++] = array(
                'Identifier' => 'I' . (string) $i,
                'TabName' => $informationTabName . ($i > 1 ? ' (Seite ' . $i . ')' : ''),
                'StageName' => 'Sonstige Informationen' . ($i > 1 ? ' (Seite ' . $i . ')' : ''),
            );
        }

        return $tabs;
    }

    /**
     * @param $studentTable
     * @param TblPerson $tblPerson
     * @param TblPrepareStudent $tblPrepareStudent
     * @param $identifier
     * @param $ranking
     */
    private function setPrepareComplexExamContent(
        &$studentTable,
        TblPerson $tblPerson,
        TblPrepareStudent $tblPrepareStudent,
        $identifier,
        $ranking
    ) {
        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && ($tblCertificate->getCertificate() == 'FsAbs' || $tblCertificate->getCertificate() == 'FsAbsFhr')
        ) {
            /*
             * Post setzen
             */
            if (($tblPrepareComplexExam = Prepare::useService()->getPrepareComplexExamBy($tblPrepareStudent, $identifier, $ranking))) {
                $global = $this->getGlobal();
                if (($tblFirstSubject = $tblPrepareComplexExam->getServiceTblFirstSubject())) {
                    $global->POST['Data'][$tblPrepareStudent->getId()][$identifier . '_' . $ranking]['S1'] = $tblFirstSubject->getId();
                }
                if (($tblSecondSubject = $tblPrepareComplexExam->getServiceTblSecondSubject())) {
                    $global->POST['Data'][$tblPrepareStudent->getId()][$identifier . '_' . $ranking]['S2'] = $tblSecondSubject->getId();
                }
                $grade = $tblPrepareComplexExam->getGrade();
                if (($tblGradeText = Gradebook::useService()->getGradeTextByName($grade))) {
                    $global->POST['Data'][$tblPrepareStudent->getId()][$identifier . '_' . $ranking]['GradeText'] = $tblGradeText->getId();
                } else {
                    $global->POST['Data'][$tblPrepareStudent->getId()][$identifier . '_' . $ranking]['Grade'] = $grade;
                }

                $global->savePost();
            }

            $tblTechnicalCourse = Student::useService()->getTechnicalCourseByPerson($tblPerson);
            $this->setSubjectListByTechnicalCourse($tblCertificate, $tblTechnicalCourse ? $tblTechnicalCourse : null);

            $preName = 'Data[' . $tblPrepareStudent->getId() . '][' . $identifier . '_' . $ranking . ']';

            $firstSubjectSelectBox = new SelectBox($preName . '[S1]', '',
                array('{{ TechnicalAcronymForCertificateFromName }}' => $this->subjectList));
            $secondSubjectSelectBox = new SelectBox($preName . '[S2]', '',
                array('{{ TechnicalAcronymForCertificateFromName }}' => $this->subjectList));
            $gradeInput = new SelectCompleter($preName . '[Grade]', '', '', $this->selectListGrades);
            $gradeTextSelectBox = new SelectBox($preName . '[GradeText]', '',
                array(TblGradeText::ATTR_NAME => $this->selectListGradeTexts));

            if ($tblPrepareStudent->isApproved()) {
                $firstSubjectSelectBox->setDisabled();
                $secondSubjectSelectBox->setDisabled();
                $gradeInput->setDisabled();
                $gradeTextSelectBox->setDisabled();
            }

            $studentTable[$tblPerson->getId()]['S1'] = $firstSubjectSelectBox;
            $studentTable[$tblPerson->getId()]['S2'] = $secondSubjectSelectBox;
            $studentTable[$tblPerson->getId()]['Grade'] = $gradeInput;
            $studentTable[$tblPerson->getId()]['GradeText'] = $gradeTextSelectBox;
        }
    }
}