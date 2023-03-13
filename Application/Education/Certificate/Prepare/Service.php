<?php
namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Certificate\Prepare\Service\Data;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGradeType;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareInformation;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Setup;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Common\Frontend\Ajax\Template\Notify;
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
use SPHERE\Common\Frontend\Message\Repository\Warning;
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
    public function setupService($doSimulation, $withData, $UTF8)
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
     * @deprecated
     *
     * @param TblDivision $tblDivision
     * @param bool $IsGradeInformation
     *
     * @return false|Service\Entity\TblPrepareCertificate[]
     */
    public function getPrepareAllByDivision(TblDivision $tblDivision, $IsGradeInformation = false)
    {
        return (new Data($this->getBinding()))->getPrepareAllByDivision($tblDivision, $IsGradeInformation);
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
    public function getPrepareStudentBy(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson, $isForced = false)
    {

        return (new Data($this->getBinding()))->getPrepareStudentBy($tblPrepare, $tblPerson, $isForced);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblCertificate $tblCertificate
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
     * Fach-Note
     *
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade
     */
    public function getPrepareGradeBySubject(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType
    ) {

        return (new Data($this->getBinding()))->getPrepareGradeBySubject(
            $tblPrepare,
            $tblPerson,
            $tblDivision,
            $tblSubject,
            $tblTestType
        );
    }

    /**
     * Kopf-Note
     *
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblTestType $tblTestType
     * @param TblGradeType $tblGradeType
     *
     * @return false|TblPrepareGrade
     */
    public function getPrepareGradeByGradeType(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblTestType $tblTestType,
        TblGradeType $tblGradeType
    ) {

        return (new Data($this->getBinding()))->getPrepareGradeByGradeType($tblPrepare, $tblPerson, $tblTestType, $tblGradeType);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade[]
     */
    public function getPrepareGradesByPrepare(
        TblPrepareCertificate $tblPrepare,
        TblTestType $tblTestType
    ) {
        return (new Data($this->getBinding()))->getPrepareGradesByPrepare($tblPrepare, $tblTestType);
    }

    /**
     * @deprecated getBehaviorGradeAllByPrepareCertificateAndPerson
     *
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblTestType $tblTestType
     * @param bool $IsForced
     *
     * @return false|TblPrepareGrade[]
     * @throws \Exception
     */
    public function getPrepareGradeAllByPerson(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblTestType $tblTestType,
        $IsForced = false
    ) {

        return (new Data($this->getBinding()))->getPrepareGradeAllByPerson($tblPrepare, $tblPerson, $tblTestType, $IsForced);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade[]
     */
    public function getPrepareGradeAllByPrepare(
        TblPrepareCertificate $tblPrepare,
        TblTestType $tblTestType
    ) {

        return (new Data($this->getBinding()))->getPrepareGradeAllByPrepare(
            $tblPrepare, $tblTestType
        );
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

        $Error = false;
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
    ) {

        if (($tblPrepareStudent = $this->getPrepareStudentBy($tblPrepare, $tblPerson))) {
            (new Data($this->getBinding()))->updatePrepareStudent(
                $tblPrepareStudent,
                $tblCertificate ? $tblCertificate : null,
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
                $tblCertificate ? $tblCertificate : null
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
    public function updatePrepareStudentSetPrinted(TblPrepareStudent $tblPrepareStudent)
    {

        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblDivision = $tblPrepareStudent->getTblPrepareCertificate()->getServiceTblDivision())
        ) {
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
     * @param string $Route
     * @param array $Data
     * @param array $CertificateList
     * @param null|integer $nextPage
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
                && ($tblPrepareItem = $tblPrepareStudent->getTblPrepareCertificate())
                && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                && is_array($array)
            ) {
                if (isset($CertificateList[$tblPerson->getId()])) {

                    /** @var Certificate $Certificate */
                    $Certificate = $CertificateList[$tblPerson->getId()];
                    $tblCertificate = $Certificate->getCertificateEntity();

                    /*
                     * Fehlzeiten
                     */
                    if ($tblCertificate) {
                        if (isset($array['ExcusedDays']) && isset($array['UnexcusedDays'])) {
                            // Fehlzeiten werden in der Zeugnisvorbereitung gepflegt
                            (new Data($this->getBinding()))->updatePrepareStudent(
                                $tblPrepareStudent,
                                $tblPrepareStudent->getServiceTblCertificate() ? $tblPrepareStudent->getServiceTblCertificate() : $tblCertificate,
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
                                $tblPrepareStudent->getServiceTblCertificate() ? $tblPrepareStudent->getServiceTblCertificate() : $tblCertificate,
                                $tblPrepareStudent->isApproved(),
                                $tblPrepareStudent->isPrinted(),
                                $tblPrepareStudent->getExcusedDays(),
                                $array['ExcusedDaysFromLessons'] ?? $tblPrepareStudent->getExcusedDaysFromLessons(),
                                $tblPrepareStudent->getUnexcusedDays(),
                                $array['UnexcusedDaysFromLessons'] ?? $tblPrepareStudent->getUnexcusedDaysFromLessons(),
                                $tblPrepareStudent->getServiceTblPersonSigner() ? $tblPrepareStudent->getServiceTblPersonSigner() : null
                            );
                        }
                    }

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
                                if (($tblGradeText = Gradebook::useService()->getGradeTextById($value))) {
                                    $value = $tblGradeText->getName();
                                } else {
                                    $value = '';
                                }
                            }

                            if (trim($value) != '') {
                                $value = trim($value);
                                if (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepareItem, $tblPerson,
                                    $field))
                                ) {
                                    (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation,
                                        $field,
                                        $value);
                                } else {
                                    (new Data($this->getBinding()))->createPrepareInformation($tblPrepareItem, $tblPerson,
                                        $field,
                                        $value);
                                }

                            } elseif (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepareItem, $tblPerson,
                                $field))
                            ) {
                                // auf Leer zurücksetzen
                                (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation,
                                    $field,
                                    $value);
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
        // todo find usage
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
        // todo find usage
        return (new Data($this->getBinding()))->updatePrepare($tblPrepare, $tblPersonSigner, $IsPrepared);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblTestType $tblTestType
     * @param TblGradeType $tblGradeType
     * @param $Grade
     *
     * @return TblPrepareGrade
     */
    public function updatePrepareGradeForBehavior(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblTestType $tblTestType,
        TblGradeType $tblGradeType,
        $Grade
    ): TblPrepareGrade {
        return (new Data($this->getBinding()))->updatePrepareGradeForBehavior($tblPrepare, $tblPerson, $tblTestType, $tblGradeType, $Grade);
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
     * @param Certificate $Certificate
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
     * @param TblGradeType $tblGradeType
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
            $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK');
            foreach ($Data as $prepareStudentId => $value) {
                if (($tblPrepareStudent = $this->getPrepareStudentById($prepareStudentId))
                    && ($tblPrepareItem = $tblPrepareStudent->getTblPrepareCertificate())
                    && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                ) {
                    if ($value != -1) {
                        if (trim($value) === '') {
                            // keine leere Kopfnoten anlegen, nur falls eine Kopfnote vorhanden ist
                            // direktes löschen ist ungünstig, da beim nächsten Speichern wieder der Durchschnitt eingetragen würde
                            if (($tblPrepareGrade = $this->getPrepareGradeByGradeType(
                                $tblPrepareItem, $tblPerson, $tblTestType, $tblGradeType
                            ))) {
                                Prepare::useService()->updatePrepareGradeForBehavior(
                                    $tblPrepareItem, $tblPerson, $tblTestType, $tblGradeType, trim($value)
                                );
                            }
                        } else {
                            Prepare::useService()->updatePrepareGradeForBehavior(
                                $tblPrepareItem, $tblPerson, $tblTestType, $tblGradeType, trim($value)
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
     * @deprecated wird jetzt direkt beim Erstellen des Zeugnisauftrags gesetzt
     *
     * Unterzeichner Klassenlehrer automatisch die angemeldete Person setzen
     *
     * @param TblPrepareStudent $tblPrepareStudent
     */
    private function setSignerFromSignedInPerson(TblPrepareStudent $tblPrepareStudent)
    {

        if (!$tblPrepareStudent->getServiceTblPersonSigner()
            && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
            && $tblGenerateCertificate->isDivisionTeacherAvailable()
        ) {
            $tblPerson = false;
            $tblAccount = Account::useService()->getAccountBySession();
            if ($tblAccount) {
                $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
                if ($tblPersonAllByAccount) {
                    $tblPerson = $tblPersonAllByAccount[0];
                }
            }

            if ($tblPerson) {
                (new Data($this->getBinding()))->updatePrepareStudent(
                    $tblPrepareStudent,
                    $tblPrepareStudent->getServiceTblCertificate() ? $tblPrepareStudent->getServiceTblCertificate() : null,
                    $tblPrepareStudent->isApproved(),
                    $tblPrepareStudent->isPrinted(),
                    $tblPrepareStudent->getExcusedDays(),
                    $tblPrepareStudent->getExcusedDaysFromLessons(),
                    $tblPrepareStudent->getUnexcusedDays(),
                    $tblPrepareStudent->getUnexcusedDaysFromLessons(),
                    $tblPerson
                );
            }
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
    public function isPreparePrinted(TblPrepareCertificate $tblPrepareCertificate)
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
    public function isGradeTypeUsed(TblGradeType $tblGradeType)
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
        $isSelected = false,
        $isLocked = false
    ) {

        return (new Data($this->getBinding()))->createPrepareAdditionalGrade($tblPrepareCertificate, $tblPerson,
            $tblSubject, $tblPrepareAdditionalGradeType, $ranking, $grade, $isSelected, $isLocked);
    }

    /**
     * @param IFormInterface $Form
     * @param $Data
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblGroup|null $tblGroup
     * @param TblPerson $tblPerson
     * @param $Route
     *
     * @return IFormInterface|string
     */
    public function createPrepareAdditionalGradeForm(
        IFormInterface $Form,
        $Data,
        TblPrepareCertificate $tblPrepareCertificate,
        TblGroup $tblGroup = null,
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
            // Todo Zenuren Wertebereich
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
                    $this->getMaxRanking(
                        $tblPrepareCertificate, $tblPerson
                    ),
                    $Data['Grade'])
                ) {
                    return new Success('Das Fach wurde erfolgreich angelegt',
                            new \SPHERE\Common\Frontend\Icon\Repository\Success())
                        . new Redirect('/Education/Certificate/Prepare/DroppedSubjects', Redirect::TIMEOUT_SUCCESS,
                            array(
                                'PrepareId' => $tblPrepareCertificate->getId(),
                                'GroupId' => $tblGroup ? $tblGroup->getId() : null,
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
     * @param TblPrepareAdditionalGradeType|null $tblPrepareAdditionalGradeType
     *
     * @return false|TblPrepareAdditionalGrade[]
     */
    public function getPrepareAdditionalGradeListBy(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType = null
    ) {

        return (new Data($this->getBinding()))->getPrepareAdditionalGradeListBy($tblPrepareCertificate,
            $tblPerson, $tblPrepareAdditionalGradeType);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function setAutoDroppedSubjects(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson)
    {

        $gradeString = '';
        $tblLastDivision = false;
        $tblCurrentDivision = $tblPrepare->getServiceTblDivision();
        if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                if (($tblDivision = $tblDivisionStudent->getTblDivision())
                    && ($tblLevel = $tblDivision->getTblLevel())
                    && (!$tblLevel->getIsChecked())
                    && ($tblLevel->getName() == '9' || $tblLevel->getName() == '09')
                ) {
                    $tblLastDivision = $tblDivision;
                    break;
                }
            }
        }

        if ($tblLastDivision
            && $tblCurrentDivision
            && ($tblLastYear = $tblLastDivision->getServiceTblYear())
            && ($tblCurrentYear = $tblCurrentDivision->getServiceTblYear())
            && ($tblLastDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear($tblPerson,
                $tblLastYear))
            && ($tblCurrentDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear($tblPerson,
                $tblCurrentYear))
        ) {
            $tblLastSubjectList = array();
            foreach ($tblLastDivisionSubjectList as $tblLastDivisionSubject) {
                if (($tblSubject = $tblLastDivisionSubject->getServiceTblSubject())) {
                    $tblLastSubjectList[$tblSubject->getId()] = $tblSubject;
                }
            }

            $tblCurrentSubjectList = array();
            foreach ($tblCurrentDivisionSubjectList as $tblCurrentDivisionSubject) {
                if (($tblSubject = $tblCurrentDivisionSubject->getServiceTblSubject())) {
                    $tblCurrentSubjectList[$tblSubject->getId()] = $tblSubject;
                }
            }

            $diffList = array();
            foreach ($tblLastSubjectList as $tblLastSubject) {
                if (!isset($tblCurrentSubjectList[$tblLastSubject->getId()])) {
                    $diffList[$tblLastSubject->getAcronym()] = $tblLastSubject;
                }
            }

            $tblLastPrepare = false;
            if (($tblLastPrepareList = Prepare::useService()->getPrepareAllByDivision($tblLastDivision))) {
                foreach ($tblLastPrepareList as $tblPrepareCertificate) {
                    if (($tblGenerateCertificate = $tblPrepareCertificate->getServiceTblGenerateCertificate())
                        && ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                        && $tblCertificateType->getIdentifier() == 'YEAR'
                    ) {
                        $tblLastPrepare = $tblPrepareCertificate;
                    }
                }
            }

            if (empty($diffList)) {
                return false;
            } else {
                /** @var TblSubject $item */
                $count = 1;
                ksort($diffList);
                if ($tblLastPrepare) {
                    foreach ($diffList as $item) {
                        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK');
                        $tblPrepareGrade = Prepare::useService()->getPrepareGradeBySubject($tblLastPrepare, $tblPerson,
                            $tblLastDivision, $item, $tblTestType);
                        if ($tblTestType
                            && $tblPrepareGrade
                            && ($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
                        ) {
                            $tblPrepareAdditionalGrade = Prepare::useService()->createPrepareAdditionalGrade(
                                $tblPrepare,
                                $tblPerson,
                                $item,
                                $tblPrepareAdditionalGradeType,
                                $count++,
                                $tblPrepareGrade->getGrade()
                            );

                            $gradeString .= $item->getAcronym() . ':' . $tblPrepareAdditionalGrade->getGrade() . ' ';
                        }
                    }
                }
            }
        }

        return $gradeString;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return string[]|false
     */
    public function getAutoDroppedSubjects(TblPerson $tblPerson, TblYear $tblYear)
    {
        $resulList = array();
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
            foreach ($tblSubjectListPrevious as $tblSubject) {
                if (!isset($tblSubjectListCurrent[$tblSubject->getId()])) {
                    $resulList[$tblSubject->getId()] = $tblSubject->getName();
                }
            }

            sort($resulList);
        }

        return empty($resulList) ? false : $resulList;
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
    ) {

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
    public function destroyPrepareAdditionalGrade(TblPrepareAdditionalGrade $tblPrepareAdditionalGrade)
    {

        return (new Data($this->getBinding()))->destroyPrepareAdditionalGrade($tblPrepareAdditionalGrade);
    }

    /**
     * @param TblPrepareAdditionalGrade $tblPrepareAdditionalGrade
     * @param $Ranking
     *
     * @return bool
     */
    public function updatePrepareAdditionalGradeRanking(TblPrepareAdditionalGrade $tblPrepareAdditionalGrade, $Ranking)
    {

        return (new Data($this->getBinding()))->updatePrepareAdditionalGradeRanking($tblPrepareAdditionalGrade,
            $Ranking);
    }

//    /**
//     * @param TblPrepareCertificate $tblPrepare
//     */
//    public function hasDiplomaCertificate(TblPrepareCertificate $tblPrepare)
//    {
//
//        if (($tblDivision = $tblPrepare->getServiceTblDivision())
//            && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
//        ) {
//            foreach ($tblPersonList as $tblPerson) {
//                if (($tblPrepareStudent = $this->getPrepareStudentBy($tblPrepare, $tblPerson))
//                    && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
//                    && ($tblCertificateType = $tblCertificate->getTblCertificateType())
//                    && $tblCertificateType->getIdentifier() == 'DIPLOMA'
//                ) {
//
//                }
//            }
//        }
//    }

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
     * @param IFormInterface|null $form
     * @param TblPrepareCertificate $tblPrepare
     * @param TblSubject $tblCurrentSubject
     * @param TblSubject|null $tblNextSubject
     * @param null|bool $IsFinalGrade
     * @param $Route
     * @param $Data
     * @param TblGroup|null $tblGroup
     *
     * @return IFormInterface|string
     */
    public function updatePrepareExamGrades(
        IFormInterface $form,
        TblPrepareCertificate $tblPrepare,
        TblSubject $tblCurrentSubject,
        TblSubject $tblNextSubject = null,
        $IsFinalGrade = null,
        $Route,
        $Data,
        TblGroup $tblGroup = null
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
                        'Nicht alle eingebenen Zensuren befinden sich im Wertebereich (1-6).
                        Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));

            return $form;
        } else {
            if ($Data != null) {
                foreach ($Data as $prepareStudentId => $personGrades) {
                    if (($tblPrepareStudent = $this->getPrepareStudentById($prepareStudentId))
                        && ($tblPrepareItem = $tblPrepareStudent->getTblPrepareCertificate())
                        && ($tblDivision = $tblPrepareItem->getServiceTblDivision())
                        && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                        && is_array($personGrades)
                    ) {
                        $this->setSignerFromSignedInPerson($tblPrepareStudent);

                        $hasGradeText = false;
                        $gradeText = '';
                        if ((isset($personGrades['Text']))
                            && ($tblGradeText = Gradebook::useService()->getGradeTextById($personGrades['Text']))
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
                                if ($tblPrepareAdditionalGrade = $this->getPrepareAdditionalGradeBy(
                                    $tblPrepareItem, $tblPerson, $tblCurrentSubject, $tblPrepareAdditionalGradeType
                                )
                                ) {
                                    (new Data($this->getBinding()))->updatePrepareAdditionalGrade($tblPrepareAdditionalGrade,
                                        trim($value), false);
                                } elseif (trim($value) != '') {
                                    (new Data($this->getBinding()))->createPrepareAdditionalGrade(
                                        $tblPrepareItem, $tblPerson, $tblCurrentSubject, $tblPrepareAdditionalGradeType,
                                        0, trim($value), false, false
                                    );
                                }
                            }
                        }
                    }
                }
            }
            if ($tblNextSubject) {
                if ($IsFinalGrade) {
                    $parameters = array(
                        'PrepareId' => $tblPrepare->getId(),
                        'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                        'Route' => $Route,
                        'SubjectId' => $tblNextSubject->getId()
                    );
                } else {
                    $parameters = array(
                        'PrepareId' => $tblPrepare->getId(),
                        'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                        'Route' => $Route,
                        'SubjectId' => $tblCurrentSubject->getId(),
                        'IsFinalGrade' => true
                    );
                }
            } else {
                if ($IsFinalGrade) {
                    $parameters = array(
                        'PrepareId' => $tblPrepare->getId(),
                        'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                        'Route' => $Route,
                        'IsNotSubject' => true
                    );
                } else {
                    $parameters = array(
                        'PrepareId' => $tblPrepare->getId(),
                        'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                        'Route' => $Route,
                        'SubjectId' => $tblCurrentSubject->getId(),
                        'IsFinalGrade' => true
                    );
                }
            }

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Noten wurden gespeichert.')
                . new Redirect('/Education/Certificate/Prepare/Prepare/Diploma/Setting',
                    Redirect::TIMEOUT_SUCCESS,
                    $parameters
                );
        }
    }

    /**
     * @deprecated
     *
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return bool
     */
    public function isCourseMainDiploma(TblPrepareCertificate $tblPrepare)
    {

        if (($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblLevel = $tblDivision->getTblLevel())
            && ($tblSchoolType = $tblLevel->getServiceTblType())
            && $tblSchoolType->getName() == 'Mittelschule / Oberschule'
        ) {
            if ($tblLevel->getName() == '9' || $tblLevel->getName() == '09') {
                return true;
            }
        }

        return false;
    }

    /**
     * soft remove
     * @param TblPrepareCertificate $tblPrepareCertificate
     *
     * @return bool
     */
    public function destroyPrepareCertificate(TblPrepareCertificate $tblPrepareCertificate)
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
     * @deprecated
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblLeaveStudent[]
     */
    public function  getLeaveStudentAllByDivision(TblDivisionCourse $tblDivisionCourse)
    {
        // todo find usage
        return (new Data($this->getBinding()))->getLeaveStudentAllByDivision($tblDivisionCourse);
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
     * @deprecated use DivisionCourse::useService()->getCoursesForStudent
     *
     * @param TblDivision $tblDivision
     * @param TblPerson $tblPerson
     *
     * @return array
     */
    public function getCoursesForStudent(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        $advancedCourses = array();
        $basicCourses = array();
        if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))) {
            foreach ($tblDivisionSubjectList as $tblDivisionSubjectItem) {
                if (($tblSubjectGroup = $tblDivisionSubjectItem->getTblSubjectGroup())) {

                    if (($tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject(
                        $tblDivisionSubjectItem))
                    ) {
                        foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                            if (($tblSubject = $tblDivisionSubjectItem->getServiceTblSubject())
                                && ($tblPersonStudent = $tblSubjectStudent->getServiceTblPerson())
                                && $tblPerson->getId() == $tblPersonStudent->getId()
                            ) {
                                if ($tblSubject->getAcronym() == 'EN2') {
                                    $tblSubject = Subject::useService()->getSubjectByAcronym('EN');
                                }

                                if ($tblSubject) {
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
        }

        return array($advancedCourses, $basicCourses);
    }

    /**
     * @param $totalPoints
     *
     * @return string
     */
    public function  getResultForAbiturAverageGrade(
        $totalPoints
    ) {

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
     * @param TblPrepareAdditionalGrade $tblPrepareAdditionalGrade
     * @param $grade
     * @param bool $isSelected
     *
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function updatePrepareAdditionalGrade(
        TblPrepareAdditionalGrade $tblPrepareAdditionalGrade,
        $grade,
        $isSelected = false
    ) {

        (new Data($this->getBinding()))->updatePrepareAdditionalGrade($tblPrepareAdditionalGrade, $grade, $isSelected);

        return false;
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @return array|bool
     * @throws \Exception
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
            && (($tblStudentList = $tblDivisionCourse->getStudentsWithSubCourses()))
        ) {
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

                    // todo Anpassung nach Fehlzeiten Anpassung
                    // Prüfung ob Fehlzeiten-Stunden erfasst wurden, nur erforderlich bei Pflege der Fehlzeiten im Klassenbuch
                    if ($useClassRegisterForAbsence && false) {
                        if (Absence::useService()->hasPersonAbsenceLessons($tblPerson, $tblDivisionCourse, TblAbsence::VALUE_STATUS_EXCUSED)) {
                            $StudentHasAbsenceLessonsList[$tblPerson->getId()][TblAbsence::VALUE_STATUS_EXCUSED] = true;
                        }
                        if (Absence::useService()->hasPersonAbsenceLessons($tblPerson, $tblDivisionCourse, TblAbsence::VALUE_STATUS_UNEXCUSED)) {
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
     * @param TblPerson $tblPerson
     * @param bool|false $IsPrinted
     *
     * @return false|TblPrepareStudent[]
     */
    public function getPrepareStudentAllWherePrintedByPerson(TblPerson $tblPerson, $IsPrinted = false)
    {
        return (new Data($this->getBinding()))->getPrepareStudentAllWherePrintedByPerson($tblPerson, $IsPrinted);
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
    public function getPrepareStudentListByPersonAndCertificateTypeAndYear(TblPerson $tblPerson, TblCertificateType $tblCertificateType, TblYear $tblYear)
    {
        return (new Data($this->getBinding()))->getPrepareStudentListByPersonAndCertificateTypeAndYear($tblPerson, $tblCertificateType, $tblYear);
    }
}