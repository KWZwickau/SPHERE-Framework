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
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Agreement\Agreement;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\People\Search\Group\Group;
use SPHERE\Common\Frontend\Link\Repository\Mailto;
use SPHERE\Common\Frontend\Text\Repository\Code;
use SPHERE\System\Database\Filter\Link\Pile;
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
     * @param $tblPersonList
     *
     * @return array
     */
    public function createClassList($tblPersonList)
    {
        $TableContent = array();
        if ($tblPersonList) {

            $count = 1;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {
                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }

                $Item['Number'] = $count++;
                $Item['Salutation'] = $tblPerson->getSalutation();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Gender'] = '';

                $Item['ForeignLanguage1'] = '';
                $Item['ForeignLanguage2'] = '';
                $Item['ForeignLanguage3'] = '';
                $Item['Profile'] = '';
                $Item['Religion'] = '';
                $Item['Orientation'] = '';
                $Item['Elective'] = '';
                $Item['ExcelElective'] = array();

                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                } else {
                    $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                    $Item['Address'] = '';
                }

                //Gender
                if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))
                && ($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
                && ($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                    $Item['Gender'] = $tblCommonGender->getName();
                }

                $Item = $this->getContactDataFromPerson($tblPerson, $Item);

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Denomination'] = $common->getTblCommonInformation()->getDenomination();
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                } else {
                    $Item['Denomination'] = $Item['Birthday'] = $Item['Birthplace'] = '';
                }

                $tblMainDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson);
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                // NK/Profil
                if ($tblStudent) {
                    for ($i = 1; $i <= 3; $i++) {
                        $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                        $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                        $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking(
                            $tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking);

                        if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject()) && $tblMainDivision
                            && ($tblDivisionLevel = $tblMainDivision->getTblLevel())) {
                            $Item['ForeignLanguage'. $i] = $tblSubject->getAcronym();

                            if (($tblLevelFrom = $tblStudentSubject->getServiceTblLevelFrom())
                                && ($LevelFrom = Division::useService()->getLevelById($tblLevelFrom->getId())->getName())
                                && (is_numeric($LevelFrom)) && (is_numeric($tblDivisionLevel->getName()))) {
                                if ($tblDivisionLevel->getName() < $LevelFrom) {
                                    $Item['ForeignLanguage' . $i] = '';
                                }
                            }
                            if (($tblLevelTill = $tblStudentSubject->getServiceTblLevelTill()) &&
                                ($LevelTill = Division::useService()->getLevelById($tblLevelTill->getId())->getName())
                                && (is_numeric($LevelTill)) && (is_numeric($tblDivisionLevel->getName()))) {
                                if ($tblDivisionLevel->getName() > $LevelTill) {
                                    $Item['ForeignLanguage' . $i] = '';
                                }
                            }
                        }
                    }

                    // Profil
                    $tblStudentProfile = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE')
                    );
                    if ($tblStudentProfile && ($tblSubject = $tblStudentProfile[0]->getServiceTblSubject())) {
                        $Item['Profile'] = $tblSubject->getAcronym();
                    }
                    // Neigungskurs
                    $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION')
                    );
                    if ($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                        $Item['Orientation'] = $tblSubject->getAcronym();
                        $Item['OrientationId'] = $tblSubject->getId();
                    }
                    // Religion
                    $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION')
                    );
                    if ($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                        $Item['Religion'] = $tblSubject->getAcronym();
                        $Item['ReligionId'] = $tblSubject->getId();
                    }

//                    // Bildungsgang
//                    $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
//                    if ($tblTransferType) {
//                        $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
//                            $tblTransferType);
//                        if ($tblStudentTransfer) {
//                            $tblCourse = $tblStudentTransfer->getServiceTblCourse();
//                            if ($tblCourse) {
//                                if ($tblCourse->getName() == 'Gymnasium') {
//                                    $Item['Education'] = 'GY';
//                                } elseif ($tblCourse->getName() == 'Hauptschule') {
//                                    $Item['Education'] = 'HS';
//                                } elseif ($tblCourse->getName() == 'Realschule') {
//                                    $Item['Education'] = 'RS';
//                                } else {
//                                    $Item['Education'] = $tblCourse->getName();
//                                }
//                            }
//                        }
//                    }

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
//                                    if(($tblSubject = $tblStudentElective->getServiceTblSubject())){
//
//                                        switch($tblSubjectRanking->getIdentifier()) {
//                                            case 1:
//                                                $Item['Elective1'] = $tblSubject->getAcronym();
//                                                $Item['Elective1Id'] = $tblSubject->getId();
//                                                break;
//                                            case 2:
//                                                $Item['Elective2'] = $tblSubject->getAcronym();
//                                                $Item['Elective2Id'] = $tblSubject->getId();
//                                                break;
//                                            case 3:
//                                                $Item['Elective3'] = $tblSubject->getAcronym();
//                                                $Item['Elective3Id'] = $tblSubject->getId();
//                                                break;
//                                            case 4:
//                                                $Item['Elective4'] = $tblSubject->getAcronym();
//                                                $Item['Elective4Id'] = $tblSubject->getId();
//                                                break;
//                                            case 5:
//                                                $Item['Elective5'] = $tblSubject->getAcronym();
//                                                $Item['Elective5Id'] = $tblSubject->getId();
//                                                break;
//                                        }
//                                    }
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
                            $Item['Elective'] = implode('<br/>', $ElectiveList);
                            foreach ($ElectiveList as $Elective) {
                                $Item['ExcelElective'][] = $Elective;
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
     * @param $PersonList
     * @param $tblPersonList
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param TblDivisionSubject|null $tblDivisionSubject
     *
     * @return false|FilePointer
     */
    public function createClassListExcel($PersonList, $tblPersonList, TblDivision $tblDivision = null, TblGroup $tblGroup = null,
        TblDivisionSubject $tblDivisionSubject = null)
    {

        if (!empty($PersonList)) {

            $isProfile = false;
            $isOrientation = false;
            $isElective = false;

            if($tblDivision && ($tblLevel = $tblDivision->getTblLevel())){
                if(($tblType = $tblLevel->getServiceTblType())){
                    // Profil
                    if(($tblLevel->getName() == 8
                            || $tblLevel->getName() == 9
                            || $tblLevel->getName() == 10)
                        && $tblType->getName() == 'Gymnasium'){
                        $isProfile = true;
                    }
                    // Wahlbereich
                    if(($tblLevel->getName() == 7
                            || $tblLevel->getName() == 8
                            || $tblLevel->getName() == 9)
                        && $tblType->getName() == 'Mittelschule / Oberschule'){
                        $isOrientation = true;
                    }
                    // Wahlfach
                    if($tblLevel->getName() == 10
                        && $tblType->getName() == 'Mittelschule / Oberschule'){
                        $isElective = true;
                    }

                }
            }

            $fileLocation = Storage::createFilePointer('xlsx');

            $Row = 0;
            $Column = 0;

            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($Column++, $Row), "lfd.Nr.");
            $export->setValue($export->getCell($Column++, $Row), "Name");
            $export->setValue($export->getCell($Column++, $Row), "Vorname");
            $export->setValue($export->getCell($Column++, $Row), "Geschlecht");
            $export->setValue($export->getCell($Column++, $Row), "Konfession");
            $export->setValue($export->getCell($Column++, $Row), "Geburtsdatum");
            $export->setValue($export->getCell($Column++, $Row), "Geburtsort");
            $export->setValue($export->getCell($Column++, $Row), "Ortsteil");
            $export->setValue($export->getCell($Column++, $Row), "Straße");
            $export->setValue($export->getCell($Column++, $Row), "Hausnr.");
            $export->setValue($export->getCell($Column++, $Row), "PLZ");
            $export->setValue($export->getCell($Column++, $Row), "Ort");
            $export->setValue($export->getCell($Column++, $Row), "Telefon");
            $export->setValue($export->getCell($Column++, $Row), "E-Mail");
            $export->setValue($export->getCell($Column++, $Row), "E-Mail Privat");
            $export->setValue($export->getCell($Column++, $Row), "E-Mail Geschäftlich");
            $export->setValue($export->getCell($Column++, $Row), "FS 1");
            $export->setValue($export->getCell($Column++, $Row), "FS 2");
            $export->setValue($export->getCell($Column++, $Row), "FS 3");
            $export->setValue($export->getCell($Column, $Row), "Religion");
            if($isProfile){
                $export->setValue($export->getCell(++$Column, $Row), "Profil");
            }
            if($isOrientation){
                $export->setValue($export->getCell(++$Column, $Row), "Wahlbereich");
            }
            if($isElective){
                $export->setValue($export->getCell(++$Column, $Row), "Wahlfächer");
            }



            $export->setStyle($export->getCell(0, $Row), $export->getCell($Column, $Row))
                // Header Fett
                ->setFontBold()
                // Strich nach dem Header
                ->setBorderBottom();


            foreach ($PersonList as $PersonData) {
                $Row++;
                $Column = 0;
                $phoneRow = $mailRow = $Row;

                $export->setValue($export->getCell($Column++, $Row), $PersonData['Number']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['LastName']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Gender']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Denomination']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['District']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['Code']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['City']);
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
                $export->setValue($export->getCell($Column++, $Row), $PersonData['ExcelMailPrivate']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['ExcelMailBusiness']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['ForeignLanguage1']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['ForeignLanguage2']);
                $export->setValue($export->getCell($Column++, $Row), $PersonData['ForeignLanguage3']);
                $export->setValue($export->getCell($Column, $Row), $PersonData['Religion']);
                if($isProfile){
                    $export->setValue($export->getCell(++$Column, $Row), $PersonData['Profile']);
                }
                if($isOrientation){
                    $export->setValue($export->getCell(++$Column, $Row), $PersonData['Orientation']);
                }
                if($isElective){
                    $export->setValue($export->getCell(++$Column, $Row), (is_array($PersonData['ExcelElective'])
                        ? implode(', ', $PersonData['ExcelElective'])
                        : '') );
                }



                // get row to the same high as highest PhoneRow or MailRow
                if ($Row < ($phoneRow - 1)) {
                    $Row = ($phoneRow - 1);
                }
                if ($Row < ($mailRow - 1)) {
                    $Row = ($mailRow - 1);
                }

                // Strich nach jedem Schüler
                $export->setStyle($export->getCell(0, $Row), $export->getCell($Column, $Row))
                    ->setBorderBottom();
            }

            //Column width
            $column = 0;
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(7);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(17);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(13);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(25);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(8);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(7);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(25);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(25);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(25);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(25);

            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(6);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(6);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(6);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(8);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(7);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(12);
            $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $Row))->setColumnWidth(12);

            $Row++;
            $Row++;
            $RowDescription = $Row;
            if ($tblDivision) {
                $export->setValue($export->getCell("0", $Row), 'Klasse:');
                $export->setValue($export->getCell("2", $Row), $tblDivision->getDisplayName());
            } elseif ($tblGroup) {
                $export->setValue($export->getCell("0", $Row), 'Stammgruppe:');
                $export->setValue($export->getCell("2", $Row), $tblGroup->getName());
            }
            $Row++;
            Person::setGenderFooter($export, $tblPersonList, $Row, 0, 2);
            $Row++;
            if ($tblDivision) {
                $export->setValue($export->getCell("0", $Row), 'Klassenlehrer:');
                if(($tblDivisionTeacherList = Division::useService()->getDivisionTeacherAllByDivision($tblDivision))){
                    $TeacherList = array();
                    /** @var TblDivisionTeacher $tblDivisionTeacher */
                    foreach($tblDivisionTeacherList as $tblDivisionTeacher){
                        if(($tblPerson = $tblDivisionTeacher->getServiceTblPerson())){
                            $TeacherList[] = $tblPerson->getFullName();
                        }
                    }
                    $TeacherString = implode(', ', $TeacherList);
                    $export->setValue($export->getCell("2", $Row), $TeacherString);
                }
            } elseif ($tblGroup) {
                $export->setValue($export->getCell("0", $Row), 'Tudor/Mentor:');
                $export->setValue($export->getCell("2", $Row), $tblGroup->getTudorsString(false));
            } elseif ($tblDivisionSubject
                && ($tblDivisionItem = $tblDivisionSubject->getTblDivision())
                && ($tblSubject = $tblDivisionSubject->getServiceTblSubject())
                && ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
            ) {
                $export->setValue($export->getCell("0", $Row), 'Fachlehrer:');
                $export->setValue($export->getCell("2", $Row), Division::useService()->getSubjectTeacherNameList($tblDivisionItem, $tblSubject, $tblSubjectGroup));
            }
            $Row++;
            if ($tblDivision) {
                $export->setValue($export->getCell("0", $Row), 'Klassensprecher:');
                if (($tblDivisionRepresentationList = Division::useService()->getDivisionRepresentativeByDivision($tblDivision))) {
                    $Representation = array();
                    foreach ($tblDivisionRepresentationList as $tblDivisionRepresentation) {
                        $tblRepresentation = $tblDivisionRepresentation->getServiceTblPerson();
                        $Description = $tblDivisionRepresentation->getDescription();
                        $Representation[] = $tblRepresentation->getFirstSecondName() . ' ' . $tblRepresentation->getLastName()
                            . ($Description ? ' (' . $Description . ')' : '');
                    }
                    $RepresentationString = implode(', ', $Representation);
                    $export->setValue($export->getCell("2", $Row), $RepresentationString);
                }
            }

            // Legende
            $Row = $RowDescription;
            $export->setValue($export->getCell("11", $Row), 'Abkürzungen Telefon:');
            $Row++;
            $export->setValue($export->getCell("11", $Row), 'p = Privat');
            $Row++;
            $export->setValue($export->getCell("11", $Row), 'g = Geschäftlich');
            $Row++;
            $export->setValue($export->getCell("11", $Row), 'n = Notfall');
            $Row++;
            $export->setValue($export->getCell("11", $Row), 'f = Fax');

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createExtendedClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();
        if (!empty($tblPersonList)) {

            $count = 1;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {

                $Item['Number'] = $count++;
                $Item['FirstName'] = $tblPerson->getFirstName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StudentNumber'] = '';
                $Item['Gender'] = '';
                $Item['Guardian1'] = $Item['PhoneGuardian1'] = $Item['PhoneGuardian1Excel'] = '';
                $Item['Guardian2'] = $Item['PhoneGuardian2'] = $Item['PhoneGuardian2Excel'] = '';
                $Item['Guardian3'] = $Item['PhoneGuardian3'] = $Item['PhoneGuardian3Excel'] = '';
                $Item['Authorized'] = $Item['PhoneAuthorized'] = $Item['PhoneAuthorizedExcel'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = '';
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $tblBirhdates = $tblCommon->getTblCommonBirthDates();
                    if ($tblBirhdates) {
                        if ($tblBirhdates->getGender() == 1) {
                            $Item['Gender'] = 'männlich';
                        } elseif ($tblBirhdates->getGender() == 2) {
                            $Item['Gender'] = 'weiblich';
                        }
                    }
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {
                        $Item['StudentNumber'] = $tblStudent->getIdentifierComplete();
                    }
                }
                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                }
                // Guardian 1
                $tblPersonG1 = false;
                // Guardian 2
                $tblPersonG2 = false;
                // Guardian 3
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
                    $Item['Guardian1'] = $tblPersonG1->getFullName();
                    $Item['PhoneGuardian1'] = $this->getPhoneList($tblPersonG1);
                    $Item['PhoneGuardian1Excel'] = $this->getPhoneList($tblPersonG1, true);
                }
                if ($tblPersonG2) {
                    $Item['Guardian2'] = $tblPersonG2->getFullName();
                    $Item['PhoneGuardian2'] = $this->getPhoneList($tblPersonG2);
                    $Item['PhoneGuardian2Excel'] = $this->getPhoneList($tblPersonG2, true);
                }
                if ($tblPersonG3) {
                    $Item['Guardian3'] = $tblPersonG3->getFullName();
                    $Item['PhoneGuardian3'] = $this->getPhoneList($tblPersonG3);
                    $Item['PhoneGuardian3Excel'] = $this->getPhoneList($tblPersonG3, true);
                }
                if($tblPersonA){
                    $Item['Authorized'] = $tblPersonA->getFullName();
                    $Item['PhoneAuthorized'] = $this->getPhoneList($tblPersonA);
                    $Item['PhoneAuthorizedExcel'] = $this->getPhoneList($tblPersonA, true);
                }

                if (($tblChild = $tblPerson->getChild())) {
                    $Item['AuthorizedToCollect'] = $tblChild->getAuthorizedToCollect();
                } else {
                    $Item['AuthorizedToCollect'] = '';
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
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
     * @param array       $PersonList
     * @param TblPerson[] $tblPersonList
     * @param TblDivision $tblDivision
     *
     * @return bool|FilePointer
     * @throws DocumentTypeException
     */
    public function createExtendedClassListExcel($PersonList, $tblPersonList, TblDivision $tblDivision)
    {

        if (!empty($PersonList)) {

            $IsAuthorized = false;
            $TempList = $PersonList;

            foreach($TempList as $Row){
                if($Row['Authorized']){
                    $IsAuthorized = true;
                    break;
                }
            }

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
            $export->setValue($export->getCell($column++, "0"), "Sorgeberechtigter 3");
            $export->setValue($export->getCell($column, "0"), "Tel. Sorgeber. 3");
            if($IsAuthorized){
                $column++;
                $export->setValue($export->getCell($column++, "0"), "Bevollmächtigt");
                $export->setValue($export->getCell($column, "0"), "Tel. Bevollmächtigt");
            }
            $column++;
            $export->setValue($export->getCell($column, "0"), "Abholberechtigte");

            $Row = 1;

            foreach ($PersonList as $PersonData) {

                $column = 0;
                $export->setValue($export->getCell($column++, $Row), $PersonData['Number']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['StudentNumber']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['LastName']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Gender']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Address']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Code']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['City']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['District']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Guardian1']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['PhoneGuardian1Excel']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Guardian2']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['PhoneGuardian2Excel']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Guardian3']);
                $export->setValue($export->getCell($column, $Row), $PersonData['PhoneGuardian3Excel']);
                if($IsAuthorized){
                    $column++;
                    $export->setValue($export->getCell($column++, $Row), $PersonData['Authorized']);
                    $export->setValue($export->getCell($column, $Row), $PersonData['PhoneAuthorizedExcel']);
                }
                $column++;
                $export->setValue($export->getCell($column, $Row), $PersonData['AuthorizedToCollect']);

                $Row++;
            }

            $Row++;
            $RowDescription = $Row;
            $export->setValue($export->getCell("0", $Row), 'Klasse:');
            $export->setValue($export->getCell("1", $Row), $tblDivision->getDisplayName());
            $Row++;
            Person::setGenderFooter($export, $tblPersonList, $Row);

            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Klassenlehrer:');
            if(($tblDivisionTeacherList = Division::useService()->getDivisionTeacherAllByDivision($tblDivision))){
                $TeacherList = array();
                /** @var TblDivisionTeacher $tblDivisionTeacher */
                foreach($tblDivisionTeacherList as $tblDivisionTeacher){
                    if(($tblPerson = $tblDivisionTeacher->getServiceTblPerson())){
                        $TeacherList[] = $tblPerson->getFullName();
                    }
                }
                $TeacherString = implode(', ', $TeacherList);
                $export->setValue($export->getCell("1", $Row), $TeacherString);
            }
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Klassensprecher:');
            if(($tblDivisionRepresentationList = Division::useService()->getDivisionRepresentativeByDivision($tblDivision))){
                $Representation = array();
                foreach($tblDivisionRepresentationList as $tblDivisionRepresentation){
                    $tblRepresentation = $tblDivisionRepresentation->getServiceTblPerson();
                    $Description = $tblDivisionRepresentation->getDescription();
                    $Representation[] = $tblRepresentation->getFirstSecondName().' '.$tblRepresentation->getLastName()
                        .($Description ? ' ('.$Description.')' : '');
                }
                $RepresentationString = implode(', ', $Representation);
                $export->setValue($export->getCell("1", $Row), $RepresentationString);
            }

            // Legende
            $Row = $RowDescription;
            $column = 14;
            $export->setValue($export->getCell($column, $Row), 'Abkürzungen Telefon:');
            $Row++;
            $export->setValue($export->getCell($column, $Row), 'p = Privat');
            $Row++;
            $export->setValue($export->getCell($column, $Row), 'g = Geschäftlich');
            $Row++;
            $export->setValue($export->getCell($column, $Row), 'n = Notfall');
            $Row++;
            $export->setValue($export->getCell($column, $Row), 'f = Fax');

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createBirthdayClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

        $TableContent = array();

        $All = 0;

        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$All) {
                //                $All++;
                //                $Item['Number'] = $All;
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['Gender'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = '';
                $Item['Address'] = '';
                $Item['Birth'] = $Item['BirthDay'] = $Item['BirthMonth'] = $Item['BirthYear'] = '';
                $Item['Birthplace'] = $Item['Age'] = '';
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $tblBirhdates = $tblCommon->getTblCommonBirthDates();
                    if ($tblBirhdates) {
                        if ($tblBirhdates->getGender() === 1) {
                            $Item['Gender'] = 'männlich';
                        } elseif ($tblBirhdates->getGender() === 2) {
                            $Item['Gender'] = 'weiblich';
                        }
                    }
                }

                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birth'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['BirthDay'] = $common->getTblCommonBirthDates()->getBirthday('d');
                    $Item['BirthMonth'] = $common->getTblCommonBirthDates()->getBirthday('m');
                    $Item['BirthYear'] = $common->getTblCommonBirthDates()->getBirthday('Y');
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                    $birthDate = new DateTime($Item['Birth']);
                    $now = new DateTime();
                    if ($birthDate->format('Y.m') != $now->format('Y.m')) {
                        if (($birthDate->format('m.d')) <= ($now->format('m.d'))) {
                            $Item['Age'] = $now->format('Y') - $birthDate->format('Y');
                        } else {
                            $Item['Age'] = ($now->format('Y') - 1) - $birthDate->format('Y');
                        }
                    }
                }
                array_push($TableContent, $Item);
            });
        }
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

            array_walk($TableContent, function (&$Row) use (&$All) {
                $All++;
                $Row['Number'] = $All;
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createBirthdayClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $i = 0;
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($i++, "0"), "lfd. Nr.");
            $export->setValue($export->getCell($i++, "0"), "Name, Vorname");
            $export->setValue($export->getCell($i++, "0"), "Anschrift");
            $export->setValue($export->getCell($i++, "0"), "Geburtsort");
            $export->setValue($export->getCell($i++, "0"), "Geburtsdatum");
            $export->setValue($export->getCell($i++, "0"), "Geburtstag");
            $export->setValue($export->getCell($i++, "0"), "Geburtsmonat");
            $export->setValue($export->getCell($i++, "0"), "Geburtsjahr");
            $export->setValue($export->getCell($i, "0"), "Alter");

            $Row = 1;

            foreach ($PersonList as $PersonData) {
                $i = 0;
                $export->setValue($export->getCell($i++, $Row), $PersonData['Number']);
                $export->setValue($export->getCell($i++, $Row), $PersonData['Name']);
                $export->setValue($export->getCell($i++, $Row), $PersonData['Address']);
                $export->setValue($export->getCell($i++, $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell($i++, $Row), $PersonData['Birth']);
                $export->setValue($export->getCell($i++, $Row), $PersonData['BirthDay']);
                $export->setValue($export->getCell($i++, $Row), $PersonData['BirthMonth']);
                $export->setValue($export->getCell($i++, $Row), $PersonData['BirthYear']);
                $export->setValue($export->getCell($i, $Row), $PersonData['Age']);

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
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createMedicalInsuranceClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();

        if (!empty($tblPersonList)) {

            $count = 1;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count) {

                $Item['Number'] = $count++;
                $Item['MedicalInsurance'] = '';
                $Item['StudentNumber'] = '';
                $Item['Gender'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = '';
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $tblBirhdates = $tblCommon->getTblCommonBirthDates();
                    if ($tblBirhdates) {
                        if ($tblBirhdates->getGender() === 1) {
                            $Item['Gender'] = 'männlich';
                        } elseif ($tblBirhdates->getGender() === 2) {
                            $Item['Gender'] = 'weiblich';
                        }
                    }

                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {
                        if ($tblStudent->getTblStudentMedicalRecord()) {
                            $Item['MedicalInsurance'] = $tblStudent->getTblStudentMedicalRecord()->getInsurance();
                        }
                        $Item['StudentNumber'] = $tblStudent->getIdentifierComplete();
                    }
                }
                $Item['Name'] = $tblPerson->getLastFirstName();
                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday() . '<br/>' . $common->getTblCommonBirthDates()->getBirthplace();
                }

                $Guardian1 = null;
                $Guardian2 = null;
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    $Count = 0;
                    foreach ($guardianList as $guardian) {
                        if ($guardian->getServiceTblPersonFrom() && $guardian->getTblType()->getName() == 'Sorgeberechtigt') {
                            if ($Count === 0) {
                                $Guardian1 = $guardian->getServiceTblPersonFrom();
                            }
                            if ($Count === 1) {
                                $Guardian2 = $guardian->getServiceTblPersonFrom();
                            }
                            $Count++;
                        }
                    }
                }

                $phoneListGuardian = array();
                if ($Guardian1) {
                    $PhoneListGuardian1 = Phone::useService()->getPhoneAllByPerson($Guardian1);
                    if ($PhoneListGuardian1) {
                        foreach ($PhoneListGuardian1 as $PhoneGuardian1) {
                            $phoneListGuardian[] = $PhoneGuardian1->getTblPhone()->getNumber();
                        }
                    }
                    $Guardian1 = $Guardian1->getFullName();
                } else {
                    $Guardian1 = '';
                }
                if ($Guardian2) {
                    $PhoneListGuardian2 = Phone::useService()->getPhoneAllByPerson($Guardian2);
                    if ($PhoneListGuardian2) {
                        foreach ($PhoneListGuardian2 as $PhoneGuardian2) {
                            $phoneListGuardian[] = $PhoneGuardian2->getTblPhone()->getNumber();
                        }
                    }
                    $Guardian2 = $Guardian2->getFullName();
                } else {
                    $Guardian2 = '';
                }
                $Item['Guardian'] = $Guardian1 . '<br/>' . $Guardian2;

                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                $phoneArray = array();
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        $phoneArray[] = $phone->getTblPhone()->getNumber();
                    }
                }
                if (count($phoneArray) >= 1) {
                    $phoneString = implode('<br/>', $phoneArray);
                } else {
                    $phoneString = '';
                }
                $Item['PhoneNumber'] = $phoneString;
                $phoneListGuardian = array_unique($phoneListGuardian);
                if (count($phoneListGuardian) >= 1) {
                    $phoneGuardianString = implode('<br/>', $phoneListGuardian);
                } else {
                    $phoneGuardianString = '';
                }
                $Item['PhoneGuardianNumber'] = $phoneGuardianString;

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createMedicalInsuranceClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "Schülernummer");
            $export->setValue($export->getCell("1", "0"), "Name, Vorname");
            $export->setValue($export->getCell("2", "0"), "Anschrift");
            $export->setValue($export->getCell("3", "0"), "Geburtsdatum");
            $export->setValue($export->getCell("3", "1"), "Geburtsort");
            $export->setValue($export->getCell("4", "0"), "Krankenkasse");
            $export->setValue($export->getCell("5", "0"), "1. Sorgeberechtigter");
            $export->setValue($export->getCell("5", "1"), "2. Sorgeberechtigter");
            $export->setValue($export->getCell("6", "0"), "Telefon");
            $export->setValue($export->getCell("6", "1"), "Schüler");
            $export->setValue($export->getCell("7", "0"), "Telefon");
            $export->setValue($export->getCell("7", "1"), "Sorgeberechtigte");

            $Row = 2;

            foreach ($PersonList as $PersonData) {
                $Name = explode('<br/>', $PersonData['Name']);
                $Address = explode('<br/>', $PersonData['Address']);
                $Birthday = explode('<br/>', $PersonData['Birthday']);
                $KK = explode('<br/>', $PersonData['MedicalInsurance']);
                $Guardian = explode('<br/>', $PersonData['Guardian']);
                $PhoneNumber = explode('<br/>', $PersonData['PhoneNumber']);
                $PhoneGuardianNumber = explode('<br/>', $PersonData['PhoneGuardianNumber']);

                $count = count($Name);
                if (count($Address) > $count) {
                    $count = count($Address);
                }
                if (count($KK) > $count) {
                    $count = count($KK);
                }
                if (count($Guardian) > $count) {
                    $count = count($Guardian);
                }
                if (count($PhoneNumber) > $count) {
                    $count = count($PhoneNumber);
                }
                if (count($PhoneGuardianNumber) > $count) {
                    $count = count($PhoneGuardianNumber);
                }

                $export->setValue($export->getCell("0", $Row), $PersonData['Number']);
                for ($i = 0; $i < $count; $i++) {
                    if (isset($Name[$i])) {
                        $export->setValue($export->getCell("1", $Row), $Name[$i]);
                    }
                    if (isset($Address[$i])) {
                        $export->setValue($export->getCell("2", $Row), $Address[$i]);
                    }
                    if (isset($Birthday[$i])) {
                        $export->setValue($export->getCell("3", $Row), $Birthday[$i]);
                    }
                    if (isset($KK[$i])) {
                        $export->setValue($export->getCell("4", $Row), $KK[$i]);
                    }
                    if (isset($Guardian[$i])) {
                        $export->setValue($export->getCell("5", $Row), $Guardian[$i]);
                    }
                    if (isset($PhoneNumber[$i])) {
                        $export->setValue($export->getCell("6", $Row), $PhoneNumber[$i]);
                    }
                    if (isset($PhoneGuardianNumber[$i])) {
                        $export->setValue($export->getCell("7", $Row), $PhoneGuardianNumber[$i]);
                    }
                    $Row++;
                }
            }

            $Row++;
            Person::setGenderFooter($export, $tblPersonList, $Row);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return array
     */
    public function createGroupList(TblGroup $tblGroup)
    {

        $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
        $TableContent = array();

        if (!empty($tblPersonList)) {

            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy(TblPerson::ATTR_LAST_NAME, new StringGermanOrderSorter());
            $All = 0;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$All, $tblGroup) {

                $All++;
                $Item['Title'] = $tblPerson->getTitle();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Number'] = $All;
                $Item['Salutation'] = $tblPerson->getSalutation();
                $Item['Gender'] = '';
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = '';
                $Item['BirthdaySort'] = '';
                $Item['BirthdayYearSort'] = '';
                $Item['PhoneNumber'] = '';
                $Item['MobilPhoneNumber'] = '';
                $Item['Mail'] = '';
                $Item['BirthPlace'] = '';
                $Item['Nationality'] = '';
                $Item['Religion'] = '';
                $Item['ParticipationWillingness'] = '';
                $Item['ParticipationActivities'] = '';
                $Item['RemarkFrontend'] = '';
                $Item['RemarkExcel'] = '';

                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $Item['RemarkExcel'] = $tblCommon->getRemark();
                    $Item['RemarkFrontend'] = nl2br($tblCommon->getRemark());
                    if (($tblBirthdates = $tblCommon->getTblCommonBirthDates())) {
                        $Item['Birthday'] = $tblBirthdates->getBirthday();
                        if ($Item['Birthday'] != '') {
                            $Year = substr($Item['Birthday'], 6, 4);
                            $Month = substr($Item['Birthday'], 3, 2);
                            $Day = substr($Item['Birthday'], 0, 2);
                            if (is_numeric($Month) && is_numeric($Day)) {
                                $Item['BirthdaySort'] = $Month * 100 + $Day;
                            }
                            if (is_numeric($Year) && is_numeric($Month) && is_numeric($Day)) {
                                $Item['BirthdayYearSort'] = ($Year * 10000) + ($Month * 100) + $Day;
                            }
                        }
                        $Item['BirthPlace'] = $tblBirthdates->getBirthplace();
                        if (($tblGender = $tblBirthdates->getTblCommonGender())) {
                            $Item['Gender'] = $tblGender->getName();
                        }
                    }
                    if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                        $Item['Nationality'] = $tblCommonInformation->getNationality();
                        $Item['Religion'] = $tblCommonInformation->getDenomination();
                        $Item['ParticipationActivities'] = $tblCommonInformation->getAssistanceActivity();
                        if ($tblCommonInformation->isAssistance()) {
                            $Item['ParticipationWillingness'] = 'ja';
                        } else {
                            $Item['ParticipationWillingness'] = 'nein';

                        }
                    }
                }
                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                }
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
                    $Item['PhoneNumber'] = implode(', ', $phoneArray);
                }
                if (count($mobilePhoneArray) >= 1) {
                    $Item['MobilPhoneNumber'] = implode(', ', $mobilePhoneArray);
                }
                $mailAddressList = Mail::useService()->getMailAllByPerson($tblPerson);
                $mailList = array();
                if ($mailAddressList) {
                    foreach ($mailAddressList as $mailAddress) {
                        $mailList[] = $mailAddress->getTblMail()->getAddress();
                    }
                }
                if (count($mailList) >= 1) {
                    $Item['Mail'] = $mailList[0];
                }

                if ($tblGroup->getMetaTable() == 'PROSPECT') {
                    $Item['ReservationDate'] = '';
                    $Item['InterviewDate'] = '';
                    $Item['TrialDate'] = '';
                    $Item['ReservationYear'] = '';
                    $Item['ReservationDivision'] = '';
                    $Item['SchoolTypeA'] = '';
                    $Item['SchoolTypeB'] = '';
                    if (($tblProspect = Prospect::useService()->getProspectByPerson($tblPerson))) {
                        if (($tblProspectAppointment = $tblProspect->getTblProspectAppointment())) {
                            $Item['ReservationDate'] = $tblProspectAppointment->getReservationDate();
                            $Item['InterviewDate'] = $tblProspectAppointment->getInterviewDate();
                            $Item['TrialDate'] = $tblProspectAppointment->getTrialDate();
                        }
                        if (($tblProspectReservation = $tblProspect->getTblProspectReservation())) {
                            $Item['ReservationYear'] = $tblProspectReservation->getReservationYear();
                            $Item['ReservationDivision'] = $tblProspectReservation->getReservationDivision();
                            $Item['SchoolTypeA'] = ($tblProspectReservation->getServiceTblTypeOptionA() ? $tblProspectReservation->getServiceTblTypeOptionA()->getName() : '');
                            $Item['SchoolTypeB'] = ($tblProspectReservation->getServiceTblTypeOptionB() ? $tblProspectReservation->getServiceTblTypeOptionB()->getName() : '');
                        }
                    }
                }

                $Item['Division'] = Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson, '');
                if ($tblGroup->getMetaTable() == 'STUDENT') {
                    $Item['Identifier'] = '';
                    $Item['School'] = '';
                    $Item['SchoolCourse'] = '';
                    $Item['SchoolType'] = '';
                    $Item['PictureSchoolWriting'] = '';
                    $Item['PicturePublication'] = '';
                    $Item['PictureWeb'] = '';
                    $Item['PictureFacebook'] = '';
                    $Item['PicturePrint'] = '';
                    $Item['PictureFilm'] = '';
                    $Item['PictureAdd'] = '';
                    $Item['NameSchoolWriting'] = '';
                    $Item['NamePublication'] = '';
                    $Item['NameWeb'] = '';
                    $Item['NameFacebook'] = '';
                    $Item['NamePrint'] = '';
                    $Item['NameFilm'] = '';
                    $Item['NameAdd'] = '';
                    if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                        $tblDivisionList = Student::useService()->getCurrentDivisionListByPerson($tblPerson);
                        if ($tblDivisionList) {
                            foreach ($tblDivisionList as $tblDivision) {
                                if ($tblDivision->getTblLevel() && $tblDivision->getTblLevel()->getName() != '') {
                                    $Item['SchoolType'] = $tblDivision->getTypeName();
                                }
                            }
                        }
                        $Item['Identifier'] = $tblStudent->getIdentifierComplete();
                        $Item['School'] = (($tblCompany = Student::useService()->getCurrentSchoolByPerson($tblPerson))
                            ? $tblCompany->getDisplayName()
                            : '');
                        $Item['SchoolCourse'] = (Student::useService()->getCourseByStudent($tblStudent)
                            ? Student::useService()->getCourseByStudent($tblStudent)->getName()
                            : '');
                        // leer befüllen
                        if(($tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll())){
                            foreach($tblAgreementCategoryAll as $tblAgreementCategory){
                                $tblAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblAgreementCategory);
                                foreach($tblAgreementTypeList as $tblAgreementType){
                                    $Item['AgreementType'.$tblAgreementType->getId()] = '';
                                }
                            }
                        }
                        // befüllen was Gesetzt ist
                        if(($tblAgreementList = Student::useService()->getStudentAgreementAllByStudent($tblStudent))){
                            foreach($tblAgreementList as $tblAgreement){
                                if(($tblAgreementType = $tblAgreement->getTblStudentAgreementType())){
                                    $Item['AgreementType'.$tblAgreementType->getId()] = 'Ja';
                                }
                            }
                        }
                    }
                }
                if ($tblGroup->getMetaTable() == 'CUSTODY') {
                    $Item['Occupation'] = '';
                    $Item['Employment'] = '';
                    $Item['Remark'] = '';
                    if (($tblCustody = Custody::useService()->getCustodyByPerson($tblPerson))) {
                        $Item['Occupation'] = $tblCustody->getOccupation();
                        $Item['Employment'] = $tblCustody->getEmployment();
                        $Item['Remark'] = $tblCustody->getRemark();
                    }
                }
                if ($tblGroup->getMetaTable() == 'TEACHER') {
                    $Item['TeacherAcronym'] = '';
                    if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))) {
                        $Item['TeacherAcronym'] = $tblTeacher->getAcronym();
                    }
                }
                if ($tblGroup->getMetaTable() == 'CLUB') {
                    $Item['ClubIdentifier'] = '';
                    $Item['EntryDate'] = '';
                    $Item['ExitDate'] = '';
                    $Item['ClubRemark'] = '';
                    if (($tblClub = Club::useService()->getClubByPerson($tblPerson))) {
                        $Item['ClubIdentifier'] = $tblClub->getIdentifier();
                        $Item['EntryDate'] = $tblClub->getEntryDate();
                        $Item['ExitDate'] = $tblClub->getExitDate();
                        $Item['ClubRemark'] = $tblClub->getRemark();
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
     * @param int   $GroupId
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createGroupListExcel($PersonList, $tblPersonList, $GroupId)
    {

        $tblGroup = Group::useService()->getGroupById($GroupId);
        if (!empty($PersonList) && $tblGroup) {
            $ColumnStandard = array(
                'Number'                   => 'lfd. Nr.',
                'Salutation'               => 'Anrede',
                'Title'                    => 'Titel',
                'FirstName'                => 'Vorname',
                'LastName'                 => 'Nachname',
                'StreetName'               => 'Straße',
                'StreetNumber'             => 'Str.Nr',
                'Code'                     => 'PLZ',
                'City'                     => 'Ort',
                'District'                 => 'Ortsteil',
                'PhoneNumber'              => 'Telefon Festnetz',
                'MobilPhoneNumber'         => 'Telefon Mobil',
                'Mail'                     => 'E-mail',
                'Birthday'                 => 'Geburtsdatum',
                'BirthdaySort'             => 'Sortierung Geburtstag',
                'BirthdayYearSort'         => 'Sortierung Geburtsdatum',
                'BirthPlace'               => 'Geburtsort',
                'Gender'                   => 'Geschlecht',
                'Nationality'              => 'Staatsangehörigkeit',
                'Religion'                 => 'Konfession',
                'Division'                 => 'aktuelle Klasse',
                'ParticipationWillingness' => 'Mitarbeitsbereitschaft',
                'ParticipationActivities'  => 'Mitarbeitsbereitschaft - Tätigkeiten',
                'RemarkExcel'              => 'Bemerkungen'
            );
            $ColumnCustom = array();

            if ($tblGroup->getMetaTable() == 'PROSPECT') {
                $ColumnCustom = array(
                    'ReservationDate'     => 'Eingangsdatum',
                    'InterviewDate'       => 'Aufnahmegespräch',
                    'TrialDate'           => 'Schnuppertag',
                    'ReservationYear'     => 'Voranmeldung Schuljahr',
                    'ReservationDivision' => 'Voranmeldung Stufe',
                    'SchoolTypeA'         => 'Voranmeldung Schulart A',
                    'SchoolTypeB'         => 'Voranmeldung Schulart B'
                );
            }
            if ($tblGroup->getMetaTable() == 'STUDENT') {

                $ColumnCustom = array(
                    'Identifier'           => 'Schülernummer',
                    'School'               => 'Schule',
                    'SchoolType'           => 'Schulart',
                    'SchoolCourse'         => 'Bildungsgang',
                    'Division'             => 'aktuelle Klasse',
                );
                //Agreement Head
                if(($tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll())){
                    foreach($tblAgreementCategoryAll as $tblAgreementCategory){
                        $tblAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblAgreementCategory);
                        foreach($tblAgreementTypeList as $tblAgreementType){
                            $ColumnCustom['AgreementType'.$tblAgreementType->getId()] = $tblAgreementType->getName();
                        }
                    }
                }
            }
            if ($tblGroup->getMetaTable() == 'CUSTODY') {
                $ColumnCustom = array(
                    'Occupation' => 'Beruf',
                    'Employment' => 'Arbeitsstelle',
                    'Remark'     => 'Bemerkung Sorgeberechtigter',
                );
            }
            if ($tblGroup->getMetaTable() == 'TEACHER') {
                $ColumnCustom = array(
                    'TeacherAcronym' => 'Lehrerkürzel',
                );
            }
            if ($tblGroup->getMetaTable() == 'CLUB') {
                $ColumnCustom = array(
                    'ClubIdentifier' => 'Mitgliedsnummer',
                    'EntryDate'      => 'Eintrittsdatum',
                    'ExitDate'       => 'Austrittsdatum',
                    'ClubRemark'     => 'Bemerkung Vereinsmitglied',
                );
            }


            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $Row = 0;
//            $export->setStyle($export->getCell(0, 0), $export->getCell(12, 0))
//                ->mergeCells()->setAlignmentCenter();
            $export->setValue($export->getCell(0, 0), 'Gruppenliste ' . $tblGroup->getName());

            if ($tblGroup->getDescription(true, true)) {
                $Row++;
//                $export->setStyle($export->getCell(0, 1), $export->getCell(12, 1))
//                    ->mergeCells()->setAlignmentCenter();
                $export->setValue($export->getCell(0, 1), $tblGroup->getDescription(true, true));
            }

            if ($tblGroup->getRemark()) {
                $Row++;
//                $export->setStyle($export->getCell(0, 2), $export->getCell(12, 2))
//                    ->mergeCells()->setAlignmentCenter();
                $export->setValue($export->getCell(0, 2), $tblGroup->getRemark());
            }

            $Row += 2;

            $Column = 0;
            foreach ($ColumnStandard as $Value) {
                $export->setValue($export->getCell($Column, $Row), $Value);
                $Column++;
            }
            foreach ($ColumnCustom as $Value) {
                $export->setValue($export->getCell($Column, $Row), $Value);
//                $export->setStyle($export->getCell($Column, $Row))->setWrapText();
                $Column++;
            }

            $Row++;

            foreach ($PersonList as $PersonData) {
                $Column = 0;
                foreach ($ColumnStandard as $Key => $Value) {
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
                if (!empty($ColumnCustom)) {
                    foreach ($ColumnCustom as $Key => $Value) {
                        if (isset($PersonData[$Key])) {
                            $export->setValue($export->getCell($Column, $Row), $PersonData[$Key]);
                        }
                        $Column++;
                    }
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

                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = '';
                $Item['Phone'] = $Item['PhoneSimple'] = '';
                $Item['PhoneFixedPrivate'] = '';
                $Item['PhoneFixedWork'] = '';
                $Item['PhoneFixedEmergency'] = '';
                $Item['PhoneMobilePrivate'] = '';
                $Item['PhoneMobileWork'] = '';
                $Item['PhoneMobileEmergency'] = '';
                $Item['Mail'] = '';
                $Item['MailPrivate'] = '';
                $Item['MailWork'] = '';
                $Item['PhoneGuardian'] = $Item['PhoneGuardianSimple'] = '';
                $Item['TypeOptionA'] = $Item['TypeOptionB'] = '';
                $Item['DivisionLevel'] = '';
                $Item['RegistrationDate'] = '';
                $Item['InterviewDate'] = '';
                $Item['TrialDate'] = '';
                $Item['SchoolYear'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = $Item['Denomination'] = $Item['Nationality'] = '';
                $Item['Siblings'] = array();
                $Item['Custody1Salutation'] = $Item['Custody1Title'] = $Item['Custody1LastName'] = $Item['Custody1FirstName'] = $Item['Custody1'] = '';
                $Item['Custody1PhoneFixedPrivate'] = $Item['Custody1PhoneFixedWork'] = $Item['Custody1PhoneFixedEmergency'] = '';
                $Item['Custody1PhoneMobilePrivate'] = $Item['Custody1PhoneMobileWork'] = $Item['Custody1PhoneMobileEmergency'] = '';
                $Item['Custody1MailPrivate'] = $Item['Custody1MailWork'] = '';
                $Item['Custody2Salutation'] = $Item['Custody2Title'] = $Item['Custody2LastName'] = $Item['Custody2FirstName'] = $Item['Custody2'] = '';
                $Item['Custody2PhoneFixedPrivate'] = $Item['Custody2PhoneFixedWork'] = $Item['Custody2PhoneFixedEmergency'] = '';
                $Item['Custody2PhoneMobilePrivate'] = $Item['Custody2PhoneMobileWork'] = $Item['Custody2PhoneMobileEmergency'] = '';
                $Item['Custody2MailPrivate'] = $Item['Custody2MailWork'] = '';
                $Item['Custody3Salutation'] = $Item['Custody3Title'] = $Item['Custody3LastName'] = $Item['Custody3FirstName'] = $Item['Custody3'] = '';
                $Item['Custody3PhoneFixedPrivate'] = $Item['Custody3PhoneFixedWork'] = $Item['Custody3PhoneFixedEmergency'] = '';
                $Item['Custody3PhoneMobilePrivate'] = $Item['Custody3PhoneMobileWork'] = $Item['Custody3PhoneMobileEmergency'] = '';
                $Item['Custody3MailPrivate'] = $Item['Custody3MailWork'] = '';
                $Item['GuardianSalutation'] = $Item['GuardianTitle'] = $Item['GuardianLastName'] = $Item['GuardianFirstName'] = $Item['Guardian'] = '';
                $Item['GuardianPhoneFixedPrivate'] = $Item['GuardianPhoneFixedWork'] = $Item['GuardianPhoneFixedEmergency'] = '';
                $Item['GuardianPhoneMobilePrivate'] = $Item['GuardianPhoneMobileWork'] = $Item['GuardianPhoneMobileEmergency'] = '';
                $Item['GuardianMailPrivate'] = $Item['GuardianMailWork'] = '';
                $Item['AuthorizedPersonSalutation'] = $Item['AuthorizedPersonTitle'] = $Item['AuthorizedPersonLastName'] = $Item['AuthorizedPersonFirstName'] = $Item['AuthorizedPerson'] = '';
                $Item['AuthorizedPersonPhoneFixedPrivate'] = $Item['AuthorizedPersonPhoneFixedWork'] = $Item['AuthorizedPersonPhoneFixedEmergency'] = '';
                $Item['AuthorizedPersonPhoneMobilePrivate'] = $Item['AuthorizedPersonPhoneMobileWork'] = $Item['AuthorizedPersonPhoneMobileEmergency'] = '';
                $Item['AuthorizedPersonMailPrivate'] = $Item['AuthorizedPersonMailWork'] = '';
                $Item['Remark'] = $Item['RemarkExcel'] = '';
                $Item['MailGuardian'] = $Item['ExcelMailGuardian'] = $Item['ExcelMailGuardianSimple'] = '';
                // Transfer Arrive
                $Item['TransferCompany'] = $Item['TransferStateCompany'] = $Item['TransferType'] = $Item['TransferCourse'] = $Item['TransferDate'] = $Item['TransferRemark'] = '';

                if (($tblToPersonAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
                    $tblToPersonAddress = $tblToPersonAddressList[0];
                } else {
                    $tblToPersonAddress = false;
                }
                if ($tblToPersonAddress && ($tblAddress = $tblToPersonAddress->getTblAddress())) {
                    $Item['StreetName'] = $tblAddress->getStreetName();
                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
                    $Item['Code'] = $tblAddress->getTblCity()->getCode();
                    $Item['City'] = $tblAddress->getTblCity()->getName();
                    $Item['District'] = $tblAddress->getTblCity()->getDistrict();
                    // show in DataTable
                    $Item['Address'] = $tblAddress->getGuiString();
                }

                $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                if ($tblProspect) {
                    $tblProspectReservation = $tblProspect->getTblProspectReservation();
                    if ($tblProspectReservation) {
                        $Item['SchoolYear'] = $tblProspectReservation->getReservationYear();
                        if ($tblProspectReservation->getServiceTblTypeOptionA()) {
                            $Item['TypeOptionA'] = $tblProspectReservation->getServiceTblTypeOptionA()->getName();
                        }
                        if ($tblProspectReservation->getServiceTblTypeOptionB()) {
                            $Item['TypeOptionB'] = $tblProspectReservation->getServiceTblTypeOptionB()->getName();
                        }
                        if ($tblProspectReservation->getReservationDivision()) {
                            $Item['DivisionLevel'] = $tblProspectReservation->getReservationDivision();
                        }
                    }
                    $tblProspectAppointment = $tblProspect->getTblProspectAppointment();
                    if ($tblProspectAppointment) {
                        $Item['RegistrationDate'] = $tblProspectAppointment->getReservationDate();
                        $Item['InterviewDate'] = $tblProspectAppointment->getInterviewDate();
                        $Item['TrialDate'] = $tblProspectAppointment->getTrialDate();
                    }

                    $Item['Remark'] = nl2br($tblProspect->getRemark());
                    $Item['RemarkExcel'] = $tblProspect->getRemark();
                }

                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Denomination'] = $common->getTblCommonInformation()->getDenomination();
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                    $Item['Nationality'] = $common->getTblCommonInformation()->getNationality();
                }

                $relationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if (!empty($relationshipList)) {
                    /** @var \SPHERE\Application\People\Relationship\Service\Entity\TblToPerson $relationship */
                    foreach ($relationshipList as $relationship) {
                        if ($relationship->getServiceTblPersonFrom() && $relationship->getServiceTblPersonTo()
                            && $relationship->getTblType()->getName() == 'Geschwisterkind'
                        ) {
                            if ($relationship->getServiceTblPersonFrom()->getId() == $tblPerson->getId()) {
                                $Item['Siblings'][] = $relationship->getServiceTblPersonTo()->getFullName();
                            } else {
                                $Item['Siblings'][] = $relationship->getServiceTblPersonFrom()->getFullName();
                            }
                        }
                    }
                    if (!empty($Item['Siblings'])) {
                        $Item['Siblings'] = implode(', ', $Item['Siblings']);
                    }
                }
                if (empty($Item['Siblings'])) {
                    $Item['Siblings'] = '';
                }

                $PhoneListSimple = array();
                // get PhoneNumber by Prospect
                $tblToPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($tblToPhoneList) {
                    foreach ($tblToPhoneList as $tblToPhone) {
                        if (($tblPhone = $tblToPhone->getTblPhone())) {
                            $PhoneListSimple[$tblPhone->getId()] = $tblPhone->getNumber();
                            if ($Item['Phone'] == '') {
                                $Item['Phone'] = $tblPerson->getFirstName() . ' ' . $tblPerson->getLastName() . ' (' . $tblPhone->getNumber() . ' ' .
                                    // modify TypeShort
                                    str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                            } else {
                                $Item['Phone'] = $Item['Phone'].', ' . $tblPhone->getNumber() . ' ' .
                                    // modify TypeShort
                                    str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                            }
                        }
                    }
                    if ($Item['Phone'] != '') {
                        $Item['Phone'] = $Item['Phone'].')';
                    }
                }
                // get Mail by Prospect
                $tblToMailList = Mail::useService()->getMailAllByPerson($tblPerson);
                if ($tblToMailList) {
                    foreach ($tblToMailList as $tblToMail) {
                        if (($tblMail = $tblToMail->getTblMail())) {
                            if ($Item['Mail'] == '') {
                                $Item['Mail'] = $Item['Mail'].$tblPerson->getFirstName() . ' ' . $tblPerson->getLastName() . ' (' . $tblMail->getAddress() . ' ' .
                                    // modify TypeShort
                                    str_replace('.', '', Mail::useService()->getMailTypeShort($tblToMail));
                            } else {
                                $Item['Mail'] = $Item['Mail'].', ' . $tblMail->getAddress() . ' ' .
                                    // modify TypeShort
                                    str_replace('.', '', Mail::useService()->getMailTypeShort($tblToMail));
                            }
                        }
                    }
                    if ($Item['Mail'] != '') {
                        $Item['Mail'] = $Item['Mail'].')';
                    }
                }

                if (!empty($PhoneListSimple)) {
                    $Item['PhoneSimple'] = implode('; ', $PhoneListSimple);
                }

                $custody1 = null;
                $custody2 = null;
                $custody3 = null;
                $guardian = null;
                $authorizedPerson = null;
                $PhoneGuardianListSimple = array();
                $MailListSimple = array();
                $tblMailList = array();
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $tblToPerson) {
                        if (($tblPersonGuardian = $tblToPerson->getServiceTblPersonFrom())
                            && ($tblType = $tblToPerson->getTblType())
                            && ($tblType->getName() == 'Sorgeberechtigt' || $tblType->getName() == 'Vormund' || $tblType->getName() == 'Bevollmächtigt')
                        ) {
                            // get PhoneNumber by Guardian
                            $this->setPhoneNumbers($tblPersonGuardian, $Item, $PhoneGuardianListSimple);
                            //Mail Guardian
                            $this->setMails($tblPersonGuardian, $tblMailList, $MailListSimple);

                            if ($tblType->getName() == 'Sorgeberechtigt' && ($ranking = $tblToPerson->getRanking())) {
                                switch ($ranking) {
                                    case 1: $custody1 = $tblPersonGuardian; break;
                                    case 2: $custody2 = $tblPersonGuardian; break;
                                    case 3: $custody3 = $tblPersonGuardian; break;
                                }
                            } elseif ($tblType->getName() == 'Vormund') {
                                $hasGuardian = true;
                                $guardian = $tblPersonGuardian;
                            } elseif ($tblType->getName() == 'Bevollmächtigt') {
                                $hasAuthorizedPerson = true;
                                $authorizedPerson = $tblPersonGuardian;
                            }
                        }
                    }
                }

                if (is_array($Item['PhoneGuardian']) && !empty($Item['PhoneGuardian'])) {
                    $Item['PhoneGuardian'] = implode('; ', $Item['PhoneGuardian']);
                }
                if (!empty($PhoneGuardianListSimple)) {
                    $Item['PhoneGuardianSimple'] = implode('; ', $PhoneGuardianListSimple);
                }

                $this->setPersonData('Custody1', $Item, $custody1);
                $this->setPersonData('Custody2', $Item, $custody2);
                $this->setPersonData('Custody3', $Item, $custody3);
                $this->setPhoneNumbersExtended('', $Item, $tblPerson);
                $this->setPhoneNumbersExtended('Custody1', $Item, $custody1);
                $this->setPhoneNumbersExtended('Custody2', $Item, $custody2);
                $this->setPhoneNumbersExtended('Custody3', $Item, $custody3);
                $this->setMailsExtended('', $Item, $tblPerson);
                $this->setMailsExtended('Custody1', $Item, $custody1);
                $this->setMailsExtended('Custody2', $Item, $custody2);
                $this->setMailsExtended('Custody3', $Item, $custody3);

                if($guardian){
                    $this->setPersonData('Guardian', $Item, $guardian);
                    $this->setPhoneNumbersExtended('Guardian', $Item, $guardian);
                    $this->setMailsExtended('Guardian', $Item, $guardian);
                }
                if($authorizedPerson){
                    $this->setPersonData('AuthorizedPerson', $Item, $authorizedPerson);
                    $this->setPhoneNumbersExtended('AuthorizedPerson', $Item, $authorizedPerson);
                    $this->setMailsExtended('AuthorizedPerson', $Item, $authorizedPerson);
                }

                // Insert MailList
                if (!empty($tblMailList)) {
                    $Item['MailGuardian'] = $Item['MailGuardian'].implode('<br>', $tblMailList);
                    $Item['ExcelMailGuardian'] = implode('; ', $tblMailList);
                }
                // Insert MailListSimple
                if (!empty($MailListSimple)) {
                    $Item['ExcelMailGuardianSimple'] = implode('; ', $MailListSimple);
                }

                // Transfer Arrive
                if(($tblStudent = $tblPerson->getStudent())){
                    $TransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');
                    if(($tblStudentTransferByTypeArrive = Student::useService()->getStudentTransferByType($tblStudent, $TransferTypeArrive))){
                        if(($tblCompanyTransfer = $tblStudentTransferByTypeArrive->getServiceTblCompany())){
                            $Item['TransferCompany'] = $tblCompanyTransfer->getDisplayName();
                        }
                        if(($tblStateCompanyTransfer = $tblStudentTransferByTypeArrive->getServiceTblStateCompany())){
                            $Item['TransferStateCompany'] = $tblStateCompanyTransfer->getDisplayName();
                        }
                        if(($SchoolType = $tblStudentTransferByTypeArrive->getServiceTblType())){
                            $Item['TransferType'] = $SchoolType->getName();
                        }
                        if(($SchoolCourse = $tblStudentTransferByTypeArrive->getServiceTblCourse())){
                            $Item['TransferCourse'] = $SchoolCourse->getName();
                        }
                        $Item['TransferDate'] = $tblStudentTransferByTypeArrive->getTransferDate();
                        $Item['TransferRemark'] = $tblStudentTransferByTypeArrive->getRemark();
                    }
                }

                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param $Identifier
     * @param $Item
     * @param TblPerson|null $tblPerson
     */
    private function setPersonData($Identifier, &$Item, TblPerson $tblPerson = null)
    {
        if ($tblPerson !== null) {
            $Item[$Identifier . 'Salutation'] = $tblPerson->getSalutation();
            $Item[$Identifier . 'Title'] = $tblPerson->getTitle();
            $Item[$Identifier . 'LastName'] = $tblPerson->getLastName();
            $Item[$Identifier . 'FirstName'] = $tblPerson->getFirstSecondName();
            $Item[$Identifier] = $tblPerson->getFullName();
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
     * @param $Item
     * @param $PhoneGuardianListSimple
     */
    private function setPhoneNumbers(TblPerson $tblPersonGuardian, &$Item, &$PhoneGuardianListSimple)
    {
        $tblToPhoneList = Phone::useService()->getPhoneAllByPerson($tblPersonGuardian);
        if ($tblToPhoneList) {
            foreach ($tblToPhoneList as $tblToPhone) {
                if (($tblPhone = $tblToPhone->getTblPhone())) {
                    $PhoneGuardianListSimple[$tblPhone->getId()] = $tblPhone->getNumber();
                    if (!isset($Item['PhoneGuardian'][$tblPersonGuardian->getId()])) {
                        $Item['PhoneGuardian'][$tblPersonGuardian->getId()] =
                            $tblPersonGuardian->getFirstName() . ' ' . $tblPersonGuardian->getLastName() .
                            ' (' . $tblPhone->getNumber() . ' ' .
                            // modify TypeShort
                            str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                    } else {
                        $Item['PhoneGuardian'][$tblPersonGuardian->getId()] = $Item['PhoneGuardian'][$tblPersonGuardian->getId()]
                            .', ' . $tblPhone->getNumber() . ' ' .
                            // modify TypeShort
                            str_replace('.', '', Phone::useService()->getPhoneTypeShort($tblToPhone));
                    }
                }
            }
        }
        if (isset($Item['PhoneGuardian'][$tblPersonGuardian->getId()])) {
            $Item['PhoneGuardian'][$tblPersonGuardian->getId()] = $Item['PhoneGuardian'][$tblPersonGuardian->getId()].')';
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
     * @param $PersonList
     * @param $tblPersonList
     *
     * @param $hasGuardian
     * @param $hasAuthorizedPerson
     *
     * @return bool|FilePointer
     */
    public function createInterestedPersonListExcel($PersonList, $tblPersonList, &$hasGuardian, &$hasAuthorizedPerson)
    {

        if (!empty($PersonList)) {
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

            $Row = 1;
            foreach ($PersonList as $PersonData) {
                $column = 0;
                $export->setValue($export->getCell($column++, $Row), $PersonData['RegistrationDate']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['InterviewDate']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['TrialDate']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['LastName']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['SchoolYear']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['DivisionLevel']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['TypeOptionA']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['TypeOptionB']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['TransferCompany']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['TransferStateCompany']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['TransferType']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['TransferCourse']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['TransferDate']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['TransferRemark']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Code']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['City']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['District']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Nationality']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Denomination']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Siblings']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody1Salutation']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody1Title']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody1LastName']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody1FirstName']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody2Salutation']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody2Title']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody2LastName']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody2FirstName']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody3Salutation']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody3Title']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody3LastName']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody3FirstName']);

                if ($hasGuardian) {
                    $export->setValue($export->getCell($column++, $Row), $PersonData['GuardianSalutation']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['GuardianTitle']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['GuardianLastName']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['GuardianFirstName']);
                }

                if ($hasAuthorizedPerson) {
                    $export->setValue($export->getCell($column++, $Row), $PersonData['AuthorizedPersonSalutation']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['AuthorizedPersonTitle']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['AuthorizedPersonLastName']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['AuthorizedPersonFirstName']);
                }

                $export->setValue($export->getCell($column++, $Row), $PersonData['Phone']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['PhoneSimple']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Mail']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['MailPrivate']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['MailWork']);

                $export->setValue($export->getCell($column++, $Row), $PersonData['PhoneMobilePrivate']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['PhoneFixedPrivate']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['PhoneMobileWork']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['PhoneFixedWork']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['PhoneMobileEmergency']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['PhoneFixedEmergency']);

                $export->setValue($export->getCell($column++, $Row), $PersonData['PhoneGuardian']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['PhoneGuardianSimple']);

                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody1PhoneMobilePrivate']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody1PhoneFixedPrivate']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody1PhoneMobileWork']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody1PhoneFixedWork']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody1PhoneMobileEmergency']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody1PhoneFixedEmergency']);

                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody2PhoneMobilePrivate']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody2PhoneFixedPrivate']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody2PhoneMobileWork']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody2PhoneFixedWork']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody2PhoneMobileEmergency']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody2PhoneFixedEmergency']);

                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody3PhoneMobilePrivate']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody3PhoneFixedPrivate']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody3PhoneMobileWork']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody3PhoneFixedWork']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody3PhoneMobileEmergency']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody3PhoneFixedEmergency']);

                if ($hasGuardian){
                    $export->setValue($export->getCell($column++, $Row), $PersonData['GuardianPhoneMobilePrivate']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['GuardianPhoneFixedPrivate']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['GuardianPhoneMobileWork']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['GuardianPhoneFixedWork']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['GuardianPhoneMobileEmergency']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['GuardianPhoneFixedEmergency']);
                }
                if ($hasAuthorizedPerson){
                    $export->setValue($export->getCell($column++, $Row), $PersonData['AuthorizedPersonPhoneMobilePrivate']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['AuthorizedPersonPhoneFixedPrivate']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['AuthorizedPersonPhoneMobileWork']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['AuthorizedPersonPhoneFixedWork']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['AuthorizedPersonPhoneMobileEmergency']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['AuthorizedPersonPhoneFixedEmergency']);
                }

                $export->setValue($export->getCell($column++, $Row), $PersonData['ExcelMailGuardian']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['ExcelMailGuardianSimple']);

                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody1MailPrivate']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody1MailWork']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody2MailPrivate']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody2MailWork']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody3MailPrivate']);
                $export->setValue($export->getCell($column++, $Row), $PersonData['Custody3MailWork']);
                if ($hasGuardian){
                    $export->setValue($export->getCell($column++, $Row), $PersonData['GuardianMailPrivate']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['GuardianMailWork']);
                }
                if ($hasAuthorizedPerson){
                    $export->setValue($export->getCell($column++, $Row), $PersonData['AuthorizedPersonMailPrivate']);
                    $export->setValue($export->getCell($column++, $Row), $PersonData['AuthorizedPersonMailWork']);
                }

                $export->setValue($export->getCell($column, $Row), $PersonData['RemarkExcel']);

                // WrapText
                $export->setStyle($export->getCell($column, $Row))->setWrapText();
                $Row++;
            }

            $export->setStyle($export->getCell($column, 0))->setColumnWidth(50);

            $Row++;
            Person::setGenderFooter($export, $tblPersonList, $Row);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createElectiveClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $TableContent = array();
        if (!empty($tblPersonList)) {
            $count = 1;

            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $tblDivision, &$count) {

                $Item['Number'] = $count++;
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['Birthday'] = '';
                $Item['Education'] = '';
                $Item['ForeignLanguage1'] = '';
                $Item['ForeignLanguage2'] = '';
                $Item['ForeignLanguage3'] = '';
                $Item['Profile'] = '';
                $Item['Orientation'] = '';
                $Item['Religion'] = '';
                $Item['Elective'] = '';
                $Item['ExcelElective'] = array();
                $Item['Elective1'] = $Item['Elective2'] = $Item['Elective3'] = $Item['Elective4'] = $Item['Elective5'] = '';

                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if ($tblCommon) {
                    $Item['Birthday'] = $tblCommon->getTblCommonBirthDates()->getBirthday();
                }

                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                // NK/Profil
                if ($tblStudent) {
                    for ($i = 1; $i <= 3; $i++) {
                        $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                        $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                        $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking(
                            $tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking);
                        if ($tblPerson->getId() == 15) {
                            echo new Code(print_r($tblStudentSubject, true));
                        }

                        if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject()) && ($tblDivisionLevel = $tblDivision->getTblLevel())) {

                            $Item['ForeignLanguage' . $i] = $tblSubject->getAcronym();

                            if (($tblLevelFrom = $tblStudentSubject->getServiceTblLevelFrom())
                                && ($LevelFrom = Division::useService()->getLevelById($tblLevelFrom->getId())->getName())
                                && (is_numeric($LevelFrom)) && (is_numeric($tblDivisionLevel->getName()))) {
                                if ($tblDivisionLevel->getName() < $LevelFrom) {
                                    $Item['ForeignLanguage' . $i] = '';
                                }
                            }
                            if (($tblLevelTill = $tblStudentSubject->getServiceTblLevelTill()) &&
                                ($LevelTill = Division::useService()->getLevelById($tblLevelTill->getId())->getName())
                                && (is_numeric($LevelTill)) && (is_numeric($tblDivisionLevel->getName()))) {
                                if ($tblDivisionLevel->getName() > $LevelTill) {
                                    $Item['ForeignLanguage' . $i] = '';
                                }
                            }

                            /* Use the following block to show the starting/ending division foreach foreign language */

                            //                            if (($LevelFrom = Division::useService()->getLevelById($tblStudentSubject->getServiceTblLevelFrom()))
                            //                                && ($LevelTill = Division::useService()->getLevelById($tblStudentSubject->getServiceTblLevelTill()))) {
                            //                                /** @var TblLevel $LevelFrom, $LevelTill */
                            //                                $Item['ForeignLanguage'.$i] .= ' (von Klasse ' . $LevelFrom->getName() . ' bis ' . $LevelTill->getName() . ')';
                            //                            } elseif (($LevelFrom = Division::useService()->getLevelById($tblStudentSubject->getServiceTblLevelFrom()))) {
                            //                            $Item['ForeignLanguage'.$i] .= ' (seit Klasse ' . $LevelFrom->getName() . ')';
                            //                            } elseif (($LevelTill = Division::useService()->getLevelById($tblStudentSubject->getServiceTblLevelTill()))) {
                            //                                $Item['ForeignLanguage'.$i] .= ' (bis Klasse ' . $LevelTill->getName() . ')';
                            //                            }

                        }
                    }
                    if ($tblPerson->getId() == 15) {
                        exit;
                    }

                    // Profil
                    $tblStudentProfile = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE')
                    );
                    if ($tblStudentProfile && ($tblSubject = $tblStudentProfile[0]->getServiceTblSubject())) {
                        $Item['Profile'] = $tblSubject->getAcronym();
                    }
                    // Neigungskurs
                    $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION')
                    );
                    if ($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                        $Item['Orientation'] = $tblSubject->getAcronym();
                    }
                    // Religion
                    $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION')
                    );
                    if ($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                        $Item['Religion'] = $tblSubject->getAcronym();
                    }

                    // Bildungsgang
                    $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                    if ($tblTransferType) {
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                            $tblTransferType);
                        if ($tblStudentTransfer) {
                            $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                            if ($tblCourse) {
                                if ($tblCourse->getName() == 'Gymnasium') {
                                    $Item['Education'] = 'GY';
                                } elseif ($tblCourse->getName() == 'Hauptschule') {
                                    $Item['Education'] = 'HS';
                                } elseif ($tblCourse->getName() == 'Realschule') {
                                    $Item['Education'] = 'RS';
                                } else {
                                    $Item['Education'] = $tblCourse->getName();
                                }
                            }
                        }
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
                                    if($tblStudentElective->getServiceTblSubject()){
                                        switch($tblSubjectRanking->getIdentifier()) {
                                            case 1:
                                                $Item['Elective1'] = $tblStudentElective->getServiceTblSubject()->getAcronym();
                                                break;
                                            case 2:
                                                $Item['Elective2'] = $tblStudentElective->getServiceTblSubject()->getAcronym();
                                                break;
                                            case 3:
                                                $Item['Elective3'] = $tblStudentElective->getServiceTblSubject()->getAcronym();
                                                break;
                                            case 4:
                                                $Item['Elective4'] = $tblStudentElective->getServiceTblSubject()->getAcronym();
                                                break;
                                            case 5:
                                                $Item['Elective5'] = $tblStudentElective->getServiceTblSubject()->getAcronym();
                                                break;
                                        }
                                    }

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
                            $Item['Elective'] = implode('<br/>', $ElectiveList);
                            foreach ($ElectiveList as $Elective) {
                                $Item['ExcelElective'][] = $Elective;
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
     * @param array $PersonList
     * @param array $tblPersonList
     * @param       $DivisionId
     *
     * @return bool|FilePointer
     * @throws TypeFileException
     * @throws DocumentTypeException
     */
    public function createElectiveClassListExcel($PersonList, $tblPersonList, $DivisionId)
    {

        // get PersonList sorted by GradeBook
        if (!empty($PersonList)) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            $custodyList = array();
            if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
                $tblDivisionCustodyList = Division::useService()->getCustodyAllByDivision($tblDivision);
                if ($tblDivisionCustodyList) {
                    foreach ($tblDivisionCustodyList as $tblPerson) {
                        $custodyList[] = trim($tblPerson->getSalutation() . ' ' . $tblPerson->getLastName());
                    }
                }

                $teacherList = array();
                $tblDivisionTeacherAll = Division::useService()->getTeacherAllByDivision($tblDivision);
                if ($tblDivisionTeacherAll) {
                    foreach ($tblDivisionTeacherAll as $tblPerson) {
                        $teacherList[] = trim($tblPerson->getSalutation() . ' ' . $tblPerson->getLastName());
                    }
                }

                $export->setStyle($export->getCell(0, 0), $export->getCell(7, 0))->setFontBold();
                $export->setValue($export->getCell(0, 0),
                    "Klasse " . $tblDivision->getDisplayName() . (empty($teacherList) ? '' : ' ' . implode(', ',
                            $teacherList)));
            }

            $i = 0;
            // Header
            $export->setValue($export->getCell($i++, 1), "Name");
            $export->setValue($export->getCell($i++, 1), "Geb.-Datum");
            $export->setValue($export->getCell($i++, 1), "Bg");
            $export->setValue($export->getCell($i++, 1), "FS 1");
            $export->setValue($export->getCell($i++, 1), "FS 2");
            $export->setValue($export->getCell($i++, 1), "FS 3");
            $export->setValue($export->getCell($i++, 1), "Profil");
            $export->setValue($export->getCell($i++, 1), "Neig.k.");
            $export->setValue($export->getCell($i++, 1), "Rel.");
            $export->setValue($export->getCell($i++, 1), "WF 1-5");
            $export->setValue($export->getCell($i++, 1), "WF 1");
            $export->setValue($export->getCell($i++, 1), "WF 2");
            $export->setValue($export->getCell($i++, 1), "WF 3");
            $export->setValue($export->getCell($i++, 1), "WF 4");
            $export->setValue($export->getCell($i, 1), "WF 5");
            // Header bold
            $export->setStyle($export->getCell(0, 1), $export->getCell(14, 1))->setFontBold();

            $Row = 2;
            foreach ($PersonList as $PersonData) {
                $ElectiveRow = $Row;

                $export->setValue($export->getCell(0, $Row), $PersonData['Name']);
                $export->setValue($export->getCell(1, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell(2, $Row), $PersonData['Education']);
                $export->setValue($export->getCell(3, $Row), $PersonData['ForeignLanguage1']);
                $export->setValue($export->getCell(4, $Row), $PersonData['ForeignLanguage2']);
                $export->setValue($export->getCell(5, $Row), $PersonData['ForeignLanguage3']);
                $export->setValue($export->getCell(6, $Row), $PersonData['Profile']);
                $export->setValue($export->getCell(7, $Row), $PersonData['Orientation']);
                $export->setValue($export->getCell(8, $Row), $PersonData['Religion']);
                if(!empty($PersonData['ExcelElective'])){
                    $export->setValue($export->getCell(9, $Row), implode(', ', $PersonData['ExcelElective']));
                }
                $export->setValue($export->getCell(10, $Row), $PersonData['Elective1']);
                $export->setValue($export->getCell(11, $Row), $PersonData['Elective2']);
                $export->setValue($export->getCell(12, $Row), $PersonData['Elective3']);
                $export->setValue($export->getCell(13, $Row), $PersonData['Elective4']);
                $export->setValue($export->getCell(14, $Row), $PersonData['Elective5']);

                $Row++;
                if ($ElectiveRow > $Row) {
                    $Row = $ElectiveRow;
                }
            }
            $export->setStyle($export->getCell(0, 1), $export->getCell(14, $Row - 1))->setBorderAll();

            // Personenanzahl
            $Row++;
            Person::setGenderFooter($export, $tblPersonList, $Row);

            // Stand
            $Row += 2;
            $export->setValue($export->getCell(0, $Row), 'Stand: ' . (new DateTime())->format('d.m.Y'));

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0))->setColumnWidth(22);
            $export->setStyle($export->getCell(1, 0))->setColumnWidth(12);
            $export->setStyle($export->getCell(2, 0))->setColumnWidth(5);
            $export->setStyle($export->getCell(3, 0))->setColumnWidth(6);
            $export->setStyle($export->getCell(4, 0))->setColumnWidth(6);
            $export->setStyle($export->getCell(5, 0))->setColumnWidth(6);
            $export->setStyle($export->getCell(6, 0))->setColumnWidth(6);
            $export->setStyle($export->getCell(7, 0))->setColumnWidth(8);
            $export->setStyle($export->getCell(8, 0))->setColumnWidth(6);
            $export->setStyle($export->getCell(9, 0))->setColumnWidth(14);
            $export->setStyle($export->getCell(10, 0))->setColumnWidth(6);
            $export->setStyle($export->getCell(11, 0))->setColumnWidth(6);
            $export->setStyle($export->getCell(12, 0))->setColumnWidth(6);
            $export->setStyle($export->getCell(13, 0))->setColumnWidth(6);
            $export->setStyle($export->getCell(14, 0))->setColumnWidth(6);

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param $Person
     * @param $Year
     * @param $Division
     * @param $PersonGroup
     *
     * @return array
     */
    public function getStudentFilterResult($Person, $Year, $Division, $PersonGroup)
    {
        ini_set('memory_limit', '1G');
        $Pile = new Pile(Pile::JOIN_TYPE_INNER);
        $Pile->addPile((new ViewPerson())->getViewService(), new ViewPerson(),
            ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
        );
        $Pile->addPile((new ViewPeopleGroupMember())->getViewService(), new ViewPeopleGroupMember(),
            ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
        );
        $Pile->addPile((new ViewDivisionStudent())->getViewService(), new ViewDivisionStudent(),
            ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, ViewDivisionStudent::TBL_DIVISION_TBL_YEAR
        );
        $Pile->addPile((new ViewYear())->getViewService(), new ViewYear(),
            ViewYear::TBL_YEAR_ID, ViewYear::TBL_YEAR_ID
        );

        $Result = array();

        if (isset($Year) && $Year['TblYear_Id'] != 0 && isset($Pile)) {
            array_walk($Year, function (&$Input) {

                if (!empty($Input)) {
                    $Input = explode(' ', $Input);
                    $Input = array_filter($Input);
                } else {
                    $Input = false;
                }
            });

            $FilterYear = array_filter($Year);
            if (isset($Person) && $Person) {
                array_walk($Person, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });

                $FilterPerson = array_filter($Person);
            } else {
                $FilterPerson = array();
            }
            if (isset($PersonGroup) && $PersonGroup) {
                array_walk($PersonGroup, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });

                $FilterPersonGroup = array_filter($PersonGroup);
            } else {
                $FilterPersonGroup = array();
            }
            if (isset($Division) && $Division) {
                array_walk($Division, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });

                $FilterDivision = array_filter($Division);
            } else {
                $FilterDivision = array();
            }

            $Result = $Pile->searchPile(array(
                0 => $FilterPerson,
                1 => $FilterPersonGroup,
                2 => $FilterDivision,
                3 => $FilterYear
            ));
        }

        return $Result;
    }

    /**
     * @param array $Result
     * @param null  $Option
     * @param null  $PersonGroup
     *
     * @return array
     */
    public function getStudentTableContent($Result, $Option = null, $PersonGroup = null)
    {

        $SearchResult = array();
        if (!empty($Result)) {

            $PersonGroupName = '';
            if($PersonGroup[ViewPeopleGroupMember::TBL_GROUP_ID] != '0' && ($tblPersonGroup = Group::useService()->getGroupById($PersonGroup[ViewPeopleGroupMember::TBL_GROUP_ID]))){
                $PersonGroupName = $tblPersonGroup->getName();
            }

            /**
             * @var int                                $Index
             * @var ViewPerson[]|ViewDivisionStudent[] $Row
             */
            foreach ($Result as $Row) {

                /** @var ViewPerson $DataPerson */
                $DataPerson = $Row[0]->__toArray();
//                /** @var ViewPeopleGroupMember $DataGroup */
//                $DataGroup = $Row[1]->__toArray();
                /** @var ViewDivisionStudent $DivisionStudent */
                $DivisionStudent = $Row[2]->__toArray();
                /** @var ViewYear $Year */
                $Year = $Row[3]->__toArray();

                $tblPerson = \SPHERE\Application\People\Person\Person::useService()->getPersonById($DataPerson['TblPerson_Id']);

                // ignor existing Accounts (By Person)
                if ($tblPerson) {
                    $DataPerson['PersonGroup'] = $PersonGroupName;

                    $DataPerson['Division'] = '';
                    if (($tblDivision = Division::useService()->getDivisionById($DivisionStudent['TblDivision_Id']))) {
                        // jahrgangsübergreifende Klassen ignorieren
                        if (($tblLevel = $tblDivision->getTblLevel()) && $tblLevel->getIsChecked()) {
                            continue;
                        }
                        // inaktive ignorieren
                        if (($tblDivisionStudent = Division::useService()->getDivisionStudentByDivisionAndPerson($tblDivision, $tblPerson))
                            && ($tblDivisionStudent->isInActive())
                        ) {
                            continue;
                        }

                        /** @var TblDivision $tblDivision */
                        $DataPerson['Division'] = $tblDivision->getDisplayName();
                    }

                    $DataPerson['StudentNumber'] = '';
                    if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                        $DataPerson['StudentNumber'] = $tblStudent->getIdentifierComplete();
                    }

                    $DataPerson['FirstName'] = $tblPerson->getFirstName();
                    $DataPerson['LastName'] = $tblPerson->getLastName();
                    if ($tblPerson->getSecondName()) {
                        $DataPerson['FirstName'] = $DataPerson['FirstName'].' ' . $tblPerson->getSecondName();
                    }

                    $DataPerson['Gender'] = '';
                    $DataPerson['Birthday'] = '';
                    $DataPerson['BirthPlace'] = '';
                    $DataPerson['Religion'] = '';
                    $DataPerson['Nationality'] = '';
                    if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                        if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                            if ($tblCommonBirthDates->getBirthday()) {
                                $DataPerson['Birthday'] = $tblCommonBirthDates->getBirthday();
                            }
                            if ($tblCommonBirthDates->getBirthplace()) {
                                $DataPerson['BirthPlace'] = $tblCommonBirthDates->getBirthplace();
                            }
                            if (($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                                $DataPerson['Gender'] = $tblCommonGender->getName();
                            }
                        }
                        if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                            $DataPerson['Religion'] = $tblCommonInformation->getDenomination();
                            $DataPerson['Nationality'] = $tblCommonInformation->getNationality();
                        }
                    }

                    $DataPerson['Address'] = '';
                    $DataPerson['Street'] = '';
                    $DataPerson['HouseNumber'] = '';
                    $DataPerson['CityCode'] = '';
                    $DataPerson['City'] = '';
                    $DataPerson['District'] = '';
                    if (($tblAddress = Address::useService()->getAddressByPerson($tblPerson))) {
                        $DataPerson['Address'] = $tblAddress->getGuiString();
                        if (($tblCity = $tblAddress->getTblCity())) {
                            $DataPerson['Street'] = $tblAddress->getStreetName();
                            $DataPerson['HouseNumber'] = $tblAddress->getStreetNumber();
                            $DataPerson['CityCode'] = $tblCity->getCode();
                            $DataPerson['City'] = $tblCity->getName();
                            $DataPerson['District'] = $tblCity->getDisplayDistrict();
                        }
                    }

                    $DataPerson['Insurance'] = '';
                    $DataPerson['InsuranceState'] = '';
                    $DataPerson['Medication'] = '';
                    if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                        if (($tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord())) {
                            $DataPerson['Insurance'] = $tblStudentMedicalRecord->getInsurance();
                            $DataPerson['InsuranceState'] = $tblStudentMedicalRecord->getInsuranceState();
                            $DataPerson['Medication'] = $tblStudentMedicalRecord->getMedication();
                        }
                    }

                    $DataPerson['MailPrivate'] = '';
                    $DataPerson['MailWork'] = '';
                    if(($tblMailAll = Mail::useService()->getMailAllByPerson($tblPerson))){
                        foreach($tblMailAll as $tblToPersonMail) {
                            if(($tblTypeMail = $tblToPersonMail->getTblType())
                                && ($tblMail = $tblToPersonMail->getTblMail())){
                                if($tblTypeMail->getName() == 'Privat'){
                                    $DataPerson['MailPrivate'] = $tblMail->getAddress();
                                } elseif($tblTypeMail->getName() == 'Geschäftlich') {
                                    $DataPerson['MailWork'] = $tblMail->getAddress();
                                }
                            }
                        }
                    }

                    $DataPerson['PhoneFixedPrivate'] = '';
                    $DataPerson['PhoneFixedWork'] = '';
                    $DataPerson['PhoneFixedEmergency'] = '';
                    $DataPerson['PhoneMobilePrivate'] = '';
                    $DataPerson['PhoneMobileWork'] = '';
                    $DataPerson['PhoneMobileEmergency'] = '';
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
                                            if (empty($DataPerson['PhoneFixedPrivate'])) {
                                                $DataPerson['PhoneFixedPrivate'] = $tblPhone->getNumber()
                                                    . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            } else {
                                                $DataPerson['PhoneFixedPrivate'] = $DataPerson['PhoneFixedPrivate'].', ' . $tblPhone->getNumber()
                                                    . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                        case 'Geschäftlich':
                                            if (empty($DataPerson['PhoneFixedWork'])) {
                                                $DataPerson['PhoneFixedWork'] = $tblPhone->getNumber()
                                                    . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            } else {
                                                $DataPerson['PhoneFixedWork'] = $DataPerson['PhoneFixedWork'].', ' . $tblPhone->getNumber()
                                                    . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                        case 'Notfall':
                                            if (empty($DataPerson['PhoneFixedEmergency'])) {
                                                $DataPerson['PhoneFixedEmergency'] = $tblPhone->getNumber()
                                                    . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            } else {
                                                $DataPerson['PhoneFixedEmergency'] = $DataPerson['PhoneFixedEmergency'].', ' . $tblPhone->getNumber()
                                                    . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                    }
                                } elseif ($PhoneDescription == 'Mobil') {
                                    switch ($PhoneName) {
                                        case 'Privat':
                                            if (empty($DataPerson['PhoneMobilePrivate'])) {
                                                $DataPerson['PhoneMobilePrivate'] = $tblPhone->getNumber()
                                                    . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            } else {
                                                $DataPerson['PhoneMobilePrivate'] = $DataPerson['PhoneMobilePrivate'].', ' . $tblPhone->getNumber()
                                                    . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                        case 'Geschäftlich':
                                            if (empty($DataPerson['PhoneMobileWork'])) {
                                                $DataPerson['PhoneMobileWork'] = $tblPhone->getNumber()
                                                    . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            } else {
                                                $DataPerson['PhoneMobileWork'] = $DataPerson['PhoneMobileWork'].', ' . $tblPhone->getNumber()
                                                    . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                        case 'Notfall':
                                            if (empty($DataPerson['PhoneMobileEmergency'])) {
                                                $DataPerson['PhoneMobileEmergency'] = $tblPhone->getNumber()
                                                    . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            } else {
                                                $DataPerson['PhoneMobileEmergency'] = $DataPerson['PhoneMobileEmergency'].', ' . $tblPhone->getNumber()
                                                    . ($tblToPerson->getRemark() ? ' (' . $tblToPerson->getRemark() . ')' : '');
                                            }
                                            break;
                                    }
                                }
                            }
                        }
                    }

                    $DataPerson['Sibling_1'] = '';
                    $DataPerson['Sibling_2'] = '';
                    $DataPerson['Sibling_3'] = '';

                    $tblTypeSibling = Relationship::useService()->getTypeByName('Geschwisterkind');
                    if (($tblRelationshipSibling = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblTypeSibling))) {
                        foreach ($tblRelationshipSibling as $tblToPerson) {
                            /** @var \SPHERE\Application\People\Relationship\Service\Entity\TblToPerson $tblToPerson */
                            $SiblingString = '';
                            if (($tblPersonFrom = $tblToPerson->getServiceTblPersonFrom()) && ($tblPersonTo = $tblToPerson->getServiceTblPersonTo())) {
                                if ($tblPersonFrom->getId() !== $tblPerson->getId()) {
                                    $tblPersonSibling = $tblPersonFrom;
                                } elseif ($tblPersonTo->getId() !== $tblPerson->getId()) {
                                    $tblPersonSibling = $tblPersonTo;
                                }
                                if (!empty($tblPersonSibling)) {
                                    $SiblingString = $tblPersonSibling->getLastName() . ', ' . $tblPersonSibling->getFirstName();
                                    if ($tblPersonSibling->getSecondName()) {
                                        $SiblingString = $SiblingString.' ' . $tblPersonSibling->getSecondName();
                                    }
                                    if (($tblYear = Term::useService()->getYearById($Year[ViewYear::TBL_YEAR_ID]))) {
                                        if (($SiblingDivision = Student::useService()->getMainDivisionByPersonAndYear($tblPersonSibling, $tblYear))) {
                                            $SiblingString = $SiblingString.' (' . $SiblingDivision->getDisplayName() . ')';
                                        } else {
                                            if ($Option) {
                                                $SiblingString = $SiblingString.' (Ehemalig)';
                                            } else {
                                                $SiblingString = '';
                                            }
                                        }
                                    }
                                }
                            }
                            if (empty($DataPerson['Sibling_1']) && $SiblingString) {
                                $DataPerson['Sibling_1'] = $SiblingString;
                            } elseif (empty($DataPerson['Sibling_2']) && $SiblingString) {
                                $DataPerson['Sibling_2'] = $SiblingString;
                            } elseif (empty($DataPerson['Sibling_3']) && $SiblingString) {
                                $DataPerson['Sibling_3'] = $SiblingString;
                            }
                        }
                    }

                    // Definition mit leerwerten wird für das Frontend benötigt
                    $TypeList = array('Sorgeberechtigt' => 3, 'Vormund' => 3, 'Bevollmächtigt' => 3, 'Notfallkontakt' => 4);
                    foreach($TypeList as $Type => $Count){
                        for($j = 1; $j <= $Count; $j++) {
                            $DataPerson[$Type.$j.'_Salutation'] = '';
                            $DataPerson[$Type.$j.'_Title'] = '';
                            $DataPerson[$Type.$j.'_FirstName'] = '';
                            $DataPerson[$Type.$j.'_LastName'] = '';
                            $DataPerson[$Type.$j.'_Birthday'] = '';
                            $DataPerson[$Type.$j.'_BirthPlace'] = '';
                            $DataPerson[$Type.$j.'_Job'] = '';
                            $DataPerson[$Type.$j.'_Address'] = '';
                            $DataPerson[$Type.$j.'_Street'] = '';
                            $DataPerson[$Type.$j.'_HouseNumber'] = '';
                            $DataPerson[$Type.$j.'_CityCode'] = '';
                            $DataPerson[$Type.$j.'_City'] = '';
                            $DataPerson[$Type.$j.'_District'] = '';
                            $DataPerson[$Type.$j.'_PhoneFixedPrivate'] = '';
                            $DataPerson[$Type.$j.'_PhoneFixedWork'] = '';
                            $DataPerson[$Type.$j.'_PhoneFixedEmergency'] = '';
                            $DataPerson[$Type.$j.'_PhoneMobilePrivate'] = '';
                            $DataPerson[$Type.$j.'_PhoneMobileWork'] = '';
                            $DataPerson[$Type.$j.'_PhoneMobileEmergency'] = '';
                            $DataPerson[$Type.$j.'_Mail_Private'] = '';
                            $DataPerson[$Type.$j.'_Mail_Work'] = '';
                        }
                    }

                    $this->setRelationshipContent($tblPerson, $DataPerson);

                    // ignore duplicated Person
                    if ($DataPerson['TblPerson_Id']) {
                        if (!array_key_exists($DataPerson['TblPerson_Id'], $SearchResult)) {
                            $SearchResult[$DataPerson['TblPerson_Id']] = $DataPerson;
                        }
                    }
                }
            }
        }

        return $SearchResult;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $DataPerson
     * @return void
     */
    public function setRelationshipContent(TblPerson $tblPerson, &$DataPerson)
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
                    $DataPerson[$TypeName.$Rank.'_Salutation'] = $tblPersonRelationship->getSalutation();
                    $DataPerson[$TypeName.$Rank.'_Title'] = $tblPersonRelationship->getTitle();
                    $DataPerson[$TypeName.$Rank.'_FirstName'] = $tblPersonRelationship->getFirstName();
                    if($tblPersonRelationship->getSecondName()){
                        $DataPerson[$TypeName.$Rank.'_FirstName'] = $DataPerson[$TypeName.$Rank.'_FirstName'].' '.$tblPersonRelationship->getSecondName();
                    }
                    $DataPerson[$TypeName.$Rank.'_LastName'] = $tblPersonRelationship->getLastName();
                    if(($tblCommonCustody = Common::useService()->getCommonByPerson($tblPersonRelationship))){
                        if(($tblCommonBirthDatesCustody = $tblCommonCustody->getTblCommonBirthDates())){
                            $DataPerson[$TypeName.$Rank.'_Birthday'] = $tblCommonBirthDatesCustody->getBirthday();
                            $DataPerson[$TypeName.$Rank.'_BirthPlace'] = $tblCommonBirthDatesCustody->getBirthplace();
                        }
                    }
                    if(($tblCustody = Custody::useService()->getCustodyByPerson($tblPersonRelationship))){
                        $DataPerson[$TypeName.$Rank.'_Job'] = $tblCustody->getOccupation();
                    }
                    if(($tblAddressCustody = Address::useService()->getAddressByPerson($tblPersonRelationship))){
                        $DataPerson[$TypeName.$Rank.'_Address'] = $tblAddressCustody->getGuiString();
                        if(($tblCityCustody = $tblAddressCustody->getTblCity())){
                            $DataPerson[$TypeName.$Rank.'_Street'] = $tblAddressCustody->getStreetName();
                            $DataPerson[$TypeName.$Rank.'_HouseNumber'] = $tblAddressCustody->getStreetNumber();
                            $DataPerson[$TypeName.$Rank.'_CityCode'] = $tblCityCustody->getCode();
                            $DataPerson[$TypeName.$Rank.'_City'] = $tblCityCustody->getName();
                            $DataPerson[$TypeName.$Rank.'_District'] = $tblCityCustody->getDisplayDistrict();
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
                                            if($DataPerson[$TypeName.$Rank.'_PhoneFixedPrivate']){
                                                $DataPerson[$TypeName.$Rank.'_PhoneFixedPrivate'] = $DataPerson[$TypeName.$Rank.'_PhoneFixedPrivate'].', ';
                                            }
                                            $DataPerson[$TypeName.$Rank.'_PhoneFixedPrivate'] = $tblPhoneCustody->getNumber()
                                                .($tblToPersonCustody->getRemark() ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                            break;
                                        case 'Geschäftlich':
                                            if($DataPerson[$TypeName.$Rank.'_PhoneFixedWork']){
                                                $DataPerson[$TypeName.$Rank.'_PhoneFixedWork'] = $DataPerson[$TypeName.$Rank.'_PhoneFixedWork'].', ';
                                            }
                                            $DataPerson[$TypeName.$Rank.'_PhoneFixedWork'] = $DataPerson[$TypeName.$Rank.'_PhoneFixedWork'].$tblPhoneCustody->getNumber()
                                                .($tblToPersonCustody->getRemark() ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                            break;
                                        case 'Notfall':
                                            if($DataPerson[$TypeName.$Rank.'_PhoneFixedEmergency']){
                                                $DataPerson[$TypeName.$Rank.'_PhoneFixedEmergency'] = $DataPerson[$TypeName.$Rank.'_PhoneFixedEmergency'].', ';
                                            }
                                            $DataPerson[$TypeName.$Rank.'_PhoneFixedEmergency'] = $DataPerson[$TypeName.$Rank.'_PhoneFixedEmergency'].$tblPhoneCustody->getNumber()
                                                .($tblToPersonCustody->getRemark() ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                            break;
                                    }
                                } elseif($PhoneDescriptionCustody == 'Mobil') {
                                    switch($PhoneNameCustody) {
                                        case 'Privat':
                                            if($DataPerson[$TypeName.$Rank.'_PhoneMobilePrivate']){
                                                $DataPerson[$TypeName.$Rank.'_PhoneMobilePrivate'] = $DataPerson[$TypeName.$Rank.'_PhoneMobilePrivate'].', ';
                                            }
                                            $DataPerson[$TypeName.$Rank.'_PhoneMobilePrivate'] = $DataPerson[$TypeName.$Rank.'_PhoneMobilePrivate'].$tblPhoneCustody->getNumber()
                                                .($tblToPersonCustody->getRemark() ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                            break;
                                        case 'Geschäftlich':
                                            if($DataPerson[$TypeName.$Rank.'_PhoneMobileWork']){
                                                $DataPerson[$TypeName.$Rank.'_PhoneMobileWork'] = $DataPerson[$TypeName.$Rank.'_PhoneMobileWork'].', ';
                                            }
                                            $DataPerson[$TypeName.$Rank.'_PhoneMobileWork'] = $DataPerson[$TypeName.$Rank.'_PhoneMobileWork'].$tblPhoneCustody->getNumber()
                                                .($tblToPersonCustody->getRemark() ? ' ('.$tblToPersonCustody->getRemark().')' : '');
                                            break;
                                        case 'Notfall':
                                            if($DataPerson[$TypeName.$Rank.'_PhoneMobileEmergency']){
                                                $DataPerson[$TypeName.$Rank.'_PhoneMobileEmergency'] = ', ';
                                            }
                                            $DataPerson[$TypeName.$Rank.'_PhoneMobileEmergency'] = $DataPerson[$TypeName.$Rank.'_PhoneMobileEmergency'].$tblPhoneCustody->getNumber()
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
                                    $DataPerson[$TypeName.$Rank.'_Mail_Private'] = $tblMailCustody->getAddress();
                                } elseif($tblTypeMailCustody->getName() == 'Geschäftlich') {
                                    $DataPerson[$TypeName.$Rank.'_Mail_Work'] = $tblMailCustody->getAddress();
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
    public function createMetaDataComparisonExcel($Person = null, $Year = null, $Division = null, $Option = null, $PersonGroup = null)
    {

        $Result = $this->getStudentFilterResult($Person, $Year, $Division, $PersonGroup);

        $TableContent = $this->getStudentTableContent($Result, $Option, $PersonGroup);

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());

        $PersonGroupName = '';
        if($PersonGroup[ViewPeopleGroupMember::TBL_GROUP_ID] != '0'
            && $tblPersonGroup = Group::useService()->getGroupById($PersonGroup[ViewPeopleGroupMember::TBL_GROUP_ID])){
            $PersonGroupName = $tblPersonGroup->getName();
        }

        $Row = 0;
        $Column = 0;

        $export->setValue($export->getCell($Column++, $Row), "Klasse");
        $export->setValue($export->getCell($Column++, $Row), "Schülernummer");
        $export->setValue($export->getCell($Column++, $Row), "Vorname");
        $export->setValue($export->getCell($Column++, $Row), "Nachname");
        $export->setValue($export->getCell($Column++, $Row), "Geschlecht");
        $export->setValue($export->getCell($Column++, $Row), "Geburtstag");
        $export->setValue($export->getCell($Column++, $Row), "Geburtsort");
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
        if($PersonGroupName){
            $export->setValue($export->getCell($Column++, $Row), "Personengruppe");
        }

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

            $export->setValue($export->getCell($Column++, $Row), $PersonData['Division']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['StudentNumber']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['FirstName']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['LastName']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Gender']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Birthday']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['BirthPlace']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Nationality']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Street']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['HouseNumber']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['CityCode']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['City']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['District']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Medication']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['InsuranceState']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Insurance']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['Religion']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneFixedPrivate']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneFixedWork']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneFixedEmergency']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneMobilePrivate']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneMobileWork']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['PhoneMobileEmergency']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['MailPrivate']);
            $export->setValue($export->getCell($Column++, $Row), $PersonData['MailWork']);
            if($PersonGroupName){
                $export->setValue($export->getCell($Column++, $Row), $PersonGroupName);
            }

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
     * @param string $GroupName
     * @param int $IsCertificateRelevant
     * @param bool $IsAbsenceOnlineOnly
     *
     * @return false|FilePointer
     */
    public function createAbsenceListExcel(DateTime $dateTimeFrom, DateTime $dateTimeTo = null, $Type = null,
        $DivisionName = '', $GroupName = '', int $IsCertificateRelevant = 0, bool $IsAbsenceOnlineOnly = false)
    {

        if ($Type != null) {
            $tblType = Type::useService()->getTypeById($Type);
        } else {
            $tblType = false;
        }

        switch ($IsCertificateRelevant) {
            case 1: $IsCertificateRelevant = true; break;
            case 2: $IsCertificateRelevant = false; break;
            default: $IsCertificateRelevant = null;
        }

        $isGroup = false;
        $hasAbsenceTypeOptions = false;
        if ($DivisionName != '') {
            $divisionList = Division::useService()->getDivisionAllByName($DivisionName);
            if (!empty($divisionList)) {
                $absenceList = Absence::useService()->getAbsenceAllByDay(
                    $dateTimeFrom,
                    $dateTimeTo,
                    $tblType ? $tblType : null,
                    $divisionList,
                    array(),
                    $hasAbsenceTypeOptions,
                    $IsCertificateRelevant,
                    $IsAbsenceOnlineOnly
                );
            } else {
                $absenceList = array();
            }
        } elseif ($GroupName != '') {
            $isGroup = true;
            $groupList = Group::useService()->getGroupListLike($GroupName);
            if (!empty($groupList)) {
                $absenceList = Absence::useService()->getAbsenceAllByDay(
                    $dateTimeFrom,
                    $dateTimeTo,
                    $tblType ? $tblType : null,
                    array(),
                    $groupList,
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
                $tblType ? $tblType : null,
                array(),
                array(),
                $hasAbsenceTypeOptions,
                $IsCertificateRelevant,
                $IsAbsenceOnlineOnly
            );
        }

//        if (!empty($absenceList)) {
            return $this->createExcelByAbsenceList($dateTimeFrom, $absenceList, $hasAbsenceTypeOptions, $isGroup);
//        }
//        return false;
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     *
     * @return bool|FilePointer
     */
    public function createAbsenceBetweenListExcel(DateTime $startDate, DateTime $endDate)
    {
        $hasAbsenceTypeOptions = false;
        $resultList = [];
        if (($tblAbsenceList = Absence::useService()->getAbsenceAllBetween($startDate, $endDate))) {
            foreach ($tblAbsenceList as $tblAbsence) {
                if (($tblPerson = $tblAbsence->getServiceTblPerson())
                    && ($tblDivision = $tblAbsence->getServiceTblDivision())
                    && ($tblLevel = $tblDivision->getTblLevel())
                    && ($tblType = $tblLevel->getServiceTblType())
                ) {
                    $resultList = Absence::useService()->setAbsenceContent($tblType, $tblDivision, false, [],
                        $tblPerson, $tblAbsence, $resultList);

                    if (!$hasAbsenceTypeOptions) {
                        $hasAbsenceTypeOptions = Absence::useService()->hasAbsenceTypeOptions($tblDivision);
                    }
                }
            }
        }
        return $this->createExcelByAbsenceList($startDate, $resultList, $hasAbsenceTypeOptions, false, $endDate);
    }

    /**
     * @param $absenceList
     * @param $hasAbsenceTypeOptions
     * @param $isGroup
     * @param DateTime $startDate
     * @param DateTime|null $endDate
     *
     * @return FilePointer
     */
    private function createExcelByAbsenceList(
        DateTime $startDate,
        $absenceList = array(),
        $hasAbsenceTypeOptions = false,
        $isGroup = false,
        DateTime $endDate = null
    ): FilePointer {
        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());

        $export->setValue($export->getCell(0, 0),
            'Fehlzeitenübersicht vom ' . $startDate->format('d.m.Y')
            . ($endDate ? ' bis ' . $endDate->format('d.m.Y') : '')
        );

        $column = 0;
        $row = 1;
        $export->setValue($export->getCell($column++, $row), "Schulart");
        $export->setValue($export->getCell($column++, $row), $isGroup ? "Gruppe" : "Klasse");
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
                $export->setValue($export->getCell($column++, $row), $isGroup ? $absence['Group'] : $absence['Division']);
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

        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CLUB));
        $TableContent = array();
        $tblGroupStudent = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
        $tblGroupProspect = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_PROSPECT);
        $tblPersonStudentAll = Group::useService()->getPersonAllByGroup($tblGroupStudent);
        $tblYearList = Term::useService()->getYearByNow();
        if (!empty($tblPersonList)) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$tblPersonStudentAll, $tblYearList, $tblGroupStudent, $tblGroupProspect) {
//                $IsOneRow = true;
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
                        // setze Jahr nach möglichen Interessenten zurück
//                        $Item['Year'] = $tblYear->getYear();
                        $tblPersonStudent = $tblToPerson->getServiceTblPersonTo();

                        if ($tblPersonStudentAll && !empty($tblPersonStudentAll) && $tblPersonStudent) {
                            $tblPersonStudentAll = array_udiff($tblPersonStudentAll, array($tblPersonStudent),
                                function (TblPerson $ObjectA, TblPerson $ObjectB) {
                                    return $ObjectA->getId() - $ObjectB->getId();
                                }
                            );
                        }

                        $Item['StudentFirstName'] = $tblPersonStudent->getFirstSecondName();
                        $Item['StudentLastName'] = $tblPersonStudent->getLastName();
                        $Item['activeDivision'] = '';
                        $Item['Type'] = '';
                        $Item['individualPersonGroup'] = '';
                        if($tblYearList){
                            foreach($tblYearList as $tblYear){
                                if(($tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPersonStudent, $tblYear))){
                                    $Item['activeDivision'] = $tblDivision->getDisplayName();
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
                        // - Nur Schüler aufnehmen, die eine aktuelle Klasse besitzen - old version
                        // Schüler/Interessenten sollen auch ohne Klasse abgebildet werden.

                        if(Group::useService()->getMemberByPersonAndGroup($tblPersonStudent, $tblGroupStudent)){
                            $Item['Type'] = 'Schüler';
                        } else {
                            if(Group::useService()->getMemberByPersonAndGroup($tblPersonStudent, $tblGroupProspect)){
                                if(($tblProspect = Prospect::useService()->getProspectByPerson($tblPersonStudent))){
                                    if(($tblProspectReservation = $tblProspect->getTblProspectReservation())){
                                        $Item['Year'] = $tblProspectReservation->getReservationYear();
                                    }
                                } else {
                                    $Item['Year'] = '';
                                }
                                $Item['Type'] = 'Interessent';
                            }
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
                    $Item['activeDivision'] = '';
                    $Item['Year'] = '';
                    if($tblYearList){
                        foreach($tblYearList as $tblYear){
                            if(($tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPersonStudent, $tblYear))){
                                $Item['activeDivision'] = $tblDivision->getDisplayName();
                                $Item['Year'] = $tblYear->getYear();
                                break;
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
            $export->setValue($export->getCell(8, 0), "Klasse");
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
                $export->setValue($export->getCell(8, $Row), $PersonData['activeDivision']);
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
        if (($tblTransferTypeLeave = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE'))
            && ($tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt'))
        ) {
            foreach ($personList as $item) {
                /** @var TblPerson $tblPerson */
                $tblPerson = $item['tblPerson'];
                /** @var TblDivision $tblDivision */
                $tblDivision = $item['tblDivision'];

                $lastSchool = ($tblCompany = $tblDivision->getServiceTblCompany()) ? $tblCompany->getDisplayName() : '';

                $tblMainAddress = $tblPerson->fetchMainAddress();

                $leaveSchool = '';
                $leaveDate = '';
                if (($tblStudent = $tblPerson->getStudent())
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
                if (($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType))) {
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
                    'LastDivision'          => $tblDivision->getDisplayName(),
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
                $division[$key] = $row['LastDivision'];
                $lastName[$key] = $row['LastName'];
            }
            array_multisort($division, SORT_NATURAL, $lastName, SORT_ASC, $dataList);
        }

        return  $dataList;
    }

    /**
     * @param array $dataList
     *
     * @return bool|FilePointer
     */
    public function createStudentArchiveExcel(array $dataList)
    {
        if (!empty($dataList)) {
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
                $export->setValue($export->getCell($column++, $row), $PersonData['LastDivision']);
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

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param array $Item
     * @return array
     */
    public function getContactDataFromPerson(TblPerson $tblPerson, array $Item): array
    {
        $Item['PhoneFixed'] = '';
        $Item['Phone'] = '';
        $Item['ExcelPhone'] = '';
        $Item['Mail'] = '';
        $Item['ExcelMail'] = '';
        $Item['ExcelMailPrivate'] = '';
        $Item['ExcelMailBusiness'] = '';
        $Item['MailFrontendListFixed'] = '';

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
            $Item['Phone'] = $Item['Phone'] . implode('<br>', $tblPhoneList);
            $Item['PhoneFixed'] = str_replace(' ', '&nbsp', $Item['Phone']);
            $Item['ExcelPhone'] = $tblPhoneList;
        }
        if (!empty($tblPhoneListFixed)) {
            ksort($tblPhoneListFixed);
            $Item['PhoneFixed'] = $Item['PhoneFixed'] . implode('<br>', $tblPhoneListFixed);
        }
        #var_dump($tblMailFrontendListFixed);
        // Insert MailList
        if (!empty($tblMailList)) {
            ksort($tblMailList);
            $Item['ExcelMail'] = $tblMailList;
            $Item['Mail'] = $Item['Mail'] . implode('<br>', $tblMailFrontendList);

            if (!empty($tblMailList)) {
                ksort($tblMailList);
                $Item['ExcelMail'] = $tblMailList;
                $Item['MailFrontendListFixed'] = $Item['MailFrontendListFixed'] . implode('<br>', $tblMailFrontendListFixed);
            }
            if (!empty($mailPrivateList)) {
                ksort($mailPrivateList);
                $Item['ExcelMailPrivate'] = implode('; ', $mailPrivateList);
            }
            if (!empty($mailBusinessList)) {
                ksort($mailBusinessList);
                $Item['ExcelMailBusiness'] = implode('; ', $mailBusinessList);
            }
        }
        return $Item;
    }
    /**
     * @param $tblPersonList
     * @param TblDivision|null $tblDivision
     *
     * @return array
     */
    public function createAbsenceContentList($tblPersonList, TblDivision $tblDivision = null): array
    {
        $dataList = array();
        if($tblPersonList){
            foreach($tblPersonList as $tblPerson) {
                if ($tblDivision) {
                    $tblStudentDivision = $tblDivision;
                } else {
                    $tblStudentDivision = false;
                }
                $birthday = '';
                if(($tblCommon = Common::useService()->getCommonByPerson($tblPerson))){
                    if($tblCommon->getTblCommonBirthDates()){
                        $birthday = $tblCommon->getTblCommonBirthDates()->getBirthday();
                    }
                }

                if (!$tblStudentDivision) {
                    $tblStudentDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson);
                }
                $course = '';
                if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){

                    $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                    if($tblTransferType){
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                            $tblTransferType);
                        if($tblStudentTransfer){
                            $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                            if($tblCourse){
                                $course = $tblCourse->getName();
                            }
                        }
                    }
                }

                // Fehlzeiten
                $unExcusedLessons = 0;
                $excusedLessons = 0;
                $unExcusedDays = 0;
                $excusedDays = 0;
                if (($tblStudentDivision)) {
                    $excusedDays = Absence::useService()->getExcusedDaysByPerson($tblPerson, $tblStudentDivision, null,
                        $excusedLessons);
                    $unExcusedDays = Absence::useService()->getUnexcusedDaysByPerson($tblPerson, $tblStudentDivision, null,
                        $unExcusedLessons);
                }

                $dataList[] = array(
                    'Number'           => (count($dataList) + 1),
                    'LastName'         => $tblPerson->getLastName(),
                    'FirstName'        => $tblPerson->getFirstName(),
                    'Birthday'         => $birthday,
                    'Course'           => $course,
                    'ExcusedDays'      => $excusedDays,
                    'unExcusedDays'    => $unExcusedDays,
                    'ExcusedLessons'   => $excusedLessons,
                    'unExcusedLessons' => $unExcusedLessons
                );
            }
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
     * @param array $PersonList
     * @param array $dataList
     *
     * @return bool|FilePointer
     */
    public function createAbsenceContentExcelMonthly(array $PersonList, array $dataList, array $countList, TblYear $tblYear): ?FilePointer
    {
        $totalCountList = array();
        if (!empty($PersonList)) {
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
                    foreach ($PersonList as $tblPerson) {
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
                if (($tblDivisionList = Division::useService()->getDivisionByYear($tblYear))) {
                    $tblDivisionList = $this->getSorter($tblDivisionList)->sortObjectBy('DisplayName', new StringNaturalOrderSorter());
                    foreach ($tblDivisionList as $tblDivision) {
                        $item = array();
                        $item['Division'] = $tblDivision->getDisplayName();
                        $TeacherColumn = 0;
                        if (($tblDivisionTeacherList = Division::useService()->getDivisionTeacherAllByDivision($tblDivision))){
                            foreach($tblDivisionTeacherList as $tblDivisionTeacher) {
                                $TeacherColumn++;
                                $item['DivisionTeacher'.$TeacherColumn.'FirstName'] = $tblDivisionTeacher->getServiceTblPerson()->getFirstName();
                                $item['DivisionTeacher'.$TeacherColumn.'Name'] = $tblDivisionTeacher->getServiceTblPerson()->getLastName();
                            }
                            if ($TeacherColumn > $maxCountTeacher){
                                $maxCountTeacher = $TeacherColumn;
                            }
                        }
                        $CustodyColumn = 0;
                        if (($tblDivisionCustodyList = Division::useService()->getDivisionCustodyAllByDivision($tblDivision))){
                            foreach($tblDivisionCustodyList as $tblDivisionCustody) {
                                $CustodyColumn++;
                                $item['DivisionCustody'.$CustodyColumn.'FirstName'] = $tblDivisionCustody->getServiceTblPerson()->getFirstName();
                                $item['DivisionCustody'.$CustodyColumn.'Name'] = $tblDivisionCustody->getServiceTblPerson()->getLastName();
                            }
                            if ($CustodyColumn > $maxCountCustody){
                                $maxCountCustody = $CustodyColumn;
                            }
                        }
                        $RepresentativeColumn = 0;
                        if (($tblDivisionRepresentativeList = Division::useService()->getDivisionRepresentativeByDivision($tblDivision))){
                            foreach($tblDivisionRepresentativeList as $tblDivisionRepresentative) {
                                $RepresentativeColumn++;
                                $item['DivisionRepresentative'.$RepresentativeColumn.'FirstName'] = $tblDivisionRepresentative->getServiceTblPerson()->getFirstName();
                                $item['DivisionRepresentative'.$RepresentativeColumn.'Name'] = $tblDivisionRepresentative->getServiceTblPerson()->getLastName();
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

        $headers['Division'] = 'Klasse';
        for ($i = 1; $i <= $maxCountTeacher; $i++){
            $headers['DivisionTeacher'.$i.'FirstName'] = 'Klassenlehrer&nbsp;'.$i.' - Vorname';
            $headers['DivisionTeacher'.$i.'Name'] = 'Klassenlehrer&nbsp;'.$i.' - Nachname';
        }
        for ($l = 1; $l <= $maxCountRepresentative; $l++){
            $headers['DivisionRepresentative'.$l.'FirstName'] = 'Klassensprecher&nbsp;'.$l.' - Vorname';
            $headers['DivisionRepresentative'.$l.'Name'] = 'Klassensprecher&nbsp;'.$l.' Nachname';
        }
        for ($j = 1; $j <= $maxCountCustody; $j++){
            $headers['DivisionCustody'.$j.'FirstName'] = 'Elternvertreter&nbsp;'.$j.' - Vorname';
            $headers['DivisionCustody'.$j.'Name'] = 'Elternvertreter&nbsp;'.$j.' - Nachname';
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
