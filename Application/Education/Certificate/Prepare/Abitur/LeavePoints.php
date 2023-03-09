<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 22.05.2018
 * Time: 10:18
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Abitur;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Student;
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

/**
 * Class LeavePoints
 *
 * @package SPHERE\Application\Education\Certificate\Prepare\Abitur
 */
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

    /**
     * @var TblSubject|null
     */
    private ?TblSubject $tblReligionSubject = null;

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
            array('width' => '10%', 'targets' => array(2,3,4,5,6,7)),
        ),
        'fixedHeader' => false
    );

    private array $columnDefinition = array(
        'Subject' => 'Fach',
        'Course' => 'Kurs',
        '11-1' => '11/1',
        '11-2' => '11/2',
        '12-1' => '12/1',
        '12-2' => '12/2',
        'Average' => 'Durchschnitt¹',
        'FinalGrade' => 'Abgangsnote²'
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
        $this->setReligion();
        $this->setPrepareStudentList();
        $this->setPointList();

        // kopieren der Vornoten aus vorhandenen Kurshalbjahreszeugnissen
        for ($level = 11; $level < 13; $level++) {
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
    public function getForm()
    {

        $form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel(
                            'Sprachlich-literarisch-künstlerisches Aufgabenfeld',
                            $this->getLinguisticTable(),
                            Panel::PANEL_TYPE_PRIMARY
                        ),
                        new Panel(
                            'Gesellschaftswissenschaftliches Aufgabenfeld',
                            $this->getSocialTable(),
                            Panel::PANEL_TYPE_PRIMARY
                        ),
                        new Panel(
                            'Mathematisch-naturwissenschaftlich-technisches Aufgabenfeld',
                            $this->getScientificTable(),
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

    private function setReligion()
    {
        // Religion aus der Schülerakte
        if (($tblLeaveStudent = $this->tblLeaveStudent)
            && (($tblPerson = $tblLeaveStudent->getServiceTblPerson()))
            && ($tblStudent = $tblPerson->getStudent())
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'))
            && ($tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier('1'))
            && ($tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent,
                $tblStudentSubjectType, $tblStudentSubjectRanking))
        ) {
            $this->tblReligionSubject = $tblStudentSubject->getServiceTblSubject();
        }
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

    private function setSubjectRow($array, $subjectName)
    {

        $course = '';
        $grades = array();
        $tblSubject = Subject::useService()->getSubjectByName($subjectName);
        if (!$tblSubject && $subjectName == 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('GRW');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('G/R/W');
            }
        }

        if ($tblSubject && (($tblLeaveStudent = $this->tblLeaveStudent))) {
            $hasSubject = false;
            if (isset($this->AdvancedCourses[$tblSubject->getId()])) {
                $hasSubject = true;
                $subjectName = new Bold($subjectName);
                $course = new Bold('LK');
            }
            if (isset($this->BasicCourses[$tblSubject->getId()])) {
                $hasSubject = true;
                $subjectName = new Bold($subjectName);
                $course = 'GK';
            }

            $global = $this->getGlobal();
            for ($level = 11; $level < 13; $level++){
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
                    if ($hasSubject && $this->View == BlockIView::EDIT_GRADES) {
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
                    } elseif ($hasSubject && $this->View == BlockIView::PREVIEW && $tblPrepareAdditionalGradeType) {
                        if ($tblLeaveAdditionalGrade
                            && $tblLeaveAdditionalGrade->getGrade() !== null
                            && $tblLeaveAdditionalGrade->getGrade() !== ''
                        ) {
                            $value = str_pad($tblLeaveAdditionalGrade->getGrade(),2, 0, STR_PAD_LEFT);
                            $grades[$midTerm] = $value;
                        }
                    }
                }
            }
            $global->savePost();

            $points = Prepare::useService()->calcAbiturLeaveGradePointsBySubject($tblLeaveStudent, $tblSubject);

            $array[] = array(
                'Subject' => $subjectName,
                'Course' => $course,
                '11-1' => $grades['11-1'] ?? '',
                '11-2' => $grades['11-2'] ?? '',
                '12-1' => $grades['12-1'] ?? '',
                '12-2' => $grades['12-2'] ?? '',
                'Average' => $hasSubject ? $points : '',
                'FinalGrade' => $hasSubject ? Prepare::useService()->getAbiturLeaveGradeBySubject($points) : ''
            );
        }

        return $array;
    }

    /**
     * Sprachlich-literarisch-künstlerisches Aufgabenfeld
     *
     * @return TableData
     */
    private function getLinguisticTable()
    {
        $dataList = array();
        $dataList = $this->setSubjectRow($dataList, 'Deutsch');
        $dataList = $this->setSubjectRow($dataList, 'Sorbisch');
        $dataList = $this->setSubjectRow($dataList, 'Englisch');
        $dataList = $this->setSubjectRow($dataList, 'Französisch');
        $dataList = $this->setSubjectRow($dataList, 'Griechisch');
        $dataList = $this->setSubjectRow($dataList, 'Italienisch');
        $dataList = $this->setSubjectRow($dataList, 'Latein');
        $dataList = $this->setSubjectRow($dataList, 'Polnisch');
        $dataList = $this->setSubjectRow($dataList, 'Russisch');
        $dataList = $this->setSubjectRow($dataList, 'Spanisch');
        $dataList = $this->setSubjectRow($dataList, 'Tschechisch');
        $dataList = $this->setSubjectRow($dataList, '&nbsp;');
        $dataList = $this->setSubjectRow($dataList, 'Kunst');
        $dataList = $this->setSubjectRow($dataList, 'Musik');

        $linguisticTable = new TableData(
            $dataList,
            null,
            $this->columnDefinition,
            $this->interactive
        );
        $linguisticTable->setHash('Abitur - Overview - Show - Sprachlich-literarisch-künstlerisches Aufgabenfeld');

        return $linguisticTable;
    }

    /**
     * Gesellschaftswissenschaftliches Aufgabenfeld
     *
     * @return TableData
     */
    private function getSocialTable()
    {

        $dataList = array();
        $dataList = $this->setSubjectRow($dataList, 'Geschichte');
        $dataList = $this->setSubjectRow($dataList, 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft');
        $dataList = $this->setSubjectRow($dataList, 'Geographie');

        $socialTable = new TableData(
            $dataList,
            null,
            $this->columnDefinition,
            $this->interactive
        );
        $socialTable->setHash('Abitur - Overview - Show - Gesellschaftswissenschaftliches Aufgabenfeld');

        return $socialTable;
    }

    /**
     * Mathematisch-naturwissenschaftlich-technisches Aufgabenfeld
     *
     * @return TableData
     */
    private function getScientificTable()
    {

        // Extra Fach aus den Einstellungen der Fächer bei den Zeugnisvorlagen
        if (($tblCertificate = Generator::useService()->getCertificateByCertificateClassName('GymAbgSekII'))) {
            $tblCertificateSubject = Generator::useService()->getCertificateSubjectByIndex($tblCertificate, 1, 1);
        } else {
            $tblCertificateSubject = false;
        }

        $dataList = array();
        $dataList = $this->setSubjectRow($dataList, 'Mathematik');
        $dataList = $this->setSubjectRow($dataList, 'Biologie');
        $dataList = $this->setSubjectRow($dataList, 'Chemie');
        $dataList = $this->setSubjectRow($dataList, 'Physik');
        $dataList = $this->setSubjectRow($dataList, '&nbsp;');
        $dataList = $this->setSubjectRow($dataList, $this->tblReligionSubject ? $this->tblReligionSubject->getName() : 'Ev./Kath. Religion/Ethik');
        $dataList = $this->setSubjectRow($dataList, 'Sport');
        $dataList = $this->setSubjectRow($dataList, $tblCertificateSubject && $tblCertificateSubject->getServiceTblSubject()
            ? $tblCertificateSubject->getServiceTblSubject()->getName() : '&nbsp;');
        $dataList = $this->setSubjectRow($dataList, 'Astronomie');
        $dataList = $this->setSubjectRow($dataList, 'Informatik');
        $dataList = $this->setSubjectRow($dataList, 'Philosophie');

        $scientificTable = new TableData(
            $dataList,
            null,
            $this->columnDefinition,
            $this->interactive
        );
        $scientificTable->setHash('Abitur - Overview - Show - Mathematisch-naturwissenschaftlich-technisches Aufgabenfeld');

        return $scientificTable;
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