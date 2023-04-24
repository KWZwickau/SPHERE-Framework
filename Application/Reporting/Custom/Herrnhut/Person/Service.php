<?php
namespace SPHERE\Application\Reporting\Custom\Herrnhut\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Reporting\Standard\Person\Person;
use SPHERE\System\Extension\Extension;

class Service extends Extension
{
    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblConsumer       $Consumer
     * @param array             $item
     */
    private function getExcelHead(TblDivisionCourse $tblDivisionCourse, TblConsumer $Consumer, array &$item)
    {

        $item['Division'] = $tblDivisionCourse->getDisplayName();
        $item['Consumer'] = '';
        $item['DivisionYear'] = '';
        $item['DivisionTeacher'] = '';
        if($Consumer) {
            $item['Consumer'] = $Consumer->getName();
        }
        if($tblYear = $tblDivisionCourse->getServiceTblYear()) {
            $item['DivisionYear'] = $tblYear->getDisplayName();
        }
        $TeacherList = array();
        if(($tblTeacherList = $tblDivisionCourse->getDivisionTeacherList())) {
            foreach($tblTeacherList as $tblPersonTeacher) {
                $TeacherList[] = $tblPersonTeacher->getSalutation().' '.$tblPersonTeacher->getLastName();;
            }
            if(!empty($TeacherList)) {
                $item['DivisionTeacher'] = implode(', ', $TeacherList);
            }
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createProfileList(TblDivisionCourse $tblDivisionCourse)
    {

        $TableContent = array();
        $Consumer = Consumer::useService()->getConsumerBySession();
        $CountNumber = 1;
        if(!empty($tblPersonList = $tblDivisionCourse->getStudents())) {
            array_walk($tblPersonList, function(TblPerson $tblPerson) use (&$TableContent, &$CountNumber, $tblDivisionCourse, $Consumer) {
                // Header (Excel)
                $item = array();
                $this->getExcelHead($tblDivisionCourse, $Consumer, $item);
                // Content
                $item['Count'] = $CountNumber;
                $item['Number'] = $CountNumber++;
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['Profile'] = 'ohne';
                if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                    // Profil
                    $tblStudentProfile = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                        Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE')
                    );
                    if($tblStudentProfile && ($tblSubject = $tblStudentProfile[0]->getServiceTblSubject())) {
                        $item['Profile'] = $tblSubject->getAcronym();
                    }
                }
                array_push($TableContent, $item);
            });
        }
        return $TableContent;
    }

    /**
     * @param $TableContent
     * @param $tblPersonList
     *
     * @return FilePointer
     */
    public function createProfileListExcel($TableContent, $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $column = 0;
        $row = 3;
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell($column++, $row), "lfdNr.");
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Vorname");
        $export->setValue($export->getCell($column, $row), "Profil");
        // Settings Header
        $export = $this->setHeader($export, 2, 3, 3);
        $export = $this->setHeader($export, 2, 3, 3);
        foreach($TableContent as $PersonData) {
            // Fill Header
            if($row == 3) {
                $export->setValue($export->getCell(0, 0), 'Klasse: '.$PersonData['Division'].
                    ' - Profil Liste');
                $export->setValue($export->getCell(0, 1), $PersonData['Consumer']);
                $export->setValue($export->getCell(0, 2), 'KL: '.$PersonData['DivisionTeacher']);
                $export->setValue($export->getCell(2, 2), $PersonData['DivisionYear']);
                $export->setValue($export->getCell(3, 2), (new \DateTime('now'))->format('d.m.Y'));
            }
            $row++;
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Count']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column, $row), $PersonData['Profile']);
        }
        // TableBorder
        $export->setStyle($export->getCell(0, 4), $export->getCell(3, $row))
            ->setBorderAll();
        // Spaltenbreite
        $column = 0;
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(6);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(25);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(25);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row))->setColumnWidth(34);
        // Center
        $export->setStyle($export->getCell(0, 4), $export->getCell(0, $row++))->setAlignmentCenter();
        $row++;
        Person::setGenderFooter($export, $tblPersonList, $row, 1);
        $row++;
        $row++;
        $Result = $this->countProfile($tblPersonList);
        if(!empty($Result)) {
            $export->setValue($export->getCell(1, $row++), 'Profile:');
            foreach($Result as $Acronym => $Count) {
                $export->setValue($export->getCell(1, $row), $Acronym);
                $export->setValue($export->getCell(2, $row++), $Count);
            }
        }
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
        $Consumer = Consumer::useService()->getConsumerBySession();
        $CountNumber = 1;
        if (($tblPersonList = $tblDivisionCourse->getStudents())) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber, $tblDivisionCourse, $Consumer) {
                // Header (Excel)
                $item = array();
                $this->getExcelHead($tblDivisionCourse, $Consumer, $item);
                // Content
                $item['Count'] = $CountNumber;
                $item['Number'] = $CountNumber++;
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['Empty'] = '';
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
    public function createSignListExcel($TableContent, $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = 0;
        $row = 3;
        $export->setValue($export->getCell($column++, $row), "lfdNr.");
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Vorname");
        $export->setValue($export->getCell($column, $row), "Unterschrift");
        // Settings Header
        $export = $this->setHeader($export, 2, 3, 3);
        foreach ($TableContent as $PersonData) {
            // Fill Header
            if ($row == 3) {
                $export->setValue($export->getCell(0, 0), 'Klasse: '.$PersonData['Division'].
                    ' - Unterschriften Liste');
                $export->setValue($export->getCell(0, 1), $PersonData['Consumer']);
                $export->setValue($export->getCell(0, 2), 'KL: '.$PersonData['DivisionTeacher']);
                $export->setValue($export->getCell(2, 2), $PersonData['DivisionYear']);
                $export->setValue($export->getCell(3, 2), (new \DateTime('now'))->format('d.m.Y'));
            }
            $row++;
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Count']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FirstName']);
            $export->setValue($export->getCell($column, $row), $PersonData['Empty']);
        }
        // TableBorder
        $export->setStyle($export->getCell(0, 4), $export->getCell(3, $row))->setBorderAll();
        // Spaltenbreite
        $column = 0;
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(6);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(25);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(25);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row))->setColumnWidth(34);
        // Center
        $export->setStyle($export->getCell(0, 4), $export->getCell(0, $row++))->setAlignmentCenter();
        $row++;
        Person::setGenderFooter($export, $tblPersonList, $row, 1);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createLanguageList(TblDivisionCourse $tblDivisionCourse)
    {

        $TableContent = array();
        $Consumer = Consumer::useService()->getConsumerBySession();
        $CountNumber = 1;
        if (($tblPersonList = $tblDivisionCourse->getStudents())) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber, $tblDivisionCourse, $Consumer) {
                // Header (Excel)
                $item = array();
                $this->getExcelHead($tblDivisionCourse, $Consumer, $item);
                // Content
                $item['Count'] = $CountNumber;
                $item['Number'] = $CountNumber++;
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = '';
                $item['Address'] = '';
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['Birthplace'] = $tblPerson->getBirthplaceString();
                $item['FS1'] = $item['FS2'] = $item['FS3'] = $item['FS4'] = '';
                $item = Person::useService()->getAddressDataFromPerson($tblPerson, $item);
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                    $tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType);
                    if ($tblStudentSubjectList) {
                        foreach ($tblStudentSubjectList as $tblStudentSubject) {
                            $this->setForeignLanguage($tblStudentSubject, $tblPerson, $tblDivisionCourse, $item);
                        }
                    }
                }
                array_push($TableContent, $item);
            });
        }
        return $TableContent;
    }

    /**
     * @param TblStudentSubject $tblStudentSubject
     * @param array $item
     */
    private function setForeignLanguage(TblStudentSubject $tblStudentSubject, TblPerson $tblPerson, TblDivisionCourse $tblDivisionCourse, array &$item) {
        $tblSubject = $tblStudentSubject->getServiceTblSubject();
        if ($tblSubject && ($ranking = $tblStudentSubject->getTblStudentSubjectRanking())) {
            $tblStudentEducation = false;
            if(($tblYear = $tblDivisionCourse->getServiceTblYear())){
                $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
            }
            if($tblStudentEducation && ($level = $tblStudentEducation->getLevel())) {
                $isSetValue = false;
                $fromLevel = $tblStudentSubject->getLevelFrom();
                $tillLevel = $tblStudentSubject->getLevelTill();
                if($fromLevel && floatval($fromLevel) <= floatval($level)
                && $tillLevel && floatval($tillLevel) >= floatval($level)) {
                    $isSetValue = true;
                } elseif($fromLevel && !$tillLevel && floatval($fromLevel) <= floatval($level)) {
                    $isSetValue = true;
                } elseif($tillLevel && !$fromLevel && floatval($tillLevel) >= floatval($level)) {
                    $isSetValue = true;
                } elseif(!$fromLevel && !$tillLevel) {
                    $isSetValue = true;
                }
                if($isSetValue) {
                    $item['FS'.$ranking->getIdentifier()] = $tblSubject->getAcronym();
                }
            }
        }
    }

    /**
     * @param $PersonList
     * @param $tblPersonList
     * @return FilePointer
     */
    public function createLanguageListExcel($TableContent, $tblPersonList)
    {

        $count['FS1']['Total'] = 0;
        $count['FS2']['Total'] = 0;
        $count['FS3']['Total'] = 0;
        $count['FS4']['Total'] = 0;
        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = 0;
        $row = 3;
        $export->setValue($export->getCell($column++, $row), "lfdNr.");
        $export->setValue($export->getCell($column++, $row), "Name, Vorname");
        $export->setValue($export->getCell($column++, $row), "Anschrift");
        $export->setValue($export->getCell($column++, $row), "Geb.-datum");
        $export->setValue($export->getCell($column++, $row), "Geburtsort");
        $export->setValue($export->getCell($column++, $row), "FS 1");
        $export->setValue($export->getCell($column++, $row), "FS 2");
        $export->setValue($export->getCell($column++, $row), "FS 3");
        $export->setValue($export->getCell($column, $row), "FS 4");
        //Settings Header
        $export = $this->setHeader($export, 3, 5, 8);
        foreach ($TableContent as $PersonData) {
            // Fill Header
            if ($row == 3) {
                $export->setValue($export->getCell(0, 0), 'Klasse: '.$PersonData['Division'].
                    ' - Klassenliste - Fremdsprachen');
                $export->setValue($export->getCell(0, 1), $PersonData['Consumer']);
                $export->setValue($export->getCell(0, 2), 'KL: '.$PersonData['DivisionTeacher']);
                $export->setValue($export->getCell(3, 2), $PersonData['DivisionYear']);
                $export->setValue($export->getCell(5, 2), (new \DateTime('now'))->format('d.m.Y'));
            }
            $row++;
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Count']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName'].', '.$PersonData['FirstName']);
            if (isset( $PersonData['StreetName'] ) && $PersonData['StreetName'] !== '' && isset( $PersonData['City'] ) && $PersonData['City'] !== '') {
                $export->setValue($export->getCell($column++, $row),
                    ( $PersonData['District'] !== '' ? $PersonData['District'].' ' : '' ).
                    $PersonData['StreetName'].' '.$PersonData['StreetNumber'].', '.
                    $PersonData['Code'].' '.$PersonData['City']);
            }
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthday']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthplace']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FS1']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FS2']);
            $export->setValue($export->getCell($column++, $row), $PersonData['FS3']);
            $export->setValue($export->getCell($column, $row), $PersonData['FS4']);
            for ($i = 1; $i < 5; $i++) {
                if (isset($PersonData['FS' . $i]) && $PersonData['FS' . $i] != '') {
                    $count['FS' . $i]['Total']++;
                    if (isset($count['FS' . $i]['Subjects'][$PersonData['FS' . $i]])) {
                        $count['FS' . $i]['Subjects'][$PersonData['FS' . $i]]++;
                    } else {
                        $count['FS' . $i]['Subjects'][$PersonData['FS' . $i]] = 1;
                    }
                }
            }
        }
        // TableBorder
        $export->setStyle($export->getCell(0, 4), $export->getCell(8, $row))
            ->setBorderAll();
        // Spaltenbreite
        $column = 0;
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(6);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(25);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(40);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(19);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(5);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(5);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(5);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row))->setColumnWidth(5);
        // Center
        $export->setStyle($export->getCell(0, 4), $export->getCell(0, $row++))->setAlignmentCenter();
        $row++;
        $rowForeign = $row;
        Person::setGenderFooter($export, $tblPersonList, $row, 1);
        foreach ($count as $ranking => $list) {
            $export->setValue($export->getCell(3, $rowForeign), 'Fremdsprache ' . str_replace('FS', '', $ranking));
            $export->setStyle($export->getCell(3, $rowForeign), $export->getCell(4, $rowForeign))
                ->mergeCells()
                ->setFontBold();
            if (isset($list['Subjects'])) {
                ksort($list['Subjects']);
                foreach ($list['Subjects'] as $acronym => $countValue) {
                    $rowForeign++;
                    $tblSubject = Subject::useService()->getSubjectByAcronym($acronym);
                    $export->setValue($export->getCell(3, $rowForeign), ($tblSubject ? $tblSubject->getName() : $acronym) . ':');
                    $export->setValue($export->getCell(4, $rowForeign), $countValue);
                }
            }
            $rowForeign++;
        }
        $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }


    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function createClassList(TblDivisionCourse $tblDivisionCourse)
    {

        $TableContent = array();
        $Consumer = Consumer::useService()->getConsumerBySession();
        $CountNumber = 1;
        if (($tblPersonList = $tblDivisionCourse->getStudents())) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber, $tblDivisionCourse, $Consumer) {
                // Header (Excel)
                $item = array();
                $this->getExcelHead($tblDivisionCourse, $Consumer, $item);
                // Content
                $item['Number'] = $CountNumber;
                $item['Count2'] = $CountNumber++;
                $item['Name'] = $tblPerson->getLastFirstName();
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['StreetName'] = $item['StreetNumber'] = $item['Code'] = $item['City'] = $item['District'] = '';
                $item['Address'] = '';
                $item['Birthday'] = $tblPerson->getBirthday();
                $item['Birthplace'] = $tblPerson->getBirthplaceString();
                $item = Person::useService()->getAddressDataFromPerson($tblPerson, $item);
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
    public function createClassListExcel($PersonList, $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $column = 0;
        $row = 3;
        $export = Document::getDocument($fileLocation->getFileLocation());
        $export->setValue($export->getCell($column++, $row), "lfdNr.");
        $export->setValue($export->getCell($column++, $row), "Name, Vorname");
        $export->setValue($export->getCell($column++, $row), "lfdNr.");
        $export->setValue($export->getCell($column++, $row), "Geburtsdatum");
        $export->setValue($export->getCell($column++, $row), "Geburtsort");
        $export->setValue($export->getCell($column, $row), "Wohnanschrift");
        //Settings Header
        $export = $this->setHeader($export, 4, 5, 5);
        foreach ($PersonList as $PersonData) {
            // Fill Header
            if ($row == 3) {
                $export->setValue($export->getCell(0, 0), 'Klasse: '.$PersonData['Division'].
                    ' - Klassenliste');
                $export->setValue($export->getCell(0, 1), $PersonData['Consumer']);
                $export->setValue($export->getCell(0, 2), 'KL: '.$PersonData['DivisionTeacher']);
                $export->setValue($export->getCell(4, 2), $PersonData['DivisionYear']);
                $export->setValue($export->getCell(5, 2), (new \DateTime('now'))->format('d.m.Y'));
            }
            $column = 0;
            $row++;
            $export->setValue($export->getCell($column++, $row), $PersonData['Number']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Name']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Count2']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthday']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Birthplace']);
            $export->setValue($export->getCell($column, $row), $PersonData['Address']);
        }
        // TableBorder
        $export->setStyle($export->getCell(0, 4), $export->getCell(5, $row))
            ->setBorderAll();
        // Spaltenbreite
        $column = 0;
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(6);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(30);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(6);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(15);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(20);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row))->setColumnWidth(45);
        // Center
        $export->setStyle($export->getCell(0, 3), $export->getCell(0, $row))->setAlignmentCenter();
        $export->setStyle($export->getCell(2, 3), $export->getCell(2, $row++))->setAlignmentCenter();
        $row++;
        Person::setGenderFooter($export, $tblPersonList, $row, 1);
        $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));
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

        $TableContent = array();
        $Consumer = Consumer::useService()->getConsumerBySession();
        $CountNumber = 1;
        if ($tblPersonList = $tblDivisionCourse->getStudents()) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber, $tblDivisionCourse, $Consumer) {
                // Header (Excel)
                $item = array();
                $this->getExcelHead($tblDivisionCourse, $Consumer, $item);
                // Content
                $item['Number'] = $CountNumber;
                $item['Count'] = $CountNumber++;
                $item['Name'] = $tblPerson->getLastFirstName();
                $item['FirstName'] = $tblPerson->getFirstSecondName();
                $item['LastName'] = $tblPerson->getLastName();
                $item['PhoneNumbers'] = '';
                $item['ExcelPhoneNumbers'] = '';
                $item['Parents'] = '';
                $item['ExcelParants'] = array();
                $item['Email'] = '';
                $item['ExcelEmail'] = '';
                $item['Entrance'] = '';
                $item['Leaving'] = '';
                // Parent's
                $phoneNumbers = array();
                $mailListing = array();
                if(($tblToPersonGuardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, TblType::IDENTIFIER_GUARDIAN))) {
                    foreach ($tblToPersonGuardianList as $tblToPerson) {
                        $Ranking = $tblToPerson->getRanking();
                        $tblPersonGuard = $tblToPerson->getServiceTblPersonFrom();
                        if(($tblToPersonPList = Phone::useService()->getPhoneAllByPerson($tblPersonGuard))){
                            foreach($tblToPersonPList as $tblToPersonP){
                                if(($tblPhone = $tblToPersonP->getTblPhone())){
                                    if($Ranking == 1){
                                        array_unshift($phoneNumbers, $tblPhone->getNumber());
                                    } else {
                                        $phoneNumbers[] = $tblPhone->getNumber();
                                    }
                                }
                            }
                        }
                        if(($tblToPersonMList = Mail::useService()->getMailAllByPerson($tblPersonGuard))){
                            foreach($tblToPersonMList as $tblToPersonM){
                                if(($tblMail = $tblToPersonM->getTblMail())){
                                    if($Ranking == 1){
                                        array_unshift($mailListing, $tblMail->getAddress());
                                    } else {
                                        $mailListing[] = $tblMail->getAddress();
                                    }
                                }
                            }
                        }
                        $item['ExcelParants'][$Ranking] = $tblPersonGuard->getFirstName().' '.$tblPersonGuard->getLastName();
                    }
                }
                if(!empty($item['ExcelParants'])){
                    sort($item['ExcelParants']);
                    $item['Parents'] = implode(', ', $item['ExcelParants']);
                }
                if(($tblToPersonPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson))) {
                    foreach ($tblToPersonPhoneList as $tblToPersonPhone) {
                        if(($tblPhone = $tblToPersonPhone->getTblPhone())){
                            array_unshift($phoneNumbers, $tblPhone->getNumber());
                        }
                    }
                }
                if(($tblToPersonMailList = Mail::useService()->getMailAllByPerson($tblPerson))) {
                    foreach ($tblToPersonMailList as $tblToPersonMail) {
                        if(($tblMail = $tblToPersonMail->getTblMail())){
                            array_unshift($mailListing, $tblMail->getAddress());
                        }
                    }
                }
                if(!empty($mailListing)) {
                    $item['Email'] = implode('<br>', $mailListing);
                    $item['ExcelEmail'] = $mailListing;
                }
                if(!empty($phoneNumbers)) {
                    $item['PhoneNumbers'] = implode('<br>', $phoneNumbers);
                    $item['ExcelPhoneNumbers'] = $phoneNumbers;
                }
                // Entrance & Leaving
                if(($tblStudent = $tblPerson->getStudent())) {
                   // Aufnahme
                    if (($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE'))
                        && ($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))
                    ) {
                        $item['Entrance'] = $tblStudentTransfer->getTransferDate();
                    }
                    // Abgabe
                    if(($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE'))
                    && ($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType))
                    ) {
                        $item['Leaving'] = $tblStudentTransfer->getTransferDate();
                    }
                }
                array_push($TableContent, $item);
            });
        }

        return $TableContent;
    }

    /**
     * @param $TableContent
     * @param $tblPersonList
     *
     * @return FilePointer
     */
    public function createExtendedClassListExcel($TableContent, $tblPersonList)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = 0;
        $row = 3;
        $export->setValue($export->getCell($column++, $row), "lfdNr.");
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column++, $row), "Telefon-Nr.");
        $export->setValue($export->getCell($column++, $row), "E-Mail");
        $export->setValue($export->getCell($column++, $row), "Erziehungsberechtigte");
        $export->setValue($export->getCell($column++, $row), "Zugang");
        $export->setValue($export->getCell($column, $row), "Abgang");
        //Settings Header
        $export = $this->setHeader($export, 4, 5, 6);
        foreach($TableContent as $PersonData) {
            // Fill Header
            if($row == 3) {
                $export->setValue($export->getCell(0, 0), 'Klasse: '.$PersonData['Division'].' - Erweiterte Klassenliste');
                $export->setValue($export->getCell(0, 1), $PersonData['Consumer']);
                $export->setValue($export->getCell(0, 2), 'KL: '.$PersonData['DivisionTeacher']);
                $export->setValue($export->getCell(4, 2), $PersonData['DivisionYear']);
                $export->setValue($export->getCell(5, 2), (new \DateTime('now'))->format('d.m.Y'));
            }
            $column = 0;
            $row++;
            $RowEmail = $RowParent = $RowPhone = $row;
            $export->setValue($export->getCell($column++, $row), $PersonData['Count']);
            $export->setValue($export->getCell($column++, $row), $PersonData['Name']);
            if(!empty($PersonData['ExcelPhoneNumbers'])) {
                foreach($PersonData['ExcelPhoneNumbers'] as $Phone) {
                    $export->setValue($export->getCell(2, $RowPhone++), $Phone);
                }
                $column++;
            }
            if(!empty($PersonData['ExcelEmail'])) {
                foreach($PersonData['ExcelEmail'] as $Mail) {
                    $export->setValue($export->getCell(3, $RowEmail++), $Mail);
                }
                $column++;
            }
            if(!empty($PersonData['ExcelParants'])) {
                foreach($PersonData['ExcelParants'] as $Parent) {
                    $export->setValue($export->getCell(4, $RowParent++), $Parent);
                }
                $column++;
            }
            $export->setValue($export->getCell($column++, $row), $PersonData['Entrance']);
            $export->setValue($export->getCell($column, $row), $PersonData['Leaving']);
            if($RowPhone > $row) {
                $row = ($RowPhone - 1);
            }
            if($RowEmail > $row) {
                $row = ($RowEmail - 1);
            }
            if($RowParent > $row) {
                $row = ($RowParent - 1);
            }
            $export->setStyle($export->getCell(0, $row), $export->getCell(6, $row))
                ->setBorderBottom();
        }
        // TableBorder
        $export->setStyle($export->getCell(0, 4), $export->getCell(0, $row))->setBorderLeft();
        $export->setStyle($export->getCell(0, 4), $export->getCell(6, $row))->setBorderVertical()->setBorderRight();
        $export->setStyle($export->getCell(0, $row), $export->getCell(6, $row))->setBorderBottom();
        // Spaltenbreite
        $column = 0;
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(6);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(21);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(24);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(25);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(21);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column++, $row))->setColumnWidth(12);
        $export->setStyle($export->getCell($column, 0), $export->getCell($column, $row))->setColumnWidth(12);
        // Center
        $export->setStyle($export->getCell(0, 3), $export->getCell(0, $row++))->setAlignmentCenter();
        $row++;
        Person::setGenderFooter($export, $tblPersonList, $row, 1);
        $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    private function countProfile($tblPersonList)
    {
        $result = array();
        if (empty( $tblPersonList )) {
            return $result;
        } else {
            foreach ($tblPersonList as $tblPerson) {
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    // Profil
                    $tblStudentProfile = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                        $tblStudent,
                        Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE')
                    );
                    if ($tblStudentProfile && ( $tblSubject = $tblStudentProfile[0]->getServiceTblSubject() )) {
                        if (!isset( $result[$tblSubject->getAcronym()] )) {
                            $result[$tblSubject->getAcronym()] = 1;
                        } else {
                            $result[$tblSubject->getAcronym()] += 1;
                        }
                    }
                }
            }
            return $result;
        }
    }

    /**
     * @param PhpExcel $export
     * @param          $secondColumn
     * @param          $thirdColumn
     * @param          $lastColumn
     *
     * @return PhpExcel
     * @throws \MOC\V\Component\Document\Component\Exception\ComponentException
     */
    private function setHeader(PhpExcel $export, $secondColumn, $thirdColumn, $lastColumn)
    {

        // Merge & Style
        $export->setStyle($export->getCell(0, 0), $export->getCell($lastColumn, 0))
            ->mergeCells()
            ->setFontSize(18)
            ->setFontBold();
        $export->setStyle($export->getCell(0, 1), $export->getCell($lastColumn, 1))
            ->mergeCells()
            ->setFontSize(14)
            ->setBorderOutline();
        $export->setStyle($export->getCell(0, 2), $export->getCell(( $secondColumn - 1 ), 2))
            ->mergeCells();
        $export->setStyle($export->getCell($secondColumn, 2), $export->getCell(( $thirdColumn - 1 ), 2))
            ->setAlignmentCenter()
            ->mergeCells();
        $export->setStyle($export->getCell($thirdColumn, 2), $export->getCell($lastColumn, 2))
            ->setAlignmentCenter()
            ->mergeCells();

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
}