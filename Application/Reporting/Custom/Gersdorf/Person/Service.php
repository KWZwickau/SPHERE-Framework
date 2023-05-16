<?php
namespace SPHERE\Application\Reporting\Custom\Gersdorf\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType as TblTypeRelationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Reporting\Standard\Person\Person;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Custom\Gersdorf\Person
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
        if (($tblPersonList = $tblDivisionCourse->getStudents())) {
            $tblConsumer = Consumer::useService()->getConsumerBySession();
            // Header (Excel)
            $item = $this->getExcelHead($tblDivisionCourse, $tblConsumer);
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count, $item) {
                // Content
                $item['Count'] = $count++;
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = '';
                $item['Address'] = $item['AddressExcel'] = '';
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['Birthplace'] = $tblPerson->getBirthplaceString();
                $item = Person::useService()->getAddressDataFromPerson($tblPerson, $item);
                if(($tblAddress = Address::useService()->getAddressByPerson($tblPerson))) {
                    $item['AddressExcel'] = $tblAddress->getGuiString(false);
                    // if Address to long
                    if(strlen($item['AddressExcel']) > 41){
                        $item['AddressExcel'] = str_replace("<br>", "\n", $item['AddressExcel']);
                    }
                }
                array_push($TableContent, $item);
            });
        }
        return $TableContent;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblConsumer|bool  $tblConsumer
     *
     * @return array
     */
    private function getExcelHead(TblDivisionCourse $tblDivisionCourse, $tblConsumer = false):array
    {
        $item = array();
        $item['Division'] = $tblDivisionCourse->getDisplayName();
        $item['Consumer'] = '';
        $item['DivisionYear'] = '';
        $item['DivisionTeacher'] = '';
        if($tblConsumer) {
            $item['Consumer'] = $tblConsumer->getName();
        }
        if(($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            $item['DivisionYear'] = $tblYear->getDisplayName(false);
        }
        if(($tblPersonTeacherList = $tblDivisionCourse->getDivisionTeacherList())){
            $teacherList = array();
            foreach ($tblPersonTeacherList as $tblPersonTeacher) {
                $teacherList[] = $tblPersonTeacher->getSalutation().' '.$tblPersonTeacher->getLastName();
            }
            if(!empty($teacherList)){
                $item['DivisionTeacher'] = implode(', ', $teacherList);
            }
        }
        return $item;
    }

    /**
     * @param array $TableContent
     * @param array $tblPersonList
     *
     * @return FilePointer
     */
    public function createClassListExcel(array $TableContent, array $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = 0;
        $row = 3;
        $export->setValue($export->getCell($column++, $row), "Nr.");
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Vorname");
        $export->setValue($export->getCell($column++, $row), "Geb.-Tag");
        $export->setValue($export->getCell($column++, $row), "Geburtsort");
        $export->setValue($export->getCell($column, $row++), "Wohnanschrift");
        //Settings Header
        $export = $this->setHeader($export, 5);
        foreach ($TableContent as $PersonData) {
            // Fill Header
            if ($row == 4) {
                $this->fillHeader($export, $PersonData, ' - Klassenliste', 5);
            }
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Count']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthday']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthplace']);
            $export->setValue($export->getCell($column, $row++), $PersonData['AddressExcel']);
        }

        // TableBorder
        $export->setStyle($export->getCell(0, 4), $export->getCell(5, ($row - 1)))->setBorderAll()->setWrapText()->setAlignmentMiddle();
        $column = 0;
        // Spaltenbreite
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(3);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(16);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(16);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(11);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(15);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(37);
        // Center
        $export->setStyle($export->getCell(0, 3), $export->getCell(0, ($row - 1)))->setAlignmentCenter();
        $row ++;
        Person::setGenderFooter($export, $tblPersonList, $row, 1);
        $export->setPagePrintMargin('0.4', '0.4', '0.4', '0.4');
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createSignList(TblDivisionCourse $tblDivisionCourse)
    {

        $TableContent = array();
        $tblConsumer = Consumer::useService()->getConsumerBySession();
        if(($tblPersonList = $tblDivisionCourse->getStudents())) {
            // Header (Excel)
            $item = $this->getExcelHead($tblDivisionCourse, $tblConsumer);
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count, $item) {
                // Content
                $item['Count'] = $count++;
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
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
    public function createSignListExcel(array $TableContent, array $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = 0;
        $row = 3;
        $export->setValue($export->getCell($column++, $row), "lfdNr.");
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column, $row++), "Vorname");
        // Settings Header
        $export = $this->setHeader($export, 4);
        foreach ($TableContent as $PersonData) {
            // Fill Header
            if ($row == 4) {
                $this->fillHeader($export, $PersonData, ' - Unterschriften Liste', 4);
            }
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Count']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column, $row++), $PersonData['FirstName']);
        }
        // TableBorder
        $export->setStyle($export->getCell(0, 4), $export->getCell(4, ($row - 1)))->setBorderAll()->setRowHeight('23')->setWrapText()->setAlignmentMiddle();
        // Spaltenbreite
        $column = 0;
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(6);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(21);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(21);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(25);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(25);
        // Center
        $export->setStyle($export->getCell(0, 4), $export->getCell(0, ($row - 1)))->setAlignmentCenter();
        $row++;
        Person::setGenderFooter($export, $tblPersonList, $row, 1);
        $export->setPagePrintMargin('0.4', '0.4', '0.4', '0.4');
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

        $TableContent = array();
        if (($tblPersonList = $tblDivisionCourse->getStudents())) {
            $count = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $tblDivisionCourse, &$count) {
                $item['Count'] = $count++;
                $item['Name'] = $tblPerson->getLastFirstName();
                $item['Education'] = '';
                $item['ForeignLanguage1'] = $item['ForeignLanguage2'] = $item['ForeignLanguage3'] = '';
                $item['Profile'] = '';
                $item['Orientation'] = '';
                $item['Religion'] = '';
                $item['Elective'] = '';
                $item['ExcelElective'] = array();
                $item['Elective1'] = $item['Elective2'] = $item['Elective3'] = $item['Elective4'] = $item['Elective5'] = '';
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                // NK/Profil
                if ($tblStudent
                && ($tblYear = $tblDivisionCourse->getServiceTblYear())
                && $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear)) {
                    $level = $tblStudentEducation->getLevel();
                    for ($i = 1; $i <= 3; $i++) {
                        $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                        $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($i);
                        $tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking(
                            $tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking);
                        if ($tblStudentSubject && ($tblSubject = $tblStudentSubject->getServiceTblSubject()) && $level) {
                            if($this->getIsSetValue($level, $tblStudentSubject->getLevelFrom(), $tblStudentSubject->getLevelTill())){
                                $item['ForeignLanguage'. $i] = $tblSubject->getAcronym();
                                $item['ForeignLanguage'. $i.'Id'] = $tblSubject->getId();
                            }
                        }
                    }
                    // Neigungskurs
                    $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, 'ORIENTATION');
                    if ($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                        $item['Orientation'] = $tblSubject->getAcronym();
                        $item['OrientationId'] = $tblSubject->getId();
                    }
                    // Religion
                    $tblStudentOrientation = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, 'RELIGION');
                    if ($tblStudentOrientation && ($tblSubject = $tblStudentOrientation[0]->getServiceTblSubject())) {
                        $item['Religion'] = $tblSubject->getAcronym();
                        $item['ReligionId'] = $tblSubject->getId();
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
                    $ElectiveList = array();
                    if(($tblStudentElectiveList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, 'ELECTIVE'))) {
                        foreach ($tblStudentElectiveList as $tblStudentElective) {
                            if(($tblSubject = $tblStudentElective->getServiceTblSubject())
                            && ($tblSubjectRanking = $tblStudentElective->getTblStudentSubjectRanking())) {
                                $ElectiveList[$tblSubjectRanking->getIdentifier()] = $tblSubject->getAcronym();
                                switch ($tblSubjectRanking->getIdentifier()) {
                                    case 1:
                                        $item['Elective1'] = $tblSubject->getAcronym();
                                        $item['Elective1Id'] = $tblSubject->getId();
                                        break;
                                    case 2:
                                        $item['Elective2'] = $tblSubject->getAcronym();
                                        $item['Elective2Id'] = $tblSubject->getId();
                                        break;
                                    case 3:
                                        $item['Elective3'] = $tblSubject->getAcronym();
                                        $item['Elective3Id'] = $tblSubject->getId();
                                        break;
                                    case 4:
                                        $item['Elective4'] = $tblSubject->getAcronym();
                                        $item['Elective4Id'] = $tblSubject->getId();
                                        break;
                                    case 5:
                                        $item['Elective5'] = $tblSubject->getAcronym();
                                        $item['Elective5Id'] = $tblSubject->getId();
                                        break;
                                }
                            }
                        }
                        if(!empty($ElectiveList)) {
                            ksort($ElectiveList);
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
     * @param string $level
     * @param null|string $levelFrom
     * @param null|string $levelTill
     *
     * @return bool
     */
    private function getIsSetValue(string $level = '', ?string $levelFrom = null, ?string $levelTill = null)
    {
        $isSetValue = false;
        if($levelFrom && floatval($levelFrom) <= floatval($level)
            && $levelTill && floatval($levelTill) >= floatval($level)) {
            $isSetValue = true;
        } elseif($levelFrom && !$levelTill && floatval($levelFrom) <= floatval($level)) {
            $isSetValue = true;
        } elseif($levelTill && !$levelFrom && floatval($levelTill) >= floatval($level)) {
            $isSetValue = true;
        } elseif(!$levelFrom && !$levelTill) {
            $isSetValue = true;
        }
        return $isSetValue;
    }

    /**
     * @param array             $TableContent
     * @param array             $tblPersonList
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return FilePointer
     */
    public function createElectiveClassListExcel(array $TableContent, array $tblPersonList, TblDivisionCourse $tblDivisionCourse)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $teacherList = array();
        if(($tblPersonTeacherList = $tblDivisionCourse->getDivisionTeacherList())){
            foreach ($tblPersonTeacherList as $tblPersonTeacher) {
                $teacherList[] = trim($tblPersonTeacher->getFullNameWithoutFirstName());
            }
        }
        $column = $row = 0;
        $export->setStyle($export->getCell($column++, $row), $export->getCell(7, 0))->setFontBold();
        $export->setValue($export->getCell($column, $row++), "Klasse ".$tblDivisionCourse->getDisplayName().(empty($teacherList) ? '' : ' '.implode(', ', $teacherList)));
        // Header
        $column = 0;
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Bg");
        $export->setValue($export->getCell($column++, $row), "FS 1");
        $export->setValue($export->getCell($column++, $row), "FS 2");
        $export->setValue($export->getCell($column++, $row), "FS 3");
        $export->setValue($export->getCell($column++, $row), "WB");
        $export->setValue($export->getCell($column++, $row), "Rel.");
        $export->setValue($export->getCell($column++, $row), "WF 1-5");
        $export->setValue($export->getCell($column++, $row), "WF 1");
        $export->setValue($export->getCell($column++, $row), "WF 2");
        $export->setValue($export->getCell($column++, $row), "WF 3");
        $export->setValue($export->getCell($column++, $row), "WF 4");
        $export->setValue($export->getCell($column, $row), "WF 5");
        // Header bold
        $export->setStyle($export->getCell(0, 1), $export->getCell($column, $row++))->setFontBold();
        // Zählung
        $countList = $this->getSubjectCount($TableContent);
        foreach ($TableContent as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Name']);
//                $export->setValue($export->getCell(1, $Row), $PersonData['Birthday']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Education']);
            $export->setValue($export->getCell($column++, $row), $PersonData['ForeignLanguage1']);
            $export->setValue($export->getCell($column++, $row), $PersonData['ForeignLanguage2']);
            $export->setValue($export->getCell($column++, $row), $PersonData['ForeignLanguage3']);
//                $export->setValue($export->getCell(6, $Row), $PersonData['Profile']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Orientation']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Religion']);
            if(!empty($PersonData['ExcelElective'])){
                $export->setValue($export->getCell($column, $row), implode(', ', $PersonData['ExcelElective']));
            }
            $column++;
            $export->setValue($export->getCell($column++, $row), $PersonData['Elective1']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Elective2']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Elective3']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Elective4']);
            $export->setValue($export->getCell($column, $row++), $PersonData['Elective5']);
        }
        $export->setStyle($export->getCell(0, 1), $export->getCell($column, ($row - 1)))->setBorderAll()->setWrapText()->setAlignmentMiddle();
        // Personenanzahl
        $row++;
        $RowReference = $RowReference2 = $row;
        Person::setGenderFooter($export, $tblPersonList, $row);
        // Stand
        $row += 2;
        $export->setValue($export->getCell(0, $row), 'Stand: ' . (new \DateTime())->format('d.m.Y'));

        foreach($countList as $Type => $SubjectList){
            foreach($SubjectList as $SubjectId => $count){
                if(!($Type == 'Elective')){
                    // Spalte 1
                    $j = 3;
                    $tblSubject = Subject::useService()->getSubjectById($SubjectId);
                    $export->setValue($export->getCell($j, $RowReference), $count);
                    $export->setStyle($export->getCell($j++, $RowReference))->setCellType(\PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $export->setValue($export->getCell($j++, $RowReference), $tblSubject->getAcronym());
                    $export->setValue($export->getCell($j, $RowReference++), $tblSubject->getName());
                } else {
                    // Spalte 2
                    $j = 8;
                    $tblSubject = Subject::useService()->getSubjectById($SubjectId);
                    $export->setValue($export->getCell($j, $RowReference2), $count);
                    $export->setStyle($export->getCell($j++, $RowReference2))->setCellType(\PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $export->setValue($export->getCell($j++, $RowReference2), $tblSubject->getAcronym());
                    $export->setValue($export->getCell($j, $RowReference2++), $tblSubject->getName());
                }
            }
        }
        // Spaltenbreite
        $column = 0;
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(27);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(7);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(8);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(8);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(8);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(7);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(7);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(17);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(8);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(8);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(8);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(8);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(8);
        $export->setPagePrintMargin('0.4', '0.4', '0.4', '0.4');
        $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param $TableContent
     *
     * @return array
     */
    public function getSubjectCount($TableContent = array())
    {
        $countList = array();
        foreach($TableContent as $PersonData){
            // Fremdsprachen
            for($i = 1; $i <= 3; $i++){
                if(isset($PersonData['ForeignLanguage'. $i.'Id'])){
                    if(!isset($countList['ForeignLanguage'][$PersonData['ForeignLanguage'. $i.'Id']])){
                        $countList['ForeignLanguage'][$PersonData['ForeignLanguage'. $i.'Id']] = 1;
                    } else {
                        $countList['ForeignLanguage'][$PersonData['ForeignLanguage'. $i.'Id']] = $countList['ForeignLanguage'][$PersonData['ForeignLanguage'. $i.'Id']] + 1;
                    }
                }
            }
            // Wahlbereich
            if(isset($PersonData['OrientationId'])){
                if(!isset($countList['Orientation'][$PersonData['OrientationId']])){
                    $countList['Orientation'][$PersonData['OrientationId']] = 1;
                } else {
                    $countList['Orientation'][$PersonData['OrientationId']] = $countList['Orientation'][$PersonData['OrientationId']] + 1;
                }
            }
            // Religion
            if(isset($PersonData['ReligionId'])){
                if(!isset($countList['Religion'][$PersonData['ReligionId']])){
                    $countList['Religion'][$PersonData['ReligionId']] = 1;
                } else {
                    $countList['Religion'][$PersonData['ReligionId']] = $countList['Religion'][$PersonData['ReligionId']] + 1;
                }
            }

            // Fremdsprachen
            for($i = 1; $i <= 5; $i++){
                if(isset($PersonData['Elective'. $i.'Id'])){
                    if(!isset($countList['Elective'][$PersonData['Elective'. $i.'Id']])){
                        $countList['Elective'][$PersonData['Elective'. $i.'Id']] = 1;
                    } else {
                        $countList['Elective'][$PersonData['Elective'. $i.'Id']] = $countList['Elective'][$PersonData['Elective'. $i.'Id']] + 1;
                    }
                }
            }
        }

        return $countList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createClassPhoneList(TblDivisionCourse $tblDivisionCourse)
    {

        $TableContent = array();
        if (($tblPersonList = $tblDivisionCourse->getStudents())) {
            $tblConsumer = Consumer::useService()->getConsumerBySession();
            $count = 1;
            // Header (Excel)
            $item = $this->getExcelHead($tblDivisionCourse, $tblConsumer);
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count, $item) {
                $item['Count'] = $count++;
                $item['Name'] = $tblPerson->getLastFirstName();
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['PhoneNumbers'] = '';
                $item['ExcelPhoneNumbers'] = '';
                $item['S1Private'] = '';
                $item['S1ExcelPrivate'] = '';
                $item['S1Business'] = '';
                $item['S1ExcelBusiness'] = '';
                $item['S2Private'] = '';
                $item['S2ExcelPrivate'] = '';
                $item['S2Business'] = '';
                $item['S2ExcelBusiness'] = '';

                // Parent's
                $PhoneParent = array();
                if (($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, TblTypeRelationship::IDENTIFIER_GUARDIAN))) {
                    foreach ($tblToPersonList as $tblToPerson) {
                        $tblPersonParent = $tblToPerson->getServiceTblPersonFrom();
                        if($tblPersonParent){
                            // PhoneNumbers
                            $PhoneParent[$tblToPerson->getRanking()][] = array();
                            $phoneList = Phone::useService()->getPhoneAllByPerson($tblPersonParent);
                            if ($phoneList) {
                                foreach ($phoneList as $phone) {
                                    $PhoneParent[$tblToPerson->getRanking()][$phone->getTblType()->getName()][] = $phone->getTblPhone()->getNumber();
                                }
                            }
                        }
                    }
                }
                if(!empty($PhoneParent)){
                    foreach($PhoneParent as $Ranking => $PhoneTypeList){
                        if($Ranking == 1){
                            foreach($PhoneTypeList as $Type => $PhoneList){
                                if($Type){
                                    if($Type == TblType::VALUE_NAME_PRIVATE){
                                        $item['S1Private'] = implode(', ', $PhoneList);
                                        $item['S1ExcelPrivate'] = $PhoneList;
                                    }elseif($Type == TblType::VALUE_NAME_BUSINESS){
                                        $item['S1Business'] = implode(', ', $PhoneList);
                                        $item['S1ExcelBusiness'] = $PhoneList;
                                    }
                                }
                            }
                        } elseif($Ranking == 2){
                            foreach($PhoneTypeList as $Type => $PhoneList){
                                if($Type){
                                    if($Type == TblType::VALUE_NAME_PRIVATE){
                                        $item['S2Private'] = implode(', ', $PhoneList);
                                        $item['S2ExcelPrivate'] = $PhoneList;
                                    } elseif($Type == TblType::VALUE_NAME_BUSINESS) {
                                        $item['S2Business'] = implode(', ', $PhoneList);
                                        $item['S2ExcelBusiness'] = $PhoneList;
                                    }
                                }
                            }
                        }
                    }
                }
                $item['PhoneParent'] = $PhoneParent;
                // PhoneNumbers
                $phoneNumbers = array();
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        $phoneNumbers[] = $phone->getTblPhone()->getNumber();
                    }
                }
                if(!empty($phoneNumbers)){
                    $item['PhoneNumbers'] = implode(', ', $phoneNumbers);
                    $item['ExcelPhoneNumbers'] = $phoneNumbers;
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
     * @return bool|\SPHERE\Application\Document\Storage\FilePointer
     */
    public function createClassPhoneListExcel(array $TableContent, array $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export = $this->setHeader($export, 6);
        //TableHead
        $export->setValue($export->getCell(0, 3), "Nr.");
        $export->setValue($export->getCell(1, 3), "Name");
        $export->setValue($export->getCell(2, 3), "Telefon Schüler");
        $export->setValue($export->getCell(3, 3), "Sorgeberechtigter 1 (Vater)");
        $export->setValue($export->getCell(5, 3), "Sorgeberechtigte 2 (Mutter)");
        $export->setValue($export->getCell(3, 4), "privat");
        $export->setValue($export->getCell(4, 4), "geschäftlich");
        $export->setValue($export->getCell(5, 4), "privat");
        $export->setValue($export->getCell(6, 4), "geschäftlich");
        $export->setStyle($export->getCell(0, 3), $export->getCell(0, 4))->mergeCells();
        $export->setStyle($export->getCell(1, 3), $export->getCell(1, 4))->mergeCells();
        $export->setStyle($export->getCell(2, 3), $export->getCell(2, 4))->mergeCells();
        $export->setStyle($export->getCell(0, 3), $export->getCell(6, 4))->setBorderAll()->setBorderBottom(2)->setFontBold();
        $row = 5;
        foreach ($TableContent as $PersonData) {
            if(isset($PersonData['ExcelPhoneNumbers']) && !empty($PersonData['ExcelPhoneNumbers'])){
                $ExcelPhoneNumbers = implode("\n", $PersonData['ExcelPhoneNumbers']);
            } else {
                $ExcelPhoneNumbers = '';
            }
            if(isset($PersonData['S1ExcelPrivate']) && !empty($PersonData['S1ExcelPrivate'])){
                $S1ExcelPrivate = implode("\n", $PersonData['S1ExcelPrivate']);
            } else {
                $S1ExcelPrivate = '';
            }
            if(isset($PersonData['S1ExcelBusiness']) && !empty($PersonData['S1ExcelBusiness'])){
                $S1ExcelBusiness = implode("\n", $PersonData['S1ExcelBusiness']);
            } else {
                $S1ExcelBusiness = '';
            }
            if(isset($PersonData['S2ExcelPrivate']) && !empty($PersonData['S2ExcelPrivate'])){
                $S2ExcelPrivate = implode("\n", $PersonData['S2ExcelPrivate']);
            } else {
                $S2ExcelPrivate = '';
            }
            if(isset($PersonData['S2ExcelBusiness']) && !empty($PersonData['S2ExcelBusiness'])){
                $S2ExcelBusiness = implode("\n", $PersonData['S2ExcelBusiness']);
            } else {
                $S2ExcelBusiness = '';
            }
            // Fill Header
            if ($row == 5) {
                $this->fillHeader($export, $PersonData, ' - Telefon Liste', 6);
            }
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Count']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Name']);
            $export->setValue($export->getCell($column++, $row), $ExcelPhoneNumbers);
            $export->setValue($export->getCell($column++, $row), $S1ExcelPrivate);
            $export->setValue($export->getCell($column++, $row), $S1ExcelBusiness);
            $export->setValue($export->getCell($column++, $row), $S2ExcelPrivate);
            $export->setValue($export->getCell($column, $row++), $S2ExcelBusiness);
        }
        // Spaltenbreite
        $column = 0;
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(4);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(26);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(20);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(20);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(20);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(20);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(20);
        // Center
        $export->setStyle($export->getCell(0, 5), $export->getCell(6, ($row - 1)))->setBorderAll()->setWrapText()->setAlignmentMiddle();
        $export->setPagePrintMargin('0.4', '0.4', '0.4', '0.4');
        $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @return array
     */
    public function createTeacherList($isTeacher = false)
    {

        $tblPersonList = $this->getPersonStaffList($isTeacher);
        $TableContent = array();
        if (!empty( $tblPersonList )) {
            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
            $tblConsumer = Consumer::useService()->getConsumerBySession();
            $count = 1;
            $tblGroupTeacher = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER);
            $tblGroupStaff = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF);
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$count, $tblGroupTeacher, $tblGroupStaff, $tblConsumer) {
                $item['Count'] = $count++;
                $item['Name'] = ($tblPerson->getTitle() ? $tblPerson->getTitle().' ' : '').$tblPerson->getLastFirstName();
                $item['FirstName'] = $tblPerson->getFirstName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['Consumer'] = '';
                if ($tblConsumer) {
                    $item['Consumer'] = $tblConsumer->getName();
                }
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['Gender'] = '';
                $item['Address'] = $item['Street'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = '';
                $item['Group'] = '';
                $item['Phone'] = '';
                $item['PhoneList'] = array();
                $item = Person::useService()->getAddressDataFromPerson($tblPerson, $item);
                if(($tblGender = $tblPerson->getGender())){
                    $item['Gender'] = $tblGender->getShortName();
                }
                if(($tblToPersonList = Phone::useService()->getPhoneAllByPerson($tblPerson))){
                    foreach($tblToPersonList as $tblToPerson){
                        $tblPhone = $tblToPerson->getTblPhone();
                        $item['PhoneList'][] = $tblPhone->getNumber();
                    }
                    if(!empty($item['PhoneList'])){
                        $item['Phone'] = implode(', ', $item['PhoneList']);
                    }
                }
                if(Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroupTeacher)
                && Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroupStaff)){
                    $item['Group'] = 'Mitarbeiter, Lehrer';
                } elseif(Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroupStaff)){
                    $item['Group'] = 'Mitarbeiter';
                } elseif(Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroupTeacher)) {
                    $item['Group'] = 'Lehrer';
                }
                array_push($TableContent, $item);
            });
        }

        return $TableContent;
    }

    /**
     * @param $PersonList
     * @param $tblPersonList
     *
     * @return bool|FilePointer
     */
    public function createTeacherListExcel($PersonList, $tblPersonList, $isTeacher = false)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        // Tabellenkopf auf jeder A4 Seite wiederholen
        /*start column and end column*/
        $StartEnd = [1,4];
        $export->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTop($StartEnd);
        //Settings Header
        $export = $this->setHeader($export, 6);
        // Fill Header
        $row = 0;
        if($isTeacher){
            $export->setValue($export->getCell(0, $row++), 'Lehrerliste - Adressen, Telefon, Geburtstag');
        } else {
            $export->setValue($export->getCell(0, $row++), 'Mitarbeiter & Lehrerliste - Adressen, Telefon, Geburtstag');
        }
        $export->setValue($export->getCell(0, $row++), 'Christlicher Schulverein e.V.');
        $export->setValue($export->getCell(0, $row), 'Evangelische Oberschule Gersdorf staatlich anerkannte Ersatzschule');
        $Year = '';
        if($tblYearList = Term::useService()->getYearByNow()){
            $tblYear = current($tblYearList);
            $Year = $tblYear->getYear();
        }
        $export->setValue($export->getCell(4, $row), $Year);
        $export->setValue($export->getCell(6, $row++), (new \DateTime('now'))->format('d.m.Y'));
        $column = 0;
        $export->setValue($export->getCell($column++, $row), "Nr.");
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Vorname");
        $export->setValue($export->getCell($column++, $row), "G");
        $export->setValue($export->getCell($column++, $row), "Anschrift");
        $export->setValue($export->getCell($column++, $row), "Telefon");
        $export->setValue($export->getCell($column, $row++), "Geb.-Tag");
        $rowCount = $row;
        foreach ($PersonList as $PersonData) {
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Count']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Gender']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Address']);
            if(!empty($PersonData['PhoneList'])){
                foreach($PersonData['PhoneList'] as $Phone){
                    $export->setValue($export->getCell($column, $rowCount), $Phone);
                    $rowCount++;
                }
            } else {
                $rowCount++;
            }
            if(($rowCount - $row) > 1){
                // number merge
                $export->setStyle($export->getCell(($column - 5), $row), $export->getCell(($column - 5), ($rowCount - 1)))->mergeCells();
                // LastName merge
                $export->setStyle($export->getCell(($column - 4), $row), $export->getCell(($column - 4), ($rowCount - 1)))->mergeCells();
                // FirstName merge
                $export->setStyle($export->getCell(($column - 3), $row), $export->getCell(($column - 3), ($rowCount - 1)))->mergeCells();
                // gender merge
                $export->setStyle($export->getCell(($column - 2), $row), $export->getCell(($column - 2), ($rowCount - 1)))->mergeCells();
                // address merge
                $export->setStyle($export->getCell(($column - 1), $row), $export->getCell(($column - 1), ($rowCount - 1)))->mergeCells();
                // birthday merge
                $export->setStyle($export->getCell(($column + 1), $row), $export->getCell(($column + 1), ($rowCount - 1)))->mergeCells();
            }
            $column++;
            $export->setValue($export->getCell($column, $row), $PersonData['Birthday']);
            // Border per RowCount
            $export->setStyle($export->getCell(0, $row), $export->getCell($column, $rowCount -1))
                ->setBorderOutline()
                ->setBorderVertical()
                ->setWrapText()
                ->setAlignmentMiddle();
            $row = $rowCount;
        }
        $column = 0;
        // Spaltenbreite
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(3);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(13);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(13);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(3);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(38);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(14);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(11);
        // Center
        $export->setStyle($export->getCell(0, 3), $export->getCell(0, $row))->setAlignmentCenter();
        $export->setStyle($export->getCell(3, 3), $export->getCell(3, $row))->setAlignmentCenter();
        $export->setStyle($export->getCell(5, 3), $export->getCell(5, $row))->setAlignmentCenter();
        $export->setStyle($export->getCell(6, 3), $export->getCell(6, $row))->setAlignmentCenter();
        $row += 2;
        Person::setGenderFooter($export, $tblPersonList, $row, 1);
        $export->setPagePrintMargin('0.4', '0.4', '0.4', '0.4');
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param bool $isTeacher
     *
     * @return array|TblPerson[]
     */
    public function getPersonStaffList(bool $isTeacher = false): array
    {
        $tblPersonList = array();
        $tblGroupTeacher = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER);
        $tblGroupStaff = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF);
        if($isTeacher){
            $tblPersonList = $tblGroupTeacher->getPersonList();
        } else {
            if(!empty($tblPersonTList = $tblGroupTeacher->getPersonList())){
                foreach($tblPersonTList as $tblPersonT){
                    $tblPersonList[$tblPersonT->getId()] = $tblPersonT;
                }
            }
            if(!empty($tblPersonSList = $tblGroupStaff->getPersonList())){
                foreach($tblPersonSList as $tblPersonS){
                    $tblPersonList[$tblPersonS->getId()] = $tblPersonS;
                }
            }
        }
        return $tblPersonList;
    }

    /**
     * @param PhpExcel $export
     * @param int      $lastColumn
     *
     * @return PhpExcel
     */
    private function setHeader(PhpExcel $export, int $lastColumn): PhpExcel
    {

        // Merge & Style
        $export->setStyle($export->getCell(0, 0), $export->getCell($lastColumn, 0))
            ->mergeCells()
            ->setFontSize(18)
            ->setFontBold()
            ->setAlignmentCenter();
        $export->setStyle($export->getCell(0, 1), $export->getCell($lastColumn, 1))
            ->mergeCells()
            ->setFontSize(14)
            ->setBorderOutline()
            ->setAlignmentCenter();
        $export->setStyle($export->getCell(0, 2), $export->getCell($lastColumn - 2, 2))
            ->mergeCells()
            ->setAlignmentCenter();
        $export->setStyle($export->getCell(0, 2), $export->getCell($lastColumn, 2))->setAlignmentCenter();

        //Border
        $export->setStyle($export->getCell(0, 0), $export->getCell($lastColumn, 0))->setBorderOutline();
        $export->setStyle($export->getCell(0, 1), $export->getCell($lastColumn, 1))->setBorderOutline();
        $export->setStyle($export->getCell(0, 2), $export->getCell($lastColumn, 2))->setBorderOutline();
        $export->setStyle($export->getCell(0, 3), $export->getCell($lastColumn, 3))
            ->setBorderAll()
            ->setBorderBottom(2)
            ->setFontBold();
        return $export;
    }

    private function fillHeader(PhpExcel $export, $PersonData, string $Zusatz = '', int $lastColumn = 5)
    {

        $export->setValue($export->getCell(0, 0), 'Klasse: '.$PersonData['Division'].$Zusatz);
        $export->setValue($export->getCell(0, 1), $PersonData['Consumer']);
        $export->setValue($export->getCell(0, 2), 'KL: '.$PersonData['DivisionTeacher']);
        $export->setValue($export->getCell($lastColumn - 1, 2), $PersonData['DivisionYear']);
        $export->setValue($export->getCell($lastColumn, 2), (new \DateTime('now'))->format('d.m.Y'));
    }
}