<?php
namespace SPHERE\Application\Education\Certificate\Prepare;

use DateTime;
use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Certificate\Prepare\Service\Data;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGradeType;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareInformation;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Setup;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 * @package SPHERE\Application\Education\Certificate\Prepare
 */
class Service extends ServiceTemplateInformation
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8): string
    {
        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return false|TblPrepareCertificate
     */
    public function getPrepareById($Id)
    {
        return (new Data($this->getBinding()))->getPrepareById($Id);
    }

    /**
     *
     * @return false|TblPrepareCertificate[]
     */
    public function getPrepareAll()
    {
        return (new Data($this->getBinding()))->getPrepareAll();
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblPrepareCertificate[]
     */
    public function getPrepareAllByYear(TblYear $tblYear)
    {
        $resultList = array();
        $entityList = $this->getPrepareAll();
        if ($entityList) {
            foreach ($entityList as $tblPrepare) {
                if (($tblDivision = $tblPrepare->getServiceTblDivision())
                    && $tblDivision->getServiceTblYear()
                    && $tblDivision->getServiceTblYear()->getId() == $tblYear->getId()
                ) {
                    $resultList[] = $tblPrepare;
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return false|TblPrepareStudent
     */
    public function getPrepareStudentBy(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson, bool $isForced = false)
    {
        return (new Data($this->getBinding()))->getPrepareStudentBy($tblPrepare, $tblPerson, $isForced);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblCertificate|null $tblCertificate
     *
     * @return false|TblPrepareStudent[]
     */
    public function getPrepareStudentAllByPerson(TblPerson $tblPerson, TblCertificate $tblCertificate = null)
    {
        return (new Data($this->getBinding()))->getPrepareStudentAllByPerson($tblPerson, $tblCertificate);
    }

    /**
     * @param $Id
     *
     * @return false|TblPrepareStudent
     */
    public function getPrepareStudentById($Id)
    {
        return (new Data($this->getBinding()))->getPrepareStudentById($Id);
    }

    /**
     * @param bool $IsApproved
     * @param bool $IsPrinted
     *
     * @return false|TblPrepareStudent[]
     */
    public function getPrepareStudentAllWhere(bool $IsApproved = false, bool $IsPrinted = false)
    {
        return (new Data($this->getBinding()))->getPrepareStudentAllWhere($IsApproved, $IsPrinted);
    }

    /**
     * Kopf-Note
     *
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblGradeType $tblGradeType
     *
     * @return false|TblPrepareGrade
     */
    public function getPrepareGradeByGradeType(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblGradeType $tblGradeType
    ) {
        return (new Data($this->getBinding()))->getPrepareGradeByGradeType($tblPrepare, $tblPerson, $tblGradeType);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     *
     * @return false|TblPrepareInformation[]
     */
    public function getPrepareInformationAllByPerson(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson)
    {
        return (new Data($this->getBinding()))->getPrepareInformationAllByPerson($tblPrepare, $tblPerson);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return false|TblPrepareInformation[]
     */
    public function getPrepareInformationAllByPrepare(TblPrepareCertificate $tblPrepare)
    {
        return (new Data($this->getBinding()))->getPrepareInformationAllByPrepare($tblPrepare);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param $Field
     *
     * @return false|TblPrepareInformation
     */
    public function getPrepareInformationBy(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson, $Field)
    {
        return (new Data($this->getBinding()))->getPrepareInformationBy($tblPrepare, $tblPerson, $Field);
    }

    /**
     * @param IFormInterface $Stage
     * @param TblPrepareCertificate $tblPrepare
     * @param $Data
     * @param $Route
     *
     * @return IFormInterface|string
     */
    public function updatePrepareSetSigner(
        IFormInterface $Stage,
        TblPrepareCertificate $tblPrepare,
        $Data,
        $Route
    ) {
        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Stage;
        }

        $tblPerson = Person::useService()->getPersonById($Data);
        $this->updatePrepareData($tblPrepare, $tblPerson ?: null, $tblPrepare->getIsPrepared());

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Unterzeichner wurde ausgewählt.')
            . new Redirect('/Education/Certificate/Prepare/Prepare/Preview', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId(),
                'Route' => $Route
            ));
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblCertificate|null $tblCertificate
     *
     * @return string
     */
    public function updatePrepareStudentSetCertificate(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblCertificate $tblCertificate = null
    ): string {
        if (($tblPrepareStudent = $this->getPrepareStudentBy($tblPrepare, $tblPerson))) {
            (new Data($this->getBinding()))->updatePrepareStudent(
                $tblPrepareStudent,
                $tblCertificate ?: null,
                $tblPrepareStudent->isApproved(),
                $tblPrepareStudent->isPrinted(),
                $tblPrepareStudent->getExcusedDays(),
                $tblPrepareStudent->getExcusedDaysFromLessons(),
                $tblPrepareStudent->getUnexcusedDays(),
                $tblPrepareStudent->getUnexcusedDaysFromLessons(),
                $tblPrepareStudent->getServiceTblPersonSigner() ? $tblPrepareStudent->getServiceTblPersonSigner() : null
            );
        } else {
            (new Data($this->getBinding()))->createPrepareStudent(
                $tblPrepare,
                $tblPerson,
                $tblCertificate ?: null
            );
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Zeugnisvorlage wurde ausgewählt.')
            . new Redirect('/Education/Certificate/Prepare/Certificate', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId(),
                'PersonId' => $tblPerson->getId()
            ));
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return bool
     */
    public function updatePrepareStudentSetPrinted(TblPrepareStudent $tblPrepareStudent): bool
    {
        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())) {
            return (new Data($this->getBinding()))->updatePrepareStudent(
                $tblPrepareStudent,
                $tblCertificate,
                $tblPrepareStudent->isApproved(),
                true,
                $tblPrepareStudent->getExcusedDays(),
                $tblPrepareStudent->getExcusedDaysFromLessons(),
                $tblPrepareStudent->getUnexcusedDays(),
                $tblPrepareStudent->getUnexcusedDaysFromLessons(),
                $tblPrepareStudent->getServiceTblPersonSigner() ? $tblPrepareStudent->getServiceTblPersonSigner() : null
            );
        } else {
            return false;
        }
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPrepareCertificate $tblPrepare
     * @param $Route
     * @param $Data
     * @param $CertificateList
     * @param $nextPage
     *
     * @return IFormInterface|string
     */
    public function updatePrepareInformationList(
        ?IFormInterface $Stage,
        TblPrepareCertificate $tblPrepare,
        $Route,
        $Data,
        $CertificateList,
        $nextPage = null
    ) {
        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $Stage;
        }

        foreach ($Data as $prepareStudentId => $array) {
            if (($tblPrepareStudent = $this->getPrepareStudentById($prepareStudentId))
                && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                && is_array($array)
            ) {
                /*
                 * Fehlzeiten
                 *
                 * bei den Fehlzeiten ist CertificateList leer, da nicht die Template-Informationen gezogen werden
                 *
                 */
                if (isset($array['ExcusedDays']) && isset($array['UnexcusedDays'])) {
                    // Fehlzeiten werden in der Zeugnisvorbereitung gepflegt
                    (new Data($this->getBinding()))->updatePrepareStudent(
                        $tblPrepareStudent,
                        $tblPrepareStudent->getServiceTblCertificate() ?: null,
                        $tblPrepareStudent->isApproved(),
                        $tblPrepareStudent->isPrinted(),
                        $array['ExcusedDays'] === '' ? null : $array['ExcusedDays'],
                        $tblPrepareStudent->getExcusedDaysFromLessons(),
                        $array['UnexcusedDays'] === '' ? null : $array['UnexcusedDays'],
                        $tblPrepareStudent->getUnexcusedDaysFromLessons(),
                        $tblPrepareStudent->getServiceTblPersonSigner() ? $tblPrepareStudent->getServiceTblPersonSigner() : null
                    );
                } elseif (isset($array['ExcusedDaysFromLessons']) || isset($array['UnexcusedDaysFromLessons'])) {
                    // Fehlzeiten werden im Klassenbuch gepflegt
                    (new Data($this->getBinding()))->updatePrepareStudent(
                        $tblPrepareStudent,
                        $tblPrepareStudent->getServiceTblCertificate() ?: null,
                        $tblPrepareStudent->isApproved(),
                        $tblPrepareStudent->isPrinted(),
                        $tblPrepareStudent->getExcusedDays(),
                        $array['ExcusedDaysFromLessons'] ?? $tblPrepareStudent->getExcusedDaysFromLessons(),
                        $tblPrepareStudent->getUnexcusedDays(),
                        $array['UnexcusedDaysFromLessons'] ?? $tblPrepareStudent->getUnexcusedDaysFromLessons(),
                        $tblPrepareStudent->getServiceTblPersonSigner() ? $tblPrepareStudent->getServiceTblPersonSigner() : null
                    );
                }

                if (isset($CertificateList[$tblPerson->getId()])) {
                    /** @var Certificate $Certificate */
                    $Certificate = $CertificateList[$tblPerson->getId()];
                    /*
                     * Sonstige Informationen
                     */
                    foreach ($array as $field => $value) {
                        if ($field == 'ExcusedDays' || $field == 'UnexcusedDays'
                            || $field == 'ExcusedDaysFromLessons' || $field == 'UnexcusedDaysFromLessons'
                        ) {
                            continue;
                        } else {
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
//                            } elseif ($field == 'FoesAbsText' // SSW-1685 Auswahl soll aktuell nicht verfügbar sein, bis aufweiteres aufheben
//                                && method_exists($Certificate, 'selectValuesFoesAbsText')
//                            ) {
//                                $value = $Certificate->selectValuesFoesAbsText()[$value];
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
                                if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, $field))) {
                                    (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation, $field, $value);
                                } else {
                                    (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, $field, $value);
                                }

                            } elseif (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, $field))) {
                                // auf Leer zurücksetzen
                                (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation, $field, $value);
                            }
                        }
                    }
                }
            }
        }

        if ($nextPage == null) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Informationen wurden gespeichert.')
                . new Redirect('/Education/Certificate/Prepare/Prepare/Preview', Redirect::TIMEOUT_SUCCESS, array(
                    'PrepareId' => $tblPrepare->getId(),
                    'Route' => $Route
                ));
        } else {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Informationen wurden gespeichert.')
                . new Redirect('/Education/Certificate/Prepare/Prepare/Setting',
                    Redirect::TIMEOUT_SUCCESS,
                   array(
                        'PrepareId' => $tblPrepare->getId(),
                        'Route' => $Route,
                        'IsNotGradeType' => true,
                        'Page' => $nextPage
                    )
                );
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblGenerateCertificate|null $tblGenerateCertificate
     * @param TblPerson|null $tblPersonSigner
     *
     * @return TblPrepareCertificate
     */
    public function createPrepareData(
        TblDivisionCourse $tblDivisionCourse,
        ?TblGenerateCertificate $tblGenerateCertificate,
        ?TblPerson $tblPersonSigner
    ): TblPrepareCertificate {
        return (new Data($this->getBinding()))->createPrepare($tblDivisionCourse, $tblGenerateCertificate, $tblPersonSigner);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson|null $tblPersonSigner
     * @param bool $IsPrepared
     *
     * @return bool
     */
    public function updatePrepareData(
        TblPrepareCertificate $tblPrepare,
        ?TblPerson $tblPersonSigner,
        bool $IsPrepared
    ): bool {
        return (new Data($this->getBinding()))->updatePrepare($tblPrepare, $tblPersonSigner, $IsPrepared);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return bool
     */
    public function updatePrepareResetRemove(
        TblPrepareCertificate $tblPrepare
    ): bool {
        return (new Data($this->getBinding()))->updatePrepareResetRemove($tblPrepare);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblGradeType $tblGradeType
     * @param $Grade
     *
     * @return TblPrepareGrade
     */
    public function updatePrepareGradeForBehavior(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblGradeType $tblGradeType,
        $Grade
    ): TblPrepareGrade {
        return (new Data($this->getBinding()))->updatePrepareGradeForBehavior($tblPrepare, $tblPerson, $tblGradeType, $Grade);
    }

    /**
     * @param $Data
     */
    public function createPrepareStudentSetBulkTemplates($Data)
    {
        (new Data($this->getBinding()))->createPrepareStudentSetBulkTemplates($Data);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param $Content
     * @param Certificate|null $Certificate
     */
    public function updatePrepareInformationDataList(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        $Content,
        Certificate $Certificate = null
    ) {
        $personId = $tblPerson->getId();
        if (isset($Content['P' . $personId]['Input']) && is_array($Content['P' . $personId]['Input'])) {
            foreach ($Content['P' . $personId]['Input'] as $field => $value) {
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
//                } elseif ($field == 'FoesAbsText' // SSW-1685 Auswahl soll aktuell nicht verfügbar sein, bis aufweiteres aufheben
//                    && method_exists($Certificate, 'selectValuesFoesAbsText')
//                ) {
//                    $value = $Certificate->selectValuesFoesAbsText()[$value];
                }

                if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepare, $tblPerson, $field))) {
                    (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation, $field, $value);
                } else {
                    (new Data($this->getBinding()))->createPrepareInformation($tblPrepare, $tblPerson, $field, $value);
                }
            }
        }
    }

    /**
     * @param TblGenerateCertificate $tblGenerateCertificate
     *
     * @return false|TblPrepareCertificate[]
     */
    public function getPrepareAllByGenerateCertificate(TblGenerateCertificate $tblGenerateCertificate)
    {
        return (new Data($this->getBinding()))->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
    }

    /**
     * @param IFormInterface|null $form
     * @param TblPrepareCertificate $tblPrepare
     * @param TblGradeType|null $tblGradeType
     * @param TblGradeType|null $tblNextGradeType
     * @param $Route
     * @param $Data
     *
     * @return IFormInterface|string|null
     */
    public function updatePrepareBehaviorGrades(
        ?IFormInterface $form,
        TblPrepareCertificate $tblPrepare,
        ?TblGradeType $tblGradeType,
        ?TblGradeType $tblNextGradeType,
        $Route,
        $Data
    ) {
        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $form;
        }

        $error = false;

        foreach ($Data as $gradeTypeId => $value) {
            if (trim($value) !== '') {
                if (!preg_match('!^([1-5]{1}|[1-4]{1}[+-]{1})$!is', trim($value))) {
                    $error = true;
                    break;
                }
            }
        }

        if ($error) {
            $form->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                    'Nicht alle eingegebenen Zensuren befinden sich im Wertebereich (1-5). Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));

            return $form;
        } else {
            foreach ($Data as $prepareStudentId => $value) {
                if (($tblPrepareStudent = $this->getPrepareStudentById($prepareStudentId))
                    && ($tblPrepareItem = $tblPrepareStudent->getTblPrepareCertificate())
                    && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                ) {
                    if ($value != -1) {
                        if (trim($value) === '') {
                            // keine leere Kopfnoten anlegen, nur falls eine Kopfnote vorhanden ist
                            // direktes löschen ist ungünstig, da beim nächsten Speichern wieder der Durchschnitt eingetragen würde
                            if ($this->getPrepareGradeByGradeType($tblPrepareItem, $tblPerson, $tblGradeType)) {
                                Prepare::useService()->updatePrepareGradeForBehavior(
                                    $tblPrepareItem, $tblPerson, $tblGradeType, trim($value)
                                );
                            }
                        } else {
                            Prepare::useService()->updatePrepareGradeForBehavior(
                                $tblPrepareItem, $tblPerson, $tblGradeType, trim($value)
                            );
                        }
                    }
                }
            }

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Kopfnoten wurden gespeichert.')
                . new Redirect('/Education/Certificate/Prepare/Prepare/Setting',
                    Redirect::TIMEOUT_SUCCESS,
                    $tblNextGradeType
                        ? array(
                            'PrepareId' => $tblPrepare->getId(),
                            'Route' => $Route,
                            'GradeTypeId' => $tblNextGradeType->getId()
                        )
                        : array(
                            'PrepareId' => $tblPrepare->getId(),
                            'Route' => $Route,
                            'IsNotGradeType' => true
                        )
                );
        }
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return bool
     */
    public function updatePrepareStudentSetApproved(TblPrepareStudent $tblPrepareStudent): bool
    {
        return (new Data($this->getBinding()))->updatePrepareStudentSetApproved($tblPrepareStudent);
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return bool
     */
    public function updatePrepareStudentResetApproved(TblPrepareStudent $tblPrepareStudent): bool
    {
        $useClassRegisterForAbsence = ($tblSettingAbsence = ConsumerSetting::useService()->getSetting('Education', 'ClassRegister', 'Absence', 'UseClassRegisterForAbsence'))
            && $tblSettingAbsence->getValue();

        return (new Data($this->getBinding()))->updatePrepareStudent(
            $tblPrepareStudent,
            $tblPrepareStudent->getServiceTblCertificate() ?: null,
            false,
            false,
            // Fehlzeiten zurücksetzen, bei automatischer Übernahme der Fehlzeiten
            $useClassRegisterForAbsence ? null : $tblPrepareStudent->getExcusedDays(),
            $tblPrepareStudent->getExcusedDaysFromLessons(),
            $useClassRegisterForAbsence ? null : $tblPrepareStudent->getUnexcusedDays(),
            $tblPrepareStudent->getUnexcusedDaysFromLessons(),
            $tblPrepareStudent->getServiceTblPersonSigner() ? $tblPrepareStudent->getServiceTblPersonSigner() : null
        );
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return bool
     */
    public function updatePrepareStudentListSetApproved(TblPrepareCertificate $tblPrepare): bool
    {
        return (new Data($this->getBinding()))->updatePrepareStudentListSetApproved($tblPrepare);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return bool
     */
    public function updatePrepareStudentListResetApproved(TblPrepareCertificate $tblPrepare): bool
    {
        return (new Data($this->getBinding()))->updatePrepareStudentListResetApproved($tblPrepare);
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     *
     * @return bool
     */
    public function isPreparePrinted(TblPrepareCertificate $tblPrepareCertificate): bool
    {
        return (new Data($this->getBinding()))->isPreparePrinted($tblPrepareCertificate);
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     *
     * @return false|TblPrepareStudent[]
     */
    public function getPrepareStudentAllByPrepare(TblPrepareCertificate $tblPrepareCertificate)
    {
        return (new Data($this->getBinding()))->getPrepareStudentAllByPrepare($tblPrepareCertificate);
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function isGradeTypeUsed(TblGradeType $tblGradeType): bool
    {
        return (new Data($this->getBinding()))->isGradeTypeUsed($tblGradeType);
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param $ranking
     * @param $grade
     * @param bool $isSelected
     * @param bool $isLocked
     *
     * @return TblPrepareAdditionalGrade
     */
    public function createPrepareAdditionalGrade(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        $ranking,
        $grade,
        bool $isSelected = false,
        bool $isLocked = false
    ): TblPrepareAdditionalGrade {
        return (new Data($this->getBinding()))->createPrepareAdditionalGrade($tblPrepareCertificate, $tblPerson,
            $tblSubject, $tblPrepareAdditionalGradeType, $ranking, $grade, $isSelected, $isLocked);
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param TblPrepareAdditionalGradeType|null $tblPrepareAdditionalGradeType
     *
     * @return false|TblPrepareAdditionalGrade[]
     */
    public function getPrepareAdditionalGradeListBy(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType = null
    ) {
        return (new Data($this->getBinding()))->getPrepareAdditionalGradeListBy($tblPrepareCertificate, $tblPerson, $tblPrepareAdditionalGradeType);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return TblSubject[]|false
     */
    public function getAutoDroppedSubjects(TblPerson $tblPerson, TblYear $tblYear)
    {
        $tblYearPrevious = false;
        if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListByPerson($tblPerson))) {
            foreach ($tblStudentEducationList as $tblStudentEducation) {
                if ($tblStudentEducation->getLeaveDateTime() == null
                    && $tblStudentEducation->getLevel() == 9
                    && ($tblYearPrevious = $tblStudentEducation->getServiceTblYear())
                ) {
                    break;
                }
            }
        }

        if ($tblYearPrevious
            && ($tblSubjectListPrevious = DivisionCourse::useService()->getSubjectListByStudentAndYear($tblPerson, $tblYearPrevious))
            && ($tblSubjectListCurrent = DivisionCourse::useService()->getSubjectListByStudentAndYear($tblPerson, $tblYear))
        ) {
            $subjectNameListCurrent = array();
            foreach ($tblSubjectListCurrent as $tblSubjectCurrent) {
                $subjectNameListCurrent[$tblSubjectCurrent->getName()] = $tblSubjectCurrent;
            }

            foreach ($tblSubjectListPrevious as $tblSubjectPrevious) {
                // Wahlbereich ignorieren
                if (Subject::useService()->isOrientation($tblSubjectPrevious)) {
                    continue;
                }

                // Fremdsprache ignorieren
                if (Subject::useService()->existsCategorySubject(Subject::useService()->getCategoryByIdentifier('FOREIGNLANGUAGE'), $tblSubjectPrevious)) {
                    continue;
                }

                // SSWHD-2752 HOGA hatte in der Klasse 9 WTH (Stundentafel) und WTH1 bzw. WTH2 (individuelles Fach)
                if (!isset($subjectNameListCurrent[$tblSubjectPrevious->getName()])) {
                    $resulList[$tblSubjectPrevious->getName()] = $tblSubjectPrevious;
                }
            }
        }

        return empty($resulList) ? false : $resulList;
    }

    /**
     * @param $Id
     *
     * @return false|TblPrepareAdditionalGrade
     */
    public function getPrepareAdditionalGradeById($Id)
    {
        return (new Data($this->getBinding()))->getPrepareAdditionalGradeById($Id);
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param bool $isForced
     *
     * @return false|TblPrepareAdditionalGrade
     */
    public function getPrepareAdditionalGradeBy(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        bool $isForced = false
    ) {
        return (new Data($this->getBinding()))->getPrepareAdditionalGradeBy(
            $tblPrepareCertificate,
            $tblPerson,
            $tblSubject,
            $tblPrepareAdditionalGradeType,
            $isForced
        );
    }

    /**
     * @param TblPrepareAdditionalGrade $tblPrepareAdditionalGrade
     *
     * @return bool
     */
    public function destroyPrepareAdditionalGrade(TblPrepareAdditionalGrade $tblPrepareAdditionalGrade): bool
    {
        return (new Data($this->getBinding()))->destroyPrepareAdditionalGrade($tblPrepareAdditionalGrade);
    }

    /**
     * @param TblPrepareAdditionalGrade $tblPrepareAdditionalGrade
     * @param $Ranking
     *
     * @return bool
     */
    public function updatePrepareAdditionalGradeRanking(TblPrepareAdditionalGrade $tblPrepareAdditionalGrade, $Ranking): bool
    {
        return (new Data($this->getBinding()))->updatePrepareAdditionalGradeRanking($tblPrepareAdditionalGrade, $Ranking);
    }

    /**
     * @param $Identifier
     *
     * @return bool|TblPrepareAdditionalGradeType
     */
    public function getPrepareAdditionalGradeTypeByIdentifier($Identifier)
    {
        return (new Data($this->getBinding()))->getPrepareAdditionalGradeTypeByIdentifier($Identifier);
    }

    /**
     * @param $Id
     *
     * @return bool|TblPrepareAdditionalGradeType
     */
    public function getPrepareAdditionalGradeTypeById($Id)
    {
        return (new Data($this->getBinding()))->getPrepareAdditionalGradeTypeById($Id);
    }

    /**
     * soft remove
     * @param TblPrepareCertificate $tblPrepareCertificate
     *
     * @return bool
     */
    public function destroyPrepareCertificate(TblPrepareCertificate $tblPrepareCertificate): bool
    {
        return (new Data($this->getBinding()))->destroyPrepareCertificate($tblPrepareCertificate);
    }

    /**
     * @deprecated use Term::useService()->setYearButtonList()
     *
     * @param $Route
     * @param $IsAllYears
     * @param $IsGroup
     * @param $YearId
     * @param $tblYear
     * @param bool $HasAllYears
     *
     * @return array
     */
    public function setYearGroupButtonList($Route, $IsAllYears, $IsGroup, $YearId, &$tblYear, $HasAllYears = true)
    {

        $tblYear = false;
        $tblYearList = Term::useService()->getYearByNow();
        if ($YearId) {
            $tblYear = Term::useService()->getYearById($YearId);
        } elseif (!$IsAllYears && !$IsGroup && $tblYearList) {
            $tblYear = end($tblYearList);
        }

        $buttonList = array();
        if ($tblYearList) {
            $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('DisplayName');
            /** @var TblYear $tblYearItem */
            foreach ($tblYearList as $tblYearItem) {
                if ($tblYear && $tblYear->getId() == $tblYearItem->getId()) {
                    $buttonList[] = (new Standard(new Info(new Bold($tblYearItem->getDisplayName())),
                        $Route, new Edit(), array('YearId' => $tblYearItem->getId())));
                } else {
                    $buttonList[] = (new Standard($tblYearItem->getDisplayName(), $Route,
                        null, array('YearId' => $tblYearItem->getId())));
                }
            }

            // Fachlehrer sollen nur Zugriff auf Leistungsüberprüfungen aller aktuellen Schuljahre haben
            // #SSW-1169 Anlegen von Leistungsüberprüfung von noch nicht erreichten Schuljahren verhindern
            if ($HasAllYears) {
                if ($IsAllYears) {
                    $buttonList[] = (new Standard(new Info(new Bold('Alle Schuljahre')),
                        $Route, new Edit(), array('IsAllYears' => true)));
                }  else {
                    $buttonList[] = (new Standard('Alle Schuljahre', $Route, null,
                        array('IsAllYears' => true)));
                }
            }

            if ($IsGroup) {
                $buttonList[] = (new Standard(new Info(new Bold('Gruppen')),
                    $Route, new Edit(), array('IsGroup' => true)));
            }  else {
                $buttonList[] = (new Standard('Gruppen', $Route, null,
                    array('IsGroup' => true)));
            }

            // Abstandszeile
            $buttonList[] = new Container('&nbsp;');
        }

        return $buttonList;
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param $ranking
     *
     * @return false|TblPrepareAdditionalGrade
     * @throws \Exception
     */
    public function getPrepareAdditionalGradeByRanking(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        $ranking
    ) {
        return (new Data($this->getBinding()))->getPrepareAdditionalGradeByRanking(
            $tblPrepareCertificate,
            $tblPerson,
            $tblPrepareAdditionalGradeType,
            $ranking
        );
    }

    /**
     * @param TblPrepareAdditionalGrade $tblPrepareAdditionalGrade
     * @param $grade
     * @param bool $isSelected
     *
     * @return bool
     */
    public function updatePrepareAdditionalGrade(TblPrepareAdditionalGrade $tblPrepareAdditionalGrade, $grade, bool $isSelected = false): bool
    {
        (new Data($this->getBinding()))->updatePrepareAdditionalGrade($tblPrepareAdditionalGrade, $grade, $isSelected);

        return false;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param bool $useClassRegisterForAbsence
     *
     * @return array[]
     */
    public function getCertificateInformationPages(TblPrepareCertificate $tblPrepare, bool $useClassRegisterForAbsence): array
    {
        $CertificateHasAbsenceList = [];
        $StudentHasAbsenceLessonsList = [];
        $tblCertificateList = $this->getCertificateListByPrepare(
            $tblPrepare,
            $useClassRegisterForAbsence,
            $CertificateHasAbsenceList,
            $StudentHasAbsenceLessonsList
        );

        $informationPageList = array();
        $pageList = array();
        foreach ($tblCertificateList as $tblCertificate) {
            if (($tblCertificateInformationList = Generator::useService()->getCertificateInformationListByCertificate($tblCertificate))) {
                foreach ($tblCertificateInformationList as $tblCertificateInformation) {
                    $page = $tblCertificateInformation->getPage();
                    if ($page > 1) {
                        $informationPageList[$tblCertificate->getId()][$page][$tblCertificateInformation->getFieldName()] = $tblCertificateInformation->getFieldName();
                        $pageList[$page] = $page;
                    }
                }
            }
        }

        // gibt es Fehlzeiten mit Unterrichtseinheiten ? -> Fehlzeiten-Tab nicht erforderlich bei Eintragung der Fehlzeiten im Klassenbuch
        if ($useClassRegisterForAbsence && empty($StudentHasAbsenceLessonsList)) {
            $CertificateHasAbsenceList = [];
        }

        // Existieren Zeugnis-Vorlagen mit Fehlzeiten, wird ein neuer "Tab" für die Eingabe der Fehlzeiten erzeugt, Ausnahme sie oben drüber
        if (!empty($CertificateHasAbsenceList)) {
            $pageList['Absence'] = 'Absence';
        }

        return array($informationPageList, $pageList, $CertificateHasAbsenceList, $StudentHasAbsenceLessonsList);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param $useClassRegisterForAbsence
     * @param $CertificateHasAbsenceList
     * @param $StudentHasAbsenceLessonsList
     *
     * @return TblCertificate[]
     */
    private function getCertificateListByPrepare(
        TblPrepareCertificate $tblPrepare,
        $useClassRegisterForAbsence,
        &$CertificateHasAbsenceList,
        &$StudentHasAbsenceLessonsList
    ): array {
        $tblCertificateList = array();
        if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && (($tblStudentList = $tblDivisionCourse->getStudentsWithSubCourses()))
        ) {
            if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                && $tblGenerateCertificate->getAppointedDateForAbsence()
            ) {
                $tillDateAbsence = new DateTime($tblGenerateCertificate->getAppointedDateForAbsence());
            } else {
                $tillDateAbsence = new DateTime($tblPrepare->getDate());
            }
            list($startDateAbsence) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);

            foreach ($tblStudentList as $tblPerson) {
                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                    && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                ) {
                    if (!isset($tblCertificateList[$tblCertificate->getId()])) {
                        $tblCertificateList[$tblCertificate->getId()] = $tblCertificate;

                        // Zeugnis-Vorlage besitzt Fehlzeiten
                        if ($this->hasCertificateAbsence($tblCertificate, $tblPerson)) {
                            $CertificateHasAbsenceList[$tblCertificate->getId()] = $tblCertificate;
                        }
                    }

                    // Prüfung ob Fehlzeiten-Stunden erfasst wurden, nur erforderlich bei Pflege der Fehlzeiten im Klassenbuch
                    if ($useClassRegisterForAbsence) {
                        if (Absence::useService()->getHasPersonAbsenceLessons($tblPerson, $startDateAbsence, $tillDateAbsence, TblAbsence::VALUE_STATUS_EXCUSED)) {
                            $StudentHasAbsenceLessonsList[$tblPerson->getId()][TblAbsence::VALUE_STATUS_EXCUSED] = true;
                        }
                        if (Absence::useService()->getHasPersonAbsenceLessons($tblPerson, $startDateAbsence, $tillDateAbsence, TblAbsence::VALUE_STATUS_UNEXCUSED)) {
                            $StudentHasAbsenceLessonsList[$tblPerson->getId()][TblAbsence::VALUE_STATUS_UNEXCUSED] = true;
                        }
                    }
                }
            }
        }

        return $tblCertificateList;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblPerson|null $tblPerson
     *
     * @return bool
     */
    public function hasCertificateAbsence(TblCertificate $tblCertificate, TblPerson $tblPerson = null): bool
    {
        $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
        if (class_exists($CertificateClass)) {
            /** @var Certificate $Certificate */
            $Certificate = new $CertificateClass();

            // create Certificate with Placeholders
            $pageList[$tblPerson ? $tblPerson->getId() : 0] = $Certificate->buildPages($tblPerson);
            $Certificate->createCertificate(array(), $pageList);

            if (($PlaceholderList = $Certificate->getCertificate()->getPlaceholder())) {
                foreach ($PlaceholderList as $PlaceHolder) {
                    if (strpos($PlaceHolder, 'Input.Missing')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblPrepareCertificate[]
     */
    public function getPrepareAllByDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getPrepareAllByDivisionCourse($tblDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblGenerateCertificate $tblGenerateCertificate
     *
     * @return false|TblPrepareCertificate
     */
    public function getForcedPrepareByDivisionCourseAndGenerateCertificate(TblDivisionCourse $tblDivisionCourse, TblGenerateCertificate $tblGenerateCertificate)
    {
        return (new Data($this->getBinding()))->getForcedPrepareByDivisionCourseAndGenerateCertificate($tblDivisionCourse, $tblGenerateCertificate);
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     *
     * @return false|TblPrepareGrade[]
     */
    public function getBehaviorGradeAllByPrepareCertificateAndPerson(TblPrepareCertificate $tblPrepareCertificate, TblPerson $tblPerson)
    {
        return (new Data($this->getBinding()))->getBehaviorGradeAllByPrepareCertificateAndPerson($tblPrepareCertificate, $tblPerson);
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     *
     * @return false|TblPrepareGrade[]
     */
    public function getBehaviorGradeAllByPrepareCertificate(TblPrepareCertificate $tblPrepareCertificate)
    {
        return (new Data($this->getBinding()))->getBehaviorGradeAllByPrepareCertificate($tblPrepareCertificate);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblCertificateType $tblCertificateType
     *
     * @return TblPrepareStudent[]|false
     */
    public function getPrepareStudentListByPersonAndCertificateType(TblPerson $tblPerson, TblCertificateType $tblCertificateType)
    {
        return (new Data($this->getBinding()))->getPrepareStudentListByPersonAndCertificateType($tblPerson, $tblCertificateType);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblCertificateType $tblCertificateType
     * @param TblYear $tblYear
     *
     * @return TblPrepareStudent[]|false
     */
    public function getPrepareStudentListByPersonAndCertificateTypeAndYear(
        TblPerson $tblPerson, TblCertificateType $tblCertificateType, TblYear $tblYear, $sort = 'DESC'
    ) {
        return (new Data($this->getBinding()))->getPrepareStudentListByPersonAndCertificateTypeAndYear($tblPerson, $tblCertificateType, $tblYear, $sort);
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function createEntityListBulk(array $tblEntityList): bool
    {
        return (new Data($this->getBinding()))->createEntityListBulk($tblEntityList);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return bool
     */
    public function getIsLeaveOrDiplomaStudent(TblPerson $tblPerson, TblYear $tblYear): bool
    {
        // gedrucktes Abgangszeugnis vorhanden
        if (($tblLeaveStudent = $this->getLeaveStudentBy($tblPerson, $tblYear))
            && $tblLeaveStudent->isPrinted()
        ) {
            return true;
        // gedrucktes Abschlusszeugnis vorhanden
        } elseif (($tblCertificateType = Generator::useService()->getCertificateTypeByIdentifier('DIPLOMA'))
            && ($tblPrepareStudentList = Prepare::useService()->getPrepareStudentListByPersonAndCertificateType($tblPerson, $tblCertificateType))
        ) {
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                if ($tblPrepareStudent->isPrinted()
                    && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                    && ($tblYearCertificate = $tblPrepare->getYear())
                    && $tblYearCertificate->getId() == $tblYear->getId()
                ) {
                   return true;
                }
            }
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblTask $tblTask
     *
     * @return bool
     */
    public function getIsAppointedDateTaskGradeApproved(TblPerson $tblPerson, TblTask $tblTask): bool
    {
        return (new Data($this->getBinding()))->getIsAppointedDateTaskGradeApproved($tblPerson, $tblTask);
    }
}