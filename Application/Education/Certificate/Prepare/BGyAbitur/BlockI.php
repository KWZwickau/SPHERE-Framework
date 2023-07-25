<?php

namespace SPHERE\Application\Education\Certificate\Prepare\BGyAbitur;

use SPHERE\Application\Api\Education\Certificate\Generator\Repository\BGymStyle;
use SPHERE\Application\Education\Certificate\Prepare\Abitur\AbstractBlock;
use SPHERE\Application\Education\Certificate\Prepare\Abitur\BlockIView;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Warning;

class BlockI extends AbstractBlock
{
    private int $View;

    private int $count = 0;

    protected array $tblSubjectList = array();

    /**
     * @var TblPrepareStudent[]|array
     */
    private ?array $tblPrepareStudentList = null;

    private array $interactive = array(
        "paging" => false, // Deaktivieren Blättern
        "iDisplayLength" => -1,    // Alle Einträge zeigen
        "searching" => false, // Deaktivieren Suchen
        "info" => false,  // Deaktivieren Such-Info
        "sort" => false,
        "responsive" => false,
        'columnDefs' => array(
            array('width' => '35%', 'targets' => 0),
            array('width' => '5%', 'targets' => 1),
            array('width' => '10%', 'targets' => array(2,3,4,5,6)),
        ),
        'fixedHeader' => false
    );

    private array $columnDefinition = array(
        'Subject' => 'Fach',
        'Course' => 'Kurs',
        '12-1' => '12/1',
        '12-2' => '12/2',
        '13-1' => '13/1',
        '13-2' => '13/2',
        'FinalGrade' => 'Note³'
    );

    /**
     * BlockI constructor.
     *
     * @param TblPerson $tblPerson
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param $view
     */
    public function __construct(TblPerson $tblPerson, TblPrepareCertificate $tblPrepareCertificate, $view)
    {
        $this->tblPerson = $tblPerson;
        $this->tblPrepareCertificate = $tblPrepareCertificate;
        $this->View = $view;

        $this->tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareCertificate, $tblPerson);

        $this->setCourses();
        $this->setPrepareStudentList();
        $this->setPointList();

        // kopieren der Vornoten aus vorhandenen Kurshalbjahreszeugnissen
        for ($level = 12; $level < 14; $level++) {
            for ($term = 1; $term < 3; $term++) {
                $midTerm = $level . '-' . $term;
                if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier($midTerm))
                    && isset($this->tblPrepareStudentList[$midTerm])
                ) {
                    /** @var TblPrepareStudent $tblPrepareStudent */
                    $tblPrepareStudent = $this->tblPrepareStudentList[$midTerm];
                    Prepare::useService()->copyAbiturPreliminaryGradesFromCertificates(
                        $tblPrepareStudent,
                        $tblPrepareAdditionalGradeType,
                        $this->tblPrepareCertificate
                    );

                    $this->columnDefinition[$midTerm] = $this->columnDefinition[$midTerm] . new Warning('*');//'<sup>*</sup>';
                }
                // Stichtagsnotenauftrag 13-2
                elseif ($level == 13 && $term == 2) {
                    if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier($midTerm))
                        && ($tblDivisionCourse = $tblPrepareCertificate->getServiceTblDivision())
                        && ($tblYear = $tblDivisionCourse->getServiceTblYear())
                        && ($tblTaskList = Grade::useService()->getTaskListByStudentAndYear($tblPerson, $tblYear))
                    ) {
                        foreach ($tblTaskList as $tblTask) {
                            $month = $tblTask->getDate() ? intval($tblTask->getDate()->format('m')) : 0;
                            if ($month > 3 && $month < 9) {
                                Prepare::useService()->copyAbiturPreliminaryGradesFromAppointedDateTask(
                                    $tblPerson,
                                    $tblPrepareCertificate,
                                    $tblPrepareAdditionalGradeType,
                                    $tblTask
                                );

                                $this->columnDefinition[$midTerm] = $this->columnDefinition[$midTerm] . new Warning('**');
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        $form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel(
                            'Sprachlich-literarisch-künstlerisches Aufgabenfeld',
                            $this->getWorkFieldTable('Sprachlich-literarisch-künstlerisches Aufgabenfeld'),
                            Panel::PANEL_TYPE_PRIMARY
                        ),
                        new Panel(
                            'Gesellschaftswissenschaftliches Aufgabenfeld',
                            $this->getWorkFieldTable('Gesellschaftswissenschaftliches Aufgabenfeld'),
                            Panel::PANEL_TYPE_PRIMARY
                        ),
                        new Panel(
                            'Mathematisch-naturwissenschaftlich-technisches Aufgabenfeld',
                            $this->getWorkFieldTable('Mathematisch-naturwissenschaftlich-technisches Aufgabenfeld'),
                            Panel::PANEL_TYPE_PRIMARY
                        ),
                        new Panel(
                            'Sonstiges Aufgabenfeld',
                            $this->getWorkFieldTable(''),
                            Panel::PANEL_TYPE_PRIMARY
                        ),
                        new Panel(
                            'Wahlbereich',
                            $this->getChosenSubjectsTable(),
                            Panel::PANEL_TYPE_PRIMARY
                        ),
                    ))
                ))
            ))
        ));

        if ($this->View != BlockIView::PREVIEW && ($this->tblPrepareStudent && !$this->tblPrepareStudent->isApproved())) {
            $form->appendFormButton(new Primary('Speichern', new Save()));
        }

        return $form;
    }

    /**
     * @param string $workField
     *
     * @return TableData
     */
    private function getWorkFieldTable(string $workField): TableData
    {
        $dataList = array();
        if (($tblSubjectList = BGymStyle::getSubjectListByWorkField($workField))) {
            foreach ($tblSubjectList as $tblSubject)
            {
                if (isset($this->AdvancedCourses[$tblSubject->getId()]) || isset($this->BasicCourses[$tblSubject->getId()])) {
                    $dataList = $this->setSubjectRow($dataList, $tblSubject);
                    $this->tblSubjectList[$tblSubject->getId()] = $tblSubject;
                }
            }
        }

        $table = new TableData(
            $dataList,
            null,
            $this->columnDefinition,
            $this->interactive
        );
        $table->setHash('Berufliches Abitur - Overview - Show - ' . $workField);

        return $table;
    }

    /**
     * @return TableData
     */
    private function getChosenSubjectsTable(): TableData
    {
        $dataList = array();
        $chosenSubjectList = array();
        foreach ($this->AdvancedCourses as $advancedCourse) {
            if (!isset($this->tblSubjectList[$advancedCourse->getId()])) {
                $chosenSubjectList[] = $advancedCourse;
            }
        }
        foreach ($this->BasicCourses as $basicCourse) {
            if (!isset($this->tblSubjectList[$basicCourse->getId()])) {
                $chosenSubjectList[] = $basicCourse;
            }
        }

        if ($chosenSubjectList) {
            foreach ($chosenSubjectList as $tblSubject)
            {
                $dataList = $this->setSubjectRow($dataList, $tblSubject);
            }
        }

        $table = new TableData(
            $dataList,
            null,
            $this->columnDefinition,
            $this->interactive
        );
        $table->setHash('Berufliches Abitur - Overview - Show - Wahlbereich');

        return $table;
    }

    private function setPrepareStudentList()
    {
        $prepareStudentList = array();
        // Zensuren von Kurshalbjahreszeugnissen
        if ($this->tblPerson) {
            $prepareStudentList = Prepare::useService()->getPrepareStudentListFromMidTermCertificatesByPerson($this->tblPerson);
        }

        $this->tblPrepareStudentList = $prepareStudentList;
    }

    private function setSubjectRow($array, TblSubject $tblSubject)
    {
        $course = '';
        $grades = array();
        $averageList = array();

        if (($tblPrepare = $this->tblPrepareCertificate)) {
            $subjectName = $tblSubject->getName();
            if (isset($this->AdvancedCourses[$tblSubject->getId()])) {
                $subjectName = new Bold($subjectName);
                $course = new Bold('LK');
            }
            if (isset($this->BasicCourses[$tblSubject->getId()])) {
                $course = 'GK';
            }

            $global = $this->getGlobal();
            for ($level = 12; $level < 14; $level++){
                for ($term = 1; $term < 3; $term++) {
                    $midTerm = $level . '-' . $term;
                    $tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier($midTerm);
                    $tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeBy(
                        $tblPrepare,
                        $this->tblPerson,
                        $tblSubject,
                        $tblPrepareAdditionalGradeType
                    );

                    $tabIndex = $level * 1000 + $term * 100 + $this->count++;
                    if ($this->View == BlockIView::EDIT_GRADES) {
                        if ($tblPrepareAdditionalGrade) {
                            $global->POST['Data'][$midTerm][$tblSubject->getId()] = $tblPrepareAdditionalGrade->getGrade();
                        }

                        if ($tblPrepareAdditionalGrade && $tblPrepareAdditionalGrade->isLocked()) {
                            $grades[$midTerm] = $tblPrepareAdditionalGrade->getGrade();
                        } else {
                            $selectBox = new SelectCompleter('Data['. $midTerm . '][' . $tblSubject->getId() . ']', '', '', $this->pointsList);
                            $selectBox->setTabIndex($tabIndex);
                            if ($this->tblPrepareStudent && $this->tblPrepareStudent->isApproved()) {
                                $selectBox->setDisabled();
                            }
                            $grades[$midTerm] = $selectBox;
                        }
                    } elseif ($this->View == BlockIView::CHOOSE_COURSES) {
                        if ($tblPrepareAdditionalGrade) {
                            $global->POST['Data'][$midTerm][$tblSubject->getId()] = $tblPrepareAdditionalGrade->isSelected() ? 1 : 0;

                            $label = $tblPrepareAdditionalGrade->getGrade();
                            $checkBox = new CheckBox('Data['. $midTerm . '][' . $tblSubject->getId() . ']', $label, 1);
                            $checkBox->setTabIndex($tabIndex);
                            if ($this->tblPrepareStudent && $this->tblPrepareStudent->isApproved()) {
                                $checkBox->setDisabled();
                            }
                            $grades[$midTerm] = $checkBox;
                        } else {
                            $grades[$midTerm] = '';
                        }
                    } elseif ($this->View == BlockIView::PREVIEW && $tblPrepareAdditionalGradeType) {
                        if ($tblPrepareAdditionalGrade) {
                            $isSelected = $tblPrepareAdditionalGrade->isSelected();
                            $value = str_pad($tblPrepareAdditionalGrade->getGrade(),2, 0, STR_PAD_LEFT);
                            $grades[$midTerm] = ($isSelected ? '' : '(') . $value . ($isSelected ? '' : ')');
                        }
                    }

                    if ($tblPrepareAdditionalGrade && $tblPrepareAdditionalGrade->getGrade() !== null && $tblPrepareAdditionalGrade->getGrade() !== '') {
                        $averageList[] = $tblPrepareAdditionalGrade->getGrade();
                    }
                }
            }
            $global->savePost();

            $array[] = array(
                'Subject' => $subjectName,
                'Course' => $course,
                '12-1' => $grades['12-1'] ?? '',
                '12-2' => $grades['12-2'] ?? '',
                '13-1' => $grades['13-1'] ?? '',
                '13-2' => $grades['13-2'] ?? '',
                'FinalGrade' => BGymStyle::getAverageTextByGradeList($averageList)
            );
        }

        return $array;
    }
}