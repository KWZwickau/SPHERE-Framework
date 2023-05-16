<?php
namespace SPHERE\Application\Reporting\Custom\Schneeberg\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Reporting\Standard\Person\Person;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Custom\Schneeberg\Person
 */
class Service extends Extension
{

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createClassList(TblDivisionCourse $tblDivisionCourse)
    {

        $TableContent = array();
        if(($tblPersonList = $tblDivisionCourse->getStudents())) {
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {
                $item['Number'] = $count++;
                $item['LastName'] = $tblPerson->getLastName();
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['Street'] = $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = '';
                $item['Parents'] = $item['ParentJob'] = '';
                $item['Phone'] = $item['PhoneS1'] = $item['PhoneS2'] = '';
                $item['Photo'] = '';
                $item = Person::useService()->getAddressDataFromPerson($tblPerson, $item);
                if($item['StreetName'] && $item['StreetNumber']){
                    $item['Street'] = $item['StreetName'].' '.$item['StreetNumber'];
                }
                $phoneNumbers = array();
                if(($phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson))) {
                    foreach ($phoneList as $phone) {
                        if(($tblPhone = $phone->getTblPhone())){
                            $phoneNumbers[] = $tblPhone->getNumber();
                        }
                    }
                }
                if (!empty($phoneNumbers)){
                    $item['Phone'] = implode(', ', $phoneNumbers);
                }
                $tblPersonGuardList = array();
                $GuardListPhoneList = array();
                if(($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, TblType::IDENTIFIER_GUARDIAN))) {
                    foreach($tblRelationshipList as $tblToPerson) {
                        if(($tblPersonGuard = $tblToPerson->getServiceTblPersonFrom())) {
                            $Ranking = $tblToPerson->getRanking();
                            $tblPersonGuardList[$Ranking] = $tblPersonGuard;
                            if(($tblToPersonGuardList = Phone::useService()->getPhoneAllByPerson($tblPersonGuard))) {
                                foreach($tblToPersonGuardList as $tblToPersonGuard) {
                                    if(($tblPhoneGuard = $tblToPersonGuard->getTblPhone())) {
                                        $GuardListPhoneList[$Ranking][] = $tblPhoneGuard->getNumber();
                                    }
                                }
                            }
                        }
                    }
                    ksort($tblPersonGuardList);
                }
                if(!empty($GuardListPhoneList)){
                    foreach($GuardListPhoneList as $Ranking => $PhoneList){
                        switch ($Ranking) {
                            case 1:
                                $item['PhoneS1'] = implode(', ', $PhoneList);
                            break;
                            case 2:
                                $item['PhoneS2'] = implode(', ', $PhoneList);
                            break;
                        }
                    }
                }
                if(!empty($tblPersonGuardList)){
                    foreach($tblPersonGuardList as $tblPersonGuard){
                        $jobString = '( - )';
                        if(($tblCustody = Custody::useService()->getCustodyByPerson($tblPersonGuard))) {
                            $jobString = '('.
                                ($tblCustody->getOccupation()
                                    ?: ' - ').
                                ($tblCustody->getEmployment()
                                    ? ', '.$tblCustody->getEmployment()
                                    : '')
                                .')';
                        }
                        $item['ParentJob'] .= ($item['ParentJob'] != ''? ', ': '').$tblPersonGuard->getFirstSecondName().' '.$tblPersonGuard->getLastName().' '
                            .$jobString;
                        $item['Parents'] .= ($item['Parents'] != ''? ' & ': '').($tblPersonGuard->getTitle() ? $tblPersonGuard->getTitle().' ' : '')
                            .$tblPersonGuard->getFirstSecondName().' '.$tblPersonGuard->getLastName();
                    }
                }
                if(($tblStudent = $tblPerson->getStudent())){
                    if(($tblStudentAgreementAllByStudent = Student::useService()->getStudentAgreementAllByStudent($tblStudent))){
                        foreach ($tblStudentAgreementAllByStudent as $tblStudentAgreement){
                            if ($tblStudentAgreement->getTblStudentAgreementType()->getTblStudentAgreementCategory()->getId() == 1 ){
                                $item['Photo'] = 'X';
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
     * @param $PersonList
     *
     * @return FilePointer
     */
    public function createClassListExcel($TableContent)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = $row = 0;
        $export->setValue($export->getCell($column++, $row), 'Nr.');
        $export->setValue($export->getCell($column++, $row), 'Name');
        $export->setValue($export->getCell($column++, $row), 'Vorname');
        $export->setValue($export->getCell($column++, $row), 'Geburtstag');
        $export->setValue($export->getCell($column++, $row), 'Ortsteil');
        $export->setValue($export->getCell($column++, $row), 'Straße');
        $export->setValue($export->getCell($column++, $row), 'PLZ');
        $export->setValue($export->getCell($column++, $row), 'Ort');
        $export->setValue($export->getCell($column++, $row), 'Eltern');
        $export->setValue($export->getCell($column++, $row), 'Eltern (Beruf)');
        $export->setValue($export->getCell($column++, $row), 'Telefon privat');
        $export->setValue($export->getCell($column++, $row), 'S1');
        $export->setValue($export->getCell($column++, $row), 'S2');
        $export->setValue($export->getCell($column, $row++), 'FOTO');
        $export->setStyle($export->getCell(0, 0), $export->getCell(12, 0))->setFontBold();
        foreach ($TableContent as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Number']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthday']);
            $export->setValue($export->getCell($column++, $row), $PersonData['District']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Street']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Code']);
            $export->setValue($export->getCell($column++, $row), $PersonData['City']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Parents']);
            $export->setValue($export->getCell($column++, $row), $PersonData['ParentJob']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Phone']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneS1']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneS2']);
            $export->setValue($export->getCell($column, $row++), $PersonData['Photo']);
        }
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }
}