<?php
namespace SPHERE\Application\Reporting\Custom\Annaberg\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperSizeParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblType as TblTypeMail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson as TblToPersonPhone;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType as TblTypeRelationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Reporting\Standard\Person\Person;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

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
                if(($tblToPersonGuardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, TblTypeRelationship::IDENTIFIER_GUARDIAN))) {
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
                    if ($tblToPerson->getIsEmergencyContact()) {
                        $secureList[] = $tblPhone->getNumber().($getArray ? ' ' : '&nbsp;').$this->getShortTypeByTblToPersonPhone($tblToPerson);
                    } elseif($tblType->getName() == 'Privat') {
                        $privateList[] = $tblPhone->getNumber().($getArray ? ' ' : '&nbsp;').$this->getShortTypeByTblToPersonPhone($tblToPerson);
                    } elseif($tblType->getName() == 'Geschäftlich') {
                        $companyList[] = $tblPhone->getNumber().($getArray ? ' ' : '&nbsp;').$this->getShortTypeByTblToPersonPhone($tblToPerson);
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
        if ($tblToPerson->getIsEmergencyContact()) {
            $result = 'n';
        }

        if(($tblType = $tblToPerson->getTblType())) {
            switch ($tblType->getName()) {
                case 'Privat':
                    $result = 'p';
                    break;
                case 'Geschäftlich':
                    $result = 'g';
                    break;
                case 'Fax':
                    $result = 'f';
                    break;
            }
        }
        return $result;
    }

    public function createExportList(TblYear $tblYear): array
    {
        $resultList = [];
        if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYear))
            && ($tblRelationshipType = Relationship::useService()->getTypeByName(TblTypeRelationship::IDENTIFIER_GUARDIAN))
            && ($tblMailType = Mail::useService()->getTypeByName(TblTypeMail::VALUE_PRIVATE))
        ) {
            foreach ($tblStudentEducationList as $tblStudentEducation) {
                if (($tblPerson = $tblStudentEducation->getServiceTblPerson())) {
                    $tblStudent = $tblPerson->getStudent();
                    $item = [
                        'StudentNumber' => $tblStudent ? $tblStudent->getIdentifierComplete() : '',
                        'StudentFirstName' => $tblPerson->getFirstName(),
                        'StudentLastName' => $tblPerson->getLastName(),
                        'StudentCallName' => $tblPerson->getCallName(),
                        'StudentMail' => '',
                        'StudentPhone' => '',
                        'StudentBirthday' => $tblPerson->getBirthday(),
                        'Division' => $tblStudentEducation->getTblDivision() ? $tblStudentEducation->getTblDivision()->getName() : '',
                        'Groups' => $this->getGroupString($tblPerson, $tblYear),
                    ];

                    $guardianList = [];
                    $phoneList = [];
                    $mailList = [];
                    $accountList = [];
                    if (($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType))) {
                        foreach ($tblToPersonList as $tblToPerson) {
                            if (($tblPersonFrom = $tblToPerson->getServiceTblPersonFrom())) {
                                $guardianList[$tblToPerson->getRanking()] = $tblPersonFrom;

                                $phoneList[$tblToPerson->getRanking()] = $this->getFirstPrivatNumber($tblPersonFrom);
                                $mailList[$tblToPerson->getRanking()] =
                                    Mail::useService()->getFirstMailAddressByPersonAndType($tblPersonFrom, $tblMailType)?->getAddress();

                                if (($tblAccountList = Account::useService()->getAccountAllByPerson($tblPersonFrom))) {
                                    $tblAccount = current($tblAccountList);
                                    $accountList[$tblToPerson->getRanking()] = $tblAccount->getUsername();
                                }
                            }
                        }
                    }

                    for ($i = 1; $i <= 2; $i++) {
                        /** @var TblPerson $tblPersonGuardian */
                        $tblPersonGuardian = $guardianList[$i] ?? null;
                        $item["S$i Id"] = $accountList[$i] ?? '';
                        $item["S$i FirstName"] = $tblPersonGuardian?->getFirstSecondName();
                        $item["S$i LastName"] = $tblPersonGuardian?->getLastName();
                        $item["S$i Mail"] = $mailList[$i] ?? '';
                        $item["S$i Phone"] = $phoneList[$i] ?? '';
                    }

                    $resultList[$tblPerson->getId()] = $item;
                }
            }
        }

        return $resultList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return string
     */
    private function getGroupString(TblPerson $tblPerson, TblYear $tblYear): string
    {
        $resultList = [];

        // Stammgruppe, Unterrichtsgruppen und Lerngruppen
        if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByStudentAndYear($tblPerson, $tblYear))) {
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                if ($tblDivisionCourse->getType()->getIdentifier() != TblDivisionCourseType::TYPE_DIVISION) {
                    $resultList[$tblDivisionCourse->getId()] = $tblDivisionCourse->getName();
                }
            }
        }

        // SekII-Kurse
        $tblDivisionCourseListSekII = DivisionCourse::useService()->getCourseListForStudentByYear($tblPerson, $tblYear);
        foreach ($tblDivisionCourseListSekII as $tblDivisionCourseSekII) {
            // Namensänderung 11Gy G-ma1 → 11_G-ma1
            $name = $tblDivisionCourseSekII->getName();
            if (str_starts_with($name, '11Gy ')) {
                $name = str_replace('11Gy ', '11_', $name);
            } elseif (str_starts_with($name, '12Gy ')) {
                $name = str_replace('12Gy ', '12_', $name);
            }

            $resultList[] = $name;
        }

        return $resultList ? implode(', ', $resultList) : '';
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    private function getFirstPrivatNumber(TblPerson $tblPerson): string
    {
        if (($tblPhoneToPersonList = Phone::useService()->getPhoneAllByPerson($tblPerson))) {
            $tblPhoneToPersonList = $this->getSorter($tblPhoneToPersonList)->sortObjectBy('EntityCreate', new DateTimeSorter());
            /** @var TblToPerson $tblPhoneToPerson */
            foreach ($tblPhoneToPersonList as $tblPhoneToPerson) {
                if ($tblPhoneToPerson->getTblType()->getName() == 'Privat') {
                    return $tblPhoneToPerson->getTblPhone()->getNumber();
                }
            }
        }

        return '';
    }

    /**
     * @return string[]
     */
    public function getExportHeaderList(): array
    {
        $resulList = [
            'StudentNumber' => 'Externe ID Schüler (optional)',
            'StudentFirstName' => 'Vorname Schüler',
            'StudentLastName' => 'Nachname Schüler',
            'StudentCallName' => 'Rufname Schüler',
            'StudentMail' => 'E-Mail Schüler (optional)',
            'StudentPhone' => 'Telefonnummer Schüler (optional)',
            'StudentBirthday' => 'Geburtsdatum (DD.MM.YYYY)',
            'Division' => 'Klasse',
            'Groups' => 'Gruppen',
        ];

        for ($i = 1; $i <= 2; $i++) {
            $resulList["S$i Id"] = "Externe ID Eltern $i (optional)";
            $resulList["S$i FirstName"] = "Vorname Eltern $i";
            $resulList["S$i LastName"] = "Nachname Eltern $i";
            $resulList["S$i Mail"] = "E-Mail Eltern $i (optional)";
            $resulList["S$i Phone"] = "Telefonnummer Eltern $i (optional)";
        }

        return $resulList;
    }

    /**
     * @param array $headerList
     * @param array $dataList
     *
     * @return FilePointer
     */
    public function createExportListCSV(array $headerList, array $dataList): FilePointer
    {
        $fileLocation = Storage::createFilePointer('csv');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setDelimiter(';');

        $row = 0;
        $column = 0;
        foreach ($headerList as $header) {
            $export->setValue($export->getCell($column++, $row), $header);
        }
        $row++;
        foreach ($dataList as $data) {
            $column = 0;
            foreach ($headerList as $key => $value) {
                $export->setValue($export->getCell($column++, $row), $data[$key] ?? '');
            }
            $row++;
        }

        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

        return $fileLocation;
    }
}