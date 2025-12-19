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
use SPHERE\Application\Transfer\Education\Education;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportMapping;
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
     * @return array
     */
    public function getIndiwareStudentSubjectOrderByPerson(TblPerson $tblPerson): array
    {
        if (($item = (new Data($this->getBinding()))->getIndiwareStudentSubjectOrderByPerson($tblPerson))) {
            return [
                strtolower($item->getSubject1()) => '1',
                strtolower($item->getSubject2()) => '2',
                strtolower($item->getSubject3()) => '3',
                strtolower($item->getSubject4()) => '4',
                strtolower($item->getSubject5()) => '5',
                strtolower($item->getSubject6()) => '6',
                strtolower($item->getSubject7()) => '7',
                strtolower($item->getSubject8()) => '8',
                strtolower($item->getSubject9()) => '9',
                strtolower($item->getSubject10()) => '10',
                strtolower($item->getSubject11()) => '11',
                strtolower($item->getSubject12()) => '12',
                strtolower($item->getSubject13()) => '13',
                strtolower($item->getSubject14()) => '14',
                strtolower($item->getSubject15()) => '15',
                strtolower($item->getSubject16()) => '16',
                strtolower($item->getSubject17()) => '17',
            ];
        }

        return [];
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
                            $position = null;
                            $acronym = strtolower($tblSubject->getAcronym());
                            if (isset($StudentSubjectOrder[$acronym])) {
                                $position = $StudentSubjectOrder[$acronym];
                            }
                            // hinterlegtes Mapping verwenden z.B. bei EN2
                            elseif (($tblImportMapping = Education::useService()->getImportMappingByMapping(
                                TblImportMapping::TYPE_SUBJECT_ACRONYM_TO_SUBJECT_ID, $tblSubject->getId()
                            ))) {
                                $acronym = strtolower($tblImportMapping->getOriginal());
                                if (isset($StudentSubjectOrder[$acronym])) {
                                    $position = $StudentSubjectOrder[$acronym];
                                }
                            }

                            if ($position) {
                                $PeopleGradeList[$tblPerson->getId()][$position] = $tblTaskGrade->getGrade();
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
    public function createGradeListCsv(int $Period, int $TaskId)
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
                if ($Period >= 5) {
                    $export->setValue($export->getCell(($i + 2), "0"), 'EinfNote' . ($Period == 5 ? '1' : '2') . $i);
                } else {
                    $export->setValue($export->getCell(($i + 2), "0"), 'Punkte' . $Period . $i);
                }
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