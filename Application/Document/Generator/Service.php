<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.03.2017
 * Time: 13:48
 */

namespace SPHERE\Application\Document\Generator;


use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Service\Data;
use SPHERE\Application\Document\Generator\Service\Entity\TblDocument;
use SPHERE\Application\Document\Generator\Service\Entity\TblDocumentSubject;
use SPHERE\Application\Document\Generator\Service\Setup;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
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
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
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
     * @param TblPerson $tblPerson
     * @param AbstractDocument $documentClass
     *
     * @return array
     */
    public function setStudentCardContent(TblPerson $tblPerson, AbstractDocument $documentClass)
    {

        $Data['Person']['Id'] = $tblPerson->getId();

        $list = array();
        if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson))){
            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                if (($tblDivision = $tblDivisionStudent->getTblDivision())
                    && ($tblPrepareList = Prepare::useService()->getPrepareAllByDivision($tblDivision))
                ) {
                    foreach ($tblPrepareList as $tblPrepare) {
                        if($tblPrepare->getServiceTblGenerateCertificate()
                            && ($tblCertificateType = $tblPrepare->getServiceTblGenerateCertificate()->getServiceTblCertificateType())
                            && ($tblCertificateType->getIdentifier() == 'HALF_YEAR' || $tblCertificateType->getIdentifier() == 'YEAR')
                            && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                            && $tblPrepareStudent->isApproved()
                            && $tblPrepareStudent->isPrinted()
                        ) {
                            $list[(new \DateTime($tblPrepare->getDate()))->format('Y.m.d')] = $tblPrepareStudent;
                        }
                    }
                }
            }
        }

        // Sortieren nach Zeugnisdatum
        ksort($list);

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

                $Data['Certificate']['Data' . $count]['Division'] = $tblLevel->getName();
                if ($tblCertificateType->getIdentifier() == 'YEAR') {
                    $Data['Certificate']['Data' . $count]['Year'] = $year;
                    $Data['Certificate']['Data' . $count]['HalfYear'] = '&ndash;';
                } else {
                    $Data['Certificate']['Data' . $count]['Year'] = '&ndash;';
                    $Data['Certificate']['Data' . $count]['HalfYear'] = $year;
                }

                // Kopfnoten
                if (($tblGradeTypeList = Gradebook::useService()->getGradeTypeAllByTestType(
                    Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR')))
                ) {
                    foreach ($tblGradeTypeList as $tblGradeType) {
                        if (($tblPrepareGrade = Prepare::useService()->getPrepareGradeByGradeType($tblPrepare,
                            $tblPerson, $tblDivision, Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK'), $tblGradeType))
                        ) {
                            $Data['Certificate']['Data' . $count]['BehaviorGrade'][$tblGradeType->getCode()] = $tblPrepareGrade->getGrade();
                        } else {
                            $Data['Certificate']['Data' . $count]['BehaviorGrade'][$tblGradeType->getCode()] = '&ndash;';
                        }
                    }
                }

                // Fachnoten
                if (($tblDocument = $this->getDocumentByName($documentClass->getName()))
                    && ($tblDocumentSubjectList = $this->getDocumentSubjectListByDocument($tblDocument))) {
                    foreach ($tblDocumentSubjectList as $tblDocumentSubject) {
                        if (($tblSubject = $tblDocumentSubject->getServiceTblSubject())
                            && ($tblPrepareGrade = Prepare::useService()->getPrepareGradeBySubject($tblPrepare,
                                $tblPerson, $tblDivision, $tblSubject,
                                Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')))
                        ) {
                            $value = $tblPrepareGrade->getGrade();
                            if ($value == 'nicht erteilt') {
                                $value = 'n.e.';
                            } elseif ($value == 'teilgenommen') {
                                $value = 't.';
                            } elseif ($value == 'Keine Benotung') {
                                $value = 'K.B.';
                            } elseif ($value == 'befreit') {
                                $value = 'b.';
                            }
                            $Data['Certificate']['Data' . $count]['SubjectGrade'][$tblSubject->getAcronym()]
                                = $value;
                        } elseif ($tblDocumentSubject->isEssential()) {
                            $Data['Certificate']['Data' . $count]['SubjectGrade'][$tblSubject->getAcronym()]
                                = '&ndash;';
                        }
                    }
                }

                $date = new \DateTime($tblPrepare->getDate());
                $Data['Certificate']['Data' . $count]['CertificateDate'] = $date->format('d.m.y');
                // ToDo weitere Versetzungsvermerke
                $transferRemark = '&ndash;';
                if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy($tblPrepare, $tblPerson, 'Transfer'))) {
                    if ($tblPrepareInformation->getValue() == 'wird nicht versetzt') {
                        $transferRemark = 'n.v.';
                    }
                }
                $Data['Certificate']['Data' . $count]['TransferRemark'] = $transferRemark;
                $Data['Certificate']['Data' . $count]['Absence'] = $tblPrepareStudent->getExcusedDays() + $tblPrepareStudent->getUnexcusedDays();
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
        if (($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson))){
            foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                if (($tblDivision = $tblDivisionStudent->getTblDivision())
                    && ($tblPrepareList = Prepare::useService()->getPrepareAllByDivision($tblDivision))
                ) {
                    foreach ($tblPrepareList as $tblPrepare) {
                        if($tblPrepare->getServiceTblGenerateCertificate()
                            && ($tblCertificateType = $tblPrepare->getServiceTblGenerateCertificate()->getServiceTblCertificateType())
                            && ($tblCertificateType->getIdentifier() == 'HALF_YEAR' || $tblCertificateType->getIdentifier() == 'YEAR')
                            && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                            && $tblPrepareStudent->isApproved()
                            && $tblPrepareStudent->isPrinted()
                        ) {
                            $list[(new \DateTime($tblPrepare->getDate()))->format('Y.m.d')] = $tblPrepareStudent;
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
}