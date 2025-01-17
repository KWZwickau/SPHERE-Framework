<?php

namespace SPHERE\Application\Transfer\Indiware\Export\Meta;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
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
     * @param string $DivisionCourseId
     *
     * @return bool|FilePointer
     */
    public function createCsv(string $DivisionCourseId = '')
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            $fileLocation = Storage::createFilePointer('csv');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setDelimiter(';');

            $export->setValue($export->getCell("0", "0"), "Name");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("3", "0"), "Geschlecht");
            $export->setValue($export->getCell("4", "0"), "Geburtsort");
            $export->setValue($export->getCell("5", "0"), "Wohnort");
            $export->setValue($export->getCell("6", "0"), "PLZ");
            $export->setValue($export->getCell("7", "0"), "Strasse");
            $export->setValue($export->getCell("8", "0"), "Klasse");

            $Row = 1;
            foreach ($tblPersonList as $tblPerson) {
                $Birthday = $Gender = $Birthplace = '';
                $City = $Street = $Code = '';
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
                $DivisionName = $tblDivisionCourse->getName();

                $export->setValue($export->getCell("0", $Row), utf8_decode($tblPerson->getLastName()));
                $export->setValue($export->getCell("1", $Row), utf8_decode($tblPerson->getFirstName()));
                $export->setValue($export->getCell("2", $Row), $Birthday);
                $export->setValue($export->getCell("3", $Row), $Gender);
                $export->setValue($export->getCell("4", $Row), utf8_decode($Birthplace));
                $export->setValue($export->getCell("5", $Row), utf8_decode($City));
                $export->setValue($export->getCell("6", $Row), $Code);
                $export->setValue($export->getCell("7", $Row), utf8_decode($Street));
                $export->setValue($export->getCell("8", $Row), utf8_decode($DivisionName));

                $Row++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }
}