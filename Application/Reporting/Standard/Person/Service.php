<?php
namespace SPHERE\Application\Reporting\Standard\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
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
    public function createFuxClassList()
    {

        // Todo JohK Klassen einbauen
        $studentList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Schüler'));
        if (!empty( $studentList )) {
            $Man = 0;
            $Woman = 0;
            $All = 0;
            foreach ($studentList as $tblPerson) {
                $All++;
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

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $tblPerson->Birthday = $common->getTblCommonBirthDates()->getBirthday();
                    $tblPerson->Birthplace = $common->getTblCommonBirthDates()->getBirthplace();
                } else {
                    $tblPerson->Birthday = $tblPerson->Birthplace = '';
                }

                $tblPerson->StudentNumber = $tblPerson->getId(); //ToDO StudentNumber

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
                if ($father) {
                    $tblPerson->Father = $father->getFullName();    // ToDO Number
                } else {
                    $tblPerson->Father = '';
                }
                if ($mother) {
                    $tblPerson->Mother = $mother->getFullName();    //ToDO Number
                } else {
                    $tblPerson->Mother = '';
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
    public function createFuxClassListExcel($studentList)
    {

        if (!empty( $studentList )) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Name, Vorname");
            $export->setValue($export->getCell("1", "0"), "Geschlecht");
            $export->setValue($export->getCell("2", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("3", "0"), "Geburtsort");
            $export->setValue($export->getCell("4", "0"), "Schülernummer");
            $export->setValue($export->getCell("5", "0"), "Vater");
            $export->setValue($export->getCell("6", "0"), "Mutter");

            $Row = 1;

            foreach ($studentList as $tblPerson) {

                $export->setValue($export->getCell("0", $Row), $tblPerson->Name);
                $export->setValue($export->getCell("1", $Row), $tblPerson->Gender);
                $export->setValue($export->getCell("2", $Row), $tblPerson->Birthday);
                $export->setValue($export->getCell("3", $Row), $tblPerson->Birthplace);
                $export->setValue($export->getCell("4", $Row), $tblPerson->StudentNumber);
                $export->setValue($export->getCell("5", $Row), $tblPerson->Father);
                $export->setValue($export->getCell("6", $Row), $tblPerson->Mother);

                $Row++;
            }

            $Count = count($studentList);
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Alle:');
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

            $Man = 0;
            $Woman = 0;
            $All = 0;

            foreach ($studentList as $tblPerson) {
                $All++;
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
            $export->setValue($export->getCell("0", "0"), "Name, Vorname");
            $export->setValue($export->getCell("1", "0"), "Anschrift");
            $export->setValue($export->getCell("2", "0"), "Geburtsort");
            $export->setValue($export->getCell("3", "0"), "Geburtsdatum");

            $Row = 1;

            foreach ($studentList as $tblPerson) {

                $export->setValue($export->getCell("0", $Row), $tblPerson->Name);
                $export->setValue($export->getCell("1", $Row), $tblPerson->Address);
                $export->setValue($export->getCell("2", $Row), $tblPerson->Birthplace);
                $export->setValue($export->getCell("3", $Row), $tblPerson->Birthday);

                $Row++;
            }

            $Count = count($studentList);
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Alle:');
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

            $Man = 0;
            $Woman = 0;
            $All = 0;

            foreach ($studentList as $tblPerson) {
                $All++;
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
                $tblPerson->KK = $tblPerson->getId(); //ToDO Krankenkasse

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
            $export->setValue($export->getCell("0", "0"), "Name, Vorname");
            $export->setValue($export->getCell("1", "0"), "Anschrift");
            $export->setValue($export->getCell("2", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("2", "1"), "Geburtsort");
            $export->setValue($export->getCell("3", "0"), "Krankenkasse");
            $export->setValue($export->getCell("4", "0"), "1. Sorgeberechtigter");
            $export->setValue($export->getCell("4", "1"), "2. Sorgeberechtigter");
            $export->setValue($export->getCell("5", "0"), "Telefon");
            $export->setValue($export->getCell("5", "1"), "Schüler");
            $export->setValue($export->getCell("6", "0"), "Telefon");
            $export->setValue($export->getCell("6", "1"), "Sorgeberechtigte");

            $Row = 2;

            foreach ($studentList as $tblPerson) {
                $Name = explode('<br/>', $tblPerson->Name);
                $Address = explode('<br/>', $tblPerson->Address);
                $Birthday = explode('<br/>', $tblPerson->Birthday);
                $KK = explode('<br/>', $tblPerson->KK);
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

                for ($i = 0; $i < $count; $i++) {
                    if (isset( $Name[$i] )) {
                        $export->setValue($export->getCell("0", $Row), $Name[$i]);
                    }
                    if (isset( $Address[$i] )) {
                        $export->setValue($export->getCell("1", $Row), $Address[$i]);
                    }
                    if (isset( $Birthday[$i] )) {
                        $export->setValue($export->getCell("2", $Row), $Birthday[$i]);
                    }
                    if (isset( $KK[$i] )) {
                        $export->setValue($export->getCell("3", $Row), $KK[$i]);
                    }
                    if (isset( $Guardian[$i] )) {
                        $export->setValue($export->getCell("4", $Row), $Guardian[$i]);
                    }
                    if (isset( $PhoneNumber[$i] )) {
                        $export->setValue($export->getCell("5", $Row), $PhoneNumber[$i]);
                    }
                    if (isset( $PhoneGuardianNumber[$i] )) {
                        $export->setValue($export->getCell("6", $Row), $PhoneGuardianNumber[$i]);
                    }
                    $Row++;
                }
            }

            $Count = count($studentList);
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Alle:');
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
}
