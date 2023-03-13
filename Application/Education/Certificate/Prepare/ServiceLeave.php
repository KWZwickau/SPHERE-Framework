<?php

namespace SPHERE\Application\Education\Certificate\Prepare;

use DateTime;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\GymAbgSekII;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Data;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveAdditionalGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveComplexExam;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveInformation;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGradeType;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;

abstract class ServiceLeave extends ServiceDiploma
{
    /**
     * @param $Id
     *
     * @return false|TblLeaveStudent
     */
    public function getLeaveStudentById($Id)
    {
        return (new Data($this->getBinding()))->getLeaveStudentById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return false|TblLeaveStudent
     */
    public  function getLeaveStudentBy(TblPerson $tblPerson, TblYear $tblYear)
    {
        return (new Data($this->getBinding()))->getLeaveStudentBy($tblPerson, $tblYear);
    }

    /**
     * @return false|TblLeaveStudent[]
     */
    public function  getLeaveStudentAll()
    {
        return (new Data($this->getBinding()))->getLeaveStudentAll();
    }

    /**
     * @param bool $IsApproved
     * @param bool $IsPrinted
     *
     * @return false|TblLeaveStudent[]
     */
    public function getLeaveStudentAllBy(bool $IsApproved = false, bool $IsPrinted = false)
    {
        return (new Data($this->getBinding()))->getLeaveStudentAllBy($IsApproved, $IsPrinted);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblLeaveStudent[]
     */
    public function getLeaveStudentAllByYear(TblYear $tblYear)
    {
        return (new Data($this->getBinding()))->getLeaveStudentAllByYear($tblYear);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblCertificate $tblCertificate
     * @param bool $IsApproved
     * @param bool $IsPrinted
     *
     * @return TblLeaveStudent
     */
    public function createLeaveStudent(
        TblPerson $tblPerson,
        TblYear $tblYear,
        TblCertificate $tblCertificate,
        bool $IsApproved = false,
        bool $IsPrinted = false
    ): TblLeaveStudent {
        return (new Data($this->getBinding()))->createLeaveStudent($tblPerson, $tblYear, $tblCertificate, $IsApproved, $IsPrinted);
    }

    /**
     * @param IFormInterface|null $form
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function createLeaveStudentFromForm(
        ?IFormInterface $form,
        TblPerson $tblPerson,
        TblYear $tblYear,
        $Data
    ) {
        if ($Data === null) {
            return $form;
        }

        if (!($tblCertificate = Generator::useService()->getCertificateById($Data['Certificate']))) {
            $form->setError('Data[Certificate]', new Exclamation() . ' Bitte wählen Sie eine Zeugnisvorlage aus.');

            return $form;
        }

        if (($tblLeaveStudent = $this->getLeaveStudentBy($tblPerson, $tblYear))) {
            (new Data($this->getBinding()))->updateLeaveStudentCertificate($tblLeaveStudent, $tblCertificate);
        } else {
            $tblLeaveStudent = (new Data($this->getBinding()))->createLeaveStudent($tblPerson, $tblYear, $tblCertificate);
        }

        if ($tblLeaveStudent) {
            return new Success('Die Daten wurden gespeichert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
                . new Redirect('/Education/Certificate/Prepare/Leave/Student', Redirect::TIMEOUT_SUCCESS, array(
                    'PersonId' => $tblPerson->getId(),
                    'YearId' => $tblYear->getId()
                ));
        } else {
            return new Danger('Die Daten konnten nicht gespeichert werden.', new Exclamation())
                . new Redirect('/Education/Certificate/Prepare/Leave/Student', Redirect::TIMEOUT_ERROR, array(
                    'PersonId' => $tblPerson->getId(),
                    'YearId' => $tblYear->getId()
                ));
        }
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblCertificate $tblCertificate
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateLeaveContent(
        ?IFormInterface $Form,
        TblPerson $tblPerson,
        TblYear $tblYear,
        TblCertificate $tblCertificate,
        $Data
    ) {
        if ($Data === null) {
            return $Form;
        }

        $error = false;
        if (isset($Data['InformationList']['CertificateDate']) && empty($Data['InformationList']['CertificateDate'])) {
            // bei einem 3 Stufigen Daten-Array lässt sich keine Fehlermeldung setzen
//            $Form->setError('Data[InformationList][CertificateDate]', new Exclamation() . ' Bitte geben Sie ein Datum ein.');

            $error = true;
        }

        // Datum "bis" muss größer sein als Datum "seit"
        $errorDate = false;
        if (isset($Data['InformationList']['DateFrom']) && isset($Data['InformationList']['DateTo'])) {
            $dateFrom = new DateTime($Data['InformationList']['DateFrom']);
            $dateTo = new DateTime($Data['InformationList']['DateTo']);

            if ($dateFrom > $dateTo) {

                $error = true;
                $errorDate = true;
            }
        }

        if ($error) {
            $text = $errorDate
                ? 'Das Datum für "Besucht "bis" die Berufsfachschule" muss größer sein als das Datum für "Besucht "seit" die Berufsfachschule".'
                : 'Es wurden nicht alle Pflichtfelder befüllt. Die Daten wurden nicht gespeichert.';
            $Form->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        $text, new Exclamation())
                ))));

            return $Form;
        }

        if ((!$tblLeaveStudent = $this->getLeaveStudentBy($tblPerson, $tblYear))) {
            $tblLeaveStudent = (new Data($this->getBinding()))->createLeaveStudent($tblPerson, $tblYear, $tblCertificate);
        }
        if ($tblLeaveStudent) {
            if (isset($Data['Grades'])) {
                foreach ($Data['Grades'] as $subjectId => $array){
                    if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                        if (isset($array['Grade']) && isset($array['GradeText'])){
                            if (($tblGradeText = Grade::useService()->getGradeTextById($array['GradeText']))) {
                                $value = $tblGradeText->getName();
                            } else {
                                $value = $array['Grade'];
                            }

                            if (($tblLeaveGrade = $this->getLeaveGradeBy($tblLeaveStudent, $tblSubject))) {
                                (new Data($this->getBinding()))->updateLeaveGrade($tblLeaveGrade, $value);
                            } else {
                                (new Data($this->getBinding()))->createLeaveGrade($tblLeaveStudent, $tblSubject, $value);
                            }
                        }
                    }
                }
            }

            if (isset($Data['InformationList'])) {
                foreach ($Data['InformationList'] as $field => $value) {
                    // Zeugnistext umwandeln
                    if (strpos($field, '_GradeText')) {
                        if (($tblGradeText = Grade::useService()->getGradeTextById($value))) {
                            $value = $tblGradeText->getName();
                        } else {
                            $value = '';
                        }
                    }

                    // HOGA\FosAbg
                    if (strpos($field, 'Job_Grade_Text') !== false) {
                        switch ($value) {
                            case 1: $value = 'bestanden'; break;
                            case 2: $value = 'nicht bestanden'; break;
                            default: $value = '';
                        }
                    }
                    if (strpos($field, 'Exam_Text') !== false) {
                        switch ($value) {
                            case 1: $value = 'Die Abschlussprüfung wurde erstmalig nicht bestanden. Sie kann wiederholt werden.'; break;
                            case 2: $value = 'Die Abschlussprüfung wurde endgültig nicht bestanden. Sie kann nicht wiederholt werden.'; break;
                            default: $value = '';
                        }
                    }

                    if (($tblLeaveInformation = $this->getLeaveInformationBy($tblLeaveStudent, $field))) {
                        (new Data($this->getBinding()))->updateLeaveInformation($tblLeaveInformation, $value);
                    } else {
                        (new Data($this->getBinding()))->createLeaveInformation($tblLeaveStudent, $field, $value);
                    }
                }
            }

            // Komplexe Prüfungen für Fachschulen
            if (isset($Data['ExamList'])) {
                foreach ($Data['ExamList'] as $identifierRanking => $columns) {
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

                    if (($tblLeaveComplexExam = $this->getLeaveComplexExamBy($tblLeaveStudent, $identifier, $ranking))) {
                        (new Data($this->getBinding()))->updateLeaveComplexExam($tblLeaveComplexExam, $grade,
                            $tblFirstSubject ?: null, $tblSecondSubject ?: null);
                    } else {
                        (new Data($this->getBinding()))->createLeaveComplexExam($tblLeaveStudent,$identifier, $ranking,
                            $grade, $tblFirstSubject ?: null, $tblSecondSubject ?: null);
                    }
                }
            }
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Leave/Student', Redirect::TIMEOUT_SUCCESS, array(
                'PersonId' => $tblPerson->getId(),
                'YearId' => $tblYear->getId()
            ));
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param bool $IsApproved
     * @param bool $IsPrinted
     *
     * @return bool
     */
    public function updateLeaveStudent(
        TblLeaveStudent $tblLeaveStudent,
        bool $IsApproved = false,
        bool $IsPrinted = false
    ): bool {
        return (new Data($this->getBinding()))->updateLeaveStudent($tblLeaveStudent, $IsApproved, $IsPrinted);
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param TblSubject $tblSubject
     *
     * @return false|TblLeaveGrade
     */
    public function  getLeaveGradeBy(TblLeaveStudent $tblLeaveStudent, TblSubject $tblSubject)
    {
        return (new Data($this->getBinding()))->getLeaveGradeBy($tblLeaveStudent, $tblSubject);
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     *
     * @return false|TblLeaveGrade[]
     */
    public function getLeaveGradeAllByLeaveStudent(TblLeaveStudent $tblLeaveStudent)
    {
        return (new Data($this->getBinding()))->getLeaveGradeAllByLeaveStudent($tblLeaveStudent);
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param $Field
     *
     * @return false|TblLeaveInformation
     */
    public function getLeaveInformationBy(TblLeaveStudent $tblLeaveStudent, $Field)
    {
        return (new Data($this->getBinding()))->getLeaveInformationBy($tblLeaveStudent, $Field);
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     *
     * @return false|TblLeaveInformation[]
     */
    public function getLeaveInformationAllByLeaveStudent(TblLeaveStudent $tblLeaveStudent)
    {
        return (new Data($this->getBinding()))->getLeaveInformationAllByLeaveStudent($tblLeaveStudent);
    }

    /**
     * @param IFormInterface|null $form
     * @param TblLeaveStudent $tblLeaveStudent
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateAbiturLeaveInformation(
        ?IFormInterface $form,
        TblLeaveStudent $tblLeaveStudent,
        $Data
    ) {
        if ($Data === null) {
            return $form;
        }

        $error = false;
        if (isset($Data['CertificateDate']) && empty($Data['CertificateDate'])) {
            $form->setError('Data[InformationList][CertificateDate]', new Exclamation() . ' Bitte geben Sie ein Datum ein.');
            $error = true;
        }

        if ($error) {
            $form->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        'Es wurden nicht alle Pflichtfelder befüllt. Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));

            return $form;
        }

        $leaveTerms = GymAbgSekII::getLeaveTerms();
        $midTerms = GymAbgSekII::getMidTerms();

        foreach ($Data as $field => $value) {
            if ($field == 'LeaveTerm' && isset($leaveTerms[$value])) {
                $saveValue = $leaveTerms[$value];
            } elseif ($field == 'MidTerm' && isset($midTerms[$value])) {
                $saveValue = $midTerms[$value];
            } else {
                $saveValue = $value;
            }

            if (($tblLeaveInformation = $this->getLeaveInformationBy($tblLeaveStudent, $field))) {
                (new Data($this->getBinding()))->updateLeaveInformation($tblLeaveInformation, $saveValue);
            } else {
                (new Data($this->getBinding()))->createLeaveInformation($tblLeaveStudent, $field, $saveValue);
            }
        }

        $tblPerson = $tblLeaveStudent->getServiceTblPerson();
        $tblYear = $tblLeaveStudent->getServiceTblYear();

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Leave/Student', Redirect::TIMEOUT_SUCCESS, array(
                'PersonId' => $tblPerson ? $tblPerson->getId() : 0,
                'YearId' => $tblYear ? $tblYear->getId() : 0
            ));
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param TblSubject $tblSubject
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param bool $isForced
     *
     * @return false|TblLeaveAdditionalGrade
     */
    public function getLeaveAdditionalGradeBy(
        TblLeaveStudent $tblLeaveStudent,
        TblSubject $tblSubject,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        bool $isForced = false
    ) {
        return (new Data($this->getBinding()))->getLeaveAdditionalGradeBy($tblLeaveStudent, $tblSubject, $tblPrepareAdditionalGradeType, $isForced);
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param $identifier
     * @param $ranking
     *
     * @return false|TblLeaveComplexExam
     */
    public function getLeaveComplexExamBy(
        TblLeaveStudent $tblLeaveStudent,
        $identifier,
        $ranking
    ) {
        return (new Data($this->getBinding()))->getLeaveComplexExamBy($tblLeaveStudent, $identifier, $ranking);
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     *
     * @return false|TblLeaveComplexExam[]
     */
    public function getLeaveComplexExamAllByLeaveStudent(TblLeaveStudent $tblLeaveStudent)
    {
        return (new Data($this->getBinding()))->getLeaveComplexExamAllByLeaveStudent($tblLeaveStudent);
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblLeaveStudent $tblLeaveStudent
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateLeaveStudentAbiturPoints(
        ?IFormInterface $Form,
        TblLeaveStudent $tblLeaveStudent,
        $Data
    ) {

        if ($Data === null) {
            return $Form;
        }

        $error = false;

        foreach ($Data as $midTerm => $subjects) {
            if (is_array($subjects) && $this->getPrepareAdditionalGradeTypeByIdentifier($midTerm)) {
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
                    if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                        if (($tblLeaveAdditionalGrade = $this->getLeaveAdditionalGradeBy(
                            $tblLeaveStudent,
                            $tblSubject,
                            $tblPrepareAdditionalGradeType
                        ))) {
                            (new Data($this->getBinding()))->updateLeaveAdditionalGrade(
                                $tblLeaveAdditionalGrade, $grade
                            );
                        } else {
                            if ($grade !== null && $grade !== '') {
                                (new Data($this->getBinding()))->createLeaveAdditionalGrade(
                                    $tblLeaveStudent,
                                    $tblSubject,
                                    $tblPrepareAdditionalGradeType,
                                    $grade,
                                    false
                                );
                            }
                        }
                    }
                }
            }
        }

        $tblPerson =  $tblLeaveStudent->getServiceTblPerson();
        $tblYear = $tblLeaveStudent->getServiceTblYear();

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Leave/Student', Redirect::TIMEOUT_SUCCESS, array(
                'PersonId' => $tblPerson ? $tblPerson->getId() : 0,
                'YearId' => $tblYear ? $tblYear->getId() : 0,
            ));
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param TblLeaveStudent $tblLeaveStudent
     *
     * @return void
     */
    public function copyAbiturLeaveGradesFromCertificates(
        TblPrepareStudent $tblPrepareStudent,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        TblLeaveStudent $tblLeaveStudent
    ) {
        // Zensuren von Zeugnissen
        if (($tblPreviousPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblAppointedDateTask = $tblPreviousPrepare->getServiceTblAppointedDateTask())
            && ($tblTaskGradeList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblAppointedDateTask, $tblPerson))
        ) {
            foreach ($tblTaskGradeList as $tblTaskGrade) {
                if (($tblSubject = $tblTaskGrade->getServiceTblSubject())
                    // keine leeren Zensuren kopieren
                    && $tblTaskGrade->getGrade() !== ''
                    && $tblTaskGrade->getGrade() !== null
                ) {
                    if ($tblSubject->getAcronym() == 'EN2' && ($tblSubjectTemp = Subject::useService()->getSubjectByAcronym('EN'))) {
                        $tblSubject = $tblSubjectTemp;
                    }

                    if (($tblLeaveAdditionalGrade = Prepare::useService()->getLeaveAdditionalGradeBy(
                        $tblLeaveStudent,
                        $tblSubject,
                        $tblPrepareAdditionalGradeType
                    ))) {
                        if (($tblTaskGrade->getGrade() !== $tblLeaveAdditionalGrade->getGrade())) {
                            (new Data($this->getBinding()))->updateLeaveAdditionalGrade(
                                $tblLeaveAdditionalGrade,
                                $tblTaskGrade->getGrade()
                            );
                        }
                    } else {
                        (new Data($this->getBinding()))->createLeaveAdditionalGrade(
                            $tblLeaveStudent,
                            $tblSubject,
                            $tblPrepareAdditionalGradeType,
                            $tblTaskGrade->getGrade(),
                            true
                        );
                    }
                }
            }
        }
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param TblSubject $tblSubject
     *
     * @return string
     */
    public function calcAbiturLeaveGradePointsBySubject(TblLeaveStudent $tblLeaveStudent, TblSubject $tblSubject): string
    {
        $sum = 0;
        $count = 0;
        for ($level = 11; $level < 13; $level++) {
            for ($term = 1; $term < 3; $term++) {
                $midTerm = $level . '-' . $term;
                if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier($midTerm))
                    && ($tblLeaveAdditionalGrade = $this->getLeaveAdditionalGradeBy($tblLeaveStudent, $tblSubject, $tblPrepareAdditionalGradeType))
                ) {
                    $grade = $tblLeaveAdditionalGrade->getGrade();
                    if ($grade !== null && $grade !== '') {
                        $sum += floatval($grade);
                        $count++;
                    }
                }
            }
        }

        if ($count > 0) {
            $result = ceil($sum/$count);

            return str_pad($result, 2, 0, STR_PAD_LEFT);
        } else {

            return '&ndash;';
        }
    }

    /**
     * @param $points
     *
     * @return string
     */
    public function getAbiturLeaveGradeBySubject($points): string
    {
        if ($points === '15') {
            return '1+';
        } elseif ($points === '14') {
            return '1';
        } elseif ($points === '13') {
            return '1-';
        } elseif ($points === '12') {
            return '2+';
        } elseif ($points === '11') {
            return '2';
        } elseif ($points === '10') {
            return '2-';
        } elseif ($points === '09') {
            return '3+';
        } elseif ($points === '08') {
            return '3';
        } elseif ($points === '07') {
            return '3-';
        } elseif ($points === '06') {
            return '4+';
        } elseif ($points === '05') {
            return '4';
        } elseif ($points === '04') {
            return '4-';
        } elseif ($points === '03') {
            return '5+';
        } elseif ($points === '02') {
            return '5';
        } elseif ($points === '01') {
            return '5-';
        } elseif ($points === '00') {
            return '6';
        } else {
            return '&ndash;';
        }
    }
}