<?php
namespace SPHERE\Application\Reporting\Custom\Chemnitz\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Exception\Repository\TypeFileException;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType as TblTypeSchool;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Reporting\Standard\Person\Person as PersonStandard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Custom\Chemnitz\Person
 */
class Service extends Extension
{

    /**
     * @param TblPerson[] $tblPersonList
     *
     * @return array
     */
    public function createClassList(array $tblPersonList)
    {

        $TableContent = array();
        if (!empty($tblPersonList)) {
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {
                $item['Number'] = $count++;
                $item['Salutation'] = $tblPerson->getSalutation();
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['StreetName'] = $item['StreetNumber'] = $item['City'] = $item['Address'] = $item['District'] = '';
                $item['Denomination'] = $tblPerson->getDenominationString();
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['Birthplace'] = $tblPerson->getBirthplaceString();
                $item['FirstNameS1'] = '';
                $item['FirstNameS2'] = '';
                // Address
                $item = PersonStandard::useService()->getAddressDataFromPerson($tblPerson, $item);
                // Custody
                $tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN);
                if (($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))) {
                    foreach ($tblToPersonList as $tblToPerson) {
                        if($tblToPerson->getRanking() == 1 && ($tblPersonG = $tblToPerson->getServiceTblPersonFrom())) {
                            $item['FirstNameS1'] = $tblPersonG->getFirstName();
                        }
                        if($tblToPerson->getRanking() == 2 && ($tblPersonG = $tblToPerson->getServiceTblPersonFrom())) {
                            $item['FirstNameS2'] = $tblPersonG->getFirstName();
                        }
                    }
                }
                array_push($TableContent, $item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $TableContent
     * @param array $tblPersonList
     *
     * @return false|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createClassListExcel($TableContent, $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = $row = 0;
        $export->setValue($export->getCell($column++, $row), "Anrede");
        $export->setValue($export->getCell($column++, $row), "Vorname Sorgeberechtigter 1");
        $export->setValue($export->getCell($column++, $row), "Vorname Sorgeberechtigter 2");
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Konfession");
        $export->setValue($export->getCell($column++, $row), "Straße");
        $export->setValue($export->getCell($column++, $row), "Hausnr.");
        $export->setValue($export->getCell($column++, $row), "PLZ");
        $export->setValue($export->getCell($column++, $row), "Ort");
        $export->setValue($export->getCell($column++, $row), "Ortsteil");
        $export->setValue($export->getCell($column++, $row), "Schüler");
        $export->setValue($export->getCell($column++, $row), "Geburtsdatum");
        $export->setValue($export->getCell($column, $row++), "Geburtsort");
        foreach ($TableContent as $TableRow) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $TableRow['Salutation']);
            $export->setValue($export->getCell($column++, $row), $TableRow['FirstNameS1']);
            $export->setValue($export->getCell($column++, $row), $TableRow['FirstNameS2']);
            $export->setValue($export->getCell($column++, $row), $TableRow['LastName']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Denomination']);
            $export->setValue($export->getCell($column++, $row), $TableRow['StreetName']);
            $export->setValue($export->getCell($column++, $row), $TableRow['StreetNumber']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Code']);
            $export->setValue($export->getCell($column++, $row), $TableRow['City']);
            $export->setValue($export->getCell($column++, $row), $TableRow['District']);
            $export->setValue($export->getCell($column++, $row), $TableRow['FirstName']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Birthday']);
            $export->setValue($export->getCell($column, $row++), $TableRow['Birthplace']);
        }
        $row++;
        PersonStandard::setGenderFooter($export, $tblPersonList, $row);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @return array
     */
    public function createStaffList()
    {

        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Mitarbeiter'));
        $TableContent = array();
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {
                $item['Salutation'] = $tblPerson->getSalutation();
                $item['Title'] = $tblPerson->getTitle();
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['StreetName'] = $item['StreetNumber'] = $item['City'] = $item['Code'] = $item['District'] = '';
                $item['Address'] = '';
                $item['Division'] = '';
                $item['Phone1'] = $item['Phone2'] = $item['Mail'] = '';
                // Address
                $item = PersonStandard::useService()->getAddressDataFromPerson($tblPerson, $item);
                // Phone
                if(($tblToPersonPList = Phone::useService()->getPhoneAllByPerson($tblPerson))){
                    foreach($tblToPersonPList as $tblToPersonP){
                        if(($tblPhone = $tblToPersonP->getTblPhone())){
                            if($item['Phone1'] === ''){
                                $item['Phone1'] = $tblPhone->getNumber();
                            } elseif($item['Phone2'] === ''){
                                $item['Phone2'] = $tblPhone->getNumber();
                            } else {
                                break;
                            }
                        }
                    }
                }
                //Mail
                if (($tblToPersonMList = Mail::useService()->getMailAllByPerson($tblPerson))) {
                    foreach($tblToPersonMList as $tblToPersonM){
                        if($item['Mail'] === ''){
                            if(($tblMail = $tblToPersonM->getTblMail())){
                                $item['Mail'] = $tblMail->getAddress();
                            }
                        } else {
                            break;
                        }
                    }
                }
                array_push($TableContent, $item);
            });
        }
        return $TableContent;
    }

    /**
     * @param array $TableContent
     * @param array $tblPersonList
     *
     * @return false|FilePointer
     */
    public function createStaffListExcel($TableContent, $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = $row = 0;
        $export->setValue($export->getCell($column++, $row), "Anrede");
        $export->setValue($export->getCell($column++, $row), "Titel");
        $export->setValue($export->getCell($column++, $row), "Vorname");
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Geburtsdatum");
        $export->setValue($export->getCell($column++, $row), "Unterbereich");
        $export->setValue($export->getCell($column++, $row), "Straße");
        $export->setValue($export->getCell($column++, $row), "Hausnr.");
        $export->setValue($export->getCell($column++, $row), "PLZ");
        $export->setValue($export->getCell($column++, $row), "Ort");
        $export->setValue($export->getCell($column++, $row), "Ortsteil");
        $export->setValue($export->getCell($column++, $row), "Telefon 1");
        $export->setValue($export->getCell($column++, $row), "Telefon 2");
        $export->setValue($export->getCell($column, $row++), "Mail");
        foreach ($TableContent as $TableRow) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $TableRow['Salutation']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Title']);
            $export->setValue($export->getCell($column++, $row), $TableRow['FirstName']);
            $export->setValue($export->getCell($column++, $row), $TableRow['LastName']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Birthday']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Division']);
            $export->setValue($export->getCell($column++, $row), $TableRow['StreetName']);
            $export->setValue($export->getCell($column++, $row), $TableRow['StreetNumber']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Code']);
            $export->setValue($export->getCell($column++, $row), $TableRow['City']);
            $export->setValue($export->getCell($column++, $row), $TableRow['District']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Phone1']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Phone2']);
            $export->setValue($export->getCell($column, $row++), $TableRow['Mail']);
        }
        $row++;
        PersonStandard::setGenderFooter($export, $tblPersonList, $row);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param array|TblPerson[] $tblPersonList
     *
     * @return array
     */
    public function createSchoolFeeList($tblPersonList = array())
    {

        $TableContent = array();
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                //Sortierung
//                $item['FirstName'] = $tblPerson->getId();
                $item['FirstName'] = $tblPerson->getFirstName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['DebtorNumber'] = Debtor::useService()->getDebtorNumberStringByPerson($tblPerson);
                $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = '';
                $item['Address'] = '';
                for($i = 1; $i <= 2; $i++){
                    $item['SalutationS'.$i] = $item['TitleS'.$i] = $item['FirstNameS'.$i] = $item['LastNameS'.$i] = $item['FullNameS'.$i] = '';
                }
                $item['Reply'] = $item['Records'] = $item['LastSchoolFee'] = $item['Remarks'] = '';
                // Address
                $item = PersonStandard::useService()->getAddressDataFromPerson($tblPerson, $item);
                // Custody
                $tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN);
                if (($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))) {
                    foreach ($tblToPersonList as $tblToPerson) {
                        if(($tblPersonG = $tblToPerson->getServiceTblPersonFrom())) {
                            $item['SalutationS'.$tblToPerson->getRanking()] = $tblPersonG->getSalutation();
                            $item['TitleS'.$tblToPerson->getRanking()] = $tblPersonG->getTitle();
                            $item['FirstNameS'.$tblToPerson->getRanking()] = $tblPersonG->getFirstSecondName();
                            $item['LastNameS'.$tblToPerson->getRanking()] = $tblPersonG->getLastName();
                            $item['FullNameS'.$tblToPerson->getRanking()] = $tblPersonG->getFullName();
                            if(($DebtorNumber = Debtor::useService()->getDebtorNumberStringByPerson($tblPersonG))){
                                if($item['DebtorNumber']){
                                    $item['DebtorNumber'] .= '; '.$DebtorNumber;
                                } else {
                                    $item['DebtorNumber'] = $DebtorNumber;
                                }
                            }
                        }
                    }
                }
                array_push($TableContent, $item);
            });
        }
        return $TableContent;
    }



    /**
     * @param array $TableContent
     * @param array $tblPersonList
     *
     * @return false|FilePointer
     */
    public function createSchoolFeeListExcel($TableContent, $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = $row = 0;
        $export->setValue($export->getCell($column++, $row), "Deb.-Nr.");
        $export->setValue($export->getCell($column++, $row), "Bescheid geschickt");
        $export->setValue($export->getCell($column++, $row), "Anrede Sorgeberechtigter 1");
        $export->setValue($export->getCell($column++, $row), "Titel Sorgeberechtigter 1");
        $export->setValue($export->getCell($column++, $row), "Name Sorgeberechtigter 1");
        $export->setValue($export->getCell($column++, $row), "Vorname Sorgeberechtigter 1");
        $export->setValue($export->getCell($column++, $row), "Anrede Sorgeberechtigter 2");
        $export->setValue($export->getCell($column++, $row), "Titel Sorgeberechtigter 2");
        $export->setValue($export->getCell($column++, $row), "Name Sorgeberechtigter 2");
        $export->setValue($export->getCell($column++, $row), "Vorname Sorgeberechtigter 2");
        $export->setValue($export->getCell($column++, $row), "Unterlagen eingereicht");
        $export->setValue($export->getCell($column++, $row), "SG Vorjahr");
        $export->setValue($export->getCell($column++, $row), "1.Kind");
        $export->setValue($export->getCell($column++, $row), "GS/MS/GY");
        $export->setValue($export->getCell($column++, $row), "Klasse");
        $export->setValue($export->getCell($column++, $row), "2.Kind");
        $export->setValue($export->getCell($column++, $row), "GS/MS/GY");
        $export->setValue($export->getCell($column++, $row), "Klasse");
        $export->setValue($export->getCell($column++, $row), "3.Kind");
        $export->setValue($export->getCell($column++, $row), "GS/MS/GY");
        $export->setValue($export->getCell($column++, $row), "Klasse");
        $export->setValue($export->getCell($column++, $row), "4.Kind");
        $export->setValue($export->getCell($column++, $row), "GS/MS/GY");
        $export->setValue($export->getCell($column++, $row), "Klasse");
        $export->setValue($export->getCell($column++, $row), "Bemerkungen");
        $export->setValue($export->getCell($column++, $row), "Straße");
        $export->setValue($export->getCell($column++, $row), "Hausnummer");
        $export->setValue($export->getCell($column++, $row), "PLZ");
        $export->setValue($export->getCell($column++, $row), "Ort");
        $export->setValue($export->getCell($column, $row++), "Ortsteil");
        foreach ($TableContent as $TableRow) {
            $column = 0;
            $export->setValue($export->getCell($column, $row), $TableRow['DebtorNumber']);
            $column = 2;
            $export->setValue($export->getCell($column++, $row), $TableRow['SalutationS1']);
            $export->setValue($export->getCell($column++, $row), $TableRow['TitleS1']);
            $export->setValue($export->getCell($column++, $row), $TableRow['LastNameS1']);
            $export->setValue($export->getCell($column++, $row), $TableRow['FirstNameS1']);
            $export->setValue($export->getCell($column++, $row), $TableRow['SalutationS2']);
            $export->setValue($export->getCell($column++, $row), $TableRow['TitleS2']);
            $export->setValue($export->getCell($column++, $row), $TableRow['LastNameS2']);
            $export->setValue($export->getCell($column, $row), $TableRow['FirstNameS2']);
            $column = 25;
            $export->setValue($export->getCell($column++, $row), $TableRow['StreetName']);
            $export->setValue($export->getCell($column++, $row), $TableRow['StreetNumber']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Code']);
            $export->setValue($export->getCell($column++, $row), $TableRow['City']);
            $export->setValue($export->getCell($column, $row++), $TableRow['District']);
        }
        $row++;
        PersonStandard::setGenderFooter($export, $tblPersonList, $row);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createMedicList(TblDivisionCourse $tblDivisionCourse)
    {

        $TableContent = array();
        if (!empty($tblPersonList = $tblDivisionCourse->getStudents())) {
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {
                $item['Number'] = $count++;
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['StreetName'] = $item['StreetNumber'] = $item['City'] = $item['Code'] = $item['District'] = '';
                $item['Address'] = '';
                // Address
                $item = PersonStandard::useService()->getAddressDataFromPerson($tblPerson, $item);
                array_push($TableContent, $item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return false|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createMedicListExcel($PersonList, $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = $row = 0;
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Vorname");
        $export->setValue($export->getCell($column++, $row), "Geburtsdatum");
        $export->setValue($export->getCell($column++, $row), "Straße");
        $export->setValue($export->getCell($column++, $row), "Hausnr.");
        $export->setValue($export->getCell($column++, $row), "PLZ");
        $export->setValue($export->getCell($column++, $row), "Wohnort");
        $export->setValue($export->getCell($column, $row++), "Ortsteil");
        foreach ($PersonList as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthday']);
            $export->setValue($export->getCell($column++, $row), $PersonData['StreetName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['StreetNumber']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Code']);
            $export->setValue($export->getCell($column++, $row), $PersonData['City']);
            $export->setValue($export->getCell($column, $row++), $PersonData['District']);
        }
        $row++;
        PersonStandard::setGenderFooter($export, $tblPersonList, $row);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @return array
     */
    public function createInterestedPersonList($tblPersonList = array())
    {

        $TableContent = array();
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = '';
                $item['Address'] = '';
                $item['Phone'] = '';
                $item['PhoneGuardian'] = '';
                $item['TypeOptionA'] = $item['TypeOptionB'] = $item['DivisionLevel'] = $item['RegistrationDate'] = '';
                $item['SchoolYear'] = '';
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['Birthplace'] = $tblPerson->getBirthplaceString();
                $item['Denomination'] = $tblPerson->getDenominationString();
                $item['Nationality'] = $tblPerson->getDenominationString();
                $item['Siblings'] = '';
                $item['Hoard'] = 'Nein';
                for($i = 1; $i <= 2; $i++){
                    $item['SalutationS'.$i] = $item['TitleS'.$i] = $item['FirstNameS'.$i] = $item['LastNameS'.$i] = $item['FullNameS'.$i] = '';
                }
                // Address
                $item = PersonStandard::useService()->getAddressDataFromPerson($tblPerson, $item);
                // Prospect
                if (($tblProspect = Prospect::useService()->getProspectByPerson($tblPerson))) {
                    if (($tblProspectReservation = $tblProspect->getTblProspectReservation())) {
                        $item['SchoolYear'] = $tblProspectReservation->getReservationYear();
                        $item['DivisionLevel'] = $tblProspectReservation->getReservationDivision();
                        if (($tblTypeOption = $tblProspectReservation->getServiceTblTypeOptionA())) {
                            $item['TypeOptionA'] = $tblTypeOption->getName();
                        }
                        if (($tblTypeOption = $tblProspectReservation->getServiceTblTypeOptionB())) {
                            $item['TypeOptionB'] = $tblTypeOption->getName();
                        }
                    }
                    if (($tblProspectAppointment = $tblProspect->getTblProspectAppointment())) {
                        $item['RegistrationDate'] = $tblProspectAppointment->getReservationDate();
                    }
                }

                $tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_SIBLING);
                if (($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))) {
                    $SiblingNameList = array();
                    foreach ($tblToPersonList as $tblToPerson) {
                        if(($tblPersonFrom = $tblToPerson->getServiceTblPersonFrom())
                        && ($tblPersonTo = $tblToPerson->getServiceTblPersonTo())){
                            if($tblPersonFrom->getId() == $tblPerson->getId()){
                                $SiblingNameList[] = $tblPersonTo->getFullName();
                            } else {
                                $SiblingNameList[] = $tblPersonFrom->getFullName();
                            }
                        }
                    }
                    $item['Siblings'] = implode(', ', $SiblingNameList);
                }


                if(($tblGroup = Group::useService()->getGroupByName('Hort'))){
                    if(Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroup)){
                        $item['Hoard'] = 'Ja';
                    }
                }
                // get PhoneNumber by Prospect
                if (($tblToPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson))) {
                    $PhoneList = array();
                    foreach ($tblToPhoneList as $tblToPhone) {
                        if (( $tblPhone = $tblToPhone->getTblPhone() )) {
                            $PhoneList[] = $tblPhone->getNumber().' '.str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                        }
                    }
                    if(!empty($PhoneList)){
                        $item['Phone'] = $tblPerson->getFirstName().' '.$tblPerson->getLastName().' ('.implode('; ', $PhoneList).')';
                    }
                }

                // Custody
                $phoneGuardianList = array();
                $tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN);
                if (($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))) {
                    foreach ($tblToPersonList as $tblToPerson) {
                        if(($tblPersonG = $tblToPerson->getServiceTblPersonFrom())) {
                            $item['SalutationS'.$tblToPerson->getRanking()] = $tblPersonG->getSalutation();
                            $item['TitleS'.$tblToPerson->getRanking()] = $tblPersonG->getTitle();
                            $item['FirstNameS'.$tblToPerson->getRanking()] = $tblPersonG->getFirstSecondName();
                            $item['LastNameS'.$tblToPerson->getRanking()] = $tblPersonG->getLastName();
                            $item['FullNameS'.$tblToPerson->getRanking()] = $tblPersonG->getFullName();
                            // get PhoneNumber by Custody
                            if (($tblToPhoneGList = Phone::useService()->getPhoneAllByPerson($tblPersonG))) {
                                $PhoneList = array();
                                foreach ($tblToPhoneGList as $tblToPhoneG) {
                                    if (( $tblPhone = $tblToPhoneG->getTblPhone() )) {
                                        $PhoneList[] = $tblPhone->getNumber().' '.str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhoneG));
                                    }
                                }
                                if(!empty($PhoneList)){
                                    $phoneGuardianList[] = $tblPersonG->getFirstName().' '.$tblPersonG->getLastName().' ('.implode('; ', $PhoneList).')';
                                }
                            }
                        }
                    }
                }
                if (!empty($phoneGuardianList)) {
                    $item['PhoneGuardian'] = implode('; ', $phoneGuardianList);
                }
                array_push($TableContent, $item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $TableContent
     * @param array $tblPersonList
     *
     * @return false|FilePointer
     */
    public function createInterestedPersonListExcel($TableContent, $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $column = $row = 0;
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell($column++, $row), "Anmeldedatum");
        $export->setValue($export->getCell($column++, $row), "Vorname");
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Schuljahr");
        $export->setValue($export->getCell($column++, $row), "Klassenstufe");
        $export->setValue($export->getCell($column++, $row), "Schulart 1");
        $export->setValue($export->getCell($column++, $row), "Schulart 2");
        $export->setValue($export->getCell($column++, $row), "Straße");
        $export->setValue($export->getCell($column++, $row), "Hausnummer");
        $export->setValue($export->getCell($column++, $row), "PLZ");
        $export->setValue($export->getCell($column++, $row), "Ort");
        $export->setValue($export->getCell($column++, $row), "Ortsteil");
        $export->setValue($export->getCell($column++, $row), "Geburtsdatum");
        $export->setValue($export->getCell($column++, $row), "Geburtsort");
        $export->setValue($export->getCell($column++, $row), "Staatsangeh.");
        $export->setValue($export->getCell($column++, $row), "Bekenntnis");
        $export->setValue($export->getCell($column++, $row), "Geschwister");
        $export->setValue($export->getCell($column++, $row), "Hort");
        $export->setValue($export->getCell($column++, $row), "Anrede Sorgeberechtigter 1");
        $export->setValue($export->getCell($column++, $row), "Titel Sorgeberechtigter 1");
        $export->setValue($export->getCell($column++, $row), "Name Sorgeberechtigter 1");
        $export->setValue($export->getCell($column++, $row), "Vorname Sorgeberechtigter 1");
        $export->setValue($export->getCell($column++, $row), "Anrede Sorgeberechtigter 2");
        $export->setValue($export->getCell($column++, $row), "Titel Sorgeberechtigter 2");
        $export->setValue($export->getCell($column++, $row), "Name Sorgeberechtigter 2");
        $export->setValue($export->getCell($column++, $row), "Vorname Sorgeberechtigter 2");
        $export->setValue($export->getCell($column++, $row), "Telefon Interessent");
        $export->setValue($export->getCell($column, $row++), "Telefon Sorgeberechtigte");
        foreach ($TableContent as $TableRow) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $TableRow['RegistrationDate']);
            $export->setValue($export->getCell($column++, $row), $TableRow['FirstName']);
            $export->setValue($export->getCell($column++, $row), $TableRow['LastName']);
            $export->setValue($export->getCell($column++, $row), $TableRow['SchoolYear']);
            $export->setValue($export->getCell($column++, $row), $TableRow['DivisionLevel']);
            $export->setValue($export->getCell($column++, $row), $TableRow['TypeOptionA']);
            $export->setValue($export->getCell($column++, $row), $TableRow['TypeOptionB']);
            $export->setValue($export->getCell($column++, $row), $TableRow['StreetName']);
            $export->setValue($export->getCell($column++, $row), $TableRow['StreetNumber']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Code']);
            $export->setValue($export->getCell($column++, $row), $TableRow['City']);
            $export->setValue($export->getCell($column++, $row), $TableRow['District']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Birthday']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Birthplace']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Nationality']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Denomination']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Siblings']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Hoard']);
            $export->setValue($export->getCell($column++, $row), $TableRow['SalutationS1']);
            $export->setValue($export->getCell($column++, $row), $TableRow['TitleS1']);
            $export->setValue($export->getCell($column++, $row), $TableRow['FirstNameS1']);
            $export->setValue($export->getCell($column++, $row), $TableRow['LastNameS1']);
            $export->setValue($export->getCell($column++, $row), $TableRow['SalutationS2']);
            $export->setValue($export->getCell($column++, $row), $TableRow['TitleS2']);
            $export->setValue($export->getCell($column++, $row), $TableRow['FirstNameS2']);
            $export->setValue($export->getCell($column++, $row), $TableRow['LastNameS2']);
            $export->setValue($export->getCell($column++, $row), $TableRow['Phone']);
            $export->setValue($export->getCell($column, $row++), $TableRow['PhoneGuardian']);
        }
        $row++;
        PersonStandard::setGenderFooter($export, $tblPersonList, $row);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createParentTeacherConferenceList(TblDivisionCourse $tblDivisionCourse)
    {

        $TableContent = array();
        if (!empty($tblPersonList = $tblDivisionCourse->getStudents())) {
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {
                $item['Number'] = $count++;
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['Attendance'] = '';
                array_push($TableContent, $item);
            });
        }
        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return false|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createParentTeacherConferenceListExcel($PersonList, $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $column = $row = 0;
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Vorname");
        $export->setValue($export->getCell($column, $row++), "Anwesenheit");
        foreach ($PersonList as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column, $row++), $PersonData['Attendance']);
        }
        $row++;
        PersonStandard::setGenderFooter($export, $tblPersonList, $row);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param array $tblPersonList
     *
     * @return array
     */
    public function createClubMemberList(array $tblPersonList)
    {

        $TableContent = array();
        array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {
            $item['FirstName'] = $tblPerson->getFirstSecondName();
            $item['LastName'] = $tblPerson->getLastName();
            $item['Salutation'] = $tblPerson->getSalutation();
            $item['Title'] = $tblPerson->getTitle();
            $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = '';
            $item['Address'] = '';
            $item['Phone'] = $item['Mail'] = '';
            $item['Directorate'] = '';
            // Address
            $item = PersonStandard::useService()->getAddressDataFromPerson($tblPerson, $item);
            if(($phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson))) {
                $item['Phone'] = $phoneList[0]->getTblPhone()->getNumber();
            }
            if(($mailList = Mail::useService()->getMailAllByPerson($tblPerson))) {
                $item['Mail'] = $mailList[0]->getTblMail()->getAddress();
            }
            array_push($TableContent, $item);
        });
        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return false|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createClubMemberListExcel($PersonList, $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $column = $row = 0;
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell($column++, $row), "Anrede");
        $export->setValue($export->getCell($column++, $row), "Titel");
        $export->setValue($export->getCell($column++, $row), "Vorname");
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Straße");
        $export->setValue($export->getCell($column++, $row), "Hausnr.");
        $export->setValue($export->getCell($column++, $row), "PLZ");
        $export->setValue($export->getCell($column++, $row), "Ort");
        $export->setValue($export->getCell($column++, $row), "Ortsteil");
        $export->setValue($export->getCell($column++, $row), "Telefon");
        $export->setValue($export->getCell($column++, $row), "Mail");
        $export->setValue($export->getCell($column, $row++), "Vorstand");
        foreach ($PersonList as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Salutation']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Title']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['StreetName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['StreetNumber']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Code']);
            $export->setValue($export->getCell($column++, $row), $PersonData['City']);
            $export->setValue($export->getCell($column++, $row), $PersonData['District']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Phone']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Mail']);
            $export->setValue($export->getCell($column, $row++), $PersonData['Directorate']);
        }
        $row++;
        PersonStandard::setGenderFooter($export, $tblPersonList, $row);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createPrintClassList(TblDivisionCourse $tblDivisionCourse)
    {

        $TableContent = array();
        $tblStudentGroup1 = Group::useService()->getGroupByMetaTable('STUDENT_GROUP_1');
        $tblStudentGroup2 = Group::useService()->getGroupByMetaTable('STUDENT_GROUP_2');
        if(!empty($tblPersonList = $tblDivisionCourse->getStudents())) {
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $tblStudentGroup1, $tblStudentGroup2, $tblDivisionCourse, &$count) {
                $item['Number'] = $count++;
                $item['ExcelName'] = array();
                $item['Address'] = '';
                $item['ExcelAddress'] = array();
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['Birthplace'] = $tblPerson->getBirthplaceString();
                $item['PhoneNumbers'] = '';
                $item['ExcelPhoneNumbers'] = '';
                $item['Orientation'] = '';
                $item['OrientationAndFrench'] = '';
                $item['Advanced'] = '';
                $item['Education'] = '';
                $item['Group'] = '';
                $item['Group1'] = false;
                $item['Group2'] = false;
                $item['Elective'] = '';
                $item['ExcelElective'] = array();
                $item['Integration'] = '';
                $item['French'] = '';
                $father = null;
                $mother = null;
                $fatherPhoneList = false;
                $motherPhoneList = false;
                if($tblStudentGroup1
                    && Group::useService()->existsGroupPerson($tblStudentGroup1, $tblPerson)) {
                    $item['Group'] .= 1;
                    $item['Group1'] = true;
                }
                if($tblStudentGroup2
                    && Group::useService()->existsGroupPerson($tblStudentGroup2, $tblPerson)) {
                    (!empty($item['Group']) ? $item['Group'] .= ', 2' : $item['Group'] = 2);
                    $item['Group2'] = true;
                }
                $tblType = false;
                if(($tblYear = $tblDivisionCourse->getServiceTblYear())
                    && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                    $tblType = $tblStudentEducation->getServiceTblSchoolType();
                }
                $Sibling = array();
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if($guardian->getTblType()->getId() == 1) {
                            if($father === null) {
                                $father = $guardian->getServiceTblPersonFrom();
                                if($father) {
                                    $fatherPhoneList = Phone::useService()->getPhoneAllByPerson($father);
                                }
                            } else {
                                $mother = $guardian->getServiceTblPersonFrom();
                                if($mother) {
                                    $motherPhoneList = Phone::useService()->getPhoneAllByPerson($mother);
                                }
                            }
                        }
                        if($guardian->getTblType()->getName() == 'Geschwisterkind') {
                            if($guardian->getServiceTblPersonFrom()->getId() != $tblPerson->getId()) {
                                if(($tblStudent = Student::useService()->getStudentByPerson($guardian->getServiceTblPersonFrom()))) {
                                    $DivisionDisplay = $this->getDivisionDisplayStringByPerson($guardian->getServiceTblPersonFrom(), $tblDivisionCourse);
                                    $Sibling[] = '[' . $guardian->getServiceTblPersonFrom()->getFirstName() . $DivisionDisplay . ']';
                                }
                            } elseif(($tblStudent = Student::useService()->getStudentByPerson($guardian->getServiceTblPersonTo()))) {
                                if($guardian->getServiceTblPersonTo()->getId() != $tblPerson->getId()) {
                                    $DivisionDisplay = $this->getDivisionDisplayStringByPerson($guardian->getServiceTblPersonTo(), $tblDivisionCourse);
                                    $Sibling[] = '[' . $guardian->getServiceTblPersonTo()->getFirstName() . $DivisionDisplay . ']';
                                }
                            }
                        }
                    }
                }
                $SiblingString = '';
                if(!empty($Sibling)) {
                    $SiblingString = implode('<br>', $Sibling);
                    $item['ExcelSibling'] = $Sibling;
                }
                if(!($tblAddress = Address::useService()->getAddressByPerson($tblPerson))) {
                    $tblAddress = false;
                }
                if($tblAddress) {
                    if($tblAddress->getTblCity()->getDisplayDistrict() != '') {
                        $item['ExcelAddress'][] = $tblAddress->getTblCity()->getDisplayDistrict();
                    }
                    $item['ExcelAddress'][] = $tblAddress->getStreetName() . ' ' . $tblAddress->getStreetNumber();
                    $item['ExcelAddress'][] = $tblAddress->getTblCity()->getCode() . ' ' . $tblAddress->getTblCity()->getName();
                }
                if($father) {
                    $tblAddressFather = Address::useService()->getAddressByPerson($father);
                    if($tblAddressFather && $tblAddressFather->getId() != ($tblAddress ? $tblAddress->getId() : null)) {
                        if(!empty($item['ExcelAddress'])) {
                            $item['ExcelAddress'][] = '- - - - - - -';
                        }
                        $item['ExcelAddress'][] = '(' . $father->getLastFirstName() . ')';
                        if($tblAddressFather->getTblCity()->getDistrict() != '') {
                            $item['ExcelAddress'][] = $tblAddressFather->getTblCity()->getDistrict();
                        }
                        $item['ExcelAddress'][] = $tblAddressFather->getStreetName() . ' ' . $tblAddressFather->getStreetNumber();
                        $item['ExcelAddress'][] = $tblAddressFather->getTblCity()->getCode() . ' ' . $tblAddressFather->getTblCity()->getName();
                    }
                }
                if($mother) {
                    $tblAddressMother = Address::useService()->getAddressByPerson($mother);
                    if($tblAddressMother && $tblAddressMother->getId() != ($tblAddress ? $tblAddress->getId() : null)) {
                        if(!empty($item['ExcelAddress'])) {
                            $item['ExcelAddress'][] = '- - - - - - -';
                        }
                        $item['ExcelAddress'][] = '(' . $mother->getLastFirstName() . ')';
                        if($tblAddressMother->getTblCity()->getDistrict() != '') {
                            $item['ExcelAddress'][] = $tblAddressMother->getTblCity()->getDistrict();
                        }
                        $item['ExcelAddress'][] = $tblAddressMother->getStreetName() . ' ' . $tblAddressMother->getStreetNumber();
                        $item['ExcelAddress'][] = $tblAddressMother->getTblCity()->getCode() . ' ' . $tblAddressMother->getTblCity()->getName();
                    }
                }
                if(!empty($item['ExcelAddress'])) {
                    $item['Address'] = implode('<br/>', $item['ExcelAddress']);
                }
                $item['FatherName'] = $father ? ($tblPerson->getLastName() == $father->getLastName()
                    ? $father->getFirstSecondName() : $father->getFirstSecondName() . ' ' . $father->getLastName())
                    : '';
                $item['MotherName'] = $mother ? ($tblPerson->getLastName() == $mother->getLastName()
                    ? $mother->getFirstSecondName() : $mother->getFirstSecondName() . ' ' . $mother->getLastName())
                    : '';
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                $item['DisplayName'] = ($item['Integration'] === '1'
                        ? new Bold($tblPerson->getLastFirstName())
                        : $tblPerson->getLastFirstName())
                    . ($father || $mother ? '<br>(' . ($father ? $item['FatherName']
                            . ($mother ? ', ' : '') : '')
                        . ($mother ? $item['MotherName'] : '') . ')' : '')
                    . ($SiblingString != '' ? '<br/>' . $SiblingString : '');
                $item['ExcelName'][] = $tblPerson->getLastFirstName();
                if($father || $mother) {
                    $item['ExcelName'][] = '(' . ($father ? $item['FatherName']
                            . ($mother ? ', ' : '') : '')
                        . ($mother ? $item['MotherName'] : '') . ')';
                }
                if(!empty($Sibling)) {
                    foreach ($Sibling as $Child) {
                        $item['ExcelName'][] = $Child;
                    }
                }
                $phoneNumbers = array();
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if($phoneList) {
                    foreach ($phoneList as $phone) {
                        $typeString = Phone::useService()->getPhoneTypeShort($phone);
                        $phoneNumbers[$phone->getId()] = $phone->getTblPhone()->getNumber() . ' ' . $typeString
                            . ($phone->getRemark() ? ' ' . $phone->getRemark() : '');
                    }
                }
                if($fatherPhoneList) {
                    foreach ($fatherPhoneList as $phone) {
                        if($phone->getServiceTblPerson()) {
                            if(!isset($phoneNumbers[$phone->getTblPhone()->getId()])) {
                                $phoneNumbers[$phone->getTblPhone()->getId()] = $this->getPhoneGuardianString($phone);
                            }
                        }
                    }
                }
                if($motherPhoneList) {
                    foreach ($motherPhoneList as $phone) {
                        if($phone->getServiceTblPerson()) {
                            if(!isset($phoneNumbers[$phone->getTblPhone()->getId()])) {
                                $phoneNumbers[$phone->getTblPhone()->getId()] = $this->getPhoneGuardianString($phone);
                            }
                        }
                    }
                }
                if(!empty($phoneNumbers)) {
//                    $phoneNumbers = array_unique($phoneNumbers);
                    $item['PhoneNumbers'] = implode('<br>', $phoneNumbers);
                    $item['ExcelPhoneNumbers'] = $phoneNumbers;
                }
                // NK/Profil
                if($tblStudent) {
                    $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                    $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier(2);
                    $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking(
                        $tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking);
                    if($tblStudentSubject) {
                        if($tblStudentSubject->getServiceTblSubject() && $tblStudentSubject->getServiceTblSubject()->getAcronym() == 'FR') {
                            $item['French'] = 'FR';
                        }
                    }
                    $isSet = false;
                    // Profil
                    $tblStudentProfile = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent,
                        Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE')
                    );
                    if($tblStudentProfile && ($tblSubject = $tblStudentProfile[0]->getServiceTblSubject())) {
                        $item['Orientation'] = $tblSubject->getAcronym();
                        $isSet = true;
                    }
                    // Neigungskurs
                    $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent,
                        Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION')
                    );
                    if($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                        if(!$isSet) {
                            $item['Orientation'] = $tblSubject->getAcronym();
                        } elseif($tblType && $tblType->getName() == TblTypeSchool::IDENT_GYMNASIUM) {
                            $item['Advanced'] = $tblSubject->getAcronym();
                        }
                    }
                    if($tblType && $tblType->getName() == TblTypeSchool::IDENT_OBER_SCHULE) {
                        $item['OrientationAndFrench'] = $item['Orientation'] .
                            (!empty($item['Orientation']) && !empty($item['French']) ? ', ' : '') . $item['French'];
                    } else {
                        $item['OrientationAndFrench'] = $item['Orientation'];
                    }
                    if($item['Advanced'] && $item['OrientationAndFrench']) {
                        $item['OrientationAndFrench'] .= '<br/>' . $item['Advanced'];
                    }
                    // Bildungsgang
                    $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                    $tblCourse = $tblStudentEducation->getServiceTblCourse();
                    // berufsbildende Schulart
                    if ($tblSchoolType && $tblSchoolType->isTechnical()) {
                        $courseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                    } else {
                        $courseName = $tblCourse ? $tblCourse->getName() : '';
                    }
                    // set Accronym for typical Course
                    switch ($courseName) {
                        case 'Gymnasium': $item['Education'] = 'GY'; break;
                        case 'Hauptschule': $item['Education'] = 'HS'; break;
                        case 'Realschule': $item['Education'] = 'RS'; break;
                        default: $item['Education'] = $courseName; break;
                    }
                    // Wahlfach
                    $tblStudentElectiveList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent,
                        Student::useService()->getStudentSubjectTypeByIdentifier('ELECTIVE')
                    );
                    $ElectiveList = array();
                    if($tblStudentElectiveList) {
                        foreach ($tblStudentElectiveList as $tblStudentElective) {
                            if($tblStudentElective->getServiceTblSubject()) {
                                $tblSubjectRanking = $tblStudentElective->getTblStudentSubjectRanking();
                                if($tblSubjectRanking) {
                                    $ElectiveList[$tblStudentElective->getTblStudentSubjectRanking()->getIdentifier()] =
                                        $tblStudentElective->getServiceTblSubject()->getAcronym();
                                } else {
                                    $ElectiveList[] =
                                        $tblStudentElective->getServiceTblSubject()->getAcronym();
                                }
                            }
                        }
                        if(!empty($ElectiveList)) {
                            ksort($ElectiveList);
                        }
                        if(!empty($ElectiveList)) {
                            $item['Elective'] = implode('<br/>', $ElectiveList);
                            foreach ($ElectiveList as $Elective) {
                                $item['ExcelElective'][] = $Elective;
                            }
                        }
                    }
                }
                array_push($TableContent, $item);
            });
        }
        return $TableContent;
    }

    /**
     * @param TblPerson         $tblPerson
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return string
     */
    private function getDivisionDisplayStringByPerson(TblPerson $tblPerson, TblDivisionCourse $tblDivisionCourse)
    {

        $result = '';
        if(($tblYear = $tblDivisionCourse->getServiceTblYear())){
//            if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))){
//                if(($tblDivisionCourseD = $tblStudentEducation->getTblDivision())){
//                    $DivisionCourseList[] = $tblDivisionCourseD->getDisplayName();
//                }
//                if(($tblDivisionCourseS = $tblStudentEducation->getTblCoreGroup())){
//                    $DivisionCourseList[] = $tblDivisionCourseD->getDisplayName();
//                }
//            }
            if(($tblDivisionCourseMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT))
            && ($tblDivisionCourseMemberList = DivisionCourse::useService()->getDivisionCourseMemberListByPersonAndYearAndMemberType($tblPerson, $tblYear, $tblDivisionCourseMemberType))){
                foreach($tblDivisionCourseMemberList as $tblDivisionCourseMember){
                    if(($tblDivisionCourse = $tblDivisionCourseMember->getTblDivisionCourse())){
                        $DivisionCourseList[] = $tblDivisionCourse->getDisplayName();
                    }
                }
            }
        }
        if(!empty($DivisionCourseList)){
            $result = implode(', ', $DivisionCourseList);
        }
        return $result;
    }

    /**
     * @param TblToPerson $tblToPerson
     * @return string
     */
    private function getPhoneGuardianString(TblToPerson $tblToPerson){
        $typeString = Phone::useService()->getPhoneTypeShort($tblToPerson);
        $person = '';
        $tblGuardianPerson = $tblToPerson->getServiceTblPerson();
        if ($tblGuardianPerson){
            // Set Default by Salutation
            if ($tblGuardianPerson->getSalutation() == 'Herr'){
                $person = 'V.';
            } elseif ($tblGuardianPerson->getSalutation() == 'Frau'){
                $person = 'M.';
            } else {
                $person = $tblGuardianPerson->getFirstName();
            }
            //Override By found Gender
            $tblGuardianCommon = Common::useService()->getCommonByPerson($tblGuardianPerson);
            if ($tblGuardianCommon) {
                if (( $GuardianBirthDates = $tblGuardianCommon->getTblCommonBirthDates() )) {
                    if (($tblCommonGender = $GuardianBirthDates->getTblCommonGender())) {
                        if ($tblCommonGender->getId() == 1) {
                            $person = 'V.';
                        } elseif ($tblCommonGender->getId() == 2) {
                            $person = 'M.';
                        }
                    }
                }
            }
        }

        return $tblToPerson->getTblPhone()->getNumber().' '.$typeString.' '.$person
        . ($tblToPerson->getRemark() ? ' ' . $tblToPerson->getRemark() : '');
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createPrintClassListExcel(array $TableContent, array $tblPersonList, TblDivisionCourse $tblDivisionCourse)
    {

        // get PersonList sorted by GradeBook
        if (!empty($TableContent)) {
            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $SetIntegrationNotice = false;
            $custodyList = array();
            if(($tblDivisionCustodyList = $tblDivisionCourse->getCustody())) {
                foreach ($tblDivisionCustodyList as $tblPerson) {
                    $custodyList[] = trim($tblPerson->getSalutation().' '.$tblPerson->getLastName());
                }
            }
            $teacherList = array();
            if(($tblDivisionTeacherAll = $tblDivisionCourse->getDivisionTeacherList())) {
                foreach ($tblDivisionTeacherAll as $tblPerson) {
                    $teacherList[] = trim($tblPerson->getSalutation() . ' ' . $tblPerson->getLastName());
                }
            }
            $tblType = false;
            if(($tblYear = $tblDivisionCourse->getServiceTblYear())
                && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                $tblType = $tblStudentEducation->getServiceTblSchoolType();
            }
            $column = $row = 0;
            $export->setStyle($export->getCell($column, $row), $export->getCell(7, $row))->setFontBold();
            $export->setValue($export->getCell($column, $row++),
                "Klasse ".$tblDivisionCourse->getDisplayName().(empty($teacherList) ? '' : ' '.implode(', ', $teacherList)));
            // Header
            $export->setValue($export->getCell($column++, $row), "Name");
            $export->setValue($export->getCell($column++, $row), "Geb.-Datum");
            $export->setValue($export->getCell($column++, $row), "Adresse");
            $export->setValue($export->getCell($column++, $row), "Telefonnummer");
            $export->setValue($export->getCell($column++, $row), "Gr");
            $export->setValue($export->getCell($column++, $row), "WB/P/FR");
            $export->setValue($export->getCell($column++, $row), "BG");
            $export->setValue($export->getCell($column, $row), "WF");
            // Header bold
            $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setFontBold();
            $row = 2;
            $counterStudentGroup1 = 0;
            $counterStudentGroup2 = 0;
            $orientationList = array();
            $counterFrench = 0;
            $excelElectiveList = array();
            $educationList = array();
            foreach ($TableContent as $PersonData) {
                $NameRow = $AddressRow = $PhoneRow = $ElectiveRow = $row;
                if(!empty($PersonData['Group1'])) {
                    $counterStudentGroup1++;
                }
                if(!empty($PersonData['Group2'])) {
                    $counterStudentGroup2++;
                }
                if(!empty($PersonData['Orientation'])) {
                    if(isset($orientationList[$PersonData['Orientation']])) {
                        $orientationList[$PersonData['Orientation']] += 1;
                    } else {
                        $orientationList[$PersonData['Orientation']] = 1;
                    }
                }
                if(!empty($PersonData['French'])) {
                    $counterFrench++;
                }
                if(!empty($PersonData['Education'])) {
                    if(isset($educationList[$PersonData['Education']])) {
                        $educationList[$PersonData['Education']] += 1;
                    } else {
                        $educationList[$PersonData['Education']] = 1;
                    }
                }
                if(!empty($PersonData['ExcelElective'])) {
                    foreach($PersonData['ExcelElective'] as $Elective) {
                        if(isset($excelElectiveList[$Elective])) {
                            $excelElectiveList[$Elective] += 1;
                        } else {
                            $excelElectiveList[$Elective] = 1;
                        }
                    }
                }
                $export->setValue($export->getCell(1, $row), $PersonData['Birthday']);
                $export->setValue($export->getCell(4, $row), $PersonData['Group']);
                $export->setValue($export->getCell(5, $row), $PersonData['Orientation']);
                if($tblType && $tblType->getName() == TblTypeSchool::IDENT_OBER_SCHULE) {
                    $export->setValue($export->getCell(5, $row + 1), $PersonData['French']);
                } elseif($PersonData['Advanced']) {
                    $export->setValue($export->getCell(5, $row + 1), $PersonData['Advanced']);
                }
                $export->setValue($export->getCell(6, $row), $PersonData['Education']);
                if(isset($PersonData['ExcelName']) && !empty($PersonData['ExcelName'])) {
                    foreach($PersonData['ExcelName'] as $Key => $Name) {
                        if($Key == 0) {
                            if($PersonData['Integration'] === '1') {
                                $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row))->setFontBold();
                                $SetIntegrationNotice = true;
                            }
                        }
                        $export->setValue($export->getCell(0, $NameRow), $Name);
                        $NameRow++;
                    }
                }
                if(isset($PersonData['ExcelAddress']) && !empty($PersonData['ExcelAddress'])) {
                    foreach($PersonData['ExcelAddress'] as $Address) {
                        if($Address == '- - - - - - -') {
                            $export->setStyle($export->getCell(2, $AddressRow), $export->getCell(2, $AddressRow))
                                ->setBorderTop();
                        } else {
                            $export->setValue($export->getCell(2, $AddressRow), $Address);
                            $AddressRow++;
                        }
                    }
                }
                if(isset($PersonData['ExcelPhoneNumbers']) && !empty($PersonData['ExcelPhoneNumbers'])) {
                    foreach($PersonData['ExcelPhoneNumbers'] as $Phone) {
                        $export->setValue($export->getCell(3, $PhoneRow), $Phone);
                        $PhoneRow++;
                    }
                }
                if(isset($PersonData['ExcelElective']) && !empty($PersonData['ExcelElective'])) {
                    foreach($PersonData['ExcelElective'] as $Elective) {
                        $export->setValue($export->getCell(7, $ElectiveRow), $Elective);
                        $ElectiveRow++;
                    }
                }
                $row++;
                if($NameRow > $row) {
                    $row = $NameRow;
                }
                if($AddressRow > $row) {
                    $row = $AddressRow;
                }
                if($PhoneRow > $row) {
                    $row = $PhoneRow;
                }
                if($ElectiveRow > $row) {
                    $row = $ElectiveRow;
                }
                // Gittertrennlinie
                $export->setStyle($export->getCell(0, $row - 1), $export->getCell(7, $row - 1))->setBorderBottom();
            }
            // Gitterlinien
            $export->setStyle($export->getCell(0, 1), $export->getCell(7, 1))->setBorderBottom();
            $export->setStyle($export->getCell(0, 1), $export->getCell(7, $row - 1))->setBorderVertical();
            $export->setStyle($export->getCell(0, 1), $export->getCell(7, $row - 1))->setBorderOutline();
            // Personenanzahl
            $row++;
            PersonStandard::setGenderFooter($export, $tblPersonList, $row);
            if($SetIntegrationNotice) {
                $row++;
                $export->setStyle($export->getCell(0, $row), $export->getCell(2, $row))->mergeCells()->setFontBold();
                $export->setValue($export->getCell(0, $row), '*Schriftart-Fett für Kinder mit Förderbedarf');
            }
            if(!empty($custodyList)) {
                $row += 2;
                $export->setValue($export->getCell(0, $row), 'Elternsprecher ' . (empty($custodyList) ? '' : ' ' . implode(', ', $custodyList)));
            }
            if(!empty($counterStudentGroup1) && !empty($counterStudentGroup2)) {
                $row += 2;
                $export->setValue($export->getCell(0, $row++), 'Klassengruppen');
                $export->setValue($export->getCell(0, $row), 'Anzahl Gruppe 1:');
                $export->setValue($export->getCell(1, $row++), $counterStudentGroup1);
                $export->setValue($export->getCell(0, $row), 'Anzahl Gruppe 2:');
                $export->setValue($export->getCell(1, $row), $counterStudentGroup2);
            }
            if(!empty($orientationList)) {
                $row += 2;
                $export->setValue($export->getCell(0, $row), 'Wahlbereiche/Profile');
                foreach($orientationList as $orientation => $count) {
                    $row++;
                    $export->setValue($export->getCell(0, $row), $orientation . ':');
                    $export->setValue($export->getCell(1, $row), $count);
                }
            }
            if(!empty($counterFrench)) {
                $row += 2;
                $export->setValue($export->getCell(0, $row++), 'Fremdsprache FR');
                $export->setValue($export->getCell(0, $row), 'Französisch:');
                $export->setValue($export->getCell(1, $row), $counterFrench);
            }
            if(!empty($educationList)) {
                $row += 2;
                $export->setValue($export->getCell(0, $row), 'Bildungsgänge');
                foreach($educationList as $education => $count) {
                    $row++;
                    $export->setValue($export->getCell(0, $row), $education . ':');
                    $export->setValue($export->getCell(1, $row), $count);
                }
            }
            if(!empty($excelElectiveList)) {
                $row += 2;
                $export->setValue($export->getCell(0, $row), 'Wahlfächer');
                foreach($excelElectiveList as $excelElective => $count) {
                    $row++;
                    $export->setValue($export->getCell(0, $row), $excelElective . ':');
                    $export->setValue($export->getCell(1, $row), $count);
                }
            }
            // Stand
            $row += 2;
            $export->setValue($export->getCell(0, $row), 'Stand: ' . (new \DateTime())->format('d.m.Y'));
            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $row))->setColumnWidth(20);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $row))->setColumnWidth(10);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $row))->setColumnWidth(20);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $row))->setColumnWidth(21);
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $row))->setColumnWidth(3);
            $export->setStyle($export->getCell(5, 0), $export->getCell(6, $row))->setColumnWidth(9);
            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $row))->setColumnWidth(3.5);
            $export->setStyle($export->getCell(7, 0), $export->getCell(7, $row))->setColumnWidth(4);
            // Schriftgröße
            $export->setStyle($export->getCell(0, 0), $export->getCell(7, 0))->setFontSize(12)->setFontBold()->mergeCells();
            $export->setStyle($export->getCell(0, 1), $export->getCell(7, $row))->setFontSize(10);
            // Spalten zentriert
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $row))->setAlignmentCenter();
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $row))->setAlignmentCenter();
            $export->setStyle($export->getCell(5, 0), $export->getCell(5, $row))->setAlignmentCenter();
            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $row))->setAlignmentCenter();
            $export->setStyle($export->getCell(7, 0), $export->getCell(7, $row))->setAlignmentCenter();
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
            return $fileLocation;
        }
        return false;
    }
}
