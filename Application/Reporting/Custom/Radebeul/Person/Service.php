<?php
namespace SPHERE\Application\Reporting\Custom\Radebeul\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Corporation\Group\Group as GroupCompany;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Custom\Radebeul\Person
 */
class Service extends Extension
{

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createParentTeacherConferenceList(TblDivisionCourse $tblDivisionCourse)
    {

        $TableContent = array();
        if (($tblPersonList = $tblDivisionCourse->getStudents())) {
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count, $tblDivisionCourse) {
                $item['Number'] = $count++;
                $item['Division'] = $tblDivisionCourse->getDisplayName();
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['Attendance'] = '';
                array_push($TableContent, $item);
            });
        }
        return $TableContent;
    }

    /**
     * @param array $TableContent
     * @param array $tblPersonList
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     */
    public function createParentTeacherConferenceListExcel(array $TableContent, array $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $column = $row = 0;
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell("3", $row++), "EVANGELISCHE");
        $export->setValue($export->getCell("3", $row++), "GRUNDSCHULE");
        $export->setValue($export->getCell("3", $row++), "RADEBEUL");
        $export->setValue($export->getCell("0", $row++), "Anwesenheitsliste Elternabend");
        $export->setValue($export->getCell("0", $row++), "Datum:");
        $export->setValue($export->getCell("0", $row++), "Thema:");
        $row++;
        $export->setValue($export->getCell("0", $row++), "Klasse: " . $TableContent[0]['Division']);
        $row++;
        $headerRow = $row;
        $export->setValue($export->getCell($column++, $row), "lfdNr.");
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Vorname");
        $export->setValue($export->getCell($column, $row), "Unterschrift");
        // Gittertrennlinie / Zentriert
        $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setBorderBottom();
        $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row++))->setAlignmentCenter();
        foreach ($TableContent as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Number']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column, $row), $PersonData['Attendance']);
            // Gittertrennlinie / Zentriert
            $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setBorderBottom();
            $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row++))->setAlignmentCenter();
        }
        // Gitterlinien
        $export->setStyle($export->getCell(0, $headerRow), $export->getCell(3, 1))->setBorderBottom();
        $export->setStyle($export->getCell(0, $headerRow), $export->getCell(3, $row - 1))->setBorderVertical();
        $export->setStyle($export->getCell(0, $headerRow), $export->getCell(3, $row - 1))->setBorderOutline();
        // Spaltenbreite
        $column = 0;
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(8);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(26);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(26);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(30);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @return array
     */
    public function createDenominationList()
    {

        $TableContent = array();
        $countArray = array(
            'All'   => 0,
            'RK'    => 0,
            'EV'    => 0,
            'KEINE' => 0
        );
        if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT))
        && (!empty($tblPersonList = $tblGroup->getPersonList()))) {
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
            $count = 1;
            /** @var TblPerson $tblPerson */
            foreach($tblPersonList as $tblPerson) {
                $countArray['All'] = $count;
                $item['Number'] = $count++;
                $item['LastName'] = $tblPerson->getLastName();
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['Denomination'] = $tblPerson->getDenominationString();
                if(isset($countArray[strtoupper($item['Denomination'])])) {
                    $countArray[strtoupper($item['Denomination'])]++;
                } else {
                    $countArray['KEINE']++;
                }
                array_push($TableContent, $item);
            }
        }
        return array($TableContent, $countArray);
    }

    /**
     * @param array $TableContent
     * @param array $countArray
     *
     * @return FilePointer
     */
    public function createDenominationListExcel(array $TableContent, array $countArray)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = $row = 0;
        $export->setValue($export->getCell($column, $row), "Religionszugehörigkeit");
        $export->setStyle($export->getCell($column, $row), $export->getCell(3, $row++))->setFontBold()->setFontSize(14)->mergeCells();
        $export->setValue($export->getCell($column, $row), "Evangelisches Schulzentrum Radebeul Staatlich genehmigte Ersatzschule                       ". date('d.m.Y'));
        $export->setStyle($export->getCell($column, $row), $export->getCell(3, $row))->mergeCells();
        $export->setStyle($export->getCell($column, 0), $export->getCell(3, $row++))->setBorderOutline(2);
        foreach ($TableContent as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Number']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column, $row), $PersonData['Denomination']);
            // Gittertrennlinie
            $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row++))->setBorderBottom();
        }
        $row++;
        $export->setValue($export->getCell("0", $row),
             "   Schüler:    ".$countArray['All']
            ."             Evangelisch (EV):    ".$countArray['EV']
            ."             Katholisch (RK):    ".$countArray['RK']
            ."             ohne Angabe:    ".$countArray['KEINE']
        );
        $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setBorderAll();
        $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->setFontBold();
        $export->setStyle($export->getCell(0, $row), $export->getCell(3, $row))->mergeCells();
        // Spaltenbreite
        $column = 0;
        $export->setStyle($export->getCell($column++, $row))->setColumnWidth(8);
        $export->setStyle($export->getCell($column++, $row))->setColumnWidth(26);
        $export->setStyle($export->getCell($column++, $row))->setColumnWidth(26);
        $export->setStyle($export->getCell($column, $row))->setColumnWidth(30);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     *
     * @param TblGroup $tblGroup
     *
     * @return array
     */
    public function createPhoneList(TblGroup $tblGroup)
    {

        $TableContent = array();
        if(!empty($tblPersonList = $tblGroup->getPersonList())) {
            foreach ($tblPersonList as $tblPerson) {
                $item['Title'] = $tblPerson->getTitle();
                $item['LastName'] = $tblPerson->getLastName();
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['PhoneHome'] = $item['PhoneEmergency'] = '';
                $item['PhoneMobileS1'] = $item['PhoneMobileS2'] = '';
                $item['PhoneBusinessS1'] = $item['PhoneBusinessS2'] = '';
                $item['Division'] = '';
                if(($tblDivisionStudent = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))
                && ($tblDivisionCourse = $tblDivisionStudent->getTblDivision())){
                    $item['Division'] = $tblDivisionCourse->getDisplayName();
                }
                $phoneList = array();
                $phoneEmergencyList = array();
                if(($tblPersonToPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson))) {
                    foreach($tblPersonToPhoneList as $tblToPerson) {
                        if(($tblPhone = $tblToPerson->getTblPhone())
                        && ($tblType = $tblToPerson->getTblType())
                        && $tblType->getName() == 'Privat'
                        && $tblType->getDescription() == 'Festnetz'
                        ) {
                            $phoneList[] = $tblPhone->getNumber();
                        } elseif(($tblPhone = $tblToPerson->getTblPhone())
                        && ($tblType = $tblToPerson->getTblType())
                        && $tblType->getName() == 'Notfall') {
                            $phoneEmergencyList[] = $tblToPerson->getRemark().($tblToPerson->getRemark() ? ' ': '').$tblPhone->getNumber();
                        }
                    }
                }
                if(!empty($phoneList)) {
                    $item['PhoneHome'] = implode('; ', $phoneList);
                }
                if(!empty($phoneEmergencyList)) {
                    $item['PhoneEmergency'] = implode('; ', $phoneEmergencyList);
                }
                if (($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, TblType::IDENTIFIER_GUARDIAN))) {
                    foreach ($tblRelationshipList as $tblToPerson) {
                        if (($tblPersonGuard = $tblToPerson->getServiceTblPersonFrom())) {
                            $Ranking = $tblToPerson->getRanking();
                            $PhoneGuardList = array();
                            if(($tblToPersonGuardList = Phone::useService()->getPhoneAllByPerson($tblPersonGuard))){
                                foreach($tblToPersonGuardList as $tblToPersonGuard){
                                    if (($tblPhoneGuard = $tblToPersonGuard->getTblPhone())
                                        && ($tblTypeGuard = $tblToPersonGuard->getTblType())
                                        && $tblTypeGuard->getName() == 'Privat'
                                        && $tblTypeGuard->getDescription() == 'Mobil'
                                    ) {
                                        $PhoneGuardList[$Ranking]['Mobile'][] = $tblPhoneGuard->getNumber();
                                    }
                                    if (($tblPhoneGuard = $tblToPersonGuard->getTblPhone())
                                    && ($tblTypeGuard = $tblToPersonGuard->getTblType())
                                    && $tblTypeGuard->getName() == 'Geschäftlich') {
                                        $PhoneGuardList[$Ranking]['Business'][] = $tblPhoneGuard->getNumber();
                                    }
                                }
                            }
                            if (!empty($PhoneGuardList)) {
                                foreach($PhoneGuardList as $TempRanking => $TypePhoneList){
                                    if(!empty($TypePhoneList)){
                                        foreach($TypePhoneList as $Type => $PhoneList){
                                            if(!empty($PhoneList)){
                                                if($Type == 'Mobile'){
                                                    $item['PhoneMobileS'.$TempRanking] = implode('; ', $PhoneList);
                                                } elseif($Type == 'Business'){
                                                    $item['PhoneBusinessS'.$TempRanking] = implode('; ', $PhoneList);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                array_push($TableContent, $item);
            }
        }
        return $TableContent;
    }

    /**
     * @param array $TableContent
     *
     * @return bool|FilePointer
     */
    public function createPhoneListExcel(array $TableContent)
    {

        $division = array();
        $lastName = array();
        $firstName = array();
        foreach ($TableContent as $key => $row) {
            $division[$key] = strtoupper($row['Division']);
            $lastName[$key] = strtoupper($row['LastName']);
            $firstName[$key] = strtoupper($row['FirstName']);
        }
        array_multisort($division, SORT_ASC, $lastName, SORT_ASC, $firstName, SORT_ASC, $TableContent);
        $fileLocation = Storage::createFilePointer('xlsx');
        $column = $row = 0;
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell($column++, $row), 'Klasse');
        $export->setValue($export->getCell($column++, $row), 'Titel');
        $export->setValue($export->getCell($column++, $row), 'Nachname');
        $export->setValue($export->getCell($column++, $row), 'Vorname');
        $export->setValue($export->getCell($column++, $row), 'Zuhause');
        $export->setValue($export->getCell($column++, $row), 'S1 Handy');
        $export->setValue($export->getCell($column++, $row), 'S2 Handy');
        $export->setValue($export->getCell($column++, $row), 'S1 dienstl.');
        $export->setValue($export->getCell($column++, $row), 'S2 dienstl.');
        $export->setValue($export->getCell($column++, $row), 'Notfall');
        $export->setValue($export->getCell($column, $row), 'Geb.-Datum');
        // Style Head
        $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row++))->setBorderAll()->setFontSize(10)->setAlignmentCenter();
        foreach ($TableContent as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Division']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Title']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneHome']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneMobileS1']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneMobileS2']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneBusinessS1']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneBusinessS2']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneEmergency']);
            $export->setValue($export->getCell($column, $row++), $PersonData['Birthday']);
        }
        // ContentBorder & Style Table
        $export->setStyle($export->getCell(0, 1), $export->getCell($column, ($row - 1)))->setBorderAll()->setFontSize(10);
        // Spaltenbreite
        $column = 0;
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(7);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(4);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(11);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(11);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(13);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(13);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(13);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(13);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(13);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(13);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(10);
        $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     *
     * @param TblGroup $tblGroup
     *
     * @return array
     */
    public function createKindergartenList(TblGroup $tblGroup)
    {

        $TableContent = array();;
        if(!empty($tblPersonList = $tblGroup->getPersonList())) {
            $count = 1;
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {
                $item['Number'] = $count++;
                $item['LastName'] = $tblPerson->getLastName();
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['Kindergarten'] = '';
                if (($tblStudent = $tblPerson->getStudent())) {
                    if (($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE'))
                    && (($tblTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblTransferType)))) {
                        if(($tblCompany = $tblTransfer->getServiceTblCompany())
                        && ($tblNurseryGroup = GroupCompany::useService()->getGroupByMetaTable('NURSERY'))
                        && GroupCompany::useService()->existsGroupCompany($tblNurseryGroup, $tblCompany)) {
                            $item['Kindergarten'] = $tblCompany->getDisplayName();
                        } elseif(($remark = $tblTransfer->getRemark())  // Suchen der Kita in der Bemerkung
                        && ($pos = strpos($remark, 'Kita:')) !== false) {
                            $startPos = $pos + strlen('Kita:');
                            if(($pos2 = strpos($remark, 'Staatliche Schule:')) !== false
                            && $pos2 > $pos) {
                                $item['Kindergarten'] = trim(substr($remark, $startPos, $pos2 - $startPos));
                            } else {
                                $item['Kindergarten'] = trim(substr($remark, $startPos));
                            }
                        }
                    }
                }
                array_push($TableContent, $item);
            }
        }
        return $TableContent;
    }

    /**
     * @param TblGroup $tblGroup
     * @param $PersonList
     *
     * @return FilePointer
     */
    public function createKindergartenListExcel(TblGroup $tblGroup, $PersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $column = $row = 0;
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell($column, $row), 'Klassenliste - Kinderhaus');
        $export->setStyle($export->getCell($column, $row), $export->getCell(4, $row++))->mergeCells()->setFontSize(14)->setFontBold();
        $export->setValue($export->getCell($column, $row), 'Klasse: ' . $tblGroup->getName());
        $export->setStyle($export->getCell($column, $row), $export->getCell(4, $row++))->mergeCells()->setFontSize(14)->setFontBold();
        $export->setValue($export->getCell(4, $row), date('d.m.Y'));
        $export->setStyle($export->getCell($column, $row), $export->getCell(4, $row))->setAlignmentRight();
        $export->setStyle($export->getCell($column, 0), $export->getCell(4, $row++))->setBorderOutline();
        $row++;
        $export->setValue($export->getCell($column++, $row), 'lfdNr.');
        $export->setValue($export->getCell($column++, $row), 'Name');
        $export->setValue($export->getCell($column++, $row), 'Vorname');
        $export->setValue($export->getCell($column++, $row), 'Geburtstag');
        $export->setValue($export->getCell($column, $row), 'Kinderhaus');
        $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row++))->setBorderAll();
        foreach ($PersonList as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column, $row), $PersonData['Number']);
            $export->setStyle($export->getCell(0, $row), $export->getCell($column++, $row))->setAlignmentCenter();
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthday']);
            $export->setValue($export->getCell($column, $row++), $PersonData['Kindergarten']);
        }
        // ContentBorder
        $export->setStyle($export->getCell(0, 4), $export->getCell($column, ($row - 1)))->setBorderAll();
        $column = 0;
        // Spaltenbreite
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(6);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(25);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(25);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(15);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(50);
        $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     *
     * @param TblGroup $tblGroup
     *
     * @return array
     */
    public function createRegularSchoolList(TblGroup $tblGroup)
    {

        $TableContent = array();
        if (!empty($tblPersonList = $tblGroup->getPersonList())) {
            $count = 1;
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {
                $item['Number'] = $count++;
                $item['LastName'] = $tblPerson->getLastName();
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['RegularSchool'] = '';
                if(($tblStudent = $tblPerson->getStudent())) {
                    if(($tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE'))
                    && (($tblTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblTransferType)))) {
                        if(($remark = $tblTransfer->getRemark())
                        && ($pos = strpos($remark, 'Staatliche Schule:')) !== false) {
                            $startPos = $pos + strlen('Staatliche Schule:');
                            if(($pos2 = strpos($remark, 'Kita:')) !== false
                            && $pos2 > $pos) {
                                $item['RegularSchool'] = trim(substr($remark, $startPos, $pos2 - $startPos));
                            } else {
                                $item['RegularSchool'] = trim(substr($remark, $startPos));
                            }
                        }
                    }
                }
                array_push($TableContent, $item);
            }
        }
        return $TableContent;
    }

    /**
     * @param $PersonList
     *
     * @return FilePointer
     */
    public function createRegularSchoolListExcel($PersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $column = $row = 0;
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell($column, $row), 'Stammschulenübersicht');
        $export->setStyle($export->getCell($column, $row), $export->getCell(3, $row++))->mergeCells()->setFontSize(14)->setFontBold();
        $export->setValue($export->getCell($column, $row), 'Evangelisches Schulzentrum Radebeul Staatlich genehmigte Ersatzschule');
        $export->setStyle($export->getCell($column, $row), $export->getCell(3, $row++))->mergeCells();
        $export->setValue($export->getCell(3, $row), date('d.m.Y'));
        $export->setStyle($export->getCell(3, $row))->setAlignmentRight();
        $export->setStyle($export->getCell($column, 0), $export->getCell(3, $row++))->setBorderOutline();
        $row++;
        $export->setValue($export->getCell($column++, $row), 'Nr.');
        $export->setValue($export->getCell($column++, $row), 'Name');
        $export->setValue($export->getCell($column++, $row), 'Vorname');
        $export->setValue($export->getCell($column, $row), 'Stammschule');
        $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row++))->setBorderAll();
        foreach ($PersonList as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Number']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column, $row++), $PersonData['RegularSchool']);
        }
        // ContentBorder
        $export->setStyle($export->getCell(0, 4), $export->getCell($column, ($row - 1)))->setBorderAll();
        $column = 0;
        // Spaltenbreite
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(6);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(20);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(20);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(44);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return array
     */
    public function createDiseaseList(TblGroup $tblGroup)
    {

        $TableContent = array();
        if(!empty($tblPersonList = $tblGroup->getPersonList())) {
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
            /** @var TblPerson $tblPerson */
            foreach($tblPersonList as $tblPerson) {
                $item['Division'] = '';
                $item['LastName'] = $tblPerson->getLastName();
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['Disease'] = '';
                if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))
                    && ($tblDivisionCourse = $tblStudentEducation->getTblDivision())){
                    $item['Division'] = $tblDivisionCourse->getDisplayName();
                }
                if(($tblStudent = $tblPerson->getStudent())
                && ($tblMedicalRecord = $tblStudent->getTblStudentMedicalRecord())) {
                    $item['Disease'] = $tblMedicalRecord->getDisease();
                }
                array_push($TableContent, $item);
            }
        }
        return $TableContent;
    }

    /**
     * @param TblGroup $tblGroup
     * @param array $TableContent
     *
     * @return FilePointer
     */
    public function createDiseaseListExcel(TblGroup $tblGroup, array $TableContent)
    {

        $division = array();
        $lastName = array();
        $firstName = array();
        foreach ($TableContent as $key => $row) {
            $division[$key] = strtoupper($row['Division']);
            $lastName[$key] = strtoupper($row['LastName']);
            $firstName[$key] = strtoupper($row['FirstName']);
        }
        array_multisort($division, SORT_ASC, $lastName, SORT_ASC, $firstName, SORT_ASC, $TableContent);
        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $column = $row = 0;
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell($column, $row), 'Klassenliste - Allergie');
        $export->setStyle($export->getCell($column, $row), $export->getCell(3, $row++))->mergeCells()->setFontSize(14)->setFontBold();
        $export->setValue($export->getCell($column, $row), 'Klasse: ' . $tblGroup->getName());
        $export->setStyle($export->getCell($column, $row), $export->getCell(3, $row++))->mergeCells()->setFontSize(14)->setFontBold();
        $export->setValue($export->getCell(3, $row), date('d.m.Y'));
        $export->setStyle($export->getCell(3, $row))->setAlignmentRight();
        $export->setStyle($export->getCell($column, 0), $export->getCell(3, $row++))->setBorderOutline();
        $row++;
        // Head
        $export->setValue($export->getCell($column++, $row), 'Klasse');
        $export->setValue($export->getCell($column++, $row), 'Name');
        $export->setValue($export->getCell($column++, $row), 'Vorname');
        $export->setValue($export->getCell($column, $row), 'Allergie');
        $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row++))->setBorderAll();
        foreach ($TableContent as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Division']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column, $row++), $PersonData['Disease']);
        }
        // ContentBorder
        $export->setStyle($export->getCell(0, 4), $export->getCell($column, ($row - 1)))->setBorderAll();
        // Spaltenbreite
        $column = 0;
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(10);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(25);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(25);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(60);
        $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param TblGroup $tblGroup
     * @param string   $PLZ
     *
     * @return array
     */
    public function createNursery(TblGroup $tblGroup, string $PLZ = '')
    {

        $TableContent = array();
        if(!empty($tblPersonList = $tblGroup->getPersonList())) {
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
            $RowCount = 1;
            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {
                $item['Count'] = $item['CountExcel'] = $RowCount++;
                $item['LastName'] = $item['LastNameExcel'] = $tblPerson->getLastName();
                $item['FirstName'] = $item['FirstNameExcel'] = $tblPerson->getFirstSecondName();
                $item['Birthday'] = $item['BirthdayExcel'] = $tblPerson->getBirthday();
                $item['City'] = $item['CityExcel'] = '';
                $item['PLZ'] = $item['PLZExcel'] = '';
                $item['Street'] = $item['StreetExcel'] = '';
                if(($tblAddress = Address::useService()->getAddressByPerson($tblPerson))) {
                    $item['Street'] = $item['StreetExcel'] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                    if(($tblCity = $tblAddress->getTblCity())) {
                        $item['City'] = $item['CityExcel'] = $tblCity->getDisplayName();
                        $item['PLZ'] = $item['PLZExcel'] = $tblCity->getCode();
                    }
                }
                // mark the unmatched
                if($item['PLZ'] != $PLZ) {
                    $item['Count'] = new Bold($item['Count']);
                    $item['LastName'] = new Bold($item['LastName']);
                    $item['FirstName'] = new Bold($item['FirstName']);
                    $item['Birthday'] = new Bold($item['Birthday']);
                    $item['City'] = new Bold($item['City']);
                    $item['PLZ'] = new Bold($item['PLZ']);
                    $item['Street'] = new Bold($item['Street']);
                }
                array_push($TableContent, $item);
            }
        }
        return $TableContent;
    }

    /**
     * @param array  $TableContent
     * @param string $PLZ
     *
     * @return int
     */
    public function getPLZMarkedCount(array $TableContent = array(), string $PLZ = '')
    {

        $mismatchedPLZ = 0;
        foreach ($TableContent as $tblPersonContent) {
            if ($tblPersonContent['PLZ'] != $PLZ) {
                $mismatchedPLZ++;
            }
        }
        return $mismatchedPLZ;
    }

    /**
     * @param array  $TableContent
     * @param string $PLZ
     *
     * @return FilePointer
     */
    public function createNurseryExcel(array $TableContent, string $PLZ = '')
    {

        $countPerson = count($TableContent);
        $countMarked = $this->getPLZMarkedCount($TableContent, $PLZ);
        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $now = (new \DateTime());
        $nowString = $now->format('d.m.Y');
        // build
        $nowMonthInt = (int)$now->format('m');
        $nowYearInt = (int)$now->format('Y');
        if ($nowMonthInt == 12) {
            $nowYearInt++;
            $nowMonthInt = 0;
        }
        $nowMonthInt++;
        $nowMonthInt = str_pad($nowMonthInt, 2, '0', STR_PAD_LEFT);
        $toDateString = '01.'.$nowMonthInt.'.'.$nowYearInt;
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell(1, 0), 'Anzahl der aufgenommenen Kinder zum 1. eines Monats');
        $export->setStyle($export->getCell(1, 0), $export->getCell(4, 0))->mergeCells()->setFontBold()->setBorderAll();
        $export->setStyle($export->getCell(1, 1), $export->getCell(1, 1))->setBorderAll();
        $export->setStyle($export->getCell(2, 1), $export->getCell(4, 1))->mergeCells()->setBorderAll();
        $export->setValue($export->getCell(1, 2), 'Einrichtung:');
        $export->setStyle($export->getCell(1, 2), $export->getCell(1, 5))->setFontBold()->setAlignmentTop()->mergeCells()->setBorderAll();
        $export->setValue($export->getCell(2, 2), 'Hort des Ev. Schulzentrum Radebeul');
        $export->setValue($export->getCell(2, 3), 'Wilhelm-Eichler-Straße 13');
        $export->setValue($export->getCell(2, 4), '01445 Radebeul');
        $export->setStyle($export->getCell(2, 2), $export->getCell(4, 2))->mergeCells()->setBorderLeft()->setBorderTop()->setBorderRight();
        $export->setStyle($export->getCell(2, 3), $export->getCell(4, 3))->mergeCells()->setBorderLeft()->setBorderRight();
        $export->setStyle($export->getCell(2, 4), $export->getCell(4, 4))->mergeCells()->setBorderLeft()->setBorderRight();
        $export->setStyle($export->getCell(2, 5), $export->getCell(4, 5))->mergeCells()->setBorderLeft()->setBorderBottom()->setBorderRight();
        $export->setValue($export->getCell(1, 6), 'Stichtag:');
        $export->setStyle($export->getCell(1, 6), $export->getCell(1, 9))->setFontBold()->setAlignmentTop()->mergeCells()->setBorderAll();
        $export->setValue($export->getCell(2, 6), $toDateString);
        $export->setStyle($export->getCell(2, 6), $export->getCell(4, 9))->setAlignmentCenter()->setAlignmentMiddle()->mergeCells()->setBorderAll();
        $export->setValue($export->getCell(2, 10), 'gesamt');
        $export->setValue($export->getCell(3, 10), 'davon');
        $export->setValue($export->getCell(4, 10), 'tatsächl.');
        $export->setStyle($export->getCell(2, 10), $export->getCell(4, 10))->setFontBold();
        $export->setStyle($export->getCell(1, 10), $export->getCell(1, 13))->setBorderOutline();
        $export->setStyle($export->getCell(2, 10), $export->getCell(2, 13))->setBorderOutline();
        $export->setStyle($export->getCell(3, 10), $export->getCell(3, 13))->setBorderOutline();
        $export->setStyle($export->getCell(4, 10), $export->getCell(4, 13))->setBorderOutline();
        $export->setValue($export->getCell(2, 11), 'Hortkinder');
        $export->setValue($export->getCell(3, 11), 'ld- anderer');
        $export->setValue($export->getCell(4, 11), 'besch. päd.');
        $export->setValue($export->getCell(3, 12), 'Kommunen');
        $export->setValue($export->getCell(4, 12), 'Fachkräfte');
        $export->setValue($export->getCell(4, 13), 'in Vzä');
        $export->setValue($export->getCell(1, 15), '6 Stunden:');
        $export->setValue($export->getCell(2, 15), $countPerson);
        $export->setValue($export->getCell(3, 15), $countMarked);
        $export->setStyle($export->getCell(2, 15), $export->getCell(3, 15))->setAlignmentCenter();
        $export->setValue($export->getCell(1, 17), '5 Stunden:');
        $export->setValue($export->getCell(2, 19), $countPerson);
        $export->setValue($export->getCell(3, 19), $countMarked);
        $export->setStyle($export->getCell(2, 19), $export->getCell(3, 19))->setAlignmentCenter();
        $export->setValue($export->getCell(1, 21), 'freie Plätze:');
        $export->setValue($export->getCell(1, 23), 'vorraus. Fr. Plätze zum');
        $export->setValue($export->getCell(1, 24), 'Schuljahresanfang:');
        $export->setStyle($export->getCell(1, 23), $export->getCell(1, 24))->setBorderOutline();
        $export->setStyle($export->getCell(2, 23), $export->getCell(4, 24))->setBorderOutline()->setAlignmentCenter();
        $export->setStyle($export->getCell(3, 23), $export->getCell(3, 24))->setBorderLeft()->setBorderRight();
        $export->setStyle($export->getCell(1, 14), $export->getCell(4, 22))->setBorderAll();
        $export->setStyle($export->getCell(2, 14), $export->getCell(4, 22))->setAlignmentCenter();
        $export->setValue($export->getCell(1, 27), 'Datum');
        $export->setValue($export->getCell(2, 27), $nowString);
        $export->setValue($export->getCell(1, 29), 'Stepel / Unterschrift');
        $export->setValue($export->getCell(2, 33), 'Ev. GS Radebeul Daniel Röhrich');
        $export->setStyle($export->getCell(1, 25), $export->getCell(1, 33))->setBorderOutline();
        $export->setStyle($export->getCell(2, 25), $export->getCell(4, 33))->setBorderOutline();
        $export->setStyle($export->getCell(2, 33), $export->getCell(4, 33))->mergeCells();
        $column = 0;
        // Spaltenbreite
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(5);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(22);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(12);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(12);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(12);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param array  $PersonList
     * @param string $PLZ
     *
     * @return FilePointer
     */
    public function createNurseryListExcel($PersonList, $PLZ = '')
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = $row = 0;
        $export->setValue($export->getCell($column++, $row), '#');
        $export->setValue($export->getCell($column++, $row), 'Name');
        $export->setValue($export->getCell($column++, $row), 'Vorname');
        $export->setValue($export->getCell($column++, $row), 'Geb.-datum');
        $export->setValue($export->getCell($column++, $row), 'Wohnort');
        $export->setValue($export->getCell($column++, $row), 'PLZ');
        $export->setValue($export->getCell($column, $row), 'Straße');
        $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row++))->setBorderAll()->setFontBold();
        foreach ($PersonList as $PersonData) {
            if ($PersonData['PLZExcel'] != $PLZ) {
                // mark person with other PLZ
                $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setFontBold();
            }
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['CountExcel']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastNameExcel']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstNameExcel']);
            $export->setValue($export->getCell($column++, $row), $PersonData['BirthdayExcel']);
            $export->setValue($export->getCell($column++, $row), $PersonData['CityExcel']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PLZExcel']);
            $export->setValue($export->getCell($column, $row++), $PersonData['StreetExcel']);
        }
        // ContentBorder
        $export->setStyle($export->getCell(0, 1), $export->getCell($column, ($row - 1)))->setBorderAll();
        $column = 0;
        // Spaltenbreite
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(4);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(18);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(21);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(11);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(27);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(7);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row))->setColumnWidth(32);
        $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }
}