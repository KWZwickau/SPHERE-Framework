<?php

namespace SPHERE\Application\Reporting\Custom\Muldental\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person as PeoplePerson;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;

class Service
{

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

        // Mentor Groups
        $SortGroupList = array();
        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {
                $tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson);
                if ($tblGroupList) {
                    $isGroup = false;
                    foreach ($tblGroupList as $tblGroup) {
                        if (preg_match('!(Mentorengruppe\s)([\w]*)!', $tblGroup->getName(), $Match)) {
                            $SortGroupList[$Match[2]][] = $tblPerson;
                            $isGroup = true;
                        }
                    }
                    if (!$isGroup) {
                        $SortGroupList['empty'][] = $tblPerson;
                    }
                }
            }
        }
        $tblPersonList = array();
        // Sorting
        if (!empty($SortGroupList)) {
            ksort($SortGroupList);
            foreach ($SortGroupList as $SortGroup) {
                PeoplePerson::useService()->getSorter($SortGroup);
                foreach ($SortGroup as $tblPerson) {
                    $tblPersonList[] = $tblPerson;
                }
            }
        }

        $TableContent = array();

        $CountNumber = 0;
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber) {
                $CountNumber++;
                // Content
                $Item['Division'] = '';
                $Item['Type'] = '';
                $Item['TypeExcel'] = '';
                $Item['Gender'] = '';
                $Item['GenderExcel'] = '';
//                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Birthday'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['ExcelStreet'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['PhoneNumbersPrivate'] = $Item['ExcelPhoneNumbersPrivate'] = '';
                $Item['PhoneNumbersGuardian1'] = $Item['ExcelPhoneNumbersGuardian1'] = '';
                $Item['PhoneNumbersGuardian2'] = $Item['ExcelPhoneNumbersGuardian2'] = '';
                $Item['MailAddress'] = $Item['ExcelMailAddress'] = '';

                //Division
                $tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
                $YearList = Term::useService()->getYearByNow();
                if ($tblDivisionStudentList && $YearList) {
                    $DivisionArray = array();
                    foreach ($YearList as $tblYear) {
                        foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                            if (( $tblDivision = $tblDivisionStudent->getTblDivision() )) {
                                if (( $tblYearDivision = $tblDivision->getServiceTblYear() )) {
                                    if ($tblYearDivision->getId() == $tblYear->getId()) {
                                        $DivisionArray[] = $tblDivision->getDisplayName();
                                    }
                                }
                            }
                        }
                    }
                    $Item['Division'] = implode(', ', $DivisionArray);
                }
                // Student
                if (( $tblStudent = Student::useService()->getStudentByPerson($tblPerson) )) {
                    $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                    $tblTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblTransferType);
                    if ($tblTransfer) {
                        if (( $tblType = $tblTransfer->getServiceTblType() )) {
                            $Item['Type'] = $tblType->getName();
                            switch ($Item['Type']) {
                                case "Mittelschule / Oberschule":
                                    $Item['TypeExcel'] = "OS";
                                    break;
                                case "Gymnasium":
                                    $Item['TypeExcel'] = "Gym";
                                    break;
                                case "Grundschule":
                                    $Item['TypeExcel'] = "GS";
                                    break;
                                default:
                                    $Item['TypeExcel'] = $Item['Type'];
                            }
                        }
                    }
                }

                // Birthday / Gender
                if (( $common = Common::useService()->getCommonByPerson($tblPerson) )) {
                    if (( $tblCommonBirthDates = $common->getTblCommonBirthDates() )) {
                        $Item['Birthday'] = $tblCommonBirthDates->getBirthday();
                        if (( $tblCommonGender = $tblCommonBirthDates->getTblCommonGender() )) {
                            $Item['Gender'] = $tblCommonGender->getName();
                            switch ($Item['Gender']) {
                                case "Männlich":
                                    $Item['GenderExcel'] = "m";
                                    break;
                                case "Weiblich":
                                    $Item['GenderExcel'] = "w";
                                    break;
                            }
                        }
                    }
                }
                // Address
                if (( $addressList = Address::useService()->getAddressAllByPerson($tblPerson) )) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }
                if ($address !== null) {
                    $Item['StreetName'] = $address->getTblAddress()->getStreetName();
                    $Item['StreetNumber'] = $address->getTblAddress()->getStreetNumber();
                    $Item['ExcelStreet'] = $address->getTblAddress()->getStreetName().' '.$address->getTblAddress()->getStreetNumber();
                    $Item['Code'] = $address->getTblAddress()->getTblCity()->getCode();
                    $Item['City'] = $address->getTblAddress()->getTblCity()->getName();
                    $Item['District'] = $address->getTblAddress()->getTblCity()->getDistrict();
                }

                // PhoneNumbers
                $phoneNumbersPrivate = array();
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        if ($phone->getTblType()->getName() == 'Privat') {
                            $phoneNumbersPrivate[] = $phone->getTblPhone()->getNumber();
                        }
                    }
                    if (!empty($phoneNumbersPrivate)) {
                        $Item['PhoneNumbersPrivate'] = implode('<br>', $phoneNumbersPrivate);
                        $Item['ExcelPhoneNumbersPrivate'] = $phoneNumbersPrivate;
                    }
                }

                // find Guardian
                $GuardianList = array();
                $tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($tblToPersonList) {
//                    if($tblPerson->getFirstName() == 'Steffen'){        // ToDO nach den Tests entfernen
                    foreach ($tblToPersonList as $tblToPerson) {
                        if (( $tblType = $tblToPerson->getTblType() )) {
                            if ($tblType->getName() == 'Sorgeberechtigt') {
                                $GuardianList[] = $tblToPerson->getServiceTblPersonFrom();
                            }
                        }
                    }
//                    }
                }

                $ContactMailList = array();
                if (!empty($GuardianList)) {
                    /** @var TblPerson $Guardian */
                    foreach ($GuardianList as $Key => $Guardian) {
                        // Guardian phone
                        $phoneList = Phone::useService()->getPhoneAllByPerson($Guardian);
                        if ($phoneList) {
                            $phoneNumbers = array();
                            foreach ($phoneList as $phone) {
                                if ($phone->getTblType()->getName() == 'Privat'
                                    || $phone->getTblType()->getName() == 'Geschäftlich'
                                ) {
                                    $phoneNumbers[] = $phone->getTblPhone()->getNumber();
                                }
                            }
                            if (!empty($phoneNumbers)) {
                                $Item['PhoneNumbersGuardian'.( $Key + 1 )] = implode('<br>', $phoneNumbers);
                                $Item['ExcelPhoneNumbersGuardian'.( $Key + 1 )] = $phoneNumbers;
                            }
                        }

                        // Guardian E-Mail
                        $tblMailList = Mail::useService()->getMailAllByPerson($Guardian);
                        if ($tblMailList) {
                            foreach ($tblMailList as $tblMail) {
                                if ($tblMail->getTblMail()) {
                                    $ContactMailList[] = $tblMail->getTblMail()->getAddress();
                                }
                            }
                        }
                    }
                }

                // E-Mail
                $tblMailList = Mail::useService()->getMailAllByPerson($tblPerson);
                if ($tblMailList) {
                    foreach ($tblMailList as $tblMail) {
                        if ($tblMail->getTblMail()) {
                            $ContactMailList[] = $tblMail->getTblMail()->getAddress();
                        }
                    }
                }

                // Insert MailList
                if (!empty($ContactMailList)) {
                    $Item['MailAddress'] .= implode('<br>', $ContactMailList);
                    $Item['ExcelMailAddress'] = $ContactMailList;
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
     * @return bool|\SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell(0, 0), "Kl.");
            $export->setValue($export->getCell(1, 0), "Sch.");
            $export->setValue($export->getCell(2, 0), "M");
            $export->setValue($export->getCell(3, 0), "G");
            $export->setValue($export->getCell(4, 0), "Name");
            $export->setValue($export->getCell(5, 0), "Vorname");
            $export->setValue($export->getCell(6, 0), "Straße");
            $export->setValue($export->getCell(7, 0), "PLZ");
            $export->setValue($export->getCell(8, 0), "Wohnort");
            $export->setValue($export->getCell(9, 0), "Ortsteil");
            $export->setValue($export->getCell(10, 0), "Tel. Privat");
            $export->setValue($export->getCell(11, 0), "Sorg.1");
            $export->setValue($export->getCell(12, 0), "Sorg.2");
            $export->setValue($export->getCell(13, 0), "E-Mail");
            $export->setValue($export->getCell(14, 0), "Geburtsd.");

            // Table Head
            $export->setStyle($export->getCell(0, 0), $export->getCell(14, 0))
                ->setFontBold()
                ->setBorderAll()
                ->setBorderBottom(2);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, 0))
                ->setFontSize(10);
            $export->setStyle($export->getCell(10, 0), $export->getCell(10, 0))
                ->setFontSize(10);


            $Row = 0;
            foreach ($PersonList as $PersonData) {
                $Row++;
                // set border for each Person
                $export->setStyle($export->getCell(0, $Row), $export->getCell(14, $Row))
                    ->setBorderTop();
                $PhonePRow = $PhoneGuardianARow = $PhoneGuardianBRow = $MailRow = $DistrictRow = $Row;
                $export->setValue($export->getCell(0, $Row), $PersonData['Division']);
                $export->setValue($export->getCell(1, $Row), $PersonData['Type']);
                $export->setValue($export->getCell(2, $Row), '');
                $export->setValue($export->getCell(3, $Row), '');
                $export->setValue($export->getCell(4, $Row), $PersonData['LastName']);
                $export->setValue($export->getCell(5, $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell(6, $Row), $PersonData['ExcelStreet']);
                $export->setValue($export->getCell(7, $Row), $PersonData['Code']);
                $export->setValue($export->getCell(8, $Row), $PersonData['City']);
                $export->setValue($export->getCell(9, $Row), $PersonData['District']);
//                $export->setValue($export->getCell(10, $Row), $PersonData['ExcelPhoneNumbersPrivate']);
//                $export->setValue($export->getCell(11, $Row), $PersonData['ExcelPhoneNumbersGuardian1']);
//                $export->setValue($export->getCell(12, $Row), $PersonData['ExcelPhoneNumbersGuardian2']);
//                $export->setValue($export->getCell(13, $Row), $PersonData['ExcelMailAddress']);
                $export->setValue($export->getCell(14, $Row), $PersonData['Birthday']);

                if (is_array($PersonData['ExcelPhoneNumbersPrivate'])) {
                    foreach ($PersonData['ExcelPhoneNumbersPrivate'] as $PhonePrivate) {
                        $export->setValue($export->getCell(10, $PhonePRow++), $PhonePrivate);
                    }
                }
                if (is_array($PersonData['ExcelPhoneNumbersGuardian1'])) {
                    foreach ($PersonData['ExcelPhoneNumbersGuardian1'] as $PhoneBusiness) {
                        $export->setValue($export->getCell(11, $PhoneGuardianARow++), $PhoneBusiness);
                    }
                }
                if (is_array($PersonData['ExcelPhoneNumbersGuardian1'])) {
                    foreach ($PersonData['ExcelPhoneNumbersGuardian1'] as $PhoneBusiness) {
                        $export->setValue($export->getCell(12, $PhoneGuardianBRow++), $PhoneBusiness);
                    }
                }
                if (is_array($PersonData['ExcelMailAddress'])) {
                    foreach ($PersonData['ExcelMailAddress'] as $Mail) {
                        $export->setValue($export->getCell(13, $MailRow++), $Mail);
                    }
                }

                if ($Row < ( $PhonePRow - 1 )) {
                    $Row = ( $PhonePRow - 1 );
                }
                if ($Row < ( $PhoneGuardianARow - 1 )) {
                    $Row = ( $PhoneGuardianARow - 1 );
                }
                if ($Row < ( $PhoneGuardianBRow - 1 )) {
                    $Row = ( $PhoneGuardianBRow - 1 );
                }
                if ($Row < ( $MailRow - 1 )) {
                    $Row = ( $MailRow - 1 );
                }
            }

            // Table Border
            $export->setStyle($export->getCell(0, 1), $export->getCell(14, $Row))
                ->setFontSize(14)
                ->setBorderVertical()
                ->setBorderLeft()
                ->setBorderRight()
                ->setBorderBottom();
//                ->setBorderAll();

            // Column Width
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $Row))->setColumnWidth(3)->setFontSize(10);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setColumnWidth(10)->setFontSize(10);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $Row))->setColumnWidth(10)->setFontSize(10);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $Row))->setColumnWidth(10)->setFontSize(10);
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setColumnWidth(10)->setFontSize(10);
            $export->setStyle($export->getCell(5, 0), $export->getCell(5, $Row))->setColumnWidth(10)->setFontSize(10);
            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $Row))->setColumnWidth(10)->setFontSize(10);
            $export->setStyle($export->getCell(7, 0), $export->getCell(7, $Row))->setColumnWidth(10)->setFontSize(10);
            $export->setStyle($export->getCell(8, 0), $export->getCell(8, $Row))->setColumnWidth(10)->setFontSize(10);
            $export->setStyle($export->getCell(9, 0), $export->getCell(9, $Row))->setColumnWidth(10)->setFontSize(10);
            $export->setStyle($export->getCell(10, 0), $export->getCell(10, $Row))->setColumnWidth(10)->setFontSize(10);
            $export->setStyle($export->getCell(11, 0), $export->getCell(11, $Row))->setColumnWidth(10)->setFontSize(10);
            $export->setStyle($export->getCell(12, 0), $export->getCell(12, $Row))->setColumnWidth(10)->setFontSize(10);
            $export->setStyle($export->getCell(13, 0), $export->getCell(13, $Row))->setColumnWidth(10)->setFontSize(10);
            $export->setStyle($export->getCell(14, 0), $export->getCell(14, $Row))->setColumnWidth(10)->setFontSize(10);

            $Row++;
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Weiblich:');
            $export->setValue($export->getCell(1, $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Männlich:');
            $export->setValue($export->getCell(1, $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Gesamt:');
            $export->setValue($export->getCell(1, $Row), count($tblPersonList));

            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Stand '.date("d.m.Y"));
            $export->setStyle($export->getCell(0, $Row), $export->getCell(1, $Row))->mergeCells();

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }
}