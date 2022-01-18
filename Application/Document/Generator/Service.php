<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.03.2017
 * Time: 13:48
 */

namespace SPHERE\Application\Document\Generator;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Generator\Service\Data;
use SPHERE\Application\Document\Generator\Service\Entity\TblDocument;
use SPHERE\Application\Document\Generator\Service\Entity\TblDocumentSubject;
use SPHERE\Application\Document\Generator\Service\Kamenz\KamenzReportService;
use SPHERE\Application\Document\Generator\Service\Setup;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Document\Standard
 */
class Service extends AbstractService
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
     * @param $id
     *
     * @return false|TblDocument
     */
    public function getDocumentById($id)
    {

        return (new Data($this->getBinding()))->getDocumentById($id);
    }

    /**
     * @return false|TblDocument[]
     */
    public function getDocumentAll()
    {

        return (new Data($this->getBinding()))->getDocumentAll();
    }

    /**
     * @param $name
     *
     * @return false|TblDocument
     */
    public function getDocumentByName($name)
    {

        return (new Data($this->getBinding()))->getDocumentByName($name);
    }

    /**
     * @param $documentClass
     *
     * @return false|TblDocument
     */
    public function getDocumentByClass($documentClass)
    {

        return (new Data($this->getBinding()))->getDocumentByClass($documentClass);
    }

    /**
     * @param TblDocument $tblDocument
     * @param integer $ranking
     *
     * @return false|TblDocumentSubject
     */
    public function getDocumentSubjectByDocumentAndRanking(TblDocument $tblDocument, $ranking)
    {

        return (new Data($this->getBinding()))->getDocumentSubjectByDocumentAndRanking($tblDocument, $ranking);
    }

    /**
     * @param TblDocument $tblDocument
     * @return false|TblDocumentSubject[]
     */
    public function getDocumentSubjectListByDocument(TblDocument $tblDocument)
    {

        return (new Data($this->getBinding()))->getDocumentSubjectListByDocument($tblDocument);
    }

    /**
     * @param array $Data
     * @param TblPerson $tblPerson
     * @param AbstractDocument $documentClass
     * @param TblType $tblType
     *
     * @return array
     */
    public function setStudentCardContent(
        $Data,
        TblPerson $tblPerson,
        AbstractDocument $documentClass,
        TblType $tblType = null
    ) {

        // Profil
        if (($tblStudent = $tblPerson->getStudent())
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                $tblStudentSubjectType))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            $tblStudentSubject = current($tblStudentSubjectList);
            if (($tblSubjectProfile = $tblStudentSubject->getServiceTblSubject())) {
                $Data['Student']['Profile'] = $tblSubjectProfile->getName();
            }
        }

        // Bildungsgang
        if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS'))
            && $tblStudent
        ) {
            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                $tblTransferType);
            if ($tblStudentTransfer) {
                // Abschluss (Bildungsgang)
                $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                if ($tblCourse) {
                    if ($tblCourse->getName() == 'Hauptschule') {
                        $Data['Student']['Course']['Degree']['Main'] = 'X';
                    } elseif ($tblCourse->getName() == 'Realschule') {
                        $Data['Student']['Course']['Degree']['Real'] = 'X';
                    }
                }
            }
        }

        $list = array();
        $listSekII = array();
        if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                if (($tblDivision = $tblDivisionStudent->getTblDivision())
                    && ($tblPrepareList = Prepare::useService()->getPrepareAllByDivision($tblDivision))
                ) {
                    foreach ($tblPrepareList as $tblPrepare) {
                        if ($tblPrepare->getServiceTblGenerateCertificate()
                            && ($tblCertificateType = $tblPrepare->getServiceTblGenerateCertificate()->getServiceTblCertificateType())
                            && ($tblCertificateType->getIdentifier() == 'HALF_YEAR'
                                || $tblCertificateType->getIdentifier() == 'YEAR'
                                || $tblCertificateType->getIdentifier() == 'MID_TERM_COURSE'
                                || $tblCertificateType->getIdentifier() == 'DIPLOMA'
                            )
                            && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                                $tblPerson))
                            && $tblPrepareStudent->isApproved()
                            && $tblPrepareStudent->isPrinted()
                        ) {
                            $tblLevel = false;
                            if (($tblType
                                    && ($tblLevel = $tblDivision->getTblLevel())
                                    && $tblLevel->getServiceTblType()
                                    && $tblType->getId() == $tblLevel->getServiceTblType()->getId())
                                || $tblType === null
                            ) {
                                if ($tblCertificateType->getIdentifier() == 'MID_TERM_COURSE') {
                                    $listSekII[(new DateTime($tblPrepare->getDate()))->format('Y.m.d')] = $tblPrepareStudent;
                                } elseif ($tblCertificateType->getIdentifier() == 'DIPLOMA'
                                    && $tblLevel
                                    && intval($tblLevel->getName()) == 12
                                ) {
                                    // Abiturzeugnis ignorieren
                                } else {
                                    $list[(new DateTime($tblPrepare->getDate()))->format('Y.m.d')] = $tblPrepareStudent;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Sortieren nach Zeugnisdatum
        ksort($list);

        $tblType ? $typeId = $tblType->getId() : $typeId = 0;

        $changeOrientation = false;
        if ($tblType) {
            if ($tblType->getName() == 'Mittelschule / Oberschule') {
                if (($tblSetting = Consumer::useService()->getSetting(
                        'Api',
                        'Education',
                        'Certificate',
                        'OrientationAcronym'
                    ))
                    && $tblSetting->getValue()
                ) {

                } else {
                    $changeOrientation = true;
                }
            }
        }
        $changeProfile = false;
        if ($tblType) {
            if ($tblType->getName() == 'Gymnasium') {
                if (($tblSetting = Consumer::useService()->getSetting(
                        'Api',
                        'Education',
                        'Certificate',
                        'ProfileAcronym'
                    ))
                    && $tblSetting->getValue()
                ) {

                } else {
                    $changeProfile = true;
                }
            }
        }

        $count = 1;
        /** @var TblPrepareStudent $item */
        foreach ($list as $item) {
            if (($tblPrepare = $item->getTblPrepareCertificate())
                && ($tblDivision = $tblPrepare->getServiceTblDivision())
                && ($tblLevel = $tblDivision->getTblLevel())
                && ($tblPrepare->getServiceTblGenerateCertificate())
                && ($tblCertificateType = $tblPrepare->getServiceTblGenerateCertificate()->getServiceTblCertificateType())
                && ($tblYear = $tblDivision->getServiceTblYear())
                && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
            ) {
                if (strlen($tblYear->getName()) > 2) {
                    $year = substr($tblYear->getName(), 2);
                } else {
                    $year = $tblYear->getName();
                }

                $Data['Certificate'][$typeId]['Data' . $count]['Division'] = $tblLevel->getName();
                if ($tblCertificateType->getIdentifier() == 'YEAR'
                    || $tblCertificateType->getIdentifier() == 'DIPLOMA'
                ) {
                    $Data['Certificate'][$typeId]['Data' . $count]['Year'] = $year;
                    $Data['Certificate'][$typeId]['Data' . $count]['HalfYear'] = '2';
                } else {
                    $Data['Certificate'][$typeId]['Data' . $count]['Year'] = $year;
                    $Data['Certificate'][$typeId]['Data' . $count]['HalfYear'] = '1';
                }
                $Data['Certificate'][$typeId]['Data' . $count]['YearForRemark'] = $year;

                // Kopfnoten
                if (($tblGradeTypeList = Gradebook::useService()->getGradeTypeAllByTestType(
                    Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR')))
                ) {
                    foreach ($tblGradeTypeList as $tblGradeType) {
                        if (($tblPrepareGrade = Prepare::useService()->getPrepareGradeByGradeType($tblPrepare,
                            $tblPerson, $tblDivision,
                            Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK'), $tblGradeType))
                        ) {
                            $Data['Certificate'][$typeId]['Data' . $count]['BehaviorGrade'][$tblGradeType->getCode()] = $tblPrepareGrade->getGrade();
                        } else {
                            $Data['Certificate'][$typeId]['Data' . $count]['BehaviorGrade'][$tblGradeType->getCode()] = '&ndash;';
                        }
                    }
                }

                // Fachnoten
                if (($tblDocument = $this->getDocumentByName($documentClass->getName()))
                    && ($tblDocumentSubjectList = $this->getDocumentSubjectListByDocument($tblDocument))
                ) {
                    foreach ($tblDocumentSubjectList as $tblDocumentSubject) {
                        if (($tblSubject = $tblDocumentSubject->getServiceTblSubject())) {
                            $acronym = $tblSubject->getAcronym();
                            if ($changeOrientation && $tblSubject->getAcronym() == 'NK') {
                                if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear($tblPerson, $tblYear))) {
                                    foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                                        if (($tblSubjectTemp = $tblDivisionSubject->getServiceTblSubject())
                                            && Subject::useService()->isOrientation($tblSubjectTemp)
                                        ) {
                                            $tblSubject = $tblSubjectTemp;
                                            break;
                                        }
                                    }
                                }
                            }
                            if ($changeProfile && $tblSubject->getAcronym() == 'PRO') {
                                if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllByPersonAndYear($tblPerson, $tblYear))) {
                                    foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                                        if (($tblSubjectTemp = $tblDivisionSubject->getServiceTblSubject())
                                            && Subject::useService()->isProfile($tblSubjectTemp)
                                        ) {
                                            $tblSubject = $tblSubjectTemp;
                                            break;
                                        }
                                    }
                                }
                            }

                            if (($tblPrepareGrade = Prepare::useService()->getPrepareGradeBySubject($tblPrepare,
                                $tblPerson, $tblDivision, $tblSubject,
                                Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')))
                            ) {
                                $value = trim($tblPrepareGrade->getGrade());
                                if ($value == 'nicht erteilt') {
                                    $value = 'ne';
                                } elseif ($value == 'teilgenommen') {
                                    $value = 't';
                                } elseif ($value == 'Keine Benotung' || $value == 'keine Benotung') {
                                    $value = 'kB';
                                } elseif ($value == 'befreit') {
                                    $value = 'b';
                                } elseif (strlen($value) > 2) {
                                    if (strtolower($value) == 'seepferdchen') {
                                        // Sonderfall Seepferdchen (Schwimmunterricht)
                                        $value = 'Sp';
                                    } else {
                                        // verbale Benotungen einkürzen
                                        $value = substr($value, 0, 2);
                                    }
                                }

                                $Data['Certificate'][$typeId]['Data' . $count]['SubjectGrade'][$acronym]
                                    = $value;
                            } elseif ($tblDocumentSubject->isEssential()) {
                                $Data['Certificate'][$typeId]['Data' . $count]['SubjectGrade'][$acronym]
                                    = '&ndash;';
                            }
                        }
                    }
                }

                $remark = '';
                // Arbeitsgemeinschaften und Bemerkungen
                if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy(
                    $tblPrepare, $tblPerson, 'Team'))
                ) {
                    $remark = 'Arbeitsgemeinschaften: ' . $tblPrepareInformation->getValue() . "\n";
                }
                if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy(
                    $tblPrepare, $tblPerson, 'Remark'))
                ) {
                    $remark .= $tblPrepareInformation->getValue();
                }
                $Data['Certificate'][$typeId]['Data' . $count]['Remark'] = $remark;

                $date = new DateTime($tblPrepare->getDate());
                $Data['Certificate'][$typeId]['Data' . $count]['CertificateDate'] = $date->format('d.m.y');
                $transferRemark = '&ndash;';
                if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy($tblPrepare, $tblPerson,
                    'Transfer'))
                ) {
                    if ($tblPrepareInformation->getValue() == 'wird nicht versetzt') {
                        $transferRemark = 'n.v.';
                    } elseif ($tblPrepareInformation->getValue() == 'wird versetzt') {
                        $transferRemark = 'v.';
                    }
                }
                $Data['Certificate'][$typeId]['Data' . $count]['TransferRemark'] = $transferRemark;
                $Data['Certificate'][$typeId]['Data' . $count]['Absence']
                    = $tblPrepareStudent->getExcusedDays()
                        + ($tblPrepareStudent->getExcusedDaysFromLessons() ? $tblPrepareStudent->getExcusedDaysFromLessons() : 0)
                        + $tblPrepareStudent->getUnexcusedDays()
                        + ($tblPrepareStudent->getUnexcusedDaysFromLessons() ? $tblPrepareStudent->getUnexcusedDaysFromLessons() : 0);
            }
            $count++;
        }

        // SekII
        // Sortieren nach Zeugnisdatum
        ksort($listSekII);
        // offset = 100;
        $count = 101;
        /** @var TblPrepareStudent $item */
        foreach ($listSekII as $item) {
            if (($tblPrepare = $item->getTblPrepareCertificate())
                && ($tblDivision = $tblPrepare->getServiceTblDivision())
                && ($tblLevel = $tblDivision->getTblLevel())
                && ($tblPrepare->getServiceTblGenerateCertificate())
                && ($tblCertificateType = $tblPrepare->getServiceTblGenerateCertificate()->getServiceTblCertificateType())
                && ($tblYear = $tblDivision->getServiceTblYear())
                && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
            ) {
                if (strlen($tblYear->getName()) > 2) {
                    $year = substr($tblYear->getName(), 2);
                } else {
                    $year = $tblYear->getName();
                }

                $midTerm = 'I';
                if (($tblAppointedDateTask = $tblPrepare->getServiceTblAppointedDateTask())
                    && $tblYear
                    && ($tblPeriodList = $tblYear->getTblPeriodAll($tblDivision))
                    && ($tblPeriod = $tblAppointedDateTask->getServiceTblPeriodByDivision($tblDivision))
                    && ($tblFirstPeriod = current($tblPeriodList))
                    && $tblPeriod->getId() != $tblFirstPeriod->getId()
                ) {
                    $midTerm = 'II';
                }

                $Data['Certificate'][$typeId]['Data' . $count]['Division'] = $tblLevel->getName();
                $Data['Certificate'][$typeId]['Data' . $count]['Year'] = $year;
                $Data['Certificate'][$typeId]['Data' . $count]['MidTerm'] = $midTerm;
                $Data['Certificate'][$typeId]['Data' . $count]['YearForRemark'] = $year;

                // Fachnoten
                if (($tblDocument = $this->getDocumentByName($documentClass->getName()))
                    && ($tblDocumentSubjectList = $this->getDocumentSubjectListByDocument($tblDocument))
                ) {
                    foreach ($tblDocumentSubjectList as $tblDocumentSubject) {
                        if (($tblSubject = $tblDocumentSubject->getServiceTblSubject())
                            && ($tblPrepareGrade = Prepare::useService()->getPrepareGradeBySubject($tblPrepare,
                                $tblPerson, $tblDivision, $tblSubject,
                                Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')))
                        ) {
                            $value = trim($tblPrepareGrade->getGrade());
                            if ($value == 'nicht erteilt') {
                                $value = 'ne';
                            } elseif ($value == 'teilgenommen') {
                                $value = 't';
                            } elseif ($value == 'Keine Benotung' || $value == 'keine Benotung') {
                                $value = 'kB';
                            } elseif ($value == 'befreit') {
                                $value = 'b';
                            }
                            $Data['Certificate'][$typeId]['Data' . $count]['SubjectGrade'][$tblSubject->getAcronym()]
                                = $value;
                        } elseif ($tblDocumentSubject->isEssential()) {
                            $Data['Certificate'][$typeId]['Data' . $count]['SubjectGrade'][$tblSubject->getAcronym()]
                                = '&ndash;';
                        }
                    }
                }

                $remark = '';
                // Arbeitsgemeinschaften und Bemerkungen
                if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy(
                    $tblPrepare, $tblPerson, 'Team'))
                ) {
                    $remark = 'Arbeitsgemeinschaften: ' . $tblPrepareInformation->getValue() . "\n";
                }
                if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy(
                    $tblPrepare, $tblPerson, 'Remark'))
                ) {
                    $remark .= $tblPrepareInformation->getValue();
                }
                $Data['Certificate'][$typeId]['Data' . $count]['Remark'] = $remark;

                $date = new DateTime($tblPrepare->getDate());
                $Data['Certificate'][$typeId]['Data' . $count]['CertificateDate'] = $date->format('d.m.y');
            }
            $count++;
        }

        return $Data;
    }

    /**
     * @param TblPerson $tblPerson
     * @param AbstractDocument $documentClass
     * @return TblSubject[]|false
     */
    public function getStudentCardSubjectListByPerson(TblPerson $tblPerson, AbstractDocument $documentClass)
    {

        $resultList = array();
        $list = array();
        if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                if (($tblDivision = $tblDivisionStudent->getTblDivision())
                    && ($tblPrepareList = Prepare::useService()->getPrepareAllByDivision($tblDivision))
                ) {
                    foreach ($tblPrepareList as $tblPrepare) {
                        if ($tblPrepare->getServiceTblGenerateCertificate()
                            && ($tblCertificateType = $tblPrepare->getServiceTblGenerateCertificate()->getServiceTblCertificateType())
                            && ($tblCertificateType->getIdentifier() == 'HALF_YEAR' || $tblCertificateType->getIdentifier() == 'YEAR')
                            && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                                $tblPerson))
                            && $tblPrepareStudent->isApproved()
                            && $tblPrepareStudent->isPrinted()
                        ) {
                            $list[(new DateTime($tblPrepare->getDate()))->format('Y.m.d')] = $tblPrepareStudent;
                        }
                    }
                }
            }
        }

        // Sortieren nach Zeugnisdatum
        ksort($list);

        /** @var TblPrepareStudent $item */
        foreach ($list as $item) {
            if (($tblPrepare = $item->getTblPrepareCertificate())
                && ($tblDivision = $tblPrepare->getServiceTblDivision())
                && ($tblPrepare->getServiceTblGenerateCertificate())
                && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
            ) {
                // Fachnoten
                if (($tblDocument = $this->getDocumentByName($documentClass->getName()))
                    && ($tblDocumentSubjectList = $this->getDocumentSubjectListByDocument($tblDocument))
                ) {
                    foreach ($tblDocumentSubjectList as $tblDocumentSubject) {
                        if (($tblSubject = $tblDocumentSubject->getServiceTblSubject())
                            && ($tblPrepareGrade = Prepare::useService()->getPrepareGradeBySubject($tblPrepare,
                                $tblPerson, $tblDivision, $tblSubject,
                                Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')))
                        ) {
                            $resultList[$tblSubject->getId()] = $tblSubject;
                        }
                    }
                }
            }

        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return TblSubject[]|false
     */
    public function getStudentCardSubjectListForSekIIByPerson(TblPerson $tblPerson)
    {

        $resultList = array();
        $list = array();
        if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                if (($tblDivision = $tblDivisionStudent->getTblDivision())
                    && ($tblPrepareList = Prepare::useService()->getPrepareAllByDivision($tblDivision))
                ) {
                    foreach ($tblPrepareList as $tblPrepare) {
                        if ($tblPrepare->getServiceTblGenerateCertificate()
                            && ($tblCertificateType = $tblPrepare->getServiceTblGenerateCertificate()->getServiceTblCertificateType())
                            && ($tblCertificateType->getIdentifier() == 'MID_TERM_COURSE')
                            && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                                $tblPerson))
                            && $tblPrepareStudent->isApproved()
                            && $tblPrepareStudent->isPrinted()
                        ) {
                            $list[(new DateTime($tblPrepare->getDate()))->format('Y.m.d')] = $tblPrepareStudent;
                        }
                    }
                }
            }
        }

        // Sortieren nach Zeugnisdatum
        ksort($list);

        $advancedCourses = array();
        $basicCourses = array();
        $tempArray = array();
        /** @var TblPrepareStudent $item */
        foreach ($list as $item) {
            if (($tblPrepare = $item->getTblPrepareCertificate())
                && ($tblDivision = $tblPrepare->getServiceTblDivision())
            ) {
                list($tempArray, $basicCourses) = $this->getCourses($tblPerson, $tblDivision, $tempArray,
                    $basicCourses);
                if (empty($advancedCourses)) {
                    $advancedCourses = $tempArray;
                }
            }
        }

        $resultList[] = isset($advancedCourses[0]) ? $advancedCourses[0] : null;
        $resultList[] = isset($advancedCourses[1]) ? $advancedCourses[1] : null;
        if (!empty($basicCourses)) {
            // leere Zeile
            $resultList[] = null;
            ksort($basicCourses);
            foreach ($basicCourses as $tblSubject) {
                $resultList[] = $tblSubject;
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param $advancedCourses
     * @param $basicCourses
     *
     * @return array
     */
    private function getCourses(TblPerson $tblPerson, TblDivision $tblDivision, $advancedCourses, $basicCourses)
    {

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
                                if ($tblSubjectGroup->isAdvancedCourse()) {
                                    if ($tblSubject->getName() == 'Deutsch' || $tblSubject->getName() == 'Mathematik') {
                                        $advancedCourses[0] = $tblSubject;
                                    } else {
                                        $advancedCourses[1] = $tblSubject;
                                    }
                                } else {
                                    $basicCourses[$tblSubject->getAcronym()] = $tblSubject;
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
     * @param TblPerson $tblPerson
     *
     * @return false|TblType[]
     */
    public function getSchoolTypeListForStudentCard(TblPerson $tblPerson)
    {

        $list = array();
        if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                if (($tblDivision = $tblDivisionStudent->getTblDivision())
                    && ($tblLevel = $tblDivision->getTblLevel())
                    && ($tblType = $tblLevel->getServiceTblType())
                ) {
                    $list[$tblType->getId()] = $tblType;
                }
            }
        }

        return empty($list) ? false : $list;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblType $tblType
     *
     * @return false|TblPrepareStudent[]
     */
    public function getPrepareStudentListForStudentCard(TblPerson $tblPerson, TblType $tblType = null)
    {

        $list = array();
        if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson))) {
            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                if (($tblDivision = $tblDivisionStudent->getTblDivision())
                    && ($tblPrepareList = Prepare::useService()->getPrepareAllByDivision($tblDivision))
                ) {
                    foreach ($tblPrepareList as $tblPrepare) {
                        if ($tblPrepare->getServiceTblGenerateCertificate()
                            && ($tblCertificateType = $tblPrepare->getServiceTblGenerateCertificate()->getServiceTblCertificateType())
                            && ($tblCertificateType->getIdentifier() == 'HALF_YEAR'
                                || $tblCertificateType->getIdentifier() == 'YEAR'
                                || $tblCertificateType->getIdentifier() == 'MID_TERM_COURSE'
                            )
                            && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                                $tblPerson))
                            && $tblPrepareStudent->isApproved()
                            && $tblPrepareStudent->isPrinted()
                        ) {
                            if (($tblType
                                    && ($tblLevel = $tblDivision->getTblLevel())
                                    && $tblLevel->getServiceTblType()
                                    && $tblType->getId() == $tblLevel->getServiceTblType()->getId())
                                || $tblType === null
                            ) {
                                $list[(new DateTime($tblPrepare->getDate()))->format('Y.m.d')] = $tblPrepareStudent;
                            }
                        }
                    }
                }
            }
        }

        ksort($list);

        return empty($list) ? false : $list;
    }

    /**
     * @param IFormInterface $Form
     * @param TblDocument $tblDocument
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function createDocumentSubjects(
        IFormInterface $Form,
        TblDocument $tblDocument,
        $Data
    ) {

        /**
         * Skip to Frontend
         */
        if (empty($Data)) {
            return $Form;
        }

        foreach ($Data as $ranking => $item) {
            $isEssential = isset($item['IsEssential']);
            if ($item['Subject'] == TblSubject::PSEUDO_ORIENTATION_ID) {
                $tblSubject = Subject::useService()->getPseudoOrientationSubject();
            } elseif ($item['Subject'] == TblSubject::PSEUDO_PROFILE_ID) {
                $tblSubject = Subject::useService()->getPseudoProfileSubject();
            } else {
                $tblSubject = Subject::useService()->getSubjectById($item['Subject']);
            }
            if (($tblDocumentSubject = $this->getDocumentSubjectByDocumentAndRanking($tblDocument, $ranking))) {
                if ($tblSubject) {
                    (new Data($this->getBinding()))->updateDocumentSubject(
                        $tblDocumentSubject, $tblSubject, $isEssential
                    );
                } else {
                    (new Data($this->getBinding()))->destroyDocumentSubject(
                        $tblDocumentSubject
                    );
                }
            } else {
                if ($tblSubject) {
                    (new Data($this->getBinding()))->createDocumentSubject($tblDocument, $ranking, $tblSubject,
                        $isEssential);
                }
            }
        }

        return new Success('Die Fächer wurden der Schülerkartei erfolgreich zugewiesen.',
                new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Document/Standard/StudentCard/Setting', Redirect::TIMEOUT_SUCCESS);
    }

    /**
     * @param $Content
     *
     * @return array
     */
    public function setKamenzReportOsContent(
        $Content
    ) {

        return KamenzReportService::setKamenzReportOsContent($Content);
    }

    /**
     * @param $Content
     *
     * @return array
     */
    public function setKamenzReportGsContent(
        $Content
    ) {

        return KamenzReportService::setKamenzReportGsContent($Content);
    }

    /**
     * @param $Content
     *
     * @return array
     */
    public function setKamenzReportGymContent(
        $Content
    ) {

        return KamenzReportService::setKamenzReportGymContent($Content);
    }

    /**
     * @param $Content
     *
     * @return array
     */
    public function setKamenzReportBFSContent(
        $Content
    ) {
        if (($tblType = Type::useService()->getTypeByName('Berufsfachschule'))) {
            return KamenzReportService::setKamenzReportBFSContent($Content, $tblType);
        } else {
            return  array();
        }
    }

    /**
     * @param $Content
     *
     * @return array
     */
    public function setKamenzReportFSContent(
        $Content
    ) {
        if (($tblType = Type::useService()->getTypeByName('Fachschule'))) {
            return KamenzReportService::setKamenzReportBFSContent($Content, $tblType);
        } else {
            return  array();
        }
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return array
     */
    public function getEnrollmentDocumentData(TblPerson $tblPerson): array
    {
        $Data['PersonId'] = $tblPerson->getId();
        $Data['FirstLastName'] = $tblPerson->getFirstSecondName().' '.$tblPerson->getLastName();
        $Data['Date'] = (new DateTime())->format('d.m.Y');

        if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
            if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                $Data['Birthday'] = $tblCommonBirthDates->getBirthday();
                $Data['Birthplace'] = $tblCommonBirthDates->getBirthplace();
                if (($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                    $Data['Gender'] = $tblCommonGender->getName();
                }
            }
        }

        // Prepare LeaveDate
        $Now = new DateTime('now');
        // increase year if date after 31.07.20xx
        if ($Now > new DateTime('31.07.'.$Now->format('Y'))) {
            $Now->add(new DateInterval('P1Y'));
        }
        $MaxDate = new DateTime('31.07.'.$Now->format('Y'));
        $DateString = $MaxDate->format('d.m.Y');
        $Data['LeaveDate'] = $DateString;

        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
        if ($tblStudent) {
            // Schuldaten der Schule des Schülers
            if (($tblCompanySchool = Student::useService()->getCurrentSchoolByPerson($tblPerson))) {
                $Data['School'] = $tblCompanySchool->getName();
                $Data['SchoolExtended'] = $tblCompanySchool->getExtendedName();
                $tblAddressSchool = Address::useService()->getAddressByCompany($tblCompanySchool);
                if ($tblAddressSchool) {
                    $Data['SchoolAddressStreet'] = $tblAddressSchool->getStreetName().' '.$tblAddressSchool->getStreetNumber();
                    $tblCitySchool = $tblAddressSchool->getTblCity();
                    if ($tblCitySchool) {
                        $Data['SchoolAddressDistrict'] = $tblCitySchool->getDistrict();
                        $Data['SchoolAddressCity'] = $tblCitySchool->getCode().' '.$tblCitySchool->getName();
                        $Data['Place'] = $tblCitySchool->getName();
                    }
                }
            }
            $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE');
            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                $tblStudentTransferType);
            if ($tblStudentTransfer) {
                $transferDate = $tblStudentTransfer->getTransferDate();
                if ($transferDate) {
                    if ($MaxDate > new DateTime($transferDate)) {
                        $DateString = $transferDate;
                        // correct leaveDate if necessary
                        $Data['LeaveDate'] = $DateString;
                    }
                }
            }
        }

        // Aktuelle Klasse
        $tblYearList = Term::useService()->getYearByNow();
        if ($tblYearList) {
            foreach ($tblYearList as $tblYear) {
                $tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPerson, $tblYear);
                if ($tblDivision && $tblDivision->getTblLevel() && $tblDivision->getTblLevel()->getName() != '') {
                    $Data['Division'] = $tblDivision->getTblLevel()->getName();
                }
            }
        }

        // Hauptadresse Schüler
        $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
        if ($tblAddress) {
            $Data['AddressStreet'] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
            $tblCity = $tblAddress->getTblCity();
            if ($tblCity) {
                $Data['AddressPLZ'] = $tblCity->getCode();
                $Data['AddressCity'] = $tblCity->getName();
                $Data['AddressDistrict'] = $tblCity->getDistrict();
            }
        }

        return $Data;
    }
}