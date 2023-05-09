<?php

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Service\Data;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareComplexExam;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Ajax\Template\Notify;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;

abstract class ServiceDiploma extends ServiceCertificateContent
{
    /**
     * @param TblPrepareStudent $tblPrepareStudent
     * @param $identifier
     * @param $ranking
     *
     * @return false|TblPrepareComplexExam
     */
    public function getPrepareComplexExamBy(TblPrepareStudent $tblPrepareStudent, $identifier, $ranking)
    {
        return (new Data($this->getBinding()))->getPrepareComplexExamBy($tblPrepareStudent, $identifier, $ranking);
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return false|TblPrepareComplexExam[]
     */
    public function getPrepareComplexExamAllByPrepareStudent(TblPrepareStudent $tblPrepareStudent)
    {
        return (new Data($this->getBinding()))->getPrepareComplexExamAllByPrepareStudent($tblPrepareStudent);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPrepareCertificate $tblPrepare
     * @param $Data
     * @param null $NextTab
     *
     * @return IFormInterface|string|null
     */
    public function updatePrepareComplexExamList(
        IFormInterface $Stage,
        TblPrepareCertificate $tblPrepare,
        $Data,
        $NextTab = null
    ) {
        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $Stage;
        }

        foreach ($Data as $prepareStudentId => $array) {
            if (($tblPrepareStudent = $this->getPrepareStudentById($prepareStudentId))
                && is_array($array)
            ) {
                foreach ($array as $identifierRanking => $columns) {
                    $temp = explode('_', $identifierRanking);
                    $identifier = $temp[0];
                    $ranking = $temp[1];

                    $tblFirstSubject = false;
                    $tblSecondSubject = false;
                    $grade = '';
                    if (isset($columns['S1'])) {
                        $tblFirstSubject = Subject::useService()->getSubjectById($columns['S1']);
                    }
                    if (isset($columns['S2'])) {
                        $tblSecondSubject = Subject::useService()->getSubjectById($columns['S2']);
                    }
                    if (isset($columns['GradeText'])
                        && ($tblGradeText = Grade::useService()->getGradeTextById($columns['GradeText']))
                    ) {
                        $grade = $tblGradeText->getName();
                    } elseif (isset($columns['Grade'])) {
                        $grade = $columns['Grade'];
                    }

                    if (($tblPrepareComplexExam = $this->getPrepareComplexExamBy($tblPrepareStudent, $identifier, $ranking))) {
                        (new Data($this->getBinding()))->updatePrepareComplexExam($tblPrepareComplexExam, $grade,
                            $tblFirstSubject ?: null, $tblSecondSubject ?: null);
                    } else {
                        (new Data($this->getBinding()))->createPrepareComplexExam($tblPrepareStudent,$identifier, $ranking,
                            $grade, $tblFirstSubject ?: null, $tblSecondSubject ?: null);
                    }
                }
            }
        }

        if ($NextTab == null) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Komplexprüfungen wurden gespeichert.')
                . new Redirect('/Education/Certificate/Prepare/Prepare/Preview', Redirect::TIMEOUT_SUCCESS, array(
                    'PrepareId' => $tblPrepare->getId(),
                    'Route' => 'Diploma'
                ));
        } else {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Komplexprüfungen wurden gespeichert.')
                . new Redirect('/Education/Certificate/Prepare/Prepare/Diploma/Technical/Setting',
                    Redirect::TIMEOUT_SUCCESS,
                    array(
                        'PrepareId' => $tblPrepare->getId(),
                        'CurrentTab' => $NextTab
                    )
                );
        }
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPrepareCertificate $tblPrepare
     * @param $Data
     * @param $CertificateList
     * @param $NextTab
     * @param bool $hasAdditionalRemarkFhr
     *
     * @return IFormInterface|string|null
     */
    public function updateTechnicalDiplomaPrepareInformationList(
        IFormInterface $Stage,
        TblPrepareCertificate $tblPrepare,
        $Data,
        $CertificateList,
        $NextTab,
        bool $hasAdditionalRemarkFhr
    ) {
        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $Stage;
        }

        foreach ($Data as $prepareStudentId => $array) {
            if (($tblPrepareStudent = $this->getPrepareStudentById($prepareStudentId))
                && ($tblPrepareItem = $tblPrepareStudent->getTblPrepareCertificate())
                && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                && is_array($array)
            ) {
                if (isset($CertificateList[$tblPerson->getId()])) {
                    /** @var Certificate $Certificate */
                    $Certificate = $CertificateList[$tblPerson->getId()];

                    $issetAdditionalRemarkFhr = false;

                    /*
                     * Sonstige Informationen
                     */
                    foreach ($array as $field => $value) {
                        if ($field == 'SchoolType'
                            && method_exists($Certificate, 'selectValuesSchoolType')
                        ) {
                            $value = $Certificate->selectValuesSchoolType()[$value];
                        } elseif ($field == 'Type'
                            && method_exists($Certificate, 'selectValuesType')
                        ) {
                            $value = $Certificate->selectValuesType()[$value];
                        } elseif ($field == 'Success'
                            && method_exists($Certificate, 'selectValuesSuccess')
                        ) {
                            $value = $Certificate->selectValuesSuccess()[$value];
                        } elseif ($field == 'Transfer'
                            && method_exists($Certificate, 'selectValuesTransfer')
                        ) {
                            $value = $Certificate->selectValuesTransfer()[$value];
                        } elseif ($field == 'Job_Grade_Text'
                            && method_exists($Certificate, 'selectValuesJobGradeText')
                        ) {
                            $value = $Certificate->selectValuesJobGradeText()[$value];
//                        } elseif ($field == 'FoesAbsText' // SSW-1685 Auswahl soll aktuell nicht verfügbar sein, bis aufweiteres aufheben
//                            && method_exists($Certificate, 'selectValuesFoesAbsText')
//                        ) {
//                            $value = $Certificate->selectValuesFoesAbsText()[$value];
                        } elseif ($field == 'AdditionalRemarkFhr') {
                            $value = 'hat erfolglos an der Prüfung zum Erwerb der Fachhochschulreife teilgenommen.';
                            $issetAdditionalRemarkFhr = true;
                        }

                        // Zeugnistext umwandeln
                        if (strpos($field, '_GradeText')) {
                            if (($tblGradeText = Grade::useService()->getGradeTextById($value))) {
                                $value = $tblGradeText->getName();
                            } else {
                                $value = '';
                            }
                        }

                        if (trim($value) != '') {
                            $value = trim($value);
                            if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepareItem, $tblPerson, $field))
                            ) {
                                (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation, $field, $value);
                            } else {
                                (new Data($this->getBinding()))->createPrepareInformation($tblPrepareItem, $tblPerson, $field, $value);
                            }

                        } elseif (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepareItem, $tblPerson, $field))
                        ) {
                            // auf Leer zurücksetzen
                            (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation, $field, $value);
                        }
                    }

                    // Checkbox auf leer zurücksetzen
                    if ($hasAdditionalRemarkFhr
                        && !$issetAdditionalRemarkFhr
                        && ($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepareItem, $tblPerson, 'AdditionalRemarkFhr'))
                    ) {
                        (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation, 'AdditionalRemarkFhr', '');
                    }
                }
            }
        }

        if ($NextTab == null) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Informationen wurden gespeichert.')
                . new Redirect('/Education/Certificate/Prepare/Prepare/Preview', Redirect::TIMEOUT_SUCCESS, array(
                    'PrepareId' => $tblPrepare->getId(),
                    'Route' => 'Diploma'
                ));
        } else {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Informationen wurden gespeichert.')
                . new Redirect('/Education/Certificate/Prepare/Prepare/Diploma/Technical/Setting',
                    Redirect::TIMEOUT_SUCCESS,
                    array(
                        'PrepareId' => $tblPrepare->getId(),
                        'CurrentTab' => $NextTab
                    )
                );
        }
    }

    /**
     * @param IFormInterface $Form
     * @param $Data
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param $Route
     *
     * @return IFormInterface|string
     */
    public function createPrepareAdditionalGradeForm(
        IFormInterface $Form,
        $Data,
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        $Route
    ) {

        /**
         * Service
         */
        if ($Data === null) {
            return $Form;
        }

        $Error = false;
        $tblSubject = false;

        if (!isset($Data['Subject']) || !(($tblSubject = Subject::useService()->getSubjectById($Data['Subject'])))) {
            $Form->setError('Data[Subject]', 'Bitte wählen Sie ein Fach aus');
            $Error = true;
        }
        if (!isset($Data['Grade']) || empty($Data['Grade'])) {
            $Form->setError('Data[Grade]', 'Bitte geben Sie eine Zensur ein');
            $Error = true;
        }

        if ($Error) {
            return $Form . new Notify(
                'Fach konnte nicht angelegt werden',
                'Bitte füllen Sie die benötigten Felder korrekt aus',
                Notify::TYPE_WARNING,
                5000
            );
        } else {

            if ($tblSubject
                && ($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
            ) {
                if ($this->createPrepareAdditionalGrade(
                    $tblPrepareCertificate,
                    $tblPerson,
                    $tblSubject,
                    $tblPrepareAdditionalGradeType,
                    $this->getMaxRanking($tblPrepareCertificate, $tblPerson),
                    $Data['Grade'])
                ) {
                    return new Success('Das Fach wurde erfolgreich angelegt', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                        . new Redirect('/Education/Certificate/Prepare/DroppedSubjects', Redirect::TIMEOUT_SUCCESS,
                            array(
                                'PrepareId' => $tblPrepareCertificate->getId(),
                                'PersonId' => $tblPerson->getId(),
                                'Route' => $Route
                            ));
                }
            }
        }

        return new Danger('Das Fach konnte nicht angelegt werden');
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     *
     * @return int
     */
    private function getMaxRanking(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson
    ): int {

        if (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
            && $list = (new Data($this->getBinding()))->getPrepareAdditionalGradeListBy($tblPrepareCertificate,
                $tblPerson, $tblPrepareAdditionalGradeType)
        ) {

            $item = end($list);

            return $item->getRanking() + 1;
        }

        return 1;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param array $droppedSubjectsCreateList
     *
     * @return string
     */
    public function setAutoDroppedSubjects(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson, array &$droppedSubjectsCreateList): string
    {
        $gradeString = '';
        if (($tblYear = $tblPrepare->getYear())
            && ($tblSubjectList = Prepare::useService()->getAutoDroppedSubjects($tblPerson, $tblYear))
            && ($tblCertificateType = Generator::useService()->getCertificateTypeByIdentifier('YEAR'))
            && ($tblPrepareStudentList = Prepare::useService()->getPrepareStudentListByPersonAndCertificateType($tblPerson, $tblCertificateType))
            && ($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
        ) {
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                if ($tblPrepareStudent->isPrinted()
                    && ($tblPrepareTemp = $tblPrepareStudent->getTblPrepareCertificate())
                    && ($tblYear = $tblPrepareTemp->getYear())
                    && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                    && $tblStudentEducation->getLevel() == 9
                    && ($tblAppointedDateTask = $tblPrepareTemp->getServiceTblAppointedDateTask())
                ) {
                    $count = 1;
                    foreach ($tblSubjectList as $tblSubject) {
                        if (($tblTaskGrade = Grade::useService()->getTaskGradeByPersonAndTaskAndSubject($tblPerson, $tblAppointedDateTask, $tblSubject))) {
                            $tblPrepareAdditionalGrade = new TblPrepareAdditionalGrade();
                            $tblPrepareAdditionalGrade->setTblPrepareCertificate($tblPrepare);
                            $tblPrepareAdditionalGrade->setServiceTblPerson($tblPerson);
                            $tblPrepareAdditionalGrade->setServiceTblSubject($tblSubject);
                            $tblPrepareAdditionalGrade->setTblPrepareAdditionalGradeType($tblPrepareAdditionalGradeType);
                            $tblPrepareAdditionalGrade->setRanking($count++);
                            $tblPrepareAdditionalGrade->setGrade($tblTaskGrade->getDisplayGrade());
                            $droppedSubjectsCreateList[] = $tblPrepareAdditionalGrade;

                            $gradeString .= $tblSubject->getAcronym() . ':' . $tblPrepareAdditionalGrade->getGrade() . ' ';
                        }
                    }
                }
            }
        }

        return $gradeString;
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     *
     * @return bool
     */
    public function getHasPrepareLevel9OS(TblPrepareCertificate $tblPrepareCertificate): bool
    {
        if (($tblDivisionCourse = $tblPrepareCertificate->getServiceTblDivision())
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            foreach ($tblPersonList as $tblPerson) {
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                    && $tblStudentEducation->getLevel() == 9
                    && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                    && $tblSchoolType->getShortName() == 'OS'
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param IFormInterface $form
     * @param TblPrepareCertificate $tblPrepare
     * @param TblSubject|null $tblSubject
     * @param string $Route
     * @param string $NextTab
     * @param string $SchoolTypeShortName
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updatePrepareExamGrades(
        IFormInterface $form,
        TblPrepareCertificate $tblPrepare,
        ?TblSubject $tblSubject,
        string $Route,
        string $NextTab,
        string $SchoolTypeShortName,
        $Data
    ) {
        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $form;
        }

        $error = false;

        if ($Data != null) {
            foreach ($Data as $personGrades) {
                if (is_array($personGrades)) {
                    foreach ($personGrades as $identifier => $value) {
                        if (trim($value) !== '' && $identifier !== 'Text') {
                            if (!preg_match('!^[1-6]{1}$!is', trim($value))) {
                                $error = true;
                                break;
                            }
                        }
                    }
                }
            }
        }

        if ($error) {
            $form->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                    'Nicht alle eingebenen Zensuren befinden sich im Wertebereich (1-6). Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));

            return $form;
        } else {
            if ($Data != null) {
                foreach ($Data as $prepareStudentId => $personGrades) {
                    if (($tblPrepareStudent = $this->getPrepareStudentById($prepareStudentId))
                        && ($tblPrepareItem = $tblPrepareStudent->getTblPrepareCertificate())
                        && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                        && is_array($personGrades)
                    ) {
                        $hasGradeText = false;
                        $gradeText = '';
                        if ((isset($personGrades['Text']))
                            && ($tblGradeText = Grade::useService()->getGradeTextById($personGrades['Text']))
                        ) {
                            $hasGradeText = true;
                            $gradeText = $tblGradeText->getName();
                        }

                        foreach ($personGrades as $identifier => $value) {
                            // GradeText als Endnote speichern
                            if ($identifier == 'EN' && $hasGradeText) {
                                $value = $gradeText;
                            }

                            if (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier($identifier))) {
                                if ($tblPrepareAdditionalGrade = $this->getPrepareAdditionalGradeBy($tblPrepareItem, $tblPerson, $tblSubject, $tblPrepareAdditionalGradeType)) {
                                    (new Data($this->getBinding()))->updatePrepareAdditionalGrade($tblPrepareAdditionalGrade, trim($value));
                                } elseif (trim($value) != '') {
                                    (new Data($this->getBinding()))->createPrepareAdditionalGrade($tblPrepareItem, $tblPerson, $tblSubject, $tblPrepareAdditionalGradeType, 0, trim($value));
                                }
                            }
                        }
                    }
                }
            }

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Noten wurden gespeichert.')
                . new Redirect('/Education/Certificate/Prepare/Prepare/Diploma/Setting', Redirect::TIMEOUT_SUCCESS,
                    array(
                        'PrepareId' => $tblPrepare->getId(),
                        'Route' => $Route,
                        'SchoolTypeShortName' => $SchoolTypeShortName,
                        'Tab' => $NextTab
                    )
                );
        }
    }

    /**
     * @param array $gradeList
     * @param string $Key
     * @param bool $isExamRound
     *
     * @return string
     */
    public function getCalcDiplomaGrade(array $gradeList, string $Key, bool $isExamRound): string
    {
        $calc = '';
        $round = PHP_ROUND_HALF_UP;
        if (isset($gradeList['JN'])) {
            // Rest
            if (isset($gradeList['PS']) && isset($gradeList['PM'])) {
                $calc = ($gradeList['JN'] + $gradeList['PS'] + $gradeList['PM']) / 3;
            } elseif (isset($gradeList['PS']) && isset($gradeList['PZ'])) {
                $calc = ($gradeList['JN'] + $gradeList['PS'] + $gradeList['PZ']) / 3;
            } elseif (isset($gradeList['PM']) && isset($gradeList['PZ'])) {
                $calc = ($gradeList['JN'] + $gradeList['PM'] + $gradeList['PZ']) / 3;
            } elseif (isset($gradeList['PS'])) {
                $calc = ($gradeList['JN'] + $gradeList['PS']) / 2;
            } elseif (isset($gradeList['PM'])) {
                $calc = ($gradeList['JN'] + $gradeList['PM']) / 2;
            } elseif (isset($gradeList['PZ'])) {
                $calc = ($gradeList['JN'] + $gradeList['PZ']) / 2;
            }
            // Hauptschule Klasse 9OS
            elseif (isset($gradeList['LS']) && isset($gradeList['LM'])) {
                $calc = ($gradeList['JN'] + $gradeList['LS'] + $gradeList['LM']) / 3;
            } elseif (isset($gradeList['LS'])) {
                $calc = ($gradeList['JN'] + $gradeList['LS']) / 2;
            } elseif (isset($gradeList['LM'])) {
                $calc = ($gradeList['JN'] + $gradeList['LM']) / 2;
            }

            // bei ,5 entscheidet die Prüfungsnote bei FOS und BFS
            if ($isExamRound
                && strpos($calc, '.5') !== false
                && $gradeList['JN'] > $calc
            ) {
                $round = PHP_ROUND_HALF_DOWN;
            }
        }

        if (!$calc) {
            return '';
        } elseif ($Key == 'Average') {
            return str_replace('.', ',', round($calc, 2, $round));
        } else {
            return (string) round($calc, 0, $round);
        }
    }

    /**
     * @param $totalPoints
     *
     * @return string
     */
    public function  getResultForAbiturAverageGrade($totalPoints): string
    {
        // ist Formel korrekt?
//        return str_replace('.',',', round((17/3) - ($totalPoints/180),1));
        if ($totalPoints <= 900 && $totalPoints > 822) {
            return '1,0';
        } elseif ($totalPoints > 804) {
            return '1,1';
        } elseif ($totalPoints > 786) {
            return '1,2';
        } elseif ($totalPoints > 768) {
            return '1,3';
        } elseif ($totalPoints > 750) {
            return '1,4';
        } elseif ($totalPoints > 732) {
            return '1,5';
        } elseif ($totalPoints > 714) {
            return '1,6';
        } elseif ($totalPoints > 696) {
            return '1,7';
        } elseif ($totalPoints > 678) {
            return '1,8';
        } elseif ($totalPoints > 660) {
            return '1,9';
        } elseif ($totalPoints > 642) {
            return '2,0';
        } elseif ($totalPoints > 624) {
            return '2,1';
        } elseif ($totalPoints > 606) {
            return '2,2';
        } elseif ($totalPoints > 588) {
            return '2,3';
        } elseif ($totalPoints > 570) {
            return '2,4';
        } elseif ($totalPoints > 552) {
            return '2,5';
        } elseif ($totalPoints > 534) {
            return '2,6';
        } elseif ($totalPoints > 516) {
            return '2,7';
        } elseif ($totalPoints > 498) {
            return '2,8';
        } elseif ($totalPoints > 480) {
            return '2,9';
        } elseif ($totalPoints > 462) {
            return '3,0';
        } elseif ($totalPoints > 444) {
            return '3,1';
        } elseif ($totalPoints > 426) {
            return '3,2';
        } elseif ($totalPoints > 408) {
            return '3,3';
        } elseif ($totalPoints > 390) {
            return '3,4';
        } elseif ($totalPoints > 372) {
            return '3,5';
        } elseif ($totalPoints > 354) {
            return '3,6';
        } elseif ($totalPoints > 336) {
            return '3,7';
        } elseif ($totalPoints > 318) {
            return '3,8';
        } elseif ($totalPoints > 300) {
            return '3,9';
        } elseif ($totalPoints == 300) {
            return '4,0';
        } else {
            return '&nbsp;';
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     *
     * @return array|bool
     */
    public function checkAbiturExams(TblPrepareCertificate $tblPrepareCertificate, TblPerson $tblPerson)
    {
        $warnings = false;
        $exams = array();
        $hasGerman = false;
        $hasMathematics = false;
        for ($i = 1; $i <6; $i++) {
            $tblSubject = false;
            $grade = false;
            if ($i < 4) {
                $tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('WRITTEN_EXAM');
            } else {
                $tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('VERBAL_EXAM');
            }

            if (($examGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                $tblPrepareCertificate,
                $tblPerson,
                $tblPrepareAdditionalGradeType,
                $i))
            ) {
                $tblSubject = $examGrade->getServiceTblSubject();
                if ($tblSubject) {
                    if ($tblSubject->getName() == 'Deutsch'){
                        $hasGerman = true;
                    }
                    if ($tblSubject->getName() == 'Mathematik') {
                        $hasMathematics = true;
                    }
                }
                $grade = $examGrade->getGrade();
            }

            $exams[$i] = array(
                'Subject' => $tblSubject,
                'Grade' => $grade
            );
        }

        if (!$hasMathematics) {
            $warnings[] = new Warning('Das Fach Mathematik muss sich unter den Prüfungsfächern befinden!', new Exclamation());
        }
        if (!$hasGerman) {
            $warnings[] = new Warning('Das Fach Deutsch muss sich unter den Prüfungsfächern befinden!', new Exclamation());
        }

        return $warnings;
    }
}