<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.03.2018
 * Time: 13:47
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Abitur;

use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;

/**
 * Class BlockII
 *
 * @package SPHERE\Application\Education\Certificate\Prepare\Abitur
 */
class BlockII extends AbstractBlock
{
    /**
     * @var bool|array
     */
    private $AvailableSubjectsP3 = false;

    /**
     * @var bool|array
     */
    private $AvailableSubjectsP4P5 = false;

    /**
     * BlockII constructor.
     *
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

        $this->setCourses();
        $this->setPointList();

        $this->setAvailableExamsSubjetsP3();
        $this->setAvailableExamsSubjectsP4P5();
    }

    /**
     * @param null $Data
     *
     * @return Layout
     */
    public function getContent($Data = null): Layout
    {
        $dataList = array();
        if (($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy(
            $this->tblPrepareCertificate,
            $this->tblPerson
        ))) {
            $global = $this->getGlobal();
            foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                if (($tblPrepareAdditionalGradeType = $tblPrepareAdditionalGrade->getTblPrepareAdditionalGradeType())
                    && ($tblPrepareAdditionalGradeType->getIdentifier() == 'WRITTEN_EXAM'
                        || $tblPrepareAdditionalGradeType->getIdentifier() == 'VERBAL_EXAM'
                        || $tblPrepareAdditionalGradeType->getIdentifier() == 'EXTRA_VERBAL_EXAM'
                    )
                ) {
                    $global->POST['Data'][$tblPrepareAdditionalGrade->getRanking()]['Grades']
                        [$tblPrepareAdditionalGradeType->getIdentifier()] = $tblPrepareAdditionalGrade->getGrade();
                    if ($tblPrepareAdditionalGrade->getRanking() > 2
                        && (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject()))
                    ) {
                        $global->POST['Data'][$tblPrepareAdditionalGrade->getRanking()]['Subject'] = $tblSubject->getId();
                    }
                }
            }
            $global->savePost();
        }

        if (($tblPrepareInformationList = Prepare::useService()->getPrepareInformationAllByPerson($this->tblPrepareCertificate, $this->tblPerson))) {
            foreach ($tblPrepareInformationList as $tblPrepareInformation) {
                $global = $this->getGlobal();
                $global->POST['Data'][$tblPrepareInformation->getField()] = $tblPrepareInformation->getValue();
                $global->savePost();
            }
        }

        for ($i = 1; $i < 6; $i++) {
            $dataList = $this->setRow($dataList, $i);
        }

        $tableExam = new TableData(
            $dataList,
            null,
            array(
                'Number' => '#',
                'Exam' => 'Prüfungsfach',
                'WrittenExam' => 'schriftliche Prüfung',
                'VerbalExam' => 'mündliche Prüfung',
                'ExtraVerbalExam' => 'zusätzliche mündliche Prüfung',
                'Total' => 'Gesamtergebnis in vierfacher Wertung'
            ),
            array(
                "paging" => false, // Deaktivieren Blättern
                "iDisplayLength" => -1,    // Alle Einträge zeigen
                "searching" => false, // Deaktivieren Suchen
                "info" => false,  // Deaktivieren Such-Info
                "sort" => false,
                "responsive" => false,
                'columnDefs' => array(
                    array('width' => '5%', 'targets' => 0),
                    array('width' => '35%', 'targets' => 1),
                    array('width' => '15%', 'targets' => array(2, 3, 4, 5)),
                ),
                'fixedHeader' => false
            )
        );

        // funktioniert nur mit array in array
        $tableBELL = new TableData(
            array(array(
                'Description' => new TextArea(
                    'Data[BellSubject]',
                    'Thema'
                ),
                'Points' => new TextField(
                    'Data[BellPoints]',
                    'vierfache Wertung'
                )
            )),
            null,
            array(
                'Description' => 'Thema',
                'Points' => 'Punktzahl in vierfacher Wertung'
            ),
            array(
                "paging" => false, // Deaktivieren Blättern
                "iDisplayLength" => -1,    // Alle Einträge zeigen
                "searching" => false, // Deaktivieren Suchen
                "info" => false,  // Deaktivieren Such-Info
                "sort" => false,
                "responsive" => false,
                'columnDefs' => array(
                    array('width' => '80%', 'targets' => 0),
                    array('width' => '20%', 'targets' => 1),
                ),
                'fixedHeader' => false
            )
        );

        $checkbox = new CheckBox('Data[IsBellUsed]', 'Die besondere Lernleistung ersetzt das 5. Prüfungsfach', 1);
        if ($this->tblPrepareStudent && $this->tblPrepareStudent->isApproved()) {
            $checkbox->setDisabled();
        }

        $form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel(
                            'Ergebnisse in der Abiturprüfung',
                            $tableExam,
                            Panel::PANEL_TYPE_PRIMARY
                        ),
                        new Panel(
                            'Besondere Lernleistung',
                            array(
                                $checkbox,
                                $tableBELL
                            ),
                            Panel::PANEL_TYPE_PRIMARY
                        )
                    ))
                ))
            ))
        ));

        if ($this->tblPrepareStudent && !$this->tblPrepareStudent->isApproved()) {
            $form->appendFormButton(new Primary('Speichern', new Save()));
            $content = new Well(Prepare::useService()->updateAbiturExamGrades(
                $form,
                $this->tblPrepareCertificate,
                $this->tblPerson,
                $Data,
                $this->getFirstAdvancedCourse(),
                $this->getSecondAdvancedCourse()
            ));
        } else {
            $content = $form;
        }

        $resultBlockII = Prepare::useService()->getResultForAbiturBlockII(
            $this->tblPrepareCertificate,
            $this->tblPerson
        );
        if ($resultBlockII >= 100) {
            $resultBlockII = new Success(new Check() . ' ' . $resultBlockII . ' von mindestens 100 Punkten erreicht.');
        } else {
            $resultBlockII = new Warning(new Disable() . ' ' . $resultBlockII . ' von mindestens 100 Punkten erreicht.');
        }

        $columnsContent[] =  new Panel(
            'Schüler',
            $this->tblPerson ? $this->tblPerson->getLastFirstName() : '',
            Panel::PANEL_TYPE_INFO
        );
        $columnsContent[] = $resultBlockII;

        if (($warnings = Prepare::useService()->checkAbiturExams($this->tblPrepareCertificate, $this->tblPerson))) {
            foreach ($warnings as $warning) {
                $columnsContent[] = $warning;
            }
        }

        return new Layout(array(
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        $columnsContent
                    )
                )),
                new LayoutRow(array(
                    new LayoutColumn(array(
                        $content
                    ))
                ))
            ))
        ));
    }

    /**
     * @param $dataList
     * @param $i
     *
     * @return array
     */
    private function setRow($dataList, $i): array
    {
        $number = $i . '.';
        if ($i == 1) {
            $tblSubject = $this->getFirstAdvancedCourse();
            $number .= ' (LF)';
            $exam = ($tblSubject ? $tblSubject->getName() : '&nbsp;');
        } elseif ($i == 2) {
            $tblSubject = $this->getSecondAdvancedCourse();
            $number .= ' (LF)';
            $exam = ($tblSubject ? $tblSubject->getName() : '&nbsp;');
        } else {
            if ($i == 3) {
                $availableSubjects = $this->AvailableSubjectsP3;
            } else {
                $availableSubjects = $this->AvailableSubjectsP4P5;
            }

            $selectBoxSubject =  new SelectBox('Data[' .  $i . '][Subject]', '', array('Name' => $availableSubjects));
            if ($this->tblPrepareStudent && $this->tblPrepareStudent->isApproved()) {
                $selectBoxSubject->setDisabled();
            }
            $exam = $selectBoxSubject;
        }

        if ($i < 4) {
            $selectBox = new SelectCompleter('Data[' .  $i . '][Grades][WRITTEN_EXAM]', '', '', $this->pointsList);
            if ($this->tblPrepareStudent && $this->tblPrepareStudent->isApproved()) {
                $selectBox->setDisabled();
            }
            $writtenExam = $selectBox;

            $verbalExam = '&nbsp;';
        } else {
            $writtenExam = '&nbsp;';

            $selectBox = new SelectCompleter('Data[' .  $i . '][Grades][VERBAL_EXAM]', '', '', $this->pointsList);
            if ($this->tblPrepareStudent && $this->tblPrepareStudent->isApproved()) {
                $selectBox->setDisabled();
            }
            $verbalExam = $selectBox;
        }

        $selectBox = new SelectCompleter('Data[' .  $i . '][Grades][EXTRA_VERBAL_EXAM]', '', '', $this->pointsList);
        if ($this->tblPrepareStudent && $this->tblPrepareStudent->isApproved()) {
            $selectBox->setDisabled();
        }
        $extraVerbalExam = $selectBox;

        // https://publikationen.sachsen.de/bdb/artikel/28331 Seite 27 Tabelle
        $total = '&nbsp;';
        if ($i < 4) {
            if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('WRITTEN_EXAM'))
                && ($writtenExamGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                    $this->tblPrepareCertificate,
                    $this->tblPerson,
                    $tblPrepareAdditionalGradeType,
                    $i))
            ) {
                if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EXTRA_VERBAL_EXAM'))
                    && ($extraVerbalExamGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                        $this->tblPrepareCertificate,
                        $this->tblPerson,
                        $tblPrepareAdditionalGradeType,
                        $i))
                ) {

                } else {
                    $extraVerbalExamGrade = false;
                }

                $total = Prepare::useService()->calcAbiturExamGradesTotalForWrittenExam(
                    $writtenExamGrade,
                    $extraVerbalExamGrade ?: null
                );
            }
        } else {
            if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('VERBAL_EXAM'))
                && ($verbalExamGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                    $this->tblPrepareCertificate,
                    $this->tblPerson,
                    $tblPrepareAdditionalGradeType,
                    $i))
            ) {
                if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EXTRA_VERBAL_EXAM'))
                    && ($extraVerbalExamGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                        $this->tblPrepareCertificate,
                        $this->tblPerson,
                        $tblPrepareAdditionalGradeType,
                        $i))
                ) {

                } else {
                    $extraVerbalExamGrade = false;
                }

                $total = Prepare::useService()->calcAbiturExamGradesTotalForVerbalExam(
                    $verbalExamGrade,
                    $extraVerbalExamGrade ?: null
                );
            }
        }

        $dataList[] = array(
            'Number' => $number,
            'Exam' => $exam,
            'WrittenExam' => $writtenExam,
            'VerbalExam' => $verbalExam,
            'ExtraVerbalExam' => $extraVerbalExam,
            'Total' => $total
        );

        return $dataList;
    }

    private function setAvailableExamsSubjetsP3()
    {
        $this->setAvailableExamsSubjectToArray('Deutsch');
        $this->setAvailableExamsSubjectToArray('Mathematik');
        $this->setAvailableExamsSubjectToArray('Geschichte');
        $this->setAvailableExamsSubjectToArray('Gemeinschaftskunde/Rechtserziehung/Wirtschaft');
        $this->setAvailableExamsSubjectToArray('Geographie');
        $this->setAvailableExamsSubjectToArray('Biologie');
        $this->setAvailableExamsSubjectToArray('Chemie');
        $this->setAvailableExamsSubjectToArray('Physik');

        //1 An Schulen in kirchlicher Trägerschaft kann das Fach auch schriftliches Prüfungsfach P3 sein
        /** @var TblSubject $tblSubject */
        foreach ($this->BasicCourses as $tblSubject) {
            if (strpos($tblSubject->getName(), 'Religion') !== false) {
                $this->AvailableSubjectsP3[$tblSubject->getId()] = $tblSubject;
            }
        }
    }

    private function setAvailableExamsSubjectsP4P5()
    {
        $this->setAvailableExamsSubjectToArray('Deutsch',false);
        $this->setAvailableExamsSubjectToArray('Mathematik',false);
        $this->setAvailableExamsSubjectToArray('Kunst',false);
        $this->setAvailableExamsSubjectToArray('Musik',false);
        $this->setAvailableExamsSubjectToArray('Geschichte',false);
        $this->setAvailableExamsSubjectToArray('Gemeinschaftskunde/Rechtserziehung/Wirtschaft', false);
        $this->setAvailableExamsSubjectToArray('Geographie', false);
        $this->setAvailableExamsSubjectToArray('Biologie', false);
        $this->setAvailableExamsSubjectToArray('Chemie', false);
        $this->setAvailableExamsSubjectToArray('Physik', false);
        $this->setAvailableExamsSubjectToArray('Ethik', false);
        $this->setAvailableExamsSubjectToArray('Informatik', false);

        $tblSubjectEn2 = Subject::useService()->getSubjectByAcronym('EN2');
        $foreignLanguages = array();
        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
            && $this->tblPerson
            && ($tblStudent = $this->tblPerson->getStudent())
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType))
        ) {
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                if (($tblSubjectItem = $tblStudentSubject->getServiceTblSubject())) {
                    // Eine in Klassenstufe 10 begonnene Fremdsprache kann kein Prüfungsfach sein
                    if ($tblStudentSubject->getLevelFrom() != 10) {
                        $foreignLanguages[$tblSubjectItem->getId()] = $tblSubjectItem;
                        if ($tblSubjectItem->getName() == 'Englisch' && $tblSubjectEn2) {
                            $foreignLanguages[$tblSubjectEn2->getId()] = $tblSubjectEn2;
                        }
                    }
                }
            }
        }

        /** @var TblSubject $tblSubject */
        foreach ($this->BasicCourses as $tblSubject) {
            if (strpos($tblSubject->getName(), 'Religion') !== false) {
                $this->AvailableSubjectsP4P5[$tblSubject->getId()] = $tblSubject;
            } elseif (isset($foreignLanguages[$tblSubject->getId()])) {
                $this->AvailableSubjectsP4P5[$tblSubject->getId()] = $tblSubject;
            }
        }
    }

    private function setAvailableExamsSubjectToArray($subjectName, $isP3 = true)
    {
        $tblSubject = Subject::useService()->getSubjectByName($subjectName);
        if (!$tblSubject && $subjectName == 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('GRW');
            if (!$tblSubject)  {
                $tblSubject = Subject::useService()->getSubjectByAcronym('G/R/W');
            }
        }

        if ($tblSubject
            && isset($this->BasicCourses[$tblSubject->getId()])
        ) {
            if ($isP3) {
                $this->AvailableSubjectsP3[$tblSubject->getId()] = $tblSubject;
            } else {
                $this->AvailableSubjectsP4P5[$tblSubject->getId()] = $tblSubject;
            }
        }
    }
}