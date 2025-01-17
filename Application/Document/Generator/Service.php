<?php
namespace SPHERE\Application\Document\Generator;

use DateTime;
use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Web\Web;
use SPHERE\Application\Document\Generator\Service\Data;
use SPHERE\Application\Document\Generator\Service\Entity\TblDocument;
use SPHERE\Application\Document\Generator\Service\Entity\TblDocumentSubject;
use SPHERE\Application\Document\Generator\Service\Kamenz\KamenzReportService;
use SPHERE\Application\Document\Generator\Service\Setup;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Sorter;

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
     * @param TblDocument $tblDocument
     * @param integer $ranking
     *
     * @return false|TblDocumentSubject
     */
    public function getDocumentSubjectByDocumentAndRanking(TblDocument $tblDocument, int $ranking)
    {
        return (new Data($this->getBinding()))->getDocumentSubjectByDocumentAndRanking($tblDocument, $ranking);
    }

    /**
     * @param TblDocument $tblDocument
     *
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
     * @param TblType|null $tblType
     *
     * @return array
     */
    public function setStudentCardContent(
        array $Data,
        TblPerson $tblPerson,
        AbstractDocument $documentClass,
        TblType $tblType = null
    ): array {
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
        if (($tblStudentEducation = $documentClass->getStudentEducation())
            && ($tblCourse = $tblStudentEducation->getServiceTblCourse())
        ) {
            if ($tblCourse->getName() == 'Hauptschule') {
                $Data['Student']['Course']['Degree']['Main'] = 'X';
            } elseif ($tblCourse->getName() == 'Realschule') {
                $Data['Student']['Course']['Degree']['Real'] = 'X';
            }
        }

        $list = array();
        $listSekII = array();
        if (($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPerson($tblPerson))) {
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                if (($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                    && $tblPrepare->getServiceTblGenerateCertificate()
                    && ($tblCertificateType = $tblPrepare->getServiceTblGenerateCertificate()->getServiceTblCertificateType())
                    && ($tblCertificateType->getIdentifier() == 'HALF_YEAR'
                        || $tblCertificateType->getIdentifier() == 'YEAR'
                        || $tblCertificateType->getIdentifier() == 'MID_TERM_COURSE'
                        || $tblCertificateType->getIdentifier() == 'DIPLOMA'
                    )
//                    && $tblPrepareStudent->isApproved()
//                    && $tblPrepareStudent->isPrinted()
                    && ($tblYear = $tblPrepare->getYear())
                    && ($tblStudentEducationPrepare = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                    && ($level = $tblStudentEducationPrepare->getLevel())
                    && ($tblSchoolType = $tblStudentEducationPrepare->getServiceTblSchoolType())
                    && (!$tblType || $tblSchoolType->getId() == $tblType->getId())
                ) {
                    if ($tblCertificateType->getIdentifier() == 'MID_TERM_COURSE') {
                        $listSekII[(new DateTime($tblPrepare->getDate()))->format('Y.m.d')] = $tblPrepareStudent;
                    } elseif ($tblCertificateType->getIdentifier() == 'DIPLOMA'
                        && ($level == 12 || $level == 13)
                    ) {
                        // Abiturzeugnis ignorieren
                    } else {
                        $list[(new DateTime($tblPrepare->getDate()))->format('Y.m.d')] = $tblPrepareStudent;
                    }
                }
            }
        }

        // Sortieren nach Zeugnisdatum
        ksort($list);

        $tblType ? $typeId = $tblType->getId() : $typeId = 0;

        $count = 1;
        /** @var TblPrepareStudent $item */
        foreach ($list as $item) {
            if (($tblPrepare = $item->getTblPrepareCertificate())
                && ($tblPrepare->getServiceTblGenerateCertificate())
                && ($tblCertificateType = $tblPrepare->getServiceTblGenerateCertificate()->getServiceTblCertificateType())
                && ($tblYear = $tblPrepare->getYear())
                && ($tblStudentEducationPrepare = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                && ($level = $tblStudentEducationPrepare->getLevel())
            ) {
                if (strlen($tblYear->getName()) > 2) {
                    $year = substr($tblYear->getName(), 2);
                } else {
                    $year = $tblYear->getName();
                }

                $Data['Certificate'][$typeId]['Data' . $count]['Division'] = $level;
                $Data['Certificate'][$typeId]['Data' . $count]['Year'] = $year;
                if ($tblCertificateType->getIdentifier() == 'YEAR'
                    || $tblCertificateType->getIdentifier() == 'DIPLOMA'
                ) {
                    $Data['Certificate'][$typeId]['Data' . $count]['HalfYear'] = '2';
                } else {
                    $Data['Certificate'][$typeId]['Data' . $count]['HalfYear'] = '1';
                }
                $Data['Certificate'][$typeId]['Data' . $count]['YearForRemark'] = $year;

                // Kopfnoten
                if (($tblGradeTypeList = Grade::useService()->getGradeTypeList(true))) {
                    foreach ($tblGradeTypeList as $tblGradeType) {
                        if (($tblTaskGrade = Prepare::useService()->getPrepareGradeByGradeType(
                            $tblPrepare,
                            $tblPerson,
                            $tblGradeType
                        ))) {
                            $Data['Certificate'][$typeId]['Data' . $count]['BehaviorGrade'][$tblGradeType->getCode()] = $tblTaskGrade->getGrade();
                        } else {
                            $Data['Certificate'][$typeId]['Data' . $count]['BehaviorGrade'][$tblGradeType->getCode()] = '&ndash;';
                        }
                    }
                }

                // Fachnoten
                if (($tblDocument = $this->getDocumentByName($documentClass->getName()))
                    && ($tblDocumentSubjectList = $this->getDocumentSubjectListByDocument($tblDocument))
                    && ($tblAppointedDateTask = $tblPrepare->getServiceTblAppointedDateTask())
                ) {
                    foreach ($tblDocumentSubjectList as $tblDocumentSubject) {
                        if (($tblSubject = $tblDocumentSubject->getServiceTblSubject())) {
                            $acronym = $tblSubject->getAcronym();

                            if (($tblTaskGrade = Grade::useService()->getTaskGradeByPersonAndTaskAndSubject($tblPerson, $tblAppointedDateTask, $tblSubject))) {
                                $value = trim($tblTaskGrade->getDisplayGrade());
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

                                $Data['Certificate'][$typeId]['Data' . $count]['SubjectGrade'][$acronym] = $value;
                            } elseif ($tblDocumentSubject->isEssential()) {
                                $Data['Certificate'][$typeId]['Data' . $count]['SubjectGrade'][$acronym] = '&ndash;';
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
                    = $item->getExcusedDays()
                        + ($item->getExcusedDaysFromLessons() ?: 0)
                        + $item->getUnexcusedDays()
                        + ($item->getUnexcusedDaysFromLessons() ?: 0);
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
                && ($tblYear = $tblPrepare->getYear())
                && ($tblAppointedDateTask = $tblPrepare->getServiceTblAppointedDateTask())
                && ($tblStudentEducationPrepare = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                && ($level = $tblStudentEducationPrepare->getLevel())
            ) {
                if (strlen($tblYear->getName()) > 2) {
                    $year = substr($tblYear->getName(), 2);
                } else {
                    $year = $tblYear->getName();
                }

                $midTerm = 'I';
                if (($date = $tblPrepare->getDateTime())
                    && ($month = intval($date->format('m')))
                    && $month > 3 && $month < 9
                ) {
                    $midTerm = 'II';
                }

                $Data['Certificate'][$typeId]['Data' . $count]['Division'] = $level;
                $Data['Certificate'][$typeId]['Data' . $count]['Year'] = $year;
                $Data['Certificate'][$typeId]['Data' . $count]['MidTerm'] = $midTerm;
                $Data['Certificate'][$typeId]['Data' . $count]['YearForRemark'] = $year;

                // Fachnoten
                if (($tblDocument = $this->getDocumentByName($documentClass->getName()))
                    && ($tblDocumentSubjectList = $this->getDocumentSubjectListByDocument($tblDocument))
                ) {
                    foreach ($tblDocumentSubjectList as $tblDocumentSubject) {
                        if (($tblSubject = $tblDocumentSubject->getServiceTblSubject())
                            && ($tblTaskGrade = Grade::useService()->getTaskGradeByPersonAndTaskAndSubject($tblPerson, $tblAppointedDateTask, $tblSubject))
                        ) {
                            $value = trim($tblTaskGrade->getGrade());
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
        if (($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPerson($tblPerson))) {
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                if (($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                    && $tblPrepare->getServiceTblGenerateCertificate()
                    && ($tblCertificateType = $tblPrepare->getServiceTblGenerateCertificate()->getServiceTblCertificateType())
                    && ($tblCertificateType->getIdentifier() == 'HALF_YEAR'
                        || $tblCertificateType->getIdentifier() == 'YEAR'
                    )
//                    && $tblPrepareStudent->isApproved()
//                    && $tblPrepareStudent->isPrinted()
                ) {
                    $list[(new DateTime($tblPrepare->getDate()))->format('Y.m.d')] = $tblPrepareStudent;
                }
            }
        }

        // Sortieren nach Zeugnisdatum
        ksort($list);

        /** @var TblPrepareStudent $item */
        foreach ($list as $item) {
            if (($tblPrepare = $item->getTblPrepareCertificate())
                && ($tblAppointedDateTask = $tblPrepare->getServiceTblAppointedDateTask())
            ) {
                // Fachnoten
                if (($tblDocument = $this->getDocumentByName($documentClass->getName()))
                    && ($tblDocumentSubjectList = $this->getDocumentSubjectListByDocument($tblDocument))
                ) {
                    foreach ($tblDocumentSubjectList as $tblDocumentSubject) {
                        if (($tblSubject = $tblDocumentSubject->getServiceTblSubject())
                            && (Grade::useService()->getTaskGradeByPersonAndTaskAndSubject($tblPerson, $tblAppointedDateTask, $tblSubject))
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
        list($advancedCourses, $basicCourses) = DivisionCourse::useService()->getCoursesForStudent($tblPerson);
        if (!empty($advancedCourses)) {
            $resultList = $advancedCourses;
        }
        if (!empty($basicCourses)) {
            $resultList = array_merge($resultList, $basicCourses);
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblType[]
     */
    public function getSchoolTypeListForStudentCard(TblPerson $tblPerson)
    {
        $list = array();
        if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListByPerson($tblPerson))) {
            foreach ($tblStudentEducationList as $tblStudentEducation) {
                if (!$tblStudentEducation->isInActive()
                    && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                    && !isset($list[$tblSchoolType->getId()])
                ) {
                    $list[$tblSchoolType->getId()] = $tblSchoolType;
                }
            }
        }

        return empty($list) ? false : $list;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblType|null $tblType
     *
     * @return false|TblPrepareStudent[]
     */
    public function getPrepareStudentListForStudentCard(TblPerson $tblPerson, TblType $tblType = null)
    {
        $list = array();
        if (($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPerson($tblPerson))) {
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                if (($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
                    && $tblPrepare->getServiceTblGenerateCertificate()
                    && ($tblCertificateType = $tblPrepare->getServiceTblGenerateCertificate()->getServiceTblCertificateType())
                    && ($tblCertificateType->getIdentifier() == 'HALF_YEAR'
                        || $tblCertificateType->getIdentifier() == 'YEAR'
                        || $tblCertificateType->getIdentifier() == 'MID_TERM_COURSE'
                    )
//                    && $tblPrepareStudent->isApproved()
//                    && $tblPrepareStudent->isPrinted()
                    && ($tblYear = $tblPrepare->getYear())
                    && ($tblStudentEducationPrepare = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                    && ($tblSchoolType = $tblStudentEducationPrepare->getServiceTblSchoolType())
                    && (!$tblType || $tblSchoolType->getId() == $tblType->getId())
                ) {
                    $list[(new DateTime($tblPrepare->getDate()))->format('Y.m.d')] = $tblPrepareStudent;
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
    public function setKamenzReportOsContent($Content): array
    {
        return KamenzReportService::setKamenzReportOsContent($Content);
    }

    /**
     * @param $Content
     *
     * @return array
     */
    public function setKamenzReportGsContent($Content): array
    {
        return KamenzReportService::setKamenzReportGsContent($Content);
    }

    /**
     * @param $Content
     *
     * @return array
     */
    public function setKamenzReportGymContent($Content): array
    {
        return KamenzReportService::setKamenzReportGymContent($Content);
    }

    /**
     * @param $Content
     *
     * @return array
     */
    public function setKamenzReportBFSContent($Content): array
    {
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
    public function setKamenzReportFSContent($Content): array
    {
        if (($tblType = Type::useService()->getTypeByName('Fachschule'))) {
            return KamenzReportService::setKamenzReportBFSContent($Content, $tblType);
        } else {
            return  array();
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param ?TblYear $tblYear
     * @param array $Data
     *
     * @return array
     */
    public function getEnrollmentDocumentData(TblPerson $tblPerson, ?TblYear $tblYear = null, array $Data = array()): array
    {

//        $Data['PersonId'] = $tblPerson->getId();
        $Data['FirstLastName'] = $tblPerson->getFirstSecondName().' '.$tblPerson->getLastName();
        $Data['Date'] = isset($Data['Date']) ? $Data['Date'] : (new DateTime())->format('d.m.Y');
        $Data['Birthday'] = '';
        $Data['Birthplace'] = '';
        $Data['Gender'] = '';
        $Data['School'] = '';
        $Data['SchoolExtended'] = '';
        $Data['SchoolAddressStreet'] = '';
        $Data['SchoolAddressDistrict'] = '';
        $Data['SchoolAddressCity'] = '';
        $Data['Place'] = '';
        $Data['Division'] = '';
        $Data['AddressStreet'] = '';
        $Data['AddressPLZ'] = '';
        $Data['AddressCity'] = '';
        $Data['AddressDistrict'] = '';

        if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
            if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                $Data['Birthday'] = $tblCommonBirthDates->getBirthday();
                $Data['Birthplace'] = $tblCommonBirthDates->getBirthplace();
                if (($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                    $Data['Gender'] = $tblCommonGender->getName();
                }
            }
        }
        if ($tblYear) {
            $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
        } else {
            $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson);
        }

        // Klasse bei Abgängern
        if (!$tblStudentEducation) {
            if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListByPerson($tblPerson))) {
                /** @var TblStudentEducation $tblStudentEducation */
                $tblStudentEducation = current($this->getSorter($tblStudentEducationList)->sortObjectBy('YearNameForSorter', null, Sorter::ORDER_DESC));
            }
        }

        $MaxDate = null;
        if ($tblStudentEducation) {
            // Schuldaten der Schule des Schülers
            if (($tblCompanySchool = $tblStudentEducation->getServiceTblCompany())) {
//                $Data['SchoolId'] = $tblCompanySchool->getId();
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

            if (($tblDivision = $tblStudentEducation->getTblDivision())) {
                $Data['Division'] = $tblDivision->getName();
                $tblYear = $tblYear ?: $tblDivision->getServiceTblYear();
            } elseif (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())) {
                $Data['Division'] = $tblCoreGroup->getName();
                $tblYear = $tblYear ?: $tblCoreGroup->getServiceTblYear();
            }

            if ($tblYear) {
                list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
                // Letztes Datum des aktuellen Schuljahres
                /** @var DateTime $endDate */
                if ($endDate) {
                    $MaxDate = $endDate;
                    $Data['LeaveDate'] = $endDate->format('d.m.Y');
                }
            }
        }

        if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
            && ($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE'))
            && ($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))
        ) {
            $transferDate = $tblStudentTransfer->getTransferDate();
            if ($transferDate) {
                if ($MaxDate > new DateTime($transferDate)) {
                    $DateString = $transferDate;
                    // correct leaveDate if necessary
                    $Data['LeaveDate'] = $DateString;
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
                $Data['AddressDistrict'] = $tblCity->getDisplayDistrict();
            }
        }

        if(Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN)){
            $Data['FirstLastName'] = $tblPerson->getFirstSecondName().', '.$tblPerson->getLastName();
            if(isset($tblCitySchool) && $tblCitySchool){
                $Data['Place'] = $tblCitySchool->getCode().' '.$tblCitySchool->getName();
            }
            $Data['CompanyPhone'] = '';
            $Data['CompanyFax'] = '';
            $Data['CompanyMail'] = '';
            $Data['CompanyWeb'] = '';
            $Data['CompanySchoolLeader'] = '';
            $Data['CompanySecretary'] = '';
            // Zusatzinformation EKBO Institutionen
            if(isset($tblCompanySchool) && $tblCompanySchool) {
                if(($tblToCompanyRelationshipList = Relationship::useService()->getRelationshipToCompanyByCompany($tblCompanySchool))){
                    foreach($tblToCompanyRelationshipList as $tblToCompany){
                        if(($tblRelType = $tblToCompany->getTblType())
                            && $tblRelType->getName() == 'Schulleiter'
                            && ($tblPersonSL = $tblToCompany->getServiceTblPerson())){
                            $Data['CompanySchoolLeader'] .= ($Data['CompanySchoolLeader'] !== '' ? ', ': '')
                                .$tblPersonSL->getFirstName().' '.$tblPersonSL->getLastName();
                        }
                        if(($tblRelType = $tblToCompany->getTblType())
                            && $tblRelType->getName() == 'Sekretariat'
                            && ($tblPersonS = $tblToCompany->getServiceTblPerson())){
                            $Data['CompanySecretary'] .= ($Data['CompanySecretary'] !== '' ? ', ': '')
                                .$tblPersonS->getFirstName().' '.$tblPersonS->getLastName();
                        }
                    }
                }
                if(($tblPhoneList = Phone::useService()->getPhoneAllByCompany($tblCompanySchool))) {
                    foreach($tblPhoneList as $tblToCompanyPhone) {
                        if(($tblPhoneType = $tblToCompanyPhone->getTblType())
                            && $tblPhoneType->getName() == 'Geschäftlich'
                            && $tblPhoneType->getDescription() == 'Festnetz'
                            && ($tblPhone = $tblToCompanyPhone->getTblPhone())) {
                            $Data['CompanyPhone'] = $tblPhone->getNumber();
                        }
                        if(($tblPhoneType = $tblToCompanyPhone->getTblType())
                            && $tblPhoneType->getName() == 'Fax'
                            && $tblPhoneType->getDescription() == 'Geschäftlich'
                            && ($tblPhone = $tblToCompanyPhone->getTblPhone())) {
                            $Data['CompanyFax'] = $tblPhone->getNumber();
                        }
                    }
                }
                if(($tblMailList = Mail::useService()->getMailAllByCompany($tblCompanySchool))) {
                    foreach($tblMailList as $tblToCompanyMail) {
                        if(($tblMailType = $tblToCompanyMail->getTblType())
                            && $tblMailType->getName() == 'Geschäftlich'
                            && ($tblMail = $tblToCompanyMail->getTblMail())) {
                            $Data['CompanyMail'] = $tblMail->getAddress();
                        }
                    }
                }
                if(($tblWebList = Web::useService()->getWebAllByCompany($tblCompanySchool))) {
                    foreach($tblWebList as $tblToCompanyWeb) {
                        if(($tblWebType = $tblToCompanyWeb->getTblType())
                            && $tblWebType->getName() == 'Geschäftlich'
                            && ($tblWeb = $tblToCompanyWeb->getTblWeb())) {
                            $Data['CompanyWeb'] = $tblWeb->getAddress();
                        }
                    }
                }
            }
        }

        return $Data;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear|null $tblYear
     * @param array $Data
     *
     * @return array
     */
    public function getSignOutCertificateData(TblPerson $tblPerson, ?TblYear $tblYear = null, array $Data = array()): array
    {
//        $Data['PersonId'] = $tblPerson->getId();
        $Data['FirstLastName'] = $tblPerson->getFirstSecondName().' '.$tblPerson->getLastName();
        $Data['Date'] = isset($Data['Date']) ? $Data['Date'] : (new DateTime())->format('d.m.Y');
        $Data['BirthDate'] = '';
        $Data['BirthPlace'] = '';
        $Data['AddressStreet'] = '';
        $Data['SchoolCity'] = '';
        $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
        if ($tblCommon) {
            if (($tblCommonBirthdate = $tblCommon->getTblCommonBirthDates())) {
                $Data['BirthDate'] = $tblCommonBirthdate->getBirthday();
                $Data['BirthPlace'] = $tblCommonBirthdate->getBirthplace();
            }
        }
        $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
        if ($tblAddress) {
            $Data['AddressStreet'] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
            if (($tblCity = $tblAddress->getTblCity())) {
                $Data['AddressCity'] = $tblCity->getCode().' '.$tblCity->getDisplayName();
            }
        }

        if ($tblYear) {
            $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
        } else {
            $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson);
            if ($tblStudentEducation) {
                $tblYear = $tblStudentEducation->getServiceTblYear();
            }
        }

        $MaxDate = null;
        if ($tblStudentEducation) {
            if (($tblCompanySchool = $tblStudentEducation->getServiceTblCompany())) {
                $Data['School1'] = $tblCompanySchool->getName();
                $Data['School2'] = $tblCompanySchool->getExtendedName();
                $tblAddressSchool = Address::useService()->getAddressByCompany($tblCompanySchool);
                if ($tblAddressSchool) {
                    $Data['SchoolAddressStreet'] = $tblAddressSchool->getStreetName().' '.$tblAddressSchool->getStreetNumber();
                    $tblCitySchool = $tblAddressSchool->getTblCity();
                    if ($tblCitySchool) {
                        $Data['SchoolAddressCity'] = $tblCitySchool->getCode().' '.$tblCitySchool->getName();
                        $Data['SchoolCity'] = $tblCitySchool->getName().', ';
                    }
                }
            }

            if ($tblYear) {
                list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
                // Letztes Datum des aktuellen Schuljahres
                /** @var DateTime $endDate */
                if ($endDate) {
                    $MaxDate = $endDate;
                    $Data['SchoolUntil'] = $endDate->format('d.m.Y');
                }
            }
        }

        if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
            && ($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE'))
            && ($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))
        ) {
            $transferDate = $tblStudentTransfer->getTransferDate();
            if ($transferDate) {
                if (!$MaxDate || $MaxDate > new DateTime($transferDate)) {
                    $DateString = $transferDate;
                    // correct leaveDate if necessary
                    $Data['SchoolUntil'] = $DateString;
                }
            }
        }

        if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
            // Datum Aufnahme
            $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');
            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType);
            if ($tblStudentTransfer) {
                $EntryDate = $tblStudentTransfer->getTransferDate();
                $Data['SchoolEntry'] = $EntryDate;
            }
        }

        $Data['PlaceDate'] = $Data['SchoolCity'] . $Data['Date'];

        // Hauptadresse Schüler
        $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
        if ($tblAddress) {
            $Data['MainAddress'] = $tblAddress->getGuiString();
        }

        return $Data;
    }
}