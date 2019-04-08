<?php

namespace SPHERE\Application\Transfer\Indiware\Export\Meta;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade\Service\Data;
use SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Export\Meta
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
     * @param string $DivisionId
     *
     * @return bool|FilePointer
     */
    public function createCsv($DivisionId = '')
    {


        $PersonList = array();
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if($tblDivision){
            if(($tblDivisionStudentList = Division::useService()->getDivisionStudentAllByDivision($tblDivision))){
                foreach($tblDivisionStudentList as $tblDivisionStudent){
                    if(($tblPerson = $tblDivisionStudent->getServiceTblPerson())){
                        $PersonList[] = $tblPerson;
                    }
                }
            }
        }

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('csv');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $export->setValue($export->getCell("0", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("1", "0"), "Name");
            $export->setValue($export->getCell("2", "0"), "Vorname");


//            for ($i = 1; $i <= 17; $i++) {
//                $export->setValue($export->getCell(($i + 2), "0"), 'Punkte'.$Period.$i);
//            }

            $Row = 1;
            foreach ($PersonList as $tblPerson) {

                $Birthday = '';
                if(($tblCommon = Common::useService()->getCommonByPerson($tblPerson))){
                    if(($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())){
                        $Birthday = $tblCommonBirthDates->getBirthday();
                    }
                }

                $export->setValue($export->getCell("0", $Row), $Birthday);
                $export->setValue($export->getCell("1", $Row), utf8_encode($tblPerson->getLastName()));
                $export->setValue($export->getCell("2", $Row), utf8_encode($tblPerson->getFirstName()));
//                for ($j = 1; $j <= 17; $j++) {
//                    if (isset($Data[$j])) {
//                        $export->setValue($export->getCell(($j + 2), $Row), $Data[$j]);
//                    }
//                }
                $Row++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }
}