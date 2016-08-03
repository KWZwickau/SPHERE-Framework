<?php

namespace SPHERE\Application\Reporting\Custom\Herrnhut\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Extension\Extension;

class Service extends Extension
{

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createSignList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        foreach ($tblPersonList as $key => $row) {
            $lastName[$key] = strtoupper($row->getLastName());
            $firstName[$key] = strtoupper($row->getFirstName());
            $id[$key] = $row->getId();
        }
        array_multisort($lastName, SORT_ASC, $firstName, SORT_ASC, $tblPersonList);
        $TableContent = array();

        $CountNumber = 0;
        if (!empty( $tblPersonList )) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber) {
                $CountNumber++;
                $Item['Count'] = $CountNumber;
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Empty'] = '';
                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createSignListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::useWriter()->getTemporary('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "lfdNr.");
            $export->setValue($export->getCell("1", "0"), "Name");
            $export->setValue($export->getCell("2", "0"), "Vorname");
            $export->setValue($export->getCell("3", "0"), "Unterschrift");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['Count']);
                $export->setValue($export->getCell("1", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("3", $Row), $PersonData['Empty']);

                $Row++;
            }

            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createLanguageList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        foreach ($tblPersonList as $key => $row) {
            $lastName[$key] = strtoupper($row->getLastName());
            $firstName[$key] = strtoupper($row->getFirstName());
            $id[$key] = $row->getId();
        }
        array_multisort($lastName, SORT_ASC, $firstName, SORT_ASC, $tblPersonList);
        $TableContent = array();

        $CountNumber = 0;
        if (!empty( $tblPersonList )) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber) {
                $CountNumber++;
                $Item['Count'] = $CountNumber;
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = $Item['Age'] = '';
                $Item['FS1'] = $Item['FS2'] = $Item['FS3'] = $Item['FS4'] = '';

                if (( $addressList = Address::useService()->getAddressAllByPerson($tblPerson) )) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }
                if ($address !== null) {
                    $Item['StreetName'] = $address->getTblAddress()->getStreetName();
                    $Item['StreetNumber'] = $address->getTblAddress()->getStreetNumber();
                    $Item['Code'] = $address->getTblAddress()->getTblCity()->getCode();
                    $Item['City'] = $address->getTblAddress()->getTblCity()->getName();

                    $Item['Address'] = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber().' '.
                        $address->getTblAddress()->getTblCity()->getCode().' '.
                        $address->getTblAddress()->getTblCity()->getName();
                }
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                }

                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                    $tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType);
                    if ($tblStudentSubjectList) {
                        foreach ($tblStudentSubjectList as $tblStudentSubject) {
                            if ($tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == 1) {
                                $tblSubject = $tblStudentSubject->getServiceTblSubject();
                                if ($tblSubject) {
                                    $Item['FS1'] = $tblSubject->getName();
                                }
                            }
                            if ($tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == 2) {
                                $tblSubject = $tblStudentSubject->getServiceTblSubject();
                                if ($tblSubject) {
                                    $Item['FS2'] = $tblSubject->getName();
                                }
                            }
                            if ($tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == 3) {
                                $tblSubject = $tblStudentSubject->getServiceTblSubject();
                                if ($tblSubject) {
                                    $Item['FS3'] = $tblSubject->getName();
                                }
                            }
                            if ($tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == 4) {
                                $tblSubject = $tblStudentSubject->getServiceTblSubject();
                                if ($tblSubject) {
                                    $Item['FS4'] = $tblSubject->getName();
                                }
                            }
                        }
                    }
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createLanguageListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::useWriter()->getTemporary('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "lfdNr.");
            $export->setValue($export->getCell("1", "0"), "Name");
            $export->setValue($export->getCell("2", "0"), "Vorname");
            $export->setValue($export->getCell("3", "0"), "Straße");
            $export->setValue($export->getCell("4", "0"), "Nummer");
            $export->setValue($export->getCell("5", "0"), "PLZ");
            $export->setValue($export->getCell("6", "0"), "Ort");
            $export->setValue($export->getCell("7", "0"), "Geb.-datum");
            $export->setValue($export->getCell("8", "0"), "Geburtsort");
            $export->setValue($export->getCell("9", "0"), "FS 1");
            $export->setValue($export->getCell("10", "0"), "FS 2");
            $export->setValue($export->getCell("11", "0"), "FS 3");
            $export->setValue($export->getCell("12", "0"), "FS 4");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['Count']);
                $export->setValue($export->getCell("1", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("3", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("4", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("5", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("6", $Row), $PersonData['City']);
                $export->setValue($export->getCell("7", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("8", $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell("9", $Row), $PersonData['FS1']);
                $export->setValue($export->getCell("10", $Row), $PersonData['FS2']);
                $export->setValue($export->getCell("11", $Row), $PersonData['FS3']);
                $export->setValue($export->getCell("12", $Row), $PersonData['FS4']);

                $Row++;
            }

            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

}