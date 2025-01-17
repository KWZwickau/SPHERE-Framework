<?php

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Api\Education\Prepare\ApiPrepare;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareComplexExam;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeText;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalCourse;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;

class FrontendDiplomaTechnicalSchool extends FrontendDiploma
{
    protected array $subjectList = array();
    private array $selectListGrades = array();
    private array $selectListGradeTexts = array();

    /**
     * @param null $PrepareId
     * @param null $CurrentTab
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendPrepareDiplomaTechnicalSetting(
        $PrepareId = null,
        $CurrentTab = null,
        $Data = null
    ) {
        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
        ) {
            // Grades
            $this->selectListGrades = array();
            $this->selectListGrades[-1] = '';
            for ($i = 1; $i < 6; $i++) {
                $this->selectListGrades[$i] = (string)($i);
            }
            $this->selectListGrades[6] = 6;

            // GradeTexts
            $this->selectListGradeTexts = array();
            if (($tblGradeTextList = Grade::useService()->getGradeTextAll())) {
                $this->selectListGradeTexts = $tblGradeTextList;
            }

            $tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents();
            if ($CurrentTab == null) {
                $CurrentTab = 1;
            }

            $countInformationPages = 1;
            $additionalRemarkFhrTab = false;
            $useClassRegisterForAbsence = ($tblSetting = ConsumerSetting::useService()->getSetting('Education', 'ClassRegister', 'Absence', 'UseClassRegisterForAbsence'))
                && $tblSetting->getValue();
            // Aufteilung der sonstigen Informationen auf mehrere Seiten
            list($informationPageList) = Prepare::useService()->getCertificateInformationPages($tblPrepare, $useClassRegisterForAbsence);
            if (!empty($informationPageList)) {
                foreach ($informationPageList as $pageList) {
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

            $tabs = $this->getTabsByType($tblSchoolTypeList, $countInformationPages);

            $Stage = new Stage('Zeugnisvorbereitung', isset($tabs[$CurrentTab]) ? $tabs[$CurrentTab]['StageName'] . ' festlegen' : '');
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
                array(
                    'DivisionId' => $tblDivisionCourse->getId(),
                    'Route' => 'Diploma'
                )
            ));
            $buttonList = $this->createExamsTechnicalButtonList($tblPrepare, $CurrentTab, $tabs);
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

            if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
                foreach ($tblPersonList as $tblPerson) {
                    $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);

                    $studentTable[$tblPerson->getId()] = array(
                        'Number' => (count($studentTable) + 1) . ' '
                            . ($tblPrepareStudent && $tblPrepareStudent->isApproved()
                                ? new ToolTip(new Warning(new Ban()),
                                    'Das Zeugnis des Schülers wurde bereits freigegeben und kann nicht mehr bearbeitet werden.')
                                : new ToolTip(new Success(new Edit()), 'Das Zeugnis des Schülers kann bearbeitet werden.')),
                        'Name' => $tblPerson->getLastFirstNameWithCallNameUnderline()
                    );
                    $courseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                    $studentTable[$tblPerson->getId()]['Course'] = $courseName ?: new Warning(new Exclamation() . ' Kein Bildungsgang hinterlegt');

                    if ($tblPrepareStudent) {
                        if ($tabIdentifier == 'K1' || $tabIdentifier == 'K2' || $tabIdentifier == 'K3' || $tabIdentifier == 'K4') {
                            // Schriftliche Komplexprüfung
                            $ranking = substr($tabIdentifier, 1, 1);
                            $this->setPrepareComplexExamContent($studentTable, $tblPerson, $tblPrepareStudent, TblPrepareComplexExam::IDENTIFIER_WRITTEN, $ranking);
                        } elseif ($tabIdentifier == 'P') {
                            // Praktische Komplexprüfung
                            $this->setPrepareComplexExamContent($studentTable, $tblPerson, $tblPrepareStudent, TblPrepareComplexExam::IDENTIFIER_PRAXIS, 1);
                        } else {
                            // Sonstige Informationen
                            $page = null;
                            if (strlen($tabIdentifier) == 2) {
                                $page = intval(substr($tabIdentifier, 1, 1));
                            }

                            Prepare::useService()->getTemplateInformation(
                                $tblPrepare, $tblPerson, $studentTable, $columnTable, $Data, $CertificateList, $page == 1 ? null : $page, $informationPageList
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

            if ($tabIdentifier == 'K1' || $tabIdentifier == 'K2' || $tabIdentifier == 'K3' || $tabIdentifier == 'K4' || $tabIdentifier == 'P') {
                // Komplexprüfungen
                $service = Prepare::useService()->updatePrepareComplexExamList($form, $tblPrepare, $Data, $nextTab);
            } else {
                // Sonstige Informationen
                $hasAdditionalRemarkFhr = $additionalRemarkFhrTab && $tabIdentifier == ('I' . $additionalRemarkFhrTab);
                $service = Prepare::useService()->updateTechnicalDiplomaPrepareInformationList($form, $tblPrepare, $Data, $CertificateList, $nextTab, $hasAdditionalRemarkFhr);
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
                                    $tblDivisionCourse->getTypeName(),
                                    $tblDivisionCourse->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn($buttonList),
                        )),
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
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
     * @param $tblSchoolTypeList
     * @param int $countInformationPages
     *
     * @return array
     */
    public function getTabsByType($tblSchoolTypeList, int $countInformationPages = 1): array
    {
        $tabs = array();
        $count = 1;
        $informationTabName = 'Sonstige Informationen';
        if (($tblSchoolTypeFS = Type::useService()->getTypeByShortName('FS'))
            && isset($tblSchoolTypeList[$tblSchoolTypeFS->getId()])
        ) {
            $informationTabName = 'Sonstige Info';
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
        for ($i = 1; $i <= $countInformationPages; $i++) {
            $tabs[$count++] = array(
                'Identifier' => 'I' . $i,
                'TabName' => $informationTabName . ($i > 1 ? ' (Seite ' . $i . ')' : ''),
                'StageName' => 'Sonstige Informationen' . ($i > 1 ? ' (Seite ' . $i . ')' : ''),
            );
        }

        return $tabs;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param null $currentTab
     * @param array $tabs
     *
     * @return Standard[]
     */
    private function createExamsTechnicalButtonList(
        TblPrepareCertificate $tblPrepare,
        $currentTab = null,
        array $tabs = array()
    ): array {
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
                    'CurrentTab' => $key
                )
            );
        }

        return $buttonList;
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
                if (($tblGradeText = Grade::useService()->getGradeTextByName($grade))) {
                    $global->POST['Data'][$tblPrepareStudent->getId()][$identifier . '_' . $ranking]['GradeText'] = $tblGradeText->getId();
                } else {
                    $global->POST['Data'][$tblPrepareStudent->getId()][$identifier . '_' . $ranking]['Grade'] = $grade;
                }

                $global->savePost();
            }

            $tblTechnicalCourse = Student::useService()->getTechnicalCourseByPerson($tblPerson);
            $this->setSubjectListByTechnicalCourse($tblCertificate, $tblTechnicalCourse ?: null);

            $preName = 'Data[' . $tblPrepareStudent->getId() . '][' . $identifier . '_' . $ranking . ']';

            $firstSubjectSelectBox = new SelectBox($preName . '[S1]', '', array('{{ TechnicalAcronymForCertificateFromName }}' => $this->subjectList));
            $secondSubjectSelectBox = new SelectBox($preName . '[S2]', '', array('{{ TechnicalAcronymForCertificateFromName }}' => $this->subjectList));
            $gradeInput = new SelectCompleter($preName . '[Grade]', '', '', $this->selectListGrades);
            $gradeTextSelectBox = new SelectBox($preName . '[GradeText]', '', array(TblGradeText::ATTR_NAME => $this->selectListGradeTexts));

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

    /**
     * @param TblCertificate $tblCertificate
     * @param TblTechnicalCourse|null $tblTechnicalCourse
     */
    protected function setSubjectListByTechnicalCourse(TblCertificate $tblCertificate, TblTechnicalCourse $tblTechnicalCourse = null)
    {
        $this->subjectList = array();
        if (($tblCertificateSubjectList = Generator::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse))) {
            foreach ($tblCertificateSubjectList as $tblCertificateSubject) {
                if (($tblSubject = $tblCertificateSubject->getServiceTblSubject())
                    && $tblCertificateSubject->getRanking() > 4
                ) {
                    $this->subjectList[$tblSubject->getId()] = $tblSubject;
                }
            }
        }
    }
}