<?php
namespace SPHERE\Application\Reporting\Custom\Chemnitz\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Search\Group\Group;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Custom\Chemnitz\Person
 */
class Service
{

    /**
     * @param IFormInterface|null $Stage
     * @param null $Select
     * @param string $Redirect
     *
     * @return IFormInterface|Redirect
     */
    public function getClass(IFormInterface $Stage = null, $Select = null, $Redirect)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Select) {
            return $Stage;
        }

        $tblDivision = Division::useService()->getDivisionById($Select['Division']);

        return new Redirect($Redirect, 0, array(
            'DivisionId' => $tblDivision->getId(),
        ));
    }

    /**
     * @param TblDivision $tblDivision
     * @return bool|\SPHERE\Application\People\Person\Service\Entity\TblPerson[]
     */
    public function createClassList(TblDivision $tblDivision)
    {

        $studentList = Division::useService()->getStudentAllByDivision($tblDivision);

        if (!empty($studentList)) {
            foreach ($studentList as $tblPerson) {
                $father = null;
                $mother = null;
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if (($guardian->getTblType()->getId() == 1)
                            && ($guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 1)
                        ) {
                            $father = $guardian->getServiceTblPersonFrom();
                        }
                        if (($guardian->getTblType()->getId() == 1)
                            && ($guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 2)
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
                $tblPerson->Father = $fatherFirstName = $father !== null ? $father->getFirstName() : '';
                $tblPerson->Mother = $mother !== null ? $mother->getFirstName() : '';

                if ($address !== null) {
                    $tblPerson->StreetName = $address->getTblAddress()->getStreetName();
                    $tblPerson->StreetNumber = $address->getTblAddress()->getStreetNumber();
                    $tblPerson->City = $address->getTblAddress()->getTblCity()->getCode()
                        . ' ' . $address->getTblAddress()->getTblCity()->getName();

                    $tblPerson->Address = $address->getTblAddress()->getStreetName() . ' ' .
                        $address->getTblAddress()->getStreetNumber() . ' ' .
                        $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getName();
                } else {
                    $tblPerson->StreetName = $tblPerson->StreetNumber = $tblPerson->City = '';
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

        if (!empty($studentList)) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anrede");
            $export->setValue($export->getCell("1", "0"), "Vorname V.");
            $export->setValue($export->getCell("2", "0"), "Vorname M.");
            $export->setValue($export->getCell("3", "0"), "Name");
            $export->setValue($export->getCell("4", "0"), "Konfession");
            $export->setValue($export->getCell("5", "0"), "Straße");
            $export->setValue($export->getCell("6", "0"), "Hausnr.");
            $export->setValue($export->getCell("7", "0"), "PLZ Ort");
            $export->setValue($export->getCell("8", "0"), "Schüler");
            $export->setValue($export->getCell("9", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("10", "0"), "Geburtsort");

            $Row = 1;
            foreach ($studentList as $tblPerson) {

                $export->setValue($export->getCell("0", $Row), $tblPerson->Salutation);
                $export->setValue($export->getCell("1", $Row), $tblPerson->Father);
                $export->setValue($export->getCell("2", $Row), $tblPerson->Mother);
                $export->setValue($export->getCell("3", $Row), $tblPerson->getLastName());
                $export->setValue($export->getCell("4", $Row), $tblPerson->Denomination);
                $export->setValue($export->getCell("5", $Row), $tblPerson->StreetName);
                $export->setValue($export->getCell("6", $Row), $tblPerson->StreetNumber);
                $export->setValue($export->getCell("7", $Row), $tblPerson->City);
                $export->setValue($export->getCell("8", $Row), $tblPerson->getFirstName());
                $export->setValue($export->getCell("9", $Row), $tblPerson->Birthday);
                $export->setValue($export->getCell("10", $Row), $tblPerson->Birthplace);

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
    public function createStaffList()
    {

        $staffList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Mitarbeiter'));

        if (!empty($staffList)) {
            foreach ($staffList as $tblPerson) {

                $common = Common::useService()->getCommonByPerson($tblPerson);

                if ($addressList = Address::useService()->getAddressAllByPerson($tblPerson)) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }

                $tblPerson->Salutation = $tblPerson->getSalutation();
                if ($common) {
                    $tblPerson->Birthday = $common->getTblCommonBirthDates()->getBirthday();
                } else {
                    $tblPerson->Birthday = '';
                }
                if ($address !== null) {
                    $tblPerson->StreetName = $address->getTblAddress()->getStreetName();
                    $tblPerson->StreetNumber = $address->getTblAddress()->getStreetNumber();
                    $tblPerson->Code = $address->getTblAddress()->getTblCity()->getCode();
                    $tblPerson->City = $address->getTblAddress()->getTblCity()->getName();

                    $tblPerson->Address = $address->getTblAddress()->getStreetName() . ' ' .
                        $address->getTblAddress()->getStreetNumber() . ' ' .
                        $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getName();
                } else {
                    $tblPerson->StreetName = $tblPerson->StreetNumber = $tblPerson->City = $tblPerson->Code = '';
                    $tblPerson->Address = '';
                }

                // Todo JohK Unterbereich ermitteln über Gruppen
                $tblPerson->Division = '';

                $tblPerson->Phone1 = $tblPerson->Phone2 = $tblPerson->Mail = '';
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($phoneList) {
                    if (count($phoneList) > 0) {
                        $tblPerson->Phone1 = $phoneList[0]->getTblPhone()->getNumber();
                    }
                    if (count($phoneList) > 1) {
                        $tblPerson->Phone2 = $phoneList[1]->getTblPhone()->getNumber();
                    }
                }
                $mailList = Mail::useService()->getMailAllByPerson($tblPerson);
                if ($mailList) {
                    $tblPerson->Mail = $mailList[0]->getTblMail()->getAddress();
                }
            }
        }

        return $staffList;
    }

    /**
     * @param $staffList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createStaffListExcel($staffList)
    {

        if (!empty($staffList)) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anrede");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Name");
            $export->setValue($export->getCell("3", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("4", "0"), "Unterbereich");
            $export->setValue($export->getCell("5", "0"), "Straße");
            $export->setValue($export->getCell("6", "0"), "Hausnr.");
            $export->setValue($export->getCell("7", "0"), "PLZ");
            $export->setValue($export->getCell("8", "0"), "Ort");
            $export->setValue($export->getCell("9", "0"), "Telefon 1");
            $export->setValue($export->getCell("10", "0"), "Telefon 2");
            $export->setValue($export->getCell("11", "0"), "Mail");

            $Row = 1;
            foreach ($staffList as $tblPerson) {

                $export->setValue($export->getCell("0", $Row), $tblPerson->Salutation);
                $export->setValue($export->getCell("1", $Row), $tblPerson->getFirstName());
                $export->setValue($export->getCell("2", $Row), $tblPerson->getLastName());
                $export->setValue($export->getCell("3", $Row), $tblPerson->Birthday);
                $export->setValue($export->getCell("4", $Row), $tblPerson->Division);
                $export->setValue($export->getCell("5", $Row), $tblPerson->StreetName);
                $export->setValue($export->getCell("6", $Row), $tblPerson->StreetNumber);
                $export->setValue($export->getCell("7", $Row), $tblPerson->Code);
                $export->setValue($export->getCell("8", $Row), $tblPerson->City);
                $export->setValue($export->getCell("9", $Row), $tblPerson->Phone1);
                $export->setValue($export->getCell("10", $Row), $tblPerson->Phone2);
                $export->setValue($export->getCell("11", $Row), $tblPerson->Mail);

                $Row++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|\SPHERE\Application\People\Person\Service\Entity\TblPerson[]
     */
    public function createMedicList(TblDivision $tblDivision)
    {

        $studentList = Division::useService()->getStudentAllByDivision($tblDivision);

        if (!empty($studentList)) {
            foreach ($studentList as $tblPerson) {

                if ($addressList = Address::useService()->getAddressAllByPerson($tblPerson)) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $tblPerson->Birthday = $common->getTblCommonBirthDates()->getBirthday();
                } else {
                    $tblPerson->Birthday = '';
                }
                if ($address !== null) {
                    $tblPerson->StreetName = $address->getTblAddress()->getStreetName();
                    $tblPerson->StreetNumber = $address->getTblAddress()->getStreetNumber();
                    $tblPerson->Code = $address->getTblAddress()->getTblCity()->getCode();
                    $tblPerson->City = $address->getTblAddress()->getTblCity()->getName();
                    $tblPerson->Address = $address->getTblAddress()->getStreetName() . ' ' .
                        $address->getTblAddress()->getStreetNumber() . ' ' .
                        $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getName();
                } else {
                    $tblPerson->StreetName = $tblPerson->StreetNumber = $tblPerson->City = $tblPerson->Code = '';
                    $tblPerson->Address = '';
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
    public function createMedicListExcel($studentList)
    {

        if (!empty($studentList)) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Name");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("3", "0"), "Straße");
            $export->setValue($export->getCell("4", "0"), "Hausnr.");
            $export->setValue($export->getCell("5", "0"), "PLZ");
            $export->setValue($export->getCell("6", "0"), "Wohnort");

            $Row = 1;
            foreach ($studentList as $tblPerson) {

                $export->setValue($export->getCell("0", $Row), $tblPerson->getLastName());
                $export->setValue($export->getCell("1", $Row), $tblPerson->getFirstName());
                $export->setValue($export->getCell("2", $Row), $tblPerson->Birthday);
                $export->setValue($export->getCell("3", $Row), $tblPerson->StreetName);
                $export->setValue($export->getCell("4", $Row), $tblPerson->StreetNumber);
                $export->setValue($export->getCell("5", $Row), $tblPerson->Code);
                $export->setValue($export->getCell("6", $Row), $tblPerson->City);

                $Row++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|\SPHERE\Application\People\Person\Service\Entity\TblPerson[]
     */
    public function createParentTeacherConferenceList(TblDivision $tblDivision)
    {

        $studentList = Division::useService()->getStudentAllByDivision($tblDivision);

        if (!empty($studentList)) {
            foreach ($studentList as $tblPerson) {
                $tblPerson->Attendance = '';
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
    public function createParentTeacherConferenceListExcel($studentList)
    {

        if (!empty($studentList)) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Name");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Anwesenheit");

            $Row = 1;
            foreach ($studentList as $tblPerson) {

                $export->setValue($export->getCell("0", $Row), $tblPerson->getLastName());
                $export->setValue($export->getCell("1", $Row), $tblPerson->getFirstName());

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
    public function createClubMemberList()
    {
        $clubGroup = Group::useService()->getGroupByName('Verein');
        if ($clubGroup) {
            $clubMemberList = Group::useService()->getPersonAllByGroup($clubGroup);
            if ($clubMemberList) {
                foreach ($clubMemberList as $tblPerson) {

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

                        $tblPerson->Address = $address->getTblAddress()->getStreetName() . ' ' .
                            $address->getTblAddress()->getStreetNumber() . ' ' .
                            $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                            $address->getTblAddress()->getTblCity()->getName();
                    } else {
                        $tblPerson->StreetName = $tblPerson->StreetNumber = $tblPerson->Code = $tblPerson->City = '';
                        $tblPerson->Address = '';
                    }

                    $tblPerson->Phone = $tblPerson->Mail = '';
                    $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                    if ($phoneList) {
                        $tblPerson->Phone = $phoneList[0]->getTblPhone()->getNumber();
                    }
                    $mailList = Mail::useService()->getMailAllByPerson($tblPerson);
                    if ($mailList) {
                        $tblPerson->Mail = $mailList[0]->getTblMail()->getAddress();
                    }

                    $tblPerson->Directorate = '';
                }
            }

            return $clubMemberList;
        }

        return false;
    }

    /**
     * @param $clubMemberList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createClubMemberListExcel($clubMemberList)
    {

        if (!empty($clubMemberList)) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Anrede");
            $export->setValue($export->getCell("1", "0"), "Vorname");
            $export->setValue($export->getCell("2", "0"), "Name");
            $export->setValue($export->getCell("3", "0"), "Straße");
            $export->setValue($export->getCell("4", "0"), "Hausnr.");
            $export->setValue($export->getCell("5", "0"), "PLZ");
            $export->setValue($export->getCell("6", "0"), "Ort");
            $export->setValue($export->getCell("7", "0"), "Telefon");
            $export->setValue($export->getCell("8", "0"), "Mail");
            $export->setValue($export->getCell("9", "0"), "Vorstand");

            $Row = 1;
            foreach ($clubMemberList as $tblPerson) {

                $export->setValue($export->getCell("0", $Row), $tblPerson->Salutation);
                $export->setValue($export->getCell("1", $Row), $tblPerson->getFirstName());
                $export->setValue($export->getCell("2", $Row), $tblPerson->getLastName());
                $export->setValue($export->getCell("3", $Row), $tblPerson->StreetName);
                $export->setValue($export->getCell("4", $Row), $tblPerson->StreetNumber);
                $export->setValue($export->getCell("5", $Row), $tblPerson->Code);
                $export->setValue($export->getCell("6", $Row), $tblPerson->City);
                $export->setValue($export->getCell("7", $Row), $tblPerson->Phone);
                $export->setValue($export->getCell("8", $Row), $tblPerson->Mail);
                $export->setValue($export->getCell("9", $Row), $tblPerson->Directorate);

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
    public function createInterestedPersonList()
    {

        $interestedPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Interessent'));

        if (!empty($interestedPersonList)) {
            foreach ($interestedPersonList as $tblPerson) {

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

                    $tblPerson->Address = $address->getTblAddress()->getStreetName() . ' ' .
                        $address->getTblAddress()->getStreetNumber() . ' ' .
                        $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getName();
                } else {
                    $tblPerson->StreetName = $tblPerson->StreetNumber = $tblPerson->Code = $tblPerson->City = '';
                    $tblPerson->Address = '';
                }

                $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                if ($tblProspect) {
                    $tblProspectReservation = $tblProspect->getTblProspectReservation();
                    if ($tblProspectReservation) {
                        $tblPerson->SchoolYear = $tblProspectReservation->getReservationYear();
                        if ($tblProspectReservation->getServiceTblTypeOptionA()) {
                            $tblPerson->TypeOptionA = $tblProspectReservation->getServiceTblTypeOptionA()->getName();
                        } else {
                            $tblPerson->TypeOptionA = '';
                        }
                        if ($tblProspectReservation->getServiceTblTypeOptionB()) {
                            $tblPerson->TypeOptionB = $tblProspectReservation->getServiceTblTypeOptionB()->getName();
                        } else {
                            $tblPerson->TypeOptionB = '';
                        }
                        if ($tblProspectReservation->getReservationDivision()) {
                            $tblPerson->DivisionLevel = $tblProspectReservation->getReservationDivision();
                        } else {
                            $tblPerson->DivisionLevel = '';
                        }
                    }
                    $tblProspectAppointment = $tblProspect->getTblProspectAppointment();
                    if ($tblProspectAppointment) {
                        $tblPerson->RegistrationDate = $tblProspectAppointment->getReservationDate();
                    } else {
                        $tblPerson->RegistrationDate = '';
                    }
                } else {
                    $tblPerson->SchoolYear = $tblPerson->TypeOptionA = $tblPerson->TypeOptionB
                        = $tblPerson->RegistrationDate = $tblPerson->DivisionLevel = '';
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $tblPerson->Denomination = $common->getTblCommonInformation()->getDenomination();
                    $tblPerson->Birthday = $common->getTblCommonBirthDates()->getBirthday();
                    $tblPerson->Birthplace = $common->getTblCommonBirthDates()->getBirthplace();
                    $tblPerson->Nationality = $common->getTblCommonInformation()->getNationality();
                } else {
                    $tblPerson->Birthday = $tblPerson->Birthplace = $tblPerson->Denomination =
                    $tblPerson->Nationality = '';
                }

                $tblPerson->Siblings = '';
                $relationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if (!empty($relationshipList)) {
                    foreach ($relationshipList as $relationship) {
                        if ($relationship->getTblType()->getName() == 'Geschwisterkind') {
                            if ($relationship->getServiceTblPersonFrom()->getId() == $tblPerson->getId()) {
                                $tblPerson->Siblings .= $relationship->getServiceTblPersonTo()->getFullName() . ' ';
                            } else {
                                $tblPerson->Siblings .= $relationship->getServiceTblPersonFrom()->getFullName() . ' ';
                            }
                        }
                    }
                    $tblPerson->Siblings = trim($tblPerson->Siblings);
                }

                $tblPerson->Hoard = 'Nein';
                $groupList = Group::useService()->getGroupAllByPerson($tblPerson);
                if (!empty($groupList)) {
                    foreach ($groupList as $group) {
                        if ($group->getName() == 'Hort') {
                            $tblPerson->Hoard = 'Ja';
                        }
                    }
                }

                $father = null;
                $mother = null;
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if (($guardian->getTblType()->getId() == 1)
                            && ($guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 1)
                        ) {
                            $father = $guardian->getServiceTblPersonFrom();
                        }
                        if (($guardian->getTblType()->getId() == 1)
                            && ($guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 2)
                        ) {
                            $mother = $guardian->getServiceTblPersonFrom();
                        }
                    }
                }
                if ($father !== null) {
                    $tblPerson->FatherSalutation = $father->getSalutation();
                    $tblPerson->FatherLastName = $father->getLastName();
                    $tblPerson->FatherFirstName = $father->getFirstName();
                    $tblPerson->Father = $father->getFullName();
                } else {
                    $tblPerson->FatherSalutation = $tblPerson->FatherLastName = $tblPerson->FatherFirstName = '';
                    $tblPerson->Father = '';
                }
                if ($mother !== null) {
                    $tblPerson->MotherSalutation = $mother->getSalutation();
                    $tblPerson->MotherLastName = $mother->getLastName();
                    $tblPerson->MotherFirstName = $mother->getFirstName();
                    $tblPerson->Mother = $mother->getFullName();
                } else {
                    $tblPerson->MotherSalutation = $tblPerson->MotherLastName = $tblPerson->MotherFirstName = '';
                    $tblPerson->Mother = '';
                }
            }
        }

        return $interestedPersonList;
    }

    /**
     * @param $interestedPersonList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createInterestedPersonListExcel($interestedPersonList)
    {

        if (!empty($interestedPersonList)) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
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
            $export->setValue($export->getCell("11", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("12", "0"), "Geburtsort");
            $export->setValue($export->getCell("13", "0"), "Staatsangeh.");
            $export->setValue($export->getCell("14", "0"), "Bekenntnis");
            $export->setValue($export->getCell("15", "0"), "Geschwister");
            $export->setValue($export->getCell("16", "0"), "Hort");
            $export->setValue($export->getCell("17", "0"), "Anrede V");
            $export->setValue($export->getCell("18", "0"), "Name V");
            $export->setValue($export->getCell("19", "0"), "Vorname V");
            $export->setValue($export->getCell("20", "0"), "Anrede M");
            $export->setValue($export->getCell("21", "0"), "Name M");
            $export->setValue($export->getCell("22", "0"), "Vorname M");

            $Row = 1;
            foreach ($interestedPersonList as $tblPerson) {

                $export->setValue($export->getCell("0", $Row), $tblPerson->RegistrationDate);
                $export->setValue($export->getCell("1", $Row), $tblPerson->getFirstName());
                $export->setValue($export->getCell("2", $Row), $tblPerson->getLastName());
                $export->setValue($export->getCell("3", $Row), $tblPerson->SchoolYear);
                $export->setValue($export->getCell("4", $Row), $tblPerson->DivisionLevel);
                $export->setValue($export->getCell("5", $Row), $tblPerson->TypeOptionA);
                $export->setValue($export->getCell("6", $Row), $tblPerson->TypeOptionB);
                $export->setValue($export->getCell("7", $Row), $tblPerson->StreetName);
                $export->setValue($export->getCell("8", $Row), $tblPerson->StreetNumber);
                $export->setValue($export->getCell("9", $Row), $tblPerson->Code);
                $export->setValue($export->getCell("10", $Row), $tblPerson->City);
                $export->setValue($export->getCell("11", $Row), $tblPerson->Birthday);
                $export->setValue($export->getCell("12", $Row), $tblPerson->Birthplace);
                $export->setValue($export->getCell("13", $Row), $tblPerson->Nationality);
                $export->setValue($export->getCell("14", $Row), $tblPerson->Denomination);
                $export->setValue($export->getCell("15", $Row), $tblPerson->Siblings);
                $export->setValue($export->getCell("16", $Row), $tblPerson->Hoard);
                $export->setValue($export->getCell("17", $Row), $tblPerson->FatherSalutation);
                $export->setValue($export->getCell("18", $Row), $tblPerson->FatherLastName);
                $export->setValue($export->getCell("19", $Row), $tblPerson->FatherFirstName);
                $export->setValue($export->getCell("20", $Row), $tblPerson->MotherSalutation);
                $export->setValue($export->getCell("21", $Row), $tblPerson->MotherLastName);
                $export->setValue($export->getCell("22", $Row), $tblPerson->MotherFirstName);

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
    public function createSchoolFeeList()
    {

        $studentList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Schüler'));

        if (!empty($studentList)) {
            foreach ($studentList as $tblPerson) {

                if ($debtorList = Banking::useService()->getDebtorAllByPerson($tblPerson)) {
                    $tblPerson->DebtorNumber = $debtorList[0]->getDebtorNumber();
                } else {
                    $tblPerson->DebtorNumber = '';
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

                    $tblPerson->Address = $address->getTblAddress()->getStreetName() . ' ' .
                        $address->getTblAddress()->getStreetNumber() . ' ' .
                        $address->getTblAddress()->getTblCity()->getCode() . ' ' .
                        $address->getTblAddress()->getTblCity()->getName();
                } else {
                    $tblPerson->StreetName = $tblPerson->StreetNumber = $tblPerson->Code = $tblPerson->City = '';
                    $tblPerson->Address = '';
                }

                $father = null;
                $mother = null;
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if (($guardian->getTblType()->getId() == 1)
                            && ($guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 1)
                        ) {
                            $father = $guardian->getServiceTblPersonFrom();
                        }
                        if (($guardian->getTblType()->getId() == 1)
                            && ($guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 2)
                        ) {
                            $mother = $guardian->getServiceTblPersonFrom();
                        }
                    }
                }
                if ($father !== null) {
                    $tblPerson->FatherSalutation = $father->getSalutation();
                    $tblPerson->FatherLastName = $father->getLastName();
                    $tblPerson->FatherFirstName = $father->getFirstName();
                    $tblPerson->Father = $father->getFullName();
                } else {
                    $tblPerson->FatherSalutation = $tblPerson->FatherLastName = $tblPerson->FatherFirstName = '';
                    $tblPerson->Father = '';
                }
                if ($mother !== null) {
                    $tblPerson->MotherSalutation = $mother->getSalutation();
                    $tblPerson->MotherLastName = $mother->getLastName();
                    $tblPerson->MotherFirstName = $mother->getFirstName();
                    $tblPerson->Mother = $mother->getFullName();
                } else {
                    $tblPerson->MotherSalutation = $tblPerson->MotherLastName = $tblPerson->MotherFirstName = '';
                    $tblPerson->Mother = '';
                }

                $tblPerson->Reply = $tblPerson->Records = $tblPerson->LastSchoolFee = $tblPerson->Remarks = '';
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
    public function createSchoolFeeListExcel($studentList)
    {

        if (!empty($studentList)) {

            $fileLocation = Storage::useWriter()->getTemporary('xls');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Deb.-Nr.");
            $export->setValue($export->getCell("1", "0"), "Bescheid geschickt");
            $export->setValue($export->getCell("2", "0"), "Anrede V");
            $export->setValue($export->getCell("3", "0"), "Name V");
            $export->setValue($export->getCell("4", "0"), "Vorname V");
            $export->setValue($export->getCell("5", "0"), "Anrede M");
            $export->setValue($export->getCell("6", "0"), "Name M");
            $export->setValue($export->getCell("7", "0"), "Vorname M");
            $export->setValue($export->getCell("8", "0"), "Unterlagen eingereicht");
            $export->setValue($export->getCell("9", "0"), "SG Vorjahr");
            $export->setValue($export->getCell("10", "0"), "1.Kind");
            $export->setValue($export->getCell("11", "0"), "GS/MS/GY");
            $export->setValue($export->getCell("12", "0"), "Klasse");
            $export->setValue($export->getCell("13", "0"), "2.Kind");
            $export->setValue($export->getCell("14", "0"), "GS/MS/GY");
            $export->setValue($export->getCell("15", "0"), "Klasse");
            $export->setValue($export->getCell("16", "0"), "3.Kind");
            $export->setValue($export->getCell("17", "0"), "GS/MS/GY");
            $export->setValue($export->getCell("18", "0"), "Klasse");
            $export->setValue($export->getCell("19", "0"), "4.Kind");
            $export->setValue($export->getCell("20", "0"), "GS/MS/GY");
            $export->setValue($export->getCell("21", "0"), "Klasse");
            $export->setValue($export->getCell("22", "0"), "Bemerkungen");
            $export->setValue($export->getCell("23", "0"), "Straße");
            $export->setValue($export->getCell("24", "0"), "Hausnummer");
            $export->setValue($export->getCell("25", "0"), "PLZ");
            $export->setValue($export->getCell("26", "0"), "Ort");

            $Row = 1;
            foreach ($studentList as $tblPerson) {

                $export->setValue($export->getCell("0", $Row), $tblPerson->DebtorNumber);

                $export->setValue($export->getCell("2", $Row), $tblPerson->FatherSalutation);
                $export->setValue($export->getCell("3", $Row), $tblPerson->FatherLastName);
                $export->setValue($export->getCell("4", $Row), $tblPerson->FatherFirstName);
                $export->setValue($export->getCell("5", $Row), $tblPerson->MotherSalutation);
                $export->setValue($export->getCell("6", $Row), $tblPerson->MotherLastName);
                $export->setValue($export->getCell("7", $Row), $tblPerson->MotherFirstName);

                $export->setValue($export->getCell("23", $Row), $tblPerson->StreetName);
                $export->setValue($export->getCell("24", $Row), $tblPerson->StreetNumber);
                $export->setValue($export->getCell("25", $Row), $tblPerson->Code);
                $export->setValue($export->getCell("26", $Row), $tblPerson->City);

                $Row++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }
}
