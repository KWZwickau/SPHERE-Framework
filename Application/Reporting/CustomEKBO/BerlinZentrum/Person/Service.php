<?php
namespace SPHERE\Application\Reporting\CustomEKBO\BerlinZentrum\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;

class Service
{

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createSuSList()
    {

        $tblPersonList = array();
        if(($tblGroupStudent = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT))){
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroupStudent);
        }

        $TableContent = array();

        if (!empty( $tblPersonList )) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {
                // Content
                $Item['PersonId'] = $tblPerson->getId();
                $Item['StudentNumber'] = '';
                $Item['Division'] = Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson, '');
                $Item['PersonGroupKL'] = '';
                $Item['PersonGroupTeam'] = '';
                $Item['PersonGroupG'] = '';
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['CallName'] = $tblPerson->getCallName();
                $Item['FirstName'] = $tblPerson->getFirstName();
                $Item['SecondName'] = $tblPerson->getSecondName();
                $Item['PersonGroupTutor'] = '';
                $Item['Birthday'] = '';
                $Item['Birthplace'] = '';
                $Item['Gender'] = '';
                $Item['ExcelStreet'] = '';
                $Item['Code'] = '';
                $Item['City'] = '';
                $Item['AddressRemark'] = '';
                $Item['Nationality'] = '';
                $Item['Denomination'] = '';
                $Item['LeavingSchool'] = '';
                for($i = 1; $i <= 2; $i++){
                    $Item['PersonIdS'.$i] = '';
                    $Item['TitleS'.$i] = '';
                    $Item['LastNameS'.$i] = '';
                    $Item['FirstNameS'.$i] = '';
                    $Item['AddressRemarkS'.$i] = '';
                    $Item['ExcelStreetS'.$i] = '';
                    $Item['CodeS'.$i] = '';
                    $Item['CityS'.$i] = '';
                    $Item['MailS'.$i] = '';
                    $Item['Mail2S'.$i] = '';
                    $Item['RemarkS'.$i] = '';
                }
                $Item['EnterDate'] = '';
                $Item['LeaveDate'] = '';
                $Item['Region'] = '';
                $Item['Mail'] = '';
                $Item['MailExcel'] = '';
                $Item['Masern'] = '';
                for($j = 1; $j <= 3; $j++) {
                    $Item['Foreign_Language'.$j] = '';
                    $Item['Foreign_Language'.$j.'_JG'] = '';
                }
                $Item['UserName'] = $tblPerson->getLastName().'.'.$tblPerson->getFirstName();
                $Item['MigrationBackground'] = '';

                if($tblStudent = Student::useService()->getStudentByPerson($tblPerson)){
                    $Item['StudentNumber'] = $tblStudent->getIdentifier();
                }
                //  Personengruppen Schüler
                if($tblPersonGroupList = Group::useService()->getGroupAllByPerson($tblPerson)){
                    foreach($tblPersonGroupList as $tblPersonGroup){
                        if(strpos($tblPersonGroup->getName(), 'Klasse') !== false){
                            if($Item['PersonGroupKL'] != ''){
                                $Item['PersonGroupKL'] .= ', '.$tblPersonGroup->getName();
                            } else {
                                $Item['PersonGroupKL'] .= $tblPersonGroup->getName();
                            }
                        }
                        if(strpos($tblPersonGroup->getName(), 'Team') !== false){
                            if($Item['PersonGroupTeam'] != ''){
                                $Item['PersonGroupTeam'] .= ', '.$tblPersonGroup->getName();
                            } else {
                                $Item['PersonGroupTeam'] .= $tblPersonGroup->getName();
                            }
                        }
                        if(strpos($tblPersonGroup->getName(), 'Gruppe') !== false){
                            if($Item['PersonGroupG'] != ''){
                                $Item['PersonGroupG'] .= ', '.$tblPersonGroup->getName();
                            } else {
                                $Item['PersonGroupG'] .= $tblPersonGroup->getName();
                            }
                        }
                        if(strpos($tblPersonGroup->getName(), 'Tutor') !== false){
                            if($Item['PersonGroupTutor'] != ''){
                                $Item['PersonGroupTutor'] .= ', '.$tblPersonGroup->getName();
                            } else {
                                $Item['PersonGroupTutor'] .= $tblPersonGroup->getName();
                            }
                        }
                    }
                }


                // Allgemeine Daten Schüler
                if (($common = Common::useService()->getCommonByPerson($tblPerson))) {
                    if(($tblCommonBirthDates = $common->getTblCommonBirthDates())){
                        $Item['Birthday'] = $tblCommonBirthDates->getBirthday();
                        $Item['Birthplace'] = $tblCommonBirthDates->getBirthplace();
                        if(($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())){
                            $Item['Gender'] = $tblCommonGender->getName();
                        }
                    }
                    if(($tblCommonInformation = $common->getTblCommonInformation())){
                        $Item['Nationality'] = $tblCommonInformation->getNationality();
                        $Item['Denomination'] = $tblCommonInformation->getDenomination();
                    }
                }
                // Address Schüler
                if (( $tblAdress = Address::useService()->getAddressByPerson($tblPerson) )) {
//                    $tblToPersonAdress = Address::useService()->getToPersonAllByAddress($tblAdress);
//                    $Item['Address'] = $tblAdress->getStreetName().' '.$tblAdress->getStreetNumber().', '.$tblAdress->getTblCity()->getCode().' '
//                        .$tblAdress->getTblCity()->getName();

//                    $Item['StreetName'] = $tblAdress->getStreetName();
//                    $Item['StreetNumber'] = $tblAdress->getStreetNumber();
                    $Item['ExcelStreet'] = $tblAdress->getStreetName().' '.$tblAdress->getStreetNumber();
                    $Item['Code'] = $tblAdress->getTblCity()->getCode();
                    $Item['City'] = $tblAdress->getTblCity()->getName();
//                    $Item['District'] = $tblAdress->getTblCity()->getDistrict();
                    $RemarkToPerson = '';
                    if(($tblToPersonAddressList = Address::useService()->getToPersonAllByAddress($tblAdress))) {
                        foreach($tblToPersonAddressList as $tblToPersonAddress){
                            if($tblToPersonAddress->getServiceTblPerson()->getId() == $tblPerson->getId()){
                                $RemarkToPerson = $tblToPersonAddress->getRemark();
                            }
                        }
                    }
                    $Item['AddressRemark'] = $RemarkToPerson;
                    if($tblAdress->getRegion()){
                        $Item['Region'] = $tblAdress->getRegion();
                    } else {
                        $Item['Region'] = Address::useService()->getRegionStringByCode($tblAdress->getTblCity()->getCode());
                    }
                }
                // Schülerakte
                if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                    if(($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::ARRIVE))){
                        if(($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))){
                            $Item['EnterDate'] = $tblStudentTransfer->getTransferDate();
                            if(($tblCompany = $tblStudentTransfer->getServiceTblCompany())){
                                $Item['LeavingSchool'] = $tblCompany->getName();
                            }
                        }
                    }
                    if(($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::LEAVE))){
                        if(($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))){
                            $Item['LeaveDate'] = $tblStudentTransfer->getTransferDate();
                        }
                    }
                    if(($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier(TblStudentSubjectType::TYPE_FOREIGN_LANGUAGE))){
                        if(($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType))){
                            foreach($tblStudentSubjectList as $tblStudentSubject){
                                for($k = 1; $k <= 3; $k++){
                                    if($tblStudentSubject->getTblStudentSubjectRanking()->getName() == $k){
                                        $Item['Foreign_Language'.$k] = $tblStudentSubject->getServiceTblSubject()->getName();
                                        if(($tblLevel = $tblStudentSubject->getServiceTblLevelFrom())){
                                            $Item['Foreign_Language'.$k.'_JG'] = $tblLevel->getName();
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if(($tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord())){
                        $Item['Masern'] = $tblStudentMedicalRecord->getMasernDocumentType()->getTextShort();
                    }
                    if($tblStudent->getHasMigrationBackground()){
                        $Item['MigrationBackground'] = 'Ja';
                    } else {
                        $Item['MigrationBackground'] = 'Nein';
                    }
                }
                // Sorgeberechtigte
                if(($tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN))){
                    if(($tbltoPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))){
                        foreach($tbltoPersonList as $tbltoPerson){
                            if(($tblPersonS = $tbltoPerson->getServiceTblPersonFrom())){
                                $Number = $tbltoPerson->getRanking();
                                $Item['PersonIdS'.$Number] = $tblPersonS->getId();
                                $Item['TitleS'.$Number] = $tblPersonS->getTitle();
                                $Item['LastNameS'.$Number] = $tblPersonS->getLastName();
                                $Item['FirstNameS'.$Number] = $tblPersonS->getFirstName();

                                // Address
                                if (( $tblAdress = Address::useService()->getAddressByPerson($tblPersonS) )) {
                                    $Item['ExcelStreetS'.$Number] = '';
                                    $Item['CodeS'.$Number] = '';
                                    $Item['CityS'.$Number] = '';
                                    $Item['ExcelStreetS'.$Number] = $tblAdress->getStreetName().' '.$tblAdress->getStreetNumber();
                                    $Item['CodeS'.$Number] = $tblAdress->getTblCity()->getCode();
                                    $Item['CityS'.$Number] = $tblAdress->getTblCity()->getName();
                                    //                    $Item['District'] = $tblAdress->getTblCity()->getDistrict();
                                    $RemarkToPerson = '';
                                    if(($tblToPersonAddressList = Address::useService()->getToPersonAllByAddress($tblAdress))) {
                                        foreach($tblToPersonAddressList as $tblToPersonAddress){
                                            if($tblToPersonAddress->getServiceTblPerson()->getId() == $tblPersonS->getId()){
                                                $RemarkToPerson = $tblToPersonAddress->getRemark();
                                            }
                                        }
                                    }
                                    $Item['AddressRemarkS'.$Number] = $RemarkToPerson;
                                }

                                if(($tblMailList = Mail::useService()->getMailAllByPerson($tblPersonS))){
                                    if(isset($tblMailList[0]) && ($tblMail = $tblMailList[0]->getTblMail())){
                                        $Item['MailS'.$Number] = $tblMail->getAddress();
                                    }
                                    if(isset($tblMailList[1]) && ($tblMail = $tblMailList[1]->getTblMail())){
                                        $Item['Mail2S'.$Number] = $tblMail->getAddress();
                                    }
                                }

                                if(($tblCommonS = $tblPerson->getCommon())){
                                    $Item['RemarkS'.$Number] = $tblCommonS->getRemark();
                                }
                            }
                        }
                    }
                }
                // E-Mail
                $ContactMailList = array();
                $tblMailList = Mail::useService()->getMailAllByPerson($tblPerson);
                if ($tblMailList) {
                    foreach ($tblMailList as $tblMail) {
                        if ($tblMail->getTblMail()) {
                            $ContactMailList[] = $tblMail->getTblMail()->getAddress();
                        }
                    }
                    if (!empty( $ContactMailList )) {
                        $Item['Mail'] = implode('<br>', $ContactMailList);
                        $Item['MailExcel'] = implode('; ', $ContactMailList);
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
     * @return false|\SPHERE\Application\Document\Storage\FilePointer
     */
    public function createSuSListExcel($TableContent)
    {

        if (!empty( $TableContent )) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $column = 0;
            $row = 0;
            $export->setValue($export->getCell($column++, $row), "PersonId");
            $export->setValue($export->getCell($column++, $row), "Schülernummer");
            $export->setValue($export->getCell($column++, $row), "JG");
            $export->setValue($export->getCell($column++, $row), "KL");
            $export->setValue($export->getCell($column++, $row), "TEAM");
            $export->setValue($export->getCell($column++, $row), "Gruppe");
            $export->setValue($export->getCell($column++, $row), "Nachname");
            $export->setValue($export->getCell($column++, $row), "Rufname");
            $export->setValue($export->getCell($column++, $row), "Vorname");
            $export->setValue($export->getCell($column++, $row), "Zweiter Vorname");
            $export->setValue($export->getCell($column++, $row), "Tutor");
            $export->setValue($export->getCell($column++, $row), "Geburtsdatum");
            $export->setValue($export->getCell($column++, $row), "Geburtsort");
            $export->setValue($export->getCell($column++, $row), "Geschlecht");
            $export->setValue($export->getCell($column++, $row), "Adresszusatz_Kind");
            $export->setValue($export->getCell($column++, $row), "Strasse Kind");
            $export->setValue($export->getCell($column++, $row), "PLZ Kind");
            $export->setValue($export->getCell($column++, $row), "Stadt Kind");
            $export->setValue($export->getCell($column++, $row), "Nationalität");
            $export->setValue($export->getCell($column++, $row), "Kirche");
            $export->setValue($export->getCell($column++, $row), "Grundschule");
            $export->setValue($export->getCell($column++, $row), "PersonId_S2");
            $export->setValue($export->getCell($column++, $row), "Akad. Titel_S2");
            $export->setValue($export->getCell($column++, $row), "Nachname_S2");
            $export->setValue($export->getCell($column++, $row), "Vorname_S2");
            $export->setValue($export->getCell($column++, $row), "Adresszusatz_S2");
            $export->setValue($export->getCell($column++, $row), "Straße_S2");
            $export->setValue($export->getCell($column++, $row), "PLZ_S2");
            $export->setValue($export->getCell($column++, $row), "Ort_S2");
            $export->setValue($export->getCell($column++, $row), "Mail_S2");
            $export->setValue($export->getCell($column++, $row), "Mail_S2_Zwei");
            $export->setValue($export->getCell($column++, $row), "Bemerkung_S2");
            $export->setValue($export->getCell($column++, $row), "PersonId_S1");
            $export->setValue($export->getCell($column++, $row), "Titel_S1");
            $export->setValue($export->getCell($column++, $row), "Nachname_S1");
            $export->setValue($export->getCell($column++, $row), "Vorname_S1");
            $export->setValue($export->getCell($column++, $row), "Adresszusatz_S1");
            $export->setValue($export->getCell($column++, $row), "Straße_S1");
            $export->setValue($export->getCell($column++, $row), "PLZ_S1");
            $export->setValue($export->getCell($column++, $row), "Ort_S1");
            $export->setValue($export->getCell($column++, $row), "Mail_S1");
            $export->setValue($export->getCell($column++, $row), "Mail_S1_Zwei");
            $export->setValue($export->getCell($column++, $row), "Bemerkung_S1");
            $export->setValue($export->getCell($column++, $row), "Zugang");
            $export->setValue($export->getCell($column++, $row), "Abgang");
            $export->setValue($export->getCell($column++, $row), "stadtbezirk");
            $export->setValue($export->getCell($column++, $row), "Mailschüler");
            $export->setValue($export->getCell($column++, $row), "Masern");
            $export->setValue($export->getCell($column++, $row), "1. Framddsprache");
            $export->setValue($export->getCell($column++, $row), "1 ab JG");
            $export->setValue($export->getCell($column++, $row), "2. Framddsprache");
            $export->setValue($export->getCell($column++, $row), "2 ab JG");
            $export->setValue($export->getCell($column++, $row), "3. Framddsprache");
            $export->setValue($export->getCell($column++, $row), "3 ab JG");
            $export->setValue($export->getCell($column++, $row), "Benutzernamen");
            $export->setValue($export->getCell($column, $row), "Nicht dt. Herkunftssprache");

            foreach ($TableContent as $RowContent) {
                $column = 0;
                $row++;

                $export->setValue($export->getCell($column++, $row), $RowContent['PersonId']);
                $export->setValue($export->getCell($column++, $row), $RowContent['StudentNumber']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Division']);
                $export->setValue($export->getCell($column++, $row), $RowContent['PersonGroupKL']);
                $export->setValue($export->getCell($column++, $row), $RowContent['PersonGroupTeam']);
                $export->setValue($export->getCell($column++, $row), $RowContent['PersonGroupG']);
                $export->setValue($export->getCell($column++, $row), $RowContent['LastName']);
                $export->setValue($export->getCell($column++, $row), $RowContent['CallName']);
                $export->setValue($export->getCell($column++, $row), $RowContent['FirstName']);
                $export->setValue($export->getCell($column++, $row), $RowContent['SecondName']);
                $export->setValue($export->getCell($column++, $row), $RowContent['PersonGroupTutor']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Birthday']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Birthplace']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Gender']);
                $export->setValue($export->getCell($column++, $row), $RowContent['AddressRemark']);
                $export->setValue($export->getCell($column++, $row), $RowContent['ExcelStreet']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Code']);
                $export->setValue($export->getCell($column++, $row), $RowContent['City']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Nationality']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Denomination']);
                $export->setValue($export->getCell($column++, $row), $RowContent['LeavingSchool']);
                $export->setValue($export->getCell($column++, $row), $RowContent['PersonIdS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['TitleS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['LastNameS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['FirstNameS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['AddressRemarkS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['ExcelStreetS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['CodeS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['CityS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['MailS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Mail2S2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['RemarkS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['PersonIdS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['TitleS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['LastNameS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['FirstNameS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['AddressRemarkS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['ExcelStreetS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['CodeS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['CityS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['MailS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Mail2S1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['RemarkS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['EnterDate']);
                $export->setValue($export->getCell($column++, $row), $RowContent['LeaveDate']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Region']);
                $export->setValue($export->getCell($column++, $row), $RowContent['MailExcel']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Masern']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Foreign_Language1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Foreign_Language1_JG']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Foreign_Language2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Foreign_Language2_JG']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Foreign_Language3']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Foreign_Language3_JG']);
                $export->setValue($export->getCell($column++, $row), $RowContent['UserName']);
                $export->setValue($export->getCell($column++, $row), $RowContent['MigrationBackground']);
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }
        return false;
    }
}