<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.03.2018
 * Time: 10:06
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Abitur;

use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;

/**
 * Class BlockI
 *
 * @package SPHERE\Application\Education\Certificate\Prepare\Abitur
 */
class BlockI
{
    /**
     * @var TblDivision|null
     */
    private $tblDivision = null;

    /**
     * @var TblPerson|null
     */
    private $tblPerson = null;

    /**
     * @var BlockIView
     */
    private $View = BlockIView::PREVIEW;

    /**
     * @var array|false
     */
    private $AdvancedCourses = false;

    /**
     * @var array|false
     */
    private $BasicCourses = false;

    /**
     * @var TblSubject|null
     */
    private $tblReligionSubject = null;

    /**
     * @var TblPrepareStudent[]|array
     */
    private $tblPrepareStudentList = null;

    private $interactive = array(
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

    private $columnDefinition = array(
        'Subject' => 'Fach',
        'Course' => 'Kurs',
        '11-1' => '11/1',
        '11-2' => '11/2',
        '12-1' => '12/1',
        '12-2' => '12/2',
    );

    public function __construct(TblDivision $tblDivision, TblPerson $tblPerson, $view)
    {
        $this->tblDivision = $tblDivision;
        $this->tblPerson = $tblPerson;
        $this->View = $view;

        $this->setCourses();
        $this->setReligion();
        $this->setPrepareStudentList();
    }

    private function setCourses()
    {
        $advancedCourses = array();
        $basicCourses = array();
        if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($this->tblDivision))) {
            foreach ($tblDivisionSubjectList as $tblDivisionSubjectItem) {
                if (($tblSubjectGroup = $tblDivisionSubjectItem->getTblSubjectGroup())) {

                    if (($tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject(
                        $tblDivisionSubjectItem))
                    ) {
                        foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                            if (($tblSubject = $tblDivisionSubjectItem->getServiceTblSubject())
                                && ($tblPersonStudent = $tblSubjectStudent->getServiceTblPerson())
                                && $this->tblPerson->getId() == $tblPersonStudent->getId()
                            ) {
                                if ($tblSubjectGroup->isAdvancedCourse()) {
                                    $advancedCourses[$tblSubject->getId()] = $tblSubject;
                                } else {
                                    $basicCourses[$tblSubject->getId()] = $tblSubject;
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->AdvancedCourses = $advancedCourses;
        $this->BasicCourses = $basicCourses;
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
        if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($this->tblPerson))) {
            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                if (($tblDivision = $tblDivisionStudent->getTblDivision())
                    && ($tblLevel = $tblDivision->getTblLevel())
                    && ($tblLevel->getName() == '11' || $tblLevel->getName() == '12')
                    && ($tblPrepareList = Prepare::useService()->getPrepareAllByDivision($tblDivision))
                ) {
                    foreach ($tblPrepareList as $tblPrepare) {
                        if ($tblPrepare->getServiceTblGenerateCertificate()
                            && ($tblCertificateType = $tblPrepare->getServiceTblGenerateCertificate()->getServiceTblCertificateType())
                            && ($tblCertificateType->getIdentifier() == 'MID_TERM_COURSE')
                            && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                                $this->tblPerson))
                            && $tblPrepareStudent->isApproved()
                            && $tblPrepareStudent->isPrinted()
                        ) {
                            $midTerm = '-1';
                            if (($tblAppointedDateTask = $tblPrepare->getServiceTblAppointedDateTask())
                                && ($tblDivisionItem = $tblPrepare->getServiceTblDivision())
                                && ($tblYear = $tblDivisionItem->getServiceTblYear())
                                && ($tblPeriodList = $tblYear->getTblPeriodAll())
                                && ($tblPeriod = $tblAppointedDateTask->getServiceTblPeriod())
                                && ($tblFirstPeriod = current($tblPeriodList))
                                && $tblPeriod->getId() != $tblFirstPeriod->getId()
                            ) {
                                $midTerm = '-2';
                            }

                            $prepareStudentList[$tblLevel->getName() . $midTerm] = $tblPrepareStudent;
                        }
                    }
                }
            }
        }

        $this->tblPrepareStudentList = $prepareStudentList;
    }

    /**
     * @param $array
     * @param $subjectName
     * @return array
     * @internal param $advancedCourses
     * @internal param $basicCourses
     * @internal param $prepareStudentList
     * @internal param $view
     */
    private function setSubjectRow($array, $subjectName)
    {

        $course = '';
        $grades = array();
        if (($tblSubject = Subject::useService()->getSubjectByName($subjectName))) {

            if (isset($this->AdvancedCourses[$tblSubject->getId()])) {
                $subjectName = new Bold($subjectName);
                $course = new Bold('LK');
            }
            if (isset($this->BasicCourses[$tblSubject->getId()])) {
                $subjectName = new Bold($subjectName);
                $course = 'GK';
            }

            // todo Stichtagsnotenauftrag 12-2
            // Zensuren von Zeugnissen
            for ($level = 11; $level < 13; $level++){
                for ($term = 1; $term < 3; $term++) {
                    $midTerm = $level . '-' . $term;
                    if (isset($this->tblPrepareStudentList[$midTerm])) {
                        /** @var TblPrepareStudent $tblPrepareStudent */
                        $tblPrepareStudent = $this->tblPrepareStudentList[$midTerm];
                        if (($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                            && ($tblDivision = $tblPrepare->getServiceTblDivision())
                            && ($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK'))
                            && ($tblPrepareGrade = Prepare::useService()->getPrepareGradeBySubject($tblPrepare, $tblPerson,
                                $tblDivision, $tblSubject, $tblTestType))
                        ) {
                            $grades[$midTerm] = $tblPrepareGrade->getGrade();
                        }
                    }
                }
            }
        } else {
            $subjectName = new Muted($subjectName);
        }

        $array[] = array(
            'Subject' => $subjectName,
            'Course' => $course,
            '11-1' => isset($grades['11-1']) ? $grades['11-1'] : '',
            '11-2' => isset($grades['11-2']) ? $grades['11-2'] : '',
            '12-1' => isset($grades['12-1']) ? $grades['12-1'] : '',
            '12-2' => isset($grades['12-2']) ? $grades['12-2'] : '',
        );

        return $array;
    }

    /**
     * Sprachlich-literarisch-künstlerisches Aufgabenfeld
     *
     * @return TableData
     */
    public function getLinguisticTable()
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
    public function getSocialTable()
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
    public function getScientificTable()
    {

        $dataList = array();
        $dataList = $this->setSubjectRow($dataList, 'Mathematik');
        $dataList = $this->setSubjectRow($dataList, 'Biologie');
        $dataList = $this->setSubjectRow($dataList, 'Chemie');
        $dataList = $this->setSubjectRow($dataList, 'Physik');
        $dataList = $this->setSubjectRow($dataList, '&nbsp;');
        $dataList = $this->setSubjectRow($dataList, $this->tblReligionSubject ? $this->tblReligionSubject->getName() : 'Ev./Kath. Religion/Ethik');
        $dataList = $this->setSubjectRow($dataList, 'Sport');
        $dataList = $this->setSubjectRow($dataList, '&nbsp;');
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
}