<?php

namespace SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade\Service\Data;
use SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade\Service\Entity\TblIndiwareStudentSubjectOrder;
use SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Export\AppointmentGrade
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
        if (!$withData) {
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
     * @return false|TblIndiwareStudentSubjectOrder
     */
    public function getIndiwareStudentSubjectOrderById($Id)
    {
        return (new Data($this->getBinding()))->getIndiwareStudentSubjectOrderById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblIndiwareStudentSubjectOrder
     */
    public function getIndiwareStudentSubjectOrderByPerson(TblPerson $tblPerson)
    {
        return (new Data($this->getBinding()))->getIndiwareStudentSubjectOrderByPerson($tblPerson);
    }

    /**
     * @return false|TblIndiwareStudentSubjectOrder[]
     */
    public function getIndiwareStudentSubjectOrderAll()
    {
        return (new Data($this->getBinding()))->getIndiwareStudentSubjectOrderAll();
    }

    /**
     * @param $TaskId
     *
     * @return array|false
     */
    public function getStudentExistInTaskList($TaskId)
    {
        $tblTask = Grade::useService()->getTaskById($TaskId);
        if (!$tblTask) {
            return false;
        }
        $StudentSubjectOrderAll = AppointmentGrade::useService()->getIndiwareStudentSubjectOrderAll();
        $tblPersonList = array();
        if ($StudentSubjectOrderAll) {
            foreach ($StudentSubjectOrderAll as $StudentSubjectOrder) {
                if (($tblPerson = $StudentSubjectOrder->getServiceTblPerson())) {
                    $tblPersonList[$tblPerson->getId()] = $tblPerson;
                }
            }
        }

        $PersonTestFoundList = array();

        if (($tblDivisionCourseListByTask = $tblTask->getDivisionCourses())
            && ($tblYear = $tblTask->getServiceTblYear())
        ) {
            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {
                if (($tblDivisionCourseListByPerson = DivisionCourse::useService()->getDivisionCourseListByStudentAndYear($tblPerson, $tblYear))) {
                    foreach ($tblDivisionCourseListByPerson as $tblDivisionCourse) {
                        if (isset($tblDivisionCourseListByTask[$tblDivisionCourse->getId()])) {
                            $PersonTestFoundList[$tblPerson->getId()] = $tblPerson;
                            break;
                        }
                    }
                }
             }
        }

        return (!empty($PersonTestFoundList) ? $PersonTestFoundList : false);
    }


    /**
     * @param $TaskId
     * @param $tblPersonList
     *
     * @return array|false
     */
    public function getStudentGradeList($TaskId, $tblPersonList)
    {
        $tblTask = Grade::useService()->getTaskById($TaskId);
        if (!$tblTask) {
            return false;
        }

        $PeopleGradeList = array();
        if ($tblPersonList) {
            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {
                $PeopleGradeList[$tblPerson->getId()]['FirstName'] = utf8_decode($tblPerson->getFirstSecondName());
                $PeopleGradeList[$tblPerson->getId()]['LastName'] = utf8_decode($tblPerson->getLastName());
                $PeopleGradeList[$tblPerson->getId()]['Birthday'] = $tblPerson->getBirthday();

                if (($tblTaskGradeList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblTask, $tblPerson))
                    && ($StudentSubjectOrder = AppointmentGrade::useService()->getIndiwareStudentSubjectOrderByPerson($tblPerson))
                ) {
                    foreach ($tblTaskGradeList as $tblTaskGrade) {
                        if (($tblSubject = $tblTaskGrade->getServiceTblSubject())) {
                            if (strtolower($StudentSubjectOrder->getSubject1()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['1'] = $tblTaskGrade->getGrade();
                            } elseif (strtolower($StudentSubjectOrder->getSubject2()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['2'] = $tblTaskGrade->getGrade();
                            } elseif (strtolower($StudentSubjectOrder->getSubject3()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['3'] = $tblTaskGrade->getGrade();
                            } elseif (strtolower($StudentSubjectOrder->getSubject4()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['4'] = $tblTaskGrade->getGrade();
                            } elseif (strtolower($StudentSubjectOrder->getSubject5()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['5'] = $tblTaskGrade->getGrade();
                            } elseif (strtolower($StudentSubjectOrder->getSubject6()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['6'] = $tblTaskGrade->getGrade();
                            } elseif (strtolower($StudentSubjectOrder->getSubject7()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['7'] = $tblTaskGrade->getGrade();
                            } elseif (strtolower($StudentSubjectOrder->getSubject8()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['8'] = $tblTaskGrade->getGrade();
                            } elseif (strtolower($StudentSubjectOrder->getSubject9()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['9'] = $tblTaskGrade->getGrade();
                            } elseif (strtolower($StudentSubjectOrder->getSubject10()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['10'] = $tblTaskGrade->getGrade();
                            } elseif (strtolower($StudentSubjectOrder->getSubject11()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['11'] = $tblTaskGrade->getGrade();
                            } elseif (strtolower($StudentSubjectOrder->getSubject12()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['12'] = $tblTaskGrade->getGrade();
                            } elseif (strtolower($StudentSubjectOrder->getSubject13()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['13'] = $tblTaskGrade->getGrade();
                            } elseif (strtolower($StudentSubjectOrder->getSubject14()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['14'] = $tblTaskGrade->getGrade();
                            } elseif (strtolower($StudentSubjectOrder->getSubject15()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['15'] = $tblTaskGrade->getGrade();
                            } elseif (strtolower($StudentSubjectOrder->getSubject16()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['16'] = $tblTaskGrade->getGrade();
                            } elseif (strtolower($StudentSubjectOrder->getSubject17()) == strtolower($tblSubject->getAcronym())) {
                                $PeopleGradeList[$tblPerson->getId()]['17'] = $tblTaskGrade->getGrade();
                            }
                        }
                    }
                }
            }
        }

        return $PeopleGradeList;
    }

    /**
     * @param int $Period
     * @param int $TaskId
     *
     * @return bool|FilePointer
     */
    public function createGradeListCsv($Period, $TaskId)
    {
        $tblPersonList = $this->getStudentExistInTaskList($TaskId);

        $PeopleGradeList = $this->getStudentGradeList($TaskId, $tblPersonList);

        if (!empty($PeopleGradeList)) {

            $fileLocation = Storage::createFilePointer('csv');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            // Auswahl des Trennzeichen's
            $export->setDelimiter(';');

            $export->setValue($export->getCell("0", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("1", "0"), "Name");
            $export->setValue($export->getCell("2", "0"), "Vorname");


            for ($i = 1; $i <= 17; $i++) {
                $export->setValue($export->getCell(($i + 2), "0"), 'Punkte'.$Period.$i);
            }

            $Row = 1;
            foreach ($PeopleGradeList as $Data) {

                $export->setValue($export->getCell("0", $Row), $Data['Birthday']);
                $export->setValue($export->getCell("1", $Row), $Data['LastName']);
                $export->setValue($export->getCell("2", $Row), $Data['FirstName']);
                for ($j = 1; $j <= 17; $j++) {
                    if (isset($Data[$j])) {
                        $export->setValue($export->getCell(($j + 2), $Row), $Data[$j]);
                    }
                }
                $Row++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param array   $ImportList
     * @param int     $Period
     * @param TblTask $tblTask
     *
     * @return bool
     */
    public function createIndiwareStudentSubjectOrderBulk($ImportList, $Period, TblTask $tblTask)
    {

        return (new Data($this->getBinding()))->createIndiwareStudentSubjectOrderBulk($ImportList, $Period, $tblTask);
    }

    /**
     * @return bool
     */
    public function destroyIndiwareStudentSubjectOrderAllBulk()
    {

        return (new Data($this->getBinding()))->destroyIndiwareStudentSubjectOrderAllBulk();
    }
}