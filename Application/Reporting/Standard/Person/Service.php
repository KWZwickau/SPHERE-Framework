<?php
namespace SPHERE\Application\Reporting\Standard\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Search\Group\Group;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Standard\Person
 */
class Service
{

    /**
     * @return bool|\SPHERE\Application\People\Person\Service\Entity\TblPerson[]
     */
    public function createClassList()
    {

        // Todo JohK Klassen einbauen
        $studentList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Schüler'));

        if (!empty( $studentList )) {
            foreach ($studentList as $tblPerson) {
//                $father = null;
//                $mother = null;
//                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
//                if ($guardianList) {
//                    foreach ($guardianList as $guardian) {
//                        if (( $guardian->getTblType()->getId() == 1 )
//                            && ( $guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 1 )
//                        ) {
//                            $father = $guardian->getServiceTblPersonFrom();
//                        }
//                        if (( $guardian->getTblType()->getId() == 1 )
//                            && ( $guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 2 )
//                        ) {
//                            $mother = $guardian->getServiceTblPersonFrom();
//                        }
//                    }
//                }

                if ($addressList = Address::useService()->getAddressAllByPerson($tblPerson)) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }

                $tblPerson->Salutation = $tblPerson->getSalutation();

                if ($address !== null) {
                    $tblPerson->StreetName = $address->getTblAddress()->getStreetName();
                    $tblPerson->StreetNumber = $address->getTblAddress()->getStreetNumber();
                    $tblPerson->Code = $address->getTblAddress()->getTblCity()->getCode();
                    $tblPerson->City = $address->getTblAddress()->getTblCity()->getName();

                    $tblPerson->Address = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber().' '.
                        $address->getTblAddress()->getTblCity()->getCode().' '.
                        $address->getTblAddress()->getTblCity()->getName();
                } else {
                    $tblPerson->StreetName = $tblPerson->StreetNumber = $tblPerson->Code = $tblPerson->City = '';
                    $tblPerson->Address = '';
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $tblPerson->Denomination = $common->getTblCommonInformation()->getDenomination();
                    $tblPerson->Birthday = $common->getTblCommonBirthDates()->getBirthday();
                    $tblPerson->Birthplace = $common->getTblCommonBirthDates()->getBirthplace();
                } else {
                    $tblPerson->Birthday = $tblPerson->Birthplace = $tblPerson->Denomination = '';
                }
            }
        }

        return $studentList;
    }

    /**
     * @param $studentList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createClassListExcel($studentList)
    {

        if (!empty( $studentList )) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anrede");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Name");
            $export->setValue($export->getCell("3", "0"), "Konfession");
            $export->setValue($export->getCell("4", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("5", "0"), "Geburtsort");
            $export->setValue($export->getCell("6", "0"), "Straße");
            $export->setValue($export->getCell("7", "0"), "Hausnr.");
            $export->setValue($export->getCell("8", "0"), "PLZ");
            $export->setValue($export->getCell("9", "0"), "Ort");

            $Row = 1;

            foreach ($studentList as $tblPerson) {

                $export->setValue($export->getCell("0", $Row), $tblPerson->Salutation);
                /** @var TblPerson $tblPerson */
                $export->setValue($export->getCell("1", $Row), $tblPerson->getFirstName());
                $export->setValue($export->getCell("2", $Row), $tblPerson->getLastName());
                /** @var $tblPerson */
                $export->setValue($export->getCell("3", $Row), $tblPerson->Denomination);
                $export->setValue($export->getCell("4", $Row), $tblPerson->Birthday);
                $export->setValue($export->getCell("5", $Row), $tblPerson->Birthplace);
                $export->setValue($export->getCell("6", $Row), $tblPerson->StreetName);
                $export->setValue($export->getCell("7", $Row), $tblPerson->StreetNumber);
                $export->setValue($export->getCell("8", $Row), $tblPerson->Code);
                $export->setValue($export->getCell("9", $Row), $tblPerson->City);

                $Row++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

    /**
     * @return bool|\SPHERE\Application\People\Person\Service\Entity\TblPerson[]
     */
    public function createExtendedClassList()
    {

        // Todo JohK Klassen einbauen
        $studentList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Schüler'));
        if (!empty( $studentList )) {

            foreach ($studentList as $key => $row) {
                $lastName[$key] = strtoupper($row->getLastName());
                $firstName[$key] = strtoupper($row->getFirstName());
                $id[$key] = $row->getId();
            }
            array_multisort($lastName, SORT_ASC, $firstName, SORT_ASC, $studentList);

            $Man = 0;
            $Woman = 0;
            $All = 0;

            foreach ($studentList as $tblPerson) {
                $All++;
                $tblPerson->Number = $All;
                $tblPerson->Name = $tblPerson->getLastName().', '.$tblPerson->getFirstName();
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    if ($tblCommon->getTblCommonBirthDates()->getGender() === 1) {
                        $tblPerson->Gender = 'männlich';
                        $Man++;
                    } elseif ($tblCommon->getTblCommonBirthDates()->getGender() === 2) {
                        $tblPerson->Gender = 'weiblich';
                        $Woman++;
                    } else {
                        $tblPerson->Gender = '';
                    }
                } else {
                    $tblPerson->Gender = '';
                }

                if ($addressList = Address::useService()->getAddressAllByPerson($tblPerson)) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }
                if ($address !== null) {
                    $tblPerson->StreetName = $address->getTblAddress()->getStreetName();
                    $tblPerson->StreetNumber = $address->getTblAddress()->getStreetNumber();
                    $tblPerson->Code = $address->getTblAddress()->getTblCity()->getCode();
                    $tblPerson->City = $address->getTblAddress()->getTblCity()->getName();

                    $tblPerson->Address = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber().' '.
                        $address->getTblAddress()->getTblCity()->getCode().' '.
                        $address->getTblAddress()->getTblCity()->getName();
                } else {
                    $tblPerson->StreetName = $tblPerson->StreetNumber = $tblPerson->Code = $tblPerson->City = '';
                    $tblPerson->Address = '';
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $tblPerson->Birthday = $common->getTblCommonBirthDates()->getBirthday();
                    $tblPerson->Birthplace = $common->getTblCommonBirthDates()->getBirthplace();
                } else {
                    $tblPerson->Birthday = $tblPerson->Birthplace = '';
                }

                $tblPerson->StudentNumber = $tblPerson->getId() + 200000; //ToDO StudentNumber

                $father = null;
                $mother = null;
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if (( $guardian->getTblType()->getId() == 1 )
                            && ( $guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 1 )
                        ) {
                            $father = $guardian->getServiceTblPersonFrom();
                            if ($father) {
                                $tblPerson->Father = $father->getFullName();
                            } else {
                                $tblPerson->Father = '';
                            }
                        }
                        if (( $guardian->getTblType()->getId() == 1 )
                            && ( $guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 2 )
                        ) {
                            $mother = $guardian->getServiceTblPersonFrom();
                            if ($mother) {
                                $tblPerson->Mother = $mother->getFullName();
                            } else {
                                $tblPerson->Mother = '';
                            }
                        }
                    }
                }
                if (!isset( $father )) {
                    $tblPerson->Father = '';
                }
                if (!isset( $mother )) {
                    $tblPerson->Mother = '';
                }

                unset( $phoneListMother );
                unset( $phoneListFather );
                if ($mother) {
                    $motherPhoneList = Phone::useService()->getPhoneAllByPerson($mother);
                    if ($motherPhoneList) {
                        foreach ($motherPhoneList as $motherPhone) {
                            if ($motherPhone->getTblType()->getName() === 'Privat' && $motherPhone->getTblType()->getDescription() === 'Mobil') {
                                $phoneListMother[] = $motherPhone->getTblPhone()->getNumber();
                            }
                        }
                        foreach ($motherPhoneList as $motherPhone) {
                            if ($motherPhone->getTblType()->getName() === 'Privat') {
                                $phoneListMother[] = $motherPhone->getTblPhone()->getNumber();
                            }
                        }
                        if (isset( $phoneListMother )) {
                            $phoneListMother = array_unique($phoneListMother);
                        }
                    }
                }
                if ($father) {
                    $fatherPhoneList = Phone::useService()->getPhoneAllByPerson($father);
                    if ($fatherPhoneList) {
                        foreach ($fatherPhoneList as $fatherPhone) {
                            if ($fatherPhone->getTblType()->getName() === 'Privat') {
                                $phoneListFather[] = $fatherPhone->getTblPhone()->getNumber();
                            }
                        }
                    }
                }
                if (isset( $phoneListMother[0] )) {
                    $tblPerson->PhoneMother = $phoneListMother[0];
                    if (isset( $phoneListFather[0] )) {
                        if ($phoneListFather[0] === $phoneListMother[0]) {
                            if (isset( $phoneListFather[1] )) {
                                $tblPerson->PhoneFather = $phoneListFather[1];
                            } else {
                                $tblPerson->PhoneFather = '';
                            }
                        } else {
                            if (isset( $phoneListFather[0] )) {
                                $tblPerson->PhoneFather = $phoneListFather[0];
                            } else {
                                $tblPerson->PhoneFather = '';
                            }
                        }
                    }
                } else {
                    $tblPerson->PhoneMother = '';
                    if (isset( $phoneListFather[0] )) {
                        $tblPerson->PhoneFather = $phoneListFather[0];
                    } else {
                        $tblPerson->PhoneFather = '';
                    }
                }
            }
            $Count = count($studentList);
            $studentList[$Count - 1]->Woman = $Woman;
            $studentList[$Count - 1]->Man = $Man;
            $studentList[$Count - 1]->All = $All;
        }

        return $studentList;
    }

    /**
     * @param $studentList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createExtendedClassListExcel($studentList)
    {

        if (!empty( $studentList )) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "lfd. Nr.");
            $export->setValue($export->getCell("1", "0"), "Name, Vorname");
            $export->setValue($export->getCell("2", "0"), "Geschlecht");
            $export->setValue($export->getCell("3", "0"), "Adresse");
            $export->setValue($export->getCell("4", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("5", "0"), "Geburtsort");
            $export->setValue($export->getCell("6", "0"), "Schülernummer");
            $export->setValue($export->getCell("7", "0"), "Sorgeberechtigte");
            $export->setValue($export->getCell("8", "0"), "Tel. Sorgeber.");
            $export->setValue($export->getCell("9", "0"), "Sorgeberechtigter");
            $export->setValue($export->getCell("10", "0"), "Tel. Sorgeber.");

            $Row = 1;

            foreach ($studentList as $tblPerson) {

                $export->setValue($export->getCell("0", $Row), $tblPerson->Number);
                $export->setValue($export->getCell("1", $Row), $tblPerson->Name);
                $export->setValue($export->getCell("2", $Row), $tblPerson->Gender);
                $export->setValue($export->getCell("3", $Row), $tblPerson->Address);
                $export->setValue($export->getCell("4", $Row), $tblPerson->Birthday);
                $export->setValue($export->getCell("5", $Row), $tblPerson->Birthplace);
                $export->setValue($export->getCell("6", $Row), $tblPerson->StudentNumber);
                $export->setValue($export->getCell("7", $Row), $tblPerson->Mother);
                $export->setValue($export->getCell("8", $Row), $tblPerson->PhoneMother);
                $export->setValue($export->getCell("9", $Row), $tblPerson->Father);
                $export->setValue($export->getCell("10", $Row), $tblPerson->PhoneFather);

                $Row++;
            }

            $Count = count($studentList);
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Schüler:');
            $export->setValue($export->getCell("1", $Row), $studentList[$Count - 1]->All);
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Mädchen:');
            $export->setValue($export->getCell("1", $Row), $studentList[$Count - 1]->Woman);
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Jungen:');
            $export->setValue($export->getCell("1", $Row), $studentList[$Count - 1]->Man);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

    /**
     * @return bool|\SPHERE\Application\People\Person\Service\Entity\TblPerson[]
     */
    public function createBirthdayClassList()
    {

        // Todo JohK Klassen einbauen
        $studentList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Schüler'));

        if (!empty( $studentList )) {

            foreach ($studentList as $key => $row) {
                $lastName[$key] = strtoupper($row->getLastName());
                $firstName[$key] = strtoupper($row->getFirstName());
                $id[$key] = $row->getId();
            }
            array_multisort($lastName, SORT_ASC, $firstName, SORT_ASC, $studentList);

            $Man = 0;
            $Woman = 0;
            $All = 0;

            foreach ($studentList as $tblPerson) {
                $All++;
                $tblPerson->Number = $All;
                $tblPerson->Name = $tblPerson->getLastName().', '.$tblPerson->getFirstName();
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    if ($tblCommon->getTblCommonBirthDates()->getGender() === 1) {
                        $tblPerson->Gender = 'männlich';
                        $Man++;
                    } elseif ($tblCommon->getTblCommonBirthDates()->getGender() === 2) {
                        $tblPerson->Gender = 'weiblich';
                        $Woman++;
                    } else {
                        $tblPerson->Gender = '';
                    }
                } else {
                    $tblPerson->Gender = '';
                }

                if ($addressList = Address::useService()->getAddressAllByPerson($tblPerson)) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }
                if ($address !== null) {
                    $tblPerson->StreetName = $address->getTblAddress()->getStreetName();
                    $tblPerson->StreetNumber = $address->getTblAddress()->getStreetNumber();
                    $tblPerson->Code = $address->getTblAddress()->getTblCity()->getCode();
                    $tblPerson->City = $address->getTblAddress()->getTblCity()->getName();

                    $tblPerson->Address = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber().' '.
                        $address->getTblAddress()->getTblCity()->getCode().' '.
                        $address->getTblAddress()->getTblCity()->getName();
                } else {
                    $tblPerson->StreetName = $tblPerson->StreetNumber = $tblPerson->Code = $tblPerson->City = '';
                    $tblPerson->Address = '';
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $tblPerson->Birthday = $common->getTblCommonBirthDates()->getBirthday();
                    $tblPerson->Birthplace = $common->getTblCommonBirthDates()->getBirthplace();
                    $birthDate = (new \DateTime($common->getTblCommonBirthDates()->getBirthday()));
                    $now = new \DateTime();
                    if ($birthDate->format('y.m') != $now->format('y.m')) {
                        if (( $birthDate->format('m.d') ) <= ( $now->format('m.d') )) {
                            $tblPerson->Age = $now->format('y') - $birthDate->format('y');
                        } else {
                            $tblPerson->Age = ( $now->format('y') - 1 ) - $birthDate->format('y');
                        }
                    } else {
                        $tblPerson->Age = '';
                    }
                } else {
                    $tblPerson->Birthday = $tblPerson->Birthplace = $tblPerson->Age = '';
                }
            }
            $Count = count($studentList);
            $studentList[$Count - 1]->Woman = $Woman;
            $studentList[$Count - 1]->Man = $Man;
            $studentList[$Count - 1]->All = $All;
        }

        return $studentList;
    }

    /**
     * @param $studentList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createBirthdayClassListExcel($studentList)
    {

        if (!empty( $studentList )) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "lfd. Nr.");
            $export->setValue($export->getCell("1", "0"), "Name, Vorname");
            $export->setValue($export->getCell("2", "0"), "Anschrift");
            $export->setValue($export->getCell("3", "0"), "Geburtsort");
            $export->setValue($export->getCell("4", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("5", "0"), "Alter");

            $Row = 1;

            foreach ($studentList as $tblPerson) {

                $export->setValue($export->getCell("0", $Row), $tblPerson->Number);
                $export->setValue($export->getCell("1", $Row), $tblPerson->Name);
                $export->setValue($export->getCell("2", $Row), $tblPerson->Address);
                $export->setValue($export->getCell("3", $Row), $tblPerson->Birthplace);
                $export->setValue($export->getCell("4", $Row), $tblPerson->Birthday);
                $export->setValue($export->getCell("5", $Row), $tblPerson->Age);

                $Row++;
            }

            $Count = count($studentList);
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Schüler:');
            $export->setValue($export->getCell("1", $Row), $studentList[$Count - 1]->All);
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Mädchen:');
            $export->setValue($export->getCell("1", $Row), $studentList[$Count - 1]->Woman);
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Jungen:');
            $export->setValue($export->getCell("1", $Row), $studentList[$Count - 1]->Man);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

    /**
     * @return bool|\SPHERE\Application\People\Person\Service\Entity\TblPerson[]
     */
    public function createMedicalInsuranceClassList()
    {

        // Todo JohK Klassen einbauen
        $studentList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Schüler'));

        if (!empty( $studentList )) {

            foreach ($studentList as $key => $row) {
                $lastName[$key] = strtoupper($row->getLastName());
                $firstName[$key] = strtoupper($row->getFirstName());
                $id[$key] = $row->getId();
            }
            array_multisort($lastName, SORT_ASC, $firstName, SORT_ASC, $studentList);

            $Man = 0;
            $Woman = 0;
            $All = 0;

            foreach ($studentList as $tblPerson) {

                $All++;
                $tblPerson->Number = $All;
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    if ($tblCommon->getTblCommonBirthDates()->getGender() === 1) {
                        $tblPerson->Gender = 'männlich';
                        $Man++;
                    } elseif ($tblCommon->getTblCommonBirthDates()->getGender() === 2) {
                        $tblPerson->Gender = 'weiblich';
                        $Woman++;
                    } else {
                        $tblPerson->Gender = '';
                    }
                } else {
                    $tblPerson->Gender = '';
                }
                $tblPerson->Name = $tblPerson->getLastName().'<br/>'.$tblPerson->getFirstName();
                if ($addressList = Address::useService()->getAddressAllByPerson($tblPerson)) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }
                if ($address !== null) {
                    $tblPerson->StreetName = $address->getTblAddress()->getStreetName();
                    $tblPerson->StreetNumber = $address->getTblAddress()->getStreetNumber();
                    $tblPerson->Code = $address->getTblAddress()->getTblCity()->getCode();
                    $tblPerson->City = $address->getTblAddress()->getTblCity()->getName();

                    $tblPerson->Address = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber().'<br/>'.
                        $address->getTblAddress()->getTblCity()->getCode().' '.
                        $address->getTblAddress()->getTblCity()->getName();
                } else {
                    $tblPerson->StreetName = $tblPerson->StreetNumber = $tblPerson->Code = $tblPerson->City = '';
                    $tblPerson->Address = '';
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $tblPerson->Birthday = $common->getTblCommonBirthDates()->getBirthday().'<br/>'.$common->getTblCommonBirthDates()->getBirthplace();
                } else {
                    $tblPerson->Birthday = $tblPerson->Birthplace = '';
                }

                $krankenkassen = array('AOK', 'BKK', 'Barmer', 'TKK', 'DAK', 'Signal-Iduna', 'LKK', 'IKK', 'KKH', 'TK');
                shuffle($krankenkassen);
                $tblPerson->MedicalInsurance = $krankenkassen[0]; //ToDO Krankenkasse

                $father = null;
                $mother = null;
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if (( $guardian->getTblType()->getId() == 1 )
                            && ( $guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 1 )
                        ) {
                            $father = $guardian->getServiceTblPersonFrom();
                        }
                        if (( $guardian->getTblType()->getId() == 1 )
                            && ( $guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 2 )
                        ) {
                            $mother = $guardian->getServiceTblPersonFrom();
                        }
                    }
                }
                $phoneListGuardian = array();
                if ($mother) {
                    $motherPhoneList = Phone::useService()->getPhoneAllByPerson($mother);
                    if ($motherPhoneList) {
                        foreach ($motherPhoneList as $motherPhone) {
                            $phoneListGuardian[] = $motherPhone->getTblPhone()->getNumber();
                        }
                    }
                    $mother = $mother->getFullName();
                } else {
                    $mother = '';
                }
                if ($father) {
                    $fatherPhoneList = Phone::useService()->getPhoneAllByPerson($father);
                    if ($fatherPhoneList) {
                        foreach ($fatherPhoneList as $fatherPhone) {
                            $phoneListGuardian[] = $fatherPhone->getTblPhone()->getNumber();
                        }
                    }
                    $father = $father->getFullName();
                } else {
                    $father = '';
                }
                $tblPerson->Guardian = $mother.'<br/>'.$father;

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
                $tblPerson->PhoneNumber = $phoneString;
                $phoneListGuardian = array_unique($phoneListGuardian);
                if (count($phoneListGuardian) >= 1) {
                    $phoneGuardianString = implode('<br/>', $phoneListGuardian);
                } else {
                    $phoneGuardianString = '';
                }
                $tblPerson->PhoneGuardianNumber = $phoneGuardianString;

            }
            $Count = count($studentList);
            $studentList[$Count - 1]->Woman = $Woman;
            $studentList[$Count - 1]->Man = $Man;
            $studentList[$Count - 1]->All = $All;
        }

        return $studentList;
    }

    /**
     * @param $studentList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createMedicalInsuranceClassListExcel($studentList)
    {

        if (!empty( $studentList )) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "lfd. Nr.");
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

            foreach ($studentList as $tblPerson) {
                $Name = explode('<br/>', $tblPerson->Name);
                $Address = explode('<br/>', $tblPerson->Address);
                $Birthday = explode('<br/>', $tblPerson->Birthday);
                $KK = explode('<br/>', $tblPerson->MedicalInsurance);
                $Guardian = explode('<br/>', $tblPerson->Guardian);
                $PhoneNumber = explode('<br/>', $tblPerson->PhoneNumber);
                $PhoneGuardianNumber = explode('<br/>', $tblPerson->PhoneGuardianNumber);

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

                $export->setValue($export->getCell("0", $Row), $tblPerson->Number);
                for ($i = 0; $i < $count; $i++) {
                    if (isset( $Name[$i] )) {
                        $export->setValue($export->getCell("1", $Row), $Name[$i]);
                    }
                    if (isset( $Address[$i] )) {
                        $export->setValue($export->getCell("2", $Row), $Address[$i]);
                    }
                    if (isset( $Birthday[$i] )) {
                        $export->setValue($export->getCell("3", $Row), $Birthday[$i]);
                    }
                    if (isset( $KK[$i] )) {
                        $export->setValue($export->getCell("4", $Row), $KK[$i]);
                    }
                    if (isset( $Guardian[$i] )) {
                        $export->setValue($export->getCell("5", $Row), $Guardian[$i]);
                    }
                    if (isset( $PhoneNumber[$i] )) {
                        $export->setValue($export->getCell("6", $Row), $PhoneNumber[$i]);
                    }
                    if (isset( $PhoneGuardianNumber[$i] )) {
                        $export->setValue($export->getCell("7", $Row), $PhoneGuardianNumber[$i]);
                    }
                    $Row++;
                }
            }

            $Count = count($studentList);
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Schüler:');
            $export->setValue($export->getCell("1", $Row), $studentList[$Count - 1]->All);
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Mädchen:');
            $export->setValue($export->getCell("1", $Row), $studentList[$Count - 1]->Woman);
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Jungen:');
            $export->setValue($export->getCell("1", $Row), $studentList[$Count - 1]->Man);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }

    public function createEmployeeList()
    {

        $employeeList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Mitarbeiter'));

        if (!empty( $employeeList )) {

            foreach ($employeeList as $key => $row) {
                $lastName[$key] = strtoupper($row->getLastName());
                $firstName[$key] = strtoupper($row->getFirstName());
                $id[$key] = $row->getId();
            }
            array_multisort($lastName, SORT_ASC, $firstName, SORT_ASC, $employeeList);

            $Man = 0;
            $Woman = 0;
            $All = 0;

            foreach ($employeeList as $tblPerson) {

                $All++;
                $tblPerson->Number = $All;
                $tblPerson->Salutation = $tblPerson->getSalutation();
                if ($addressList = Address::useService()->getAddressAllByPerson($tblPerson)) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    if ($tblCommon->getTblCommonBirthDates()->getGender() === 1) {
                        $tblPerson->Gender = 'männlich';
                        $Man++;
                    } elseif ($tblCommon->getTblCommonBirthDates()->getGender() === 2) {
                        $tblPerson->Gender = 'weiblich';
                        $Woman++;
                    } else {
                        $tblPerson->Gender = '';
                    }
                } else {
                    $tblPerson->Gender = '';
                }
                if ($address !== null) {
                    $tblPerson->StreetName = $address->getTblAddress()->getStreetName();
                    $tblPerson->StreetNumber = $address->getTblAddress()->getStreetNumber();
                    $tblPerson->Code = $address->getTblAddress()->getTblCity()->getCode();
                    $tblPerson->City = $address->getTblAddress()->getTblCity()->getName();

                    $tblPerson->Address = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber().' '.
                        $address->getTblAddress()->getTblCity()->getCode().' '.
                        $address->getTblAddress()->getTblCity()->getName();
                } else {
                    $tblPerson->StreetName = $tblPerson->StreetNumber = $tblPerson->Code = $tblPerson->City = '';
                    $tblPerson->Address = '';
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $tblPerson->Birthday = $common->getTblCommonBirthDates()->getBirthday();
                } else {
                    $tblPerson->Birthday = $tblPerson->Birthplace = '';
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
                    $tblPerson->PhoneNumber = implode(', ', $phoneArray);
                } else {
                    $tblPerson->PhoneNumber = '';
                }
                if (count($mobilePhoneArray) >= 1) {
                    $tblPerson->MobilPhoneNumber = implode(', ', $mobilePhoneArray);
                } else {
                    $tblPerson->MobilPhoneNumber = '';
                }
                $mailAddressList = Mail::useService()->getMailAllByPerson($tblPerson);
                $mailList = array();
                foreach ($mailAddressList as $mailAddress) {
                    $mailList[] = $mailAddress->getTblMail()->getAddress();
                }
                if (count($mailList) >= 1) {
                    $tblPerson->Mail = $mailList[0];
                } else {
                    $tblPerson->Mail = '';
                }
            }
            $Count = count($employeeList);
            $employeeList[$Count - 1]->Woman = $Woman;
            $employeeList[$Count - 1]->Man = $Man;
            $employeeList[$Count - 1]->All = $All;
        }

        return $employeeList;
    }

    /**
     * @param $employeeList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createEmployeeListExcel($employeeList)
    {

        if (!empty( $employeeList )) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "lfd. Nr.");
            $export->setValue($export->getCell("1", "0"), "Anrede");
            $export->setValue($export->getCell("2", "0"), "Vorname");
            $export->setValue($export->getCell("3", "0"), "Nachname");
            $export->setValue($export->getCell("4", "0"), "Geburtstag");
            $export->setValue($export->getCell("5", "0"), "Anschrift");
            $export->setValue($export->getCell("6", "0"), "Telefon Festnetz");
            $export->setValue($export->getCell("7", "0"), "Telefon Mobil");
            $export->setValue($export->getCell("8", "0"), "E-mail");

            $Row = 1;

            foreach ($employeeList as $tblPerson) {

                $export->setValue($export->getCell("0", $Row), $tblPerson->Number);
                $export->setValue($export->getCell("1", $Row), $tblPerson->Salutation);
                /** @var TblPerson $tblPerson */
                $export->setValue($export->getCell("2", $Row), $tblPerson->getFirstName());
                $export->setValue($export->getCell("3", $Row), $tblPerson->getLastName());
                /** @var $tblPerson */
                $export->setValue($export->getCell("4", $Row), $tblPerson->Birthday);
                $export->setValue($export->getCell("5", $Row), $tblPerson->Address);
                $export->setValue($export->getCell("6", $Row), $tblPerson->PhoneNumber);
                $export->setValue($export->getCell("7", $Row), $tblPerson->MobilPhoneNumber);
                $export->setValue($export->getCell("8", $Row), $tblPerson->Mail);

                $Row++;
            }

            $Count = count($employeeList);
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), $employeeList[$Count - 1]->All);
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Frauen:');
            $export->setValue($export->getCell("1", $Row), $employeeList[$Count - 1]->Woman);
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männer:');
            $export->setValue($export->getCell("1", $Row), $employeeList[$Count - 1]->Man);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }
}
