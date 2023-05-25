<?php
namespace SPHERE\Application\Reporting\Standard\Person;

use DateTime;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperSizeParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException;
use PHPExcel_Cell_DataType;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblType as TblTypeMail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use \SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType as TblSchoolType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Agreement\Agreement;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson as TblToPersonRelationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Common\Frontend\Link\Repository\Mailto;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Standard\Person
 */
class Service extends Extension
{
    /** Zählung zur Anzeigenbestimmung "Stammdatenabfrage"
     * @var array
     */
    private $MetaComparisonList = array();

    public function getMetaComparisonList()
    {
        return $this->MetaComparisonList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool      $IsExcel
     *
     * @return string
     */
    private function getPhoneList(TblPerson $tblPerson, $IsExcel = false)
    {

        $tblToPersonList = Phone::useService()->getPhoneAllByPerson($tblPerson);

        $phoneList = array();

        if ($tblToPersonList) {
            $privateList = array();
            $companyList = array();
            $secureList = array();
            $faxList = array();
            foreach ($tblToPersonList as $tblToPerson) {
                if($tblToPerson->getTblType()->getName() == 'Privat'){
                    $privateList[] = $tblToPerson->getTblPhone()->getNumber().($IsExcel ? ' ' : '&nbsp;').
                        Phone::useService()->getPhoneTypeShort($tblToPerson);
                }
                if($tblToPerson->getTblType()->getName() == 'Geschäftlich'){
                    $companyList[] = $tblToPerson->getTblPhone()->getNumber().($IsExcel ? ' ' : '&nbsp;').
                        Phone::useService()->getPhoneTypeShort($tblToPerson);
                }
                if($tblToPerson->getTblType()->getName() == 'Notfall'){
                    $secureList[] = $tblToPerson->getTblPhone()->getNumber().($IsExcel ? ' ' : '&nbsp;').
                        Phone::useService()->getPhoneTypeShort($tblToPerson);
                }
                if($tblToPerson->getTblType()->getName() == 'Fax'){
                    $faxList[] = $tblToPerson->getTblPhone()->getNumber().($IsExcel ? ' ' : '&nbsp;').
                        Phone::useService()->getPhoneTypeShort($tblToPerson);
                }
            }
            $phoneList = array_merge($privateList, $companyList, $secureList, $faxList);
        }
        if(!empty($phoneList)){
            return implode(', ', $phoneList);
        }
        return '';
    }

    /**
     * @param false|array $tblPersonList
     * @param TblYear $tblYear
     *
     * @return array
     */
    public function createClassList($tblPersonList, TblYear $tblYear): array
    {
        $TableContent = array();
        if ($tblPersonList) {
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count, $tblYear) {
                $item['Number'] = $count++;
                $this->setPersonData('', $item, $tblPerson);
                $item['Gender'] = $tblPerson->getGenderString();
                $item['Denomination'] = $tblPerson->getDenominationString();
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['Birthplace'] = $tblPerson->getBirthplaceString();
                $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = '';
                $item['Address'] = '';
                $item['ForeignLanguage1'] = $item['ForeignLanguage2'] = $item['ForeignLanguage3'] = '';
                $item['Profile'] = $item['Religion'] = $item['Orientation'] = $item['Elective'] = '';
                $item['ExcelElective'] = array();
                // Address
                $item = $this->getAddressDataFromPerson($tblPerson, $item);
                // Mail, Phone,
                $item = $this->getContactDataFromPerson($tblPerson, $item);

                $level = null;
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                    $level = $tblStudentEducation->getLevel();
                }

                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                // NK/Profil
                if ($tblStudent) {
                    for ($i = 1; $i <= 3; $i++) {
                        $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                        $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                        $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking(
                            $tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking);
                        if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                            $item['ForeignLanguage'. $i] = $tblSubject->getAcronym();
                            if (($levelFrom = $tblStudentSubject->getLevelFrom())
                                && $level
                                && $level < $levelFrom
                            ) {
                                $item['ForeignLanguage' . $i] = '';
                            }
                            if (($levelTill = $tblStudentSubject->getLevelTill())
                                && $level
                                && $level > $levelTill
                            ) {
                                $item['ForeignLanguage' . $i] = '';
                            }
                        }
                    }
                    // Profil
                    $tblStudentProfile = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE')
                    );
                    if ($tblStudentProfile && ($tblSubject = $tblStudentProfile[0]->getServiceTblSubject())) {
                        $item['Profile'] = $tblSubject->getAcronym();
                    }
                    // Neigungskurs
                    $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION')
                    );
                    if ($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                        $item['Orientation'] = $tblSubject->getAcronym();
                        $item['OrientationId'] = $tblSubject->getId();
                    }
                    // Religion
                    $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION')
                    );
                    if ($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                        $item['Religion'] = $tblSubject->getAcronym();
                        $item['ReligionId'] = $tblSubject->getId();
                    }
                    // Wahlfach
                    $tblStudentElectiveList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent,
                        Student::useService()->getStudentSubjectTypeByIdentifier('ELECTIVE')
                    );
                    $ElectiveList = array();
                    if ($tblStudentElectiveList) {
                        foreach ($tblStudentElectiveList as $tblStudentElective) {
                            if ($tblStudentElective->getServiceTblSubject()) {
                                $tblSubjectRanking = $tblStudentElective->getTblStudentSubjectRanking();
                                if ($tblSubjectRanking) {
                                    $ElectiveList[$tblStudentElective->getTblStudentSubjectRanking()->getIdentifier()] =
                                        $tblStudentElective->getServiceTblSubject()->getAcronym();
                                } else {
                                    $ElectiveList[] =
                                        $tblStudentElective->getServiceTblSubject()->getAcronym();
                                }
                            }
                        }
                        if (!empty($ElectiveList)) {
                            ksort($ElectiveList);
                        }
                        if (!empty($ElectiveList)) {
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
     * @param array $dataList
     * @param TblPerson[] $tblPersonList
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return FilePointer
     */
    public function createClassListExcel($dataList, $tblPersonList, TblDivisionCourse $tblDivisionCourse)
    {

        $isProfile = false;
        $isOrientation = false;
        $isElective = false;
        $LevelList = array();
        foreach($tblPersonList as $tblPerson){
            if($tblYear = $tblDivisionCourse->getServiceTblYear()){
                $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
                if(($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())){
                    $LevelList[$tblSchoolType->getName()][$tblStudentEducation->getLevel()] = $tblStudentEducation->getLevel();
                }
            }
        }
        // Profil
        if(isset($LevelList[TblSchoolType::IDENT_GYMNASIUM])
            && (in_array('8', $LevelList[TblSchoolType::IDENT_GYMNASIUM])
                || in_array('9', $LevelList[TblSchoolType::IDENT_GYMNASIUM])
                || in_array('10', $LevelList[TblSchoolType::IDENT_GYMNASIUM])
            )
        ){
            $isProfile = true;
        }
        // Wahlbereich
        if(isset($LevelList[TblSchoolType::IDENT_OBER_SCHULE])
            && (in_array('7', $LevelList[TblSchoolType::IDENT_OBER_SCHULE])
                || in_array('8', $LevelList[TblSchoolType::IDENT_OBER_SCHULE])
                || in_array('9', $LevelList[TblSchoolType::IDENT_OBER_SCHULE])
            )
        ){
            $isOrientation = true;
        }
        // Wahlfach
        if(isset($LevelList[TblSchoolType::IDENT_OBER_SCHULE])
            && in_array('10', $LevelList[TblSchoolType::IDENT_OBER_SCHULE])
        ){
            $isElective = true;
        }
        // create File
        $fileLocation = Storage::createFilePointer('xlsx');
        $row = 0;
        $Column = 0;
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell($Column++, $row), "lfd.Nr.");
        $export->setValue($export->getCell($Column++, $row), "Name");
        $export->setValue($export->getCell($Column++, $row), "Vorname");
        $export->setValue($export->getCell($Column++, $row), "Geschlecht");
        $export->setValue($export->getCell($Column++, $row), "Konfession");
        $export->setValue($export->getCell($Column++, $row), "Geburtsdatum");
        $export->setValue($export->getCell($Column++, $row), "Geburtsort");
        $export->setValue($export->getCell($Column++, $row), "Ortsteil");
        $export->setValue($export->getCell($Column++, $row), "Straße");
        $export->setValue($export->getCell($Column++, $row), "Hausnr.");
        $export->setValue($export->getCell($Column++, $row), "PLZ");
        $export->setValue($export->getCell($Column++, $row), "Ort");
        $export->setValue($export->getCell($Column++, $row), "Telefon");
        $export->setValue($export->getCell($Column++, $row), "E-Mail");
        $export->setValue($export->getCell($Column++, $row), "E-Mail Privat");
        $export->setValue($export->getCell($Column++, $row), "E-Mail Geschäftlich");
        $export->setValue($export->getCell($Column++, $row), "FS 1");
        $export->setValue($export->getCell($Column++, $row), "FS 2");
        $export->setValue($export->getCell($Column++, $row), "FS 3");
        $export->setValue($export->getCell($Column, $row), "Religion");
        if($isProfile){
            $export->setValue($export->getCell(++$Column, $row), "Profil");
        }
        if($isOrientation){
            $export->setValue($export->getCell(++$Column, $row), "Wahlbereich");
        }
        if($isElective){
            $export->setValue($export->getCell(++$Column, $row), "Wahlfächer");
        }
        $export->setStyle($export->getCell(0, $row), $export->getCell($Column, $row))
            // Header Fett
            ->setFontBold()
            // Strich nach dem Header
            ->setBorderBottom();
        foreach ($dataList as $PersonData) {
            $row++;
            $Column = 0;
            $phoneRow = $mailRow = $row;
            $export->setValue($export->getCell($Column++, $row), $PersonData['Number']);
            $export->setValue($export->getCell($Column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($Column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($Column++, $row), $PersonData['Gender']);
            $export->setValue($export->getCell($Column++, $row), $PersonData['Denomination']);
            $export->setValue($export->getCell($Column++, $row), $PersonData['Birthday']);
            $export->setValue($export->getCell($Column++, $row), $PersonData['Birthplace']);
            $export->setValue($export->getCell($Column++, $row), $PersonData['District']);
            $export->setValue($export->getCell($Column++, $row), $PersonData['StreetName']);
            $export->setValue($export->getCell($Column++, $row), $PersonData['StreetNumber']);
            $export->setValue($export->getCell($Column++, $row), $PersonData['Code']);
            $export->setValue($export->getCell($Column++, $row), $PersonData['City']);
            if (is_array($PersonData['ExcelPhone'])) {
                foreach ($PersonData['ExcelPhone'] as $Phone) {
                    $export->setValue($export->getCell($Column, $phoneRow++), $Phone);
                }
            }
            $Column++;
            if (is_array($PersonData['ExcelMail'])) {
                foreach ($PersonData['ExcelMail'] as $Mail) {
                    $export->setValue($export->getCell($Column, $mailRow++), $Mail);
                }
            }
            $Column++;
            $export->setValue($export->getCell($Column++, $row), $PersonData['ExcelMailPrivate']);
            $export->setValue($export->getCell($Column++, $row), $PersonData['ExcelMailBusiness']);
            $export->setValue($export->getCell($Column++, $row), $PersonData['ForeignLanguage1']);
            $export->setValue($export->getCell($Column++, $row), $PersonData['ForeignLanguage2']);
            $export->setValue($export->getCell($Column++, $row), $PersonData['ForeignLanguage3']);
            $export->setValue($export->getCell($Column, $row), $PersonData['Religion']);
            if($isProfile){
                $export->setValue($export->getCell(++$Column, $row), $PersonData['Profile']);
            }
            if($isOrientation){
                $export->setValue($export->getCell(++$Column, $row), $PersonData['Orientation']);
            }
            if($isElective){
                $export->setValue($export->getCell(++$Column, $row), (is_array($PersonData['ExcelElective'])
                    ? implode(', ', $PersonData['ExcelElective'])
                    : '') );
            }
            // get row to the same high as highest PhoneRow or MailRow
            if ($row < ($phoneRow - 1)) {
                $row = ($phoneRow - 1);
            }
            if ($row < ($mailRow - 1)) {
                $row = ($mailRow - 1);
            }
            // Strich nach jedem Schüler
            $export->setStyle($export->getCell(0, $row), $export->getCell($Column, $row))
                ->setBorderBottom();
        }
        // Spaltenbreite
        $column = 0;
        $columnWithList = array(7, 15, 17, 13, 15, 15, 15, 15, 25, 8, 7, 15, 25, 25, 25, 25, 6, 6, 6, 8, 7, 12, 12);
        foreach($columnWithList as $with){
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth($with);
        }
        $row++;$row++;
        $rowDescription = $row;
        if ($tblDivisionCourse) {
            $TypeName = 'Klasse';
            if(( $tblType = $tblDivisionCourse->getType())){
                $TypeName = $tblType->getName();
            }
            $export->setValue($export->getCell("0", $row), $TypeName.':');
            $export->setValue($export->getCell("2", $row), $tblDivisionCourse->getDisplayName());
        }
        $row++;
        Person::setGenderFooter($export, $tblPersonList, $row, 0, 2);
        $row++;
        if ($tblDivisionCourse) {
            $TeacherTypeName = $tblDivisionCourse->getDivisionTeacherName().':';
            $export->setValue($export->getCell("0", $row), $TeacherTypeName);
            $TeacherNameListString = DivisionCourse::useService()->getDivisionTeacherNameListString($tblDivisionCourse, ', ');
            $export->setValue($export->getCell("2", $row), $TeacherNameListString);

        }
        $row++;
        if ($tblDivisionCourse) {
            $export->setValue($export->getCell("0", $row), 'Klassensprecher:');
            if (($tblDivisionRepresentationList = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_REPRESENTATIVE))) {
                $Representation = array();
                foreach ($tblDivisionRepresentationList as $tblDivisionRepresentation) {
                    $tblRepresentation = $tblDivisionRepresentation->getServiceTblPerson();
                    $Description = $tblDivisionRepresentation->getDescription();
                    $Representation[] = $tblRepresentation->getFirstSecondName() . ' ' . $tblRepresentation->getLastName()
                        . ($Description ? ' (' . $Description . ')' : '');
                }
                $RepresentationString = implode(', ', $Representation);
                $export->setValue($export->getCell("2", $row), $RepresentationString);
            }
        }
        // Legende
        $row = $rowDescription;
        $export->setValue($export->getCell("11", $row++), 'Abkürzungen Telefon:');
        $export->setValue($export->getCell("11", $row++), 'p = Privat');
        $export->setValue($export->getCell("11", $row++), 'g = Geschäftlich');
        $export->setValue($export->getCell("11", $row++), 'n = Notfall');
        $export->setValue($export->getCell("11", $row), 'f = Fax');
        // Export File
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createExtendedClassList(TblDivisionCourse $tblDivisionCourse)
    {

        $tblPersonList = $tblDivisionCourse->getStudents();
        $TableContent = array();
        if (!empty($tblPersonList)) {
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {

                $item['Number'] = $count++;
                $this->setPersonData('', $item, $tblPerson);
                $item['Gender'] = $tblPerson->getGenderString();
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['Birthplace'] = $tblPerson->getBirthplaceString();
                $item['StudentNumber'] = '';
                $item['Guardian1'] = $item['PhoneGuardian1'] = $item['PhoneGuardian1Excel'] = '';
                $item['Guardian2'] = $item['PhoneGuardian2'] = $item['PhoneGuardian2Excel'] = '';
                $item['Guardian3'] = $item['PhoneGuardian3'] = $item['PhoneGuardian3Excel'] = '';
                $item['Authorized'] = $item['PhoneAuthorized'] = $item['PhoneAuthorizedExcel'] = '';
                $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = '';
                $item['Address'] = '';
                if (($tblStudent = $tblPerson->getStudent())) {
                    $item['StudentNumber'] = $tblStudent->getIdentifierComplete();
                }
                // Address
                $item = $this->getAddressDataFromPerson($tblPerson, $item);
                // Guardian 1, 2, 3
                $tblPersonG1 = false;
                $tblPersonG2 = false;
                $tblPersonG3 = false;
                // Authorized
                $tblPersonA = false;
                $tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($tblToPersonList) {
                    foreach ($tblToPersonList as $tblToPerson) {
                        if ($tblToPerson->getTblType()->getName() == 'Sorgeberechtigt' && $tblToPerson->getServiceTblPersonFrom()) {
                            switch ($tblToPerson->getRanking()) {
                                case 1: $tblPersonG1 = $tblToPerson->getServiceTblPersonFrom(); break;
                                case 2: $tblPersonG2 = $tblToPerson->getServiceTblPersonFrom(); break;
                                case 3: $tblPersonG3 = $tblToPerson->getServiceTblPersonFrom(); break;
                            }
                        } elseif($tblToPerson->getTblType()->getName() == 'Bevollmächtigt' && $tblToPerson->getServiceTblPersonFrom()){
                            $tblPersonA = $tblToPerson->getServiceTblPersonFrom();
                        }
                    }
                }
                if ($tblPersonG1) {
                    $item['Guardian1'] = $tblPersonG1->getFullName();
                    $item['PhoneGuardian1'] = $this->getPhoneList($tblPersonG1);
                    $item['PhoneGuardian1Excel'] = $this->getPhoneList($tblPersonG1, true);
                }
                if ($tblPersonG2) {
                    $item['Guardian2'] = $tblPersonG2->getFullName();
                    $item['PhoneGuardian2'] = $this->getPhoneList($tblPersonG2);
                    $item['PhoneGuardian2Excel'] = $this->getPhoneList($tblPersonG2, true);
                }
                if ($tblPersonG3) {
                    $item['Guardian3'] = $tblPersonG3->getFullName();
                    $item['PhoneGuardian3'] = $this->getPhoneList($tblPersonG3);
                    $item['PhoneGuardian3Excel'] = $this->getPhoneList($tblPersonG3, true);
                }
                if($tblPersonA){
                    $item['Authorized'] = $tblPersonA->getFullName();
                    $item['PhoneAuthorized'] = $this->getPhoneList($tblPersonA);
                    $item['PhoneAuthorizedExcel'] = $this->getPhoneList($tblPersonA, true);
                }

                if (($tblChild = $tblPerson->getChild())) {
                    $item['AuthorizedToCollect'] = $tblChild->getAuthorizedToCollect();
                } else {
                    $item['AuthorizedToCollect'] = '';
                }

                array_push($TableContent, $item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array             $dataList
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return FilePointer
     */
    public function createExtendedClassListExcel(array $dataList, TblDivisionCourse $tblDivisionCourse)
    {

        $IsGuardian3 = false;
        $IsAuthorized = false;
        $TempList = $dataList;
        foreach($TempList as $dataRow){
            if($dataRow['Authorized']){
                $IsAuthorized = true;
            }
            if($dataRow['Guardian3']){
                $IsGuardian3 = true;
            }
        }
        // create File
        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $column = 0;
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell($column++, "0"), "#");
        $export->setValue($export->getCell($column++, "0"), "Schülernummer");
        $export->setValue($export->getCell($column++, "0"), "Name");
        $export->setValue($export->getCell($column++, "0"), "Vorname");
        $export->setValue($export->getCell($column++, "0"), "Geschlecht");
        $export->setValue($export->getCell($column++, "0"), "Adresse");
        $export->setValue($export->getCell($column++, "0"), "Straße");
        $export->setValue($export->getCell($column++, "0"), "Str.Nr");
        $export->setValue($export->getCell($column++, "0"), "PLZ");
        $export->setValue($export->getCell($column++, "0"), "Ort");
        $export->setValue($export->getCell($column++, "0"), "Ortsteil");
        $export->setValue($export->getCell($column++, "0"), "Geburtsdatum");
        $export->setValue($export->getCell($column++, "0"), "Geburtsort");
        $export->setValue($export->getCell($column++, "0"), "Sorgeberechtigter 1");
        $export->setValue($export->getCell($column++, "0"), "Tel. Sorgeber. 1");
        $export->setValue($export->getCell($column++, "0"), "Sorgeberechtigter 2");
        $export->setValue($export->getCell($column++, "0"), "Tel. Sorgeber. 2");
        if($IsGuardian3) {
            $export->setValue($export->getCell($column++, "0"), "Sorgeberechtigter 3");
            $export->setValue($export->getCell($column++, "0"), "Tel. Sorgeber. 3");
        }
        if($IsAuthorized){
            $export->setValue($export->getCell($column++, "0"), "Bevollmächtigt");
            $export->setValue($export->getCell($column++, "0"), "Tel. Bevollmächtigt");
        }
        $export->setValue($export->getCell($column, "0"), "Abholberechtigte");
        $row = 1;
        foreach ($dataList as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Number']);
            $export->setValue($export->getCell($column++, $row), $PersonData['StudentNumber']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Gender']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Address']);
            $export->setValue($export->getCell($column++, $row), $PersonData['StreetName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['StreetNumber']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Code']);
            $export->setValue($export->getCell($column++, $row), $PersonData['City']);
            $export->setValue($export->getCell($column++, $row), $PersonData['District']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthday']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthplace']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Guardian1']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneGuardian1Excel']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Guardian2']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneGuardian2Excel']);
            if($IsGuardian3) {
                $export->setValue($export->getCell($column++, $row), $PersonData['Guardian3']);
                $export->setValue($export->getCell($column++, $row), $PersonData['PhoneGuardian3Excel']);
            }
            if($IsAuthorized){
                $export->setValue($export->getCell($column++, $row), $PersonData['Authorized']);
                $export->setValue($export->getCell($column++, $row), $PersonData['PhoneAuthorizedExcel']);
            }
            $export->setValue($export->getCell($column, $row), $PersonData['AuthorizedToCollect']);
            $row++;
        }
        $row++;$row++;
        $rowDescription = $row;
        if ($tblDivisionCourse) {
            $TypeName = 'Klasse';
            if(( $tblType = $tblDivisionCourse->getType())){
                $TypeName = $tblType->getName();
            }
            $export->setValue($export->getCell("0", $row), $TypeName.':');
            $export->setValue($export->getCell("2", $row), $tblDivisionCourse->getDisplayName());
        }
        $row++;
        if(($tblPersonList = $tblDivisionCourse->getStudents())){
            Person::setGenderFooter($export, $tblPersonList, $row, 0, 2);
        }
        $row++;
        if ($tblDivisionCourse) {
            $TeacherTypeName = $tblDivisionCourse->getDivisionTeacherName().':';
            $export->setValue($export->getCell("0", $row), $TeacherTypeName);
            $TeacherNameListString = DivisionCourse::useService()->getDivisionTeacherNameListString($tblDivisionCourse, ', ');
            $export->setValue($export->getCell("2", $row), $TeacherNameListString);
        }
        $row++;
        if ($tblDivisionCourse) {
            $export->setValue($export->getCell("0", $row), 'Klassensprecher:');
            if (($tblDivisionRepresentationList = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_REPRESENTATIVE))) {
                $Representation = array();
                foreach ($tblDivisionRepresentationList as $tblDivisionRepresentation) {
                    $tblRepresentation = $tblDivisionRepresentation->getServiceTblPerson();
                    $Description = $tblDivisionRepresentation->getDescription();
                    $Representation[] = $tblRepresentation->getFirstSecondName() . ' ' . $tblRepresentation->getLastName()
                        . ($Description ? ' (' . $Description . ')' : '');
                }
                $RepresentationString = implode(', ', $Representation);
                $export->setValue($export->getCell("2", $row), $RepresentationString);
            }
        }
        // Legende
        $row = $rowDescription;
        $export->setValue($export->getCell("11", $row++), 'Abkürzungen Telefon:');
        $export->setValue($export->getCell("11", $row++), 'p = Privat');
        $export->setValue($export->getCell("11", $row++), 'g = Geschäftlich');
        $export->setValue($export->getCell("11", $row++), 'n = Notfall');
        $export->setValue($export->getCell("11", $row), 'f = Fax');
        // Export File
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createElectiveClassList(TblDivisionCourse $tblDivisionCourse)
    {

        $tblPersonList = $tblDivisionCourse->getStudents();
        $tblYear = $tblDivisionCourse->getServiceTblYear();
        $TableContent = array();
        if (!empty($tblPersonList)) {
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $tblDivisionCourse, &$count, $tblYear) {
                $item['Number'] = $count++;
                $item['Name'] = $tblPerson->getLastFirstName();
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['Education'] = '';
                $item['ForeignLanguage1'] = $item['ForeignLanguage2'] = $item['ForeignLanguage3'] = '';
                $item['Profile'] = $item['Orientation'] = $item['Religion'] = $item['Elective'] = '';
                $item['ExcelElective'] = array();
                $item['Elective1'] = $item['Elective2'] = $item['Elective3'] = $item['Elective4'] = $item['Elective5'] = '';

                $level = null;
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                    $level = $tblStudentEducation->getLevel();
                }

                // NK/Profil
                if (($tblStudent = $tblPerson->getStudent())) {
                    for ($i = 1; $i <= 3; $i++) {
                        $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                        $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                        $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking(
                            $tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking);
                        if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                            $item['ForeignLanguage' . $i] = $tblSubject->getAcronym();
                            if (($levelFrom = $tblStudentSubject->getLevelFrom())
                                && $level
                                && $level < $levelFrom
                            ) {
                                $item['ForeignLanguage' . $i] = '';
                            }
                            if (($levelTill = $tblStudentSubject->getLevelTill())
                                && $level
                                && $level > $levelTill
                            ) {
                                $item['ForeignLanguage' . $i] = '';
                            }
                        }
                    }
                    // Profil
                    $tblStudentProfile = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'));
                    if ($tblStudentProfile && ($tblSubject = $tblStudentProfile[0]->getServiceTblSubject())) {
                        $item['Profile'] = $tblSubject->getAcronym();
                    }
                    // Neigungskurs
                    $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'));
                    if ($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                        $item['Orientation'] = $tblSubject->getAcronym();
                    }
                    // Religion
                    $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'));
                    if ($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                        $item['Religion'] = $tblSubject->getAcronym();
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
                    $tblStudentSubjectElective = Student::useService()->getStudentSubjectTypeByIdentifier('ELECTIVE');
                    $ElectiveList = array();
                    if (($tblStudentElectiveList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectElective))) {
                        foreach ($tblStudentElectiveList as $tblStudentElective) {
                            if ($tblStudentElective->getServiceTblSubject()
                                && ($tblSubjectRanking = $tblStudentElective->getTblStudentSubjectRanking())) {
                                $Ranking = $tblSubjectRanking->getIdentifier();
                                $ElectiveList[$Ranking] = $tblStudentElective->getServiceTblSubject()->getAcronym();
                                $item['Elective'.$Ranking] = $tblStudentElective->getServiceTblSubject()->getAcronym();
                            }
                        }
                        if (!empty($ElectiveList)) {
                            ksort($ElectiveList);
                        }
                        if (!empty($ElectiveList)) {
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
     * @param array $PersonList
     * @param array $tblPersonList
     * @param       $DivisionId
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createElectiveClassListExcel(array $dataList, TblDivisionCourse $tblDivisionCourse)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $teacherList = array();
        if (($tblPersonTeacherList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))) {
            foreach ($tblPersonTeacherList as $tblPerson) {
                $teacherList[] = trim($tblPerson->getSalutation() . ' ' . $tblPerson->getLastName());
            }
        }
        $export->setValue($export->getCell(0, 0), "Klasse ".$tblDivisionCourse->getDisplayName().(empty($teacherList) ? '' : ' '.implode(', ', $teacherList)));
        $export->setStyle($export->getCell(0, 0), $export->getCell(7, 0))->setFontBold();
        $column = 0;
        $row = 1;
        // Header
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Geb.-Datum");
        $export->setValue($export->getCell($column++, $row), "Bg");
        $export->setValue($export->getCell($column++, $row), "FS 1");
        $export->setValue($export->getCell($column++, $row), "FS 2");
        $export->setValue($export->getCell($column++, $row), "FS 3");
        $export->setValue($export->getCell($column++, $row), "Profil");
        $export->setValue($export->getCell($column++, $row), "Neig.k.");
        $export->setValue($export->getCell($column++, $row), "Rel.");
        $export->setValue($export->getCell($column++, $row), "WF 1-5");
        $export->setValue($export->getCell($column++, $row), "WF 1");
        $export->setValue($export->getCell($column++, $row), "WF 2");
        $export->setValue($export->getCell($column++, $row), "WF 3");
        $export->setValue($export->getCell($column++, $row), "WF 4");
        $export->setValue($export->getCell($column, $row), "WF 5");
        // Header bold
        $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setFontBold();
        foreach ($dataList as $PersonData) {
            $column = 0;
            $row++;
            $export->setValue($export->getCell($column++, $row), $PersonData['Name']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthday']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Education']);
            $export->setValue($export->getCell($column++, $row), $PersonData['ForeignLanguage1']);
            $export->setValue($export->getCell($column++, $row), $PersonData['ForeignLanguage2']);
            $export->setValue($export->getCell($column++, $row), $PersonData['ForeignLanguage3']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Profile']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Orientation']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Religion']);
            if(!empty($PersonData['ExcelElective'])){
                $export->setValue($export->getCell($column++, $row), implode(', ', $PersonData['ExcelElective']));
            }
            $export->setValue($export->getCell($column++, $row), $PersonData['Elective1']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Elective2']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Elective3']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Elective4']);
            $export->setValue($export->getCell($column, $row), $PersonData['Elective5']);
        }
        $export->setStyle($export->getCell(0, 1), $export->getCell(14, $row - 1))->setBorderAll();
        // Personenanzahl
        $row++; $row++;
        if(($tblPersonList = $tblDivisionCourse->getStudents())){
            Person::setGenderFooter($export, $tblPersonList, $row);
        }
        // Stand
        $row += 2;
        $export->setValue($export->getCell(0, $row), 'Stand: ' . (new DateTime())->format('d.m.Y'));
        // Spaltenbreite
        $column = 0;
        $columnWithList = array(22, 12, 5, 6, 6, 6, 6, 8, 6, 14, 6, 6, 6, 6, 6);
        foreach($columnWithList as $with){
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth($with);
        }
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

        return $fileLocation;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createBirthdayClassList(TblDivisionCourse $tblDivisionCourse)
    {

        $tblPersonList = $tblDivisionCourse->getStudents();
        $TableContent = array();
        $All = 0;
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$All) {
                $item['Name'] = $tblPerson->getLastFirstName();
                $item['Gender'] = $tblPerson->getGenderString();
                $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = '';
                $item['Address'] = '';
                $item['Birth'] = $tblPerson->getBirthday();
                $item['BirthDay'] = $tblPerson->getBirthday('d');
                $item['BirthMonth'] = $tblPerson->getBirthday('m');
                $item['BirthYear'] = $tblPerson->getBirthday('Y');
                $item['Birthplace'] = $tblPerson->getBirthplaceString();
                $item['Age'] = '';
                // Altersberechnung
                if($item['Birth']){
                    $birthDate = new DateTime($item['Birth']);
                    $now = new DateTime();
                    if ($birthDate->format('Y.m') != $now->format('Y.m')) {
                        if (($birthDate->format('m.d')) <= ($now->format('m.d'))) {
                            $item['Age'] = $now->format('Y') - $birthDate->format('Y');
                        } else {
                            $item['Age'] = ($now->format('Y') - 1) - $birthDate->format('Y');
                        }
                    }
                }
                // Address
                $item = $this->getAddressDataFromPerson($tblPerson, $item);
                array_push($TableContent, $item);
            });
        }
        // multisort content
        if (!empty($TableContent)) {
            $day = array();
            $month = array();
            $year = array();
            foreach ($TableContent as $key => $row) {
                $month[$key] = substr($row['Birth'], 3, 2);
                $day[$key] = substr($row['Birth'], 0, 2);
                $year[$key] = substr($row['Birth'], 6, 4);
            }
            array_multisort($month, SORT_ASC, $day, SORT_ASC, $year, SORT_DESC, $TableContent);
            array_walk($TableContent, function (&$dataRow) use (&$All) {
                $All++;
                $dataRow['Number'] = $All;
            });
        }
        return $TableContent;
    }

    /**
     * @param array $dataList
     * @param array $tblPersonList
     *
     * @return bool|FilePointer
     */
    public function createBirthdayClassListExcel(array $dataList, array $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $column = 0;
        $row = 0;
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell($column++, $row), "lfd. Nr.");
        $export->setValue($export->getCell($column++, $row), "Name, Vorname");
        $export->setValue($export->getCell($column++, $row), "Anschrift");
        $export->setValue($export->getCell($column++, $row), "Geburtsort");
        $export->setValue($export->getCell($column++, $row), "Geburtsdatum");
        $export->setValue($export->getCell($column++, $row), "Geburtstag");
        $export->setValue($export->getCell($column++, $row), "Geburtsmonat");
        $export->setValue($export->getCell($column++, $row), "Geburtsjahr");
        $export->setValue($export->getCell($column, $row), "Alter");
        foreach ($dataList as $PersonData) {
            $column = 0;
            $row++;
            $export->setValue($export->getCell($column++, $row), $PersonData['Number']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Name']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Address']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthplace']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birth']);
            $export->setValue($export->getCell($column++, $row), $PersonData['BirthDay']);
            $export->setValue($export->getCell($column++, $row), $PersonData['BirthMonth']);
            $export->setValue($export->getCell($column++, $row), $PersonData['BirthYear']);
            $export->setValue($export->getCell($column, $row), $PersonData['Age']);

        }
        $row++; $row++;
        Person::setGenderFooter($export, $tblPersonList, $row);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createMedicalInsuranceClassList(TblDivisionCourse $tblDivisionCourse)
    {

        $tblPersonList = $tblDivisionCourse->getStudents();
        $TableContent = array();
        if (!empty($tblPersonList)) {
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {
                $item['Number'] = $count++;
                $item['Name'] = $tblPerson->getLastFirstName();
                $item['MedicalInsurance'] = '';
                $item['StudentNumber'] = '';
                $item['Gender'] = $tblPerson->getGenderString();
                $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = '';
                $item['Address'] = '';
                $item['Birthday'] = $tblPerson->getBirthday().'<br/>'.$tblPerson->getBirthplaceString();
                $item['BirthdayExcel'] = array($tblPerson->getBirthday(), $tblPerson->getBirthplaceString());
                $item['GuardianExcel'] = array();
                $item['PhoneNumberExcel'] = array();
                $item['PhoneGuardianNumberExcel'] = array();
                if (($tblStudent = $tblPerson->getStudent())) {
                    if (($tblStudentMedicalRecord =  $tblStudent->getTblStudentMedicalRecord())) {
                        $item['MedicalInsurance'] = $tblStudentMedicalRecord->getInsurance();
                    }
                    $item['StudentNumber'] = $tblStudent->getIdentifierComplete();
                }
                // Address
                $item = $this->getAddressDataFromPerson($tblPerson, $item);
                $S1 = $S2 = null;
                $tblRelationshipType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN);
                if (($guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType))) {
                    foreach($guardianList as $tblToPerson){
                        $tblPersonGuardian = $tblToPerson->getServiceTblPersonFrom();
                        switch ($tblToPerson->getRanking()) {
                            case 1: $S1 = $tblPersonGuardian; break;
                            case 2: $S2 = $tblPersonGuardian; break;
                        }
                    }
                }
                $phoneListGuardian = array();
                $NameS1 = $NameS2 = '';
                if ($S1) {
                    if ($PhoneS1List = Phone::useService()->getPhoneAllByPerson($S1)) {
                        foreach ($PhoneS1List as $PhoneS1) {
                            $phoneListGuardian[] = $PhoneS1->getTblPhone()->getNumber();
                        }
                    }
                    $NameS1 = $S1->getFullName();
                    $item['GuardianExcel'][] = $NameS1;
                }
                if ($S2) {
                    if ($PhoneS2List = Phone::useService()->getPhoneAllByPerson($S2)) {
                        foreach ($PhoneS2List as $PhoneS2) {
                            $phoneListGuardian[] = $PhoneS2->getTblPhone()->getNumber();
                        }
                    }
                    $NameS2 = $S2->getFullName();
                    $item['GuardianExcel'][] = $NameS2;
                }
                $item['Guardian'] = $NameS1.'<br/>'.$NameS2;
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                $phoneArray = array();
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        $phoneArray[] = $phone->getTblPhone()->getNumber();
                    }
                }
                $phoneString = '';
                if (!empty($phoneArray)) {
                    $phoneString = implode('<br/>', $phoneArray);
                    $item['PhoneNumberExcel'] = $phoneArray;
                }
                $item['PhoneNumber'] = $phoneString;
                $phoneGuardianString = '';
                if(!empty($phoneListGuardian)){
                    $phoneListGuardian = array_unique($phoneListGuardian);
                    $phoneGuardianString = implode('<br/>', $phoneListGuardian);
                    $item['PhoneGuardianNumberExcel'] = $phoneListGuardian;
                }
                $item['PhoneGuardianNumber'] = $phoneGuardianString;
                array_push($TableContent, $item);
            });
        }
        return $TableContent;
    }

    /**
     * @param $PersonList
     * @param $tblPersonList
     *
     * @return FilePointer
     */
    public function createMedicalInsuranceClassListExcel($dataList, $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = 0;
        $row = 0;
        $export->setValue($export->getCell($column++, $row), "Lfd. Nr.");
        $export->setValue($export->getCell($column++, $row), "Schülernummer");
        $export->setValue($export->getCell($column++, $row), "Name, Vorname");
        $export->setValue($export->getCell($column++, $row), "Anschrift");
        $export->setValue($export->getCell($column, $row), "Geburtsdatum");
        $export->setValue($export->getCell($column++, $row + 1), "Geburtsort");
        $export->setValue($export->getCell($column++, $row), "Krankenkasse");
        $export->setValue($export->getCell($column, $row), "1. Sorgeberechtigter");
        $export->setValue($export->getCell($column++, $row + 1), "2. Sorgeberechtigter");
        $export->setValue($export->getCell($column, $row), "Telefon");
        $export->setValue($export->getCell($column++, $row + 1), "Schüler");
        $export->setValue($export->getCell($column, $row), "Telefon");
        $export->setValue($export->getCell($column, $row + 1), "Sorgeberechtigte");
        $export->setStyle($export->getCell(0, 1), $export->getCell(8, 1))->setBorderBottom();
        $row = 2;
        foreach ($dataList as $PersonData) {
            $BirthCount = $GuardianCount = $PhoneCount = $PhoneGuardianCount = $row;
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Number']);
            $export->setValue($export->getCell($column++, $row), $PersonData['StudentNumber']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Name']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Address']);
            if(!empty($PersonData['BirthdayExcel'])){
                foreach($PersonData['BirthdayExcel'] as $BirthValue){
                    $export->setValue($export->getCell($column, $BirthCount++), $BirthValue);
                }
            }
            $column++;
            $export->setValue($export->getCell($column++, $row), $PersonData['MedicalInsurance']);
            if(!empty($PersonData['GuardianExcel'])){
                foreach($PersonData['GuardianExcel'] as $GuardianName){
                    $export->setValue($export->getCell($column, $GuardianCount++), $GuardianName);
                }
            }
            $column++;
            if(!empty($PersonData['PhoneNumberExcel'])){
                foreach($PersonData['PhoneNumberExcel'] as $PhoneNumber){
                    $export->setValue($export->getCell($column, $PhoneCount++), $PhoneNumber);
                }
            }
            $column++;
            if(!empty($PersonData['PhoneGuardianNumberExcel'])){
                foreach($PersonData['PhoneGuardianNumberExcel'] as $PhoneGuardianNumber){
                    $export->setValue($export->getCell($column, $PhoneGuardianCount++), $PhoneGuardianNumber);
                }
            }
            $row = max($row, $BirthCount, $GuardianCount, $PhoneCount, $PhoneGuardianCount);
            $export->setStyle($export->getCell(0, $row), $export->getCell(8, $row))->setBorderTop();
        }
        $row++;
        Person::setGenderFooter($export, $tblPersonList, $row);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param boolean $hasGuardian
     * @param boolean $hasAuthorizedPerson
     *
     * @return array
     */
    public function createInterestedPersonList(&$hasGuardian, &$hasAuthorizedPerson)
    {

        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('PROSPECT'));
        $TableContent = array();
        if (!empty($tblPersonList)) {
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy(TblPerson::ATTR_LAST_NAME, new StringGermanOrderSorter());
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$hasGuardian, &$hasAuthorizedPerson) {

                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = $item['Address'] = '';
                $item['Phone'] = $item['PhoneSimple'] = $item['PhoneFixedPrivate'] = $item['PhoneFixedWork'] = '';
                $item['PhoneFixedEmergency'] = $item['PhoneMobilePrivate'] = $item['PhoneMobileWork'] = $item['PhoneMobileEmergency'] = '';
                $item['Mail'] = $item['MailPrivate'] = $item['MailWork'] = '';
                $item['PhoneGuardian'] = array();
                $item['PhoneGuardianString'] = $item['PhoneGuardianSimple'] = '';
                $item['TypeOptionA'] = $item['TypeOptionB'] = $item['Level'] = $item['RegistrationDate'] = $item['InterviewDate'] = '';
                $item['TrialDate'] = $item['SchoolYear'] = '';
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['Birthplace'] = $tblPerson->getBirthplaceString();
                $item['Denomination'] = $tblPerson->getDenominationString();
                $item['Nationality'] = $tblPerson->getNationalityString();
                $item['Siblings'] = array();
                $item['Custody1Salutation'] = $item['Custody1Title'] = $item['Custody1LastName'] = $item['Custody1FirstName'] = $item['Custody1'] = '';
                $item['Custody1PhoneFixedPrivate'] = $item['Custody1PhoneFixedWork'] = $item['Custody1PhoneFixedEmergency'] = '';
                $item['Custody1PhoneMobilePrivate'] = $item['Custody1PhoneMobileWork'] = $item['Custody1PhoneMobileEmergency'] = '';
                $item['Custody1MailPrivate'] = $item['Custody1MailWork'] = '';
                $item['Custody2Salutation'] = $item['Custody2Title'] = $item['Custody2LastName'] = $item['Custody2FirstName'] = $item['Custody2'] = '';
                $item['Custody2PhoneFixedPrivate'] = $item['Custody2PhoneFixedWork'] = $item['Custody2PhoneFixedEmergency'] = '';
                $item['Custody2PhoneMobilePrivate'] = $item['Custody2PhoneMobileWork'] = $item['Custody2PhoneMobileEmergency'] = '';
                $item['Custody2MailPrivate'] = $item['Custody2MailWork'] = '';
                $item['Custody3Salutation'] = $item['Custody3Title'] = $item['Custody3LastName'] = $item['Custody3FirstName'] = $item['Custody3'] = '';
                $item['Custody3PhoneFixedPrivate'] = $item['Custody3PhoneFixedWork'] = $item['Custody3PhoneFixedEmergency'] = '';
                $item['Custody3PhoneMobilePrivate'] = $item['Custody3PhoneMobileWork'] = $item['Custody3PhoneMobileEmergency'] = '';
                $item['Custody3MailPrivate'] = $item['Custody3MailWork'] = '';
                $item['GuardianSalutation'] = $item['GuardianTitle'] = $item['GuardianLastName'] = $item['GuardianFirstName'] = $item['Guardian'] = '';
                $item['GuardianPhoneFixedPrivate'] = $item['GuardianPhoneFixedWork'] = $item['GuardianPhoneFixedEmergency'] = '';
                $item['GuardianPhoneMobilePrivate'] = $item['GuardianPhoneMobileWork'] = $item['GuardianPhoneMobileEmergency'] = '';
                $item['GuardianMailPrivate'] = $item['GuardianMailWork'] = '';
                $item['AuthorizedPersonSalutation'] = $item['AuthorizedPersonTitle'] = $item['AuthorizedPersonLastName'] = $item['AuthorizedPersonFirstName'] = $item['AuthorizedPerson'] = '';
                $item['AuthorizedPersonPhoneFixedPrivate'] = $item['AuthorizedPersonPhoneFixedWork'] = $item['AuthorizedPersonPhoneFixedEmergency'] = '';
                $item['AuthorizedPersonPhoneMobilePrivate'] = $item['AuthorizedPersonPhoneMobileWork'] = $item['AuthorizedPersonPhoneMobileEmergency'] = '';
                $item['AuthorizedPersonMailPrivate'] = $item['AuthorizedPersonMailWork'] = '';
                $item['Remark'] = $item['RemarkExcel'] = '';
                $item['MailGuardian'] = $item['ExcelMailGuardian'] = $item['ExcelMailGuardianSimple'] = '';
                // Transfer Arrive
                $item['TransferCompany'] = $item['TransferStateCompany'] = $item['TransferType'] = $item['TransferCourse'] = $item['TransferDate'] = $item['TransferRemark'] = '';
                // Address
                $item = $this->getAddressDataFromPerson($tblPerson, $item);
                if (($tblProspect = Prospect::useService()->getProspectByPerson($tblPerson))) {
                    if (($tblProspectReservation = $tblProspect->getTblProspectReservation())) {
                        $item['SchoolYear'] = $tblProspectReservation->getReservationYear();
                        if ($tblProspectReservation->getServiceTblTypeOptionA()) {
                            $item['TypeOptionA'] = $tblProspectReservation->getServiceTblTypeOptionA()->getName();
                        }
                        if ($tblProspectReservation->getServiceTblTypeOptionB()) {
                            $item['TypeOptionB'] = $tblProspectReservation->getServiceTblTypeOptionB()->getName();
                        }
                        if ($tblProspectReservation->getReservationDivision()) {
                            $item['Level'] = $tblProspectReservation->getReservationDivision();
                        }
                    }
                    if (($tblProspectAppointment = $tblProspect->getTblProspectAppointment())) {
                        $item['RegistrationDate'] = $tblProspectAppointment->getReservationDate();
                        $item['InterviewDate'] = $tblProspectAppointment->getInterviewDate();
                        $item['TrialDate'] = $tblProspectAppointment->getTrialDate();
                    }
                    $item['Remark'] = nl2br($tblProspect->getRemark());
                    $item['RemarkExcel'] = $tblProspect->getRemark();
                }
                $relationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if (!empty($relationshipList)) {
                    /** @var TblToPersonRelationship $relationship */
                    foreach ($relationshipList as $relationship) {
                        if ($relationship->getServiceTblPersonFrom() && $relationship->getServiceTblPersonTo()
                            && $relationship->getTblType()->getName() == 'Geschwisterkind'
                        ) {
                            if ($relationship->getServiceTblPersonFrom()->getId() == $tblPerson->getId()) {
                                $item['Siblings'][] = $relationship->getServiceTblPersonTo()->getFullName();
                            } else {
                                $item['Siblings'][] = $relationship->getServiceTblPersonFrom()->getFullName();
                            }
                        }
                    }
                    if (!empty($item['Siblings'])) {
                        $item['Siblings'] = implode(', ', $item['Siblings']);
                    }
                }
                if (empty($item['Siblings'])) {
                    $item['Siblings'] = '';
                }
                $PhoneListSimple = array();
                // get PhoneNumber by Prospect
                $tblToPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($tblToPhoneList) {
                    foreach ($tblToPhoneList as $tblToPhone) {
                        if (($tblPhone = $tblToPhone->getTblPhone())) {
                            $PhoneListSimple[$tblPhone->getId()] = $tblPhone->getNumber();
                            if ($item['Phone'] == '') {
                                $item['Phone'] = $tblPerson->getFirstName() . ' ' . $tblPerson->getLastName() . ' (' . $tblPhone->getNumber() . ' ' .
                                    // modify TypeShort
                                    str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                            } else {
                                $item['Phone'] = $item['Phone'].', ' . $tblPhone->getNumber() . ' ' .
                                    // modify TypeShort
                                    str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                            }
                        }
                    }
                    if ($item['Phone'] != '') {
                        $item['Phone'] = $item['Phone'].')';
                    }
                }
                // get Mail by Prospect
                $tblToMailList = Mail::useService()->getMailAllByPerson($tblPerson);
                if ($tblToMailList) {
                    foreach ($tblToMailList as $tblToMail) {
                        if (($tblMail = $tblToMail->getTblMail())) {
                            if ($item['Mail'] == '') {
                                $item['Mail'] = $item['Mail'].$tblPerson->getFirstName() . ' ' . $tblPerson->getLastName() . ' (' . $tblMail->getAddress() . ' ' .
                                    // modify TypeShort
                                    str_replace('.', '', Mail::useService()->getMailTypeShort($tblToMail));
                            } else {
                                $item['Mail'] = $item['Mail'].', ' . $tblMail->getAddress() . ' ' .
                                    // modify TypeShort
                                    str_replace('.', '', Mail::useService()->getMailTypeShort($tblToMail));
                            }
                        }
                    }
                    if ($item['Mail'] != '') {
                        $item['Mail'] = $item['Mail'].')';
                    }
                }
                if (!empty($PhoneListSimple)) {
                    $item['PhoneSimple'] = implode('; ', $PhoneListSimple);
                }
                $PhoneGuardianListSimple = array();
                $MailListSimple = array();
                $tblMailList = array();
                $this->setPhoneNumbersExtended('', $item, $tblPerson);
                $this->setMailsExtended('', $item, $tblPerson);
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $tblToPerson) {
                        if (($tblPersonGuardian = $tblToPerson->getServiceTblPersonFrom())
                            && ($tblType = $tblToPerson->getTblType())
                            && ($tblType->getName() == 'Sorgeberechtigt' || $tblType->getName() == 'Vormund' || $tblType->getName() == 'Bevollmächtigt')
                        ) {
                            // get PhoneNumber by Guardian
                            $this->setPhoneNumbers($tblPersonGuardian, $item, $PhoneGuardianListSimple);
                            //Mail Guardian
                            $this->setMails($tblPersonGuardian, $tblMailList, $MailListSimple);

                            if ($tblType->getName() == 'Sorgeberechtigt' && ($ranking = $tblToPerson->getRanking())) {
                                $this->setPersonData('Custody'.$ranking, $item, $tblPersonGuardian);
                                $this->setPhoneNumbersExtended('Custody'.$ranking, $item, $tblPersonGuardian);
                                $this->setMailsExtended('Custody'.$ranking, $item, $tblPersonGuardian);
                            } elseif ($tblType->getName() == 'Vormund') {
                                $hasGuardian = true;
                                $this->setPersonData('Guardian', $item, $tblPersonGuardian);
                                $this->setPhoneNumbersExtended('Guardian', $item, $tblPersonGuardian);
                                $this->setMailsExtended('Guardian', $item, $tblPersonGuardian);
                            } elseif ($tblType->getName() == 'Bevollmächtigt') {
                                $hasAuthorizedPerson = true;
                                $this->setPersonData('AuthorizedPerson', $item, $tblPersonGuardian);
                                $this->setPhoneNumbersExtended('AuthorizedPerson', $item, $tblPersonGuardian);
                                $this->setMailsExtended('AuthorizedPerson', $item, $tblPersonGuardian);
                            }
                        }
                    }
                }
                if (is_array($item['PhoneGuardian']) && !empty($item['PhoneGuardian'])) {
                    $item['PhoneGuardianString'] = implode('; ', $item['PhoneGuardian']);
                }
                if (!empty($PhoneGuardianListSimple)) {
                    $item['PhoneGuardianSimple'] = implode('; ', $PhoneGuardianListSimple);
                }
                // Insert MailList
                if (!empty($tblMailList)) {
                    $item['MailGuardian'] = $item['MailGuardian'].implode('<br>', $tblMailList);
                    $item['ExcelMailGuardian'] = implode('; ', $tblMailList);
                }
                // Insert MailListSimple
                if (!empty($MailListSimple)) {
                    $item['ExcelMailGuardianSimple'] = implode('; ', $MailListSimple);
                }
                // Transfer Arrive
                if(($tblStudent = $tblPerson->getStudent())){
                    $TransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');
                    if(($tblStudentTransferByTypeArrive = Student::useService()->getStudentTransferByType($tblStudent, $TransferTypeArrive))){
                        if(($tblCompanyTransfer = $tblStudentTransferByTypeArrive->getServiceTblCompany())){
                            $item['TransferCompany'] = $tblCompanyTransfer->getDisplayName();
                        }
                        if(($tblStateCompanyTransfer = $tblStudentTransferByTypeArrive->getServiceTblStateCompany())){
                            $item['TransferStateCompany'] = $tblStateCompanyTransfer->getDisplayName();
                        }
                        if(($SchoolType = $tblStudentTransferByTypeArrive->getServiceTblType())){
                            $item['TransferType'] = $SchoolType->getName();
                        }
                        if(($SchoolCourse = $tblStudentTransferByTypeArrive->getServiceTblCourse())){
                            $item['TransferCourse'] = $SchoolCourse->getName();
                        }
                        $item['TransferDate'] = $tblStudentTransferByTypeArrive->getTransferDate();
                        $item['TransferRemark'] = $tblStudentTransferByTypeArrive->getRemark();
                    }
                }
                array_push($TableContent, $item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array       $dataList
     * @param TblPerson[] $tblPersonList
     * @param bool        $hasGuardian
     * @param bool        $hasAuthorizedPerson
     *
     * @return bool|FilePointer
     */
    public function createInterestedPersonListExcel(array $dataList, array $tblPersonList, bool $hasGuardian, bool $hasAuthorizedPerson)
    {

        $column = 0;
        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell($column++, 0), "Anmeldedatum");
        $export->setValue($export->getCell($column++, 0), "Aufnahmegespräch");
        $export->setValue($export->getCell($column++, 0), "Schnuppertag");
        $export->setValue($export->getCell($column++, 0), "Vorname");
        $export->setValue($export->getCell($column++, 0), "Name");
        $export->setValue($export->getCell($column++, 0), "Schuljahr");
        $export->setValue($export->getCell($column++, 0), "Klassenstufe");
        $export->setValue($export->getCell($column++, 0), "Schulart 1");
        $export->setValue($export->getCell($column++, 0), "Schulart 2");
        $export->setValue($export->getCell($column++, 0), "Abgebende Schule / Kita");
        $export->setValue($export->getCell($column++, 0), "Staatliche Stammschule");
        $export->setValue($export->getCell($column++, 0), "Letzte Schulart");
        $export->setValue($export->getCell($column++, 0), "Letzter Bildungsgang");
        $export->setValue($export->getCell($column++, 0), "Aufnahme Datum");
        $export->setValue($export->getCell($column++, 0), "Aufnahme Bemerkung");
        $export->setValue($export->getCell($column++, 0), "Straße");
        $export->setValue($export->getCell($column++, 0), "Hausnummer");
        $export->setValue($export->getCell($column++, 0), "PLZ");
        $export->setValue($export->getCell($column++, 0), "Ort");
        $export->setValue($export->getCell($column++, 0), "Ortsteil");
        $export->setValue($export->getCell($column++, 0), "Geburtsdatum");
        $export->setValue($export->getCell($column++, 0), "Geburtsort");
        $export->setValue($export->getCell($column++, 0), "Staatsangeh.");
        $export->setValue($export->getCell($column++, 0), "Bekenntnis");
        $export->setValue($export->getCell($column++, 0), "Geschwister");
        $export->setValue($export->getCell($column++, 0), "Anrede Sorgeberechtigter 1");
        $export->setValue($export->getCell($column++, 0), "Titel Sorgeberechtigter 1");
        $export->setValue($export->getCell($column++, 0), "Name Sorgeberechtigter 1");
        $export->setValue($export->getCell($column++, 0), "Vorname Sorgeberechtigter 1");
        $export->setValue($export->getCell($column++, 0), "Anrede Sorgeberechtigter 2");
        $export->setValue($export->getCell($column++, 0), "Titel Sorgeberechtigter 2");
        $export->setValue($export->getCell($column++, 0), "Name Sorgeberechtigter 2");
        $export->setValue($export->getCell($column++, 0), "Vorname Sorgeberechtigter 2");
        $export->setValue($export->getCell($column++, 0), "Anrede Sorgeberechtigter 3");
        $export->setValue($export->getCell($column++, 0), "Titel Sorgeberechtigter 3");
        $export->setValue($export->getCell($column++, 0), "Name Sorgeberechtigter 3");
        $export->setValue($export->getCell($column++, 0), "Vorname Sorgeberechtigter 3");
        if ($hasGuardian) {
            $export->setValue($export->getCell($column++, 0), "Anrede Vormund");
            $export->setValue($export->getCell($column++, 0), "Titel Vormund");
            $export->setValue($export->getCell($column++, 0), "Name Vormund");
            $export->setValue($export->getCell($column++, 0), "Vorname Vormund");
        }
        if ($hasAuthorizedPerson) {
            $export->setValue($export->getCell($column++, 0), "Anrede Bevollmächtigter");
            $export->setValue($export->getCell($column++, 0), "Titel Bevollmächtigter");
            $export->setValue($export->getCell($column++, 0), "Name Bevollmächtigter");
            $export->setValue($export->getCell($column++, 0), "Vorname Bevollmächtigter");
        }
        $export->setValue($export->getCell($column++, 0), "Telefon Interessent");
        $export->setValue($export->getCell($column++, 0), "Telefon Interessent Kurz");
        $export->setValue($export->getCell($column++, 0), "E-Mail Interessent");
        $export->setValue($export->getCell($column++, 0), "E-Mail Interessent Privat");
        $export->setValue($export->getCell($column++, 0), "E-Mail Interessent Geschäftlich");
        $export->setValue($export->getCell($column++, 0), "Telefon Privat Mobil");
        $export->setValue($export->getCell($column++, 0), "Telefon Privat Festnetz");
        $export->setValue($export->getCell($column++, 0), "Telefon Geschäftlich Mobil");
        $export->setValue($export->getCell($column++, 0), "Telefon Geschäftlich Festnetz");
        $export->setValue($export->getCell($column++, 0), "Telefon Notfall Mobil");
        $export->setValue($export->getCell($column++, 0), "Telefon Notfall Festnetz");
        $export->setValue($export->getCell($column++, 0), "Telefon Sorgeberechtigte");
        $export->setValue($export->getCell($column++, 0), "Telefon Sorgeberechtigte Kurz");
        $export->setValue($export->getCell($column++, 0), "Telefon S1 Privat Mobil");
        $export->setValue($export->getCell($column++, 0), "Telefon S1 Privat Festnetz");
        $export->setValue($export->getCell($column++, 0), "Telefon S1 Geschäftlich Mobil");
        $export->setValue($export->getCell($column++, 0), "Telefon S1 Geschäftlich Festnetz");
        $export->setValue($export->getCell($column++, 0), "Telefon S1 Notfall Mobil");
        $export->setValue($export->getCell($column++, 0), "Telefon S1 Notfall Festnetz");
        $export->setValue($export->getCell($column++, 0), "Telefon S2 Privat Mobil");
        $export->setValue($export->getCell($column++, 0), "Telefon S2 Privat Festnetz");
        $export->setValue($export->getCell($column++, 0), "Telefon S2 Geschäftlich Mobil");
        $export->setValue($export->getCell($column++, 0), "Telefon S2 Geschäftlich Festnetz");
        $export->setValue($export->getCell($column++, 0), "Telefon S2 Notfall Mobil");
        $export->setValue($export->getCell($column++, 0), "Telefon S2 Notfall Festnetz");
        $export->setValue($export->getCell($column++, 0), "Telefon S3 Privat Mobil");
        $export->setValue($export->getCell($column++, 0), "Telefon S3 Privat Festnetz");
        $export->setValue($export->getCell($column++, 0), "Telefon S3 Geschäftlich Mobil");
        $export->setValue($export->getCell($column++, 0), "Telefon S3 Geschäftlich Festnetz");
        $export->setValue($export->getCell($column++, 0), "Telefon S3 Notfall Mobil");
        $export->setValue($export->getCell($column++, 0), "Telefon S3 Notfall Festnetz");
        if ($hasGuardian){
            $export->setValue($export->getCell($column++, 0), "Telefon Vormund Privat Mobil");
            $export->setValue($export->getCell($column++, 0), "Telefon Vormund Privat Festnetz");
            $export->setValue($export->getCell($column++, 0), "Telefon Vormund Geschäftlich Mobil");
            $export->setValue($export->getCell($column++, 0), "Telefon Vormund Geschäftlich Festnetz");
            $export->setValue($export->getCell($column++, 0), "Telefon Vormund Notfall Mobil");
            $export->setValue($export->getCell($column++, 0), "Telefon Vormund Notfall Festnetz");
        }
        if ($hasAuthorizedPerson){
            $export->setValue($export->getCell($column++, 0), "Telefon Bevollmächtigt Privat Mobil");
            $export->setValue($export->getCell($column++, 0), "Telefon Bevollmächtigt Privat Festnetz");
            $export->setValue($export->getCell($column++, 0), "Telefon Bevollmächtigt Geschäftlich Mobil");
            $export->setValue($export->getCell($column++, 0), "Telefon Bevollmächtigt Geschäftlich Festnetz");
            $export->setValue($export->getCell($column++, 0), "Telefon Bevollmächtigt Notfall Mobil");
            $export->setValue($export->getCell($column++, 0), "Telefon Bevollmächtigt Notfall Festnetz");
        }
        $export->setValue($export->getCell($column++, 0), "E-Mail Sorgeberechtigte");
        $export->setValue($export->getCell($column++, 0), "E-Mail Sorgeberechtigte Kurz");
        $export->setValue($export->getCell($column++, 0), "E-Mail S1 Privat");
        $export->setValue($export->getCell($column++, 0), "E-Mail S1 Geschäftlich");
        $export->setValue($export->getCell($column++, 0), "E-Mail S2 Privat");
        $export->setValue($export->getCell($column++, 0), "E-Mail S2 Geschäftlich");
        $export->setValue($export->getCell($column++, 0), "E-Mail S3 Privat");
        $export->setValue($export->getCell($column++, 0), "E-Mail S3 Geschäftlich");
        if ($hasGuardian){
            $export->setValue($export->getCell($column++, 0), "E-Mail Vormund Privat");
            $export->setValue($export->getCell($column++, 0), "E-Mail Vormund Geschäftlich");
        }
        if ($hasAuthorizedPerson){
            $export->setValue($export->getCell($column++, 0), "E-Mail Bevollmächtigter Privat");
            $export->setValue($export->getCell($column++, 0), "E-Mail Bevollmächtigter Geschäftlich");
        }
        $export->setValue($export->getCell($column, 0), "Bemerkung");
        $row = 1;
        foreach ($dataList as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['RegistrationDate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['InterviewDate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['TrialDate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['SchoolYear']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Level']);
            $export->setValue($export->getCell($column++, $row), $PersonData['TypeOptionA']);
            $export->setValue($export->getCell($column++, $row), $PersonData['TypeOptionB']);
            $export->setValue($export->getCell($column++, $row), $PersonData['TransferCompany']);
            $export->setValue($export->getCell($column++, $row), $PersonData['TransferStateCompany']);
            $export->setValue($export->getCell($column++, $row), $PersonData['TransferType']);
            $export->setValue($export->getCell($column++, $row), $PersonData['TransferCourse']);
            $export->setValue($export->getCell($column++, $row), $PersonData['TransferDate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['TransferRemark']);
            $export->setValue($export->getCell($column++, $row), $PersonData['StreetName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['StreetNumber']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Code']);
            $export->setValue($export->getCell($column++, $row), $PersonData['City']);
            $export->setValue($export->getCell($column++, $row), $PersonData['District']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthday']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthplace']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Nationality']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Denomination']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Siblings']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody1Salutation']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody1Title']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody1LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody1FirstName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody2Salutation']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody2Title']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody2LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody2FirstName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody3Salutation']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody3Title']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody3LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody3FirstName']);
            if ($hasGuardian) {
                $export->setValue($export->getCell($column++, $row), $PersonData['GuardianSalutation']);
                $export->setValue($export->getCell($column++, $row), $PersonData['GuardianTitle']);
                $export->setValue($export->getCell($column++, $row), $PersonData['GuardianLastName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['GuardianFirstName']);
            }
            if ($hasAuthorizedPerson) {
                $export->setValue($export->getCell($column++, $row), $PersonData['AuthorizedPersonSalutation']);
                $export->setValue($export->getCell($column++, $row), $PersonData['AuthorizedPersonTitle']);
                $export->setValue($export->getCell($column++, $row), $PersonData['AuthorizedPersonLastName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['AuthorizedPersonFirstName']);
            }
            $export->setValue($export->getCell($column++, $row), $PersonData['Phone']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneSimple']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Mail']);
            $export->setValue($export->getCell($column++, $row), $PersonData['MailPrivate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['MailWork']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneMobilePrivate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneFixedPrivate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneMobileWork']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneFixedWork']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneMobileEmergency']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneFixedEmergency']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneGuardianString']);
            $export->setValue($export->getCell($column++, $row), $PersonData['PhoneGuardianSimple']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody1PhoneMobilePrivate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody1PhoneFixedPrivate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody1PhoneMobileWork']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody1PhoneFixedWork']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody1PhoneMobileEmergency']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody1PhoneFixedEmergency']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody2PhoneMobilePrivate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody2PhoneFixedPrivate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody2PhoneMobileWork']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody2PhoneFixedWork']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody2PhoneMobileEmergency']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody2PhoneFixedEmergency']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody3PhoneMobilePrivate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody3PhoneFixedPrivate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody3PhoneMobileWork']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody3PhoneFixedWork']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody3PhoneMobileEmergency']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody3PhoneFixedEmergency']);
            if ($hasGuardian){
                $export->setValue($export->getCell($column++, $row), $PersonData['GuardianPhoneMobilePrivate']);
                $export->setValue($export->getCell($column++, $row), $PersonData['GuardianPhoneFixedPrivate']);
                $export->setValue($export->getCell($column++, $row), $PersonData['GuardianPhoneMobileWork']);
                $export->setValue($export->getCell($column++, $row), $PersonData['GuardianPhoneFixedWork']);
                $export->setValue($export->getCell($column++, $row), $PersonData['GuardianPhoneMobileEmergency']);
                $export->setValue($export->getCell($column++, $row), $PersonData['GuardianPhoneFixedEmergency']);
            }
            if ($hasAuthorizedPerson){
                $export->setValue($export->getCell($column++, $row), $PersonData['AuthorizedPersonPhoneMobilePrivate']);
                $export->setValue($export->getCell($column++, $row), $PersonData['AuthorizedPersonPhoneFixedPrivate']);
                $export->setValue($export->getCell($column++, $row), $PersonData['AuthorizedPersonPhoneMobileWork']);
                $export->setValue($export->getCell($column++, $row), $PersonData['AuthorizedPersonPhoneFixedWork']);
                $export->setValue($export->getCell($column++, $row), $PersonData['AuthorizedPersonPhoneMobileEmergency']);
                $export->setValue($export->getCell($column++, $row), $PersonData['AuthorizedPersonPhoneFixedEmergency']);
            }
            $export->setValue($export->getCell($column++, $row), $PersonData['ExcelMailGuardian']);
            $export->setValue($export->getCell($column++, $row), $PersonData['ExcelMailGuardianSimple']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody1MailPrivate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody1MailWork']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody2MailPrivate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody2MailWork']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody3MailPrivate']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody3MailWork']);
            if ($hasGuardian){
                $export->setValue($export->getCell($column++, $row), $PersonData['GuardianMailPrivate']);
                $export->setValue($export->getCell($column++, $row), $PersonData['GuardianMailWork']);
            }
            if ($hasAuthorizedPerson){
                $export->setValue($export->getCell($column++, $row), $PersonData['AuthorizedPersonMailPrivate']);
                $export->setValue($export->getCell($column++, $row), $PersonData['AuthorizedPersonMailWork']);
            }
            $export->setValue($export->getCell($column, $row), $PersonData['RemarkExcel']);
            // WrapText
            $export->setStyle($export->getCell($column, $row))->setWrapText();
            $row++;
        }
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(50);
        $row++;
        Person::setGenderFooter($export, $tblPersonList, $row);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return array
     */
    public function createGroupList(TblGroup $tblGroup)
    {

        $TableContent = array();
        if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy(TblPerson::ATTR_LAST_NAME, new StringGermanOrderSorter());
            $All = 0;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$All, $tblGroup) {
                $All++;
                $item['Title'] = $tblPerson->getTitle();
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['Number'] = $All;
                $item['Salutation'] = $tblPerson->getSalutation();
                $item['Gender'] = $tblPerson->getGenderString();
                $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = '';
                $item['Address'] = '';
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['BirthdaySort'] = $item['BirthdayYearSort'] = '';
                $item['PhoneNumber'] = $item['MobilPhoneNumber'] = '';
                $item['Mail'] = '';
                $item['BirthPlace'] = $tblPerson->getBirthplaceString();
                $item['Nationality'] = $tblPerson->getNationalityString();
                $item['Religion'] = $tblPerson->getDenominationString();
                $item['ParticipationWillingness'] = $item['ParticipationActivities'] = '';
                $item['RemarkFrontend'] = $item['RemarkExcel'] = '';
                if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                    $item['RemarkExcel'] = $tblCommon->getRemark();
                    $item['RemarkFrontend'] = nl2br($tblCommon->getRemark());
                    if (($tblBirthDates = $tblCommon->getTblCommonBirthDates())) {
                        if ($item['Birthday'] != '') {
                            $Year = substr($item['Birthday'], 6, 4);
                            $Month = substr($item['Birthday'], 3, 2);
                            $Day = substr($item['Birthday'], 0, 2);
                            if (is_numeric($Month) && is_numeric($Day)) {
                                $item['BirthdaySort'] = $Month * 100 + $Day;
                            }
                            if (is_numeric($Year) && is_numeric($Month) && is_numeric($Day)) {
                                $item['BirthdayYearSort'] = ($Year * 10000) + ($Month * 100) + $Day;
                            }
                        }
                    }
                    if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                        $item['ParticipationActivities'] = $tblCommonInformation->getAssistanceActivity();
                        if ($tblCommonInformation->isAssistance()) {
                            $item['ParticipationWillingness'] = 'ja';
                        } else {
                            $item['ParticipationWillingness'] = 'nein';
                        }
                    }
                }
                // Address
                $item = $this->getAddressDataFromPerson($tblPerson, $item);
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                $phoneArray = array();
                $mobilePhoneArray = array();
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        if ($phone->getTblType()->getDescription() === 'Festnetz') {
                            $phoneArray[] = $phone->getTblPhone()->getNumber();
                        }
                        if ($phone->getTblType()->getDescription() === 'Mobil') {
                            $mobilePhoneArray[] = $phone->getTblPhone()->getNumber();
                        }
                    }
                }
                if (count($phoneArray) >= 1) {
                    $item['PhoneNumber'] = implode(', ', $phoneArray);
                }
                if (count($mobilePhoneArray) >= 1) {
                    $item['MobilPhoneNumber'] = implode(', ', $mobilePhoneArray);
                }
                $mailAddressList = Mail::useService()->getMailAllByPerson($tblPerson);
                $mailList = array();
                if ($mailAddressList) {
                    foreach ($mailAddressList as $mailAddress) {
                        $mailList[] = $mailAddress->getTblMail()->getAddress();
                    }
                }
                if (count($mailList) >= 1) {
                    $item['Mail'] = $mailList[0];
                }
                if ($tblGroup->getMetaTable() == 'PROSPECT') {
                    $item['ReservationDate'] = '';
                    $item['InterviewDate'] = '';
                    $item['TrialDate'] = '';
                    $item['ReservationYear'] = '';
                    $item['ReservationDivision'] = '';
                    $item['SchoolTypeA'] = '';
                    $item['SchoolTypeB'] = '';
                    if (($tblProspect = Prospect::useService()->getProspectByPerson($tblPerson))) {
                        if (($tblProspectAppointment = $tblProspect->getTblProspectAppointment())) {
                            $item['ReservationDate'] = $tblProspectAppointment->getReservationDate();
                            $item['InterviewDate'] = $tblProspectAppointment->getInterviewDate();
                            $item['TrialDate'] = $tblProspectAppointment->getTrialDate();
                        }
                        if (($tblProspectReservation = $tblProspect->getTblProspectReservation())) {
                            $item['ReservationYear'] = $tblProspectReservation->getReservationYear();
                            $item['ReservationDivision'] = $tblProspectReservation->getReservationDivision();
                            $item['SchoolTypeA'] = ($tblProspectReservation->getServiceTblTypeOptionA() ? $tblProspectReservation->getServiceTblTypeOptionA()->getName() : '');
                            $item['SchoolTypeB'] = ($tblProspectReservation->getServiceTblTypeOptionB() ? $tblProspectReservation->getServiceTblTypeOptionB()->getName() : '');
                        }
                    }
                }
                if ($tblGroup->getMetaTable() == 'STUDENT') {
                    $item['Identifier'] = '';
                    $item['School'] = '';
                    $item['SchoolCourse'] = '';
                    $item['SchoolType'] = '';
                    $item['PictureSchoolWriting'] = '';
                    $item['PicturePublication'] = '';
                    $item['PictureWeb'] = '';
                    $item['PictureFacebook'] = '';
                    $item['PicturePrint'] = '';
                    $item['PictureFilm'] = '';
                    $item['PictureAdd'] = '';
                    $item['NameSchoolWriting'] = '';
                    $item['NamePublication'] = '';
                    $item['NameWeb'] = '';
                    $item['NameFacebook'] = '';
                    $item['NamePrint'] = '';
                    $item['NameFilm'] = '';
                    $item['NameAdd'] = '';
                    if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))) {
                        if(($tblDivisionCourseClass = $tblStudentEducation->getTblDivision())){
                            $item['Division'] = $tblDivisionCourseClass->getDisplayName();
                        }
                        if(($tblDivisionCourseCoreGroup = $tblStudentEducation->getTblCoreGroup())){
                            $item['Division'] .= ($item['Division'] ? ', ' : '').$tblDivisionCourseCoreGroup->getDisplayName();
                        }
                        if (($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())) {
                            $item['SchoolType'] = $tblSchoolType->getName();
                        }
                        if (($tblCourseStudent = $tblStudentEducation->getServiceTblCourse())) {
                            $item['SchoolCourse'] = $tblCourseStudent->getName();
                        }
                        if(($tblCompany = $tblStudentEducation->getServiceTblCompany())){
                            $item['School'] = $tblCompany->getDisplayName();
                        }
                    }
                    if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                        $item['Identifier'] = $tblStudent->getIdentifierComplete();
                        // leer befüllen
                        if(($tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll())){
                            foreach($tblAgreementCategoryAll as $tblAgreementCategory){
                                $tblAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblAgreementCategory);
                                foreach($tblAgreementTypeList as $tblAgreementType){
                                    $item['AgreementType'.$tblAgreementType->getId()] = '';
                                }
                            }
                        }
                        // befüllen was gesetzt ist
                        if(($tblAgreementList = Student::useService()->getStudentAgreementAllByStudent($tblStudent))){
                            foreach($tblAgreementList as $tblAgreement){
                                if(($tblAgreementType = $tblAgreement->getTblStudentAgreementType())){
                                    $item['AgreementType'.$tblAgreementType->getId()] = 'Ja';
                                }
                            }
                        }
                    }
                }
                if ($tblGroup->getMetaTable() == 'CUSTODY') {
                    $item['Occupation'] = '';
                    $item['Employment'] = '';
                    $item['Remark'] = '';
                    if (($tblCustody = Custody::useService()->getCustodyByPerson($tblPerson))) {
                        $item['Occupation'] = $tblCustody->getOccupation();
                        $item['Employment'] = $tblCustody->getEmployment();
                        $item['Remark'] = $tblCustody->getRemark();
                    }
                }
                if ($tblGroup->getMetaTable() == 'TEACHER') {
                    $item['TeacherAcronym'] = '';
                    if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))) {
                        $item['TeacherAcronym'] = $tblTeacher->getAcronym();
                    }
                }
                if ($tblGroup->getMetaTable() == 'CLUB') {
                    $item['ClubIdentifier'] = '';
                    $item['EntryDate'] = '';
                    $item['ExitDate'] = '';
                    $item['ClubRemark'] = '';
                    if (($tblClub = Club::useService()->getClubByPerson($tblPerson))) {
                        $item['ClubIdentifier'] = $tblClub->getIdentifier();
                        $item['EntryDate'] = $tblClub->getEntryDate();
                        $item['ExitDate'] = $tblClub->getExitDate();
                        $item['ClubRemark'] = $tblClub->getRemark();
                    }
                }
                array_push($TableContent, $item);
            });
        }
        return $TableContent;
    }

    /**
     * @param array    $TableContent
     * @param array    $tblPersonList
     * @param TblGroup $GroupId
     *
     * @return FilePointer
     */
    public function createGroupListExcel($TableContent, $tblPersonList, $tblGroup)
    {

        $ColumnList['Number'] = 'lfd. Nr.';
        $ColumnList['Salutation'] = 'Anrede';
        $ColumnList['Title'] = 'Titel';
        $ColumnList['FirstName'] = 'Vorname';
        $ColumnList['LastName'] = 'Nachname';
        $ColumnList['StreetName'] = 'Straße';
        $ColumnList['StreetNumber'] = 'Str.Nr';
        $ColumnList['Code'] = 'PLZ';
        $ColumnList['City'] = 'Ort';
        $ColumnList['District'] = 'Ortsteil';
        $ColumnList['PhoneNumber'] = 'Telefon Festnetz';
        $ColumnList['MobilPhoneNumber'] = 'Telefon Mobil';
        $ColumnList['Mail'] = 'E-mail';
        $ColumnList['Birthday'] = 'Geburtsdatum';
        $ColumnList['BirthdaySort'] = 'Sortierung Geburtstag';
        $ColumnList['BirthdayYearSort'] = 'Sortierung Geburtsdatum';
        $ColumnList['BirthPlace'] = 'Geburtsort';
        $ColumnList['Gender'] = 'Geschlecht';
        $ColumnList['Nationality'] = 'Staatsangehörigkeit';
        $ColumnList['Religion'] = 'Konfession';
        $ColumnList['Division'] = 'aktuelle Klasse';
        $ColumnList['ParticipationWillingness'] = 'Mitarbeitsbereitschaft';
        $ColumnList['ParticipationActivities'] = 'Mitarbeitsbereitschaft - Tätigkeiten';
        $ColumnList['RemarkExcel'] = 'Bemerkungen';
        if ($tblGroup->getMetaTable() == 'PROSPECT') {
            $ColumnList['ReservationDate'] = 'Eingangsdatum';
            $ColumnList['InterviewDate'] = 'Aufnahmegespräch';
            $ColumnList['TrialDate'] = 'Schnuppertag';
            $ColumnList['ReservationYear'] = 'Voranmeldung Schuljahr';
            $ColumnList['ReservationDivision'] = 'Voranmeldung Stufe';
            $ColumnList['SchoolTypeA'] = 'Voranmeldung Schulart A';
            $ColumnList['SchoolTypeB'] = 'Voranmeldung Schulart B';
        }
        if ($tblGroup->getMetaTable() == 'STUDENT') {
            $ColumnList['Identifier'] = 'Schülernummer';
            $ColumnList['School'] = 'Schule';
            $ColumnList['SchoolType'] = 'Schulart';
            $ColumnList['SchoolCourse'] = 'Bildungsgang';
            $ColumnList['Division'] = 'aktuelle Klasse';
            //Agreement Head
            if(($tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll())){
                foreach($tblAgreementCategoryAll as $tblAgreementCategory){
                    if(($tblAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblAgreementCategory))){
                        foreach($tblAgreementTypeList as $tblAgreementType){
                            $ColumnList['AgreementType'.$tblAgreementType->getId()] = $tblAgreementType->getName();
                        }
                    }
                }
            }
        }
        if ($tblGroup->getMetaTable() == 'CUSTODY') {
            $ColumnList['Occupation'] = 'Beruf';
            $ColumnList['Employment'] = 'Arbeitsstelle';
            $ColumnList['Remark'] = 'Bemerkung Sorgeberechtigter';
        }
        if ($tblGroup->getMetaTable() == 'TEACHER') {
            $ColumnList['TeacherAcronym'] = 'Lehrerkürzel';
        }
        if ($tblGroup->getMetaTable() == 'CLUB') {
            $ColumnList['ClubIdentifier'] = 'Mitgliedsnummer';
            $ColumnList['EntryDate'] = 'Eintrittsdatum';
            $ColumnList['ExitDate'] = 'Austrittsdatum';
            $ColumnList['ClubRemark'] = 'Bemerkung Vereinsmitglied';
        }
        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $Row = 0;
        $export->setValue($export->getCell(0, 0), 'Gruppenliste ' . $tblGroup->getName());
        if ($tblGroup->getDescription(true, true)) {
            $Row++;
            $export->setValue($export->getCell(0, 1), $tblGroup->getDescription(true, true));
        }
        if ($tblGroup->getRemark()) {
            $Row++;
            $export->setValue($export->getCell(0, 2), $tblGroup->getRemark());
        }
        $Row += 2;
        $Column = 0;
        foreach ($ColumnList as $Value) {
            $export->setValue($export->getCell($Column, $Row), $Value);
            $Column++;
        }
        $Row++;
        foreach ($TableContent as $PersonData) {
            $Column = 0;
            foreach ($ColumnList as $Key => $Value) {
                if (isset($PersonData[$Key])) {
                    // handle value as numeric
                    if ($Key == 'Number'
                        || $Key == 'BirthdaySort'
                        || $Key == 'BirthdayYearSort') {
                        // don't display if empty
                        if ($PersonData[$Key] != '') {
                            $export->setValue($export->getCell($Column, $Row), $PersonData[$Key],
                                PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        }
                    } else {
                        $export->setValue($export->getCell($Column, $Row), $PersonData[$Key]);
                        if ($Key == 'RemarkExcel') {
                            $export->setStyle($export->getCell($Column, $Row))->setWrapText()
                                ->setAlignmentMiddle();
                        }
                    }
                }
                $Column++;
            }
            $Row++;
        }
        $Row++;
        Person::setGenderFooter($export, $tblPersonList, $Row);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param $Identifier
     * @param $Item
     * @param TblPerson|null $tblPerson
     */
    private function setPersonData($Identifier, &$Item, TblPerson $tblPerson = null)
    {
        if ($tblPerson !== null) {
            $Item[$Identifier.'Salutation'] = $tblPerson->getSalutation();
            $Item[$Identifier.'Title'] = $tblPerson->getTitle();
            $Item[$Identifier.'LastName'] = $tblPerson->getLastName();
            $Item[$Identifier.'FirstName'] = $tblPerson->getFirstSecondName();
            if($Identifier != ''){
                $Item[$Identifier] = $tblPerson->getFullName();
            }
        }
    }

    /**
     * @param $Identifier
     * @param $Item
     * @param TblPerson|null $tblPerson
     */
    private function setPhoneNumbersExtended($Identifier, &$Item, TblPerson $tblPerson = null)
    {
        if($tblPerson !== null){
            $tblToPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
            if ($tblToPhoneList) {
                foreach ($tblToPhoneList as $tblToPhone) {

                    if(($tblPhoneType = $tblToPhone->getTblType())
                        && ($PhoneDescription = $tblPhoneType->getDescription())
                        && ($PhoneName = $tblPhoneType->getName())
                        && ($tblPhone = $tblToPhone->getTblPhone())){
                        if($PhoneDescription == 'Festnetz'){
                            switch($PhoneName) {
                                case 'Privat':
                                    if($Item[$Identifier.'PhoneFixedPrivate']){
                                        $Item[$Identifier.'PhoneFixedPrivate'] = $Item[$Identifier.'PhoneFixedPrivate'].', ';
                                    }
                                    $Item[$Identifier.'PhoneFixedPrivate'] = $tblPhone->getNumber();
                                    break;
                                case 'Geschäftlich':
                                    if($Item[$Identifier.'PhoneFixedWork']){
                                        $Item[$Identifier.'PhoneFixedWork'] = $Item[$Identifier.'PhoneFixedWork'].', ';
                                    }
                                    $Item[$Identifier.'PhoneFixedWork'] = $Item[$Identifier.'PhoneFixedWork'].$tblPhone->getNumber();
                                    break;
                                case 'Notfall':
                                    if($Item[$Identifier.'PhoneFixedEmergency']){
                                        $Item[$Identifier.'PhoneFixedEmergency'] = $Item[$Identifier.'PhoneFixedEmergency'].', ';
                                    }
                                    $Item[$Identifier.'PhoneFixedEmergency'] = $Item[$Identifier.'PhoneFixedEmergency'].$tblPhone->getNumber();
                                    break;
                            }
                        } elseif($PhoneDescription == 'Mobil') {
                            switch($PhoneName) {
                                case 'Privat':
                                    if($Item[$Identifier.'PhoneMobilePrivate']){
                                        $Item[$Identifier.'PhoneMobilePrivate'] = $Item[$Identifier.'PhoneMobilePrivate'].', ';
                                    }
                                    $Item[$Identifier.'PhoneMobilePrivate'] = $Item[$Identifier.'PhoneMobilePrivate'].$tblPhone->getNumber();
                                    break;
                                case 'Geschäftlich':
                                    if($Item[$Identifier.'PhoneMobileWork']){
                                        $Item[$Identifier.'PhoneMobileWork'] = $Item[$Identifier.'PhoneMobileWork'].', ';
                                    }
                                    $Item[$Identifier.'PhoneMobileWork'] = $Item[$Identifier.'PhoneMobileWork'].$tblPhone->getNumber();
                                    break;
                                case 'Notfall':
                                    if($Item[$Identifier.'PhoneMobileEmergency']){
                                        $Item[$Identifier.'PhoneMobileEmergency'] = $Item[$Identifier.'PhoneMobileEmergency'].', ';
                                    }
                                    $Item[$Identifier.'PhoneMobileEmergency'] = $Item[$Identifier.'PhoneMobileEmergency'].$tblPhone->getNumber();
                                    break;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param TblPerson $tblPersonGuardian
     * @param $item
     * @param $PhoneGuardianListSimple
     */
    private function setPhoneNumbers(TblPerson $tblPersonGuardian, &$item, &$PhoneGuardianListSimple)
    {
        $tblToPhoneList = Phone::useService()->getPhoneAllByPerson($tblPersonGuardian);
        if ($tblToPhoneList) {
            foreach ($tblToPhoneList as $tblToPhone) {
                if (($tblPhone = $tblToPhone->getTblPhone())) {
                    $shortType = str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                    $PhoneGuardianListSimple[$tblPhone->getId()] = $tblPhone->getNumber();
                    if (!isset($item['PhoneGuardian'][$tblPersonGuardian->getId()])) {
                        $item['PhoneGuardian'][$tblPersonGuardian->getId()] = $tblPersonGuardian->getFirstName().' '.$tblPersonGuardian->getLastName().
                            ' ('.$tblPhone->getNumber().' '.$shortType;
                    } else {
                        $item['PhoneGuardian'][$tblPersonGuardian->getId()] .= ', '.$tblPhone->getNumber().' '.str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                    }
                }
            }
        }
        if (isset($item['PhoneGuardian'][$tblPersonGuardian->getId()])) {
            $item['PhoneGuardian'][$tblPersonGuardian->getId()] .= ')';
        }
    }

    /**
     * @param $Identifier
     * @param $Item
     * @param TblPerson|null $tblPerson
     */
    private function setMailsExtended($Identifier, &$Item, TblPerson $tblPerson = null)
    {
        if($tblPerson !== null){
            if (($tblToPersonList = Mail::useService()->getMailAllByPerson($tblPerson))) {
                foreach ($tblToPersonList as $tblToPerson) {
                    $tblType = $tblToPerson->getTblType();
                    $tblMail = $tblToPerson->getTblMail();
                    if ($tblType->getName() == TblTypeMail::VALUE_PRIVATE) {
                        if($Item[$Identifier.'MailPrivate']){
                            $Item[$Identifier.'MailPrivate'] = $Item[$Identifier.'MailPrivate'].', ';
                        }
                        $Item[$Identifier.'MailPrivate'] = $tblMail->getAddress();
                    } elseif($tblType->getName() == TblTypeMail::VALUE_BUSINESS) {
                        if($Item[$Identifier.'MailWork']){
                            $Item[$Identifier.'MailWork'] = $Item[$Identifier.'MailWork'].', ';
                        }
                        $Item[$Identifier.'MailWork'] = $tblMail->getAddress();
                    }
                }
            }
        }
    }

    /**
     * @param TblPerson $tblPersonGuardian
     * @param $tblMailList
     * @param $MailListSimple
     */
    private function setMails(TblPerson $tblPersonGuardian, &$tblMailList, &$MailListSimple)
    {
        $tblToPersonMailList = Mail::useService()->getMailAllByPerson($tblPersonGuardian);
        if ($tblToPersonMailList) {
            foreach ($tblToPersonMailList as $tblToPersonMail) {
                $tblMail = $tblToPersonMail->getTblMail();
                if ($tblMail) {
                    $MailListSimple[$tblMail->getId()] = $tblMail->getAddress();
                    if (isset($tblMailList[$tblPersonGuardian->getId()])) {
                        $tblMailList[$tblPersonGuardian->getId()] = $tblMailList[$tblPersonGuardian->getId()].', '
                            .$tblMail->getAddress();
                    } else {
                        $tblMailList[$tblPersonGuardian->getId()] = $tblPersonGuardian->getFirstName().' '.
                            $tblPersonGuardian->getLastName().' ('
                            .$tblMail->getAddress();
                    }
                }
            }
            if (isset($tblMailList[$tblPersonGuardian->getId()])) {
                $tblMailList[$tblPersonGuardian->getId()] = $tblMailList[$tblPersonGuardian->getId()].')';
            }
        }
    }

    /**
     * @param $Data
     *
     * @return array|TblPerson[]
     */
    public function getStudentFilterResult($Data)
    {

        ini_set('memory_limit', '1G');
        $tblPersonList = array();
        if(empty($Data) || !isset($Data['YearId']) || !$Data['YearId']){
            return $tblPersonList;
        }
        $tblYear = Term::useService()->getYearById($Data['YearId']);
        $tblDivisionCourse = null;
        if(!isset($Data['DivisionCourseId'])){
            $tblDivisionCourse = null;
        } elseif (isset($Data['DivisionCourseId']) && !($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($Data['DivisionCourseId']))){
            $tblDivisionCourse = null;
        }
        $tblTypeSchool = null;
        if(!isset($Data['TypeId'])){
            $tblTypeSchool = null;
        } elseif (isset($Data['TypeId']) && !($tblTypeSchool = Type::useService()->getTypeById($Data['TypeId']))) {
            $tblTypeSchool = null;
        }
        $level = '';
        if(isset($Data['Level']) && $Data['Level'] != '0'){
            $level = $Data['Level'];
        }
        return DivisionCourse::useService()->getPersonListByYear($tblYear, $tblDivisionCourse, $tblTypeSchool, $level);
    }

    /**
     * @param array $tblPersonList
     * @param array $Data
     *
     * @return array $TableContent
     */
    public function getStudentTableContent($tblPersonList = array(), $Data = array())
    {

        $TableContent = array();
        if (empty($tblPersonList)) {
            return $TableContent;
        }
        /* @var TblPerson $tblPerson */
        foreach($tblPersonList as $tblPerson){
            $item['Level'] = '';
            $item['DivisionCourse'] = '';
            $item['CoreGroup'] = '';
            $item['StudentNumber'] = '';
            $item['FirstName'] = $tblPerson->getFirstSecondName();
            $item['LastName'] = $tblPerson->getLastName();
            $item['Gender'] = $tblPerson->getGenderString();
            $item['Birthday'] = $tblPerson->getBirthday();
            $item['BirthPlace'] = $tblPerson->getBirthplaceString();
            $item['School'] = '';
            $item['SchoolType'] = '';
            $item['Denomination'] = $tblPerson->getDenominationString();
            $item['Nationality'] = $tblPerson->getNationalityString();
            $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = '';
            $item['Address'] = '';
            // Address
            $item = $this->getAddressDataFromPerson($tblPerson, $item);
            $item['Insurance'] = $item['InsuranceState'] = '';
            $item['Medication'] = $item['MailPrivate'] = $item['MailWork'] = $item['PhoneFixedPrivate'] = $item['PhoneFixedWork'] = '';
            $item['PhoneFixedEmergency'] = $item['PhoneMobilePrivate'] = $item['PhoneMobileWork'] = $item['PhoneMobileEmergency'] = '';
            $item['Sibling_1'] = $item['Sibling_2'] = $item['Sibling_3'] = '';
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                $item['StudentNumber'] = $tblStudent->getIdentifierComplete();
                if (($tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord())) {
                    $item['Insurance'] = $tblStudentMedicalRecord->getInsurance();
                    $item['InsuranceState'] = $tblStudentMedicalRecord->getInsuranceState();
                    $item['Medication'] = $tblStudentMedicalRecord->getMedication();
                }
            }
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))) {
                $item['Level'] = $tblStudentEducation->getLevel();
                if(($tblCompany = $tblStudentEducation->getServiceTblCompany())){
                    $item['School'] = $tblCompany->getDisplayName();
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
                $item['CourseType'] = $courseName;
                $item['SchoolType'] = $tblSchoolType->getName();
                // Klasse / Gruppe
                if(($tblDivisionCourseClass = $tblStudentEducation->getTblDivision())){
                    $item['DivisionCourse'] = $tblDivisionCourseClass->getDisplayName();
                }
                if(($tblDivisionCourseCoreGroup = $tblStudentEducation->getTblCoreGroup())){
                    $item['CoreGroup'] = ($item['CoreGroup'] ? ', ' : '').$tblDivisionCourseCoreGroup->getDisplayName();
                }
            }
            if(($tblMailAll = Mail::useService()->getMailAllByPerson($tblPerson))){
                foreach($tblMailAll as $tblToPersonMail) {
                    if(($tblTypeMail = $tblToPersonMail->getTblType())
                        && ($tblMail = $tblToPersonMail->getTblMail())){
                        if($tblTypeMail->getName() == 'Privat'){
                            $item['MailPrivate'] = $tblMail->getAddress();
                        } elseif($tblTypeMail->getName() == 'Geschäftlich') {
                            $item['MailWork'] = $tblMail->getAddress();
                        }
                    }
                }
            }
            if (($tblPhoneAll = Phone::useService()->getPhoneAllByPerson($tblPerson))) {
                foreach ($tblPhoneAll as $tblToPerson) {
                    /** @var TblToPerson $tblToPerson */
                    if (($tblPhoneType = $tblToPerson->getTblType())
                    && ($PhoneDescription = $tblPhoneType->getDescription())
                    && ($PhoneName = $tblPhoneType->getName())
                    && ($tblPhone = $tblToPerson->getTblPhone())) {
                        if ($PhoneDescription == 'Festnetz') {
                            switch ($PhoneName) {
                                case 'Privat':
                                    if (empty($item['PhoneFixedPrivate'])) {
                                        $item['PhoneFixedPrivate'] = $tblPhone->getNumber()
                                            . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                    } else {
                                        $item['PhoneFixedPrivate'] = $item['PhoneFixedPrivate'].', ' . $tblPhone->getNumber()
                                            . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                    }
                                    break;
                                case 'Geschäftlich':
                                    if (empty($item['PhoneFixedWork'])) {
                                        $item['PhoneFixedWork'] = $tblPhone->getNumber()
                                            . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                    } else {
                                        $item['PhoneFixedWork'] = $item['PhoneFixedWork'].', ' . $tblPhone->getNumber()
                                            . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                    }
                                    break;
                                case 'Notfall':
                                    if (empty($item['PhoneFixedEmergency'])) {
                                        $item['PhoneFixedEmergency'] = $tblPhone->getNumber()
                                            . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                    } else {
                                        $item['PhoneFixedEmergency'] = $item['PhoneFixedEmergency'].', ' . $tblPhone->getNumber()
                                            . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                    }
                                    break;
                            }
                        } elseif ($PhoneDescription == 'Mobil') {
                            switch ($PhoneName) {
                                case 'Privat':
                                    if (empty($item['PhoneMobilePrivate'])) {
                                        $item['PhoneMobilePrivate'] = $tblPhone->getNumber()
                                            . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                    } else {
                                        $item['PhoneMobilePrivate'] = $item['PhoneMobilePrivate'].', ' . $tblPhone->getNumber()
                                            . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                    }
                                    break;
                                case 'Geschäftlich':
                                    if (empty($item['PhoneMobileWork'])) {
                                        $item['PhoneMobileWork'] = $tblPhone->getNumber()
                                            . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                    } else {
                                        $item['PhoneMobileWork'] = $item['PhoneMobileWork'].', ' . $tblPhone->getNumber()
                                            . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                    }
                                    break;
                                case 'Notfall':
                                    if (empty($item['PhoneMobileEmergency'])) {
                                        $item['PhoneMobileEmergency'] = $tblPhone->getNumber()
                                            . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                    } else {
                                        $item['PhoneMobileEmergency'] = $item['PhoneMobileEmergency'].', ' . $tblPhone->getNumber()
                                            . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                    }
                                    break;
                            }
                        }
                    }
                }
            }
            $tblTypeSibling = Relationship::useService()->getTypeByName('Geschwisterkind');
            if (($tblRelationshipSibling = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblTypeSibling))) {
                $SiblingCount = 1;
                foreach ($tblRelationshipSibling as $tblToPerson) {
                    $SiblingString = '';
                    if (($tblPersonFrom = $tblToPerson->getServiceTblPersonFrom()) && ($tblPersonTo = $tblToPerson->getServiceTblPersonTo())) {
                        if ($tblPersonFrom->getId() !== $tblPerson->getId()) {
                            $tblPersonSibling = $tblPersonFrom;
                        } elseif ($tblPersonTo->getId() !== $tblPerson->getId()) {
                            $tblPersonSibling = $tblPersonTo;
                        }
                        $SiblingString = $tblPersonSibling->getLastName() . ', ' . $tblPersonSibling->getFirstSecondName();
                        if (($tblYear = Term::useService()->getYearById($Data['YearId']))
                        && ($SiblingStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPersonSibling, $tblYear))) {
                            $DivisionSting = '';
                            if(($tblDivisionCourseDivision = $SiblingStudentEducation->getTblDivision())){
                                $DivisionSting = $tblDivisionCourseDivision->getDisplayName();
                            }
                            if(($tblDivisionCourseCoreGroup = $SiblingStudentEducation->getTblCoreGroup())){
                                if($DivisionSting){
                                    $DivisionSting = ', '.$tblDivisionCourseCoreGroup->getDisplayName();
                                } else {
                                    $DivisionSting = $tblDivisionCourseCoreGroup->getDisplayName();
                                }
                            }
                            $SiblingString = $SiblingString.' ('.$DivisionSting.')';
                        } else {
                            if (isset($Data['Sibling'])) {
                                $SiblingString = $SiblingString.' (Ehemalig)';
                            } else {
                                $SiblingString = '';
                            }
                        }
                    }
                    if($SiblingString){
                        $item['Sibling_'.$SiblingCount++] = $SiblingString;
                    }
                }
            }

            // Definition mit leerwerten wird für das Frontend benötigt
            $TypeList = array('Sorgeberechtigt' => 3, 'Vormund' => 3, 'Bevollmächtigt' => 3, 'Notfallkontakt' => 4);
            foreach($TypeList as $Type => $Count){
                for($j = 1; $j <= $Count; $j++) {
                    $item[$Type.$j.'_Salutation'] = '';
                    $item[$Type.$j.'_Title'] = '';
                    $item[$Type.$j.'_FirstName'] = '';
                    $item[$Type.$j.'_LastName'] = '';
                    $item[$Type.$j.'_Birthday'] = '';
                    $item[$Type.$j.'_BirthPlace'] = '';
                    $item[$Type.$j.'_Job'] = '';
                    $item[$Type.$j.'_Address'] = '';
                    $item[$Type.$j.'_Street'] = '';
                    $item[$Type.$j.'_HouseNumber'] = '';
                    $item[$Type.$j.'_CityCode'] = '';
                    $item[$Type.$j.'_City'] = '';
                    $item[$Type.$j.'_District'] = '';
                    $item[$Type.$j.'_PhoneFixedPrivate'] = '';
                    $item[$Type.$j.'_PhoneFixedWork'] = '';
                    $item[$Type.$j.'_PhoneFixedEmergency'] = '';
                    $item[$Type.$j.'_PhoneMobilePrivate'] = '';
                    $item[$Type.$j.'_PhoneMobileWork'] = '';
                    $item[$Type.$j.'_PhoneMobileEmergency'] = '';
                    $item[$Type.$j.'_Mail_Private'] = '';
                    $item[$Type.$j.'_Mail_Work'] = '';
                }
            }
            $this->setRelationshipContent($tblPerson, $item);
            array_push($TableContent, $item);
        }
        return $TableContent;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $item
     * @return void
     */
    public function setRelationshipContent(TblPerson $tblPerson, &$item)
    {

        // Erstellen der Personenlisten, und die Zählung über die Beziehungstypen
        $tblToPersonList = array();
        if(($tblType = Relationship::useService()->getTypeByName('Sorgeberechtigt'))){
            if(($tblRelationshipCustodyList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))){
                if(!isset($this->MetaComparisonList['Sorgeberechtigt']) || $this->MetaComparisonList['Sorgeberechtigt'] < count($tblRelationshipCustodyList)){
                    $this->MetaComparisonList['Sorgeberechtigt'] = count($tblRelationshipCustodyList);
                }
                foreach($tblRelationshipCustodyList as $tblRelationshipCustody){
                    $tblToPersonList[] = $tblRelationshipCustody;
                }
            }
        }
        if(($tblType = Relationship::useService()->getTypeByName('Vormund'))){
            if(($tblRelationshipGuardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))){
                if(!isset($this->MetaComparisonList['Vormund']) || $this->MetaComparisonList['Vormund'] < count($tblRelationshipGuardianList)){
                    $this->MetaComparisonList['Vormund'] = count($tblRelationshipGuardianList);
                }
                foreach($tblRelationshipGuardianList as $tblRelationshipGuardian){
                    $tblToPersonList[] = $tblRelationshipGuardian;
                }
            }
        }
        if(($tblType = Relationship::useService()->getTypeByName('Bevollmächtigt'))){
            if(($tblRelationshipAuthorizedList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))){
                if(!isset($this->MetaComparisonList['Bevollmächtigt']) || $this->MetaComparisonList['Bevollmächtigt'] < count($tblRelationshipAuthorizedList)){
                    $this->MetaComparisonList['Bevollmächtigt'] = count($tblRelationshipAuthorizedList);
                }
                foreach($tblRelationshipAuthorizedList as $tblRelationshipAuthorized){
                    $tblToPersonList[] = $tblRelationshipAuthorized;
                }
            }
        }
        if(($tblType = Relationship::useService()->getTypeByName('Notfallkontakt'))){
            if(($tblRelationshipEmergencyList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))){
                if(!isset($this->MetaComparisonList['Notfallkontakt']) || $this->MetaComparisonList['Notfallkontakt'] < count($tblRelationshipEmergencyList)){
                    $this->MetaComparisonList['Notfallkontakt'] = count($tblRelationshipEmergencyList);
                }
                foreach($tblRelationshipEmergencyList as $tblRelationshipEmergency){
                    $tblToPersonList[] = $tblRelationshipEmergency;
                }
            }
        }

        if(!empty($tblToPersonList)){
            $TypeCount = array('Sorgeberechtigt' => 0, 'Vormund' => 0, 'Bevollmächtigt' => 0, 'Notfallkontakt' => 0);
            foreach($tblToPersonList as $tblToPerson){
                $TypeName = $tblToPerson->getTblType()->getName();
                if($tblToPerson->getRanking()){
                    $Rank = $tblToPerson->getRanking();
                } else {
                    $Rank = ++$TypeCount[$TypeName];
                }
                // mehr als 3 Einträge in einem Beziehungstyp werden ignoriert
                if($Rank > 3){
                    continue;
                }
                if(($tblPersonRelationship = $tblToPerson->getServiceTblPersonFrom())){
                    $item[$TypeName.$Rank.'_Salutation'] = $tblPersonRelationship->getSalutation();
                    $item[$TypeName.$Rank.'_Title'] = $tblPersonRelationship->getTitle();
                    $item[$TypeName.$Rank.'_FirstName'] = $tblPersonRelationship->getFirstName();
                    if($tblPersonRelationship->getSecondName()){
                        $item[$TypeName.$Rank.'_FirstName'] = $item[$TypeName.$Rank.'_FirstName'].' '.$tblPersonRelationship->getSecondName();
                    }
                    $item[$TypeName.$Rank.'_LastName'] = $tblPersonRelationship->getLastName();
                    if(($tblCommonCustody = Common::useService()->getCommonByPerson($tblPersonRelationship))){
                        if(($tblCommonBirthDatesCustody = $tblCommonCustody->getTblCommonBirthDates())){
                            $item[$TypeName.$Rank.'_Birthday'] = $tblCommonBirthDatesCustody->getBirthday();
                            $item[$TypeName.$Rank.'_BirthPlace'] = $tblCommonBirthDatesCustody->getBirthplace();
                        }
                    }
                    if(($tblCustody = Custody::useService()->getCustodyByPerson($tblPersonRelationship))){
                        $item[$TypeName.$Rank.'_Job'] = $tblCustody->getOccupation();
                    }
                    if(($tblAddressCustody = Address::useService()->getAddressByPerson($tblPersonRelationship))){
                        $item[$TypeName.$Rank.'_Address'] = $tblAddressCustody->getGuiString();
                        if(($tblCityCustody = $tblAddressCustody->getTblCity())){
                            $item[$TypeName.$Rank.'_Street'] = $tblAddressCustody->getStreetName();
                            $item[$TypeName.$Rank.'_HouseNumber'] = $tblAddressCustody->getStreetNumber();
                            $item[$TypeName.$Rank.'_CityCode'] = $tblCityCustody->getCode();
                            $item[$TypeName.$Rank.'_City'] = $tblCityCustody->getName();
                            $item[$TypeName.$Rank.'_District'] = $tblCityCustody->getDisplayDistrict();
                        }
                    }

                    if(($tblPhoneAllCustody = Phone::useService()->getPhoneAllByPerson($tblPersonRelationship))){
                        foreach($tblPhoneAllCustody as $tblToPersonCustody) {
                            /** @var TblToPerson $tblToPersonCustody */
                            if(($tblPhoneTypeCustody = $tblToPersonCustody->getTblType())
                                && ($PhoneDescriptionCustody = $tblPhoneTypeCustody->getDescription())
                                && ($PhoneNameCustody = $tblPhoneTypeCustody->getName())
                                && ($tblPhoneCustody = $tblToPersonCustody->getTblPhone())){
                                if($PhoneDescriptionCustody == 'Festnetz'){
                                    switch($PhoneNameCustody) {
                                        case 'Privat':
                                            if($item[$TypeName.$Rank.'_PhoneFixedPrivate']){
                                                $item[$TypeName.$Rank.'_PhoneFixedPrivate'] = $item[$TypeName.$Rank.'_PhoneFixedPrivate'].', ';
                                            }
                                            $item[$TypeName.$Rank.'_PhoneFixedPrivate'] = $tblPhoneCustody->getNumber()
                                                .($tblToPersonCustody->getRemark() ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                            break;
                                        case 'Geschäftlich':
                                            if($item[$TypeName.$Rank.'_PhoneFixedWork']){
                                                $item[$TypeName.$Rank.'_PhoneFixedWork'] = $item[$TypeName.$Rank.'_PhoneFixedWork'].', ';
                                            }
                                            $item[$TypeName.$Rank.'_PhoneFixedWork'] = $item[$TypeName.$Rank.'_PhoneFixedWork'].$tblPhoneCustody->getNumber()
                                                .($tblToPersonCustody->getRemark() ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                            break;
                                        case 'Notfall':
                                            if($item[$TypeName.$Rank.'_PhoneFixedEmergency']){
                                                $item[$TypeName.$Rank.'_PhoneFixedEmergency'] = $item[$TypeName.$Rank.'_PhoneFixedEmergency'].', ';
                                            }
                                            $item[$TypeName.$Rank.'_PhoneFixedEmergency'] = $item[$TypeName.$Rank.'_PhoneFixedEmergency'].$tblPhoneCustody->getNumber()
                                                .($tblToPersonCustody->getRemark() ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                            break;
                                    }
                                } elseif($PhoneDescriptionCustody == 'Mobil') {
                                    switch($PhoneNameCustody) {
                                        case 'Privat':
                                            if($item[$TypeName.$Rank.'_PhoneMobilePrivate']){
                                                $item[$TypeName.$Rank.'_PhoneMobilePrivate'] = $item[$TypeName.$Rank.'_PhoneMobilePrivate'].', ';
                                            }
                                            $item[$TypeName.$Rank.'_PhoneMobilePrivate'] = $item[$TypeName.$Rank.'_PhoneMobilePrivate'].$tblPhoneCustody->getNumber()
                                                .($tblToPersonCustody->getRemark() ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                            break;
                                        case 'Geschäftlich':
                                            if($item[$TypeName.$Rank.'_PhoneMobileWork']){
                                                $item[$TypeName.$Rank.'_PhoneMobileWork'] = $item[$TypeName.$Rank.'_PhoneMobileWork'].', ';
                                            }
                                            $item[$TypeName.$Rank.'_PhoneMobileWork'] = $item[$TypeName.$Rank.'_PhoneMobileWork'].$tblPhoneCustody->getNumber()
                                                .($tblToPersonCustody->getRemark() ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                            break;
                                        case 'Notfall':
                                            if($item[$TypeName.$Rank.'_PhoneMobileEmergency']){
                                                $item[$TypeName.$Rank.'_PhoneMobileEmergency'] = ', ';
                                            }
                                            $item[$TypeName.$Rank.'_PhoneMobileEmergency'] = $item[$TypeName.$Rank.'_PhoneMobileEmergency'].$tblPhoneCustody->getNumber()
                                                .($tblToPersonCustody->getRemark() ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                            break;
                                    }
                                }
                            }
                        }
                    }
                    if(($tblMailAllCustody = Mail::useService()->getMailAllByPerson($tblPersonRelationship))){
                        foreach($tblMailAllCustody as $tblToPersonMailCustody) {
                            if(($tblTypeMailCustody = $tblToPersonMailCustody->getTblType())
                                && ($tblMailCustody = $tblToPersonMailCustody->getTblMail())){
                                if($tblTypeMailCustody->getName() == 'Privat'){
                                    $item[$TypeName.$Rank.'_Mail_Private'] = $tblMailCustody->getAddress();
                                } elseif($tblTypeMailCustody->getName() == 'Geschäftlich') {
                                    $item[$TypeName.$Rank.'_Mail_Work'] = $tblMailCustody->getAddress();
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param null $Person
     * @param null $Year
     * @param null $Division
     * @param null $Option
     * @param null $PersonGroup
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createMetaDataComparisonExcel(array $Data = array())
    {

        $tblPersonList = $this->getStudentFilterResult($Data);
        $TableContent = $this->getStudentTableContent($tblPersonList, $Data);
        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $Row = 0;
        $Column = 0;
        $export->setValue($export->getCell($Column++, $Row), "Stufe");
        $export->setValue($export->getCell($Column++, $Row), "Klasse");
        $export->setValue($export->getCell($Column++, $Row), "Stammgruppe");
        $export->setValue($export->getCell($Column++, $Row), "Schülernummer");
        $export->setValue($export->getCell($Column++, $Row), "Vorname");
        $export->setValue($export->getCell($Column++, $Row), "Nachname");
        $export->setValue($export->getCell($Column++, $Row), "Geschlecht");
        $export->setValue($export->getCell($Column++, $Row), "Geburtstag");
        $export->setValue($export->getCell($Column++, $Row), "Geburtsort");
        $export->setValue($export->getCell($Column++, $Row), "Bildungsgang");
        $export->setValue($export->getCell($Column++, $Row), "Schule");
        $export->setValue($export->getCell($Column++, $Row), "Schulart");
        $export->setValue($export->getCell($Column++, $Row), "Staatsangehörigkeit");
        $export->setValue($export->getCell($Column++, $Row), "Straße");
        $export->setValue($export->getCell($Column++, $Row), "Hausnummer");
        $export->setValue($export->getCell($Column++, $Row), "PLZ");
        $export->setValue($export->getCell($Column++, $Row), "Wohnort");
        $export->setValue($export->getCell($Column++, $Row), "Ortsteil");
        $export->setValue($export->getCell($Column++, $Row), "Medikamente");
        $export->setValue($export->getCell($Column++, $Row), "Versicherungsstatus");
        $export->setValue($export->getCell($Column++, $Row), "Krankenkasse");
        $export->setValue($export->getCell($Column++, $Row), "Konfession");
        $export->setValue($export->getCell($Column++, $Row), "Festnetz (Privat)");
        $export->setValue($export->getCell($Column++, $Row), "Festnetz (Geschäftl.)");
        $export->setValue($export->getCell($Column++, $Row), "Festnetz (Notfall)");
        $export->setValue($export->getCell($Column++, $Row), "Mobil (Privat)");
        $export->setValue($export->getCell($Column++, $Row), "Mobil (Geschäftl.)");
        $export->setValue($export->getCell($Column++, $Row), "Mobil (Notfall)");
        $export->setValue($export->getCell($Column++, $Row), "E-Mail Privat");
        $export->setValue($export->getCell($Column++, $Row), "E-Mail Geschäftlich");
        $export->setValue($export->getCell($Column++, $Row), "Geschwister1");
        $export->setValue($export->getCell($Column++, $Row), "Geschwister2");
        $export->setValue($export->getCell($Column++, $Row), "Geschwister3");
        $MetaComparisonList = $this->getMetaComparisonList();
        foreach($MetaComparisonList as $Type => $TypeCount){
            if($TypeCount >= 1){
                for($i = 1; $i <= $TypeCount ; $i++) {
                    $TableHead[$Type.$i.'_Salutation'] = $Type.' '.$i.' Anrede';
                    $TableHead[$Type.$i.'_Title'] = $Type.' '.$i.' Titel';
                    $TableHead[$Type.$i.'_FirstName'] = $Type.' '.$i.' Vorname';
                    $TableHead[$Type.$i.'_LastName'] = $Type.' '.$i.' Nachname';
                    $TableHead[$Type.$i.'_Birthday'] = $Type.' '.$i.' Geburtsdatum';
                    $TableHead[$Type.$i.'_BirthPlace'] = $Type.' '.$i.' Geburtsort';
                    $TableHead[$Type.$i.'_Job'] = $Type.' '.$i.' Beruf';
                    $TableHead[$Type.$i.'_Address'] = $Type.' '.$i.' Adresse';
                    $TableHead[$Type.$i.'_PhoneFixedPrivate'] = $Type.' '.$i.' Festnetz (Privat)';
                    $TableHead[$Type.$i.'_PhoneFixedWork'] = $Type.' '.$i.' Festnetz (Geschäftl.)';
                    $TableHead[$Type.$i.'_PhoneFixedEmergency'] = $Type.' '.$i.' Festnetz (Notfall)';
                    $TableHead[$Type.$i.'_PhoneMobilePrivate'] = $Type.' '.$i.' Festnetz (Privat)';
                    $TableHead[$Type.$i.'_PhoneMobileWork'] = $Type.' '.$i.' Festnetz (Geschäftl.)';
                    $TableHead[$Type.$i.'_PhoneMobileEmergency'] = $Type.' '.$i.' Festnetz (Notfall)';
                    $TableHead[$Type.$i.'_Mail_Private'] = $Type.' '.$i.' Mail (Privat)';
                    $TableHead[$Type.$i.'_Mail_Work'] = $Type.' '.$i.' Mail (Geschäftl.)';
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Anrede');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Titel');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Vorname');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Nachname');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Geburtstag');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Geburtsort');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Beruf');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Straße');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Hausnummer');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' PLZ');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Wohnort');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Ortsteil');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Festnetz (Privat)');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Festnetz (Geschäftl.)');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Festnetz (Notfall)');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Mobil (Privat)');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Mobil (Geschäftl.)');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Mobil (Notfall)');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Mail (Privat)');
                    $export->setValue($export->getCell($Column++, $Row), $Type.' '.$i.' Mail (Geschäftl.)');
                }
            }
        }
        foreach ($TableContent as $PersonData) {
            $Row++;
            $Column = 0;
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Level']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['DivisionCourse']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['CoreGroup']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['StudentNumber']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['FirstName']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['LastName']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Gender']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Birthday']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['BirthPlace']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['CourseType']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['School']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['SchoolType']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Nationality']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['StreetName']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['StreetNumber']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Code']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['City']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['District']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Medication']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['InsuranceState']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Insurance']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Denomination']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneFixedPrivate']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneFixedWork']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneFixedEmergency']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneMobilePrivate']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneMobileWork']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneMobileEmergency']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['MailPrivate']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['MailWork']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Sibling_1']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Sibling_2']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Sibling_3']);
            foreach($MetaComparisonList as $Type => $TypeCount){
                if($TypeCount >= 1){
                    for($j = 1; $j <= $TypeCount ; $j++) {
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_Salutation']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_Title']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_FirstName']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_LastName']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_Birthday']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_BirthPlace']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_Job']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_Street']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_HouseNumber']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_CityCode']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_City']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_District']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_PhoneFixedPrivate']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_PhoneFixedWork']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_PhoneFixedEmergency']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_PhoneMobilePrivate']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_PhoneMobileWork']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_PhoneMobileEmergency']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_Mail_Private']);
                        $export->setValue($export->getCell($Column++, $Row), $PersonData[$Type.$j.'_Mail_Work']);
                    }
                }
            }
        }
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param $tblPersonList
     *
     * @return array
     */
    public function createMedicalRecordClassList($tblPersonList)
    {
        $TableContent = array();
        if ($tblPersonList) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['StudentNumber'] = '';
                $Item['Birthday'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Disease'] = '';
                $Item['Medication'] = '';
                $Item['AttendingDoctor'] = '';

                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $tblBirhdates = $tblCommon->getTblCommonBirthDates();
                    if ($tblBirhdates) {
                        $Item['Birthday'] = $tblBirhdates->getBirthday();
                    }
                }

                if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                    $Item['StudentNumber'] = $tblStudent->getIdentifier();
                    if(($tblMedicalRecord = $tblStudent->getTblStudentMedicalRecord())){
                        $Item['Disease'] = $tblMedicalRecord->getDisease();
                        $Item['Medication'] = $tblMedicalRecord->getMedication();
                        $Item['AttendingDoctor'] = $tblMedicalRecord->getAttendingDoctor();
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
    public function createMedicalRecordClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Schülernummer");
            $export->setValue($export->getCell("1", "0"), "Name, Vorname");
            $export->setValue($export->getCell("2", "0"), "Anschrift");
            $export->setValue($export->getCell("3", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("4", "0"), "Krankheiten/Allergie");
            $export->setValue($export->getCell("5", "0"), "Medikamente");
            $export->setValue($export->getCell("6", "0"), "Behandelnder Arzt");

            $Row = 1;

            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell("0", $Row), $PersonData['StudentNumber']);
                $export->setValue($export->getCell("1", $Row), $PersonData['Name']);
                $export->setValue($export->getCell("2", $Row), $PersonData['Address']);
                $export->setValue($export->getCell("3", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("4", $Row), $PersonData['Disease']);
                $export->setValue($export->getCell("5", $Row), $PersonData['Medication']);
                $export->setValue($export->getCell("6", $Row), $PersonData['AttendingDoctor']);
                $Row++;
            }

            $Row++;
            Person::setGenderFooter($export, $tblPersonList, $Row);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param array $tblPersonList
     *
     * @return array
     */
    public function createAgreementList($tblPersonList = array())
    {

        $TableContent = array();

        if (!empty($tblPersonList)) {
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy(TblPerson::ATTR_LAST_NAME, new StringGermanOrderSorter());
            $All = 0;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$All) {

                $All++;
                $Item['StudentNumber'] = '';
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['AddressExcel'] = '';
                $Item['Birthday'] = '';

                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    if (($tblBirthdates = $tblCommon->getTblCommonBirthDates())) {
                        $Item['Birthday'] = $tblBirthdates->getBirthday();
                    }
                }
                if (($tblAddress = Address::useService()->getAddressByPerson($tblPerson))) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiTwoRowString();
                    $Item['AddressExcel'] = $tblAddress->getGuiString();
                }

                // leer befüllen
                if(($tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll())){
                    foreach($tblAgreementCategoryAll as $tblAgreementCategory){
                        if (($tblAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblAgreementCategory))) {
                            foreach ($tblAgreementTypeList as $tblAgreementType) {
                                $Item['AgreementType'][$tblAgreementType->getId()] = 'Nein';
                                $Item['AgreementType' . $tblAgreementType->getId()] = 'Nein';
                            }
                        }
                    }
                }

                if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                    $Item['StudentNumber'] = $tblStudent->getIdentifier();
                    // befüllen was Gesetzt ist
                    if(($tblAgreementList = Student::useService()->getStudentAgreementAllByStudent($tblStudent))){
                        foreach($tblAgreementList as $tblAgreement){
                            if(($tblAgreementType = $tblAgreement->getTblStudentAgreementType())){
                                $Item['AgreementType'][$tblAgreementType->getId()] = 'Ja';
                                $Item['AgreementType'.$tblAgreementType->getId()] = 'Ja';
                            }
                        }
                    }
                }
                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

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
                    if (($tblAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblAgreementCategory))) {
                        foreach ($tblAgreementTypeList as $tblAgreementType) {
                            $ColumnCustom['AgreementType' . $tblAgreementType->getId()] = $tblAgreementType->getName();
                        }
                    }
                }
            }

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['StudentNumber'] = '';
                $Item['Birthday'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['AddressExcel'] = '';
                // Grundlegend setzen und befüllen
                if(($tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll())){
                    foreach($tblAgreementCategoryAll as $tblAgreementCategory){
                        if (($tblAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblAgreementCategory))) {
                            foreach ($tblAgreementTypeList as $tblAgreementType) {
                                $Item['AgreementType'][$tblAgreementType->getId()] = 'Nein';
                            }
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
                $export->setValue($export->getCell($Column++, $Row), $PersonData['AddressExcel']);
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

    /**
     * @param array $tblPersonList
     *
     * @return array
     */
    public function createPersonAgreementList($tblPersonList = array())
    {

        $TableContent = array();

        if (!empty($tblPersonList)) {
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy(TblPerson::ATTR_LAST_NAME, new StringGermanOrderSorter());
            $All = 0;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$All) {

                $All++;
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['AddressExcel'] = '';
                $Item['Birthday'] = '';

                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    if (($tblBirthdates = $tblCommon->getTblCommonBirthDates())) {
                        $Item['Birthday'] = $tblBirthdates->getBirthday();
                    }
                }
                if (($tblAddress = Address::useService()->getAddressByPerson($tblPerson))) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiTwoRowString();
                    $Item['AddressExcel'] = $tblAddress->getGuiString();
                }
                $Item['AgreementType'] = array();
                // leer befüllen
                if(($tblAgreementCategoryAll = Agreement::useService()->getPersonAgreementCategoryAll())){
                    foreach($tblAgreementCategoryAll as $tblAgreementCategory){
                        if (($tblAgreementTypeList = Agreement::useService()->getPersonAgreementTypeAllByCategory($tblAgreementCategory))) {
                            foreach ($tblAgreementTypeList as $tblAgreementType) {
                                $Item['AgreementType'][$tblAgreementType->getId()] = 'Nein';
                                $Item['AgreementType' . $tblAgreementType->getId()] = 'Nein';
                            }
                        }
                    }
                }

                // befüllen was Gesetzt ist
                if(($tblAgreementList = Agreement::useService()->getPersonAgreementAllByPerson($tblPerson))){
                    foreach($tblAgreementList as $tblAgreement){
                        if(($tblAgreementType = $tblAgreement->getTblPersonAgreementType())){
                            $Item['AgreementType'][$tblAgreementType->getId()] = 'Ja';
                            $Item['AgreementType'.$tblAgreementType->getId()] = 'Ja';
                        }
                    }
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
    public function createAgreementPersonListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $Column = 0;
            $Row = 1;
            $export->setValue($export->getCell($Column++, $Row), "Name, Vorname");
            $export->setValue($export->getCell($Column++, $Row), "Anschrift");
            $export->setValue($export->getCell($Column++, $Row), "Geburtsdatum");

            //Agreement Head
            if(($tblAgreementCategoryAll = Agreement::useService()->getPersonAgreementCategoryAll())){
                foreach($tblAgreementCategoryAll as $tblAgreementCategory){
                    // Header für Ketegorie
                    $export->setValue($export->getCell($Column, $Row - 1), $tblAgreementCategory->getName());
                    if(($tblAgreementTypeList = Agreement::useService()->getPersonAgreementTypeAllByCategory($tblAgreementCategory))){
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
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Name']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['AddressExcel']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Birthday']);

                foreach($PersonData['AgreementType'] as $AgreementTypeContent){
                    $export->setValue($export->getCell($Column++, $Row), $AgreementTypeContent);
                }

                $Row++;
            }
//            $Row++;
//            Person::setGenderFooter($export, $tblPersonList, $Row);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param DateTime $dateTimeFrom
     * @param DateTime|null $dateTimeTo
     * @param null $Type
     * @param string $DivisionName
     * @param int $IsCertificateRelevant
     * @param bool $IsAbsenceOnlineOnly
     *
     * @return FilePointer
     */
    public function createAbsenceListExcel(DateTime $dateTimeFrom, DateTime $dateTimeTo = null, $Type = null, string $DivisionName = '',
        int $IsCertificateRelevant = 0, bool $IsAbsenceOnlineOnly = false): FilePointer
    {
        if ($Type != null) {
            $tblSchoolType = Type::useService()->getTypeById($Type);
        } else {
            $tblSchoolType = false;
        }

        switch ($IsCertificateRelevant) {
            case 1: $IsCertificateRelevant = true; break;
            case 2: $IsCertificateRelevant = false; break;
            default: $IsCertificateRelevant = null;
        }

        $hasAbsenceTypeOptions = false;
        if ($DivisionName != '') {
            $tblYearList = Term::useService()->getYearAllByDate($dateTimeFrom);
            if (($divisionCourseList = DivisionCourse::useService()->getDivisionCourseListByLikeName($DivisionName, $tblYearList ?: null, true))) {
                $absenceList = Absence::useService()->getAbsenceAllByDay(
                    $dateTimeFrom,
                    $dateTimeTo,
                    $tblSchoolType ?: null,
                    $divisionCourseList,
                    $hasAbsenceTypeOptions,
                    $IsCertificateRelevant,
                    $IsAbsenceOnlineOnly
                );
            } else {
                $absenceList = array();
            }
        } else {
            $absenceList = Absence::useService()->getAbsenceAllByDay(
                $dateTimeFrom,
                $dateTimeTo,
                $tblSchoolType ?: null,
                array(),
                $hasAbsenceTypeOptions,
                $IsCertificateRelevant,
                $IsAbsenceOnlineOnly
            );
        }

        return $this->createExcelByAbsenceList($dateTimeFrom, $dateTimeTo, $absenceList, $hasAbsenceTypeOptions);
    }

    /**
     * @param array $absenceList
     * @param bool $hasAbsenceTypeOptions
     * @param DateTime $startDate
     * @param DateTime|null $endDate
     *
     * @return FilePointer
     */
    private function createExcelByAbsenceList(
        DateTime $startDate,
        ?DateTime $endDate,
        array $absenceList,
        bool $hasAbsenceTypeOptions
    ): FilePointer {
        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());

        $export->setValue($export->getCell(0, 0),
            'Fehlzeitenübersicht vom ' . $startDate->format('d.m.Y') . ($endDate ? ' bis ' . $endDate->format('d.m.Y') : '')
        );

        $column = 0;
        $row = 1;
        $export->setValue($export->getCell($column++, $row), "Schulart");
        $export->setValue($export->getCell($column++, $row), "Kurs");
        $export->setValue($export->getCell($column++, $row), "Schüler");
        $export->setValue($export->getCell($column++, $row), "Zeitraum");
        $export->setValue($export->getCell($column++, $row), "Ersteller");
        $export->setValue($export->getCell($column++, $row), "Unterrichtseinheiten");
        if ($hasAbsenceTypeOptions) {
            $export->setValue($export->getCell($column++, $row), "Typ");
        }
        $export->setValue($export->getCell($column++, $row), "ZR");
        $export->setValue($export->getCell($column++, $row), "Status");
        $export->setValue($export->getCell($column, $row), "Bemerkung");

        // header bold
        $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setFontBold();

        $maxColumn = $column;

        $row++;

        if(!empty($absenceList)){
            foreach ($absenceList as $absence) {
                $column = 0;

                $export->setValue($export->getCell($column++, $row), $absence['TypeExcel']);
                $export->setValue($export->getCell($column++, $row), $absence['Division']);
                $export->setValue($export->getCell($column++, $row), $absence['PersonExcel']);
                $export->setValue($export->getCell($column++, $row), $absence['DateSpan']);
                $export->setValue($export->getCell($column++, $row), $absence['PersonCreator']);
                $export->setValue($export->getCell($column++, $row), $absence['Lessons']);
                if ($hasAbsenceTypeOptions) {
                    $export->setValue($export->getCell($column++, $row), $absence['AbsenceTypeExcel']);
                }
                $export->setValue($export->getCell($column++, $row), $absence['IsCertificateRelevant']);
                $export->setValue($export->getCell($column++, $row), $absence['StatusExcel']);
                $export->setValue($export->getCell($column, $row), $absence['Remark']);

                $export->setStyle($export->getCell(0, $row - 1), $export->getCell($maxColumn, $row))->setBorderBottom();

                $row++;
            }
        }

        // Spaltenbreite
        $column = 0;
        $export->setStyle($export->getCell($column, 1), $export->getCell($column++, $row))->setColumnWidth(8);
        $export->setStyle($export->getCell($column, 1), $export->getCell($column++, $row))->setColumnWidth(6);
        $export->setStyle($export->getCell($column, 1), $export->getCell($column++, $row))->setColumnWidth(23);
        $export->setStyle($export->getCell($column, 1), $export->getCell($column++, $row))->setColumnWidth(22);
        $export->setStyle($export->getCell($column, 1), $export->getCell($column++, $row))->setColumnWidth(15);
        $export->setStyle($export->getCell($column, 1), $export->getCell($column++, $row))->setColumnWidth(25);
        if ($hasAbsenceTypeOptions) {
            $export->setStyle($export->getCell($column, 1), $export->getCell($column++, $row))->setColumnWidth(5);
        }
        $export->setStyle($export->getCell($column, 1), $export->getCell($column++, $row))->setColumnWidth(5);
        $export->setStyle($export->getCell($column, 1), $export->getCell($column++, $row))->setColumnWidth(7);
        $export->setStyle($export->getCell($column, 1), $export->getCell($column, $row))->setColumnWidth(
            $hasAbsenceTypeOptions ? 15 : 20
        );

        // Gitterlinien
        $export->setStyle($export->getCell(0, 1), $export->getCell($maxColumn, 1))->setBorderBottom();
        $export->setStyle($export->getCell(0, 1), $export->getCell($maxColumn, $row - 1))->setBorderVertical();
        $export->setStyle($export->getCell(0, 1), $export->getCell($maxColumn, $row - 1))->setBorderOutline();

        $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));
        $export->setPaperSizeParameter(new PaperSizeParameter('A4'));

        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

        return $fileLocation;
    }

    /**
     * @return array
     */
    public function createClubList()
    {

        $tblPersonList = array();
        if($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CLUB)){
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
        }
        $TableContent = array();
        $tblGroupStudent = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
        $tblGroupProspect = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_PROSPECT);
        $tblPersonStudentAll = Group::useService()->getPersonAllByGroup($tblGroupStudent);
        $tblYearList = Term::useService()->getYearByNow();
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$tblPersonStudentAll, $tblYearList, $tblGroupStudent, $tblGroupProspect) {
                $Item['Number'] = '';
                $Item['Title'] = $tblPerson->getTitle();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Year'] = '';
                if(($tblClub = Club::useService()->getClubByPerson($tblPerson))){
                    $Item['Number'] = $tblClub->getIdentifier();
                }
                $tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN);
                if(($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))){
                    foreach($tblToPersonList as $tblToPerson){
                        $tblPersonStudent = $tblToPerson->getServiceTblPersonTo();
                        if ($tblPersonStudentAll && $tblPersonStudent) {
                            $tblPersonStudentAll = array_udiff($tblPersonStudentAll, array($tblPersonStudent),
                                function (TblPerson $ObjectA, TblPerson $ObjectB) {
                                    return $ObjectA->getId() - $ObjectB->getId();
                                }
                            );
                        }
                        $Item['StudentFirstName'] = $tblPersonStudent->getFirstSecondName();
                        $Item['StudentLastName'] = $tblPersonStudent->getLastName();
                        $Item['DivisionCourse'] = '';
                        $Item['Type'] = '';
                        $Item['individualPersonGroup'] = '';
                        if(Group::useService()->getMemberByPersonAndGroup($tblPersonStudent, $tblGroupStudent)) {
                            $Item['Type'] = 'Schüler';
                        }
                        if($Item['Type'] == 'Schüler' && $tblYearList){
                            foreach($tblYearList as $tblYear){
                                if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPersonStudent, $tblYear))){
                                    if(($tblDivisionCourseDivision = $tblStudentEducation->getTblDivision())){
                                        $Item['DivisionCourse'] = $tblDivisionCourseDivision->getDisplayName();
                                    }
                                    if(($tblDivisionCourseDivision = $tblStudentEducation->getTblCoreGroup())){
                                        $Item['DivisionCourse'] = ($Item['DivisionCourse']
                                            ?$Item['DivisionCourse'].', '.$tblDivisionCourseDivision->getDisplayName()
                                            :$tblDivisionCourseDivision->getDisplayName());
                                    }
                                    $Item['Year'] = $tblYear->getYear();
                                }
                            }
                        }
                        $PersonGroupList = array();
                        if(($tblPersonGroupList = Group::useService()->getGroupAllByPerson($tblPersonStudent))){
                            foreach($tblPersonGroupList as $tblPersonGroup){
                                // nur individuelle Personengruppen
                                if(!$tblPersonGroup->getMetaTable()){
                                    $PersonGroupList[] = $tblPersonGroup->getName();
                                }
                            }
                        }
                        if(!empty($PersonGroupList)){
                            $Item['individualPersonGroup'] = implode(', ', $PersonGroupList);
                        }
                        // Jeder Schüler bekommt eigene Spalte (Vereinsmitglied steht mehrmals da)
                        // Schüler/Interessenten sollen auch ohne Klasse abgebildet werden.
                        if($Item['Type'] != 'Schüler'
                        && Group::useService()->getMemberByPersonAndGroup($tblPersonStudent, $tblGroupProspect)){
                            if(($tblProspect = Prospect::useService()->getProspectByPerson($tblPersonStudent))){
                                if(($tblProspectReservation = $tblProspect->getTblProspectReservation())){
                                    $Item['Year'] = $tblProspectReservation->getReservationYear();
                                }
                            } else {
                                $Item['Year'] = '';
                            }
                            $Item['Type'] = 'Interessent';
                        }
                        array_push($TableContent, $Item);
                    }
                }
            });
            // Füght die Schühler ohne Mitglied an.
            if (!empty($tblPersonStudentAll)) {
                array_walk($tblPersonStudentAll, function (TblPerson $tblPersonStudent) use (&$TableContent, $tblYearList) {
                    $Item['Number'] = '';
                    $Item['Title'] = '';
                    $Item['FirstName'] = '';
                    $Item['LastName'] = '';
                    $Item['DivisionCourse'] = '';
                    $Item['Year'] = '';
                    if($tblYearList){
                        foreach($tblYearList as $tblYear){
                            if(($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPersonStudent, $tblYear))){
                                if(($tblDivisionCourseDivision = $tblStudentEducation->getTblDivision())){
                                    $Item['DivisionCourse'] = $tblDivisionCourseDivision->getDisplayName();
                                }
                                if(($tblDivisionCourseDivision = $tblStudentEducation->getTblCoreGroup())){
                                    $Item['DivisionCourse'] = ($Item['DivisionCourse']
                                        ?$Item['DivisionCourse'].', '.$tblDivisionCourseDivision->getDisplayName()
                                        :$tblDivisionCourseDivision->getDisplayName());
                                }
                                $Item['Year'] = $tblYear->getYear();
                            }
                        }
                    }
                    $Item['StudentFirstName'] = $tblPersonStudent->getFirstSecondName();
                    $Item['StudentLastName'] = $tblPersonStudent->getLastName();
                    $Item['Type'] = 'Schüler';
                    $Item['individualPersonGroup'] = '';
                    $PersonGroupList = array();
                    if(($tblPersonGroupList = Group::useService()->getGroupAllByPerson($tblPersonStudent))){
                        foreach($tblPersonGroupList as $tblPersonGroup){
                            // nur individuelle Personengruppen
                            if(!$tblPersonGroup->getMetaTable()){
                                $PersonGroupList[] = $tblPersonGroup->getName();
                            }
                        }
                    }
                    if(!empty($PersonGroupList)){
                        $Item['individualPersonGroup'] = implode(', ', $PersonGroupList);
                    }

                    array_push($TableContent, $Item);
                });
            }
        }

        $Number = array();
        $Name = array();
        foreach ($TableContent as $key => $row) {
            $Number[$key] = $row['Number'];
            $Name[$key] = $row['LastName'];
        }
        array_multisort($Number, SORT_ASC, $Name, SORT_ASC, $TableContent);

        return $TableContent;
    }

    /**
     * @param $PersonList
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createClubListExcel($PersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell(0, 0), "Mitgliedsnummer");
            $export->setValue($export->getCell(1, 0), "Titel");
            $export->setValue($export->getCell(2, 0), "Sorgeberechtigt Name");
            $export->setValue($export->getCell(3, 0), "Sorgeberechtigt Vorname");
            $export->setValue($export->getCell(4, 0), "Schüler / Interessent Name");
            $export->setValue($export->getCell(5, 0), "Schüler / Interessent Vorname");
            $export->setValue($export->getCell(6, 0), "Typ");
            $export->setValue($export->getCell(7, 0), "Schuljahr");
            $export->setValue($export->getCell(8, 0), "Klasse/Stammgruppe");
            $export->setValue($export->getCell(9, 0), "Personengruppen");

            $Row = 1;
            foreach ($PersonList as $PersonData) {

                $export->setValue($export->getCell(0, $Row), $PersonData['Number']);
                $export->setValue($export->getCell(1, $Row), $PersonData['Title']);
                $export->setValue($export->getCell(2, $Row), $PersonData['LastName']);
                $export->setValue($export->getCell(3, $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell(4, $Row), $PersonData['StudentLastName']);
                $export->setValue($export->getCell(5, $Row), $PersonData['StudentFirstName']);
                $export->setValue($export->getCell(6, $Row), $PersonData['Type']);
                $export->setValue($export->getCell(7, $Row), $PersonData['Year']);
                $export->setValue($export->getCell(8, $Row), $PersonData['DivisionCourse']);
                $export->setValue($export->getCell(9, $Row), $PersonData['individualPersonGroup']);
                $Row++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param array $personList
     *
     * @return array
     */
    public function createStudentArchiveList(array $personList): array
    {
        $dataList = array();
        foreach ($personList as $item) {
            /** @var TblPerson $tblPerson */
            $tblPerson = $item['tblPerson'];
            /** @var TblDivisionCourse $tblDivisionCourseDivision */
            $tblDivisionCourseDivision = $item['tblDivisionCourseDivision'];
            /** @var TblDivisionCourse $tblDivisionCourseCoreGroup */
            $tblDivisionCourseCoreGroup = $item['tblDivisionCourseCoreGroup'];
            /** @var TblStudentEducation $tblStudentEducation */
            $tblStudentEducation = $item['tblStudentEducation'];
            // DivisionCourse string
            $divisionCourseList = array();
            if($tblDivisionCourseDivision){
                $divisionCourseList[] = $tblDivisionCourseDivision->getDisplayName();
            }
            if($tblDivisionCourseCoreGroup){
                $divisionCourseList[] = $tblDivisionCourseCoreGroup->getDisplayName();
            }
            $divisionCourseString = implode(', ', $divisionCourseList);
            // school string
            $lastSchool = ($tblCompany = $tblStudentEducation->getServiceTblCompany()) ? $tblCompany->getDisplayName() : '';
            $tblMainAddress = $tblPerson->fetchMainAddress();
            $leaveSchool = '';
            $leaveDate = '';
            if (($tblStudent = $tblPerson->getStudent())
            && ($tblTransferTypeLeave = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE'))
            && ($tblTransferLeave = Student::useService()->getStudentTransferByType($tblStudent, $tblTransferTypeLeave))
            ) {
                $leaveSchool = $tblTransferLeave->getServiceTblCompany() ? $tblTransferLeave->getServiceTblCompany()->getDisplayName() : '';
                $leaveDate = $tblTransferLeave->getTransferDate();
            }
            $custody1Salutation = '';
            $custody1FirstName = '';
            $custody1LastName = '';
            $custody2Salutation = '';
            $custody2FirstName = '';
            $custody2LastName = '';
            if (($tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt'))
            && ($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType))) {
                foreach ($tblToPersonList as $tblToPerson) {
                    if (($tblPersonCustody = $tblToPerson->getServiceTblPersonFrom())) {
                        if ($tblToPerson->getRanking() == 1) {
                            $custody1Salutation = $tblPersonCustody->getSalutation();
                            $custody1FirstName = $tblPersonCustody->getFirstSecondName();
                            $custody1LastName = $tblPersonCustody->getLastName();
                        } elseif ($tblToPerson->getRanking() == 2) {
                            $custody2Salutation = $tblPersonCustody->getSalutation();
                            $custody2FirstName = $tblPersonCustody->getFirstSecondName();
                            $custody2LastName = $tblPersonCustody->getLastName();
                        }
                    }
                }
            }
            $dataList[] = array(
                'LastDivisionCourse'    => $divisionCourseString,
                'LastName'              => $tblPerson->getLastName(),
                'FirstName'             => $tblPerson->getFirstSecondName(),
                'Gender'                => $tblPerson->getGenderString(),
                'Birthday'              => $tblPerson->getBirthday(),
                'Custody1Salutation'    => $custody1Salutation,
                'Custody1FirstName'     => $custody1FirstName,
                'Custody1LastName'      => $custody1LastName,
                'Custody2Salutation'    => $custody2Salutation,
                'Custody2FirstName'     => $custody2FirstName,
                'Custody2LastName'      => $custody2LastName,
                'Street'                => $tblMainAddress ? $tblMainAddress->getStreetName() . ' '. $tblMainAddress->getStreetNumber() : '',
                'ZipCode'               => $tblMainAddress ? $tblMainAddress->getTblCity()->getCode() : '',
                'City'                  => $tblMainAddress ? $tblMainAddress->getTblCity()->getDisplayName() : '',
                'LastSchool'            => $lastSchool,
                'NewSchool'             => $leaveSchool,
                'LeaveDate'             => $leaveDate
            );
        }
        $division = array();
        $lastName = array();
        foreach ($dataList as $key => $row) {
            $division[$key] = $row['LastDivisionCourse'];
            $lastName[$key] = $row['LastName'];
        }
        array_multisort($division, SORT_NATURAL, $lastName, SORT_ASC, $dataList);
        return  $dataList;
    }

    /**
     * @param array $dataList
     *
     * @return bool|FilePointer
     */
    public function createStudentArchiveExcel(array $dataList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = 0;
        $export->setValue($export->getCell($column++, 0), 'Abgangsklasse');
        $export->setValue($export->getCell($column++, 0), 'Name');
        $export->setValue($export->getCell($column++, 0), 'Vorname');
        $export->setValue($export->getCell($column++, 0), 'Geschlecht');
        $export->setValue($export->getCell($column++, 0), 'Geburtsdatum');
        $export->setValue($export->getCell($column++, 0), 'Anrede Sorg1');
        $export->setValue($export->getCell($column++, 0), 'Vorname Sorg1');
        $export->setValue($export->getCell($column++, 0), 'Nachname Sorg1');
        $export->setValue($export->getCell($column++, 0), 'Anrede Sorg2');
        $export->setValue($export->getCell($column++, 0), 'Vorname Sorg2');
        $export->setValue($export->getCell($column++, 0), 'Nachname Sorg2');
        $export->setValue($export->getCell($column++, 0), 'Straße');
        $export->setValue($export->getCell($column++, 0), 'PLZ');
        $export->setValue($export->getCell($column++, 0), 'Ort');
        $export->setValue($export->getCell($column++, 0), 'Abgebende Schule');
        $export->setValue($export->getCell($column++, 0), 'Aufnehmende Schule');
        $export->setValue($export->getCell($column, 0), 'Abmeldedatum');
        $row = 1;
        foreach ($dataList as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['LastDivisionCourse']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Gender']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthday']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody1Salutation']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody1FirstName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody1LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody2Salutation']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody2FirstName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Custody2LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Street']);
            $export->setValue($export->getCell($column++, $row), $PersonData['ZipCode']);
            $export->setValue($export->getCell($column++, $row), $PersonData['City']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastSchool']);
            $export->setValue($export->getCell($column++, $row), $PersonData['NewSchool']);
            $export->setValue($export->getCell($column, $row), $PersonData['LeaveDate']);
            $row++;
        }
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param TblPerson $tblPerson
     * @param array     $item
     *
     * @return array
     */
    public function getAddressDataFromPerson(TblPerson $tblPerson, array $item): array
    {

        if (($tblAddress = Address::useService()->getAddressByPerson($tblPerson))) {
            $item['StreetName'] = $tblAddress->getStreetName();
            $item['StreetNumber'] = $tblAddress->getStreetNumber();
            $item['Code'] = $tblAddress->getCodeString();
            $item['City'] = $tblAddress->getCityString();
            $item['District'] = $tblAddress->getDistrictString();
            // show in DataTable
            $item['Address'] = $tblAddress->getGuiString();
        }
        return $item;
    }

    /**
     * @param TblPerson $tblPerson
     * @param array $item
     * @return array
     */
    public function getContactDataFromPerson(TblPerson $tblPerson, array $item): array
    {
        $item['PhoneFixed'] = '';
        $item['Phone'] = '';
        $item['ExcelPhone'] = '';
        $item['Mail'] = '';
        $item['ExcelMail'] = '';
        $item['ExcelMailPrivate'] = '';
        $item['ExcelMailBusiness'] = '';
        $item['MailFrontendListFixed'] = '';

        //Phone
        $tblPhoneList = array();
        $tblPhoneListFixed = array();
        $tblToPersonPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
        if ($tblToPersonPhoneList) {
            $key = 'Sort_1_' . $tblPerson->getId();
            foreach ($tblToPersonPhoneList as $tblToPersonPhone) {
                $tblPhone = $tblToPersonPhone->getTblPhone();
                if ($tblPhone) {
                    if (isset($tblPhoneList[$key])) {
                        $tblPhoneList[$key] = $tblPhoneList[$key] . ', '
                            . $tblPhone->getNumber() . ' ' . Phone::useService()->getPhoneTypeShort($tblToPersonPhone);
                    } else {
                        $tblPhoneList[$key] = $tblPerson->getFirstName() . ' ' . $tblPerson->getLastName() . ' ('
                            . $tblPhone->getNumber() . ' ' . Phone::useService()->getPhoneTypeShort($tblToPersonPhone);
                    }
                }
            }
            if (isset($tblPhoneList[$key])) {
                $tblPhoneList[$key] = $tblPhoneList[$key] . ')';
            }
            if (isset($tblPhoneListFixed[$key])) {
                $tblPhoneList[$key] = $tblPhoneList[$key] . ')';
            }
        }

        //Mail
        $tblMailList = array();
        $tblMailFrontendList = array();
        $tblMailFrontendListFixed = array();
        $mailBusinessList = array();
        $mailPrivateList = array();
        $tblToPersonMailList = Mail::useService()->getMailAllByPerson($tblPerson);
        if ($tblToPersonMailList) {
            $key = 'Sort_1_' . $tblPerson->getId();
            foreach ($tblToPersonMailList as $tblToPersonMail) {
                $tblMail = $tblToPersonMail->getTblMail();
                if ($tblMail) {
                    if (isset($tblMailList[$key])) {
                        $preString = ', ';
                        $tblMailList[$key] = $tblMailList[$key] . $preString . $tblMail->getAddress();
                        $tblMailFrontendList[$key] = $tblMailFrontendList[$key] . $preString .
                            new Mailto($tblMail->getAddress(), $tblMail->getAddress());
                        $preString = ',&nbsp;';
                        $tblMailFrontendListFixed[$key] = $tblMailFrontendListFixed[$key] . $preString .
                            new Mailto($tblMail->getAddress(), $tblMail->getAddress());
                    } else {
                        $preString = $tblPerson->getFirstName() . ' ' . $tblPerson->getLastName() . ' (';
                        $tblMailList[$key] = $preString . $tblMail->getAddress();
                        $tblMailFrontendList[$key] = $preString .
                            new Mailto($tblMail->getAddress(), $tblMail->getAddress());
                        $preString = $tblPerson->getFirstName() . '&nbsp;' . $tblPerson->getLastName() . '</br>';
                        $tblMailFrontendListFixed[$key] = $preString .
                            new Mailto($tblMail->getAddress(), $tblMail->getAddress());
                    }

                    // für die Excel-Trennung der  Emailadressen nach Type
                    if ($tblToPersonMail->getTblType()->getName() == 'Privat') {
                        $mailPrivateList[$key . '_' . $tblMail->getId()] = $tblMail->getAddress();
                    } else {
                        $mailBusinessList[$key . '_' . $tblMail->getId()] = $tblMail->getAddress();
                    }
                }
            }
            if (isset($tblMailList[$key])) {
                $tblMailList[$key] = $tblMailList[$key] . ')';
                $tblMailFrontendList[$key] = $tblMailFrontendList[$key] . ')';
            }
        }

        $tblToPersonGuardianList = array();
        $tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN);
        if ($tblType
            && ($GuardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))
        ) {
            $tblToPersonGuardianList = $GuardianList;
        }
        $tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_AUTHORIZED);
        if ($tblType
            && ($AuthorizedList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))
        ) {
            $tblToPersonGuardianList = array_merge($tblToPersonGuardianList, $AuthorizedList);
        }
        $tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN_SHIP);
        if ($tblType
            && ($GuardianShipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))
        ) {
            $tblToPersonGuardianList = array_merge($tblToPersonGuardianList, $GuardianShipList);
        }
        $tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_EMERGENCY_CONTACT);
        if ($tblType
            && ($EmergencyList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblType))
        ) {
            $tblToPersonGuardianList = array_merge($tblToPersonGuardianList, $EmergencyList);
        }
        if (!empty($tblToPersonGuardianList)) {
            foreach ($tblToPersonGuardianList as $tblToPersonGuardian) {
                if (($tblPersonGuardian = $tblToPersonGuardian->getServiceTblPersonFrom())) {
                    // Für die Sortierung der Telefonnummern und Mailadressen
                    // Schüler zuerst, dann Mutter, dann Vater, dann Bevollmächtigter
                    $genderString = $tblPersonGuardian->getGenderString();
                    if ($genderString == 'Weiblich') {
                        $isFemale = true;
                    } elseif ($genderString == 'Männlich') {
                        $isFemale = false;
                    } else {
                        if ($tblPersonGuardian->getSalutation() == 'Frau') {
                            $isFemale = true;
                        } elseif ($tblPersonGuardian->getSalutation() == 'Herr') {
                            $isFemale = false;
                        } else {
                            $isFemale = false;
                        }
                    }

                    $pre = '';
                    if ($tblToPersonGuardian->getTblType()->getName() == TblType::IDENTIFIER_AUTHORIZED) {
                        $pre = 'Bev. ';

                        if ($isFemale) {
                            $key = 'Sort_4_' . $tblPersonGuardian->getId();
                        } else {
                            $key = 'Sort_5_' . $tblPersonGuardian->getId();
                        }
                    } elseif ($tblToPersonGuardian->getTblType()->getName() == TblType::IDENTIFIER_GUARDIAN_SHIP) {
                        $pre = 'Vorm. ';
                        if ($isFemale) {
                            $key = 'Sort_6_' . $tblPersonGuardian->getId();
                        } else {
                            $key = 'Sort_7_' . $tblPersonGuardian->getId();
                        }
                    } elseif ($tblToPersonGuardian->getTblType()->getName() == TblType::IDENTIFIER_EMERGENCY_CONTACT) {
                        $pre = 'NK ';
                        if ($isFemale) {
                            $key = 'Sort_8_' . $tblPersonGuardian->getId();
                        } else {
                            $key = 'Sort_9_' . $tblPersonGuardian->getId();
                        }
                    } else {
                        if ($isFemale) {
                            $key = 'Sort_2_' . $tblPersonGuardian->getId();
                        } else {
                            $key = 'Sort_3_' . $tblPersonGuardian->getId();
                        }
                    }

                    $tblPhoneList[$key] = $pre . $tblPersonGuardian->getFirstName() . ' ' .
                        $tblPersonGuardian->getLastName();
                    //Phone Guardian
                    $tblToPersonPhoneList = Phone::useService()->getPhoneAllByPerson($tblPersonGuardian);
                    if ($tblToPersonPhoneList) {
                        $FirstNumber = true;
                        foreach ($tblToPersonPhoneList as $tblToPersonPhone) {
                            $tblPhone = $tblToPersonPhone->getTblPhone();
                            if ($tblPhone) {
                                if (!$FirstNumber) {
                                    $tblPhoneList[$key] = $tblPhoneList[$key] . ', '
                                        . $tblPhone->getNumber() . ' ' . Phone::useService()->getPhoneTypeShort($tblToPersonPhone);
                                } else {
                                    $tblPhoneList[$key] = $tblPhoneList[$key] . ' (' . $tblPhone->getNumber() . ' '
                                        . Phone::useService()->getPhoneTypeShort($tblToPersonPhone);
                                    $FirstNumber = false;
                                }
                            }
                        }
                        if (isset($tblPhoneList[$key])) {
                            $tblPhoneList[$key] = $tblPhoneList[$key] . ')';
                        }
                        if (isset($tblPhoneListFixed[$key])) {
                            $tblPhoneListFixed[$key] = $tblPhoneListFixed[$key] . ')';
                        }
                    }

                    //Mail Guardian
                    $tblToPersonMailList = Mail::useService()->getMailAllByPerson($tblPersonGuardian);
                    if ($tblToPersonMailList) {
                        foreach ($tblToPersonMailList as $tblToPersonMail) {
                            $tblMail = $tblToPersonMail->getTblMail();
                            if ($tblMail) {
                                if (isset($tblMailList[$key])) {
                                    $preString = ', ';

                                    $tblMailList[$key] = $tblMailList[$key] . $preString . $tblMail->getAddress();
                                    $tblMailFrontendList[$key] = $tblMailFrontendList[$key] . $preString .
                                        new Mailto($tblMail->getAddress(), $tblMail->getAddress());
                                    $preString = '&nbsp;';
                                    $tblMailFrontendListFixed[$key] = $tblMailFrontendListFixed[$key] . $preString .
                                        new Mailto($tblMail->getAddress(), $tblMail->getAddress());
                                } else {
                                    $preString = $pre . $tblPersonGuardian->getFirstName() . ' ' .
                                        $tblPersonGuardian->getLastName() . ' (';
                                    $tblMailList[$key] = $preString . $tblMail->getAddress();
                                    $tblMailFrontendList[$key] = $preString . new Mailto($tblMail->getAddress(),
                                            $tblMail->getAddress());
                                    $preString = $pre . $tblPersonGuardian->getFirstName() . '&nbsp;' .
                                        $tblPersonGuardian->getLastName() . '<br/>';
                                    $tblMailFrontendListFixed[$key] = $preString . new Mailto($tblMail->getAddress(),
                                            $tblMail->getAddress());
                                }

                                // für die Excel-Trennung der  Emailadressen nach Type
                                if ($tblToPersonMail->getTblType() == 'Privat') {
                                    $mailPrivateList[$key . '_' . $tblMail->getId()] = $tblMail->getAddress();
                                } else {
                                    $mailBusinessList[$key . '_' . $tblMail->getId()] = $tblMail->getAddress();
                                }
                            }
                        }
                        if (isset($tblMailList[$key])) {
                            $tblMailList[$key] = $tblMailList[$key] . ')';
                            $tblMailFrontendList[$key] = $tblMailFrontendList[$key] . ')';
                        }
                    }
                }
            }
        }
        // Insert PhoneList
        if (!empty($tblPhoneList)) {
            ksort($tblPhoneList);
            $item['Phone'] = $item['Phone'] . implode('<br>', $tblPhoneList);
            $item['PhoneFixed'] = str_replace(' ', '&nbsp', $item['Phone']);
            $item['ExcelPhone'] = $tblPhoneList;
        }
        if (!empty($tblPhoneListFixed)) {
            ksort($tblPhoneListFixed);
            $item['PhoneFixed'] = $item['PhoneFixed'] . implode('<br>', $tblPhoneListFixed);
        }
        #var_dump($tblMailFrontendListFixed);
        // Insert MailList
        if (!empty($tblMailList)) {
            ksort($tblMailList);
            $item['ExcelMail'] = $tblMailList;
            $item['Mail'] = $item['Mail'] . implode('<br>', $tblMailFrontendList);

            if (!empty($tblMailList)) {
                ksort($tblMailList);
                $item['ExcelMail'] = $tblMailList;
                $item['MailFrontendListFixed'] = $item['MailFrontendListFixed'] . implode('<br>', $tblMailFrontendListFixed);
            }
            if (!empty($mailPrivateList)) {
                ksort($mailPrivateList);
                $item['ExcelMailPrivate'] = implode('; ', $mailPrivateList);
            }
            if (!empty($mailBusinessList)) {
                ksort($mailBusinessList);
                $item['ExcelMailBusiness'] = implode('; ', $mailBusinessList);
            }
        }
        return $item;
    }
    /**
     * @param $tblPersonList
     *
     * @return array
     */
    public function createAbsenceContentList(array $tblPersonList, TblYear $tblYear): array
    {
        $dataList = array();
        $count = 0;
        /** @var TblPerson $tblPerson */
        foreach ($tblPersonList as $tblPerson) {
            $tblCompany = false;
            $tblSchoolType = false;
            $tblCourse = false;
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                $tblCompany = $tblStudentEducation->getServiceTblCompany();
                $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                $tblCourse = $tblStudentEducation->getServiceTblCourse();
            }

            list($startDateAbsence, $tillDateAbsence) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);

            // Fehlzeiten
            $excusedLessons = 0;
            $unexcusedLessons = 0;
            $excusedDays = 0;
            $unexcusedDays = 0;
            if ($startDateAbsence && $tillDateAbsence) {
                $excusedDays = Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null,
                    $startDateAbsence, $tillDateAbsence, $excusedLessons);
                $unexcusedDays = Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null,
                    $startDateAbsence, $tillDateAbsence, $unexcusedLessons);
            }

            $dataList[] = array(
                'Number'           => ++$count,
                'LastName'         => $tblPerson->getLastName(),
                'FirstName'        => $tblPerson->getFirstName(),
                'Birthday'         => $tblPerson->getBirthday(),
                'Course'           => $tblCourse ? $tblCourse->getName() : '',
                'ExcusedDays'      => $excusedDays,
                'unExcusedDays'    => $unexcusedDays,
                'ExcusedLessons'   => $excusedLessons,
                'unExcusedLessons' => $unexcusedLessons
            );
        }

        return $dataList;
    }

    /**
     * @param array $dataList
     *
     * @return bool|FilePointer
     */
    public function createAbsenceContentExcel(array $PersonList):?FilePointer
    {
        if (!empty($PersonList)) {
            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $column = 0;
            $export->setValue($export->getCell($column++, 1), '#');
            $export->setValue($export->getCell($column++, 1), 'Name');
            $export->setValue($export->getCell($column++, 1), 'Vorname');
//            $export->setValue($export->getCell($column++, 1), 'Adresse');
            $export->setValue($export->getCell($column++, 1), 'Geburtsdatum');
            $export->setValue($export->getCell($column++, 1), 'Bildungsgang');
            $export->setValue($export->getCell($column, 0), 'Fehlzeiten Tage');
            $AbsenceDays = $column;
            $export->setValue($export->getCell($column++, 1), 'Entschuldigte');
            $export->setValue($export->getCell($column++, 1), 'Unentschuldigte');
            $AbsenceUE = $column;
            $export->setValue($export->getCell($column, 0), 'Fehlzeiten Unterrichtseinheiten');
            $export->setValue($export->getCell($column++, 1), 'Entschuldigte');
            $export->setValue($export->getCell($column, 1), 'Unentschuldigte');

            $row = 2;
            foreach ($PersonList as $PersonData) {
                $column = 0;
                $export->setValue($export->getCell($column++, $row), $PersonData['Number']);
                $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
                $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
//                $export->setValue($export->getCell($column++, $row), $PersonData['Address']);
                $export->setValue($export->getCell($column++, $row), $PersonData['Birthday']);
                $export->setValue($export->getCell($column++, $row), $PersonData['Course']);
                $export->setValue($export->getCell($column++, $row), $PersonData['ExcusedDays']);
                $export->setValue($export->getCell($column++, $row), $PersonData['unExcusedDays']);
                $export->setValue($export->getCell($column++, $row), $PersonData['ExcusedLessons']);
                $export->setValue($export->getCell($column, $row), $PersonData['unExcusedLessons']);
                $row++;
            }
            $column = 0;

            // A4 Querformat|landscape
            $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));
            // header with merged cells
            $export->setStyle($export->getCell($AbsenceDays++, 0), $export->getCell($AbsenceDays, 0))->mergeCells();
            $export->setStyle($export->getCell($AbsenceUE++, 0), $export->getCell($AbsenceUE, 0))->mergeCells();
            // with and type of cells
            $export->setStyle($export->getCell($column, 2), $export->getCell($column++, $row))->setColumnWidth(5)->setCellType(\PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $export->setStyle($export->getCell($column, 2), $export->getCell($column++, $row))->setColumnWidth(13);
            $export->setStyle($export->getCell($column, 2), $export->getCell($column++, $row))->setColumnWidth(13);
//            $export->setStyle($export->getCell($column, 2), $export->getCell($column++, $row))->setColumnWidth(30);
            $export->setStyle($export->getCell($column, 2), $export->getCell($column++, $row))->setColumnWidth(13);
            $export->setStyle($export->getCell($column, 2), $export->getCell($column++, $row))->setColumnWidth(13);
            $export->setStyle($export->getCell($column, 2), $export->getCell($column++, $row))->setColumnWidth(14)->setCellType(\PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $export->setStyle($export->getCell($column, 2), $export->getCell($column++, $row))->setColumnWidth(15)->setCellType(\PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $export->setStyle($export->getCell($column, 2), $export->getCell($column++, $row))->setColumnWidth(14)->setCellType(\PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $export->setStyle($export->getCell($column, 2), $export->getCell($column, $row))->setColumnWidth(15)->setCellType(\PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param array $tblPersonList
     * @param array $dataList
     * @param array $countList
     * @param TblYear $tblYear
     *
     * @return bool|FilePointer
     */
    public function createAbsenceContentExcelMonthly(array $tblPersonList, array $dataList, array $countList, TblYear $tblYear): ?FilePointer
    {
        $totalCountList = array();
        if (!empty($tblPersonList)) {
            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $workSheetsName = [
                '1' => 'Januar',
                '2' => 'Februar',
                '3' => 'März',
                '4' => 'April',
                '5' => 'Mai',
                '6' => 'Juni',
                '7' => 'Juli',
                '8' => 'August',
                '9' => 'September',
                '10' => 'Oktober',
                '11' => 'November',
                '12' => 'Dezember'
            ];
            $IsFirstTab = true;
            /** @var DateTime $startDate */
            list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            if ($startDate && $endDate) {
                while ($startDate < $endDate) {
                    $month = intval($startDate->format('m'));
                    $startDate->add(new \DateInterval('P1M'));
                    if ($IsFirstTab === true) {
                        $export->renameWorksheet($workSheetsName[(string)$month]);
                        $IsFirstTab = false;
                    } else {
                        $export->createWorksheet($workSheetsName[(string)$month]);
                    }
                    // Header
                    $row = $column = 0;
                    $export->setValue($export->getCell($column, $row), 'Schüler');
                    $export->setStyle($export->getCell($column, $row), $export->getCell($column++, 2))->mergeCells()->setBorderAll()->setFontBold();
                    for ($i = 1; $i <= 31; $i++) {
                        $export->setValue($export->getCell($column, $row), $i);
                        $export->setStyle($export->getCell($column, $row), $export->getCell($column++, 2))->mergeCells()->setBorderAll()->setFontBold()
                            ->setAlignmentCenter();
                    }
                    for($i = 0; $i <= 4; $i += 4){
                        $column += $i;
                        if($i == 0){
                            $export->setValue($export->getCell($column, 0), 'Fehlzeiten');
                        } elseif($i == 4) {
                            $export->setValue($export->getCell($column, 0), 'Ges. Fz');
                        }
                        $export->setStyle($export->getCell($column, 0), $export->getCell($column + 3, 0))->mergeCells()->setBorderTop()->setBorderRight()
                            ->setAlignmentCenter();
                        $export->setValue($export->getCell($column, 1), 'Tage');
                        $export->setStyle($export->getCell($column, 1), $export->getCell($column + 1, 1))->mergeCells()->setBorderRight()->setAlignmentCenter();
                        $export->setValue($export->getCell($column + 2, 1), 'Std');
                        $export->setStyle($export->getCell($column + 2, 1), $export->getCell($column + 3, 1))->mergeCells()->setBorderRight()
                            ->setAlignmentCenter();
                        for($j = 0; $j < 4; $j++){
                            $export->setStyle($export->getCell($column + $j, 2))->setBorderRight()->setAlignmentCenter();
                        }
                    }
                    $column -= 4;
                    // Content
                    $columnStudents = 0;
                    $rowStudents = 3;
                    /** @var TblPerson $tblPerson */
                    foreach ($tblPersonList as $tblPerson) {
                        $lastName = $tblPerson->getLastName();
                        $firstName = $tblPerson->getFirstSecondName();
                        $export->setValue($export->getCell($columnStudents, $rowStudents), $lastName . ', ' . $firstName);
                        $export->setStyle($export->getCell($columnStudents, $rowStudents))->setBorderAll();

                        if (isset($dataList[$month][$tblPerson->getId()])) {
                            foreach ($dataList[$month][$tblPerson->getId()] as $day => $status) {
                                $export->setValue($export->getCell($day, $rowStudents), $status);
                            }
                        }
                        $export->setValue($export->getCell(32, $rowStudents), $countList[$month][$tblPerson->getId()]['Days']['E'] ?? 0);
                        $export->setValue($export->getCell(33, $rowStudents), $countList[$month][$tblPerson->getId()]['Days']['U'] ?? 0);
                        $export->setValue($export->getCell(34, $rowStudents), $countList[$month][$tblPerson->getId()]['Lessons']['E'] ?? 0);
                        $export->setValue($export->getCell(35, $rowStudents), $countList[$month][$tblPerson->getId()]['Lessons']['U'] ?? 0);

                        if (isset($totalCountList[$tblPerson->getId()]['Days']['E'])) {
                            $totalCountList[$tblPerson->getId()]['Days']['E'] += $countList[$month][$tblPerson->getId()]['Days']['E'] ?? 0;
                        } else {
                            $totalCountList[$tblPerson->getId()]['Days']['E'] = $countList[$month][$tblPerson->getId()]['Days']['E'] ?? 0;
                        }
                        $export->setValue($export->getCell(36, $rowStudents), $totalCountList[$tblPerson->getId()]['Days']['E']);

                        if (isset($totalCountList[$tblPerson->getId()]['Days']['U'])) {
                            $totalCountList[$tblPerson->getId()]['Days']['U'] += $countList[$month][$tblPerson->getId()]['Days']['U'] ?? 0;
                        } else {
                            $totalCountList[$tblPerson->getId()]['Days']['U'] = $countList[$month][$tblPerson->getId()]['Days']['U'] ?? 0;
                        }
                        $export->setValue($export->getCell(37, $rowStudents), $totalCountList[$tblPerson->getId()]['Days']['U']);

                        if (isset($totalCountList[$tblPerson->getId()]['Lessons']['E'])) {
                            $totalCountList[$tblPerson->getId()]['Lessons']['E'] += $countList[$month][$tblPerson->getId()]['Lessons']['E'] ?? 0;
                        } else {
                            $totalCountList[$tblPerson->getId()]['Lessons']['E'] = $countList[$month][$tblPerson->getId()]['Lessons']['E'] ?? 0;
                        }
                        $export->setValue($export->getCell(38, $rowStudents), $totalCountList[$tblPerson->getId()]['Lessons']['E']);

                        if (isset($totalCountList[$tblPerson->getId()]['Lessons']['U'])) {
                            $totalCountList[$tblPerson->getId()]['Lessons']['U'] += $countList[$month][$tblPerson->getId()]['Lessons']['U'] ?? 0;
                        } else {
                            $totalCountList[$tblPerson->getId()]['Lessons']['U'] = $countList[$month][$tblPerson->getId()]['Lessons']['U'] ?? 0;
                        }
                        $export->setValue($export->getCell(39, $rowStudents), $totalCountList[$tblPerson->getId()]['Lessons']['U']);

                        for ($columnCount = 1; $columnCount < 40; $columnCount++) {
                            $columnLetter = \PHPExcel_Cell::stringFromColumnIndex($columnCount);
                            $export->getActiveSheet()->getStyle($columnLetter . $rowStudents)->getAlignment()
                                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            $export->setStyle($export->getCell($columnCount, $rowStudents), $export->getCell($columnCount, $rowStudents))
                                ->setBorderOutline();
                        }
                        $rowStudents++;
                    }
                    // Center Data
                    for ($maxColumn = 1; $maxColumn < 40; $maxColumn++) {
                        $columnLetter = \PHPExcel_Cell::stringFromColumnIndex($maxColumn);
                        $export->getActiveSheet()->getStyle($columnLetter . 3)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $export->getActiveSheet()->getStyle($columnLetter . $rowStudents)->getAlignment()
                            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $export->setStyle($export->getCell($maxColumn, 0))->setColumnWidth(2.6);
                        $export->setStyle($export->getCell($maxColumn, $row), $export->getCell($maxColumn, $row));
                    }
                    $columnOffset = $column;
                    for ($day = 1; $day <= 8; $day++) {
                        $value = ($day % 2 == 1) ? 'E' : 'U';
                        $cellCoord = $export->getCell($columnOffset++, 2);
                        $export->setValue($cellCoord, $value);
                    }
                    // width of cells
                    $export->setStyle($export->getCell(0, 0))->setColumnWidth(21);
                }
            }
            $Month = (int)$startDate->format('m');
            $nowMonth = (int)(new DateTime())->format('m');
            if($nowMonth < $Month){
                $nowMonth += 12;
            }
            $DiffMonth = $nowMonth - $Month;
            $export->selectWorksheetByIndex($DiffMonth);
            $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @return array[]
     */
    public function createDivisionTeacherList(): array
    {
        $TableContent = array();
        $maxCountTeacher = 0;
        $maxCountCustody = 0;
        $maxCountRepresentative = 0;
        if (($tblYearList = Term::useService()->getYearByNow())) {
            foreach ($tblYearList as $tblYear) {
                if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByYear($tblYear, true))) {
                    $tblDivisionCourseList = $this->getSorter($tblDivisionCourseList)->sortObjectBy('DisplayName', new StringNaturalOrderSorter());
                    /** @var TblDivisionCourse $tblDivisionCourse */
                    foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                        $item = array();
                        $item['DivisionCourse'] = $tblDivisionCourse->getDisplayName();
                        $TeacherColumn = 0;
                        if (($tblPersonTeacherList = DivisionCourse::useService()->getDivisionCourseMemberListBy(
                            $tblDivisionCourse, TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))){
                            foreach($tblPersonTeacherList as $tblPersonTeacher) {
                                $TeacherColumn++;
                                $item['DivisionCourseTeacher'.$TeacherColumn.'FirstName'] = $tblPersonTeacher->getFirstName();
                                $item['DivisionCourseTeacher'.$TeacherColumn.'Name'] = $tblPersonTeacher->getLastName();
                            }
                            if ($TeacherColumn > $maxCountTeacher){
                                $maxCountTeacher = $TeacherColumn;
                            }
                        }
                        $CustodyColumn = 0;
                        if (($tblPersonCustodyList = DivisionCourse::useService()->getDivisionCourseMemberListBy(
                            $tblDivisionCourse, TblDivisionCourseMemberType::TYPE_CUSTODY))){
                            foreach($tblPersonCustodyList as $tblPersonCustody) {
                                $CustodyColumn++;
                                $item['DivisionCourseCustody'.$CustodyColumn.'FirstName'] = $tblPersonCustody->getFirstName();
                                $item['DivisionCourseCustody'.$CustodyColumn.'Name'] = $tblPersonCustody->getLastName();
                            }
                            if ($CustodyColumn > $maxCountCustody){
                                $maxCountCustody = $CustodyColumn;
                            }
                        }
                        $RepresentativeColumn = 0;
                        if (($tblPersonRepresentativeList = DivisionCourse::useService()->getDivisionCourseMemberListBy(
                            $tblDivisionCourse, TblDivisionCourseMemberType::TYPE_REPRESENTATIVE))){
                            foreach($tblPersonRepresentativeList as $tblPersonRepresentative) {
                                $RepresentativeColumn++;
                                $item['DivisionCourseRepresentative'.$RepresentativeColumn.'FirstName'] = $tblPersonRepresentative->getFirstName();
                                $item['DivisionCourseRepresentative'.$RepresentativeColumn.'Name'] = $tblPersonRepresentative->getLastName();
                            }
                            if ($RepresentativeColumn > $maxCountRepresentative){
                                $maxCountRepresentative = $RepresentativeColumn;
                            }
                        }
                        array_push($TableContent, $item);
                    }
                }
            }
        }

        $headers['DivisionCourse'] = 'Klasse';
        for ($i = 1; $i <= $maxCountTeacher; $i++){
            $headers['DivisionCourseTeacher'.$i.'FirstName'] = 'Klassenlehrer&nbsp;'.$i.' - Vorname';
            $headers['DivisionCourseTeacher'.$i.'Name'] = 'Klassenlehrer&nbsp;'.$i.' - Nachname';
        }
        for ($l = 1; $l <= $maxCountRepresentative; $l++){
            $headers['DivisionCourseRepresentative'.$l.'FirstName'] = 'Klassensprecher&nbsp;'.$l.' - Vorname';
            $headers['DivisionCourseRepresentative'.$l.'Name'] = 'Klassensprecher&nbsp;'.$l.' Nachname';
        }
        for ($j = 1; $j <= $maxCountCustody; $j++){
            $headers['DivisionCourseCustody'.$j.'FirstName'] = 'Elternvertreter&nbsp;'.$j.' - Vorname';
            $headers['DivisionCourseCustody'.$j.'Name'] = 'Elternvertreter&nbsp;'.$j.' - Nachname';
        }
        foreach($TableContent as &$contentItem) {
            foreach ($headers as $key => $header) {
                if (!isset($contentItem[$key])) {
                    $contentItem[$key] = ' ';
                }
            }
        }
        return array($TableContent, $headers);
    }

    /**
     * @param array $content
     * @param array $headers
     *
     * @return false|FilePointer
     */
    public function createDivisionTeacherExcelList(array $content, array $headers)
    {
        if (!empty($content)) {
            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $row = 0;
            $column = 0;
            foreach ($headers as $header) {
                $export->setValue($export->getCell($column++, $row), str_replace('&nbsp;', ' ', $header));
            }
            $export->setStyle($export->getCell(0, $row), $export->getCell($column, $row))->setFontBold();
            foreach ($content as $item) {
                $row++;
                $column = 0;
                foreach ($headers as $key => $header) {
                    if (isset($item[$key])) {
                        $export->setValue($export->getCell($column, $row), $item[$key]);
                    }
                    $column++;
                }
            }
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
            return $fileLocation;
        }
        return false;
    }
}
