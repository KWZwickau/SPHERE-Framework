<?php

namespace SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Exception\Repository\TypeFileException;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\People\Meta\Common\Common;

/**
 * Class Service
 * @package SPHERE\Application\Transfer\Export\Invoice
 */
class Service
{

    /**
     * @param int|boolean $TaskId
     *
     * @return array|false
     */
    public function createGradeList($TaskId = false)
    {

        $tblTask = Evaluation::useService()->getTaskById($TaskId);
        if (!$tblTask) {
            false;
        }
        $TableContent = array();
        $tblTestList = Evaluation::useService()->getTestAllByTask($tblTask);
        if ($tblTestList) {
            foreach ($tblTestList as $tblTest) {
                $tblGradeList = Gradebook::useService()->getGradeAllByTest($tblTest);
                if ($tblGradeList) {

                    foreach ($tblGradeList as $tblGrade) {
                        $tblPersonStudent = $tblGrade->getServiceTblPerson();
                        if (!isset($TableContent[$tblPersonStudent->getId()])) {
                            $TableContent[$tblPersonStudent->getId()]['FirstName'] = $tblPersonStudent->getFirstName();
                            $TableContent[$tblPersonStudent->getId()]['LastName'] = $tblPersonStudent->getLastName();
                            $Birthday = '';
                            $tblCommon = Common::useService()->getCommonByPerson($tblPersonStudent);
                            if ($tblCommon) {
                                $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates();
                                if ($tblCommonBirthDates) {
                                    $Birthday = $tblCommonBirthDates->getBirthday();
                                }
                            }
                            $TableContent[$tblPersonStudent->getId()]['Birthday'] = $Birthday;
                        }

                        $tblSubject = $tblGrade->getServiceTblSubject();
                        if ($tblSubject) {
                            $TableContent[$tblPersonStudent->getId()][$tblSubject->getAcronym()] = $tblGrade->getGrade();
                        }
                    }
                }
            }
        }

        return $TableContent;
    }

    /**
     * @param int $TaskId
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createGradeListCsv($TaskId)
    {

        $PeopleGradeList = $this->createGradeList($TaskId);
        $SubjectList = array();
        // Header Vorbereiten
        foreach ($PeopleGradeList as $PersonRow) {
            if ($PersonRow) {
                foreach ($PersonRow as $key => $value) {
                    if ($key != 'FirstName' && $key != 'LastName' && $key != 'Birthday') {
                        $SubjectList[$key] = $key;
                    }
                }
            }
        }
        ksort($SubjectList);
        sort($SubjectList);
//        Debugger::screenDump($SubjectList);
//        exit;

//        Debugger::screenDump($SubjectList);
        if (!empty($PeopleGradeList)) {

            $fileLocation = Storage::createFilePointer('csv');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Name");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Geburtsdatum");
            foreach ($SubjectList as $key => $Acronym) {
                $export->setValue($export->getCell(($key + 3), "0"), $Acronym);
            }

            $Row = 1;
            foreach ($PeopleGradeList as $Data) {

                $export->setValue($export->getCell("0", $Row), $Data['LastName']);
                $export->setValue($export->getCell("1", $Row), $Data['FirstName']);
                $export->setValue($export->getCell("2", $Row), $Data['Birthday']);
                foreach ($SubjectList as $key => $Acronym) {
                    if (isset($Data[$Acronym])) {
                        $export->setValue($export->getCell(($key + 3), $Row), $Data[$Acronym]);
                    }
                }
                $Row++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }
}