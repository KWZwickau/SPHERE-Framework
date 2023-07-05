<?php

namespace SPHERE\Application\Education\Certificate\Prepare\BGyAbitur;

use SPHERE\Application\Api\Education\Certificate\Generator\Repository\BGymStyle;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
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
use SPHERE\System\Extension\Extension;

class LeavePoints extends Extension
{
    /**
     * @var TblLeaveStudent|null
     */
    protected ?TblLeaveStudent $tblLeaveStudent = null;
    /**
     * @var BlockIView
     */
    private $View;

    /**
     * @var array|false
     */
    protected $AdvancedCourses = false;

    /**
     * @var array|false
     */
    protected $BasicCourses = false;

    protected array $tblSubjectList = array();

    private int $count = 0;

    /**
     * @var TblPrepareStudent[]
     */
    private ?array $tblPrepareStudentList = null;

    /**
     * @var array
     */
    protected array $pointsList = array();

    protected function setPointList()
    {

        $list[-1] = '';
        for ($i = 0; $i < 16; $i++) {
            $list[$i] = (string)$i;
        }

        $this->pointsList = $list;
    }

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
        'FinalGrade' => 'Note²'
    );

    /**
     * LeavePoints constructor.
     *
     * @param TblLeaveStudent $tblLeaveStudent
     * @param $view
     */
    public function __construct(TblLeaveStudent $tblLeaveStudent, $view = BlockIView::PREVIEW)
    {
        $this->tblLeaveStudent = $tblLeaveStudent;
        $this->View = $view;

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
                    Prepare::useService()->copyAbiturLeaveGradesFromCertificates(
                        $tblPrepareStudent,
                        $tblPrepareAdditionalGradeType,
                        $this->tblLeaveStudent
                    );

                    $this->columnDefinition[$midTerm] = $this->columnDefinition[$midTerm] . new Warning('*');//'<sup>*</sup>';
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
                            // todo Wahlbereich
                            'Wahlbereich',
                            $this->getChosenSubjectsTable(),
                            Panel::PANEL_TYPE_PRIMARY
                        ),
                    ))
                ))
            ))
        ));

        if ($this->View != BlockIView::PREVIEW && ($this->tblLeaveStudent && !$this->tblLeaveStudent->isApproved())) {
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

    /**
     * @param $array
     * @param TblSubject $tblSubject
     *
     * @return mixed
     */
    private function setSubjectRow($array, TblSubject $tblSubject)
    {
        $course = '';
        $grades = array();
        $averageList = array();

        if (($tblLeaveStudent = $this->tblLeaveStudent)) {
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
                    $tblLeaveAdditionalGrade = Prepare::useService()->getLeaveAdditionalGradeBy(
                        $tblLeaveStudent,
                        $tblSubject,
                        $tblPrepareAdditionalGradeType,
                        true
                    );

                    $tabIndex = $level * 1000 + $term * 100 + $this->count++;
                    if ($this->View == BlockIView::EDIT_GRADES) {
                        if ($tblLeaveAdditionalGrade) {
                            $global->POST['Data'][$midTerm][$tblSubject->getId()] = $tblLeaveAdditionalGrade->getGrade();
                        }

                        if ($tblLeaveAdditionalGrade && $tblLeaveAdditionalGrade->isLocked()) {
                            $grades[$midTerm] = $tblLeaveAdditionalGrade->getGrade();
                        } else {
                            $selectBox = new SelectCompleter('Data['. $midTerm . '][' . $tblSubject->getId() . ']', '', '', $this->pointsList);
                            $selectBox->setTabIndex($tabIndex);
                            if ($tblLeaveStudent->isApproved()) {
                                $selectBox->setDisabled();
                            }
                            $grades[$midTerm] = $selectBox;
                        }
                    } elseif ($this->View == BlockIView::PREVIEW && $tblPrepareAdditionalGradeType) {
                        if ($tblLeaveAdditionalGrade
                            && $tblLeaveAdditionalGrade->getGrade() !== null
                            && $tblLeaveAdditionalGrade->getGrade() !== ''
                        ) {
                            $value = str_pad($tblLeaveAdditionalGrade->getGrade(),2, 0, STR_PAD_LEFT);
                            $grades[$midTerm] = $value;
                        }
                    }

                    if ($tblLeaveAdditionalGrade && $tblLeaveAdditionalGrade->getGrade() !== null && $tblLeaveAdditionalGrade->getGrade() !== '') {
                        $averageList[] = $tblLeaveAdditionalGrade->getGrade();
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

    private function setPrepareStudentList()
    {
        $prepareStudentList = array();
        // Zensuren von Kurshalbjahreszeugnissen
        if (($tblLeaveStudent = $this->tblLeaveStudent)
            && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
        ) {
            $prepareStudentList = Prepare::useService()->getPrepareStudentListFromMidTermCertificatesByPerson($tblPerson);
        }

        $this->tblPrepareStudentList = $prepareStudentList;
    }

    private function setCourses()
    {
        if (($tblLeaveStudent = $this->tblLeaveStudent)
            && (($tblPerson = $tblLeaveStudent->getServiceTblPerson()))
        ) {
            list($this->AdvancedCourses, $this->BasicCourses) = DivisionCourse::useService()->getCoursesForStudent($tblPerson);
        }
    }
}