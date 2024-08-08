<?php

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Abitur\BlockIView;
use SPHERE\Application\Education\Certificate\Prepare\Service\Data;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGradeType;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

abstract class ServiceAbitur extends AbstractService
{
    /**
     * @param TblPerson $tblPerson
     *
     * @return array
     */
    public function getPrepareStudentListFromMidTermCertificatesByPerson(TblPerson $tblPerson): array
    {
        $prepareStudentList = array();
        if (($tblCertificateType = Generator::useService()->getCertificateTypeByIdentifier('MID_TERM_COURSE'))
            && ($tblPrepareStudentList = Prepare::useService()->getPrepareStudentListByPersonAndCertificateType($tblPerson, $tblCertificateType))
        ) {
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                if (($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                    && ($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                    && ($tblYear = $tblGenerateCertificate->getServiceTblYear())
                    && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                ) {
                    $midTerm = '-1';
                    $month = $tblGenerateCertificate->getDateTime() ? intval($tblGenerateCertificate->getDateTime()->format('m')) : 0;
                    if ($month > 3 && $month < 9) {
                        $midTerm = '-2';
                    }

                    // Schuljahreswiederholungen, alte Klasse ignorieren, es kommt die neuere Klasse zuerst raus
                    if (!isset($prepareStudentList[$tblStudentEducation->getLevel() . $midTerm])) {
                        $prepareStudentList[$tblStudentEducation->getLevel() . $midTerm] = $tblPrepareStudent;
                    }
                }
            }
        }

        return $prepareStudentList;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     *
     * @return array
     */
    public function getResultForAbiturBlockI(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson
    ): array {
        $countCourses = 0;
        $countCoursesTotal = 0;
        $resultBlockI = 0;
        if (($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy(
            $tblPrepare,
            $tblPerson
        ))) {
            if (($tblYear = $tblPrepare->getYear())) {
                $advancedCourses = DivisionCourse::useService()->getAdvancedCoursesForStudent($tblPerson, $tblYear);
                // BGy
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                    && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                    && $tblSchoolType->getShortName() == 'BGy'
                ) {
                    $identifierList = array(
                        '12-1' => 1,
                        '12-2' => 1,
                        '13-1' => 1,
                        '13-2' => 1,
                    );
                } else {
                    // Gy
                    $identifierList = array(
                        '11-1' => 1,
                        '11-2' => 1,
                        '12-1' => 1,
                        '12-2' => 1,
                    );
                }
            } else {
                $advancedCourses = array();
            }

            foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                $identifier = $tblPrepareAdditionalGrade->getTblPrepareAdditionalGradeType()->getIdentifier();
                if (isset($identifierList[$identifier])) {
                    if (($tblPrepareAdditionalGrade->isSelected())) {
                        $countCourses++;
                        // Leistungskurse zählen doppelt
                        if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())
                            && isset($advancedCourses[$tblSubject->getId()])
                        ) {
                            $countCoursesTotal += 2;
                            $resultBlockI += 2 * floatval($tblPrepareAdditionalGrade->getGrade());
                        } else {
                            $countCoursesTotal++;
                            $resultBlockI += floatval($tblPrepareAdditionalGrade->getGrade());
                        }
                    }
                }
            }

            if ($countCoursesTotal > 0) {
                $resultBlockI = round(($resultBlockI / $countCoursesTotal) * 40);
            }
        }

        return array($countCourses, $resultBlockI);
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     *
     * @return int
     */
    public function getResultForAbiturBlockII(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson
    ): int {
        $result = 0;
        for ($i = 1; $i < 6; $i++) {
            $total = 0;
            if ($i < 4) {
                if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('WRITTEN_EXAM'))
                    && ($writtenExamGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                        $tblPrepareCertificate,
                        $tblPerson,
                        $tblPrepareAdditionalGradeType,
                        $i
                    ))
                ) {
                    if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EXTRA_VERBAL_EXAM'))
                        && ($extraVerbalExamGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                            $tblPrepareCertificate,
                            $tblPerson,
                            $tblPrepareAdditionalGradeType,
                            $i
                        ))
                    ) {

                    } else {
                        $extraVerbalExamGrade = false;
                    }

                    $total = Prepare::useService()->calcAbiturExamGradesTotalForWrittenExam($writtenExamGrade, $extraVerbalExamGrade ?: null);
                }
            } else {
                if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('VERBAL_EXAM'))
                    && ($verbalExamGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                        $tblPrepareCertificate,
                        $tblPerson,
                        $tblPrepareAdditionalGradeType,
                        $i
                    ))
                ) {
                    if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EXTRA_VERBAL_EXAM'))
                        && ($extraVerbalExamGrade = Prepare::useService()->getPrepareAdditionalGradeByRanking(
                            $tblPrepareCertificate,
                            $tblPerson,
                            $tblPrepareAdditionalGradeType,
                            $i
                        ))
                    ) {

                    } else {
                        $extraVerbalExamGrade = false;
                    }

                    $total = Prepare::useService()->calcAbiturExamGradesTotalForVerbalExam($verbalExamGrade, $extraVerbalExamGrade ?: null);
                }
            }

            // die Bell ersetzt das 5. Prüfungsfach
            if ($i == 5) {
                if (($tblPrepareInformationIsBellUsed = Prepare::useService()->getPrepareInformationBy(
                        $tblPrepareCertificate, $tblPerson, 'IsBellUsed'))
                    && $tblPrepareInformationIsBellUsed->getValue()
                ) {
                    $total = 0;
                    if (($tblPrepareInformationBellPoints = Prepare::useService()->getPrepareInformationBy(
                        $tblPrepareCertificate, $tblPerson, 'BellPoints'))
                    ) {
                        $total = floatval($tblPrepareInformationBellPoints->getValue());
                    }
                }
            }

            $result += floatval($total);
        }

        return $result;
    }

    /**
     * @param TblPrepareAdditionalGrade $tblVerbalExamGrade
     * @param TblPrepareAdditionalGrade|null $tblExtraVerbalGrade
     *
     * @return string
     */
    public function calcAbiturExamGradesTotalForVerbalExam(
        TblPrepareAdditionalGrade $tblVerbalExamGrade,
        TblPrepareAdditionalGrade $tblExtraVerbalGrade = null
    ): string {
        $verbalExamGradeValue = $tblVerbalExamGrade->getGrade();
        if ($tblExtraVerbalGrade) {
            $extraVerbalExamGradeValue = $tblExtraVerbalGrade->getGrade();
            if ($extraVerbalExamGradeValue !== '' && $extraVerbalExamGradeValue !== null) {
                $total = 4 * (floatval($verbalExamGradeValue) * (2 / 3) + floatval($extraVerbalExamGradeValue) * (1 / 3));
                $total = str_pad(round($total), 2, 0, STR_PAD_LEFT);
            } else {
                $total = floatval($verbalExamGradeValue) * 4;
            }
        } else {
            $total = floatval($verbalExamGradeValue) * 4;
        }

        return str_pad(round($total), 2, 0, STR_PAD_LEFT);
    }

    /**
     * @param TblPrepareAdditionalGrade $tblWrittenExamGrade
     * @param TblPrepareAdditionalGrade|null $tblExtraVerbalGrade
     *
     * @return string
     */
    public function calcAbiturExamGradesTotalForWrittenExam(
        TblPrepareAdditionalGrade $tblWrittenExamGrade,
        TblPrepareAdditionalGrade $tblExtraVerbalGrade = null
    ): string {

        $writtenExamGradeValue = $tblWrittenExamGrade->getGrade();
        if ($tblExtraVerbalGrade) {
            $extraVerbalExamGradeValue = $tblExtraVerbalGrade->getGrade();
            if ($extraVerbalExamGradeValue !== '' && $extraVerbalExamGradeValue !== null) {
                $total = 4 * (floatval($writtenExamGradeValue) * (2 / 3) + floatval($extraVerbalExamGradeValue) * (1 / 3));
            } else {
                $total = floatval($writtenExamGradeValue) * 4;
            }
        } else {
            $total = floatval($writtenExamGradeValue) * 4;
        }

        return str_pad(round($total), 2, 0, STR_PAD_LEFT);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param TblTask $tblAppointedDateTask
     */
    public function copyAbiturPreliminaryGradesFromAppointedDateTask(
        TblPerson $tblPerson,
        TblPrepareCertificate $tblPrepareCertificate,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        TblTask $tblAppointedDateTask
    ) {
        if (($tblTaskGradeList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblAppointedDateTask, $tblPerson))) {
            foreach ($tblTaskGradeList as $tblTaskGrade) {
                if (($tblSubject = $tblTaskGrade->getServiceTblSubject())
                    // keine leeren Zensuren kopieren
                    && $tblTaskGrade->getGrade() !== ''
                    && $tblTaskGrade->getGrade() !== null
                ) {
                    if ($tblSubject->getAcronym() == 'EN2' && ($tblSubjectTemp = Subject::useService()->getSubjectByAcronym('EN'))) {
                        $tblSubject = $tblSubjectTemp;
                    }

                    if (($tblPrepareAdditionalGrade = $this->getPrepareAdditionalGradeBy($tblPrepareCertificate, $tblPerson, $tblSubject, $tblPrepareAdditionalGradeType))) {
                        // Zensur aktualisieren
                        if (($tblTaskGrade->getGrade() !== $tblPrepareAdditionalGrade->getGrade())) {
                            (new Data($this->getBinding()))->updatePrepareAdditionalGrade(
                                $tblPrepareAdditionalGrade,
                                $tblTaskGrade->getGrade(),
                                $tblPrepareAdditionalGrade->isSelected()
                            );
                        }
                    } else {
                        (new Data($this->getBinding()))->createPrepareAdditionalGrade(
                            $tblPrepareCertificate,
                            $tblPerson,
                            $tblSubject,
                            $tblPrepareAdditionalGradeType,
                            0,
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
     * @param TblPrepareStudent $tblPrepareStudent
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param TblPrepareCertificate $tblPrepareCertificate
     */
    public function copyAbiturPreliminaryGradesFromCertificates(
        TblPrepareStudent $tblPrepareStudent,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        TblPrepareCertificate $tblPrepareCertificate
    ) {
        // Zensuren vom Zeugnissen
        if (($tblPreviousPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblAppointedDateTask = $tblPreviousPrepare->getServiceTblAppointedDateTask())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
        ) {
            $this->copyAbiturPreliminaryGradesFromAppointedDateTask($tblPerson, $tblPrepareCertificate, $tblPrepareAdditionalGradeType, $tblAppointedDateTask);
        }
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblPerson $tblPerson
     * @param TblPrepareCertificate $tblPrepare
     * @param $View
     * @param $Data
     *
     * @return IFormInterface|string|null
     */
    public function updateAbiturPreliminaryGrades(
        ?IFormInterface $Form,
        TblPerson $tblPerson,
        TblPrepareCertificate $tblPrepare,
        $View = null,
        $Data = null
    ) {
        if ($Data === null) {
            return $Form;
        }

        if ($View == BlockIView::EDIT_GRADES) {
            // check Wertebereich
            $error = false;
            foreach ($Data as $midTerm => $subjects) {
                if (is_array($subjects)
                    && $this->getPrepareAdditionalGradeTypeByIdentifier($midTerm)
                ) {
                    foreach ($subjects as $value) {
                        if (trim($value) !== '') {
                            if (!preg_match('!^([0-9]{1}|1[0-5]{1})$!is', trim($value))) {
                                $error = true;
                                break;
                            }
                        }
                    }
                }
            }

            if ($error) {
                $Form->prependGridGroup(
                    new FormGroup(new FormRow(new FormColumn(new Danger(
                            'Nicht alle eingebenen Zensuren befinden sich im Wertebereich (0 - 15 Punkte).
                            Die Daten wurden nicht gespeichert.', new Exclamation())
                    ))));

                return $Form;
            }

            foreach ($Data as $midTerm => $subjects) {
                if (is_array($subjects)
                    && (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier($midTerm)))
                ) {
                    foreach ($subjects as $subjectId => $grade) {
                        $grade = trim($grade);
                        if (($tblSubject = Subject::useService()->getSubjectById($subjectId))
                            && $grade !== null && $grade !== ''
                        ) {
                            if (($tblPrepareAdditionalGrade = $this->getPrepareAdditionalGradeBy(
                                $tblPrepare,
                                $tblPerson,
                                $tblSubject,
                                $tblPrepareAdditionalGradeType
                            ))) {
                                (new Data($this->getBinding()))->updatePrepareAdditionalGrade(
                                    $tblPrepareAdditionalGrade, $grade, $tblPrepareAdditionalGrade->isSelected());
                            } else {
                                (new Data($this->getBinding()))->createPrepareAdditionalGrade($tblPrepare,
                                    $tblPerson, $tblSubject, $tblPrepareAdditionalGradeType, 0, $grade, false, false);
                            }
                        }
                    }
                }
            }
        } elseif ($View == BlockIView::CHOOSE_COURSES) {
            // BGy
            if (($tblYear = $tblPrepare->getYear())
                && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                && $tblSchoolType->getShortName() == 'BGy'
            ) {
                $levelFrom = 12;
                $levelTo = 13;
            } else {
                // Gy
                $levelFrom = 11;
                $levelTo = 12;
            }

            for ($level = $levelFrom; $level <= $levelTo; $level++) {
                for ($term = 1; $term < 3; $term++) {
                    $midTerm = $level . '-' . $term;
                    if (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier($midTerm))
                        && ($tblPrepareAdditionalGradeList = $this->getPrepareAdditionalGradeListBy(
                            $tblPrepare, $tblPerson, $tblPrepareAdditionalGradeType
                        ))
                    ) {
                        foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                            if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                                if (isset($Data[$midTerm][$tblSubject->getId()])) {
                                    if (!$tblPrepareAdditionalGrade->isSelected()) {
                                        (new Data($this->getBinding()))->updatePrepareAdditionalGrade(
                                            $tblPrepareAdditionalGrade, $tblPrepareAdditionalGrade->getGrade(), true
                                        );
                                    }
                                } else {
                                    if ($tblPrepareAdditionalGrade->isSelected()) {
                                        (new Data($this->getBinding()))->updatePrepareAdditionalGrade(
                                            $tblPrepareAdditionalGrade, $tblPrepareAdditionalGrade->getGrade(), false
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Prepare/Diploma/Abitur/BlockI', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId(),
                'PersonId' => $tblPerson->getId(),
                'Route' => 'Diploma'
            ));
    }

    /**
     * @param IFormInterface $form
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param $Data
     * @param $firstAdvancedCourse
     * @param $secondAdvancedCourse
     *
     * @return IFormInterface|string
     */
    public function updateAbiturExamGrades(
        IFormInterface $form,
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        $Data,
        $firstAdvancedCourse,
        $secondAdvancedCourse
    ) {
        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $form;
        }

        // check Wertebereich && is subject selected
        $errorGrades = false;
        $errorSubject = false;
        $errorRanking = 0;
        foreach ($Data as $ranking => $items) {
            if (isset($items['Grades'])) {
                foreach ($items['Grades'] as $value) {
                    if (trim($value) !== '') {
                        if (!preg_match('!^([0-9]{1}|1[0-5]{1})$!is', trim($value))) {
                            $errorGrades = true;
                            break;
                        }
                    }
                }
            }
            if ($ranking > 2) {
                if (isset($items['Subject']) && !Subject::useService()->getSubjectById($items['Subject'])) {
                    if (isset($items['Grades'])) {
                        foreach ($items['Grades'] as $value) {
                            if (trim($value) !== '') {
                                $errorSubject = true;
                                $errorRanking = $ranking;
                                break;
                            }
                        }
                    }
                }
            }

        }

        if ($errorGrades) {
            $form->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        'Nicht alle eingebenen Zensuren befinden sich im Wertebereich (0 - 15 Punkte).
                            Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));
        }
        if ($errorSubject) {
            $form->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        'Beim ' . $errorRanking . '. Prüfungsfach wurde kein Fach ausgewählt. Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));
        }
        if ($errorGrades || $errorSubject) {
            return $form;
        }

        foreach ($Data as $ranking => $items) {
            $tblSubject = false;
            if ($ranking === 1) {
                if ($firstAdvancedCourse) {
                    $tblSubject = $firstAdvancedCourse;
                }
            } elseif ($ranking === 2) {
                if ($secondAdvancedCourse) {
                    $tblSubject = $secondAdvancedCourse;
                }
            } elseif (isset($items['Subject'])) {
                $tblSubject = Subject::useService()->getSubjectById($items['Subject']);
            }

            if ($tblSubject) {
                if (isset($items['Grades'])) {
                    foreach ($items['Grades'] as $key => $value) {
                        if (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier($key))) {
                            if (($tblPrepareAdditionalGrade = $this->getPrepareAdditionalGradeByRanking(
                                $tblPrepare,
                                $tblPerson,
                                $tblPrepareAdditionalGradeType,
                                $ranking
                            ))) {
                                (new Data($this->getBinding()))->updatePrepareAdditionalGradeAndSubject(
                                    $tblPrepareAdditionalGrade,
                                    $tblSubject,
                                    $value
                                );
                            } else {
                                (new Data($this->getBinding()))->createPrepareAdditionalGrade(
                                    $tblPrepare,
                                    $tblPerson,
                                    $tblSubject,
                                    $tblPrepareAdditionalGradeType,
                                    $ranking,
                                    $value
                                );
                            }
                        }
                    }
                }
            }
        }

        if (isset($Data['BellSubject'])) {
            if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, 'BellSubject'))) {
                (new Data($this->getBinding()))->updatePrepareInformation(
                    $tblPrepareInformation,
                    'BellSubject',
                    $Data['BellSubject']
                );
            } else {
                (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, 'BellSubject', $Data['BellSubject']);
            }
        }
        if (isset($Data['BellPoints'])) {
            if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, 'BellPoints'))) {
                (new Data($this->getBinding()))->updatePrepareInformation(
                    $tblPrepareInformation,
                    'BellPoints',
                    $Data['BellPoints']
                );
            } else {
                (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, 'BellPoints', $Data['BellPoints']);
            }
        }

        $isBellUsed = isset($Data['IsBellUsed']);
        if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, 'IsBellUsed'))) {
            (new Data($this->getBinding()))->updatePrepareInformation(
                $tblPrepareInformation,
                'IsBellUsed',
                $isBellUsed
            );
        } else {
            (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, 'IsBellUsed', $isBellUsed);
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Prepare/Diploma/Abitur/BlockII', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId(),
                'PersonId' => $tblPerson->getId(),
                'Route' => 'Diploma'
            ));
    }

    /**
     * @param IFormInterface $form
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param $Data
     * @param int $level
     *
     * @return IFormInterface|string
     */
    public function updateAbiturLevelTenGrades(
        IFormInterface $form,
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        $Data,
        int $level = 10
    ) {
        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $form;
        }

        // check Wertebereich
        $errorGrades = false;
        if (isset($Data['Grades'])) {
            foreach ($Data['Grades'] as $value) {
                if (trim($value) !== '') {
                    if (!preg_match('!^[1-6]{1}$!is', trim($value))) {
                        $errorGrades = true;
                        break;
                    }
                }
            }
        }

        if ($errorGrades) {
            $form->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        'Nicht alle eingebenen Zensuren befinden sich im Wertebereich (1 - 6).
                            Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));

            return $form;
        }

        if (isset($Data['Grades'])
            && ($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('LEVEL-' . $level))
        ) {
            $ranking = 1;
            foreach ($Data['Grades'] as $subjectId => $value) {
                $value = trim($value);
                if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                    if (($tblPrepareAdditionalGrade = $this->getPrepareAdditionalGradeBy(
                        $tblPrepare,
                        $tblPerson,
                        $tblSubject,
                        $tblPrepareAdditionalGradeType
                    ))) {
                        (new Data($this->getBinding()))->updatePrepareAdditionalGrade(
                            $tblPrepareAdditionalGrade,
                            $value
                        );
                    } else {
                        if ($value !== '') {
                            (new Data($this->getBinding()))->createPrepareAdditionalGrade(
                                $tblPrepare,
                                $tblPerson,
                                $tblSubject,
                                $tblPrepareAdditionalGradeType,
                                $ranking,
                                $value
                            );
                        }
                    }
                }

                $ranking++;
            }
        }

        $levelTenGradesAreNotShown = isset($Data['LevelTenGradesAreNotShown']);
        if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, 'LevelTenGradesAreNotShown'))) {
            (new Data($this->getBinding()))->updatePrepareInformation(
                $tblPrepareInformation,
                'LevelTenGradesAreNotShown',
                $levelTenGradesAreNotShown
            );
        } else {
            (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, 'LevelTenGradesAreNotShown', $levelTenGradesAreNotShown);
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Prepare/Diploma/Abitur/Preview', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId(),
                'Route' => 'Diploma'
            ));
    }

    /**
     * @param IFormInterface $form
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateAbiturPrepareInformation(
        IFormInterface $form,
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        $Data
    ) {
        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $form;
        }

        if (isset($Data['Remark'])) {
            if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, 'Remark'))) {
                (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation, 'Remark', $Data['Remark']);
            } else {
                (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, 'Remark', $Data['Remark']);
            }
        }

        if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, 'Latinums'))) {
            (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation, 'Latinums', isset($Data['Latinums']));
        } else {
            (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, 'Latinums', isset($Data['Latinums']));
        }
        if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, 'Graecums'))) {
            (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation, 'Graecums', isset($Data['Graecums']));
        } else {
            (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, 'Graecums', isset($Data['Graecums']));
        }
        if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, 'Hebraicums'))) {
            (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation, 'Hebraicums', isset($Data['Hebraicums']));
        } else {
            (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, 'Hebraicums', isset($Data['Hebraicums']));
        }

        if (isset($Data['ForeignLanguages'])) {
            foreach ($Data['ForeignLanguages'] as $ranking => $value) {
                $identifier = 'ForeignLanguages' . $ranking;
                if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, $identifier))) {
                    (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation, $identifier, $value);
                } else {
                    (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, $identifier, $value);
                }
            }
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Prepare/Diploma/Abitur/Preview', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId(),
                'Route' => 'Diploma'
            ));
    }
}