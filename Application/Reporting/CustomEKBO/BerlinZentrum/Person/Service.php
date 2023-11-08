<?php
namespace SPHERE\Application\Reporting\CustomEKBO\BerlinZentrum\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblType as TblTypeMail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Setting\User\Account\Account;
use SPHERE\System\Extension\Extension;

class Service extends Extension
{

    /**
     * @return array
     */
    public function createSuSList()
    {

        $TableContent = array();
        $tblPersonList = array();
        if(($tblGroupStudent = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT))){
            $tblPersonList = $tblGroupStudent->getPersonList();
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
        }
        if(empty($tblPersonList)){
            return $TableContent;
        }
        array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {
            // Content
            $item['PersonId'] = $tblPerson->getId();
            $item['StudentNumber'] = '';
            $item['Deactivated'] = 'ja';
            $item['Level'] = '';
            $item['KL'] = '';
            $item['Team'] = '';
            $item['Tudor'] = '';
            $item['LastName'] = $tblPerson->getLastName();
            $item['CallName'] = $tblPerson->getCallName();
            $item['FirstName'] = $tblPerson->getFirstName();
            $item['SecondName'] = $tblPerson->getSecondName();
            $item['Birthday'] = '';
            $item['Birthplace'] = '';
            $item['Gender'] = '';
            $item['ExcelStreet'] = '';
            $item['Code'] = '';
            $item['City'] = '';
            $item['PhonePrivate'] = '';
            $item['MobilePrivate'] = '';
            $item['PhoneBusiness'] = '';
            $item['MobileBusiness'] = '';
            $item['EmergencyPhone'] = '';
            $item['EmergencyMobile'] = '';
            $item['AddressRemark'] = '';
            $item['Nationality'] = '';
            $item['Denomination'] = '';
            $item['LeavingSchool'] = '';
            $item['AssistanceActivity'] = '';
            for($i = 1; $i <= 2; $i++){
                $item['PersonIdS'.$i] = '';
                $item['TitleS'.$i] = '';
                $item['LastNameS'.$i] = '';
                $item['FirstNameS'.$i] = '';
                $item['AddressRemarkS'.$i] = '';
                $item['ExcelStreetS'.$i] = '';
                $item['CodeS'.$i] = '';
                $item['CityS'.$i] = '';
                $item['PhonePrivateS'.$i] = '';
                $item['MobilePrivateS'.$i] = '';
                $item['PhoneBusinessS'.$i] = '';
                $item['MobileBusinessS'.$i] = '';
                $item['EmergencyPhoneS'.$i] = '';
                $item['EmergencyMobileS'.$i] = '';
                $item['MailS'.$i] = '';
                $item['Mail2S'.$i] = '';
                $item['CustodyS'.$i] = '';
                $item['RemarkS'.$i] = '';
                $item['AssistanceActivityS'.$i] = '';
            }
            $item['EnterDate'] = '';
            $item['LeaveDate'] = '';
            $item['Region'] = '';
            $item['Mail'] = '';
            $item['MailExcel'] = '';
            $item['Masern'] = '';
            for($j = 1; $j <= 3; $j++) {
                $item['Foreign_Language'.$j] = '';
                $item['Foreign_Language'.$j.'_JG'] = '';
            }
            $tblDivision = false;
            if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))){
                $item['Level'] = $tblStudentEducation->getLevel();
                $tblDivision = $tblStudentEducation->getTblDivision();
                if($tblDivision){
                    $item['KL'] = $tblDivision->getName();
                }
                $tblCoreGroup = $tblStudentEducation->getTblCoreGroup();
                if($tblDivision){
                    $item['Deactivated'] = '';
                }
                if($tblCoreGroup
                    && ($tblPersonTeacherList = $tblCoreGroup->getDivisionTeacherList())){
                    $TeacherAcronymList = array();
                    foreach($tblPersonTeacherList as $tblPersonTeacher){
                        if(($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPersonTeacher))) {
                            if(($Acronym = $tblTeacher->getAcronym())) {
                                $TeacherAcronymList[] = $Acronym;
                            }
                        }
                    }
                    if(!empty($TeacherAcronymList)){
                        $item['Tudor'] = implode(', ', $TeacherAcronymList);
                    }
                }
            }
            // unterrichtsgruppen
            $TeachingList = array();
            if(($tblYearList = Term::useService()->getYearByNow())){
                foreach($tblYearList as $tblYear){
                    if(($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByStudentAndYear($tblPerson, $tblYear))){
                        foreach($tblDivisionCourseList as $tblDivisionCourse){
                            if($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_TEACHING_GROUP){
                                $TeachingList[] = $tblDivisionCourse->getName();
                            } else {
                                if(($tblDivisionCourseLinkList =  DivisionCourse::useService()->getAboveDivisionCourseListBySubDivisionCourse($tblDivisionCourse))){
                                    foreach($tblDivisionCourseLinkList as $tblDivisionCourseLink){
                                        if($tblDivisionCourseLink->getTypeIdentifier() == TblDivisionCourseType::TYPE_TEACHING_GROUP){
                                            $TeachingList[] = $tblDivisionCourseLink->getName();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // alles außer Zahlen entfernen
            if(!empty($TeachingList)){
                foreach($TeachingList as &$Teaching){
                    $Teaching = preg_replace('/[^0-9]/', '', $Teaching);
                }
                $TeachingList = array_filter($TeachingList);
            }
            if(!empty($TeachingList)){
                $item['Team'] = implode(', ', $TeachingList);
            }
            $item['UserName'] = '';
            if(($tblUserAccount = Account::useService()->getUserAccountByPerson($tblPerson))
            && ($tblAccount = $tblUserAccount->getServiceTblAccount())){
                $item['UserName'] = $tblAccount->getUsername();
            }
            $item['MigrationBackground'] = '';
            if($tblStudent = Student::useService()->getStudentByPerson($tblPerson)){
                $item['StudentNumber'] = $tblStudent->getIdentifier();
            }

            // Allgemeine Daten Schüler
            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                if(($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())){
                    $item['Birthday'] = $tblCommonBirthDates->getBirthday();
                    $item['Birthplace'] = $tblCommonBirthDates->getBirthplace();
                    if(($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())){
                        $item['Gender'] = $tblCommonGender->getName();
                    }
                }
                if(($tblCommonInformation = $tblCommon->getTblCommonInformation())){
                    $item['Nationality'] = $tblCommonInformation->getNationality();
                    $item['Denomination'] = $tblCommonInformation->getDenomination();
                    $item['AssistanceActivity'] = $tblCommonInformation->getAssistanceActivity();
                }
            }
            // Address Schüler
            if (( $tblAdress = Address::useService()->getAddressByPerson($tblPerson) )) {
                $item['ExcelStreet'] = $tblAdress->getStreetName().' '.$tblAdress->getStreetNumber();
                $item['Code'] = $tblAdress->getTblCity()->getCode();
                $item['City'] = $tblAdress->getTblCity()->getName();
                $RemarkToPerson = '';
                if(($tblToPersonAddressList = Address::useService()->getToPersonAllByAddress($tblAdress))) {
                    foreach($tblToPersonAddressList as $tblToPersonAddress){
                        if($tblToPersonAddress->getServiceTblPerson()->getId() == $tblPerson->getId()){
                            $RemarkToPerson = $tblToPersonAddress->getRemark();
                        }
                    }
                }
                $item['AddressRemark'] = $RemarkToPerson;
                if($tblAdress->getRegion()){
                    $item['Region'] = $tblAdress->getRegion();
                } else {
                    $item['Region'] = Address::useService()->getRegionStringByCode($tblAdress->getTblCity()->getCode());
                }
            }
            // Telefon Schüler
            if(($tblToPersonPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson))){
                $phoneFP = array();
                $phoneMP = array();
                $phoneFB = array();
                $phoneMB = array();
                $phoneEP = array();
                $phoneEM = array();
                foreach($tblToPersonPhoneList as $tblToPersonPhone){
                    $PhoneNumber = $tblToPersonPhone->getTblPhone()->getNumber();
                    $TypeName = $tblToPersonPhone->getTblType()->getName();
                    $TypeDescription = $tblToPersonPhone->getTblType()->getDescription();
                    if($TypeDescription == 'Festnetz' && $TypeName == 'Privat'){
                        $phoneFP[] = $PhoneNumber;
                    }
                    if($TypeDescription == 'Mobil' && $TypeName == 'Privat'){
                        $phoneMP[] = $PhoneNumber;
                    }
                    if($TypeDescription == 'Festnetz' && $TypeName == 'Geschäftlich'){
                        $phoneFB[] = $PhoneNumber;
                    }
                    if($TypeDescription == 'Mobil' && $TypeName == 'Geschäftlich'){
                        $phoneMB[] = $PhoneNumber;
                    }
                    if($TypeName == 'Notfall' && $TypeDescription == 'Festnetz'){
                        $phoneEP[] = $PhoneNumber;
                    }
                    if($TypeName == 'Notfall' && $TypeDescription == 'Mobil'){
                        $phoneEM[] = $PhoneNumber;
                    }
                }
                if(!empty($phoneFP)){
                    $item['PhonePrivate'] = implode('; ', $phoneFP);
                }
                if(!empty($phoneMP)){
                    $item['MobilePrivate'] = implode('; ', $phoneMP);
                }
                if(!empty($phoneFB)){
                    $item['PhoneBusiness'] = implode('; ', $phoneFB);
                }
                if(!empty($phoneMB)){
                    $item['MobileBusiness'] = implode('; ', $phoneMB);
                }
                if(!empty($phoneEP)){
                    $item['EmergencyPhone'] = implode('; ', $phoneEP);
                }
                if(!empty($phoneEM)){
                    $item['EmergencyMobile'] = implode('; ', $phoneEM);
                }
            }
            // Schülerakte
            if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                if(($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::ARRIVE))){
                    if(($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))){
                        $item['EnterDate'] = $tblStudentTransfer->getTransferDate();
                        if(($tblCompany = $tblStudentTransfer->getServiceTblCompany())){
                            $item['LeavingSchool'] = $tblCompany->getName();
                        }
                    }
                }
                if(($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::LEAVE))){
                    if(($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))){
                        $item['LeaveDate'] = $tblStudentTransfer->getTransferDate();
                    }
                }
                if(($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier(TblStudentSubjectType::TYPE_FOREIGN_LANGUAGE))){
                    if(($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType))){
                        foreach($tblStudentSubjectList as $tblStudentSubject){
                            for($k = 1; $k <= 3; $k++){
                                if($tblStudentSubject->getServiceTblSubject()
                                    && $tblStudentSubject->getTblStudentSubjectRanking()
                                    && $tblStudentSubject->getTblStudentSubjectRanking()->getName() == $k
                                ){
                                    $item['Foreign_Language'.$k] = $tblStudentSubject->getServiceTblSubject()->getName();
                                    if(($level = $tblStudentSubject->getLevelFrom())){
                                        $item['Foreign_Language'.$k.'_JG'] = $level;
                                    }
                                }
                            }
                        }
                    }
                }
                if(($tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord())){
                    if(($tblMasernDocumentType = $tblStudentMedicalRecord->getMasernDocumentType())){
                        $item['Masern'] = $tblMasernDocumentType->getTextShort();
                    }
                }
                if($tblStudent->getHasMigrationBackground()){
                    $item['MigrationBackground'] = 'Ja';
                } else {
                    $item['MigrationBackground'] = 'Nein';
                }
            }
            // Sorgeberechtigte
            if(($tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN))){
                if(($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))){
                    foreach($tblToPersonList as $tblToPerson){
                        if(($tblPersonS = $tblToPerson->getServiceTblPersonFrom())){
                            $Number = $tblToPerson->getRanking();
                            $item['PersonIdS'.$Number] = $tblPersonS->getId();
                            $item['TitleS'.$Number] = $tblPersonS->getTitle();
                            $item['LastNameS'.$Number] = $tblPersonS->getLastName();
                            $item['FirstNameS'.$Number] = $tblPersonS->getFirstName();

                            // Address
                            if (( $tblAdress = Address::useService()->getAddressByPerson($tblPersonS) )) {
                                $item['ExcelStreetS'.$Number] = '';
                                $item['CodeS'.$Number] = '';
                                $item['CityS'.$Number] = '';
                                $item['ExcelStreetS'.$Number] = $tblAdress->getStreetName().' '.$tblAdress->getStreetNumber();
                                $item['CodeS'.$Number] = $tblAdress->getTblCity()->getCode();
                                $item['CityS'.$Number] = $tblAdress->getTblCity()->getName();
                                $RemarkToPerson = '';
                                if(($tblToPersonAddressList = Address::useService()->getToPersonAllByAddress($tblAdress))) {
                                    foreach($tblToPersonAddressList as $tblToPersonAddress){
                                        if($tblToPersonAddress->getServiceTblPerson()->getId() == $tblPersonS->getId()){
                                            $RemarkToPerson = $tblToPersonAddress->getRemark();
                                        }
                                    }
                                }
                                $item['AddressRemarkS'.$Number] = $RemarkToPerson;
                            }
                            // Telefon Sorgeberechtigte
                            if(($tblToPersonPhoneList = Phone::useService()->getPhoneAllByPerson($tblPersonS))){
                                $phoneFP = array();
                                $phoneMP = array();
                                $phoneFB = array();
                                $phoneMB = array();
                                $phoneEP = array();
                                $phoneEM = array();
                                foreach($tblToPersonPhoneList as $tblToPersonPhone){
                                    $PhoneNumber = $tblToPersonPhone->getTblPhone()->getNumber();
                                    $TypeName = $tblToPersonPhone->getTblType()->getName();
                                    $TypeDescription = $tblToPersonPhone->getTblType()->getDescription();
                                    if($TypeDescription == 'Festnetz' && $TypeName == 'Privat'){
                                        $phoneFP[] = $PhoneNumber;
                                    }
                                    if($TypeDescription == 'Mobil' && $TypeName == 'Privat'){
                                        $phoneMP[] = $PhoneNumber;
                                    }
                                    if($TypeDescription == 'Festnetz' && $TypeName == 'Geschäftlich'){
                                        $phoneFB[] = $PhoneNumber;
                                    }
                                    if($TypeDescription == 'Mobil' && $TypeName == 'Geschäftlich'){
                                        $phoneMB[] = $PhoneNumber;
                                    }
                                    if($TypeName == 'Notfall' && $TypeDescription == 'Festnetz'){
                                        $phoneEP[] = $PhoneNumber;
                                    }
                                    if($TypeName == 'Notfall' && $TypeDescription == 'Mobil'){
                                        $phoneEM[] = $PhoneNumber;
                                    }
                                }
                                if(!empty($phoneFP)){
                                    $item['PhonePrivateS'.$Number] = implode('; ', $phoneFP);
                                }
                                if(!empty($phoneMP)){
                                    $item['MobilePrivateS'.$Number] = implode('; ', $phoneMP);
                                }
                                if(!empty($phoneFB)){
                                    $item['PhoneBusinessS'.$Number] = implode('; ', $phoneFB);
                                }
                                if(!empty($phoneMB)){
                                    $item['MobileBusinessS'.$Number] = implode('; ', $phoneMB);
                                }
                                if(!empty($phoneEP)){
                                    $item['EmergencyPhoneS'.$Number] = implode('; ', $phoneEP);
                                }
                                if(!empty($phoneEM)){
                                    $item['EmergencyMobileS'.$Number] = implode('; ', $phoneEM);
                                }
                            }
                            if(($tblToPersonMailList = Mail::useService()->getMailAllByPerson($tblPersonS))){
                                foreach($tblToPersonMailList as $tblToPersonMail){
                                    $tblType = $tblToPersonMail->getTblType();
                                    $tblMail = $tblToPersonMail->getTblMail();
                                    if($tblType->getName() == TblTypeMail::VALUE_PRIVATE) {
                                        $item['MailS'.$Number] = $tblMail->getAddress();
                                    }elseif($tblType->getName() == TblTypeMail::VALUE_BUSINESS) {
                                        $item['Mail2S'.$Number] = $tblMail->getAddress();
                                    }
                                }
                            }
                            // Elternsprecher
                            if($tblDivision && ($tblPersonCustodyList = $tblDivision->getCustody())){
                                foreach($tblPersonCustodyList as $tblPersonCustody){
                                    if($tblPersonCustody->getId() == $tblPersonS->getId()){
                                        $item['CustodyS'.$Number] = $tblDivision->getName();
                                    }
                                }
                            }
                            // Mitarbeitsbereitschaft
                            if (($tblCommon = Common::useService()->getCommonByPerson($tblPersonS))) {
                                if(($tblCommonInformation = $tblCommon->getTblCommonInformation())){
                                    $item['AssistanceActivityS'.$Number] = $tblCommonInformation->getAssistanceActivity();
                                }
                            }

                            if(($tblCommonS = $tblPerson->getCommon())){
                                $item['RemarkS'.$Number] = $tblCommonS->getRemark();
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
                    $item['Mail'] = implode('<br>', $ContactMailList);
                    $item['MailExcel'] = implode('; ', $ContactMailList);
                }
            }

            array_push($TableContent, $item);
        });
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
            $export->setValue($export->getCell($column++, $row), "Tutor");
            $export->setValue($export->getCell($column++, $row), "Nachname");
            $export->setValue($export->getCell($column++, $row), "Rufname");
            $export->setValue($export->getCell($column++, $row), "Vorname");
            $export->setValue($export->getCell($column++, $row), "Zweiter Vorname");
            $export->setValue($export->getCell($column++, $row), "Deaktiviert");
            $export->setValue($export->getCell($column++, $row), "Geburtsdatum");
            $export->setValue($export->getCell($column++, $row), "Geburtsort");
            $export->setValue($export->getCell($column++, $row), "Geschlecht");
            $export->setValue($export->getCell($column++, $row), "Adresszusatz_Kind");
            $export->setValue($export->getCell($column++, $row), "Strasse Kind");
            $export->setValue($export->getCell($column++, $row), "PLZ Kind");
            $export->setValue($export->getCell($column++, $row), "Stadt Kind");
            $export->setValue($export->getCell($column++, $row), "Privat_Festnetz");
            $export->setValue($export->getCell($column++, $row), "Privat_Mobil");
            $export->setValue($export->getCell($column++, $row), "Geschäftlich_Festnetz");
            $export->setValue($export->getCell($column++, $row), "Geschäftlich_Mobil");
            $export->setValue($export->getCell($column++, $row), "Notfall_Festnetz");
            $export->setValue($export->getCell($column++, $row), "Notfall_Mobil");
            $export->setValue($export->getCell($column++, $row), "Nationalität");
            $export->setValue($export->getCell($column++, $row), "Kirche");
            $export->setValue($export->getCell($column++, $row), "Grundschule");
            $export->setValue($export->getCell($column++, $row), "Mitarbeit Tätigkeit");
            $export->setValue($export->getCell($column++, $row), "PersonId_S2");
            $export->setValue($export->getCell($column++, $row), "Akad. Titel_S2");
            $export->setValue($export->getCell($column++, $row), "Nachname_S2");
            $export->setValue($export->getCell($column++, $row), "Vorname_S2");
            $export->setValue($export->getCell($column++, $row), "Adresszusatz_S2");
            $export->setValue($export->getCell($column++, $row), "Straße_S2");
            $export->setValue($export->getCell($column++, $row), "PLZ_S2");
            $export->setValue($export->getCell($column++, $row), "Ort_S2");
            $export->setValue($export->getCell($column++, $row), "Privat_Festnetz_S2");
            $export->setValue($export->getCell($column++, $row), "Privat_Mobil_S2");
            $export->setValue($export->getCell($column++, $row), "Geschäftlich_Festnetz_S2");
            $export->setValue($export->getCell($column++, $row), "Geschäftlich_Mobil_S2");
            $export->setValue($export->getCell($column++, $row), "Notfall_Festnetz_S2");
            $export->setValue($export->getCell($column++, $row), "Notfall_Mobil_S2");
            $export->setValue($export->getCell($column++, $row), "Mail_S2");
            $export->setValue($export->getCell($column++, $row), "Mail_S2_Zwei");
            $export->setValue($export->getCell($column++, $row), "Elternvertretung_S2");
            $export->setValue($export->getCell($column++, $row), "Bemerkung_S2");
            $export->setValue($export->getCell($column++, $row), "Mitarbeit Tätigkeit_S2");
            $export->setValue($export->getCell($column++, $row), "PersonId_S1");
            $export->setValue($export->getCell($column++, $row), "Titel_S1");
            $export->setValue($export->getCell($column++, $row), "Nachname_S1");
            $export->setValue($export->getCell($column++, $row), "Vorname_S1");
            $export->setValue($export->getCell($column++, $row), "Adresszusatz_S1");
            $export->setValue($export->getCell($column++, $row), "Straße_S1");
            $export->setValue($export->getCell($column++, $row), "PLZ_S1");
            $export->setValue($export->getCell($column++, $row), "Ort_S1");
            $export->setValue($export->getCell($column++, $row), "Privat_Festnetz_S1");
            $export->setValue($export->getCell($column++, $row), "Privat_Mobil_S1");
            $export->setValue($export->getCell($column++, $row), "Geschäftlich_Festnetz_S1");
            $export->setValue($export->getCell($column++, $row), "Geschäftlich_Mobil_S1");
            $export->setValue($export->getCell($column++, $row), "Notfall_Festnetz_S1");
            $export->setValue($export->getCell($column++, $row), "Notfall_Mobil_S1");
            $export->setValue($export->getCell($column++, $row), "Mail_S1");
            $export->setValue($export->getCell($column++, $row), "Mail_S1_Zwei");
            $export->setValue($export->getCell($column++, $row), "Elternvertretung_S1");
            $export->setValue($export->getCell($column++, $row), "Bemerkung_S1");
            $export->setValue($export->getCell($column++, $row), "Mitarbeit Tätigkeit_S1");
            $export->setValue($export->getCell($column++, $row), "Zugang");
            $export->setValue($export->getCell($column++, $row), "Abgang");
            $export->setValue($export->getCell($column++, $row), "Stadtbezirk");
            $export->setValue($export->getCell($column++, $row), "Mailschüler");
            $export->setValue($export->getCell($column++, $row), "Masern");
            $export->setValue($export->getCell($column++, $row), "1. Fremdsprache");
            $export->setValue($export->getCell($column++, $row), "1 ab JG");
            $export->setValue($export->getCell($column++, $row), "2. Fremdsprache");
            $export->setValue($export->getCell($column++, $row), "2 ab JG");
            $export->setValue($export->getCell($column++, $row), "3. Fremdsprache");
            $export->setValue($export->getCell($column++, $row), "3 ab JG");
            $export->setValue($export->getCell($column++, $row), "Benutzernamen");
            $export->setValue($export->getCell($column, $row), "Nicht dt. Herkunftssprache");

            foreach ($TableContent as $RowContent) {
                $column = 0;
                $row++;

                $export->setValue($export->getCell($column++, $row), $RowContent['PersonId']);
                $export->setValue($export->getCell($column++, $row), $RowContent['StudentNumber']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Level']);
                $export->setValue($export->getCell($column++, $row), $RowContent['KL']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Team']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Tudor']);
                $export->setValue($export->getCell($column++, $row), $RowContent['LastName']);
                $export->setValue($export->getCell($column++, $row), $RowContent['CallName']);
                $export->setValue($export->getCell($column++, $row), $RowContent['FirstName']);
                $export->setValue($export->getCell($column++, $row), $RowContent['SecondName']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Deactivated']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Birthday']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Birthplace']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Gender']);
                $export->setValue($export->getCell($column++, $row), $RowContent['AddressRemark']);
                $export->setValue($export->getCell($column++, $row), $RowContent['ExcelStreet']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Code']);
                $export->setValue($export->getCell($column++, $row), $RowContent['City']);
                $export->setValue($export->getCell($column++, $row), $RowContent['PhonePrivate']);
                $export->setValue($export->getCell($column++, $row), $RowContent['MobilePrivate']);
                $export->setValue($export->getCell($column++, $row), $RowContent['PhoneBusiness']);
                $export->setValue($export->getCell($column++, $row), $RowContent['MobileBusiness']);
                $export->setValue($export->getCell($column++, $row), $RowContent['EmergencyPhone']);
                $export->setValue($export->getCell($column++, $row), $RowContent['EmergencyMobile']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Nationality']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Denomination']);
                $export->setValue($export->getCell($column++, $row), $RowContent['LeavingSchool']);
                $export->setValue($export->getCell($column++, $row), $RowContent['AssistanceActivity']);
                $export->setValue($export->getCell($column++, $row), $RowContent['PersonIdS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['TitleS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['LastNameS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['FirstNameS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['AddressRemarkS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['ExcelStreetS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['CodeS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['CityS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['PhonePrivateS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['MobilePrivateS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['PhoneBusinessS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['MobileBusinessS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['EmergencyPhoneS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['EmergencyMobileS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['MailS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Mail2S2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['CustodyS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['RemarkS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['AssistanceActivityS2']);
                $export->setValue($export->getCell($column++, $row), $RowContent['PersonIdS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['TitleS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['LastNameS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['FirstNameS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['AddressRemarkS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['ExcelStreetS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['CodeS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['CityS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['PhonePrivateS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['MobilePrivateS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['PhoneBusinessS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['MobileBusinessS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['EmergencyPhoneS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['EmergencyMobileS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['MailS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['Mail2S1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['CustodyS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['RemarkS1']);
                $export->setValue($export->getCell($column++, $row), $RowContent['AssistanceActivityS1']);
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