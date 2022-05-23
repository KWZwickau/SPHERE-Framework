<?php
namespace SPHERE\Application\Reporting\Standard\Person\Service;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Reporting\Standard\Person\Person;

class StudentAgreement
{
    /**
     * @param $tblPersonList
     *
     * @return array
     */
    public function createAgreementClassList($tblPersonList)
    {
        $TableContent = array();
        if ($tblPersonList) {

            //Agreement Head
            if(($tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll())){
                foreach($tblAgreementCategoryAll as $tblAgreementCategory){
                    $tblAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblAgreementCategory);
                    foreach($tblAgreementTypeList as $tblAgreementType){
                        $ColumnCustom['AgreementType'.$tblAgreementType->getId()] = $tblAgreementType->getName();
                    }
                }
            }

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['StudentNumber'] = '';
                $Item['Birthday'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                // Grundlegend setzen und befüllen
                if(($tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll())){
                    foreach($tblAgreementCategoryAll as $tblAgreementCategory){
                        $tblAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblAgreementCategory);
                        foreach($tblAgreementTypeList as $tblAgreementType){
                            $Item['AgreementType'][$tblAgreementType->getId()] = 'Nein';
                        }
                    }
                }

                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $tblBirhdates = $tblCommon->getTblCommonBirthDates();
                    if ($tblBirhdates) {
                        $Item['Birthday'] = $tblBirhdates->getBirthday();
                    }
                }

                if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                    $Item['StudentNumber'] = $tblStudent->getIdentifier();
                    // Bestätigung Setzen
                    if(($tblAgreementList = Student::useService()->getStudentAgreementAllByStudent($tblStudent))){
                        foreach($tblAgreementList as $tblAgreement){
                            if(($tblAgreementType = $tblAgreement->getTblStudentAgreementType())){
                                $Item['AgreementType'][$tblAgreementType->getId()] = 'Ja';
                            }
                        }
                    }
                }

                $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                if ($tblAddress) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
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
    public function createAgreementClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $Column = 0;
            $Row = 1;
            $export->setValue($export->getCell($Column++, $Row), "Schülernummer");
            $export->setValue($export->getCell($Column++, $Row), "Name, Vorname");
            $export->setValue($export->getCell($Column++, $Row), "Anschrift");
            $export->setValue($export->getCell($Column++, $Row), "Geburtsdatum");

            //Agreement Head
            if(($tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll())){
                foreach($tblAgreementCategoryAll as $tblAgreementCategory){
                    // Header für Ketegorie
                    $export->setValue($export->getCell($Column, $Row - 1), $tblAgreementCategory->getName());
                    if(($tblAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblAgreementCategory))){
                        // Header für Ketegorie (Breite)
                        $export->setStyle($export->getCell($Column, $Row - 1), $export->getCell($Column + (count($tblAgreementTypeList) - 1), $Row - 1))->mergeCells();
                        foreach($tblAgreementTypeList as $tblAgreementType){
                            $export->setValue($export->getCell($Column++, $Row), $tblAgreementType->getName());
                        }
                    }
                }
            }

            $Row = 2;

            foreach ($PersonList as $PersonData) {
                $Column = 0;
                $export->setValue($export->getCell($Column++, $Row), $PersonData['StudentNumber']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Name']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Address']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Birthday']);

                foreach($PersonData['AgreementType'] as $AgreementTypeContent){
                    $export->setValue($export->getCell($Column++, $Row), $AgreementTypeContent);
                }

                $Row++;
            }

            $Row++;
            Person::setGenderFooter($export, $tblPersonList, $Row);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }
}