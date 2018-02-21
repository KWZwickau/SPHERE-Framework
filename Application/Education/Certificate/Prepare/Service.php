<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 10:42
 */

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Data;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGradeType;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareInformation;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Setup;
use SPHERE\Application\Education\Certificate\Setting\Setting;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
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
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Education\Certificate\Prepare
 */
class Service extends AbstractService
{

    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
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
     * @param $Id
     *
     * @return false|TblPrepareStudent
     */
    public function getPrepareStudentById($Id)
    {

        return (new Data($this->getBinding()))->getPrepareStudentById($Id);
    }

    /**
     * @param bool|false $IsApproved
     * @param bool|false $IsPrinted
     *
     * @return false|TblPrepareStudent[]
     */
    public function getPrepareStudentAllWhere($IsApproved = false, $IsPrinted = false)
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
     * @param TblDivision $tblDivision
     * @param TblTestType $tblTestType
     * @param TblGradeType $tblGradeType
     *
     * @return false|TblPrepareGrade
     */
    public function getPrepareGradeByGradeType(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblTestType $tblTestType,
        TblGradeType $tblGradeType
    ) {

        return (new Data($this->getBinding()))->getPrepareGradeByGradeType($tblPrepare, $tblPerson, $tblDivision,
            $tblTestType, $tblGradeType);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblTestType $tblTestType
     *
     * @return false|TblPrepareGrade[]
     */
    public function getPrepareGradeAllByPerson(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblTestType $tblTestType
    ) {

        return (new Data($this->getBinding()))->getPrepareGradeAllByPerson($tblPrepare, $tblPerson, $tblTestType);
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
     * @param TblGroup|null $tblGroup
     * @param $Data
     * @param $Route
     *
     * @return IFormInterface|string
     */
    public function updatePrepareSetSigner(
        IFormInterface $Stage,
        TblPrepareCertificate $tblPrepare,
        TblGroup $tblGroup = null,
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
        if (!$tblPerson) {
            $Stage->setError('Data', 'Bitte wählen Sie eine Person aus');
            $Error = true;
        }

        if (!$Error) {
            $tblPrepareList = false;
            $tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate();
            if ($tblGroup) {
                if (($tblGenerateCertificate)) {
                    $tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
                }
            } else {
                $tblPrepareList = array(0 => $tblPrepare);
            }

            if ($tblPrepareList) {
                foreach ($tblPrepareList as $tblPrepareItem) {
                    if (($tblPrepareStudentList = $this->getPrepareStudentAllByPrepare($tblPrepareItem))) {
                        foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                            if (!$tblGroup
                                || (($tblPersonTemp = $tblPrepareStudent->getServiceTblPerson())
                                    && Group::useService()->existsGroupPerson($tblGroup, $tblPersonTemp))
                            ) {
                                (new Data($this->getBinding()))->updatePrepareStudent(
                                    $tblPrepareStudent,
                                    $tblPrepareStudent->getServiceTblCertificate() ? $tblPrepareStudent->getServiceTblCertificate() : null,
                                    $tblPrepareStudent->isApproved(),
                                    $tblPrepareStudent->isPrinted(),
                                    $tblPrepareStudent->getExcusedDays(),
                                    $tblPrepareStudent->getUnexcusedDays(),
                                    $tblPerson
                                );
                            }
                        }
                    }
                }
            }

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Unterzeichner wurde ausgewählt.')
                . new Redirect('/Education/Certificate/Prepare/Prepare/Preview', Redirect::TIMEOUT_SUCCESS, array(
                    'PrepareId' => $tblPrepare->getId(),
                    'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                    'Route' => $Route
                ));
        }

        return $Stage;
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
                $tblPrepareStudent->getUnexcusedDays(),
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
    public function updatePrepareStudentSetApproved(TblPrepareStudent $tblPrepareStudent)
    {

        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblDivision = $tblPrepareStudent->getTblPrepareCertificate()->getServiceTblDivision())
        ) {

            if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                && !$tblGenerateCertificate->isLocked()
            ) {
                Generate::useService()->lockGenerateCertificate($tblGenerateCertificate, true);
            }

            if (($tblCertificateType = $tblCertificate->getTblCertificateType())
                && $tblCertificateType->getIdentifier() == 'DIPLOMA'
            ) {
                $isDiploma = true;
            } else {
                $isDiploma = false;
            }

            return (new Data($this->getBinding()))->copySubjectGradesByPerson($tblPrepare, $tblPerson, $isDiploma);
        } else {
            return false;
        }
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
                $tblPrepareStudent->getUnexcusedDays(),
                $tblPrepareStudent->getServiceTblPersonSigner() ? $tblPrepareStudent->getServiceTblPersonSigner() : null
            );
        } else {
            return false;
        }
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return bool
     */
    public function updatePrepareStudentResetApproved(TblPrepareStudent $tblPrepareStudent)
    {

        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblDivision = $tblPrepareStudent->getTblPrepareCertificate()->getServiceTblDivision())
        ) {
            return (new Data($this->getBinding()))->updatePrepareStudent(
                $tblPrepareStudent,
                $tblCertificate,
                false,
                false,
                $tblPrepareStudent->getExcusedDays(),
                $tblPrepareStudent->getUnexcusedDays(),
                $tblPrepareStudent->getServiceTblPersonSigner() ? $tblPrepareStudent->getServiceTblPersonSigner() : null
            );
        } else {
            return false;
        }
    }


    /**
     * @param IFormInterface|null $Stage
     * @param TblPrepareCertificate $tblPrepare
     * @param TblGroup|null $tblGroup
     * @param string $Route
     * @param array $Data
     * @param array $CertificateList
     *
     * @return IFormInterface|string
     */
    public function updatePrepareInformationList(
        IFormInterface $Stage = null,
        TblPrepareCertificate $tblPrepare,
        TblGroup $tblGroup = null,
        $Route,
        $Data,
        $CertificateList
    ) {

        /**
         * Skip to Frontend
         */
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Button']['Submit'])) {
            return $Stage;
        }

        foreach ($Data as $prepareStudentId => $array) {
            if (($tblPrepareStudent = $this->getPrepareStudentById($prepareStudentId))
                && ($tblPrepareItem = $tblPrepareStudent->getTblPrepareCertificate())
                && ($tblDivision = $tblPrepareItem->getServiceTblDivision())
                && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                && is_array($array)
            ) {

                $this->setSignerFromSignedInPerson($tblPrepareStudent);

                if (isset($CertificateList[$tblPerson->getId()])) {

                    /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                    $Certificate = $CertificateList[$tblPerson->getId()];
                    $tblCertificate = $Certificate->getCertificateEntity();

                    /*
                     * Fehlzeiten
                     */
                    if ($tblCertificate && isset($array['ExcusedDays']) && isset($array['UnexcusedDays'])) {
                        (new Data($this->getBinding()))->updatePrepareStudent(
                            $tblPrepareStudent,
                            $tblPrepareStudent->getServiceTblCertificate() ? $tblPrepareStudent->getServiceTblCertificate() : $tblCertificate,
                            $tblPrepareStudent->isApproved(),
                            $tblPrepareStudent->isPrinted(),
                            $array['ExcusedDays'],
                            $array['UnexcusedDays'],
                            $tblPrepareStudent->getServiceTblPersonSigner() ? $tblPrepareStudent->getServiceTblPersonSigner() : null
                        );
                    }

                    /*
                     * Sonstige Informationen
                     */
                    foreach ($array as $field => $value) {
                        if ($field == 'ExcusedDays' || $field == 'UnexcusedDays') {
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
                            } elseif ($field == 'Transfer'
                                && method_exists($Certificate, 'selectValuesTransfer')
                            ) {
                                $value = $Certificate->selectValuesTransfer()[$value];
                            }

                            if (!empty(trim($value))) {
                                $value = trim($value);
                                // Zeichenbegrenzen
                                if (($CharCount = Generator::useService()->getCharCountByCertificateAndField(
                                    $tblCertificate, $field, !isset($array['TeamExtra'])))
                                ) {
                                    $value = str_replace("\n", " ", $value);

                                    if (strlen($value) > $CharCount) {
                                        $value = substr($value, 0, $CharCount);
                                    }
                                }

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
                                // auf Leer zurücksetzen
                            } elseif (($tblPrepareInformation = $this->getPrepareInformationBy($tblPrepareItem, $tblPerson,
                                $field))
                            ) {
                                (new Data($this->getBinding()))->updatePrepareInformation($tblPrepareInformation,
                                    $field,
                                    $value);
                            }
                        }
                    }
                }
            }
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Informationen wurden gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Prepare/Preview', Redirect::TIMEOUT_SUCCESS, array(
                'PrepareId' => $tblPrepare->getId(),
                'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                'Route' => $Route
            ));
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     *
     * @param array $Content
     * @return array
     */
    private function createCertificateContent(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        $Content = array()
    ) {
        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);
        $tblDivision = $tblPrepare->getServiceTblDivision();
        $tblLevel = $tblDivision->getTblLevel();
        $personId = $tblPerson->getId();

        // Person data
        $Content['P' . $personId]['Person']['Id'] = $tblPerson->getId();
        $Content['P' . $personId]['Person']['Data']['Name']['Salutation'] = $tblPerson->getSalutation();
        $Content['P' . $personId]['Person']['Data']['Name']['First'] = $tblPerson->getFirstSecondName();
        $Content['P' . $personId]['Person']['Data']['Name']['Last'] = $tblPerson->getLastName();

        // Person address
        if (($tblAddress = $tblPerson->fetchMainAddress())) {
            $Content['P' . $personId]['Person']['Address']['Street']['Name'] = $tblAddress->getStreetName();
            $Content['P' . $personId]['Person']['Address']['Street']['Number'] = $tblAddress->getStreetNumber();
            $Content['P' . $personId]['Person']['Address']['City']['Code'] = $tblAddress->getTblCity()->getCode();
            $Content['P' . $personId]['Person']['Address']['City']['Name'] = $tblAddress->getTblCity()->getDisplayName();
        }

        // Person Common
        if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))
            && $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates()
        ) {
            $Content['P' . $personId]['Person']['Common']['BirthDates']['Gender'] = $tblCommonBirthDates->getGender();
            $Content['P' . $personId]['Person']['Common']['BirthDates']['Birthday'] = $tblCommonBirthDates->getBirthday();
            $Content['P' . $personId]['Person']['Common']['BirthDates']['Birthplace'] = $tblCommonBirthDates->getBirthplace()
                ? $tblCommonBirthDates->getBirthplace() : '&nbsp;';
        }

        // Person Parents
        if (($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
            foreach ($tblRelationshipList as $tblToPerson) {
                if (($tblFromPerson = $tblToPerson->getServiceTblPersonFrom())
                    && $tblToPerson->getServiceTblPersonTo()
                    && $tblToPerson->getTblType()->getName() == 'Sorgeberechtigt'
                    && $tblToPerson->getServiceTblPersonTo()->getId() == $tblPerson->getId()
                ) {
                    if (!isset($Content['P' . $personId]['Person']['Parent']['Mother']['Name'])) {
                        $Content['P' . $personId]['Person']['Parent']['Mother']['Name']['First'] = $tblFromPerson->getFirstSecondName();
                        $Content['P' . $personId]['Person']['Parent']['Mother']['Name']['Last'] = $tblFromPerson->getLastName();
                    } elseif (!isset($Content['P' . $personId]['Person']['Parent']['Father']['Name'])) {
                        $Content['P' . $personId]['Person']['Parent']['Father']['Name']['First'] = $tblFromPerson->getFirstSecondName();
                        $Content['P' . $personId]['Person']['Parent']['Father']['Name']['Last'] = $tblFromPerson->getLastName();
                    }
                }
            }
        }

        // Company
        $tblCompany = false;
        if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
            && $tblStudent
        ) {
            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                $tblTransferType);
            if ($tblStudentTransfer) {
                $tblCompany = $tblStudentTransfer->getServiceTblCompany();

                // Abschluss (Bildungsgang)
                $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                if ($tblCourse) {
                    if ($tblLevel && (intval($tblLevel->getName()) > 6)) {
                        if ($tblCourse->getName() == 'Hauptschule') {
                            $Content['P' . $personId]['Student']['Course']['Degree'] = 'Hauptschulabschlusses';
                        } elseif ($tblCourse->getName() == 'Realschule') {
                            $Content['P' . $personId]['Student']['Course']['Degree'] = 'Realschulabschlusses';
                        }
                    }
                }
            }
        }
        if ($tblCompany) {
            $Content['P' . $personId]['Company']['Id'] = $tblCompany->getId();
            $Content['P' . $personId]['Company']['Data']['Name'] = $tblCompany->getName();
            $Content['P'.$personId]['Company']['Data']['ExtendedName'] = $tblCompany->getExtendedName();
            if (($tblAddress = $tblCompany->fetchMainAddress())) {
                $Content['P' . $personId]['Company']['Address']['Street']['Name'] = $tblAddress->getStreetName();
                $Content['P' . $personId]['Company']['Address']['Street']['Number'] = $tblAddress->getStreetNumber();
                $Content['P' . $personId]['Company']['Address']['City']['Code'] = $tblAddress->getTblCity()->getCode();
                $Content['P' . $personId]['Company']['Address']['City']['Name'] = $tblAddress->getTblCity()->getDisplayName();
            }
        }

        // Arbeitsgemeinschaften
        if ($tblStudent
            && ($tblSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('TEAM'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                $tblStudent, $tblSubjectType
            ))
        ) {
            $tempList = array();
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                if ($tblStudentSubject->getServiceTblSubject()) {
                    $tempList[] = $tblStudentSubject->getServiceTblSubject()->getName();
                }
            }
            if (!empty($tempList)) {
                $Content['P' . $personId]['Subject']['Team'] = implode(', ', $tempList);
            }
        }

        // Fremdsprache ab Klassenstufe
        if ($tblStudent
            && ($tblSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                $tblStudent, $tblSubjectType
            ))
        ) {
            if ($tblStudentSubjectList) {
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if (($tblSubject = $tblStudentSubject->getServiceTblSubject())
                        && ($level = $tblStudentSubject->getServiceTblLevelFrom())
                    ) {
                        $Content['P' . $personId]['Subject']['Level'][$tblSubject->getAcronym()] = $level->getName();
                    }
                }
            }
        }

        $tblYear = false;
        if ($tblDivision && ($tblYear = $tblDivision->getServiceTblYear())) {
            $Content['P' . $personId]['Division']['Data']['Year'] = $tblYear->getName();
        }
        // Division
        if ($tblDivision && $tblLevel) {
            $Content['P' . $personId]['Division']['Id'] = $tblDivision->getId();
            $Content['P' . $personId]['Division']['Data']['Level']['Name'] = $tblLevel->getName();
            $Content['P' . $personId]['Division']['Data']['Name'] = $tblDivision->getName();

            $course = $tblLevel->getName();
            // html funktioniert, allerdings kann es der DOM-PDF nicht, enable utf-8 for domPdf? oder eventuell Schriftart ändern
            // $midTerm = '/&#x2160;';
            $midTerm = '/I';
            if (($tblAppointedDateTask = $tblPrepare->getServiceTblAppointedDateTask())
                && $tblYear
                && ($tblPeriodList = $tblYear->getTblPeriodAll())
                && ($tblPeriod = $tblAppointedDateTask->getServiceTblPeriod())
                && ($tblFirstPeriod = current($tblPeriodList))
                && $tblPeriod->getId() != $tblFirstPeriod->getId()
            ) {
                // $midTerm = '/&#x2161;';
                $midTerm = '/II';
            }
            $course .= $midTerm;
            $Content['P' . $personId]['Division']['Data']['Course']['Name'] = $course;
        }
        if (($tblPrepareStudent && ($tblPersonSigner = $tblPrepareStudent->getServiceTblPersonSigner()))
            || ($tblPersonSigner = $tblPrepare->getServiceTblPersonSigner())
        ) {
            $Content['P' . $personId]['Division']['Data']['Teacher'] = $tblPersonSigner->getFullName();
        }

        // zusätzliche Informationen
        $tblPrepareInformationList = Prepare::useService()->getPrepareInformationAllByPerson($tblPrepare,
            $tblPerson);
        if ($tblPrepareInformationList) {
            // Spezialfall Arbeitsgemeinschaften im Bemerkungsfeld
            $team = '';
            $remark = '';

            foreach ($tblPrepareInformationList as $tblPrepareInformation) {
                if ($tblPrepareInformation->getField() == 'Team') {
                    if ($tblPrepareInformation->getValue() != '') {
                        $team = 'Arbeitsgemeinschaften: '.$tblPrepareInformation->getValue();
                    }
                } elseif ($tblPrepareInformation->getField() == 'Remark') {
                    $remark = $tblPrepareInformation->getValue();
                } elseif ($tblPrepareInformation->getField() == 'Transfer') {
                    $Content['P' . $personId]['Input'][$tblPrepareInformation->getField()] = $tblPerson->getFirstSecondName()
                        . ' ' . $tblPerson->getLastName() . ' ' . $tblPrepareInformation->getValue();
                } elseif ($tblPrepareInformation->getField() == 'IndividualTransfer') {
                    $Content['P' . $personId]['Input'][$tblPrepareInformation->getField()] = $tblPerson->getFirstSecondName()
                        . ' ' . $tblPrepareInformation->getValue();
                } else {
                    $Content['P' . $personId]['Input'][$tblPrepareInformation->getField()] = $tblPrepareInformation->getValue();
                }
            }

            // Streichung leeres Bemerkungsfeld
            if ($remark == '') {
                $remark = '---';
            }

            if ($team || $remark) {
                if ($team) {
                    if (($tblConsumer = Consumer::useService()->getConsumerBySession())
                        && $tblConsumer->getAcronym() == 'EVSR'
                    ) {
                        // Arbeitsgemeinschaften am Ende der Bemerkungnen
                        $remark = $remark . " \n\n " . $team;
                    } else {
                        $remark = $team . " \n\n " . $remark;
                    }
                }
            }
            $Content['P' . $personId]['Input']['Remark'] = $remark;
        } else {
            $Content['P' . $personId]['Input']['Remark'] = '---';
        }

        // Klassenlehrer
        $tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate();
        // Todo als Mandanteneinstellung umbauen
        if ($tblPersonSigner) {
            $divisionTeacherDescription = 'Klassenlehrer';

            $tblConsumer = Consumer::useService()->getConsumerBySession();
            if ($tblConsumer
                && $tblConsumer->getAcronym() == 'EVSR'
                && $tblGenerateCertificate->isDivisionTeacherAvailable()
            ) {
                $firstName = $tblPersonSigner->getFirstName();
                if (strlen($firstName) > 1) {
                    $firstName = substr($firstName, 0, 1) . '.';
                }
                $Content['P' . $personId]['DivisionTeacher']['Name'] = $firstName . ' '
                    . $tblPersonSigner->getLastName();
            } elseif ($tblConsumer
                && $tblConsumer->getAcronym() == 'ESZC'
                && $tblGenerateCertificate->isDivisionTeacherAvailable()
            ) {
                $Content['P' . $personId]['DivisionTeacher']['Name'] = trim($tblPersonSigner->getSalutation()
                    . " " . $tblPersonSigner->getLastName());
            } elseif ($tblConsumer
                && $tblConsumer->getAcronym() == 'EVSC'
                && $tblGenerateCertificate->isDivisionTeacherAvailable()
            ) {
                $Content['P' . $personId]['DivisionTeacher']['Name'] = trim($tblPersonSigner->getFirstName()
                    . " " . $tblPersonSigner->getLastName());
                $divisionTeacherDescription = 'Klassenleiter';
            } elseif ($tblConsumer
                && $tblConsumer->getAcronym() == 'EGE'
                && $tblGenerateCertificate->isDivisionTeacherAvailable()
            ) {
                $Content['P'.$personId]['DivisionTeacher']['Name'] = $tblPersonSigner->getFullName();
                if ($tblLevel
                    && ($tblSchoolType = $tblLevel->getServiceTblType())
                    && $tblSchoolType->getName() == 'Mittelschule / Oberschule'
                    && ($level = intval($tblLevel->getName()))
                    && $level < 9
                ) {
                    $divisionTeacherDescription = 'Gruppenleiter';
                }
            } elseif ($tblConsumer
                && $tblConsumer->getAcronym() == 'EVAMTL'
                && $tblGenerateCertificate->isDivisionTeacherAvailable()
            ) {
                $Content['P'.$personId]['DivisionTeacher']['Name'] = $tblPersonSigner->getFullName();
                if ($tblLevel
                    && ($tblSchoolType = $tblLevel->getServiceTblType())
                    && $tblSchoolType->getName() != 'Grundschule'
                ){
                    $divisionTeacherDescription = 'Mentor';
                }
            } elseif ($tblConsumer
                && $tblConsumer->getAcronym() == 'CSW'
                && $tblGenerateCertificate->isDivisionTeacherAvailable()
            ) {
                if ($tblLevel
                    && ($tblSchoolType = $tblLevel->getServiceTblType())
                    && $tblSchoolType->getName() == 'Mittelschule / Oberschule'
                ) {
                    $Content['P' . $personId]['DivisionTeacher']['Name'] = $tblPersonSigner->getFirstSecondName()
                        . ' ' . $tblPersonSigner->getLastName();
                } else {
                    $Content['P'.$personId]['DivisionTeacher']['Name'] = $tblPersonSigner->getFullName();
                }
            }
            else {
                if ($tblGenerateCertificate->isDivisionTeacherAvailable()) {
                    $Content['P'.$personId]['DivisionTeacher']['Name'] = $tblPersonSigner->getFullName();
                }
            }

            if (($genderValue = $this->getGenderByPerson($tblPersonSigner))) {
                $Content['P' . $personId]['DivisionTeacher']['Gender'] = $genderValue;
                if ($genderValue == 'M') {
                    $Content['P' . $personId]['DivisionTeacher']['Description'] = $divisionTeacherDescription;
                    $Content['P' . $personId]['Tudor']['Description'] = 'Tutor';
                } elseif ($genderValue == 'F') {
                    $Content['P' . $personId]['DivisionTeacher']['Description'] = $divisionTeacherDescription . 'in';
                    $Content['P' . $personId]['Tudor']['Description'] = 'Tutorin';
                }
            }
        }

        // Schulleitung
        if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())) {
            if ($tblGenerateCertificate->getHeadmasterName()
                && $tblGenerateCertificate) {
                $Content['P' . $personId]['Headmaster']['Name'] = $tblGenerateCertificate->getHeadmasterName();
            }
            if (($tblCommonGender = $tblGenerateCertificate->getServiceTblCommonGenderHeadmaster())
                && $tblGenerateCertificate->isDivisionTeacherAvailable()) {
                if ($tblCommonGender->getName() == 'Männlich') {
                    $Content['P' . $personId]['Headmaster']['Description'] = 'Schulleiter';
                } elseif ($tblCommonGender->getName() == 'Weiblich') {
                    $Content['P' . $personId]['Headmaster']['Description'] = 'Schulleiterin';
                }
            }
        }

        // Kopfnoten
        $tblPrepareGradeBehaviorList = Prepare::useService()->getPrepareGradeAllByPerson(
            $tblPrepare,
            $tblPerson,
            Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK')
        );
        if ($tblPrepareGradeBehaviorList) {
            foreach ($tblPrepareGradeBehaviorList as $tblPrepareGrade) {
                if ($tblPrepareGrade->getServiceTblGradeType()) {
                    if (($tblConsumer = Consumer::useService()->getConsumerBySession())
                        && $tblConsumer->getAcronym() == 'EVSR'
                        && ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                        && $tblCertificateType->getIdentifier() != 'RECOMMENDATION'
                        && ($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                            'Education', 'Certificate', 'Radebeul', 'IsGradeVerbal'))
                        && $tblSetting->getValue()
                    ) {
                        $grade = $this->getVerbalGrade($tblPrepareGrade->getGrade());;
                    } else {
                        $grade = $tblPrepareGrade->getGrade();
                    }

                    $Content['P' . $personId]['Input'][$tblPrepareGrade->getServiceTblGradeType()->getCode()] = $grade;
                }
            }
        }
        // Kopfnoten von Fachlehrern für Noteninformation
        if ($tblPrepare->isGradeInformation() && ($tblBehaviorTask = $tblPrepare->getServiceTblBehaviorTask())) {
            if (($tblTestAllByTask = Evaluation::useService()->getTestAllByTask($tblBehaviorTask))) {
                /** @var TblTest $testItem */
                foreach ($tblTestAllByTask as $testItem) {
                    if (($tblGrade = Gradebook::useService()->getGradeByTestAndStudent($testItem, $tblPerson))
                        && $testItem->getServiceTblGradeType()
                        && $testItem->getServiceTblSubject()
                    ) {
                        $Content['P' . $personId]['Input']['BehaviorTeacher'][$testItem->getServiceTblSubject()->getAcronym()]
                        [$testItem->getServiceTblGradeType()->getCode()] = $tblGrade->getDisplayGrade();
                    }
                }
            }
        }

        // Fachnoten
        if ($tblPrepare->isGradeInformation() || ($tblPrepareStudent && !$tblPrepareStudent->isApproved())) {
            // Abschlusszeugnisse
            if ($tblGenerateCertificate
                && ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                && $tblCertificateType->getIdentifier() == 'DIPLOMA'
            ) {
                  if (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('EN'))
                      && ($tblPrepareAdditionalGradeList = $this->getPrepareAdditionalGradeListBy(
                      $tblPrepare, $tblPerson, $tblPrepareAdditionalGradeType
                  ))) {
                      foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                          if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                              if (($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                                  'Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnDiploma'))
                                  && $tblSetting->getValue()
                              ) {
                                  $grade = $this->getVerbalGrade($tblPrepareAdditionalGrade->getGrade());
                                  $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblSubject->getAcronym()] = true;
                              } else {
                                  $grade = $tblPrepareAdditionalGrade->getGrade();
                                  if ((Gradebook::useService()->getGradeTextByName($grade))
                                      && $grade != 'befreit'
                                  ) {
                                      $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblSubject->getAcronym()] = true;
                                  }
                              }

                              $Content['P' . $personId]['Grade']['Data'][$tblSubject->getAcronym()]
                                  = $grade;
                          }
                      }
                  }
            } else {
                if (($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
                    && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblTask))
                ) {
                    foreach ($tblTestList as $tblTest) {
                        if (($tblGradeItem = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson))
                            && $tblTest->getServiceTblSubject()
                        ) {
                            // keine Tendenzen auf Zeugnissen
                            $withTrend = true;
                            if ($tblPrepareStudent
                                && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                                && !$tblCertificate->isInformation()
                            ) {
                                $withTrend = false;
                            }

                            // Radebeul Zensuren im Wortlaut
                            if (($tblConsumer = Consumer::useService()->getConsumerBySession())
                                && $tblConsumer->getAcronym() == 'EVSR'
                                && ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                                && $tblCertificateType->getIdentifier() != 'RECOMMENDATION'
                                && ($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                                'Education', 'Certificate', 'Radebeul', 'IsGradeVerbal'))
                                && $tblSetting->getValue()
                            ) {
                                $Content['P' . $personId]['Grade']['Data'][$tblTest->getServiceTblSubject()->getAcronym()]
                                    = $this->getVerbalGrade($tblGradeItem->getGrade());;
                            } else {
                                $Content['P' . $personId]['Grade']['Data'][$tblTest->getServiceTblSubject()->getAcronym()]
                                    = $tblGradeItem->getDisplayGrade($withTrend);
                            }

                            // bei Zeugnistext als Note Schriftgröße verkleinern
                            if ($tblGradeItem->getTblGradeText()
                                && $tblGradeItem->getTblGradeText()->getName() != 'befreit'
                            ) {
                                $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblTest->getServiceTblSubject()->getAcronym()] = true;
                            }
                        }
                    }
                }
            }
        } else {
            $tblPrepareGradeSubjectList = Prepare::useService()->getPrepareGradeAllByPerson(
                $tblPrepare,
                $tblPerson,
                Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')
            );
            if ($tblPrepareGradeSubjectList) {
                foreach ($tblPrepareGradeSubjectList as $tblPrepareGrade) {
                    if (($tblSubject = $tblPrepareGrade->getServiceTblSubject())) {
                        if (($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                            && $tblCertificateType->getIdentifier() == 'DIPLOMA'
                            && ($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                                'Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnDiploma'))
                            && $tblSetting->getValue()
                        ) {
                            $grade = $this->getVerbalGrade($tblPrepareGrade->getGrade());
                            $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblSubject->getAcronym()] = true;
                        } elseif (($tblConsumer = Consumer::useService()->getConsumerBySession())
                                && $tblConsumer->getAcronym() == 'EVSR'
                                && ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                                && $tblCertificateType->getIdentifier() != 'RECOMMENDATION'
                                && ($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                                    'Education', 'Certificate', 'Radebeul', 'IsGradeVerbal'))
                                && $tblSetting->getValue()
                            ) {
                                $grade = $this->getVerbalGrade($tblPrepareGrade->getGrade());;
                        } else {
                            // bei Zeugnistext als Note Schriftgröße verkleinern
                            if (Gradebook::useService()->getGradeTextByName($tblPrepareGrade->getGrade())
                                && $tblPrepareGrade->getGrade() != 'befreit'
                            ) {
                                $Content['P' . $personId]['Grade']['Data']['IsShrinkSize'][$tblPrepareGrade->getServiceTblSubject()->getAcronym()] = true;
                            }
                            $grade = $tblPrepareGrade->getGrade();
                        }

                        $Content['P' . $personId]['Grade']['Data'][$tblPrepareGrade->getServiceTblSubject()->getAcronym()]
                            = $grade;
                    }
                }
            }
        }

        // Fachnoten von abgewählten Fächern vom Vorjahr
        if (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
            && ($tblPrepareAdditionalGradeList = $this->getPrepareAdditionalGradeListBy($tblPrepare, $tblPerson, $tblPrepareAdditionalGradeType))
        ) {
            foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                    if (($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting(
                            'Education', 'Certificate', 'Prepare', 'IsGradeVerbalOnDiploma'))
                        && $tblSetting->getValue()
                    ) {
                        $grade = $this->getVerbalGrade($tblPrepareAdditionalGrade->getGrade());
                        $Content['P' . $personId]['AdditionalGrade']['Data']['IsShrinkSize'][$tblSubject->getAcronym()] = true;
                    } else {
                        $grade = $tblPrepareAdditionalGrade->getGrade();
                    }

                    $Content['P' . $personId]['AdditionalGrade']['Data'][$tblSubject->getAcronym()]
                        = $grade;
                }
            }
        }

        // Fehlzeiten
        $excusedDays = $tblPrepareStudent->getExcusedDays();
        $unexcusedDays = $tblPrepareStudent->getUnexcusedDays();
        if ($excusedDays === null) {
            $excusedDays = Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblDivision,
                new \DateTime($tblPrepare->getDate()));
        }
        if ($unexcusedDays === null) {
            $unexcusedDays = Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblDivision,
                new \DateTime($tblPrepare->getDate()));
        }
        $Content['P' . $personId]['Input']['Missing'] = $excusedDays;
        $Content['P' . $personId]['Input']['Bad']['Missing'] = $unexcusedDays;
        $Content['P' . $personId]['Input']['Total']['Missing'] = $excusedDays + $unexcusedDays;

        // Zeugnisdatum
        $Content['P' . $personId]['Input']['Date'] = $tblPrepare->getDate();

        // Notendurchschnitt der angegebenen Fächer für Bildungsempfehlung
        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && $tblCertificate->getName() == 'Bildungsempfehlung'
        ) {
            $average = $this->calcSubjectGradesAverage($tblPrepareStudent);
            if ($average) {
                $Content['P' . $personId]['Grade']['Data']['Average'] = number_format($average, 1, ',', '.');
                    //str_replace('.', ',', $average);
            }
        }

        // Notendurchschnitt aller anderen Fächer für Bildungsempfehlung
        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && $tblCertificate->getName() == 'Bildungsempfehlung'
        ) {
            $average = $this->calcSubjectGradesAverageOthers($tblPrepareStudent);
            if ($average) {
                $Content['P' . $personId]['Grade']['Data']['AverageOthers'] = number_format($average, 1, ',', '.');
            }
        }

        // Wahlpflichtbereich
        if ($tblStudent) {

            // Vertiefungskurs
            if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ADVANCED'))
                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                    $tblStudentSubjectType))
            ) {
                /** @var TblStudentSubject $tblStudentSubject */
                $tblStudentSubject = current($tblStudentSubjectList);
                if (($tblSubjectAdvanced = $tblStudentSubject->getServiceTblSubject())) {
                    $Content['P' . $personId]['Student']['Advanced'][$tblSubjectAdvanced->getAcronym()]['Name'] = $tblSubjectAdvanced->getName();
                }
            }

            // Neigungskurs
            if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                    $tblStudentSubjectType))
            ) {
                /** @var TblStudentSubject $tblStudentSubject */
                $tblStudentSubject = current($tblStudentSubjectList);
                if (($tblSubjectOrientation = $tblStudentSubject->getServiceTblSubject())) {
                    $Content['P' . $personId]['Student']['Orientation'][
                        $tblSubjectOrientation->getAcronym()]['Name'] = $tblSubjectOrientation->getName();
                }
            }

            // 2. Fremdsprache
            if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                    $tblStudentSubjectType))
            ) {
                /** @var TblStudentSubject $tblStudentSubject */
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if ($tblStudentSubject->getTblStudentSubjectRanking()
                        && $tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == '2'
                        && ($tblSubjectForeignLanguage = $tblStudentSubject->getServiceTblSubject())
                    ) {
                        $Content['P' . $personId]['Student']['ForeignLanguage'][
                            $tblSubjectForeignLanguage->getAcronym()]['Name'] = $tblSubjectForeignLanguage->getName();
                    }
                }
            }

            // Profil
            if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
                && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                    $tblStudentSubjectType))
            ) {
                /** @var TblStudentSubject $tblStudentSubject */
                $tblStudentSubject = current($tblStudentSubjectList);
                if (($tblSubjectProfile = $tblStudentSubject->getServiceTblSubject())) {
                    $Content['P' . $personId]['Student']['Profile'][$tblSubjectProfile->getAcronym()]['Name']
                        = str_replace('Profil', '', $tblSubjectProfile->getName());

                    // bei Herrnhut EZSH steht das Wort "Profil" nicht Extra auf dem Formular
                    $Content['P' . $personId]['Student']['ProfileEZSH'][$tblSubjectProfile->getAcronym()]['Name']
                        = $tblSubjectProfile->getName();
                }
            }
        }

        return $Content;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblGroup|null $tblGroup
     *
     * @return array
     */
    public function getCertificateMultiContent(TblPrepareCertificate $tblPrepare, TblGroup $tblGroup = null)
    {

        $Content = array();

        $tblPrepareList = false;

        $tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate();
        if ($tblGroup) {
            if (($tblGenerateCertificate)) {
                $tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
            }
        } else {
            if (($tblDivision = $tblPrepare->getServiceTblDivision())) {
                $tblPrepareList = array(0 => $tblPrepare);
            }
        }

        if ($tblPrepareList) {
            foreach ($tblPrepareList as $tblPrepareItem) {
                if (($tblDivision = $tblPrepareItem->getServiceTblDivision())
                    && ($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision))
                ) {
                    foreach ($tblStudentList as $tblPerson) {
                        if (!$tblGroup || Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareItem, $tblPerson))) {
                                $Content = $this->createCertificateContent($tblPrepareItem, $tblPerson, $Content);
                            }
                        }
                    }
                }
            }
        }

        return $Content;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     *
     * @return array
     */
    public function getCertificateContent(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson)
    {

        $Content = array();
        if (($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblPrepareStudent = $this->getPrepareStudentBy($tblPrepare, $tblPerson))
        ) {
            $Content = $this->createCertificateContent($tblPrepare, $tblPerson, $Content);
        }

        return $Content;
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return bool|float
     */
    private function calcSubjectGradesAverage(TblPrepareStudent $tblPrepareStudent)
    {

        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
        ) {
            $tblCertificateSubjectAll = Generator::useService()->getCertificateSubjectAll($tblCertificate);

            if ($tblCertificateSubjectAll) {
                $gradeList = array();
                foreach ($tblCertificateSubjectAll as $tblCertificateSubject) {
                    if (($tblSubject = $tblCertificateSubject->getServiceTblSubject())) {
                        $tblPrepareGrade = false;
                        if ($tblPrepareStudent->isApproved()) {
                            // kopierte Zenur
                            $tblPrepareGrade = Prepare::useService()->getPrepareGradeBySubject(
                                $tblPrepare, $tblPerson, $tblDivision, $tblSubject,
                                Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')
                            );
                        } elseif ($tblPrepare->getServiceTblAppointedDateTask()) {
                            // Zensur aus dem Stichtagsnotenauftrag
                            $tblTestList = Evaluation::useService()->getTestListBy($tblDivision, $tblSubject,
                                $tblPrepare->getServiceTblAppointedDateTask());
                            if ($tblTestList) {
                                foreach ($tblTestList as $tblTest) {
                                    $tblPrepareGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest,
                                        $tblPerson);
                                    if ($tblPrepareGrade) {
                                        break;
                                    }
                                }
                            }
                        }
                        if ($tblPrepareGrade && $tblPrepareGrade->getGrade() != '') {
                            $grade = str_replace('+', '', $tblPrepareGrade->getGrade());
                            $grade = str_replace('-', '', $grade);
                            if (is_numeric($grade)) {
                                $gradeList[] = $grade;
                            }
                        }

                    }
                }

                if (!empty($gradeList)) {
                    return round(floatval(array_sum($gradeList) / count($gradeList)), 1);
                }
            }
        }

        return false;
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return bool|float
     */
    private function calcSubjectGradesAverageOthers(TblPrepareStudent $tblPrepareStudent)
    {

        if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
        ) {

            $gradeList = array();

            if ($tblPrepareStudent->isApproved()) {
                // kopierte Zenuren
                $tblPrepareGradeList = $this->getPrepareGradeAllByPerson(
                    $tblPrepare,
                    $tblPerson,
                    Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')
                );
                if ($tblPrepareGradeList) {
                    /** @var TblPrepareGrade $tblPrepareGrade */
                    foreach ($tblPrepareGradeList as $tblPrepareGrade) {
                        if (($tblSubject = $tblPrepareGrade->getServiceTblSubject())) {
                            $tblCertificateSubject = Generator::useService()->getCertificateSubjectBySubject(
                                $tblCertificate,
                                $tblSubject
                            );
                            if (!$tblCertificateSubject
                                && $tblPrepareGrade && $tblPrepareGrade->getGrade() != ''
                            ) {
                                $grade = str_replace('+', '', $tblPrepareGrade->getGrade());
                                $grade = str_replace('-', '', $grade);
                                if (is_numeric($grade)) {
                                    $gradeList[] = $grade;
                                }
                            }
                        }
                    }
                }
            } elseif ($tblPrepare->getServiceTblAppointedDateTask()) {
                // Zensur aus dem Stichtagsnotenauftrag
                $tblTestList = Evaluation::useService()->getTestAllByTask($tblPrepare->getServiceTblAppointedDateTask(),
                    $tblDivision);
                if ($tblTestList) {
                    foreach ($tblTestList as $tblTest) {
                        if (($tblSubject = $tblTest->getServiceTblSubject())) {
                            $tblCertificateSubject = Generator::useService()->getCertificateSubjectBySubject(
                                $tblCertificate,
                                $tblSubject
                            );
                            $tblGrade = Gradebook::useService()->getGradeByTestAndStudent($tblTest, $tblPerson);
                            if (!$tblCertificateSubject
                                && $tblGrade && $tblGrade->getGrade() != ''
                            ) {
                                $grade = str_replace('+', '', $tblGrade->getGrade());
                                $grade = str_replace('-', '', $grade);
                                if (is_numeric($grade)) {
                                    $gradeList[] = $grade;
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($gradeList)) {
                return round(floatval(array_sum($gradeList) / count($gradeList)), 1);
            }
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param $Date
     * @param $Name
     * @param bool $IsGradeInformation
     * @param TblGenerateCertificate $tblGenerateCertificate
     * @param TblTask $tblAppointedDateTask
     * @param TblTask $tblBehaviorTask
     *
     * @return TblPrepareCertificate
     */
    public function createPrepareData(
        TblDivision $tblDivision,
        $Date,
        $Name,
        $IsGradeInformation = false,
        TblGenerateCertificate $tblGenerateCertificate = null,
        TblTask $tblAppointedDateTask = null,
        TblTask $tblBehaviorTask = null
    ) {

        return (new Data($this->getBinding()))->createPrepare(
            $tblDivision, $Date, $Name, $IsGradeInformation, $tblGenerateCertificate, $tblAppointedDateTask,
            $tblBehaviorTask);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param $Date
     * @param $Name
     * @param TblTask|null $tblAppointedDateTask
     * @param TblTask|null $tblBehaviorTask
     * @param TblPerson|null $tblPersonSigner
     *
     * @return bool
     */
    public function updatePrepareData(
        TblPrepareCertificate $tblPrepare,
        $Date,
        $Name,
        TblTask $tblAppointedDateTask = null,
        TblTask $tblBehaviorTask = null,
        TblPerson $tblPersonSigner = null
    ) {

        return (new Data($this->getBinding()))->updatePrepare($tblPrepare, $Date, $Name, $tblAppointedDateTask,
            $tblBehaviorTask, $tblPersonSigner);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblTestType $tblTestType
     * @param TblGradeType $tblGradeType
     * @param $Grade
     *
     * @return TblPrepareGrade
     */
    public function updatePrepareGradeForBehavior(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblTestType $tblTestType,
        TblGradeType $tblGradeType,
        $Grade
    ) {

        return (new Data($this->getBinding()))->updatePrepareGradeForBehavior($tblPrepare, $tblPerson, $tblDivision,
            $tblTestType, $tblGradeType, $Grade);
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     * @param TblCertificate $tblCertificate
     *
     * @return bool|TblPrepareStudent
     */
    public function updatePrepareStudentSetTemplate(
        TblPrepareCertificate $tblPrepare,
        TblPerson $tblPerson,
        TblCertificate $tblCertificate
    ) {

        if (($tblPrepareStudent = $this->getPrepareStudentBy($tblPrepare, $tblPerson))) {
            (new Data($this->getBinding()))->updatePrepareStudent(
                $tblPrepareStudent,
                $tblCertificate,
                $tblPrepareStudent->isApproved(),
                $tblPrepareStudent->isPrinted(),
                $tblPrepareStudent->getExcusedDays(),
                $tblPrepareStudent->getUnexcusedDays(),
                $tblPrepareStudent->getServiceTblPersonSigner() ? $tblPrepareStudent->getServiceTblPersonSigner() : null
            );

            return $tblPrepareStudent;
        } else {
            return (new Data($this->getBinding()))->createPrepareStudent(
                $tblPrepare,
                $tblPerson,
                $tblCertificate
            );
        }
    }

    /**
     * @param $Data
     */
    public function createPrepareStudentSetBulkTemplates(
        $Data
    ) {

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
                } elseif ($field == 'Transfer'
                    && method_exists($Certificate, 'selectValuesTransfer')
                ) {
                    $value = $Certificate->selectValuesTransfer()[$value];
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
     * @param IFormInterface|null $Stage
     * @param TblPrepareCertificate $tblPrepare
     * @param TblGroup|null $tblGroup
     * @param TblGradeType $tblGradeType
     * @param TblGradeType $tblNextGradeType
     * @param string $Route
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updatePrepareBehaviorGrades(
        IFormInterface $Stage = null,
        TblPrepareCertificate $tblPrepare,
        TblGroup $tblGroup = null,
        TblGradeType $tblGradeType,
        TblGradeType $tblNextGradeType = null,
        $Route,
        $Data
    ) {

        /**
         * Skip to Frontend
         */
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Button']['Submit'])) {
            return $Stage;
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
            $Stage->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        'Nicht alle eingebenen Zensuren befinden sich im Wertebereich (1-5).
                        Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));

            return $Stage;
        } else {
            if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK'))) {
                foreach ($Data as $prepareStudentId => $value) {
                    if (($tblPrepareStudent = $this->getPrepareStudentById($prepareStudentId))
                        && ($tblPrepareItem = $tblPrepareStudent->getTblPrepareCertificate())
                        && ($tblDivision = $tblPrepareItem->getServiceTblDivision())
                        && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                    ) {

                        $this->setSignerFromSignedInPerson($tblPrepareStudent);

                        if (trim($value) && trim($value) !== '' && $value != -1) {

                            Prepare::useService()->updatePrepareGradeForBehavior(
                                $tblPrepareItem, $tblPerson, $tblDivision, $tblTestType, $tblGradeType,
                                trim($value)
                            );
                        }
                    }
                }

                return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Kopfnoten wurden gespeichert.')
                    . new Redirect('/Education/Certificate/Prepare/Prepare/Setting',
                        Redirect::TIMEOUT_SUCCESS,
                        $tblNextGradeType ? array(
                            'PrepareId' => $tblPrepare->getId(),
                            'GroupId' => $tblGroup ? $tblGroup : null,
                            'Route' => $Route,
                            'GradeTypeId' => $tblNextGradeType->getId()
                        )
                            : array(
                            'PrepareId' => $tblPrepare->getId(),
                            'GroupId' => $tblGroup ? $tblGroup : null,
                            'Route' => $Route,
                            'IsNotGradeType' => true
                        )
                    );
            }
        }

        return $Stage;
    }

    /**
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
                    $tblPrepareStudent->getUnexcusedDays(),
                    $tblPerson
                );
            }
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     */
    public function setTemplatesAllByPrepareCertificate(TblPrepareCertificate $tblPrepare)
    {

        $tblConsumer = Consumer::useService()->getConsumerBySession();
        if (($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
        ) {
            foreach ($tblPersonList as $tblPerson) {
                // Template bereits gesetzt
                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                    if ($tblPrepareStudent->getServiceTblCertificate()) {
                        continue;
                    }
                }

                // Noteninformation
                if ($tblPrepare->isGradeInformation()) {
                    $this->updatePrepareStudentSetTemplate($tblPrepare, $tblPerson,
                        Generator::useService()->getCertificateByCertificateClassName('GradeInformation')
                    );
                    continue;
                }

                if ($tblConsumer) {
                    // Eigene Vorlage
                    if (($certificateList = Generate::useService()->getPossibleCertificates($tblPrepare, $tblPerson,
                        $tblConsumer))
                    ) {
                        if (count($certificateList) == 1) {
                            $this->updatePrepareStudentSetTemplate($tblPrepare, $tblPerson, current($certificateList));
                        } elseif (count($certificateList) > 1) {
                            /** @var TblCertificate $certificate */
                            $ChosenCertificate = false;
                            foreach ($certificateList as $certificate) {
                                if ($certificate->isChosenDefault()) {
                                    $ChosenCertificate = $certificate;
                                    break;
                                }
                            }
                            if ($ChosenCertificate) {
                                $this->updatePrepareStudentSetTemplate($tblPrepare, $tblPerson, $ChosenCertificate);
                            }
                        } else {
                            continue;
                        }
                        // Standard Vorlagen
                    } elseif (($certificateList = Generate::useService()->getPossibleCertificates($tblPrepare,
                        $tblPerson))
                    ) {
                        if (count($certificateList) == 1) {
                            $this->updatePrepareStudentSetTemplate($tblPrepare, $tblPerson, current($certificateList));
                        } else {
                            continue;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return bool
     */
    public function updatePrepareDivisionSetApproved(TblPrepareCertificate $tblPrepare)
    {

        if (($tblDivision = $tblPrepare->getServiceTblDivision())) {
            if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
                && ($tblCertificateType =$tblGenerateCertificate->getServiceTblCertificateType())
                && $tblCertificateType->getIdentifier() == 'DIPLOMA'
            ) {
                $isDiploma = true;
            } else {
                $isDiploma = false;
            }

            if (!$tblGenerateCertificate->isLocked()) {

                Generate::useService()->lockGenerateCertificate($tblGenerateCertificate, true);
            }

            return (new Data($this->getBinding()))->copySubjectGradesByPrepare($tblPrepare, $isDiploma);
        } else {
            return false;
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return bool
     */
    public function updatePrepareDivisionResetApproved(TblPrepareCertificate $tblPrepare)
    {

        if (($tblDivision = $tblPrepare->getServiceTblDivision())) {
            return (new Data($this->getBinding()))->updatePrepareStudentDivisionResetApproved($tblPrepare);
        } else {
            return false;
        }
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
     * @param TblPerson $tblPerson
     *
     * @return null|string
     * male -> M | female -> F | nothing -> false
     */
    private function getGenderByPerson(TblPerson $tblPerson)
    {

        $return = false;
        if (($tblCommonTeacher = $tblPerson->getCommon())) {
            if (($tblCommonBirthDates = $tblCommonTeacher->getTblCommonBirthDates())) {
                if (($tblCommonGenderTeacher = $tblCommonBirthDates->getTblCommonGender())) {
                    if ($tblCommonGenderTeacher->getName() == 'Männlich') {
                        $return = 'M';
                    } else {
                        $return = 'F';
                    }
                }
            }
        }
        if ($return == false) {
            if ($tblPerson->getSalutation() == 'Herr') {
                $return = 'M';
            } elseif ($tblPerson->getSalutation() == 'Frau') {
                $return = 'F';
            }
        }

        return $return;
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param $ranking
     * @param $grade
     *
     * @return TblPrepareAdditionalGrade
     */
    public function createPrepareAdditionalGrade(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        $ranking,
        $grade
    ) {

        return (new Data($this->getBinding()))->createPrepareAdditionalGrade($tblPrepareCertificate,
            $tblPerson, $tblSubject, $tblPrepareAdditionalGradeType, $ranking, $grade);
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
     *
     * @return false|TblPrepareAdditionalGrade
     */
    public function getPrepareAdditionalGradeBy(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
    ) {

        return (new Data($this->getBinding()))->getPrepareAdditionalGradeBy(
            $tblPrepareCertificate,
            $tblPerson,
            $tblSubject,
            $tblPrepareAdditionalGradeType
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
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Button']['Submit'])) {
            return $form;
        }

        $error = false;

        if ($Data != null) {
            foreach ($Data as $personGrades) {
                if (is_array($personGrades)) {
                    foreach ($personGrades as $identifier => $value) {
                        if (trim($value) !== '') {
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

                        foreach ($personGrades as $identifier => $value) {
                            if (($tblPrepareAdditionalGradeType = $this->getPrepareAdditionalGradeTypeByIdentifier($identifier))) {
                                if ($tblPrepareAdditionalGrade = $this->getPrepareAdditionalGradeBy(
                                    $tblPrepareItem, $tblPerson, $tblCurrentSubject, $tblPrepareAdditionalGradeType
                                )
                                ) {
                                    (new Data($this->getBinding()))->updatePrepareAdditionalGrade($tblPrepareAdditionalGrade,
                                        trim($value));
                                } elseif (trim($value) != '') {
                                    (new Data($this->getBinding()))->createPrepareAdditionalGrade(
                                        $tblPrepareItem,
                                        $tblPerson,
                                        $tblCurrentSubject,
                                        $tblPrepareAdditionalGradeType,
                                        0,
                                        trim($value)
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
     * @param $grade
     *
     * @return string
     */
    private function getVerbalGrade($grade)
    {
        switch ($grade) {
            case 1 : return 'sehr gut'; break;
            case 2 : return 'gut'; break;
            case 3 : return 'befriedigend'; break;
            case 4 : return 'ausreichend'; break;
            case 5 : return 'mangelhaft'; break;
            case 6 : return 'ungenügend'; break;
        }

        return $grade;
    }

    /**
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
     * @param TblPrepareCertificate $tblPrepare
     * @param array $certificateNameList
     * @param bool $hasMissingLanguage
     *
     * @return array
     */
    public function checkCertificateSubjectsForDivision(TblPrepareCertificate $tblPrepare, $certificateNameList, &$hasMissingLanguage)
    {

        if (($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting('Api', 'Education', 'Certificate', 'ProfileAcronym'))
            && ($value = $tblSetting->getValue())
        ) {
            $tblProfileSubject = Subject::useService()->getSubjectByAcronym($value);
        } else {
            $tblProfileSubject  = false;
        }
        if (($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting('Api', 'Education', 'Certificate', 'OrientationAcronym'))
            && ($value = $tblSetting->getValue())
        ) {
            $tblOrientationSubject = Subject::useService()->getSubjectByAcronym($value);
        } else {
            $tblOrientationSubject  = false;
        }

        $subjectList = array();
        if (($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblTask, $tblDivision))
        ) {

            if (($tblSubjectProfileAll = Subject::useService()->getSubjectProfileAll())){
                $hasProfiles = true;
            } else {
                $hasProfiles = false;
            }
            if (($tblSubjectOrientationAll = Subject::useService()->getSubjectOrientationAll())){
                $hasOrientations = true;
            } else {
                $hasOrientations = false;
            }
            if (($tblForeignLanguagesAll = Subject::useService()->getSubjectForeignLanguageAll())) {
                $hasForeignLanguages = true;
            } else {
                $hasForeignLanguages = false;
            }
            foreach ($tblTestList as $tblTest) {
                if (($tblSubject = $tblTest->getServiceTblSubject())) {
                    // Profile ignorieren
                    if ($tblProfileSubject) {
                        if ($tblProfileSubject->getId() == $tblSubject->getId()) {
                            continue;
                        }
                    } elseif ($hasProfiles && isset($tblSubjectProfileAll[$tblSubject->getId()])) {
                        continue;
                    }

                    // Neigungskurse orientieren
                    if ($tblOrientationSubject) {
                        if ($tblOrientationSubject->getId() == $tblSubject->getId()) {
                            continue;
                        }
                    } elseif ($hasOrientations && isset($tblSubjectOrientationAll[$tblSubject->getId()])) {
                        continue;
                    }

                    // bei Fremdsprache I-Icon mit ToolTip
                    if ($hasForeignLanguages && isset($tblForeignLanguagesAll[$tblSubject->getId()])) {
//                        $isForeignLanguage = true;
                        $hasMissingLanguage = true;
                    } else {
//                        $isForeignLanguage = false;
                    }

                    foreach ($certificateNameList as $certificateId => $name) {
                        if (($tblCertificate = Setting::useService()->getCertificateById($certificateId))
                            && !Setting::useService()->getCertificateSubjectBySubject($tblCertificate, $tblSubject)
                        ) {
                            $subjectList[$tblSubject->getAcronym()] = $tblSubject->getAcronym();
//                                .($isForeignLanguage
//                                    ? ' ' . new ToolTip(new \SPHERE\Common\Frontend\Icon\Repository\Info(),
//                                        'Bei Fremdsprachen kann die Warnung unter Umständen ignoriert werden,
//                                         bitte prüfen Sie die Detailansicht unter Bearbeiten.') : '');
                        }
                    }
                }
            }

        }

        return $subjectList;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     *
     * @return array
     */
    public function checkCertificateSubjectsForStudents(TblPrepareCertificate $tblPrepare)
    {

        $subjectList = array();
        $resultList = array();
        if (($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
            && ($tblTestList = Evaluation::useService()->getTestAllByTask($tblTask, $tblDivision))
            && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
        ) {
            foreach ($tblTestList as $tblTest) {
                if (($tblSubject = $tblTest->getServiceTblSubject())) {
                    if (($tblSubjectGroup = $tblTest->getServiceTblSubjectGroup())) {
                        if (($tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                                $tblDivision, $tblSubject, $tblSubjectGroup))
                            && ($tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject))
                        ) {
                            foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                                if (($tblPerson = $tblSubjectStudent->getServiceTblPerson())) {
                                    $subjectList[$tblPerson->getId()][$tblSubject->getAcronym()] = $tblSubject;
                                }
                            }
                        }
                    } else {
                        foreach ($tblPersonList as $tblPerson) {
                            $subjectList[$tblPerson->getId()][$tblSubject->getAcronym()] = $tblSubject;
                        }
                    }
                }
            }
        }

        if (($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting('Api', 'Education', 'Certificate', 'ProfileAcronym'))
            && ($value = $tblSetting->getValue())
        ) {
            $tblProfileSubject = Subject::useService()->getSubjectByAcronym($value);
        } else {
            $tblProfileSubject  = false;
        }
        if (($tblSetting = \SPHERE\Application\Setting\Consumer\Consumer::useService()->getSetting('Api', 'Education', 'Certificate', 'OrientationAcronym'))
            && ($value = $tblSetting->getValue())
        ) {
            $tblOrientationSubject = Subject::useService()->getSubjectByAcronym($value);
        } else {
            $tblOrientationSubject  = false;
        }

        foreach ($subjectList as $personId => $subjects) {
            if (($tblPerson = Person::useService()->getPersonById($personId))) {
                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                    && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                ) {
                    ksort($subjects);
                    /** @var TblSubject $tblSubject */
                    foreach ($subjects as $tblSubject) {
                        // Profile überspringen --> stehen extra im Wahlpflichtbereich
                        if ($tblProfileSubject) {
                            if ($tblProfileSubject->getId() == $tblSubject->getId()) {
                                continue;
                            }
                        } elseif (($tblStudent = $tblPerson->getStudent())
                            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
                            && ($tblProfileList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                                $tblStudentSubjectType))
                        ) {
                            $isIgnore = false;
                            foreach ($tblProfileList as $tblProfile) {
                                if ($tblProfile->getServiceTblSubject() && $tblProfile->getServiceTblSubject()->getId() == $tblSubject->getId()) {
                                    $isIgnore = true;
                                }
                            }
                            if ($isIgnore) {
                                continue;
                            }
                        }

                        // Neigungskurs überspringen --> stehen extra im Wahlpflichtbereich
                        if ($tblOrientationSubject) {
                            if ($tblOrientationSubject->getId() == $tblSubject->getId()) {
                                continue;
                            }
                        } elseif (($tblStudent = $tblPerson->getStudent())
                            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
                            && ($tblOrientationList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                                $tblStudentSubjectType))
                        ) {
                            $isIgnore = false;
                            foreach ($tblOrientationList as $tblOrientation) {
                                if ($tblOrientation->getServiceTblSubject() && $tblOrientation->getServiceTblSubject()->getId() == $tblSubject->getId()) {
                                    $isIgnore = true;
                                }
                            }
                            if ($isIgnore) {
                                continue;
                            }
                        }

                        // ab 2. Fremdsprache ignorieren
                        if (($tblStudent = $tblPerson->getStudent())
                            // nicht für alle Zeugnisse sinnvoll, z.B. Kurshalbjahreszeugnis
                            && $tblCertificate->getName() !== 'Gymnasium Kurshalbjahreszeugnis'
                            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                            && ($tblForeignLanguageList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                                $tblStudentSubjectType))
                        ) {
                            $isIgnore = false;
                            foreach ($tblForeignLanguageList as $tblForeignLanguage) {
                                if ($tblForeignLanguage->getServiceTblSubject()
                                    && $tblForeignLanguage->getTblStudentSubjectRanking()
                                    && $tblForeignLanguage->getTblStudentSubjectRanking()->getName() != '1'
                                    && $tblForeignLanguage->getServiceTblSubject()->getId() == $tblSubject->getId()
                                ) {
                                    $isIgnore = true;
                                }
                            }
                            if ($isIgnore) {
                                continue;
                            }
                        }


                        if (!Setting::useService()->getCertificateSubjectBySubject($tblCertificate, $tblSubject)) {
                            $resultList[$tblPerson->getId()][$tblSubject->getAcronym()] = $tblSubject->getAcronym();
                        }
                    }
                }
            }
        }

        return $resultList;
    }

    /**
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
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     *
     * @return false|TblLeaveStudent
     */
    public  function getLeaveStudentBy(TblPerson $tblPerson, TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getLeaveStudentBy($tblPerson, $tblDivision);
    }

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
     * @param IFormInterface|null $Form
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param TblCertificate $tblCertificate
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateLeaveContent(
        IFormInterface $Form = null,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblCertificate $tblCertificate,
        $Data
    ) {

        if ($Data === null) {
            return $Form;
        }

        $error = false;

        // todo Wertebereich
//        if (!empty($Grade)
//            && $tblScoreType
//            && $tblScoreType->getPattern() !== ''
//        ) {
//            foreach ($Grade as $personId => $value) {
//                $tblPerson = Person::useService()->getPersonById($personId);
//                $gradeValue = str_replace(',', '.', trim($value['Grade']));
//                if (!isset($value['Attendance']) && $gradeValue !== '' && $gradeValue !== '-1') {
//                    if (!preg_match('!' . $tblScoreType->getPattern() . '!is', $gradeValue)) {
//                        if ($tblPerson) {
//                            $errorRange[] = new Container(new Bold($tblPerson->getLastFirstName()));
//                        }
//                    }
//                }
//            }
//        }

//        Debugger::screenDump($Data);

        if ($error) {
            $Form->prependGridGroup(
                new FormGroup(new FormRow(new FormColumn(new Danger(
                        'Nicht alle eingebenen Zensuren befinden sich im Wertebereich (1-5).
                        Die Daten wurden nicht gespeichert.', new Exclamation())
                ))));

            return $Form;
        }

        if ((!$tblLeaveStudent = $this->getLeaveStudentBy($tblPerson, $tblDivision))) {
            $tblLeaveStudent = (new Data($this->getBinding()))->createLeaveStudent($tblPerson, $tblDivision, $tblCertificate);
        }
        if ($tblLeaveStudent) {
            if (isset($Data['Grades'])) {
                foreach ($Data['Grades'] as $subjectId => $array){
                    if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                        if (isset($array['Grade']) && isset($array['GradeText'])){
                            if (($tblGradeText = Gradebook::useService()->getGradeTextById($array['GradeText']))) {
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
        }

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Informationen wurden erfolgreich gespeichert.')
            . new Redirect('/Education/Certificate/Prepare/Leave/Student', Redirect::TIMEOUT_SUCCESS, array(
                'PersonId' => $tblPerson->getId(),
            ));
    }
}