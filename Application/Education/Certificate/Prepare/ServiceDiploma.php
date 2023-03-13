<?php

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Data;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareComplexExam;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
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
}