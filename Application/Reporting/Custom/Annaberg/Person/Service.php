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
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Custom\Annaberg\Person
 */
class Service extends Extension
{
    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createPrintClassList(TblDivision $tblDivision)
    {
        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();
        if (!empty($tblPersonList)) {

            $count = 1;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {

                $Item['Number'] = $count++;
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['FirstName'] = $tblPerson->getFirstName();
                $Item['Address'] = '';
                $Item['Birthday']  = '';
                $Item['PhoneGuardian1'] = $Item['PhoneGuardian1Excel'] = '';
                $Item['PhoneGuardian2'] = $Item['PhoneGuardian2Excel'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';

                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();

                    if ($tblAddress->getTblCity()->getDisplayDistrict() != '') {
                        $Item['ExcelAddress'][] = $tblAddress->getTblCity()->getDisplayDistrict();
                    }
                    $Item['ExcelAddress'][] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                    $Item['ExcelAddress'][] = $tblAddress->getTblCity()->getCode().' '.$tblAddress->getTblCity()->getName();
                }
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                }
                // Guardian 1
                $tblPersonG1 = false;
                // Guardian 2
                $tblPersonG2 = false;

                $tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($tblToPersonList) {
                    foreach ($tblToPersonList as $tblToPerson) {
                        if ($tblToPerson->getTblType()->getName() == 'Sorgeberechtigt' && $tblToPerson->getServiceTblPersonFrom()) {
                            switch ($tblToPerson->getRanking()) {
                                case 1: $tblPersonG1 = $tblToPerson->getServiceTblPersonFrom(); break;
                                case 2: $tblPersonG2 = $tblToPerson->getServiceTblPersonFrom(); break;
                            }
                        }
                    }
                }
                if ($tblPersonG1) {
                    $Item['Guardian1'] = $tblPersonG1->getFullName();
                    $Item['PhoneGuardian1'] = $this->getPhoneList($tblPersonG1);
                    $Item['PhoneGuardian1Excel'] = $this->getPhoneList($tblPersonG1, true);
                }
                if ($tblPersonG2) {
                    $Item['Guardian2'] = $tblPersonG2->getFullName();
                    $Item['PhoneGuardian2'] = $this->getPhoneList($tblPersonG2);
                    $Item['PhoneGuardian2Excel'] = $this->getPhoneList($tblPersonG2, true);
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param $PersonList
     * @param TblDivision $tblDivision
     *
     * @return bool|FilePointer
     */
    public function createPrintClassListExcel($PersonList, TblDivision $tblDivision)
    {
        if (!empty($PersonList)) {
            $teachers = array();
            if (($tblDivisionTeacherList = Division::useService()->getDivisionTeacherAllByDivision($tblDivision))) {
                foreach ($tblDivisionTeacherList as $tblDivisionTeacher) {
                    if (($tblPerson = $tblDivisionTeacher->getServiceTblPerson())) {
                        $teachers[] = $tblPerson->getSalutation() . ' ' . $tblPerson->getLastName();
                    }
                }
            }

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $column = 0;
            $row = 0;
            $export->setValue($export->getCell($column, $row), "SJ "
                . (($tblYear = $tblDivision->getServiceTblYear()) ? $tblYear->getName() : '')
                . ' Klasse ' . $tblDivision->getDisplayName() . ' '
                . (empty($teachers) ? '' : implode(' - ', $teachers))
            );
            $export->setStyle($export->getCell(0, $row), $export->getCell(6, $row))
                ->setFontBold()
                ->setBorderBottom();
            $row++;

            $export->setValue($export->getCell($column++, $row), "#");
            $export->setValue($export->getCell($column++, $row), "Name");
            $export->setValue($export->getCell($column++, $row), "Vorname");
            $export->setValue($export->getCell($column++, $row), "Adresse");
            $export->setValue($export->getCell($column++, $row), "Geb.-datum");
            $export->setValue($export->getCell($column++, $row), "Tel. Sorgeber. 1");
            $export->setValue($export->getCell($column, $row), "Tel. Sorgeber. 2");
            $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))
                ->setFontBold()
                ->setBorderBottom();
            $row++;

            foreach ($PersonList as $PersonData) {

                $column = 0;
                $export->setValue($export->getCell($column++, $row), $PersonData['Number']);
                $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);

                $addressRow = $row;
                if (isset($PersonData['ExcelAddress']) && !empty($PersonData['ExcelAddress'])) {
                    foreach ($PersonData['ExcelAddress'] as $Address) {
                        $export->setValue($export->getCell($column, $addressRow), $Address);
                        $addressRow++;
                    }
                }
                $column++;

                $export->setValue($export->getCell($column++, $row), $PersonData['Birthday']);

                $phoneGuardian1Row = $row;
                if (isset($PersonData['PhoneGuardian1Excel']) && !empty($PersonData['PhoneGuardian1Excel'])) {
                    foreach ($PersonData['PhoneGuardian1Excel'] as $Phone) {
                        $export->setValue($export->getCell($column, $phoneGuardian1Row), $Phone);
                        $phoneGuardian1Row++;
                    }
                }
                $column++;

                $phoneGuardian2Row = $row;
                if (isset($PersonData['PhoneGuardian2Excel']) && !empty($PersonData['PhoneGuardian2Excel'])) {
                    foreach ($PersonData['PhoneGuardian2Excel'] as $Phone) {
                        $export->setValue($export->getCell($column, $phoneGuardian2Row), $Phone);
                        $phoneGuardian2Row++;
                    }
                }

                $row++;

                if ($addressRow > $row) {
                    $row = $addressRow;
                }
                if ($phoneGuardian1Row > $row) {
                    $row = $phoneGuardian1Row;
                }

                $export->setStyle($export->getCell(0, $row - 1), $export->getCell($column, $row - 1))->setBorderBottom();
            }

            $export->setStyle($export->getCell(0, 1), $export->getCell(0, $row - 1))->setBorderLeft();
            for ($i=0; $i<7; $i++) {
                $export->setStyle($export->getCell($i, 1), $export->getCell($i, $row - 1))->setBorderRight();
            }

            // Spaltenbreite Definieren
            $column = 0;
            $export->setStyle($export->getCell($column++, 0))->setColumnWidth(4);
            $export->setStyle($export->getCell($column++, 0))->setColumnWidth(20);
            $export->setStyle($export->getCell($column++, 0))->setColumnWidth(20);
            $export->setStyle($export->getCell($column++, 0))->setColumnWidth(25);
            $export->setStyle($export->getCell($column++, 0))->setColumnWidth(12);
            $export->setStyle($export->getCell($column++, 0))->setColumnWidth(25);
            $export->setStyle($export->getCell($column, 0))->setColumnWidth(25);

            $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));
            $export->setPaperSizeParameter(new PaperSizeParameter('A4'));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool      $IsExcel
     *
     * @return string|array
     */
    private function getPhoneList(TblPerson $tblPerson, $IsExcel = false)
    {

        $tblToPersonList = Phone::useService()->getPhoneAllByPerson($tblPerson);

        $phoneList = array();

        if ($tblToPersonList) {
            $privateList = array();
            foreach ($tblToPersonList as $tblToPerson) {
                if($tblToPerson->getTblType()->getName() == 'Privat'){
                    $privateList[] = $tblToPerson->getTblPhone()->getNumber().($IsExcel ? ' ' : '&nbsp;').
                        $this->getShortTypeByTblToPersonPhone($tblToPerson);
                }
            }
            $companyList = array();
            foreach ($tblToPersonList as $tblToPerson) {
                if($tblToPerson->getTblType()->getName() == 'Geschäftlich'){
                    $companyList[] = $tblToPerson->getTblPhone()->getNumber().($IsExcel ? ' ' : '&nbsp;').
                        $this->getShortTypeByTblToPersonPhone($tblToPerson);
                }
            }
            $secureList = array();
            foreach ($tblToPersonList as $tblToPerson) {
                if($tblToPerson->getTblType()->getName() == 'Notfall'){
                    $secureList[] = $tblToPerson->getTblPhone()->getNumber().($IsExcel ? ' ' : '&nbsp;').
                        $this->getShortTypeByTblToPersonPhone($tblToPerson);
                }
            }
            $faxList = array();
            foreach ($tblToPersonList as $tblToPerson) {
                if($tblToPerson->getTblType()->getName() == 'Fax'){
                    $faxList[] = $tblToPerson->getTblPhone()->getNumber().($IsExcel ? ' ' : '&nbsp;').
                        $this->getShortTypeByTblToPersonPhone($tblToPerson);
                }
            }
            $phoneList = array_merge($privateList, $companyList, $secureList, $faxList);
        }
        if ($IsExcel) {
            return $phoneList;
        } else {
            if(!empty($phoneList)){
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
        $tblType = $tblToPerson->getTblType();
        if ($tblType) {
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