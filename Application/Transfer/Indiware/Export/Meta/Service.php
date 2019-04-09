<?php

namespace SPHERE\Application\Transfer\Indiware\Export\Meta;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
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
        if($tblDivision = Division::useService()->getDivisionById($DivisionId)){
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

            $export->setValue($export->getCell("0", "0"), "Name");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("3", "0"), "Geschlecht");
            $export->setValue($export->getCell("4", "0"), "Geburtsort");
            $export->setValue($export->getCell("5", "0"), "Wohnort");
            $export->setValue($export->getCell("6", "0"), "PLZ");
            $export->setValue($export->getCell("7", "0"), "Strasse");
            $export->setValue($export->getCell("8", "0"), "Klasse");


//            for ($i = 1; $i <= 17; $i++) {
//                $export->setValue($export->getCell(($i + 2), "0"), 'Punkte'.$Period.$i);
//            }

            $Row = 1;
            foreach ($PersonList as $tblPerson) {

                $Birthday = $Gender = $Birthplace = '';
                $City = $Street = $Code = '';
                $DivisionName = '';
                // Birth
                if(($tblCommon = Common::useService()->getCommonByPerson($tblPerson))){
                    if(($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())){
                        $Birthday = $tblCommonBirthDates->getBirthday();
                        $Birthplace = $tblCommonBirthDates->getBirthplace();
                        if(($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())){
                            if($tblCommonGender->getName() == 'Männlich'){
                                $Gender = 'm';
                            } elseif($tblCommonGender->getName() == 'Weiblich') {
                                $Gender = 'w';
                            }
                        }
                    }
                }
                //Address
                if($tblAddress = Address::useService()->getAddressByPerson($tblPerson)){
                    $Street = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                    if(($tblCity = $tblAddress->getTblCity())){
                        $City = $tblCity->getName();
                        $Code = $tblCity->getCode();
                    }
                }
                //Division
                if($tblDivision){
                    $DivisionName = $tblDivision->getDisplayName();
                }



                $export->setValue($export->getCell("0", $Row), utf8_encode($tblPerson->getLastName()));
                $export->setValue($export->getCell("1", $Row), utf8_encode($tblPerson->getFirstName()));
                $export->setValue($export->getCell("2", $Row), $Birthday);
                $export->setValue($export->getCell("3", $Row), $Gender);
                $export->setValue($export->getCell("4", $Row), $Birthplace);
                $export->setValue($export->getCell("5", $Row), $City);
                $export->setValue($export->getCell("6", $Row), $Code);
                $export->setValue($export->getCell("7", $Row), $Street);
                $export->setValue($export->getCell("8", $Row), $DivisionName);
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