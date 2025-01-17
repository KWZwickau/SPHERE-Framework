<?php

namespace SPHERE\Application\Education\Certificate\Prepare\BGyAbitur;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Abitur\AbstractBlock;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Structure\TableData;

class LeaveLevelEleven extends AbstractBlock
{
    /**
     * @var TblLeaveStudent|null
     */
    protected ?TblLeaveStudent $tblLeaveStudent = null;

    /**
     * @var array
     */
    private array $subjectList = array(
        'DE' => 'Deutsch',
        'EN' => 'Englisch',
        '2FS' => '2. Fremdsprache',
        'KU' => 'Kunst',
        'MU' => 'Musik',
        'GE' => 'Geschichte',
        'GRW' => 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft',
        'GEO' => 'Geographie',

        'MA' => 'Mathematik',
        'BIO' => 'Biologie',
        'CH' => 'Chemie',
        'PH' => 'Physik',
        'SPO' => 'Sport',
        'RELIGION' => 'Religion',
        'INF' => 'Informatik'
    );

    private array $gradeTextList = array(
        '1' => 'sehr gut',
        '2' => 'gut',
        '3' => 'befriedigend',
        '4' => 'ausreichend',
        '5' => 'mangelhaft',
        '6' => 'ungenügend',
    );

    /**
     * @var array
     */
    protected array $gradeList = array();

    /**
     * @var array|TblSubject[]
     */
    private array $availableSubjectList = array();

    /**
     * @param TblPerson $tblPerson
     * @param TblLeaveStudent|null $tblLeaveStudent
     */
    public function __construct(
        TblPerson $tblPerson,
        TblLeaveStudent $tblLeaveStudent
    ) {
        $this->tblPerson = $tblPerson;
        $this->tblLeaveStudent = $tblLeaveStudent;

        $this->setAvailableSubjects();
        $this->setGradeList();

        // Zensuren der Klasse 11 ermitteln
        $tblAppointedDateTaskLevelTen = false;
        if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListByPerson($tblPerson))) {
            foreach ($tblStudentEducationList as $tblStudentEducation) {
                if ($tblStudentEducation->getLevel() == 11
                    && ($tblYear = $tblStudentEducation->getServiceTblYear())
                    && ($tblCertificateType = Generator::useService()->getCertificateTypeByIdentifier('YEAR'))
                    && ($tblPrepareStudentList = Prepare::useService()->getPrepareStudentListByPersonAndCertificateTypeAndYear($tblPerson, $tblCertificateType, $tblYear))
                ) {
                    foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                        if (($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                            && $tblPrepare->getServiceTblAppointedDateTask()
                        ) {
                            $tblAppointedDateTaskLevelTen = $tblPrepare->getServiceTblAppointedDateTask();
                            break;
                        }
                    }

                    if ($tblAppointedDateTaskLevelTen) {
                        break;
                    }
                }
            }
        }

        if ($tblAppointedDateTaskLevelTen
            && ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('LEVEL-11'))
        ) {
            $count = 1;
            foreach ($this->availableSubjectList as $tblSubject) {
                if (($tblTaskGrade = Grade::useService()->getTaskGradeByPersonAndTaskAndSubject(
                    $tblPerson,
                    $tblAppointedDateTaskLevelTen,
                    $tblSubject,
                ))) {
                    if (Prepare::useService()->getLeaveAdditionalGradeBy(
                        $tblLeaveStudent,
                        $tblSubject,
                        $tblPrepareAdditionalGradeType
                    )) {
                        // #SSW-132 Es sollen nicht immer alle Fächer ausgewiesen werden.
//                        if ($tblPrepareAdditionalGrade->getGrade() !== $tblPrepareGradeLevelTen->getGrade()) {
//                            Prepare::useService()->updatePrepareAdditionalGrade(
//                                $tblPrepareAdditionalGrade,
//                                $tblPrepareGradeLevelTen->getGrade(),
//                                $tblPrepareAdditionalGrade->isSelected()
//                            );
//                        }
                    } else {
                        Prepare::useService()->createLeaveAdditionalGrade(
                            $tblLeaveStudent,
                            $tblSubject,
                            $tblPrepareAdditionalGradeType,
                            $tblTaskGrade->getGrade()
                        );
                    }
                }
            }
        }
    }

    /**
     * @param $Data
     *
     * @return Layout
     */
    public function getContent($Data): Layout
    {
        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('LEVEL-11'))
            && ($tblLeaveStudent = $this->tblLeaveStudent)
            && ($tblLeaveAdditionalGradeList = Prepare::useService()->getLeaveAdditionalGradeListBy(
                $tblLeaveStudent,
                $tblPrepareAdditionalGradeType
            ))
        ) {
            $global = $this->getGlobal();
            foreach ($tblLeaveAdditionalGradeList as $tblLeaveAdditionalGrade) {
                if (($tblSubject = $tblLeaveAdditionalGrade->getServiceTblSubject())) {
                    $global->POST['Data']['Grades'][$tblSubject->getId()] = $tblLeaveAdditionalGrade->getGrade();
                }
            }
            $global->savePost();
        }

        $dataList = $this->setData();

        $table = new TableData(
            $dataList,
            null,
            array(
                'Subject' => 'Fach',
                'Grade' => 'Note',
                'VerbalGrade' => 'Notenstufe',
            ),
            array(
                "paging" => false, // Deaktivieren Blättern
                "iDisplayLength" => -1,    // Alle Einträge zeigen
                "searching" => false, // Deaktivieren Suchen
                "info" => false,  // Deaktivieren Such-Info
                "sort" => false,
                "responsive" => false
            )
        );

        $form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel(
                            'Ergebnisse der Pflichtfächer, die in Klassenstufe 11 abgeschlossen wurden',
                            $table,
                            Panel::PANEL_TYPE_PRIMARY
                        ),
                    ))
                ))
            ))
        ));

        if ($this->tblLeaveStudent && !$this->tblLeaveStudent->isApproved()) {
            $content = new Well(Prepare::useService()->updateLeaveAbiturLevelElevenGrades(
                $form->appendFormButton(new Primary('Speichern', new Save())),
                $this->tblLeaveStudent,
                $Data
            ));
        } else {
            $content = $form;
        }

        return new Layout(array(
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        new Panel(
                            'Schüler',
                            $this->tblPerson ? $this->tblPerson->getLastFirstName() : '',
                            Panel::PANEL_TYPE_INFO
                        ),
                    ))
                )),
                new LayoutRow(array(
                    new LayoutColumn(array(
                        $content
                    ))
                ))
            ))
        ));
    }

    private function setAvailableSubjects()
    {
        $this->setCourses();
        $list = array();
        foreach ($this->subjectList as $acronym => $name) {
            // 2. Fremdsprache mit dem der tatsächlichen Sprache ersetzen
            if ($acronym == '2FS'
                && ($tblStudent = $this->tblPerson->getStudent())
                && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                && ($tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier(2))
                && ($tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking(
                    $tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking
                ))
            ) {
                $tblSubject = $tblStudentSubject->getServiceTblSubject();
            } else {
                $tblSubject = Subject::useService()->getSubjectByAcronym($acronym);
                if (!$tblSubject) {
                    $tblSubject = Subject::useService()->getSubjectByName($name);
                }
            }

            if ($tblSubject) {
                if (!(isset($this->AdvancedCourses[$tblSubject->getId()])
                    || isset($this->BasicCourses[$tblSubject->getId()]))
                ) {
                    $list[$tblSubject->getAcronym()] = $tblSubject;
                }
            }
        }

        ksort($list);
        $this->availableSubjectList = $list;
    }

    private function setData(): array
    {
        $dataList = array();
        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('LEVEL-11'))
            && ($tblLeaveStudent = $this->tblLeaveStudent)
        ) {
            foreach ($this->availableSubjectList as $tblSubject) {
                $isLocked = false;
                $gradeText = '';
                $selectBox = new SelectCompleter('Data[Grades][' . $tblSubject->getId() . ']', '', '', $this->gradeList);
                if (($tblPrepareAdditionalGrade = Prepare::useService()->getLeaveAdditionalGradeBy(
                    $tblLeaveStudent,
                    $tblSubject,
                    $tblPrepareAdditionalGradeType
                ))) {
                    $isLocked = $tblPrepareAdditionalGrade->isLocked();
                    if (isset($this->gradeTextList[$tblPrepareAdditionalGrade->getGrade()])) {
                        $gradeText = $this->gradeTextList[$tblPrepareAdditionalGrade->getGrade()];
                    }
                }
                if ($tblLeaveStudent->isApproved() || $isLocked) {
                    $selectBox->setDisabled();
                }

                $dataList[] = array(
                    'Subject' => $tblSubject->getName(),
                    'Grade' => $selectBox,
                    'VerbalGrade' => $gradeText,
                );
            }
        }

        return $dataList;
    }

    /**
     * @return void
     */
    protected function setGradeList()
    {
        $list[-1] = '';
        for ($i = 1; $i < 7; $i++) {
            $list[$i] = (string)$i;
        }

        $this->gradeList = $list;
    }
}