<?php
namespace SPHERE\Application\Reporting\Custom\Hormersdorf\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Reporting\Standard\Person\Person;
use SPHERE\System\Extension\Extension;

class Service extends Extension
{

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createClassList(TblDivisionCourse $tblDivisionCourse)
    {

        $TableContent = array();
        if ($tblPersonList = $tblDivisionCourse->getStudents()) {
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {
                $item['Number'] = $count++;
                $item['ExcelNameRow2'] = '';
                $item['Address'] = '';
                $item['ExcelAddressRow'] = array();
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['Birthplace'] = $tblPerson->getBirthplaceString();
                $item['PhoneNumbers'] = '';
                $item['ExcelPhoneNumbers'] = '';
                $item = Person::useService()->getAddressDataFromPerson($tblPerson, $item);
                if (($tblAddress = Address::useService()->getAddressByPerson($tblPerson))) {
                    if(($District = $tblAddress->getTblCity()->getDisplayDistrict())){
                        $item['ExcelAddressRow'][] = $District;
                    }
                    $item['ExcelAddressRow'][] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                    if(($tblCity = $tblAddress->getTblCity())){
                        $item['ExcelAddressRow'][] = $tblCity->getCode().' '.$tblCity->getName();
                    }
                }
                $phoneNumbers = array();
                if(($tblToPersonGuardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, TblType::IDENTIFIER_GUARDIAN))) {
                    foreach ($tblToPersonGuardianList as $tblToPerson) {
                        $Ranking = $tblToPerson->getRanking();
                        $tblPersonGuard = $tblToPerson->getServiceTblPersonFrom();
                        if(($tblToPersonPList = Phone::useService()->getPhoneAllByPerson($tblPersonGuard))){
                            foreach($tblToPersonPList as $tblToPersonP){
                                if(($tblPhone = $tblToPersonP->getTblPhone())){
                                    if($Ranking == 1){
                                        array_unshift($phoneNumbers, $tblPhone->getNumber().' '.$tblToPersonP->getTblType()->getName()
                                            .($tblToPersonP->getRemark() !== '' ? ' '.$tblToPersonP->getRemark() : ''));
                                    } else {
                                        $phoneNumbers[] = $tblPhone->getNumber().' '.$tblToPersonP->getTblType()->getName()
                                            .($tblToPersonP->getRemark() !== '' ? ' '.$tblToPersonP->getRemark() : '');
                                    }
                                }
                            }
                        }
                        $item['ParentNameList'][$Ranking] = $tblPersonGuard->getFirstSecondName().' '.$tblPersonGuard->getLastName();
                    }
                }
                $item['DisplayName'] = $tblPerson->getLastFirstName().(!empty($item['ParentNameList']) ? '<br>('.implode(', ', $item['ParentNameList']).')' : '');
                $item['ExcelNameRow1'] = $tblPerson->getLastFirstName();
                if (!empty($item['ParentNameList'])) {
                    $item['ExcelNameRow2'] = (!empty($item['ParentNameList']) ? '('.implode(', ', $item['ParentNameList']).')' : '');
                }
                if (!empty( $phoneNumbers )) {
                    $item['PhoneNumbers'] = implode('<br>', $phoneNumbers);
                    $item['ExcelPhoneNumbers'] = $phoneNumbers;
                }
                array_push($TableContent, $item);
            });
        }

        return $TableContent;
    }

    /**
     * @param $TableContent
     * @param $tblPersonList
     *
     * @return FilePointer
     */
    public function createClassListExcel($TableContent, $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = $row = 0;
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Geb.-Datum");
        $export->setValue($export->getCell($column++, $row), "Adresse");
        $export->setValue($export->getCell($column, $row), "Telefonnummer");
        $row = 2;
        foreach($TableContent as $PersonData) {
            $rowPhone = $row;
            $export->setValue($export->getCell(0, $row), $PersonData['ExcelNameRow1']);
            $export->setValue($export->getCell(1, $row), $PersonData['Birthday']);
            if(!empty($PersonData['ExcelAddressRow'])) {
                foreach($PersonData['ExcelAddressRow'] as $AddressLine) {
                    $export->setValue($export->getCell(2, $row++), $AddressLine);
                }
            }
            if(!empty($PersonData['ExcelPhoneNumbers'])) {
                foreach($PersonData['ExcelPhoneNumbers'] as $phone) {
                    $export->setValue($export->getCell(3, $rowPhone++), $phone);
                }
            }
            if($rowPhone > $row) {
                $row = $rowPhone;
            }
            $row++;
        }
        $row++;
        Person::setGenderFooter($export, $tblPersonList, $row);
        $row++;
        $export->setValue($export->getCell(0, $row), 'Stand '.date("d.m.Y"));
        $column = 0;
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(20);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(12);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(25);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(25);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @return array
     */
    public function createStaffList($tblPersonList)
    {

        $TableContent = array();
        if (!empty( $tblPersonList )) {
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {
                $item['Name'] = ($tblPerson->getTitle() ? $tblPerson->getTitle().' ' : '').$tblPerson->getLastFirstName();
                $item['Birthday'] = $tblPerson->getBirthday();
                array_push($TableContent, $item);
            });
        }
        return $TableContent;
    }

    /**
     * @param $PersonList
     * @param $tblPersonList
     *
     * @return FilePointer
     */
    public function createStaffListExcel($TableContent, $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $column = $row = 0;
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column, $row++), "Geburtstag");
        foreach ($TableContent as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Name']);
            $export->setValue($export->getCell($column, $row++), $PersonData['Birthday']);
        }
        $row++;
        Person::setGenderFooter($export, $tblPersonList, $row);
        $column = 0;
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(20);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(12);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }
}