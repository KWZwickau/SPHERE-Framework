<?php
namespace SPHERE\Application\Reporting\Custom\Coswig\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Reporting\Standard\Person\Person as PersonStandard;

class Service
{
    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createClassList(TblDivisionCourse $tblDivisionCourse)
    {

        $TableContent = array();
        $CountNumber = 1;
        if(!empty($tblPersonList = $tblDivisionCourse->getStudents())) {
            array_walk($tblPersonList, function(TblPerson $tblPerson) use (&$TableContent, &$CountNumber) {
                $item['Number'] = $CountNumber++;
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['StreetName'] = $item['StreetNumber'] = $item['ExcelStreet'] = $item['Code'] = $item['City'] = $item['District'] = '';
                $item['PhoneNumbersPrivate'] = $item['ExcelPhoneNumbersPrivate'] = '';
                $item['PhoneNumbersBusiness'] = $item['ExcelPhoneNumbersBusiness'] = '';
                $item['MailAddress'] = $item['ExcelMailAddress'] = '';
                // Address
                $item = PersonStandard::useService()->getAddressDataFromPerson($tblPerson, $item);
                // PhoneNumbers
                $phoneNumbersPrivate = array();
                $phoneNumbersBusiness = array();
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if($phoneList) {
                    foreach($phoneList as $phone) {
                        if($phone->getTblType()->getName() == 'Privat') {
                            $phoneNumbersPrivate[] = $phone->getTblPhone()->getNumber();
                        } elseif($phone->getTblType()->getName() == 'Geschäftlich') {
                            $phoneNumbersBusiness[] = $phone->getTblPhone()->getNumber();
                        }
                    }
                    if(!empty($phoneNumbersPrivate)) {
                        $item['PhoneNumbersPrivate'] = implode('<br>', $phoneNumbersPrivate);
                        $item['ExcelPhoneNumbersPrivate'] = $phoneNumbersPrivate;
                    }
                    if(!empty($phoneNumbersBusiness)) {
                        $item['PhoneNumbersBusiness'] = implode('<br>', $phoneNumbersBusiness);
                        $item['ExcelPhoneNumbersBusiness'] = $phoneNumbersBusiness;
                    }
                }
                // E-Mail
                $ContactMailList = array();
                $tblMailList = Mail::useService()->getMailAllByPerson($tblPerson);
                if($tblMailList) {
                    foreach($tblMailList as $tblMail) {
                        if($tblMail->getTblMail()) {
                            $ContactMailList[] = $tblMail->getTblMail()->getAddress();
                        }
                    }
                    if(!empty($ContactMailList)) {
                        $item['MailAddress'] = implode('<br>', $ContactMailList);
                        $item['ExcelMailAddress'] = $ContactMailList;
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
     * @return \SPHERE\Application\Document\Storage\FilePointer
     */
    public function createClassListExcel($TableContent, $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = $row = 0;
        $export->setValue($export->getCell($column++, $row), "Nachname");
        $export->setValue($export->getCell($column++, $row), "Vorname");
        $export->setValue($export->getCell($column++, $row), "Geb.-Datum");
        $export->setValue($export->getCell($column++, $row), "Ortsteil");
        $export->setValue($export->getCell($column++, $row), "Straße");
        $export->setValue($export->getCell($column++, $row), "PLZ");
        $export->setValue($export->getCell($column++, $row), "Ort");
        $export->setValue($export->getCell($column++, $row), "Tel. Privat");
        $export->setValue($export->getCell($column++, $row), "Tel. Geschäftlich");
        $export->setValue($export->getCell($column, $row), "E-Mail");
        // Table Head
        $export->setStyle($export->getCell(0, $row), $export->getCell(9, $row))->setFontBold()->setBorderAll()->setBorderBottom(2);
        $export->setStyle($export->getCell(2, $row), $export->getCell(2, $row))->setFontSize(10);
        $export->setStyle($export->getCell(7, $row), $export->getCell(7, $row))->setFontSize(10);
        $row = 0;
        foreach($TableContent as $PersonData) {
            $row++;
            $column = 0;
            // set border for each Person
            $export->setStyle($export->getCell(0, $row), $export->getCell(9, $row))->setBorderTop();
            $PhonePRow = $PhoneBRow = $MailRow = $row;
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthday']);
            $export->setValue($export->getCell($column++, $row), $PersonData['District']);
            $export->setValue($export->getCell($column++, $row), $PersonData['ExcelStreet']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Code']);
            $export->setValue($export->getCell($column, $row), $PersonData['City']);
            if(is_array($PersonData['ExcelPhoneNumbersPrivate'])) {
                foreach($PersonData['ExcelPhoneNumbersPrivate'] as $PhonePrivate) {
                    $export->setValue($export->getCell(7, $PhonePRow++), $PhonePrivate);
                }
            }
            if(is_array($PersonData['ExcelPhoneNumbersBusiness'])) {
                foreach($PersonData['ExcelPhoneNumbersBusiness'] as $PhoneBusiness) {
                    $export->setValue($export->getCell(8, $PhoneBRow++), $PhoneBusiness);
                }
            }
            if(is_array($PersonData['ExcelMailAddress'])) {
                foreach($PersonData['ExcelMailAddress'] as $Mail) {
                    $export->setValue($export->getCell(9, $MailRow++), $Mail);
                }
            }
            if($row < ($PhonePRow - 1)) {
                $row = ($PhonePRow - 1);
            }
            if($row < ($PhoneBRow - 1)) {
                $row = ($PhoneBRow - 1);
            }
            if($row < ($MailRow - 1)) {
                $row = ($MailRow - 1);
            }
        }
        // Table Border
        $export->setStyle($export->getCell(0, 1), $export->getCell(9, $row))
            ->setFontSize(9)
            ->setBorderVertical()
            ->setBorderLeft()
            ->setBorderRight()
            ->setBorderBottom();
        // Column Width
        $column = 0;
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(10);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(10);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(20);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(5);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(11);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(13);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row++))->setColumnWidth(17);
        $row++;
        PersonStandard::setGenderFooter($export, $tblPersonList, $row);
        $row++;
        $export->setValue($export->getCell(0, $row), 'Stand '.date("d.m.Y"));
        $export->setStyle($export->getCell(0, $row), $export->getCell(1, $row))->mergeCells();
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }
}