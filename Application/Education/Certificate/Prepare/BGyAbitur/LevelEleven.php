<?php

namespace SPHERE\Application\Education\Certificate\Prepare\BGyAbitur;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Abitur\AbstractBlock;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
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
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Structure\TableData;

class LevelEleven extends AbstractBlock
{
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
     * @param TblPrepareCertificate $tblPrepareCertificate
     */
    public function __construct(
        TblPerson $tblPerson,
        TblPrepareCertificate $tblPrepareCertificate
    ) {
        $this->tblPerson = $tblPerson;
        $this->tblPrepareCertificate = $tblPrepareCertificate;

        $this->tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareCertificate, $tblPerson);

        $this->setAvailableSubjects();
        $this->setGradeList();

        // Zensuren der Klasse 10 ermitteln
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
                    if (Prepare::useService()->getPrepareAdditionalGradeBy(
                        $tblPrepareCertificate,
                        $tblPerson,
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
                        Prepare::useService()->createPrepareAdditionalGrade(
                            $tblPrepareCertificate,
                            $tblPerson,
                            $tblSubject,
                            $tblPrepareAdditionalGradeType,
                            $count++,
                            $tblTaskGrade->getGrade(),
                            false,
                            true
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
            && ($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy(
                $this->tblPrepareCertificate,
                $this->tblPerson,
                $tblPrepareAdditionalGradeType
            ))
        ) {
            $global = $this->getGlobal();
            foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                    $global->POST['Data']['Grades'][$tblSubject->getId()] = $tblPrepareAdditionalGrade->getGrade();
                }
            }
            $global->savePost();
        }
        if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy(
                $this->tblPrepareCertificate,
                $this->tblPerson,
                'LevelTenGradesAreNotShown'
            ))
            && $tblPrepareInformation->getValue()
        ) {
            $global = $this->getGlobal();
            $global->POST['Data']['LevelTenGradesAreNotShown'] = 1;
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
                "responsive" => false,
//                'columnDefs' => array(
//                    array('width' => '5%', 'targets' => 0),
//                    array('width' => '35%', 'targets' => 1),
//                    array('width' => '15%', 'targets' => array(2, 3, 4, 5)),
//                ),
//                'fixedHeader' => false
            )
        );

        $form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel(
                            'Ergebnisse der Pflichtfächer, die in Klassenstufe 11 abgeschlossen wurden',
                            array(
                                new CheckBox('Data[LevelTenGradesAreNotShown]','Die Ausweisung der Noten und Notenstufen wurde vom Schüler abgelehnt.',1),
                                $table
                            ),
                            Panel::PANEL_TYPE_PRIMARY
                        ),
                    ))
                ))
            ))
        ));

        if ($this->tblPrepareStudent && !$this->tblPrepareStudent->isApproved()) {
            $content = new Well(Prepare::useService()->updateAbiturLevelTenGrades(
                $form->appendFormButton(new Primary('Speichern', new Save())),
                $this->tblPrepareCertificate,
                $this->tblPerson,
                $Data,
                11
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
        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('LEVEL-11'))) {
            foreach ($this->availableSubjectList as $tblSubject) {
                $isLocked = false;
                $gradeText = '';
                $selectBox = new SelectCompleter('Data[Grades][' . $tblSubject->getId() . ']', '', '',
                    $this->gradeList);
                if (($tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeBy(
                    $this->tblPrepareCertificate,
                    $this->tblPerson,
                    $tblSubject,
                    $tblPrepareAdditionalGradeType
                ))) {
                    $isLocked = $tblPrepareAdditionalGrade->isLocked();
                    if (isset($this->gradeTextList[$tblPrepareAdditionalGrade->getGrade()])) {
                        $gradeText = $this->gradeTextList[$tblPrepareAdditionalGrade->getGrade()];
                    }
                }
                if (($this->tblPrepareStudent && $this->tblPrepareStudent->isApproved()) || $isLocked) {
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

    protected function setGradeList()
    {
        $list[-1] = '';
        for ($i = 1; $i < 7; $i++) {
            $list[$i] = (string)$i;
        }

        $this->gradeList = $list;
    }
}