<?php
namespace SPHERE\Application\Reporting\Custom\Annaberg\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperSizeParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson as TblToPersonPhone;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Reporting\Standard\Person\Person;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Custom\Annaberg\Person
 */
class Service extends Extension
{
    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createPrintClassList(TblDivisionCourse $tblDivisionCourse)
    {

        $TableContent = array();
        if(($tblPersonList = $tblDivisionCourse->getStudents())) {
            $count = 1;
            array_walk($tblPersonList, function(TblPerson $tblPerson) use (&$TableContent, &$count) {
                $item['Number'] = $count++;
                $item['LastName'] = $tblPerson->getLastName();
                $item['FirstName'] = $tblPerson->getFirstName();
                $item['Address'] = '';
                $item['ExcelAddress'] = array();
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['PhoneStudent'] = '';
                $item['PhoneStudentExcel'] = array();
                $item['PhoneGuardian1'] = '';
                $item['PhoneGuardian1Excel'] = array();
                $item['PhoneGuardian2'] = '';
                $item['PhoneGuardian2Excel'] = array();
                $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = '';
                $item = Person::useService()->getAddressDataFromPerson($tblPerson, $item);
                if(($tblAddress = Address::useService()->getAddressByPerson($tblPerson))
                && ($tblCity = $tblAddress->getTblCity())) {
                    if($tblCity->getDisplayDistrict() != '') {
                        $item['ExcelAddress'][] = $tblAddress->getTblCity()->getDisplayDistrict();
                    }
                    $item['ExcelAddress'][] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                    $item['ExcelAddress'][] = $tblCity->getCode().' '.$tblCity->getName();
                }
                //Phone List Student
                $item['PhoneStudent'] = $this->getPhoneList($tblPerson);
                $item['PhoneStudentExcel'] = $this->getPhoneList($tblPerson, true);
                $tblPersonGuardList = array();
                if(($tblToPersonGuardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, TblType::IDENTIFIER_GUARDIAN))) {
                    foreach($tblToPersonGuardianList as $tblToPerson) {
                        $Ranking = $tblToPerson->getRanking();
                        if(($tblPersonGuard = $tblToPerson->getServiceTblPersonFrom())) {
                            $tblPersonGuardList[$Ranking] = $tblPersonGuard;
                        }
                    }
                }
                //Phone List Guards
                if(!empty($tblPersonGuardList)) {
                    foreach($tblPersonGuardList as $Ranking => $tblPersonGuard) {
                        $item['PhoneGuardian'.$Ranking] = $this->getPhoneList($tblPersonGuard);
                        $item['PhoneGuardian'.$Ranking.'Excel'] = $this->getPhoneList($tblPersonGuard, true);
                    }
                }
                array_push($TableContent, $item);
            });
        }
        return $TableContent;
    }

    /**
     * @param array             $TableContent
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool|FilePointer
     */
    public function createPrintClassListExcel(array $TableContent, TblDivisionCourse $tblDivisionCourse)
    {
        $teacherList = array();
        if(($tblPersonTeacherList = $tblDivisionCourse->getDivisionTeacherList())) {
            foreach($tblPersonTeacherList as $tblPersonTeacher) {
                $teacherList[] = $tblPersonTeacher->getSalutation().' '.$tblPersonTeacher->getLastName();
            }
        }
        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $tblYear = $tblDivisionCourse->getServiceTblYear();
        $headerText = "SJ "
            .($tblYear ? $tblYear->getName() : '')
            .' Klasse '.$tblDivisionCourse->getDisplayName().' '
            .(empty($teacherList) ? '' : implode(' - ', $teacherList));
        $column = $row = 0;
        $export->setValue($export->getCell($column++, $row), "#");
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Vorname");
        $export->setValue($export->getCell($column++, $row), "Adresse");
        $export->setValue($export->getCell($column++, $row), "Geb.-datum");
        $export->setValue($export->getCell($column++, $row), "Tel. Schüler");
        $export->setValue($export->getCell($column++, $row), "Tel. Sorgeber. 1");
        $export->setValue($export->getCell($column, $row), "Tel. Sorgeber. 2");
        $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row++))->setFontBold()->setBorderBottom()->setBorderTop();
        foreach($TableContent as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Number']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $addressRow = $row;
            if(!empty($PersonData['ExcelAddress'])) {
                foreach($PersonData['ExcelAddress'] as $Address) {
                    $export->setValue($export->getCell($column, $addressRow), $Address);
                    $addressRow++;
                }
            }
            $column++;
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthday']);
            $phoneStudentRow = $row;
            if(!empty($PersonData['PhoneStudentExcel'])) {
                foreach($PersonData['PhoneStudentExcel'] as $Phone) {
                    $export->setValue($export->getCell($column, $phoneStudentRow), $Phone);
                    $phoneStudentRow++;
                }
            }
            $column++;
            $phoneGuardian1Row = $row;
            if(!empty($PersonData['PhoneGuardian1Excel'])) {
                foreach($PersonData['PhoneGuardian1Excel'] as $Phone) {
                    $export->setValue($export->getCell($column, $phoneGuardian1Row), $Phone);
                    $phoneGuardian1Row++;
                }
            }
            $column++;
            $phoneGuardian2Row = $row;
            if(!empty($PersonData['PhoneGuardian2Excel'])) {
                foreach($PersonData['PhoneGuardian2Excel'] as $Phone) {
                    $export->setValue($export->getCell($column, $phoneGuardian2Row), $Phone);
                    $phoneGuardian2Row++;
                }
            }
            $row++;
            if($addressRow > $row) {
                $row = $addressRow;
            }
            if($phoneStudentRow > $row) {
                $row = $phoneStudentRow;
            }
            if($phoneGuardian1Row > $row) {
                $row = $phoneGuardian1Row;
            }
            if($phoneGuardian2Row > $row) {
                $row = $phoneGuardian2Row;
            }
            $export->setStyle($export->getCell(0, $row - 1), $export->getCell($column, $row - 1))->setBorderBottom();
        }
        $export->setStyle($export->getCell(0, 0), $export->getCell(0, $row - 1))->setBorderLeft();
        for($i = 0; $i < 8; $i++) {
            $export->setStyle($export->getCell($i, 0), $export->getCell($i, $row - 1))->setBorderRight();
        }
        // Spaltenbreite Definieren
        $column = 0;
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(4);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(18);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(18);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(25);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(12);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(18);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(18);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(18);
        $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));
        $export->setPaperSizeParameter(new PaperSizeParameter('A4'));
        // Kopfzeile im Excel setzen, sieht man nur beim Drucken oder wenn man es als PDF speichert
        $export->getActiveSheet()->getHeaderFooter()->setDifferentOddEven(false);
        $export->getActiveSheet()->getHeaderFooter()->setOddHeader($headerText);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool      $getArray
     *
     * @return string|array
     */
    private function getPhoneList(TblPerson $tblPerson, $getArray = false)
    {

        $phoneList = array();
        if($tblToPersonList = Phone::useService()->getPhoneAllByPerson($tblPerson)) {
            $privateList = array();
            $companyList = array();
            $faxList = array();
            $secureList = array();
            foreach($tblToPersonList as $tblToPerson) {
                if(($tblType = $tblToPerson->getTblType())
                && ($tblPhone = $tblToPerson->getTblPhone())) {
                    if($tblType->getName() == 'Privat') {
                        $privateList[] = $tblPhone->getNumber().($getArray ? ' ' : '&nbsp;').$this->getShortTypeByTblToPersonPhone($tblToPerson);
                    } elseif($tblType->getName() == 'Geschäftlich') {
                        $companyList[] = $tblPhone->getNumber().($getArray ? ' ' : '&nbsp;').$this->getShortTypeByTblToPersonPhone($tblToPerson);
                    } elseif($tblType->getName() == 'Notfall') {
                        $secureList[] = $tblPhone->getNumber().($getArray ? ' ' : '&nbsp;').$this->getShortTypeByTblToPersonPhone($tblToPerson);
                    } elseif($tblType->getName() == 'Fax') {
                        $faxList[] = $tblPhone->getNumber().($getArray ? ' ' : '&nbsp;').$this->getShortTypeByTblToPersonPhone($tblToPerson);
                    }
                }
            }
            $phoneList = array_merge($privateList, $companyList, $secureList, $faxList);
        }
        if($getArray) {
            return $phoneList;
        } else {
            if(!empty($phoneList)) {
                return implode(', ', $phoneList);
            }
        }
        return '';
    }

    /**
     * @param TblToPersonPhone $tblToPerson
     *
     * @return string
     */
    public function getShortTypeByTblToPersonPhone(TblToPersonPhone $tblToPerson)
    {

        $result = '';
        if(($tblType = $tblToPerson->getTblType())) {
            switch ($tblType->getName()) {
                case 'Privat':
                    $result = 'p';
                    break;
                case 'Geschäftlich':
                    $result = 'g';
                    break;
                case 'Notfall':
                    $result = 'n';
                    break;
                case 'Fax':
                    $result = 'f';
                    break;
            }
        }
        return $result;
    }
}