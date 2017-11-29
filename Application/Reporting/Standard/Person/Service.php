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
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson as TblToPersonPhone;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Search\Group\Group;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Text\Repository\Code;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Filter\Link\Pile;
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
     * @param null                $Select
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
                $Item['Gender'] = '';
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

                //Gender
                if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))
                && ($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
                && ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                    $Item['Gender'] = $tblCommonGender->getName();
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
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Vorname");
            $export->setValue($export->getCell("1", "0"), "Name");
            $export->setValue($export->getCell("2", "0"), "Geschlecht");
            $export->setValue($export->getCell("3", "0"), "Konfession");
            $export->setValue($export->getCell("4", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("5", "0"), "Geburtsort");
            $export->setValue($export->getCell("6", "0"), "Ortsteil");
            $export->setValue($export->getCell("7", "0"), "Straße");
            $export->setValue($export->getCell("8", "0"), "Hausnr.");
            $export->setValue($export->getCell("9", "0"), "PLZ");
            $export->setValue($export->getCell("10", "0"), "Ort");
            $export->setValue($export->getCell("11", "0"), "Telefon");
            $export->setValue($export->getCell("12", "0"), "E-Mail");

            $export->setStyle($export->getCell(0, 0), $export->getCell(11, 0))
                ->setFontBold();

            $Row = 0;

            foreach ($PersonList as $PersonData) {
                $Row++;
                $phoneRow = $mailRow = $Row;

                $export->setValue($export->getCell("0", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("1", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['Gender']);
                $export->setValue($export->getCell("3", $Row), $PersonData['Denomination']);
                $export->setValue($export->getCell("4", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("5", $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell("6", $Row), $PersonData['District']);
                $export->setValue($export->getCell("7", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("8", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("9", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("10", $Row), $PersonData['City']);
                //                $export->setValue($export->getCell("10", $Row), $PersonData['ExcelPhone']);
                //                $export->setValue($export->getCell("11", $Row), $PersonData['ExcelMail']);

                if (is_array($PersonData['ExcelPhone'])) {
                    foreach ($PersonData['ExcelPhone'] as $Phone) {
                        $export->setValue($export->getCell(11, $phoneRow++), $Phone);
                    }
                }
                if (is_array($PersonData['ExcelMail'])) {
                    foreach ($PersonData['ExcelMail'] as $Mail) {
                        $export->setValue($export->getCell(12, $mailRow++), $Mail);
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
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
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
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
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
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
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

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$All, $tblGroup) {

                $All++;
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Number'] = $All;
                $Item['Salutation'] = $tblPerson->getSalutation();
                $Item['Gender'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = '';
                $Item['BirthdaySort'] = '';
                $Item['BirthdayYearSort'] = '';
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
                        if ($Item['Birthday'] != '') {
                            $Year = substr($Item['Birthday'], 6, 4);
                            $Month = substr($Item['Birthday'], 3, 2);
                            $Day = substr($Item['Birthday'], 0, 2);
                            if (is_numeric($Month) && is_numeric($Day)) {
                                $Item['BirthdaySort'] = $Month * 100 + $Day;
                            }
                            if (is_numeric($Year) && is_numeric($Month) && is_numeric($Day)) {
                                $Item['BirthdayYearSort'] = ($Year * 10000) + ($Month * 100) + $Day;
                            }
                        }
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

                if ($tblGroup->getMetaTable() == 'PROSPECT') {
                    $Item['ReservationDate'] = '';
                    $Item['InterviewDate'] = '';
                    $Item['TrialDate'] = '';
                    $Item['ReservationYear'] = '';
                    $Item['ReservationDivision'] = '';
                    $Item['SchoolTypeA'] = '';
                    $Item['SchoolTypeB'] = '';
                    if (($tblProspect = Prospect::useService()->getProspectByPerson($tblPerson))) {
                        if (($tblProspectAppointment = $tblProspect->getTblProspectAppointment())) {
                            $Item['ReservationDate'] = $tblProspectAppointment->getReservationDate();
                            $Item['InterviewDate'] = $tblProspectAppointment->getInterviewDate();
                            $Item['TrialDate'] = $tblProspectAppointment->getTrialDate();
                        }
                        if (($tblProspectReservation = $tblProspect->getTblProspectReservation())) {
                            $Item['ReservationYear'] = $tblProspectReservation->getReservationYear();
                            $Item['ReservationDivision'] = $tblProspectReservation->getReservationDivision();
                            $Item['SchoolTypeA'] = ($tblProspectReservation->getServiceTblTypeOptionA() ? $tblProspectReservation->getServiceTblTypeOptionA()->getName() : '');
                            $Item['SchoolTypeB'] = ($tblProspectReservation->getServiceTblTypeOptionB() ? $tblProspectReservation->getServiceTblTypeOptionB()->getName() : '');
                        }
                    }
                }

                if ($tblGroup->getMetaTable() == 'STUDENT') {
                    $Item['Identifier'] = '';
                    $Item['School'] = '';
                    $Item['SchoolCourse'] = '';
                    $Item['SchoolType'] = '';
                    $Item['PictureSchoolWriting'] = '';
                    $Item['PicturePublication'] = '';
                    $Item['PictureWeb'] = '';
                    $Item['PictureFacebook'] = '';
                    $Item['PicturePrint'] = '';
                    $Item['PictureFilm'] = '';
                    $Item['PictureAdd'] = '';
                    $Item['NameSchoolWriting'] = '';
                    $Item['NamePublication'] = '';
                    $Item['NameWeb'] = '';
                    $Item['NameFacebook'] = '';
                    $Item['NamePrint'] = '';
                    $Item['NameFilm'] = '';
                    $Item['NameAdd'] = '';
                    if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                        $tblDivisionList = Student::useService()->getCurrentDivisionListByPerson($tblPerson);
                        if ($tblDivisionList) {
                            foreach ($tblDivisionList as $tblDivision) {
                                if ($tblDivision->getTblLevel() && $tblDivision->getTblLevel()->getName() != '') {
                                    $Item['SchoolType'] = $tblDivision->getTypeName();
                                }
                            }
                        }
                        $Item['Division'] = Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson);
                        $Item['Identifier'] = $tblStudent->getIdentifier();
                        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                        if (($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                            $tblStudentTransferType))) {
                            $Item['School'] = ($tblStudentTransfer->getServiceTblCompany()
                                ? $tblStudentTransfer->getServiceTblCompany()->getDisplayName()
                                : '');
                            $Item['SchoolCourse'] = (Student::useService()->getCourseByStudent($tblStudent)
                                ? Student::useService()->getCourseByStudent($tblStudent)->getName()
                                : '');
                        }
                        $tblAgreementList = Student::useService()->getStudentAgreementAllByStudent($tblStudent);
                        if ($tblAgreementList) {
                            $MarkValue = 'Ja';
                            foreach ($tblAgreementList as $tblAgreement) {
                                $tblAgreementType = $tblAgreement->getTblStudentAgreementType();
                                $CategoryString = $tblAgreementType->getTblStudentAgreementCategory()->getName();
                                switch ($tblAgreementType->getName()) {
                                    case 'in Schulschriften':
                                        ($CategoryString == 'Foto des Schülers'
                                            ? $Item['PictureSchoolWriting'] = $MarkValue
                                            : $Item['NameSchoolWriting'] = $MarkValue);
                                        break;
                                    case 'in Veröffentlichungen':
                                        ($CategoryString == 'Foto des Schülers'
                                            ? $Item['PicturePublication'] = $MarkValue
                                            : $Item['NamePublication'] = $MarkValue);
                                        break;
                                    case 'auf Internetpräsenz':
                                        ($CategoryString == 'Foto des Schülers'
                                            ? $Item['PictureWeb'] = $MarkValue
                                            : $Item['NameWeb'] = $MarkValue);
                                        break;
                                    case 'auf Facebookseite':
                                        ($CategoryString == 'Foto des Schülers'
                                            ? $Item['PictureFacebook'] = $MarkValue
                                            : $Item['NameFacebook'] = $MarkValue);
                                        break;
                                    case 'für Druckpresse':
                                        ($CategoryString == 'Foto des Schülers'
                                            ? $Item['PicturePrint'] = $MarkValue
                                            : $Item['NamePrint'] = $MarkValue);
                                        break;
                                    case 'durch Ton/Video/Film':
                                        ($CategoryString == 'Foto des Schülers'
                                            ? $Item['PictureFilm'] = $MarkValue
                                            : $Item['NameFilm'] = $MarkValue);
                                        break;
                                    case 'für Werbung in eigener Sache':
                                        ($CategoryString == 'Foto des Schülers'
                                            ? $Item['PictureAdd'] = $MarkValue
                                            : $Item['NameAdd'] = $MarkValue);
                                        break;
                                }
                            }
                        }
                    }
                }
                if ($tblGroup->getMetaTable() == 'CUSTODY') {
                    $Item['Occupation'] = '';
                    $Item['Employment'] = '';
                    $Item['Remark'] = '';
                    if (($tblCustody = Custody::useService()->getCustodyByPerson($tblPerson))) {
                        $Item['Occupation'] = $tblCustody->getOccupation();
                        $Item['Employment'] = $tblCustody->getEmployment();
                        $Item['Remark'] = $tblCustody->getRemark();
                    }
                }
                if ($tblGroup->getMetaTable() == 'TEACHER') {
                    $Item['TeacherAcronym'] = '';
                    if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))) {
                        $Item['TeacherAcronym'] = $tblTeacher->getAcronym();
                    }
                }
                if ($tblGroup->getMetaTable() == 'CLUB') {
                    $Item['ClubIdentifier'] = '';
                    $Item['EntryDate'] = '';
                    $Item['ExitDate'] = '';
                    $Item['ClubRemark'] = '';
                    if (($tblClub = Club::useService()->getClubByPerson($tblPerson))) {
                        $Item['ClubIdentifier'] = $tblClub->getIdentifier();
                        $Item['EntryDate'] = $tblClub->getEntryDate();
                        $Item['ExitDate'] = $tblClub->getExitDate();
                        $Item['ClubRemark'] = $tblClub->getRemark();
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
     * @param int   $GroupId
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createGroupListExcel($PersonList, $tblPersonList, $GroupId)
    {

        $tblGroup = Group::useService()->getGroupById($GroupId);
        if (!empty($PersonList) && $tblGroup) {
            $ColumnStandard = array(
                'Number'                   => 'lfd. Nr.',
                'Salutation'               => 'Anrede',
                'FirstName'                => 'Vorname',
                'LastName'                 => 'Nachname',
                'StreetName'               => 'Straße',
                'StreetNumber'             => 'Str.Nr',
                'Code'                     => 'PLZ',
                'City'                     => 'Ort',
                'District'                 => 'Ortsteil',
                'PhoneNumber'              => 'Telefon Festnetz',
                'MobilPhoneNumber'         => 'Telefon Mobil',
                'Mail'                     => 'E-mail',
                'Birthday'                 => 'Geburtstag',
                'BirthdaySort'             => 'Sortierung Geburtstag',
                'BirthdayYearSort'         => 'Sortierung Geburtsdatum',
                'BirthPlace'               => 'Geburtsort',
                'Gender'                   => 'Geschlecht',
                'Nationality'              => 'Staatsangehörigkeit',
                'Religion'                 => 'Konfession',
                'ParticipationWillingness' => 'Mitarbeitsbereitschaft',
                'ParticipationActivities'  => 'Mitarbeitsbereitschaft - Tätigkeiten',
                'RemarkExcel'              => 'Bemerkungen'
            );
            $ColumnCustom = array();

            if ($tblGroup->getMetaTable() == 'PROSPECT') {
                $ColumnCustom = array(
                    'ReservationDate'     => 'Eingangsdatum',
                    'InterviewDate'       => 'Aufnahmegespräch',
                    'TrialDate'           => 'Schnuppertag',
                    'ReservationYear'     => 'Voranmeldung Schuljahr',
                    'ReservationDivision' => 'Voranmeldung Stufe',
                    'SchoolTypeA'         => 'Voranmeldung Schulart A',
                    'SchoolTypeB'         => 'Voranmeldung Schulart B'
                );
            }
            if ($tblGroup->getMetaTable() == 'STUDENT') {
                $ColumnCustom = array(
                    'Identifier'           => 'Schülernummer',
                    'School'               => 'Schule',
                    'SchoolType'           => 'Schulart',
                    'SchoolCourse'         => 'Bildungsgang',
                    'Division'             => 'aktuelle Klasse',
                    'PictureSchoolWriting' => 'Einverständnis Foto Schulschriften',
                    'PicturePublication'   => 'Einverständnis Foto Veröffentlichungen',
                    'PictureWeb'           => 'Einverständnis Foto Internetpräsenz',
                    'PictureFacebook'      => 'Einverständnis Foto Facebookseite',
                    'PicturePrint'         => 'Einverständnis Foto Druckpresse',
                    'PictureFilm'          => 'Einverständnis Foto Ton/Video/Film',
                    'PictureAdd'           => 'Einverständnis Foto Werbung in eigener Sache',
                    'NameSchoolWriting'    => 'Einverständnis Name Schulschriften',
                    'NamePublication'      => 'Einverständnis Name Veröffentlichungen',
                    'NameWeb'              => 'Einverständnis Name Internetpräsenz',
                    'NameFacebook'         => 'Einverständnis Name Facebookseite',
                    'NamePrint'            => 'Einverständnis Name Druckpresse',
                    'NameFilm'             => 'Einverständnis Name Ton/Video/Film',
                    'NameAdd'              => 'Einverständnis Name Werbung in eigener Sache',
                );
            }
            if ($tblGroup->getMetaTable() == 'CUSTODY') {
                $ColumnCustom = array(
                    'Occupation' => 'Beruf',
                    'Employment' => 'Arbeitsstelle',
                    'Remark'     => 'Bemerkung Sorgeberechtigter',
                );
            }
            if ($tblGroup->getMetaTable() == 'TEACHER') {
                $ColumnCustom = array(
                    'TeacherAcronym' => 'Lehrerkürzel',
                );
            }
            if ($tblGroup->getMetaTable() == 'CLUB') {
                $ColumnCustom = array(
                    'ClubIdentifier' => 'Mitgliedsnummer',
                    'EntryDate'      => 'Eintrittsdatum',
                    'ExitDate'       => 'Austrittsdatum',
                    'ClubRemark'     => 'Bemerkung Vereinsmitglied',
                );
            }


            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $Row = 0;
//            $export->setStyle($export->getCell(0, 0), $export->getCell(12, 0))
//                ->mergeCells()->setAlignmentCenter();
            $export->setValue($export->getCell(0, 0), 'Gruppenliste ' . $tblGroup->getName());

            if (!empty($tblGroup->getDescription())) {
                $Row++;
//                $export->setStyle($export->getCell(0, 1), $export->getCell(12, 1))
//                    ->mergeCells()->setAlignmentCenter();
                $export->setValue($export->getCell(0, 1), $tblGroup->getDescription());
            }

            if (!empty($tblGroup->getRemark())) {
                $Row++;
//                $export->setStyle($export->getCell(0, 2), $export->getCell(12, 2))
//                    ->mergeCells()->setAlignmentCenter();
                $export->setValue($export->getCell(0, 2), $tblGroup->getRemark());
            }

            $Row += 2;

            $Column = 0;
            foreach ($ColumnStandard as $Key => $Value) {
                $export->setValue($export->getCell($Column, $Row), $Value);
                $Column++;
            }
            foreach ($ColumnCustom as $Key => $Value) {
                $export->setValue($export->getCell($Column, $Row), $Value);
//                $export->setStyle($export->getCell($Column, $Row))->setWrapText();
                $Column++;
            }

            $Row++;

            foreach ($PersonList as $PersonData) {
                $Column = 0;
                foreach ($ColumnStandard as $Key => $Value) {
                    if (isset($PersonData[$Key])) {
                        // handle value as numeric
                        if ($Key == 'Number'
                            || $Key == 'BirthdaySort'
                            || $Key == 'BirthdayYearSort') {
                            // don't display if empty
                            if ($PersonData[$Key] != '') {
                                $export->setValue($export->getCell($Column, $Row), $PersonData[$Key],
                                    \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                            }
                        } else {
                            $export->setValue($export->getCell($Column, $Row), $PersonData[$Key]);
                            if ($Key == 'RemarkExcel') {
                                $export->setStyle($export->getCell($Column, $Row))->setWrapText()
                                    ->setAlignmentMiddle();
                            }
                        }
                    }
                    $Column++;
                }
                if (!empty($ColumnCustom)) {
                    foreach ($ColumnCustom as $Key => $Value) {
                        if (isset($PersonData[$Key])) {
                            $export->setValue($export->getCell($Column, $Row), $PersonData[$Key]);
                        }
                        $Column++;
                    }
                }

                $Row++;
            }

            $Row++;
            $export->setValue($export->getCell("0", $Row),
                'Weiblich: '.Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row),
                'Männlich: '.Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt: '.count($tblPersonList));;

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

        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('PROSPECT'));
        $TableContent = array();
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Phone'] = $Item['PhoneSimple'] = '';
                $Item['PhoneGuardian'] = $Item['PhoneGuardianSimple'] = '';
                $Item['TypeOptionA'] = $Item['TypeOptionB'] = '';
                $Item['DivisionLevel'] = '';
                $Item['RegistrationDate'] = '';
                $Item['InterviewDate'] = '';
                $Item['TrialDate'] = '';
                $Item['SchoolYear'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = $Item['Denomination'] = $Item['Nationality'] = '';
                $Item['Siblings'] = array();
                $Item['FatherSalutation'] = $Item['FatherLastName'] = $Item['FatherFirstName'] = $Item['Father'] = '';
                $Item['MotherSalutation'] = $Item['MotherLastName'] = $Item['MotherFirstName'] = $Item['Mother'] = '';
                $Item['Remark'] = $Item['RemarkExcel'] = '';
                $Item['MailGuardian'] = $Item['ExcelMailGuardian'] = $Item['ExcelMailGuardianSimple'] = '';

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
                        $Item['InterviewDate'] = $tblProspectAppointment->getInterviewDate();
                        $Item['TrialDate'] = $tblProspectAppointment->getTrialDate();
                    }

                    $Item['Remark'] = nl2br($tblProspect->getRemark());
                    $Item['RemarkExcel'] = $tblProspect->getRemark();
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

                $PhoneListSimple = array();
                // get PhoneNumber by Prospect
                $tblToPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($tblToPhoneList) {
                    foreach ($tblToPhoneList as $tblToPhone) {
                        if (($tblPhone = $tblToPhone->getTblPhone())) {
                            $PhoneListSimple[$tblPhone->getId()] = $tblPhone->getNumber();
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

                if (!empty($PhoneListSimple)) {
                    $Item['PhoneSimple'] = implode('; ', $PhoneListSimple);
                }

                $father = null;
                $mother = null;
                $PhoneGuardianListSimple = array();
                $MailListSimple = array();
                $tblMailList = array();
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
                                            $PhoneGuardianListSimple[$tblPhone->getId()] = $tblPhone->getNumber();
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

                            //Mail Guardian
                            $tblToPersonMailList = Mail::useService()->getMailAllByPerson($tblPersonGuardian);
                            if ($tblToPersonMailList) {
                                foreach ($tblToPersonMailList as $tblToPersonMail) {
                                    $tblMail = $tblToPersonMail->getTblMail();
                                    if ($tblMail) {
                                        $MailListSimple[$tblMail->getId()] = $tblMail->getAddress();
                                        if (isset($tblMailList[$tblPersonGuardian->getId()])) {
                                            $tblMailList[$tblPersonGuardian->getId()] = $tblMailList[$tblPersonGuardian->getId()].', '
                                                .$tblMail->getAddress();
                                        } else {
                                            $tblMailList[$tblPersonGuardian->getId()] = $tblPersonGuardian->getFirstName().' '.
                                                $tblPersonGuardian->getLastName().' ('
                                                .$tblMail->getAddress();
                                        }
                                    }
                                }
                                if (isset($tblMailList[$tblPersonGuardian->getId()])) {
                                    $tblMailList[$tblPersonGuardian->getId()] .= ')';
                                }
                            }
                        }
                    }
                }

                if (!empty($Item['PhoneGuardian'])) {
                    $Item['PhoneGuardian'] = implode('; ', $Item['PhoneGuardian']);
                }
                if (!empty($PhoneGuardianListSimple)) {
                    $Item['PhoneGuardianSimple'] = implode('; ', $PhoneGuardianListSimple);
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

                // Insert MailList
                if (!empty($tblMailList)) {
                    $Item['MailGuardian'] .= implode('<br>', $tblMailList);
                    $Item['ExcelMailGuardian'] = implode('; ', $tblMailList);
                }
                // Insert MailListSimple
                if (!empty($MailListSimple)) {
                    $Item['ExcelMailGuardianSimple'] = implode('; ', $MailListSimple);
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
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createInterestedPersonListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell(0, 0), "Anmeldedatum");
            $export->setValue($export->getCell(1, 0), "Aufnahmegespräch");
            $export->setValue($export->getCell(2, 0), "Schnuppertag");
            $export->setValue($export->getCell(3, 0), "Vorname");
            $export->setValue($export->getCell(4, 0), "Name");
            $export->setValue($export->getCell(5, 0), "Schuljahr");
            $export->setValue($export->getCell(6, 0), "Klassenstufe");
            $export->setValue($export->getCell(7, 0), "Schulart 1");
            $export->setValue($export->getCell(8, 0), "Schulart 2");
            $export->setValue($export->getCell(9, 0), "Straße");
            $export->setValue($export->getCell(10, 0), "Hausnummer");
            $export->setValue($export->getCell(11, 0), "PLZ");
            $export->setValue($export->getCell(12, 0), "Ort");
            $export->setValue($export->getCell(13, 0), "Ortsteil");
            $export->setValue($export->getCell(14, 0), "Geburtsdatum");
            $export->setValue($export->getCell(15, 0), "Geburtsort");
            $export->setValue($export->getCell(16, 0), "Staatsangeh.");
            $export->setValue($export->getCell(17, 0), "Bekenntnis");
            $export->setValue($export->getCell(18, 0), "Geschwister");
            $export->setValue($export->getCell(19, 0), "Anrede Sorgeberechtigter 1");
            $export->setValue($export->getCell(20, 0), "Name Sorgeberechtigter 1");
            $export->setValue($export->getCell(21, 0), "Vorname Sorgeberechtigter 1");
            $export->setValue($export->getCell(22, 0), "Anrede Sorgeberechtigter 2");
            $export->setValue($export->getCell(23, 0), "Name Sorgeberechtigter 2");
            $export->setValue($export->getCell(24, 0), "Vorname Sorgeberechtigter 2");
            $export->setValue($export->getCell(25, 0), "Telefon Interessent");
            $export->setValue($export->getCell(26, 0), "Telefon Interessent Kurz");
            $export->setValue($export->getCell(27, 0), "Telefon Sorgeberechtigte");
            $export->setValue($export->getCell(28, 0), "Telefon Sorgeberechtigte Kurz");
            $export->setValue($export->getCell(29, 0), "E-Mail Sorgeberechtigte");
            $export->setValue($export->getCell(30, 0), "E-Mail Sorgeberechtigte Kurz");
            $export->setValue($export->getCell(31, 0), "Bemerkung");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell(0, $Row), $PersonData['RegistrationDate']);
                $export->setValue($export->getCell(1, $Row), $PersonData['InterviewDate']);
                $export->setValue($export->getCell(2, $Row), $PersonData['TrialDate']);
                $export->setValue($export->getCell(3, $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell(4, $Row), $PersonData['LastName']);
                $export->setValue($export->getCell(5, $Row), $PersonData['SchoolYear']);
                $export->setValue($export->getCell(6, $Row), $PersonData['DivisionLevel']);
                $export->setValue($export->getCell(7, $Row), $PersonData['TypeOptionA']);
                $export->setValue($export->getCell(8, $Row), $PersonData['TypeOptionB']);
                $export->setValue($export->getCell(9, $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell(10, $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell(11, $Row), $PersonData['Code']);
                $export->setValue($export->getCell(12, $Row), $PersonData['City']);
                $export->setValue($export->getCell(13, $Row), $PersonData['District']);
                $export->setValue($export->getCell(14, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell(15, $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell(16, $Row), $PersonData['Nationality']);
                $export->setValue($export->getCell(17, $Row), $PersonData['Denomination']);
                $export->setValue($export->getCell(18, $Row), $PersonData['Siblings']);
                $export->setValue($export->getCell(19, $Row), $PersonData['FatherSalutation']);
                $export->setValue($export->getCell(20, $Row), $PersonData['FatherLastName']);
                $export->setValue($export->getCell(21, $Row), $PersonData['FatherFirstName']);
                $export->setValue($export->getCell(22, $Row), $PersonData['MotherSalutation']);
                $export->setValue($export->getCell(23, $Row), $PersonData['MotherLastName']);
                $export->setValue($export->getCell(24, $Row), $PersonData['MotherFirstName']);
                $export->setValue($export->getCell(25, $Row), $PersonData['Phone']);
                $export->setValue($export->getCell(26, $Row), $PersonData['PhoneSimple']);
                $export->setValue($export->getCell(27, $Row), $PersonData['PhoneGuardian']);
                $export->setValue($export->getCell(28, $Row), $PersonData['PhoneGuardianSimple']);
                $export->setValue($export->getCell(29, $Row), $PersonData['ExcelMailGuardian']);
                $export->setValue($export->getCell(30, $Row), $PersonData['ExcelMailGuardianSimple']);
                $export->setValue($export->getCell(31, $Row), $PersonData['RemarkExcel']);

                // WrapText
                $export->setStyle($export->getCell(31, $Row))->setWrapText();
                $Row++;
            }

            $export->setStyle($export->getCell(31, 0))->setColumnWidth(50);

            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Weiblich:');
            $export->setValue($export->getCell(1, $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Männlich:');
            $export->setValue($export->getCell(1, $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Gesamt:');
            $export->setValue($export->getCell(1, $Row), count($tblPersonList));

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

                            $Item['ForeignLanguage' . $i] = $tblSubject->getAcronym();

                            if (($tblLevelFrom = $tblStudentSubject->getServiceTblLevelFrom())
                                && ($LevelFrom = Division::useService()->getLevelById($tblLevelFrom->getId())->getName())
                                && (is_numeric($LevelFrom)) && (is_numeric($tblDivisionLevel->getName()))) {
                                if ($tblDivisionLevel->getName() < $LevelFrom) {
                                    $Item['ForeignLanguage' . $i] = '';
                                }
                            }
                            if (($tblLevelTill = $tblStudentSubject->getServiceTblLevelTill()) &&
                                ($LevelTill = Division::useService()->getLevelById($tblLevelTill->getId())->getName())
                                && (is_numeric($LevelTill)) && (is_numeric($tblDivisionLevel->getName()))) {
                                if ($tblDivisionLevel->getName() > $LevelTill) {
                                    $Item['ForeignLanguage' . $i] = '';
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
                        $custodyList[] = trim($tblPerson->getSalutation() . ' ' . $tblPerson->getLastName());
                    }
                }

                $teacherList = array();
                $tblDivisionTeacherAll = Division::useService()->getTeacherAllByDivision($tblDivision);
                if ($tblDivisionTeacherAll) {
                    foreach ($tblDivisionTeacherAll as $tblPerson) {
                        $teacherList[] = trim($tblPerson->getSalutation() . ' ' . $tblPerson->getLastName());
                    }
                }

                $export->setStyle($export->getCell(0, 0), $export->getCell(7, 0))->setFontBold();
                $export->setValue($export->getCell(0, 0),
                    "Klasse " . $tblDivision->getDisplayName() . (empty($teacherList) ? '' : ' ' . implode(', ',
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
            $export->setValue($export->getCell(0, $Row), 'Stand: ' . (new \DateTime())->format('d.m.Y'));

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

    /**
     * @param $Person
     * @param $Year
     * @param $Division
     *
     * @return array
     */
    public function getStudentFilterResult($Person, $Year, $Division)
    {

        $Pile = new Pile(Pile::JOIN_TYPE_INNER);
        $Pile->addPile((new ViewPeopleGroupMember())->getViewService(), new ViewPeopleGroupMember(),
            null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
        );
        $Pile->addPile((new ViewPerson())->getViewService(), new ViewPerson(),
            ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
        );
        $Pile->addPile((new ViewDivisionStudent())->getViewService(), new ViewDivisionStudent(),
            ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, ViewDivisionStudent::TBL_DIVISION_TBL_YEAR
        );
        $Pile->addPile((new ViewYear())->getViewService(), new ViewYear(),
            ViewYear::TBL_YEAR_ID, ViewYear::TBL_YEAR_ID
        );

        $Result = array();

        if (isset($Year) && $Year['TblYear_Id'] != 0 && isset($Pile)) {
            array_walk($Year, function (&$Input) {

                if (!empty($Input)) {
                    $Input = explode(' ', $Input);
                    $Input = array_filter($Input);
                } else {
                    $Input = false;
                }
            });

            $FilterYear = array_filter($Year);
            if (isset($Person) && $Person) {
                array_walk($Person, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });

                $FilterPerson = array_filter($Person);
            } else {
                $FilterPerson = array();
            }
            if (isset($Division) && $Division) {
                array_walk($Division, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });

                $FilterDivision = array_filter($Division);
            } else {
                $FilterDivision = array();
            }

            $StudentGroup = Group::useService()->getGroupByMetaTable('STUDENT');
            $Result = $Pile->searchPile(array(
                0 => array(ViewPeopleGroupMember::TBL_GROUP_ID => array($StudentGroup->getId())),
                1 => $FilterPerson,
                2 => $FilterDivision,
                3 => $FilterYear
            ));
        }

        return $Result;
    }

    /**
     * @param array $Result
     *
     * @return array
     */
    public function getStudentTableContent($Result)
    {

        $SearchResult = array();
        if (!empty($Result)) {
            /**
             * @var int                                $Index
             * @var ViewPerson[]|ViewDivisionStudent[] $Row
             */
            foreach ($Result as $Index => $Row) {

                /** @var ViewPerson $DataPerson */
                $DataPerson = $Row[1]->__toArray();
                /** @var ViewDivisionStudent $DivisionStudent */
                $DivisionStudent = $Row[2]->__toArray();
                /** @var ViewYear $Year */
                $Year = $Row[3]->__toArray();

                $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($DataPerson['TblPerson_Id']);

                // ignor existing Accounts (By Person)
                if ($tblPerson) {
                    /** @noinspection PhpUndefinedFieldInspection */

                    $DataPerson['Division'] = '';
                    if (($tblDivision = Division::useService()->getDivisionById($DivisionStudent['TblDivision_Id']))) {
                        /** @var TblDivision $tblDivision */
                        $DataPerson['Division'] = $tblDivision->getDisplayName();
                    }

                    $DataPerson['StudentNumber'] = '';
                    if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                        $DataPerson['StudentNumber'] = $tblStudent->getIdentifier();
                    }

                    $DataPerson['FirstName'] = '';
                    $DataPerson['LastName'] = '';
                    $DataPerson['FirstName'] = $tblPerson->getFirstName();
                    if (!empty($tblPerson->getSecondName())) {
                        $DataPerson['FirstName'] .= ' ' . $tblPerson->getSecondName();
                    }
                    $DataPerson['LastName'] = $tblPerson->getLastName();

                    $DataPerson['Gender'] = '';
                    $DataPerson['Birthday'] = '';
                    $DataPerson['BirthPlace'] = '';
                    $DataPerson['Religion'] = '';
                    if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                        if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                            if (!empty($tblCommonBirthDates->getBirthday())) {
                                $DataPerson['Birthday'] = $tblCommonBirthDates->getBirthday();
                            }
                            if (!empty($tblCommonBirthDates->getBirthplace())) {
                                $DataPerson['BirthPlace'] = $tblCommonBirthDates->getBirthplace();
                            }
                            if (($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                                $DataPerson['Gender'] = $tblCommonGender->getName();
                            }
                        }
                        if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                            $DataPerson['Religion'] = $tblCommonInformation->getDenomination();
                        }
                    }

                    $DataPerson['Address'] = '';
                    $DataPerson['Street'] = '';
                    $DataPerson['HouseNumber'] = '';
                    $DataPerson['CityCode'] = '';
                    $DataPerson['City'] = '';
                    $DataPerson['District'] = '';
                    if (($tblAddress = Address::useService()->getAddressByPerson($tblPerson))) {
                        $DataPerson['Address'] = $tblAddress->getGuiString();
                        if (($tblCity = $tblAddress->getTblCity())) {
                            $DataPerson['Street'] = $tblAddress->getStreetName();
                            $DataPerson['HouseNumber'] = $tblAddress->getStreetNumber();
                            $DataPerson['CityCode'] = $tblCity->getCode();
                            $DataPerson['City'] = $tblCity->getName();
                            $DataPerson['District'] = $tblCity->getDisplayDistrict();
                        }
                    }

                    $DataPerson['Insurance'] = '';
                    if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                        if (($tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord())) {
                            $DataPerson['Insurance'] = $tblStudentMedicalRecord->getInsurance();
                        }
                    }

                    $DataPerson['PhoneFixedPrivate'] = '';
                    $DataPerson['PhoneFixedWork'] = '';
                    $DataPerson['PhoneFixedEmergency'] = '';
                    $DataPerson['PhoneMobilePrivate'] = '';
                    $DataPerson['PhoneMobileWork'] = '';
                    $DataPerson['PhoneMobileEmergency'] = '';
                    if (($tblPhoneAll = Phone::useService()->getPhoneAllByPerson($tblPerson))) {
                        foreach ($tblPhoneAll as $tblToPerson) {
                            /** @var TblToPerson $tblToPerson */
                            if (($tblPhoneType = $tblToPerson->getTblType())
                            && ($PhoneDescription = $tblPhoneType->getDescription())
                            && ($PhoneName = $tblPhoneType->getName())
                            && ($tblPhone = $tblToPerson->getTblPhone())) {
                                if ($PhoneDescription == 'Festnetz') {
                                    switch ($PhoneName) {
                                        case 'Privat':
                                            if (empty($DataPerson['PhoneFixedPrivate'])) {
                                                $DataPerson['PhoneFixedPrivate'] = $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            } else {
                                                $DataPerson['PhoneFixedPrivate'] .= ', ' . $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                        case 'Geschäftlich':
                                            if (empty($DataPerson['PhoneFixedWork'])) {
                                                $DataPerson['PhoneFixedWork'] = $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            } else {
                                                $DataPerson['PhoneFixedWork'] .= ', ' . $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                        case 'Notfall':
                                            if (empty($DataPerson['PhoneFixedEmergency'])) {
                                                $DataPerson['PhoneFixedEmergency'] = $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : 'TEst');
                                            } else {
                                                $DataPerson['PhoneFixedEmergency'] .= ', ' . $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                    }
                                } elseif ($PhoneDescription == 'Mobil') {
                                    switch ($PhoneName) {
                                        case 'Privat':
                                            if (empty($DataPerson['PhoneMobilePrivate'])) {
                                                $DataPerson['PhoneMobilePrivate'] = $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            } else {
                                                $DataPerson['PhoneMobilePrivate'] .= ', ' . $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                        case 'Geschäftlich':
                                            if (empty($DataPerson['PhoneMobileWork'])) {
                                                $DataPerson['PhoneMobileWork'] = $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            } else {
                                                $DataPerson['PhoneMobileWork'] .= ', ' . $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                        case 'Notfall':
                                            if (empty($DataPerson['PhoneMobileEmergency'])) {
                                                $DataPerson['PhoneMobileEmergency'] = $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            } else {
                                                $DataPerson['PhoneMobileEmergency'] .= ', ' . $tblPhone->getNumber()
                                                    . (!empty($tblToPerson->getRemark()) ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                    }
                                }
                            }
                        }
                    }

                    $DataPerson['Sibling_1'] = '';
                    $DataPerson['Sibling_2'] = '';
                    $DataPerson['Sibling_3'] = '';

                    $DataPerson['Custody_1_Salutation'] = '';
                    $DataPerson['Custody_1_Title'] = '';
                    $DataPerson['Custody_1_FirstName'] = '';
                    $DataPerson['Custody_1_LastName'] = '';
                    $DataPerson['Custody_1_Address'] = '';
                    $DataPerson['Custody_1_Street'] = '';
                    $DataPerson['Custody_1_HouseNumber'] = '';
                    $DataPerson['Custody_1_CityCode'] = '';
                    $DataPerson['Custody_1_City'] = '';
                    $DataPerson['Custody_1_District'] = '';
                    $DataPerson['Custody_1_PhoneFixedPrivate'] = '';
                    $DataPerson['Custody_1_PhoneFixedWork'] = '';
                    $DataPerson['Custody_1_PhoneFixedEmergency'] = '';
                    $DataPerson['Custody_1_PhoneMobilePrivate'] = '';
                    $DataPerson['Custody_1_PhoneMobileWork'] = '';
                    $DataPerson['Custody_1_PhoneMobileEmergency'] = '';
                    $DataPerson['Custody_1_Mail_Private'] = '';
                    $DataPerson['Custody_1_Mail_Work'] = '';

                    $DataPerson['Custody_2_Salutation'] = '';
                    $DataPerson['Custody_2_Title'] = '';
                    $DataPerson['Custody_2_FirstName'] = '';
                    $DataPerson['Custody_2_LastName'] = '';
                    $DataPerson['Custody_2_Address'] = '';
                    $DataPerson['Custody_2_Street'] = '';
                    $DataPerson['Custody_2_HouseNumber'] = '';
                    $DataPerson['Custody_2_CityCode'] = '';
                    $DataPerson['Custody_2_City'] = '';
                    $DataPerson['Custody_2_District'] = '';
                    $DataPerson['Custody_2_PhoneFixedPrivate'] = '';
                    $DataPerson['Custody_2_PhoneFixedWork'] = '';
                    $DataPerson['Custody_2_PhoneFixedEmergency'] = '';
                    $DataPerson['Custody_2_PhoneMobilePrivate'] = '';
                    $DataPerson['Custody_2_PhoneMobileWork'] = '';
                    $DataPerson['Custody_2_PhoneMobileEmergency'] = '';
                    $DataPerson['Custody_2_Mail_Private'] = '';
                    $DataPerson['Custody_2_Mail_Work'] = '';

                    if (($tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
                        foreach ($tblRelationshipAll as $tblToPerson) {
                            /** @var \SPHERE\Application\People\Relationship\Service\Entity\TblToPerson $tblToPerson */
                            if (($tblType = $tblToPerson->getTblType())) {
                                if ($tblType->getName() == 'Geschwisterkind') {
                                    if (($tblPersonFrom = $tblToPerson->getServiceTblPersonFrom()) && ($tblPersonTo = $tblToPerson->getServiceTblPersonTo())) {
                                        if ($tblPersonFrom->getId() !== $tblPerson->getId()) {
                                            $tblPersonSibling = $tblPersonFrom;
                                        } elseif ($tblPersonTo->getId() !== $tblPerson->getId()) {
                                            $tblPersonSibling = $tblPersonTo;
                                        }
                                        if (!empty($tblPersonSibling)) {
                                            $SiblingString = $tblPersonSibling->getLastName() . ', ' . $tblPersonSibling->getFirstName();
                                            if (!empty($tblPersonSibling->getSecondName())) {
                                                $SiblingString .= ' ' . $tblPersonSibling->getSecondName();
                                            }
                                            if (($tblYear = Term::useService()->getYearById($Year[ViewYear::TBL_YEAR_ID]))) {
                                                if (($SiblingDivision = Division::useService()->getDivisionByPersonAndYear($tblPersonSibling, $tblYear))) {
                                                    if (($tblSiblingLevel = $SiblingDivision->getTblLevel())
                                                        && ($SiblingLevel = $tblSiblingLevel->getName())) {
                                                        $SiblingString .= ' ' . $SiblingLevel . $SiblingDivision->getName();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if (empty($DataPerson['Sibling_1']) && !empty($SiblingString)) {
                                        $DataPerson['Sibling_1'] = $SiblingString;
                                    } elseif (empty($DataPerson['Sibling_2']) && !empty($SiblingString)) {
                                        $DataPerson['Sibling_2'] = $SiblingString;
                                    } elseif (empty($DataPerson['Sibling_3']) && !empty($SiblingString)) {
                                        $DataPerson['Sibling_3'] = $SiblingString;
                                    }
                                } elseif ($tblType->getName() == 'Sorgeberechtigt') {
                                    if (($tblPersonCustody = $tblToPerson->getServiceTblPersonFrom())) {
                                        if (empty($DataPerson['Custody_1_FirstName']) && empty($DataPerson['Custody_1_LastName'])) {

                                            if (($tblSalutationCustody = $tblPersonCustody->getTblSalutation())) {
                                                $DataPerson['Custody_1_Salutation'] = $tblSalutationCustody->getSalutation();
                                            }
                                            $DataPerson['Custody_1_Title'] = $tblPersonCustody->getTitle();
                                            $DataPerson['Custody_1_FirstName'] = $tblPersonCustody->getFirstName();
                                            if (!empty($tblPersonCustody->getSecondName())) {
                                                $DataPerson['Custody_1_FirstName'] .= $tblPersonCustody->getSecondName();
                                            }
                                            $DataPerson['Custody_1_LastName'] = $tblPersonCustody->getLastName();

                                            if (($tblAddressCustody = Address::useService()->getAddressByPerson($tblPersonCustody))) {
                                                $DataPerson['Custody_1_Address'] = $tblAddressCustody->getGuiString();
                                                if (($tblCityCustody = $tblAddressCustody->getTblCity())) {
                                                    $DataPerson['Custody_1_Street'] = $tblAddressCustody->getStreetName();
                                                    $DataPerson['Custody_1_HouseNumber'] = $tblAddressCustody->getStreetNumber();
                                                    $DataPerson['Custody_1_CityCode'] = $tblCityCustody->getCode();
                                                    $DataPerson['Custody_1_City'] = $tblCityCustody->getName();
                                                    $DataPerson['Custody_1_District'] = $tblCityCustody->getDisplayDistrict();
                                                }
                                            }

                                            if (($tblPhoneAllCustody = Phone::useService()->getPhoneAllByPerson($tblPersonCustody))) {
                                                foreach ($tblPhoneAllCustody as $tblToPersonCustody) {
                                                    /** @var TblToPerson $tblToPersonCustody */
                                                    if (($tblPhoneTypeCustody = $tblToPersonCustody->getTblType())
                                                        && ($PhoneDescriptionCustody = $tblPhoneTypeCustody->getDescription())
                                                        && ($PhoneNameCustody = $tblPhoneTypeCustody->getName())
                                                        && ($tblPhoneCustody = $tblToPersonCustody->getTblPhone())) {
                                                        if ($PhoneDescriptionCustody == 'Festnetz') {
                                                            switch ($PhoneNameCustody) {
                                                                case 'Privat':
                                                                    if (empty($DataPerson['Custody_1_PhoneFixedPrivate'])) {
                                                                        $DataPerson['Custody_1_PhoneFixedPrivate'] = $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    } else {
                                                                        $DataPerson['Custody_1_PhoneFixedPrivate'] .= ', ' . $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    }
                                                                    break;
                                                                case 'Geschäftlich':
                                                                    if (empty($DataPerson['Custody_1_PhoneFixedWork'])) {
                                                                        $DataPerson['Custody_1_PhoneFixedWork'] = $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    } else {
                                                                        $DataPerson['Custody_1_PhoneFixedWork'] .= ', ' . $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    }
                                                                    break;
                                                                case 'Notfall':
                                                                    if (empty($DataPerson['Custody_1_PhoneFixedEmergency'])) {
                                                                        $DataPerson['Custody_1_PhoneFixedEmergency'] = $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    } else {
                                                                        $DataPerson['Custody_1_PhoneFixedEmergency'] .= ', ' . $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    }
                                                                    break;
                                                            }
                                                        } elseif ($PhoneDescriptionCustody == 'Mobil') {
                                                            switch ($PhoneNameCustody) {
                                                                case 'Privat':
                                                                    if (empty($DataPerson['Custody_1_PhoneMobilePrivate'])) {
                                                                        $DataPerson['Custody_1_PhoneMobilePrivate'] = $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    } else {
                                                                        $DataPerson['Custody_1_PhoneMobilePrivate'] .= ', ' . $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    }
                                                                    break;
                                                                case 'Geschäftlich':
                                                                    if (empty($DataPerson['Custody_1_PhoneMobileWork'])) {
                                                                        $DataPerson['Custody_1_PhoneMobileWork'] = $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    } else {
                                                                        $DataPerson['Custody_1_PhoneMobileWork'] .= ', ' . $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    }
                                                                    break;
                                                                case 'Notfall':
                                                                    if (empty($DataPerson['Custody_1_PhoneMobileEmergency'])) {
                                                                        $DataPerson['Custody_1_PhoneMobileEmergency'] = $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    } else {
                                                                        $DataPerson['Custody_1_PhoneMobileEmergency'] .= ', ' . $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    }
                                                                    break;
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            if (($tblMailAllCustody = Mail::useService()->getMailAllByPerson($tblPersonCustody))) {
                                                foreach ($tblMailAllCustody as $tblToPersonMailCustody) {
                                                    if (($tblTypeMailCustody = $tblToPersonMailCustody->getTblType())
                                                        && ($tblMailCustody = $tblToPersonMailCustody->getTblMail())) {
                                                        if ($tblTypeMailCustody->getName() == 'Privat') {
                                                            $DataPerson['Custody_1_Mail_Private'] = $tblMailCustody->getAddress();
                                                        } elseif ($tblTypeMailCustody->getName() == 'Geschäftlich') {
                                                            $DataPerson['Custody_1_Mail_Work'] = $tblMailCustody->getAddress();
                                                        }
                                                    }
                                                }
                                            }
                                        } elseif (empty($DataPerson['Custody_2_FirstName']) && empty($DataPerson['Custody_2_LastName'])) {

                                            if (($tblSalutationCustody = $tblPersonCustody->getTblSalutation())) {
                                                $DataPerson['Custody_2_Salutation'] = $tblSalutationCustody->getSalutation();
                                            }
                                            $DataPerson['Custody_2_Title'] = $tblPersonCustody->getTitle();
                                            $DataPerson['Custody_2_FirstName'] = $tblPersonCustody->getFirstName();
                                            if (!empty($tblPersonCustody->getSecondName())) {
                                                $DataPerson['Custody_2_FirstName'] .= $tblPersonCustody->getSecondName();
                                            }
                                            $DataPerson['Custody_2_LastName'] = $tblPersonCustody->getLastName();

                                            if (($tblAddressCustody = Address::useService()->getAddressByPerson($tblPersonCustody))) {
                                                $DataPerson['Custody_2_Address'] = $tblAddressCustody->getGuiString();
                                                if (($tblCityCustody = $tblAddressCustody->getTblCity())) {
                                                    $DataPerson['Custody_2_Street'] = $tblAddressCustody->getStreetName();
                                                    $DataPerson['Custody_2_HouseNumber'] = $tblAddressCustody->getStreetNumber();
                                                    $DataPerson['Custody_2_CityCode'] = $tblCityCustody->getCode();
                                                    $DataPerson['Custody_2_City'] = $tblCityCustody->getName();
                                                    $DataPerson['Custody_2_District'] = $tblCityCustody->getDisplayDistrict();
                                                }
                                            }

                                            if (($tblPhoneAllCustody = Phone::useService()->getPhoneAllByPerson($tblPersonCustody))) {
                                                foreach ($tblPhoneAllCustody as $tblToPersonCustody) {
                                                    /** @var TblToPerson $tblToPersonCustody */
                                                    if (($tblPhoneTypeCustody = $tblToPersonCustody->getTblType())
                                                        && ($PhoneDescriptionCustody = $tblPhoneTypeCustody->getDescription())
                                                        && ($PhoneNameCustody = $tblPhoneTypeCustody->getName())
                                                        && ($tblPhoneCustody = $tblToPersonCustody->getTblPhone())) {
                                                        if ($PhoneDescriptionCustody == 'Festnetz') {
                                                            switch ($PhoneNameCustody) {
                                                                case 'Privat':
                                                                    if (empty($DataPerson['Custody_2_PhoneFixedPrivate'])) {
                                                                        $DataPerson['Custody_2_PhoneFixedPrivate'] = $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    } else {
                                                                        $DataPerson['Custody_2_PhoneFixedPrivate'] .= ', ' . $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    }
                                                                    break;
                                                                case 'Geschäftlich':
                                                                    if (empty($DataPerson['Custody_2_PhoneFixedWork'])) {
                                                                        $DataPerson['Custody_2_PhoneFixedWork'] = $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    } else {
                                                                        $DataPerson['Custody_2_PhoneFixedWork'] .= ', ' . $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    }
                                                                    break;
                                                                case 'Notfall':
                                                                    if (empty($DataPerson['Custody_2_PhoneFixedEmergency'])) {
                                                                        $DataPerson['Custody_2_PhoneFixedEmergency'] = $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    } else {
                                                                        $DataPerson['Custody_2_PhoneFixedEmergency'] .= ', ' . $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    }
                                                                    break;
                                                            }
                                                        } elseif ($PhoneDescriptionCustody == 'Mobil') {
                                                            switch ($PhoneNameCustody) {
                                                                case 'Privat':
                                                                    if (empty($DataPerson['Custody_2_PhoneMobilePrivate'])) {
                                                                        $DataPerson['Custody_2_PhoneMobilePrivate'] = $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    } else {
                                                                        $DataPerson['Custody_2_PhoneMobilePrivate'] .= ', ' . $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    }
                                                                    break;
                                                                case 'Geschäftlich':
                                                                    if (empty($DataPerson['Custody_2_PhoneMobileWork'])) {
                                                                        $DataPerson['Custody_2_PhoneMobileWork'] = $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    } else {
                                                                        $DataPerson['Custody_2_PhoneMobileWork'] .= ', ' . $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    }
                                                                    break;
                                                                case 'Notfall':
                                                                    if (empty($DataPerson['Custody_2_PhoneMobileEmergency'])) {
                                                                        $DataPerson['Custody_2_PhoneMobileEmergency'] = $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    } else {
                                                                        $DataPerson['Custody_2_PhoneMobileEmergency'] .= ', ' . $tblPhoneCustody->getNumber()
                                                                            . (!empty($tblToPersonCustody->getRemark()) ? ' (' . $tblToPersonCustody->getRemark() . ')' : '');
                                                                    }
                                                                    break;
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            if (($tblMailAllCustody = Mail::useService()->getMailAllByPerson($tblPersonCustody))) {
                                                foreach ($tblMailAllCustody as $tblToPersonMailCustody) {
                                                    if (($tblTypeMailCustody = $tblToPersonMailCustody->getTblType())
                                                        && ($tblMailCustody = $tblToPersonMailCustody->getTblMail())) {
                                                        if ($tblTypeMailCustody->getName() == 'Privat') {
                                                            $DataPerson['Custody_2_Mail_Private'] = $tblMailCustody->getAddress();
                                                        } elseif ($tblTypeMailCustody->getName() == 'Geschäftlich') {
                                                            $DataPerson['Custody_2_Mail_Work'] = $tblMailCustody->getAddress();
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // ignore duplicated Person
                    if ($DataPerson['TblPerson_Id']) {
                        if (!array_key_exists($DataPerson['TblPerson_Id'], $SearchResult)) {
                            $SearchResult[$DataPerson['TblPerson_Id']] = $DataPerson;
                        }
                    }
                }
            }
        }

        return $SearchResult;
    }

    /**
     * @param null $Person
     * @param null $Year
     * @param null $Division
     *
     * @return FilePointer
     */
    public function createMetaDataComparisonExcel($Person = null, $Year = null, $Division = null)
    {

        $Result = $this->getStudentFilterResult($Person, $Year, $Division);

        $TableContent = $this->getStudentTableContent($Result);

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());

        $Row = 0;

        $export->setValue($export->getCell(0, $Row), "Klasse");
        $export->setValue($export->getCell(1, $Row), "Schülernummer");
        $export->setValue($export->getCell(2, $Row), "Vorname");
        $export->setValue($export->getCell(3, $Row), "Nachname");
        $export->setValue($export->getCell(4, $Row), "Geschlecht");
        $export->setValue($export->getCell(5, $Row), "Geburtstag");
        $export->setValue($export->getCell(6, $Row), "Geburtsort");
        $export->setValue($export->getCell(7, $Row), "Straße");
        $export->setValue($export->getCell(8, $Row), "Hausnummer");
        $export->setValue($export->getCell(9, $Row), "PLZ");
        $export->setValue($export->getCell(10, $Row), "Wohnort");
        $export->setValue($export->getCell(11, $Row), "Ortsteil");
        $export->setValue($export->getCell(12, $Row), "Krankenkasse");
        $export->setValue($export->getCell(13, $Row), "Religion");
        $export->setValue($export->getCell(14, $Row), "Festnetz (Privat)");
        $export->setValue($export->getCell(15, $Row), "Festnetz (Geschäftl.)");
        $export->setValue($export->getCell(16, $Row), "Festnetz (Notfall)");
        $export->setValue($export->getCell(17, $Row), "Mobil (Privat)");
        $export->setValue($export->getCell(18, $Row), "Mobil (Geschäftl.)");
        $export->setValue($export->getCell(19, $Row), "Mobil (Notfall)");

        $export->setValue($export->getCell(20, $Row), "Geschwister1");
        $export->setValue($export->getCell(21, $Row), "Geschwister2");
        $export->setValue($export->getCell(22, $Row), "Geschwister3");

        $export->setValue($export->getCell(23, $Row), "Sorg1 Anrede");
        $export->setValue($export->getCell(24, $Row), "Sorg1 Titel");
        $export->setValue($export->getCell(25, $Row), "Sorg1 Vorname");
        $export->setValue($export->getCell(26, $Row), "Sorg1 Nachname");
        $export->setValue($export->getCell(27, $Row), "Sorg1 Straße");
        $export->setValue($export->getCell(28, $Row), "Sorg1 Hausnummer");
        $export->setValue($export->getCell(29, $Row), "Sorg1 PLZ");
        $export->setValue($export->getCell(30, $Row), "Sorg1 Wohnort");
        $export->setValue($export->getCell(31, $Row), "Sorg1 Ortsteil");
        $export->setValue($export->getCell(32, $Row), "Sorg1 Festnetz (Privat)");
        $export->setValue($export->getCell(33, $Row), "Sorg1 Festnetz (Geschäftl.)");
        $export->setValue($export->getCell(34, $Row), "Sorg1 Festnetz (Notfall)");
        $export->setValue($export->getCell(35, $Row), "Sorg1 Mobil (Privat)");
        $export->setValue($export->getCell(36, $Row), "Sorg1 Mobil (Geschäftl.)");
        $export->setValue($export->getCell(37, $Row), "Sorg1 Mobil (Notfall)");
        $export->setValue($export->getCell(38, $Row), "Sorg1 Mail (Privat)");
        $export->setValue($export->getCell(39, $Row), "Sorg1 Mail (Geschäftl.)");

        $export->setValue($export->getCell(40, $Row), "Sorg2 Anrede");
        $export->setValue($export->getCell(41, $Row), "Sorg2 Titel");
        $export->setValue($export->getCell(42, $Row), "Sorg2 Vorname");
        $export->setValue($export->getCell(43, $Row), "Sorg2 Nachname");
        $export->setValue($export->getCell(44, $Row), "Sorg2 Straße");
        $export->setValue($export->getCell(45, $Row), "Sorg2 Hausnummer");
        $export->setValue($export->getCell(46, $Row), "Sorg2 PLZ");
        $export->setValue($export->getCell(47, $Row), "Sorg2 Wohnort");
        $export->setValue($export->getCell(48, $Row), "Sorg2 Ortsteil");
        $export->setValue($export->getCell(49, $Row), "Sorg2 Festnetz (Privat)");
        $export->setValue($export->getCell(50, $Row), "Sorg2 Festnetz (Geschäftl.)");
        $export->setValue($export->getCell(51, $Row), "Sorg2 Festnetz (Notfall)");
        $export->setValue($export->getCell(52, $Row), "Sorg2 Mobil (Privat)");
        $export->setValue($export->getCell(53, $Row), "Sorg2 Mobil (Geschäftl.)");
        $export->setValue($export->getCell(54, $Row), "Sorg2 Mobil (Notfall)");
        $export->setValue($export->getCell(55, $Row), "Sorg2 Mail (Privat)");
        $export->setValue($export->getCell(56, $Row), "Sorg2 Mail (Geschäftl.)");

        foreach ($TableContent as $PersonData) {
            $Row++;

            $export->setValue($export->getCell(0, $Row), $PersonData['Division']);
            $export->setValue($export->getCell(1, $Row), $PersonData['StudentNumber']);
            $export->setValue($export->getCell(2, $Row), $PersonData['FirstName']);
            $export->setValue($export->getCell(3, $Row), $PersonData['LastName']);
            $export->setValue($export->getCell(4, $Row), $PersonData['Gender']);
            $export->setValue($export->getCell(5, $Row), $PersonData['Birthday']);
            $export->setValue($export->getCell(6, $Row), $PersonData['BirthPlace']);
            $export->setValue($export->getCell(7, $Row), $PersonData['Street']);
            $export->setValue($export->getCell(8, $Row), $PersonData['HouseNumber']);
            $export->setValue($export->getCell(9, $Row), $PersonData['CityCode']);
            $export->setValue($export->getCell(10, $Row), $PersonData['City']);
            $export->setValue($export->getCell(11, $Row), $PersonData['District']);
            $export->setValue($export->getCell(12, $Row), $PersonData['Insurance']);
            $export->setValue($export->getCell(13, $Row), $PersonData['Religion']);
            $export->setValue($export->getCell(14, $Row), $PersonData['PhoneFixedPrivate']);
            $export->setValue($export->getCell(15, $Row), $PersonData['PhoneFixedWork']);
            $export->setValue($export->getCell(16, $Row), $PersonData['PhoneFixedEmergency']);
            $export->setValue($export->getCell(17, $Row), $PersonData['PhoneMobilePrivate']);
            $export->setValue($export->getCell(18, $Row), $PersonData['PhoneMobileWork']);
            $export->setValue($export->getCell(19, $Row), $PersonData['PhoneMobileEmergency']);

            $export->setValue($export->getCell(20, $Row), $PersonData['Sibling_1']);
            $export->setValue($export->getCell(21, $Row), $PersonData['Sibling_2']);
            $export->setValue($export->getCell(22, $Row), $PersonData['Sibling_3']);

            $export->setValue($export->getCell(23, $Row), $PersonData['Custody_1_Salutation']);
            $export->setValue($export->getCell(24, $Row), $PersonData['Custody_1_Title']);
            $export->setValue($export->getCell(25, $Row), $PersonData['Custody_1_FirstName']);
            $export->setValue($export->getCell(26, $Row), $PersonData['Custody_1_LastName']);
            $export->setValue($export->getCell(27, $Row), $PersonData['Custody_1_Street']);
            $export->setValue($export->getCell(28, $Row), $PersonData['Custody_1_HouseNumber']);
            $export->setValue($export->getCell(29, $Row), $PersonData['Custody_1_CityCode']);
            $export->setValue($export->getCell(30, $Row), $PersonData['Custody_1_City']);
            $export->setValue($export->getCell(31, $Row), $PersonData['Custody_1_District']);
            $export->setValue($export->getCell(32, $Row), $PersonData['Custody_1_PhoneFixedPrivate']);
            $export->setValue($export->getCell(33, $Row), $PersonData['Custody_1_PhoneFixedWork']);
            $export->setValue($export->getCell(34, $Row), $PersonData['Custody_1_PhoneFixedEmergency']);
            $export->setValue($export->getCell(35, $Row), $PersonData['Custody_1_PhoneMobilePrivate']);
            $export->setValue($export->getCell(36, $Row), $PersonData['Custody_1_PhoneMobileWork']);
            $export->setValue($export->getCell(37, $Row), $PersonData['Custody_1_PhoneMobileEmergency']);
            $export->setValue($export->getCell(38, $Row), $PersonData['Custody_1_Mail_Private']);
            $export->setValue($export->getCell(39, $Row), $PersonData['Custody_1_Mail_Work']);

            $export->setValue($export->getCell(40, $Row), $PersonData['Custody_2_Salutation']);
            $export->setValue($export->getCell(41, $Row), $PersonData['Custody_2_Title']);
            $export->setValue($export->getCell(42, $Row), $PersonData['Custody_2_FirstName']);
            $export->setValue($export->getCell(43, $Row), $PersonData['Custody_2_LastName']);
            $export->setValue($export->getCell(44, $Row), $PersonData['Custody_2_Street']);
            $export->setValue($export->getCell(45, $Row), $PersonData['Custody_2_HouseNumber']);
            $export->setValue($export->getCell(46, $Row), $PersonData['Custody_2_CityCode']);
            $export->setValue($export->getCell(47, $Row), $PersonData['Custody_2_City']);
            $export->setValue($export->getCell(48, $Row), $PersonData['Custody_2_District']);
            $export->setValue($export->getCell(49, $Row), $PersonData['Custody_2_PhoneFixedPrivate']);
            $export->setValue($export->getCell(50, $Row), $PersonData['Custody_2_PhoneFixedWork']);
            $export->setValue($export->getCell(51, $Row), $PersonData['Custody_2_PhoneFixedEmergency']);
            $export->setValue($export->getCell(52, $Row), $PersonData['Custody_2_PhoneMobilePrivate']);
            $export->setValue($export->getCell(53, $Row), $PersonData['Custody_2_PhoneMobileWork']);
            $export->setValue($export->getCell(54, $Row), $PersonData['Custody_2_PhoneMobileEmergency']);
            $export->setValue($export->getCell(55, $Row), $PersonData['Custody_2_Mail_Private']);
            $export->setValue($export->getCell(56, $Row), $PersonData['Custody_2_Mail_Work']);
        }

        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

        return $fileLocation;
    }
}
