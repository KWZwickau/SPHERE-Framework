<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.03.2018
 * Time: 10:06
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Abitur;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Student;
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

/**
 * Class BlockI
 *
 * @package SPHERE\Application\Education\Certificate\Prepare\Abitur
 */
class BlockI extends AbstractBlock
{
    private int $View;

    /**
     * @var TblSubject|null
     */
    private ?TblSubject $tblReligionSubject = null;

    private int $count = 0;

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
            array('width' => '15%', 'targets' => array(2,3,4,5)),
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
                    Prepare::useService()->copyAbiturPreliminaryGradesFromCertificates(
                        $tblPrepareStudent,
                        $tblPrepareAdditionalGradeType,
                        $this->tblPrepareCertificate
                    );

                    $this->columnDefinition[$midTerm] = $this->columnDefinition[$midTerm] . new Warning('*');//'<sup>*</sup>';
                }
                // Stichtagsnotenauftrag 12-2
                elseif ($level == 12 && $term == 2) {
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
                        new Panel(
                            'Sonstiges Aufgabenfeld',
                            $this->getOtherTable(),
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

    private function setReligion()
    {
        // Religion aus der Schülerakte
        if (($tblStudent = $this->tblPerson->getStudent())
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
        if ($this->tblPerson) {
            $prepareStudentList = Prepare::useService()->getPrepareStudentListFromMidTermCertificatesByPerson($this->tblPerson);
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

        if ($tblSubject) {
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
                    $tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeBy(
                        $this->tblPrepareCertificate,
                        $this->tblPerson,
                        $tblSubject,
                        $tblPrepareAdditionalGradeType
                    );

                    // Es kann sein das der Schüler das Fach in ein vorherigen Schuljahr hatte
                    if (!$hasSubject && $tblPrepareAdditionalGrade) {
                        $hasSubject = true;
                        $course = 'GK';
                    }

                    $tabIndex = $level * 1000 + $term * 100 + $this->count++;
                    if ($hasSubject && $this->View == BlockIView::EDIT_GRADES) {
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
                    } elseif ($hasSubject && $this->View == BlockIView::CHOOSE_COURSES) {
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
                    } elseif ($hasSubject && $this->View == BlockIView::PREVIEW && $tblPrepareAdditionalGradeType) {
                        if ($tblPrepareAdditionalGrade) {
                            $isSelected = $tblPrepareAdditionalGrade->isSelected();
                            $value = str_pad($tblPrepareAdditionalGrade->getGrade(),2, 0, STR_PAD_LEFT);
                            $grades[$midTerm] = ($isSelected ? '' : '(') . $value . ($isSelected ? '' : ')');
                        }
                    }
                }
            }
            $global->savePost();

            $array[] = array(
                'Subject' => $subjectName,
                'Course' => $course,
                '11-1' => $grades['11-1'] ?? '',
                '11-2' => $grades['11-2'] ?? '',
                '12-1' => $grades['12-1'] ?? '',
                '12-2' => $grades['12-2'] ?? '',
            );
        } /** @noinspection PhpStatementHasEmptyBodyInspection */ else {
            // nicht vorhandene Fächer sollen erstmal nicht angezeigt werden
            // $subjectName = new Muted($subjectName);
//            $array[] = array(
//                'Subject' => $subjectName,
//                'Course' => $course,
//                '11-1' => isset($grades['11-1']) ? $grades['11-1'] : '',
//                '11-2' => isset($grades['11-2']) ? $grades['11-2'] : '',
//                '12-1' => isset($grades['12-1']) ? $grades['12-1'] : '',
//                '12-2' => isset($grades['12-2']) ? $grades['12-2'] : '',
//            );
        }

        return $array;
    }

    /**
     * Sprachlich-literarisch-künstlerisches Aufgabenfeld
     *
     * @return TableData
     */
    private function getLinguisticTable(): TableData
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
    private function getSocialTable(): TableData
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
    private function getScientificTable(): TableData
    {
        $dataList = array();
        $dataList = $this->setSubjectRow($dataList, 'Mathematik');
        $dataList = $this->setSubjectRow($dataList, 'Biologie');
        $dataList = $this->setSubjectRow($dataList, 'Chemie');
        $dataList = $this->setSubjectRow($dataList, 'Physik');

        $scientificTable = new TableData(
            $dataList,
            null,
            $this->columnDefinition,
            $this->interactive
        );
        $scientificTable->setHash('Abitur - Overview - Show - Mathematisch-naturwissenschaftlich-technisches Aufgabenfeld');

        return $scientificTable;
    }

    private function getOtherTable(): TableData
    {
        // Extra Fach aus den Einstellungen der Fächer bei den Zeugnisvorlagen
        if (($tblCertificate = Generator::useService()->getCertificateByCertificateClassName('GymAbitur'))) {
            $tblCertificateSubject1 = Generator::useService()->getCertificateSubjectByIndex($tblCertificate, 1, 1);
            $tblCertificateSubject2 = Generator::useService()->getCertificateSubjectByIndex($tblCertificate, 2, 1);
        } else {
            $tblCertificateSubject1 = false;
            $tblCertificateSubject2 = false;
        }

        $dataList = array();
        $dataList = $this->setSubjectRow($dataList, $this->tblReligionSubject ? $this->tblReligionSubject->getName() : 'Ev./Kath. Religion/Ethik');
        $dataList = $this->setSubjectRow($dataList, 'Sport');
        if (($tblExtraSubject = Subject::useService()->getSubjectByAcronym('DSW'))) {
            $dataList = $this->setSubjectRow($dataList, $tblExtraSubject->getName());
        }
        $dataList = $this->setSubjectRow($dataList, 'Astronomie');
        $dataList = $this->setSubjectRow($dataList, 'Informatik');
        $dataList = $this->setSubjectRow($dataList, 'Philosophie');
        if ($tblCertificateSubject1) {
            $dataList = $this->setSubjectRow($dataList, $tblCertificateSubject1->getServiceTblSubject()
                ? $tblCertificateSubject1->getServiceTblSubject()->getName() : '&nbsp;');
        }
        if ($tblCertificateSubject2) {
            $dataList = $this->setSubjectRow($dataList, $tblCertificateSubject2->getServiceTblSubject()
                ? $tblCertificateSubject2->getServiceTblSubject()->getName() : '&nbsp;');
        }

        $otherTable = new TableData(
            $dataList,
            null,
            $this->columnDefinition,
            $this->interactive
        );
        $otherTable->setHash('Abitur - Overview - Show - Sonstiges Aufgabenfeld');

        return $otherTable;
    }
}