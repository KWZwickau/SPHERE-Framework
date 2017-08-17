<?php

namespace SPHERE\Application\Reporting\Standard\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson as TblToPersonPhone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Search\Group\Group;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Text\Repository\Code;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Standard\Person
 */
class Service extends Extension
{

    /**
     * @param IFormInterface|null $Stage
     * @param null $Select
     * @param                     $Redirect
     *
     * @return IFormInterface|Redirect
     */
    public function getGroup(IFormInterface $Stage = null, $Select = null, $Redirect)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Select) {
            return $Stage;
        }

        $tblGroup = Group::useService()->getGroupById($Select['Group']);
        if ($tblGroup) {
            return new Redirect($Redirect, Redirect::TIMEOUT_SUCCESS, array(
                'GroupId' => $tblGroup->getId(),
            ));
        } else {
            $Stage->setError('Select[Group]', 'Bitte wählen Sie eine Gruppe aus');
            return $Stage;
        }
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

        $TableContent = array();
        if (!empty($tblPersonList)) {

            $count = 1;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {
                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }

                $Item['Number'] = $count++;
                $Item['Salutation'] = $tblPerson->getSalutation();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Phone'] = '';
                $Item['ExcelPhone'] = '';
                $Item['Mail'] = '';
                $Item['ExcelMail'] = '';

                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                } else {
                    $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                    $Item['Address'] = '';
                }

                //Phone
                $tblPhoneList = array();
                $tblToPersonPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($tblToPersonPhoneList) {
                    foreach ($tblToPersonPhoneList as $tblToPersonPhone) {
                        $tblPhone = $tblToPersonPhone->getTblPhone();
                        if ($tblPhone) {
                            if (isset($tblPhoneList[$tblPerson->getId()])) {
                                $tblPhoneList[$tblPerson->getId()] = $tblPhoneList[$tblPerson->getId()] . ', '
                                    . $tblPhone->getNumber() . ' ' . $this->getShortTypeByTblToPersonPhone($tblToPersonPhone);
                            } else {
                                $tblPhoneList[$tblPerson->getId()] = $tblPerson->getFirstName() . ' ' . $tblPerson->getLastName() . ' ('
                                    . $tblPhone->getNumber() . ' ' . $this->getShortTypeByTblToPersonPhone($tblToPersonPhone);
                            }
                        }
                    }
                    if (isset($tblPhoneList[$tblPerson->getId()])) {
                        $tblPhoneList[$tblPerson->getId()] .= ')';
                    }
                }

                //Mail
                $tblMailList = array();
                $tblToPersonMailList = Mail::useService()->getMailAllByPerson($tblPerson);
                if ($tblToPersonMailList) {
                    foreach ($tblToPersonMailList as $tblToPersonMail) {
                        $tblMail = $tblToPersonMail->getTblMail();
                        if ($tblMail) {
                            if (isset($tblMailList[$tblPerson->getId()])) {
                                $tblMailList[$tblPerson->getId()] = $tblMailList[$tblPerson->getId()] . ', '
                                    . $tblMail->getAddress();
                            } else {
                                $tblMailList[$tblPerson->getId()] = $tblPerson->getFirstName() . ' ' . $tblPerson->getLastName() . ' ('
                                    . $tblMail->getAddress();
                            }
                        }
                    }
                    if (isset($tblMailList[$tblPerson->getId()])) {
                        $tblMailList[$tblPerson->getId()] .= ')';
                    }
                }

                $tblType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                $tblToPersonGuardianList = false;
                if ($tblType) {
                    $tblToPersonGuardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson,
                        $tblType);
                }
                if ($tblToPersonGuardianList) {
                    foreach ($tblToPersonGuardianList as $tblToPersonGuardian) {
                        //Phone Guardian
                        $tblPersonGuardian = $tblToPersonGuardian->getServiceTblPersonFrom();
                        $tblToPersonPhoneList = Phone::useService()->getPhoneAllByPerson($tblPersonGuardian);
                        if ($tblToPersonPhoneList) {
                            foreach ($tblToPersonPhoneList as $tblToPersonPhone) {
                                $tblPhone = $tblToPersonPhone->getTblPhone();
                                if ($tblPhone) {
                                    if (isset($tblPhoneList[$tblPersonGuardian->getId()])) {
                                        $tblPhoneList[$tblPersonGuardian->getId()] = $tblPhoneList[$tblPersonGuardian->getId()] . ', '
                                            . $tblPhone->getNumber() . ' ' . $this->getShortTypeByTblToPersonPhone($tblToPersonPhone);
                                    } else {
                                        $tblPhoneList[$tblPersonGuardian->getId()] = $tblPersonGuardian->getFirstName() . ' ' .
                                            $tblPersonGuardian->getLastName() . ' ('
                                            . $tblPhone->getNumber() . ' ' . $this->getShortTypeByTblToPersonPhone($tblToPersonPhone);
                                    }
                                }
                            }
                            if (isset($tblPhoneList[$tblPersonGuardian->getId()])) {
                                $tblPhoneList[$tblPersonGuardian->getId()] .= ')';
                            }
                        }

                        //Mail Guardian
                        $tblToPersonMailList = Mail::useService()->getMailAllByPerson($tblPersonGuardian);
                        if ($tblToPersonMailList) {
                            foreach ($tblToPersonMailList as $tblToPersonMail) {
                                $tblMail = $tblToPersonMail->getTblMail();
                                if ($tblMail) {
                                    if (isset($tblMailList[$tblPersonGuardian->getId()])) {
                                        $tblMailList[$tblPersonGuardian->getId()] = $tblMailList[$tblPersonGuardian->getId()] . ', '
                                            . $tblMail->getAddress();
                                    } else {
                                        $tblMailList[$tblPersonGuardian->getId()] = $tblPersonGuardian->getFirstName() . ' ' .
                                            $tblPersonGuardian->getLastName() . ' ('
                                            . $tblMail->getAddress();
                                    }
                                }
                            }
                            if (isset($tblMailList[$tblPersonGuardian->getId()])) {
                                $tblMailList[$tblPersonGuardian->getId()] .= ')';
                            }
                        }
                    }
                }

                // Insert PhoneList
                if (!empty($tblPhoneList)) {
                    $Item['Phone'] .= implode('<br>', $tblPhoneList);
                    $Item['ExcelPhone'] = $tblPhoneList;
                }
                // Insert MailList
                if (!empty($tblMailList)) {
                    $Item['Mail'] .= implode('<br>', $tblMailList);
                    $Item['ExcelMail'] = $tblMailList;
                }


                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Denomination'] = $common->getTblCommonInformation()->getDenomination();
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                } else {
                    $Item['Denomination'] = $Item['Birthday'] = $Item['Birthplace'] = '';
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
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary|false
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Vorname");
            $export->setValue($export->getCell("1", "0"), "Name");
            $export->setValue($export->getCell("2", "0"), "Konfession");
            $export->setValue($export->getCell("3", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("4", "0"), "Geburtsort");
            $export->setValue($export->getCell("5", "0"), "Ortsteil");
            $export->setValue($export->getCell("6", "0"), "Straße");
            $export->setValue($export->getCell("7", "0"), "Hausnr.");
            $export->setValue($export->getCell("8", "0"), "PLZ");
            $export->setValue($export->getCell("9", "0"), "Ort");
            $export->setValue($export->getCell("10", "0"), "Telefon");
            $export->setValue($export->getCell("11", "0"), "E-Mail");

            $export->setStyle($export->getCell(0, 0), $export->getCell(11, 0))
                ->setFontBold();

            $Row = 0;

            foreach ($PersonList as $PersonData) {
                $Row++;
                $phoneRow = $mailRow = $Row;

                $export->setValue($export->getCell("0", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("1", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['Denomination']);
                $export->setValue($export->getCell("3", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("4", $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell("5", $Row), $PersonData['District']);
                $export->setValue($export->getCell("6", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("7", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("8", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("9", $Row), $PersonData['City']);
//                $export->setValue($export->getCell("10", $Row), $PersonData['ExcelPhone']);
//                $export->setValue($export->getCell("11", $Row), $PersonData['ExcelMail']);

                if (is_array($PersonData['ExcelPhone'])) {
                    foreach ($PersonData['ExcelPhone'] as $Phone) {
                        $export->setValue($export->getCell(10, $phoneRow++), $Phone);
                    }
                }
                if (is_array($PersonData['ExcelMail'])) {
                    foreach ($PersonData['ExcelMail'] as $Mail) {
                        $export->setValue($export->getCell(11, $mailRow++), $Mail);
                    }
                }
                // get row to the same high as highest PhoneRow or MailRow
                if ($Row < ($phoneRow - 1)) {
                    $Row = ($phoneRow - 1);
                }
                if ($Row < ($mailRow - 1)) {
                    $Row = ($mailRow - 1);
                }
            }

            //Column width
            $export->setStyle($export->getCell(10, 0), $export->getCell(10, $Row))->setColumnWidth(35);
            $export->setStyle($export->getCell(11, 0), $export->getCell(11, $Row))->setColumnWidth(45);

            $Row++;
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            // Legende
            $Row = $Row - 2;
            $export->setValue($export->getCell("10", $Row), 'Abkürzungen Telefon:');
            $Row++;
            $export->setValue($export->getCell("10", $Row), 'p = Privat');
            $Row++;
            $export->setValue($export->getCell("10", $Row), 'g = Geschäftlich');
            $Row++;
            $export->setValue($export->getCell("10", $Row), 'n = Notfall');
            $Row++;
            $export->setValue($export->getCell("10", $Row), 'f = Fax');

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
    public function createExtendedClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();
        if (!empty($tblPersonList)) {

            $count = 1;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {

                $Item['Number'] = $count++;
                $Item['FirstName'] = $tblPerson->getFirstName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StudentNumber'] = '';
                $Item['Gender'] = '';
                $Item['Guardian1'] = '';
                $Item['Guardian2'] = '';
                $Item['PhoneGuardian1'] = '';
                $Item['PhoneGuardian2'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = '';
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $tblBirhdates = $tblCommon->getTblCommonBirthDates();
                    if ($tblBirhdates) {
                        if ($tblBirhdates->getGender() == 1) {
                            $Item['Gender'] = 'männlich';
                        } elseif ($tblBirhdates->getGender() == 2) {
                            $Item['Gender'] = 'weiblich';
                        }
                    }
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {
                        $Item['StudentNumber'] = $tblStudent->getIdentifier();
                    }
                }
                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                }
                $Guardian1 = null;
                $Guardian2 = null;
                unset($phoneListGuardian1);
                unset($phoneListGuardian2);
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    $Count = 0;
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getTblType()->getName() == 'Sorgeberechtigt') {
                            if ($Count === 0) {
                                if ($guardian->getServiceTblPersonFrom()) {
                                    $Guardian1 = $guardian->getServiceTblPersonFrom();
                                }
                            }
                            if ($Count === 1) {
                                if ($guardian->getServiceTblPersonFrom()) {
                                    $Guardian2 = $guardian->getServiceTblPersonFrom();
                                }
                            }
                            $Count = $Count + 1;
                        }
                    }
                }
                if (isset($Guardian1)) {
                    $Item['Guardian1'] = $Guardian1->getFullName();
                    $Guardian1PhoneList = Phone::useService()->getPhoneAllByPerson($Guardian1);
                    if ($Guardian1PhoneList) {
                        foreach ($Guardian1PhoneList as $Guardian1Phone) {
                            if ($Guardian1Phone->getTblType()->getName() === 'Privat' && $Guardian1Phone->getTblType()->getDescription() === 'Mobil') {
                                $phoneListGuardian1[] = $Guardian1Phone->getTblPhone()->getNumber();
                            }
                        }
                        foreach ($Guardian1PhoneList as $Guardian1Phone) {
                            if ($Guardian1Phone->getTblType()->getName() === 'Privat') {
                                $phoneListGuardian1[] = $Guardian1Phone->getTblPhone()->getNumber();
                            }
                        }
                        if (isset($phoneListGuardian1)) {
                            $phoneListGuardian1 = array_unique($phoneListGuardian1);
                        }
                    }
                }
                if (isset($Guardian2)) {
                    $Item['Guardian2'] = $Guardian2->getFullName();
                    $Guardian2PhoneList = Phone::useService()->getPhoneAllByPerson($Guardian2);
                    if ($Guardian2PhoneList) {
                        foreach ($Guardian2PhoneList as $Guardian2Phone) {
                            if ($Guardian2Phone->getTblType()->getName() === 'Privat' && $Guardian2Phone->getTblType()->getDescription() === 'Mobil') {
                                $phoneListGuardian2[] = $Guardian2Phone->getTblPhone()->getNumber();
                            }
                        }
                        foreach ($Guardian2PhoneList as $Guardian2Phone) {
                            if ($Guardian2Phone->getTblType()->getName() === 'Privat') {
                                $phoneListGuardian2[] = $Guardian2Phone->getTblPhone()->getNumber();
                            }
                        }
                        if (isset($phoneListGuardian2)) {
                            $phoneListGuardian2 = array_unique($phoneListGuardian2);
                        }
                    }
                }

                if (isset($phoneListGuardian1[0])) {
                    $Item['PhoneGuardian1'] = $phoneListGuardian1[0];
                    if (isset($phoneListGuardian2[0])) {
                        if ($phoneListGuardian2[0] === $phoneListGuardian1[0] && isset($phoneListGuardian1[0])) {
                            if (isset($phoneListGuardian2[1])) {
                                $Item['PhoneGuardian2'] = $phoneListGuardian2[1];
                            }
                        } else {
                            if (isset($phoneListGuardian2[0])) {
                                $Item['PhoneGuardian2'] = $phoneListGuardian2[0];
                            }
                        }
                    }
                } else {
                    $tblPerson->PhoneGuardian1 = '';
                    if (isset($phoneListGuardian2[0])) {
                        $Item['PhoneGuardian2'] = $phoneListGuardian2[0];
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
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary|false
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createExtendedClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "#");
            $export->setValue($export->getCell("1", "0"), "Schülernummer");
            $export->setValue($export->getCell("2", "0"), "Name");
            $export->setValue($export->getCell("3", "0"), "Vorname");
            $export->setValue($export->getCell("4", "0"), "Geschlecht");
            $export->setValue($export->getCell("5", "0"), "Adresse");
            $export->setValue($export->getCell("6", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("7", "0"), "Geburtsort");
            $export->setValue($export->getCell("8", "0"), "Sorgeberechtigter 1");
            $export->setValue($export->getCell("9", "0"), "Tel. Sorgeber. 1");
            $export->setValue($export->getCell("10", "0"), "Sorgeberechtigter 2");
            $export->setValue($export->getCell("11", "0"), "Tel. Sorgeber. 2");

            $Row = 1;

            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['Number']);
                $export->setValue($export->getCell("1", $Row), $PersonData['StudentNumber']);
                $export->setValue($export->getCell("2", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("3", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("4", $Row), $PersonData['Gender']);
                $export->setValue($export->getCell("5", $Row), $PersonData['Address']);
                $export->setValue($export->getCell("6", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("7", $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell("8", $Row), $PersonData['Guardian1']);
                $export->setValue($export->getCell("9", $Row), $PersonData['PhoneGuardian1']);
                $export->setValue($export->getCell("10", $Row), $PersonData['Guardian2']);
                $export->setValue($export->getCell("11", $Row), $PersonData['PhoneGuardian2']);

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
    public function createBirthdayClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

        $TableContent = array();

        $All = 0;

        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$All) {
//                $All++;
//                $Item['Number'] = $All;
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['Gender'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = $Item['Age'] = '';
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $tblBirhdates = $tblCommon->getTblCommonBirthDates();
                    if ($tblBirhdates) {
                        if ($tblBirhdates->getGender() === 1) {
                            $Item['Gender'] = 'männlich';
                        } elseif ($tblBirhdates->getGender() === 2) {
                            $Item['Gender'] = 'weiblich';
                        }
                    }
                }

                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                    $birthDate = (new \DateTime($common->getTblCommonBirthDates()->getBirthday()));
                    $now = new \DateTime();
                    if ($birthDate->format('Y.m') != $now->format('Y.m')) {
                        if (($birthDate->format('m.d')) <= ($now->format('m.d'))) {
                            $Item['Age'] = $now->format('Y') - $birthDate->format('Y');
                        } else {
                            $Item['Age'] = ($now->format('Y') - 1) - $birthDate->format('Y');
                        }
                    }
                }
                array_push($TableContent, $Item);
            });
        }
        if (!empty($TableContent)) {

            $day = array();
            $month = array();
            $year = array();
            foreach ($TableContent as $key => $row) {
                $month[$key] = substr($row['Birthday'], 3, 2);
                $day[$key] = substr($row['Birthday'], 0, 2);
                $year[$key] = substr($row['Birthday'], 6, 4);
            }
            array_multisort($month, SORT_ASC, $day, SORT_ASC, $year, SORT_DESC, $TableContent);


            array_walk($TableContent, function (&$Row) use (&$All) {
                $All++;
                $Row['Number'] = $All;
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary|false
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createBirthdayClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "lfd. Nr.");
            $export->setValue($export->getCell("1", "0"), "Name, Vorname");
            $export->setValue($export->getCell("2", "0"), "Anschrift");
            $export->setValue($export->getCell("3", "0"), "Geburtsort");
            $export->setValue($export->getCell("4", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("5", "0"), "Alter");

            $Row = 1;

            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['Number']);
                $export->setValue($export->getCell("1", $Row), $PersonData['Name']);
                $export->setValue($export->getCell("2", $Row), $PersonData['Address']);
                $export->setValue($export->getCell("3", $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell("4", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("5", $Row), $PersonData['Age']);

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
    public function createMedicalInsuranceClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();

        if (!empty($tblPersonList)) {

            $count = 1;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {

                $Item['Number'] = $count++;
                $Item['MedicalInsurance'] = '';
                $Item['StudentNumber'] = '';
                $Item['Gender'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = '';
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $tblBirhdates = $tblCommon->getTblCommonBirthDates();
                    if ($tblBirhdates) {
                        if ($tblBirhdates->getGender() === 1) {
                            $Item['Gender'] = 'männlich';
                        } elseif ($tblBirhdates->getGender() === 2) {
                            $Item['Gender'] = 'weiblich';
                        }
                    }

                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {
                        if ($tblStudent->getTblStudentMedicalRecord()) {
                            $Item['MedicalInsurance'] = $tblStudent->getTblStudentMedicalRecord()->getInsurance();
                        }
                        $Item['StudentNumber'] = $tblStudent->getIdentifier();
                    }
                }
                $Item['Name'] = $tblPerson->getLastFirstName();
                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday() . '<br/>' . $common->getTblCommonBirthDates()->getBirthplace();
                }

                $Guardian1 = null;
                $Guardian2 = null;
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    $Count = 0;
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getServiceTblPersonFrom() && $guardian->getTblType()->getName() == 'Sorgeberechtigt') {
                            if ($Count === 0) {
                                $Guardian1 = $guardian->getServiceTblPersonFrom();
                            }
                            if ($Count === 1) {
                                $Guardian2 = $guardian->getServiceTblPersonFrom();
                            }
                            $Count++;
                        }
                    }
                }

                $phoneListGuardian = array();
                if ($Guardian1) {
                    $PhoneListGuardian1 = Phone::useService()->getPhoneAllByPerson($Guardian1);
                    if ($PhoneListGuardian1) {
                        foreach ($PhoneListGuardian1 as $PhoneGuardian1) {
                            $phoneListGuardian[] = $PhoneGuardian1->getTblPhone()->getNumber();
                        }
                    }
                    $Guardian1 = $Guardian1->getFullName();
                } else {
                    $Guardian1 = '';
                }
                if ($Guardian2) {
                    $PhoneListGuardian2 = Phone::useService()->getPhoneAllByPerson($Guardian2);
                    if ($PhoneListGuardian2) {
                        foreach ($PhoneListGuardian2 as $PhoneGuardian2) {
                            $phoneListGuardian[] = $PhoneGuardian2->getTblPhone()->getNumber();
                        }
                    }
                    $Guardian2 = $Guardian2->getFullName();
                } else {
                    $Guardian2 = '';
                }
                $Item['Guardian'] = $Guardian1 . '<br/>' . $Guardian2;

                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                $phoneArray = array();
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        $phoneArray[] = $phone->getTblPhone()->getNumber();
                    }
                }
                if (count($phoneArray) >= 1) {
                    $phoneString = implode('<br/>', $phoneArray);
                } else {
                    $phoneString = '';
                }
                $Item['PhoneNumber'] = $phoneString;
                $phoneListGuardian = array_unique($phoneListGuardian);
                if (count($phoneListGuardian) >= 1) {
                    $phoneGuardianString = implode('<br/>', $phoneListGuardian);
                } else {
                    $phoneGuardianString = '';
                }
                $Item['PhoneGuardianNumber'] = $phoneGuardianString;

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary|false
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createMedicalInsuranceClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Schülernummer");
            $export->setValue($export->getCell("1", "0"), "Name, Vorname");
            $export->setValue($export->getCell("2", "0"), "Anschrift");
            $export->setValue($export->getCell("3", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("3", "1"), "Geburtsort");
            $export->setValue($export->getCell("4", "0"), "Krankenkasse");
            $export->setValue($export->getCell("5", "0"), "1. Sorgeberechtigter");
            $export->setValue($export->getCell("5", "1"), "2. Sorgeberechtigter");
            $export->setValue($export->getCell("6", "0"), "Telefon");
            $export->setValue($export->getCell("6", "1"), "Schüler");
            $export->setValue($export->getCell("7", "0"), "Telefon");
            $export->setValue($export->getCell("7", "1"), "Sorgeberechtigte");

            $Row = 2;

            foreach ($PersonList as $PersonData) {
                $Name = explode('<br/>', $PersonData['Name']);
                $Address = explode('<br/>', $PersonData['Address']);
                $Birthday = explode('<br/>', $PersonData['Birthday']);
                $KK = explode('<br/>', $PersonData['MedicalInsurance']);
                $Guardian = explode('<br/>', $PersonData['Guardian']);
                $PhoneNumber = explode('<br/>', $PersonData['PhoneNumber']);
                $PhoneGuardianNumber = explode('<br/>', $PersonData['PhoneGuardianNumber']);

                $count = count($Name);
                if (count($Address) > $count) {
                    $count = count($Address);
                }
                if (count($KK) > $count) {
                    $count = count($KK);
                }
                if (count($Guardian) > $count) {
                    $count = count($Guardian);
                }
                if (count($PhoneNumber) > $count) {
                    $count = count($PhoneNumber);
                }
                if (count($PhoneGuardianNumber) > $count) {
                    $count = count($PhoneGuardianNumber);
                }

                $export->setValue($export->getCell("0", $Row), $PersonData['Number']);
                for ($i = 0; $i < $count; $i++) {
                    if (isset($Name[$i])) {
                        $export->setValue($export->getCell("1", $Row), $Name[$i]);
                    }
                    if (isset($Address[$i])) {
                        $export->setValue($export->getCell("2", $Row), $Address[$i]);
                    }
                    if (isset($Birthday[$i])) {
                        $export->setValue($export->getCell("3", $Row), $Birthday[$i]);
                    }
                    if (isset($KK[$i])) {
                        $export->setValue($export->getCell("4", $Row), $KK[$i]);
                    }
                    if (isset($Guardian[$i])) {
                        $export->setValue($export->getCell("5", $Row), $Guardian[$i]);
                    }
                    if (isset($PhoneNumber[$i])) {
                        $export->setValue($export->getCell("6", $Row), $PhoneNumber[$i]);
                    }
                    if (isset($PhoneGuardianNumber[$i])) {
                        $export->setValue($export->getCell("7", $Row), $PhoneGuardianNumber[$i]);
                    }
                    $Row++;
                }
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
     * @param TblGroup $tblGroup
     *
     * @return array
     */
    public function createGroupList(TblGroup $tblGroup)
    {

        $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
        $TableContent = array();

        if (!empty($tblPersonList)) {

            $lastName = array();
            $firstName = array();
            foreach ($tblPersonList as $key => $row) {
                $lastName[$key] = strtoupper($row->getLastName());
                $firstName[$key] = strtoupper($row->getFirstName());
                $id[$key] = $row->getId();
            }
            array_multisort($lastName, SORT_ASC, $firstName, SORT_ASC, $tblPersonList);

            $All = 0;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$All) {

                $All++;
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Number'] = $All;
                $Item['Salutation'] = $tblPerson->getSalutation();
                $Item['Gender'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] =  $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = '';
                $Item['PhoneNumber'] = '';
                $Item['MobilPhoneNumber'] = '';
                $Item['Mail'] = '';
                $Item['BirthPlace'] = '';
                $Item['Gender'] = '';
                $Item['Nationality'] = '';
                $Item['Religion'] = '';
                $Item['ParticipationWillingness'] = '';
                $Item['ParticipationActivities'] = '';
                $Item['RemarkFrontend'] = '';
                $Item['RemarkExcel'] = '';

                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $Item['RemarkExcel'] = $tblCommon->getRemark();
                    $Item['RemarkFrontend'] = nl2br($tblCommon->getRemark());
                    if (($tblBirthdates = $tblCommon->getTblCommonBirthDates())) {
                        $Item['Birthday'] = $tblBirthdates->getBirthday();
                        $Item['BirthPlace'] = $tblBirthdates->getBirthplace();
                        if (($tblGender = $tblBirthdates->getTblCommonGender())) {
                            $Item['Gender'] = $tblGender->getName();
                        }
                    }
                    if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                        $Item['Nationality'] = $tblCommonInformation->getNationality();
                        $Item['Religion'] = $tblCommonInformation->getDenomination();
                        $Item['ParticipationActivities'] = $tblCommonInformation->getAssistanceActivity();
                        if ($tblCommonInformation->isAssistance()) {
                            $Item['ParticipationWillingness'] = 'ja';
                        } else {
                            $Item['ParticipationWillingness'] = 'nein';

                        }
                    }
                }
                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                }
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);

                $phoneArray = array();
                $mobilePhoneArray = array();
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        if ($phone->getTblType()->getDescription() === 'Festnetz') {
                            $phoneArray[] = $phone->getTblPhone()->getNumber();
                        }
                        if ($phone->getTblType()->getDescription() === 'Mobil') {
                            $mobilePhoneArray[] = $phone->getTblPhone()->getNumber();
                        }
                    }
                }
                if (count($phoneArray) >= 1) {
                    $Item['PhoneNumber'] = implode(', ', $phoneArray);
                }
                if (count($mobilePhoneArray) >= 1) {
                    $Item['MobilPhoneNumber'] = implode(', ', $mobilePhoneArray);
                }
                $mailAddressList = Mail::useService()->getMailAllByPerson($tblPerson);
                $mailList = array();
                if ($mailAddressList) {
                    foreach ($mailAddressList as $mailAddress) {
                        $mailList[] = $mailAddress->getTblMail()->getAddress();
                    }
                }
                if (count($mailList) >= 1) {
                    $Item['Mail'] = $mailList[0];
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
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary|false
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createGroupListExcel($PersonList, $tblPersonList, $GroupId)
    {

        $tblGroup = Group::useService()->getGroupById($GroupId);
        if (!empty($PersonList) && $tblGroup) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $Row = 0;

            $export->setStyle($export->getCell(0, 0), $export->getCell(12, 0))
                ->mergeCells()->setAlignmentCenter();
            $export->setValue($export->getCell(0, 0), 'Gruppenliste ' . $tblGroup->getName());

            if (!empty($tblGroup->getDescription())) {
                $Row++;
                $export->setStyle($export->getCell(0, 1), $export->getCell(12, 1))
                    ->mergeCells()->setAlignmentCenter();
                $export->setValue($export->getCell(0, 1), $tblGroup->getDescription());
            }

            if (!empty($tblGroup->getRemark())) {
                $Row++;
                $export->setStyle($export->getCell(0, 2), $export->getCell(12, 2))
                    ->mergeCells()->setAlignmentCenter();
                $export->setValue($export->getCell(0, 2), $tblGroup->getRemark());
            }

            $Row += 2;

            $export->setValue($export->getCell("0", $Row), "lfd. Nr.");
            $export->setValue($export->getCell("1", $Row), "Anrede");
            $export->setValue($export->getCell("2", $Row), "Vorname");
            $export->setValue($export->getCell("3", $Row), "Nachname");
            $export->setValue($export->getCell("4", $Row), "Straße");
            $export->setValue($export->getCell("5", $Row), "Nummer");
            $export->setValue($export->getCell("6", $Row), "PLZ");
            $export->setValue($export->getCell("7", $Row), "Stadt");
            $export->setValue($export->getCell("8", $Row), "Ortsteil");
            $export->setValue($export->getCell("9", $Row), "Telefon Festnetz");
            $export->setValue($export->getCell("10", $Row), "Telefon Mobil");
            $export->setValue($export->getCell("11", $Row), "E-mail");
            $export->setValue($export->getCell("12", $Row), "Geburtstag");
            $export->setValue($export->getCell("13", $Row), "Geburtsort");
            $export->setValue($export->getCell("14", $Row), "Geschlecht");
            $export->setValue($export->getCell("15", $Row), "Staatsangehörigkeit");
            $export->setValue($export->getCell("16", $Row), "Konfession");
            $export->setValue($export->getCell("17", $Row), "Mitarbeitsbereitschaft");
            $export->setValue($export->getCell("18", $Row), "Mitarbeitsbereitschaft - Tätigkeiten");
            $export->setValue($export->getCell("19", $Row), "Bemerkungen");

            $Row++;

            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['Number']);
                $export->setValue($export->getCell("1", $Row), $PersonData['Salutation']);
                $export->setValue($export->getCell("2", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("3", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("4", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("5", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("6", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("7", $Row), $PersonData['City']);
                $export->setValue($export->getCell("8", $Row), $PersonData['District']);
                $export->setValue($export->getCell("9", $Row), $PersonData['PhoneNumber']);
                $export->setValue($export->getCell("10", $Row), $PersonData['MobilPhoneNumber']);
                $export->setValue($export->getCell("11", $Row), $PersonData['Mail']);
                $export->setValue($export->getCell("12", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("13", $Row), $PersonData['BirthPlace']);
                $export->setValue($export->getCell("14", $Row), $PersonData['Gender']);
                $export->setValue($export->getCell("15", $Row), $PersonData['Nationality']);
                $export->setValue($export->getCell("16", $Row), $PersonData['Religion']);
                $export->setValue($export->getCell("17", $Row), $PersonData['ParticipationWillingness']);
                $export->setValue($export->getCell("18", $Row), $PersonData['ParticipationActivities']);
                $export->setValue($export->getCell("19", $Row), $PersonData['RemarkExcel']);
                $export->setStyle($export->getCell("19", $Row))->setWrapText();

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

    /**
     * @return array
     */
    public function createInterestedPersonList()
    {

        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Interessent'));
        $TableContent = array();
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Phone'] = '';
                $Item['PhoneGuardian'] = '';
                $Item['TypeOptionA'] = $Item['TypeOptionB'] = $Item['DivisionLevel'] = $Item['RegistrationDate'] = '';
                $Item['SchoolYear'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = $Item['Denomination'] = $Item['Nationality'] = '';
                $Item['Siblings'] = array();
                $Item['FatherSalutation'] = $Item['FatherLastName'] = $Item['FatherFirstName'] = $Item['Father'] = '';
                $Item['MotherSalutation'] = $Item['MotherLastName'] = $Item['MotherFirstName'] = $Item['Mother'] = '';

                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }

                $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                if ($tblProspect) {
                    $tblProspectReservation = $tblProspect->getTblProspectReservation();
                    if ($tblProspectReservation) {
                        $Item['SchoolYear'] = $tblProspectReservation->getReservationYear();
                        if ($tblProspectReservation->getServiceTblTypeOptionA()) {
                            $Item['TypeOptionA'] = $tblProspectReservation->getServiceTblTypeOptionA()->getName();
                        }
                        if ($tblProspectReservation->getServiceTblTypeOptionB()) {
                            $Item['TypeOptionB'] = $tblProspectReservation->getServiceTblTypeOptionB()->getName();
                        }
                        if ($tblProspectReservation->getReservationDivision()) {
                            $Item['DivisionLevel'] = $tblProspectReservation->getReservationDivision();
                        }
                    }
                    $tblProspectAppointment = $tblProspect->getTblProspectAppointment();
                    if ($tblProspectAppointment) {
                        $Item['RegistrationDate'] = $tblProspectAppointment->getReservationDate();
                    }
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Denomination'] = $common->getTblCommonInformation()->getDenomination();
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                    $Item['Nationality'] = $common->getTblCommonInformation()->getNationality();
                }

                $relationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if (!empty($relationshipList)) {
                    /** @var \SPHERE\Application\People\Relationship\Service\Entity\TblToPerson $relationship */
                    foreach ($relationshipList as $relationship) {
                        if ($relationship->getServiceTblPersonFrom() && $relationship->getServiceTblPersonTo()
                            && $relationship->getTblType()->getName() == 'Geschwisterkind'
                        ) {
                            if ($relationship->getServiceTblPersonFrom()->getId() == $tblPerson->getId()) {
                                $Item['Siblings'][] = $relationship->getServiceTblPersonTo()->getFullName();
                            } else {
                                $Item['Siblings'][] = $relationship->getServiceTblPersonFrom()->getFullName();
                            }
                        }
                    }
                    if (!empty($Item['Siblings'])) {
                        $Item['Siblings'] = implode(', ', $Item['Siblings']);
                    }
                }
                if (empty($Item['Siblings'])) {
                    $Item['Siblings'] = '';
                }

                // get PhoneNumber by Prospect
                $tblToPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($tblToPhoneList) {
                    foreach ($tblToPhoneList as $tblToPhone) {
                        if (($tblPhone = $tblToPhone->getTblPhone())) {
                            if ($Item['Phone'] == '') {
                                $Item['Phone'] = $tblPerson->getFirstName() . ' ' . $tblPerson->getLastName() . ' (' . $tblPhone->getNumber() . ' ' .
                                    // modify TypeShort
                                    str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                            } else {
                                $Item['Phone'] .= ', ' . $tblPhone->getNumber() . ' ' .
                                    // modify TypeShort
                                    str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                            }
                        }
                    }
                    if ($Item['Phone'] != '') {
                        $Item['Phone'] .= ')';
                    }
                }

                $father = null;
                $mother = null;
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getServiceTblPersonFrom() && $guardian->getTblType()->getId() == 1) {
                            // get PhoneNumber by Guardian
                            $tblPersonGuardian = $guardian->getServiceTblPersonFrom();
                            if ($tblPersonGuardian) {
                                $tblToPhoneList = Phone::useService()->getPhoneAllByPerson($tblPersonGuardian);
                                if ($tblToPhoneList) {
                                    foreach ($tblToPhoneList as $tblToPhone) {
                                        if (($tblPhone = $tblToPhone->getTblPhone())) {
                                            if (!isset($Item['PhoneGuardian'][$tblPersonGuardian->getId()])) {
                                                $Item['PhoneGuardian'][$tblPersonGuardian->getId()] =
                                                    $tblPersonGuardian->getFirstName() . ' ' . $tblPersonGuardian->getLastName() .
                                                    ' (' . $tblPhone->getNumber() . ' ' .
                                                    // modify TypeShort
                                                    str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                                            } else {
                                                $Item['PhoneGuardian'][$tblPersonGuardian->getId()] .= ', ' . $tblPhone->getNumber() . ' ' .
                                                    // modify TypeShort
                                                    str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                                            }
                                        }
                                    }
                                }
                                if (isset($Item['PhoneGuardian'][$tblPersonGuardian->getId()])) {
                                    $Item['PhoneGuardian'][$tblPersonGuardian->getId()] .= ')';
                                }
                            }

                            if (($salutation = $guardian->getServiceTblPersonFrom()->getTblSalutation())) {
                                if ($salutation->getId() == 1) {
                                    $father = $guardian->getServiceTblPersonFrom();
                                } elseif ($salutation->getId() == 2) {
                                    $mother = $guardian->getServiceTblPersonFrom();
                                }
                            } else {
                                if ($father === null) {
                                    $father = $guardian->getServiceTblPersonFrom();
                                } else {
                                    $mother = $guardian->getServiceTblPersonFrom();
                                }
                            }
                        }
                    }
                }

                if (!empty($Item['PhoneGuardian'])) {
                    $Item['PhoneGuardian'] = implode('; ', $Item['PhoneGuardian']);
                }

                if ($father !== null) {
                    $Item['FatherSalutation'] = $father->getSalutation();
                    $Item['FatherLastName'] = $father->getLastName();
                    $Item['FatherFirstName'] = $father->getFirstSecondName();
                    $Item['Father'] = $father->getFullName();
                }
                if ($mother !== null) {
                    $Item['MotherSalutation'] = $mother->getSalutation();
                    $Item['MotherLastName'] = $mother->getLastName();
                    $Item['MotherFirstName'] = $mother->getFirstSecondName();
                    $Item['Mother'] = $mother->getFullName();
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param $PersonList
     * @param $tblPersonList
     *
     * @return bool|FilePointer
     */
    public function createInterestedPersonListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anmeldedatum");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Name");
            $export->setValue($export->getCell("3", "0"), "Schuljahr");
            $export->setValue($export->getCell("4", "0"), "Klassenstufe");
            $export->setValue($export->getCell("5", "0"), "Schulart 1");
            $export->setValue($export->getCell("6", "0"), "Schulart 2");
            $export->setValue($export->getCell("7", "0"), "Straße");
            $export->setValue($export->getCell("8", "0"), "Hausnummer");
            $export->setValue($export->getCell("9", "0"), "PLZ");
            $export->setValue($export->getCell("10", "0"), "Ort");
            $export->setValue($export->getCell("11", "0"), "Ortsteil");
            $export->setValue($export->getCell("12", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("13", "0"), "Geburtsort");
            $export->setValue($export->getCell("14", "0"), "Staatsangeh.");
            $export->setValue($export->getCell("15", "0"), "Bekenntnis");
            $export->setValue($export->getCell("16", "0"), "Geschwister");
            $export->setValue($export->getCell("17", "0"), "Anrede Sorgeberechtigter 1");
            $export->setValue($export->getCell("18", "0"), "Name Sorgeberechtigter 1");
            $export->setValue($export->getCell("19", "0"), "Vorname Sorgeberechtigter 1");
            $export->setValue($export->getCell("20", "0"), "Anrede Sorgeberechtigter 2");
            $export->setValue($export->getCell("21", "0"), "Name Sorgeberechtigter 2");
            $export->setValue($export->getCell("22", "0"), "Vorname Sorgeberechtigter 2");
            $export->setValue($export->getCell("23", "0"), "Telefon Interessent");
            $export->setValue($export->getCell("24", "0"), "Telefon Sorgeberechtigte");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['RegistrationDate']);
                $export->setValue($export->getCell("1", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("3", $Row), $PersonData['SchoolYear']);
                $export->setValue($export->getCell("4", $Row), $PersonData['DivisionLevel']);
                $export->setValue($export->getCell("5", $Row), $PersonData['TypeOptionA']);
                $export->setValue($export->getCell("6", $Row), $PersonData['TypeOptionB']);
                $export->setValue($export->getCell("7", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("8", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("9", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("10", $Row), $PersonData['City']);
                $export->setValue($export->getCell("11", $Row), $PersonData['District']);
                $export->setValue($export->getCell("12", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("13", $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell("14", $Row), $PersonData['Nationality']);
                $export->setValue($export->getCell("15", $Row), $PersonData['Denomination']);
                $export->setValue($export->getCell("16", $Row), $PersonData['Siblings']);
                $export->setValue($export->getCell("17", $Row), $PersonData['FatherSalutation']);
                $export->setValue($export->getCell("18", $Row), $PersonData['FatherLastName']);
                $export->setValue($export->getCell("19", $Row), $PersonData['FatherFirstName']);
                $export->setValue($export->getCell("20", $Row), $PersonData['MotherSalutation']);
                $export->setValue($export->getCell("21", $Row), $PersonData['MotherLastName']);
                $export->setValue($export->getCell("22", $Row), $PersonData['MotherFirstName']);
                $export->setValue($export->getCell("23", $Row), $PersonData['Phone']);
                $export->setValue($export->getCell("24", $Row), $PersonData['PhoneGuardian']);

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
    public function createElectiveClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();
        if (!empty($tblPersonList)) {
            $count = 1;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $tblDivision, &$count) {

                $Item['Number'] = $count++;
                $Item['Name'] = '';
                $Item['Birthday'] = '';
                $Item['Education'] = '';
                $Item['ForeignLanguage1'] = '';
                $Item['ForeignLanguage2'] = '';
                $Item['ForeignLanguage3'] = '';
                $Item['Profile'] = '';
                $Item['Orientation'] = '';
                $Item['Religion'] = '';
                $Item['Elective'] = '';
                $Item['ExcelElective'] = '';

                $Item['Name'] = $tblPerson->getLastFirstName();
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $Item['Birthday'] = $tblCommon->getTblCommonBirthDates()->getBirthday();
                }

                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                // NK/Profil
                if ($tblStudent) {
                    for ($i = 1; $i <= 3; $i++) {
                        $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                        $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                        $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking(
                            $tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking);
                        if ($tblPerson->getId() == 15) {
                            echo new Code(print_r($tblStudentSubject, true));
                        }

                        if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject()) && ($tblDivisionLevel = $tblDivision->getTblLevel())) {

                            $Item['ForeignLanguage'.$i] = $tblSubject->getAcronym();

                            if (($tblLevelFrom = $tblStudentSubject->getServiceTblLevelFrom())
                                && ($LevelFrom = Division::useService()->getLevelById($tblLevelFrom->getId())->getName())
                                && (is_numeric($LevelFrom)) && (is_numeric($tblDivisionLevel->getName()))) {
                                if ($tblDivisionLevel->getName() < $LevelFrom) {
                                    $Item['ForeignLanguage'.$i] = '';
                                }
                            }
                            if (($tblLevelTill = $tblStudentSubject->getServiceTblLevelTill()) &&
                                ($LevelTill = Division::useService()->getLevelById($tblLevelTill->getId())->getName())
                                && (is_numeric($LevelTill)) && (is_numeric($tblDivisionLevel->getName()))) {
                                if ($tblDivisionLevel->getName() > $LevelTill) {
                                    $Item['ForeignLanguage'.$i] = '';
                                }
                            }

                            /* Use the following block to show the starting/ending division foreach foreign language */

//                            if (($LevelFrom = Division::useService()->getLevelById($tblStudentSubject->getServiceTblLevelFrom()))
//                                && ($LevelTill = Division::useService()->getLevelById($tblStudentSubject->getServiceTblLevelTill()))) {
//                                /** @var TblLevel $LevelFrom, $LevelTill */
//                                $Item['ForeignLanguage'.$i] .= ' (von Klasse ' . $LevelFrom->getName() . ' bis ' . $LevelTill->getName() . ')';
//                            } elseif (($LevelFrom = Division::useService()->getLevelById($tblStudentSubject->getServiceTblLevelFrom()))) {
//                            $Item['ForeignLanguage'.$i] .= ' (seit Klasse ' . $LevelFrom->getName() . ')';
//                            } elseif (($LevelTill = Division::useService()->getLevelById($tblStudentSubject->getServiceTblLevelTill()))) {
//                                $Item['ForeignLanguage'.$i] .= ' (bis Klasse ' . $LevelTill->getName() . ')';
//                            }

                        }
                    }
                    if ($tblPerson->getId() == 15) {
                        exit;
                    }

                    // Profil
                    $tblStudentProfile = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE')
                    );
                    if ($tblStudentProfile && ($tblSubject = $tblStudentProfile[0]->getServiceTblSubject())) {
                        $Item['Profile'] = $tblSubject->getAcronym();
                    }
                    // Neigungskurs
                    $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION')
                    );
                    if ($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                        $Item['Orientation'] = $tblSubject->getAcronym();
                    }
                    // Religion
                    $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION')
                    );
                    if ($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                        $Item['Religion'] = $tblSubject->getAcronym();
                    }

                    // Bildungsgang
                    $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                    if ($tblTransferType) {
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                            $tblTransferType);
                        if ($tblStudentTransfer) {
                            $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                            if ($tblCourse) {
                                if ($tblCourse->getName() == 'Gymnasium') {
                                    $Item['Education'] = 'GY';
                                } elseif ($tblCourse->getName() == 'Hauptschule') {
                                    $Item['Education'] = 'HS';
                                } elseif ($tblCourse->getName() == 'Realschule') {
                                    $Item['Education'] = 'RS';
                                } else {
                                    $Item['Education'] = $tblCourse->getName();
                                }
                            }
                        }
                    }

                    // Wahlfach
                    $tblStudentElectiveList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent,
                        Student::useService()->getStudentSubjectTypeByIdentifier('ELECTIVE')
                    );
                    $ElectiveList = array();
                    if ($tblStudentElectiveList) {
                        foreach ($tblStudentElectiveList as $tblStudentElective) {
                            if ($tblStudentElective->getServiceTblSubject()) {
                                $tblSubjectRanking = $tblStudentElective->getTblStudentSubjectRanking();
                                if ($tblSubjectRanking) {
                                    $ElectiveList[$tblStudentElective->getTblStudentSubjectRanking()->getIdentifier()] =
                                        $tblStudentElective->getServiceTblSubject()->getAcronym();
                                } else {
                                    $ElectiveList[] =
                                        $tblStudentElective->getServiceTblSubject()->getAcronym();
                                }
                            }
                        }
                        if (!empty($ElectiveList)) {
                            ksort($ElectiveList);
                        }
                        if (!empty($ElectiveList)) {
                            $Item['Elective'] = implode('<br/>', $ElectiveList);
                            foreach ($ElectiveList as $Elective) {
                                $Item['ExcelElective'][] = $Elective;
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
     * @param       $DivisionId
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createElectiveClassListExcel($PersonList, $tblPersonList, $DivisionId)
    {

        // get PersonList sorted by GradeBook
        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $custodyList = array();
            if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
                $tblDivisionCustodyList = Division::useService()->getCustodyAllByDivision($tblDivision);
                if ($tblDivisionCustodyList) {
                    foreach ($tblDivisionCustodyList as $tblPerson) {
                        $custodyList[] = trim($tblPerson->getSalutation().' '.$tblPerson->getLastName());
                    }
                }

                $teacherList = array();
                $tblDivisionTeacherAll = Division::useService()->getTeacherAllByDivision($tblDivision);
                if ($tblDivisionTeacherAll) {
                    foreach ($tblDivisionTeacherAll as $tblPerson) {
                        $teacherList[] = trim($tblPerson->getSalutation().' '.$tblPerson->getLastName());
                    }
                }

                $export->setStyle($export->getCell(0, 0), $export->getCell(7, 0))->setFontBold();
                $export->setValue($export->getCell(0, 0),
                    "Klasse ".$tblDivision->getDisplayName().(empty($teacherList) ? '' : ' '.implode(', ',
                            $teacherList)));
            }

            // Header
            $export->setValue($export->getCell(0, 1), "Name");
            $export->setValue($export->getCell(1, 1), "Geb.-Datum");
            $export->setValue($export->getCell(2, 1), "Bildungsgang");
            $export->setValue($export->getCell(3, 1), "FS 1");
            $export->setValue($export->getCell(4, 1), "FS 2");
            $export->setValue($export->getCell(5, 1), "FS 3");
            $export->setValue($export->getCell(6, 1), "Profil");
            $export->setValue($export->getCell(7, 1), "Neigungskurs");
            $export->setValue($export->getCell(8, 1), "Religion");
            $export->setValue($export->getCell(9, 1), "Wahlfächer");
            // Header bold
            $export->setStyle($export->getCell(0, 1), $export->getCell(9, 1))->setFontBold();

            $Row = 2;
            foreach ($PersonList as $PersonData) {
                $ElectiveRow = $Row;

                $export->setValue($export->getCell(0, $Row), $PersonData['Name']);
                $export->setValue($export->getCell(1, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell(2, $Row), $PersonData['Education']);
                $export->setValue($export->getCell(3, $Row), $PersonData['ForeignLanguage1']);
                $export->setValue($export->getCell(4, $Row), $PersonData['ForeignLanguage2']);
                $export->setValue($export->getCell(5, $Row), $PersonData['ForeignLanguage3']);
                $export->setValue($export->getCell(6, $Row), $PersonData['Profile']);
                $export->setValue($export->getCell(7, $Row), $PersonData['Orientation']);
                $export->setValue($export->getCell(8, $Row), $PersonData['Religion']);

                if (isset($PersonData['ExcelElective']) && !empty($PersonData['ExcelElective'])) {
                    foreach ($PersonData['ExcelElective'] as $Elective) {
                        $export->setValue($export->getCell(9, $ElectiveRow), $Elective);
                        $ElectiveRow++;
                    }
                }

                $Row++;
                if ($ElectiveRow > $Row) {
                    $Row = $ElectiveRow;
                }

                // Gittertrennlinie
                $export->setStyle($export->getCell(0, $Row - 1), $export->getCell(9, $Row - 1))->setBorderBottom();
            }

            // Gitterlinien
            $export->setStyle($export->getCell(0, 1), $export->getCell(9, 1))->setBorderBottom();
            $export->setStyle($export->getCell(0, 1), $export->getCell(9, $Row - 1))->setBorderVertical();
            $export->setStyle($export->getCell(0, 1), $export->getCell(9, $Row - 1))->setBorderOutline();

            // Personenanzahl
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Weiblich:');
            $export->setValue($export->getCell(1, $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Männlich:');
            $export->setValue($export->getCell(1, $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Gesamt:');
            $export->setValue($export->getCell(1, $Row), count($tblPersonList));

            // Stand
            $Row += 2;
            $export->setValue($export->getCell(0, $Row), 'Stand: '.(new \DateTime())->format('d.m.Y'));

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $Row))->setColumnWidth(20);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setColumnWidth(12);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $Row))->setColumnWidth(5);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(5, 0), $export->getCell(6, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $Row))->setColumnWidth(8);
            $export->setStyle($export->getCell(7, 0), $export->getCell(7, $Row))->setColumnWidth(8);
            $export->setStyle($export->getCell(8, 0), $export->getCell(7, $Row))->setColumnWidth(8);
            $export->setStyle($export->getCell(9, 0), $export->getCell(7, $Row))->setColumnWidth(8);
//
//            // Schriftgröße
//            $export->setStyle($export->getCell(0, 0), $export->getCell(7, 0))->setFontSize(12)
//                ->setFontBold()
//                ->mergeCells();
//            $export->setStyle($export->getCell(0, 1), $export->getCell(7, $Row))->setFontSize(10);
//
//            // Spalten zentriert
//            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setAlignmentCenter();
//            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setAlignmentCenter();
//            $export->setStyle($export->getCell(5, 0), $export->getCell(5, $Row))->setAlignmentCenter();
//            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $Row))->setAlignmentCenter();
//            $export->setStyle($export->getCell(7, 0), $export->getCell(7, $Row))->setAlignmentCenter();

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }
}
