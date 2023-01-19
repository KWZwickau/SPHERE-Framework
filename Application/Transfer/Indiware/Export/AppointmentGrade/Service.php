<?php

namespace SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Meta\Common\Common;
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
     * @param int $Id
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
     * @param bool $TaskId
     *
     * @return array|false
     */
    public function getStudentExistInTaskList($TaskId = false)
    {
        $tblTask = Evaluation::useService()->getTaskById($TaskId);
        if (!$tblTask) {
            return false;
        }
        $StudentSubjectOrderAll = AppointmentGrade::useService()->getIndiwareStudentSubjectOrderAll();
        $PersonList = array();
        if ($StudentSubjectOrderAll) {
            foreach ($StudentSubjectOrderAll as $StudentSubjectOrder) {
                if (($tblPerson = $StudentSubjectOrder->getServiceTblPerson())) {
                    $PersonList[$tblPerson->getId()] = true;
                }
            }
        }

        $PersonTestFoundList = array();
        $tblTestList = Evaluation::useService()->getTestAllByTask($tblTask);
        if ($tblTestList) {
            foreach ($tblTestList as $tblTest) {
                // stop search if every Person got found
                if (empty($PersonList)) {
                    break;
                }
                if ($tblDivision = $tblTest->getServiceTblDivision()) {
                    if ($tbLevel = $tblDivision->getTblLevel()) {
                        if ($tbLevel->getName() == '11' || $tbLevel->getName() == '12' || $tbLevel->getName() == '13') {
                            $GradeList = Gradebook::useService()->getGradeAllByTest($tblTest);
                            if ($GradeList) {
                                foreach ($GradeList as $Grade) {
                                    // stop search if every Person got found
                                    if (empty($PersonList)) {
                                        break;
                                    }
                                    if (($GradePerson = $Grade->getServiceTblPerson())) {
                                        foreach ($PersonList as $Key => $value) {
                                            if ($Key == $GradePerson->getId()) {
                                                $PersonTestFoundList[$Key] = true;
                                                unset($PersonList[$Key]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return (!empty($PersonTestFoundList) ? $PersonTestFoundList : false);
    }


    /**
     * @param int|boolean $TaskId
     *
     * @return array|false
     */
    public function getStudentGradeList($TaskId = false)
    {

        $tblTask = Evaluation::useService()->getTaskById($TaskId);
        if (!$tblTask) {
            return false;
        }

        $PeopleGradeList = array();
        $tblTestList = Evaluation::useService()->getTestAllByTask($tblTask);
        if ($tblTestList) {
            foreach ($tblTestList as $tblTest) {
                if ($tblDivision = $tblTest->getServiceTblDivision()) {
                    if (Division::useService()->getIsDivisionCourseSystem($tblDivision)) {
                        $tblGradeList = Gradebook::useService()->getGradeAllByTest($tblTest);
                        if ($tblGradeList) {
                            foreach ($tblGradeList as $tblGrade) {
                                $tblPersonStudent = $tblGrade->getServiceTblPerson();
                                if ($tblPersonStudent) {
                                    $StudentSubjectOrder = AppointmentGrade::useService()->getIndiwareStudentSubjectOrderByPerson($tblPersonStudent);
                                    if ($StudentSubjectOrder) {
                                        if (!isset($PeopleGradeList[$tblPersonStudent->getId()])) {
                                            $PeopleGradeList[$tblPersonStudent->getId()]['FirstName'] = utf8_decode($tblPersonStudent->getFirstSecondName());
                                            $PeopleGradeList[$tblPersonStudent->getId()]['LastName'] = utf8_decode($tblPersonStudent->getLastName());
                                            $Birthday = '';
                                            $tblCommon = Common::useService()->getCommonByPerson($tblPersonStudent);
                                            if ($tblCommon) {
                                                $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates();
                                                if ($tblCommonBirthDates) {
                                                    $Birthday = $tblCommonBirthDates->getBirthday();
                                                }
                                            }
                                            $PeopleGradeList[$tblPersonStudent->getId()]['Birthday'] = $Birthday;
                                        }

                                        $tblSubject = $tblGrade->getServiceTblSubject();
                                        if ($tblSubject) {
                                            if (strtolower($StudentSubjectOrder->getSubject1()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['1'] = $tblGrade->getGrade();
                                            } elseif (strtolower($StudentSubjectOrder->getSubject2()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['2'] = $tblGrade->getGrade();
                                            } elseif (strtolower($StudentSubjectOrder->getSubject3()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['3'] = $tblGrade->getGrade();
                                            } elseif (strtolower($StudentSubjectOrder->getSubject4()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['4'] = $tblGrade->getGrade();
                                            } elseif (strtolower($StudentSubjectOrder->getSubject5()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['5'] = $tblGrade->getGrade();
                                            } elseif (strtolower($StudentSubjectOrder->getSubject6()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['6'] = $tblGrade->getGrade();
                                            } elseif (strtolower($StudentSubjectOrder->getSubject7()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['7'] = $tblGrade->getGrade();
                                            } elseif (strtolower($StudentSubjectOrder->getSubject8()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['8'] = $tblGrade->getGrade();
                                            } elseif (strtolower($StudentSubjectOrder->getSubject9()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['9'] = $tblGrade->getGrade();
                                            } elseif (strtolower($StudentSubjectOrder->getSubject10()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['10'] = $tblGrade->getGrade();
                                            } elseif (strtolower($StudentSubjectOrder->getSubject11()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['11'] = $tblGrade->getGrade();
                                            } elseif (strtolower($StudentSubjectOrder->getSubject12()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['12'] = $tblGrade->getGrade();
                                            } elseif (strtolower($StudentSubjectOrder->getSubject13()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['13'] = $tblGrade->getGrade();
                                            } elseif (strtolower($StudentSubjectOrder->getSubject14()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['14'] = $tblGrade->getGrade();
                                            } elseif (strtolower($StudentSubjectOrder->getSubject15()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['15'] = $tblGrade->getGrade();
                                            } elseif (strtolower($StudentSubjectOrder->getSubject16()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['16'] = $tblGrade->getGrade();
                                            } elseif (strtolower($StudentSubjectOrder->getSubject17()) == strtolower($tblSubject->getAcronym())) {
                                                $PeopleGradeList[$tblPersonStudent->getId()]['17'] = $tblGrade->getGrade();
                                            }
                                        }
                                    }
                                }
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

        $PeopleGradeList = $this->getStudentGradeList($TaskId);

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