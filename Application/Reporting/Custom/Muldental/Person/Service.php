<?php
namespace SPHERE\Application\Reporting\Custom\Muldental\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Reporting\Standard\Person\Person;

class Service
{

    /**
     * @param array $tblPersonList
     *
     * @return array
     */
    public function createClassList(array $tblPersonList)
    {

        if(!empty($tblPersonList)) {
            // sort by LastName and FirstName
            $LastName = $FirstName = array();
            /** @var TblPerson $Person */
            foreach($tblPersonList as $key => $Person) {
                $LastName[$key] = strtoupper($Person->getLastName());
                $FirstName[$key] = strtoupper($Person->getLastName());
            }
            array_multisort($LastName, SORT_ASC, $FirstName, SORT_ASC, $tblPersonList);
        }
        $TableContent = array();
        $tblYearList = Term::useService()->getYearByNow();
        if(!empty($tblPersonList)) {
            array_walk($tblPersonList, function(TblPerson $tblPerson) use (&$TableContent, $tblYearList) {
                // Content
                $item['Division'] = '';
                $item['Type'] = $item['TypeExcel'] = '';
                $item['Mentor'] = '';
                $item['Gender'] = $tblPerson->getGenderString();
                $item['GenderExcel'] = '';
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = '';
                $item['PhoneNumbersPrivate'] = $item['ExcelPhoneNumbersPrivate'] = '';
                $item['PhoneNumbersBusiness'] = $item['ExcelPhoneNumbersBusiness'] = '';
                $item['PhoneNumbersGuardian1'] = $item['ExcelPhoneNumbersGuardian1'] = '';
                $item['PhoneNumbersGuardian2'] = $item['ExcelPhoneNumbersGuardian2'] = '';
                $item['MailAddress'] = $item['ExcelMailAddress'] = '';
                $item = Person::useService()->getAddressDataFromPerson($tblPerson, $item);
                foreach($tblYearList as $tblYear) {
                    if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                        if(($tblDivisionCourse = $tblStudentEducation->getTblDivision())) {
                            $item['Division'] = $tblDivisionCourse->getDisplayName();
                        }
                        if(($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())) {
                            $item['Type'] = $tblSchoolType->getName();
                            $item['TypeExcel'] = $tblSchoolType->getShortName();
                        }
                        if(($tblDivisionCourseCore = $tblStudentEducation->getTblCoreGroup())) {
                            $item['Mentor'] = $tblDivisionCourseCore->getDisplayName();
                        }
                        break;
                    }
                }
                // Gender
                if($item['Gender']) {
                    switch ($item['Gender']) {
                        case "Männlich":
                            $item['GenderExcel'] = "m";
                            break;
                        case "Weiblich":
                            $item['GenderExcel'] = "w";
                            break;
                    }
                }
                // find Guardian
                $GuardianList = array();
                if(($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, TblType::IDENTIFIER_GUARDIAN))) {
                    foreach($tblToPersonList as $tblToPerson) {
                        $GuardianList[$tblToPerson->getRanking()] = $tblToPerson->getServiceTblPersonFrom();
                    }
                }
                $ContactMailList = array();
                $phoneNumbersBusiness = array();
                if (!empty($GuardianList)) {
                    /** @var TblPerson $Guardian */
                    foreach ($GuardianList as $Key => $Guardian) {
                        // Guardian phone
                        $phoneList = Phone::useService()->getPhoneAllByPerson($Guardian);
                        if ($phoneList) {
                            $phoneNumbers = array();
                            foreach ($phoneList as $phone) {
                                if($Key == 1 && $phone->getTblType()->getName() == 'Privat'
                                    && $phone->getTblType()->getDescription() == 'Festnetz'){
                                    $phoneNumbersPrivate[] = $phone->getTblPhone()->getNumber();
                                }
                                if (// $phone->getTblType()->getName() == 'Privat' &&
                                    $phone->getTblType()->getDescription() == 'Mobil'
                                ) {
                                    $phoneNumbers[] = $phone->getTblPhone()->getNumber();
                                }
                                if ($Key == 2 && $phone->getTblType()->getName() == 'Geschäftlich'
                                    && $phone->getTblType()->getDescription() == 'Festnetz'
                                ) {
                                    $phoneNumbersBusiness[] = $phone->getTblPhone()->getNumber();
                                }
                            }
                            if (!empty($phoneNumbersPrivate)) {
                                $item['PhoneNumbersPrivate'] = implode('<br>', $phoneNumbersPrivate);
                                $item['ExcelPhoneNumbersPrivate'] = implode(";\n ", $phoneNumbersPrivate);
                            }
                            if (!empty($phoneNumbers)) {
                                $item['PhoneNumbersGuardian'.($Key)] = implode(';<br>', $phoneNumbers);
                                $item['ExcelPhoneNumbersGuardian'.($Key)] = implode(";\n ", $phoneNumbers);
                            }
                            if (!empty($phoneNumbersBusiness)) {
                                $item['PhoneNumbersBusiness'] = implode('<br>', $phoneNumbersBusiness);
                                $item['ExcelPhoneNumbersBusiness'] = implode(";\n ", $phoneNumbersBusiness);
                            }
                        }
                        // Guardian E-Mail
                        $tblMailList = Mail::useService()->getMailAllByPerson($Guardian);
                        if ($tblMailList) {
                            foreach ($tblMailList as $tblMail) {
                                if ($tblMail->getTblMail()) {
                                    if (!empty($ContactMailList)) {
                                        $ContactMailList[] = $tblMail->getTblMail()->getAddress();
                                    } else {
                                        $ContactMailList[] = $tblMail->getTblMail()->getAddress();
                                    }
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
                    $item['MailAddress'] .= implode(';<br>', $ContactMailList);
                    $item['ExcelMailAddress'] = implode(";\n ", $ContactMailList);
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
     * @return FilePointer
     */
    public function createClassListExcel($TableContent, $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $column = $row = 0;
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell($column++, $row), "Kl.");
        $export->setValue($export->getCell($column++, $row), "Sch.");
        $export->setValue($export->getCell($column++, $row), "Gruppe");
        $export->setValue($export->getCell($column++, $row), "G");
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Vorname");
        $export->setValue($export->getCell($column++, $row), "Straße");
        $export->setValue($export->getCell($column++, $row), "PLZ");
        $export->setValue($export->getCell($column++, $row), "Wohnort");
        $export->setValue($export->getCell($column++, $row), "Ortsteil");
        $export->setValue($export->getCell($column++, $row), "S1 Tel. privat");
        $export->setValue($export->getCell($column++, $row), "S1 Tel. dienstlich");
        $export->setValue($export->getCell($column++, $row), "S1 Tel.");
        $export->setValue($export->getCell($column++, $row), "S2 Tel.");
        $export->setValue($export->getCell($column++, $row), "E-Mail");
        $export->setValue($export->getCell($column, $row++), "Geburtsd.");
        // Table Head
        $export->setStyle($export->getCell(0, 0), $export->getCell(15, 0))
            ->setFontBold()
            ->setBorderAll()
            ->setBorderBottom(2);
        $export->setStyle($export->getCell(3, 0), $export->getCell(3, 0))
            ->setFontSize(10);
        $export->setStyle($export->getCell(10, 0), $export->getCell(10, 0))
            ->setFontSize(10);
        $MentorGroup = '';
        foreach ($TableContent as $PersonData) {
            // set border for each Person
            $export->setStyle($export->getCell(0, $row), $export->getCell(15, $row))->setBorderTop();
            $export->setStyle($export->getCell(10, $row), $export->getCell(14, $row))->setWrapText();
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Division']);
            $export->setValue($export->getCell($column++, $row), $PersonData['TypeExcel']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Mentor']);
            $export->setValue($export->getCell($column++, $row), $PersonData['GenderExcel']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['StreetName'].' '.$PersonData['StreetNumber']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Code']);
            $export->setValue($export->getCell($column++, $row), $PersonData['City']);
            $export->setValue($export->getCell($column++, $row), $PersonData['District']);
            $export->setValue($export->getCell($column++, $row), $PersonData['ExcelPhoneNumbersPrivate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['ExcelPhoneNumbersBusiness']);
            $export->setValue($export->getCell($column++, $row), $PersonData['ExcelPhoneNumbersGuardian1']);
            $export->setValue($export->getCell($column++, $row), $PersonData['ExcelPhoneNumbersGuardian2']);
            $export->setValue($export->getCell($column++, $row), $PersonData['ExcelMailAddress']);
            $export->setValue($export->getCell($column, $row++), $PersonData['Birthday']);
        }
        // Table Border
        $export->setStyle($export->getCell(0, 1), $export->getCell(15, $row))->setAlignmentMiddle()->setBorderAll();
        // Column Width
        $column = 0;
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(3)->setFontSize(9);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(4)->setFontSize(9);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(7)->setFontSize(9);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(3)->setFontSize(9);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(13)->setFontSize(9);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(16)->setFontSize(9);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(21)->setFontSize(9);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(6)->setFontSize(9);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12)->setFontSize(9);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(10)->setFontSize(9);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(14)->setFontSize(9);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(14)->setFontSize(9);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(14)->setFontSize(9);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(14)->setFontSize(9);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(32)->setFontSize(9);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row++))->setColumnWidth(9)->setFontSize(9);
        $row++;
        Person::setGenderFooter($export, $tblPersonList, $row, 0, 3);
        $row++;
        $export->setValue($export->getCell(0, $row), 'Stand '.date("d.m.Y"));
        $export->setStyle($export->getCell(0, $row), $export->getCell(4, $row))->mergeCells();
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }
}