<?php

namespace SPHERE\Application\Reporting\Custom\Gersdorf\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Component\Document\Exception\DocumentTypeException;
use MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Reporting\Standard\Person\Person;
use SPHERE\Common\Frontend\Text\Repository\Code;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Custom\Gersdorf\Person
 */
class Service extends Extension
{

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createClassList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

        $TableContent = array();
        $Consumer = Consumer::useService()->getConsumerBySession();

        $CountNumber = 0;
        if (!empty( $tblPersonList )) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber, $tblDivision, $Consumer) {
                $CountNumber++;
                // Header (Excel)
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Consumer'] = '';
                $Item['DivisionYear'] = '';
                $Item['DivisionTeacher'] = '';
                if ($Consumer) {
                    $Item['Consumer'] = $Consumer->getName();
                }
                if ($tblDivision->getServiceTblYear()) {
                    $Item['DivisionYear'] = $tblDivision->getServiceTblYear()->getName().' '.$tblDivision->getServiceTblYear()->getDescription();
                }
                $tblTeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
                if ($tblTeacherList) {
                    foreach ($tblTeacherList as $tblTeacher) {
                        if ($Item['DivisionTeacher'] == '') {
                            $Item['DivisionTeacher'] = $tblTeacher->getSalutation().' '.$tblTeacher->getLastName();
                        } else {
                            $Item['DivisionTeacher'] .= ', '.$tblTeacher->getSalutation().' '.$tblTeacher->getLastName();
                        }
                    }
                }
                // Content
                $Item['Number'] = $CountNumber;
                $Item['Count2'] = $CountNumber;
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Address'] = $Item['AddressExcel'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = '';
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

                    $Item['Address'] = $Item['AddressExcel'] = $tblAddress->getGuiString(false);
                    if(strlen($Item['Address']) > 41){
                        $Item['Address'] = $tblAddress->getGuiTwoRowString(false);
                        $Item['AddressExcel'] = str_replace("<br>", "\n", $Item['Address']);
                    }
                }
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
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
     * @return bool|\SPHERE\Application\Document\Storage\FilePointer
     */
    public function createClassListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $i = 0;
            $export->setValue($export->getCell($i++, 3), "lfdNr.");
            $export->setValue($export->getCell($i++, 3), "Name, Vorname");
//            $export->setValue($export->getCell(2, 3), "lfdNr.");
            $export->setValue($export->getCell($i++, 3), "Geburtsdatum");
            $export->setValue($export->getCell($i++, 3), "Geburtsort");
            $export->setValue($export->getCell($i, 3), "Wohnanschrift");

            //Settings Header
            $export = $this->setHeader($export, 4);

            $Start = $Row = 3;
            foreach ($PersonList as $PersonData) {
                // Fill Header
                if ($Row == 3) {
                    $export->setValue($export->getCell(0, 0), 'Klasse: '.$PersonData['Division'].
                        ' - Klassenliste');
                    $export->setValue($export->getCell(0, 1), $PersonData['Consumer']);
                    $export->setValue($export->getCell(0, 2), 'KL: '.$PersonData['DivisionTeacher']);
                    $export->setValue($export->getCell(3, 2), $PersonData['DivisionYear']);
                    $export->setValue($export->getCell(4, 2), (new \DateTime('now'))->format('d.m.Y'));
                }
                $Row++;

                $i = 0;
                $export->setValue($export->getCell($i++, $Row), $PersonData['Number']);
                $export->setValue($export->getCell($i++, $Row), $PersonData['Name']);
//                $export->setValue($export->getCell(2, $Row), $PersonData['Count2']);
                $export->setValue($export->getCell($i++, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell($i++, $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell($i, $Row), $PersonData['AddressExcel']);
            }

            // TableBorder
            $export->setStyle($export->getCell(0, ( $Start + 1 )), $export->getCell(4, $Row))
                ->setBorderAll()
                ->setWrapText()
                ->setAlignmentMiddle();

            $i = 0;
            // Spaltenbreite
            $export->setStyle($export->getCell($i++, 0), $export->getCell(0, $Row))->setColumnWidth(6);
            $export->setStyle($export->getCell($i++, 0), $export->getCell(1, $Row))->setColumnWidth(26);
            $export->setStyle($export->getCell($i++, 0), $export->getCell(2, $Row))->setColumnWidth(13);
            $export->setStyle($export->getCell($i++, 0), $export->getCell(3, $Row))->setColumnWidth(15);
            $export->setStyle($export->getCell($i, 0), $export->getCell(4, $Row))->setColumnWidth(38);

            // Center
            $export->setStyle($export->getCell(0, 3), $export->getCell(0, $Row))->setAlignmentCenter();

            $Row++;
            $Row++;
            Person::setGenderFooter($export, $tblPersonList, $Row, 1);

            $export->setPagePrintMargin('0.4', '0.4', '0.4', '0.4');

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
    public function createSignList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

        $TableContent = array();
        $Consumer = Consumer::useService()->getConsumerBySession();

        $CountNumber = 0;
        if (!empty( $tblPersonList )) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber, $tblDivision, $Consumer) {
                $CountNumber++;

                // Header (Excel)
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Consumer'] = '';
                $Item['DivisionYear'] = '';
                $Item['DivisionTeacher'] = '';
                if ($Consumer) {
                    $Item['Consumer'] = $Consumer->getName();
                }
                if ($tblDivision->getServiceTblYear()) {
                    $Item['DivisionYear'] = $tblDivision->getServiceTblYear()->getName().' '.$tblDivision->getServiceTblYear()->getDescription();
                }
                $tblTeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
                if ($tblTeacherList) {
                    foreach ($tblTeacherList as $tblTeacher) {
                        if ($Item['DivisionTeacher'] == '') {
                            $Item['DivisionTeacher'] = $tblTeacher->getSalutation().' '.$tblTeacher->getLastName();
                        } else {
                            $Item['DivisionTeacher'] .= ', '.$tblTeacher->getSalutation().' '.$tblTeacher->getLastName();
                        }
                    }
                }

                // Content
                $Item['Count'] = $CountNumber;
                $Item['Number'] = $CountNumber;
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param $PersonList
     * @param $tblPersonList
     *
     * @return bool|\SPHERE\Application\Document\Storage\FilePointer
     */
    public function createSignListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell(0, 3), "lfdNr.");
            $export->setValue($export->getCell(1, 3), "Name");
            $export->setValue($export->getCell(2, 3), "Vorname");

            // Settings Header
            $export = $this->setHeader($export, 4);

            $Start = $Row = 3;
            foreach ($PersonList as $PersonData) {
                // Fill Header
                if ($Row == 3) {
                    $export->setValue($export->getCell(0, 0), 'Klasse: '.$PersonData['Division'].
                        ' - Unterschriften Liste');
                    $export->setValue($export->getCell(0, 1), $PersonData['Consumer']);
                    $export->setValue($export->getCell(0, 2), 'KL: '.$PersonData['DivisionTeacher']);
                    $export->setValue($export->getCell(3, 2), $PersonData['DivisionYear']);
                    $export->setValue($export->getCell(4, 2), (new \DateTime('now'))->format('d.m.Y'));
                }

                $Row++;

                $export->setValue($export->getCell(0, $Row), $PersonData['Count']);
                $export->setValue($export->getCell(1, $Row), $PersonData['LastName']);
                $export->setValue($export->getCell(2, $Row), $PersonData['FirstName']);
            }

            //Zeilenhöhe
            $RowHeight = '23';

            // TableBorder
            $export->setStyle($export->getCell(0, ( $Start + 1 )), $export->getCell(4, $Row))
                ->setBorderAll()
                ->setRowHeight($RowHeight)
                ->setWrapText()
                ->setAlignmentMiddle();

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $Row))->setColumnWidth(6);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setColumnWidth(21);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $Row))->setColumnWidth(21);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $Row))->setColumnWidth(25);
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setColumnWidth(25);

            // Center
            $export->setStyle($export->getCell(0, 4), $export->getCell(0, $Row))->setAlignmentCenter();

            $Row++;
            $Row++;
            Person::setGenderFooter($export, $tblPersonList, $Row, 1);

            $export->setPagePrintMargin('0.4', '0.4', '0.4', '0.4');

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
            $SubjectCount = array();
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $tblDivision, &$count, &$SubjectCount) {

                $Item['Number'] = $count++;
                $Item['Name'] = $tblPerson->getLastFirstName();
//                $Item['Birthday'] = '';
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

//                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
//                if ($tblCommon) {
//                    $Item['Birthday'] = $tblCommon->getTblCommonBirthDates()->getBirthday();
//                }

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
                            $Item['ForeignLanguage'. $i] = $tblSubject->getAcronym();
                            $Item['ForeignLanguage'. $i.'Id'] = $tblSubject->getId();

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
                    if ($tblPerson->getId() == 15) {
                        exit;
                    }

//                    // Profil
//                    $tblStudentProfile = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
//                        $tblStudent, Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE')
//                    );
//                    if ($tblStudentProfile && ($tblSubject = $tblStudentProfile[0]->getServiceTblSubject())) {
//                        $Item['Profile'] = $tblSubject->getAcronym();
//                    }
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
                                    if(($tblSubject = $tblStudentElective->getServiceTblSubject())){

                                        switch($tblSubjectRanking->getIdentifier()) {
                                            case 1:
                                                $Item['Elective1'] = $tblSubject->getAcronym();
                                                $Item['Elective1Id'] = $tblSubject->getId();
                                                break;
                                            case 2:
                                                $Item['Elective2'] = $tblSubject->getAcronym();
                                                $Item['Elective2Id'] = $tblSubject->getId();
                                                break;
                                            case 3:
                                                $Item['Elective3'] = $tblSubject->getAcronym();
                                                $Item['Elective3Id'] = $tblSubject->getId();
                                                break;
                                            case 4:
                                                $Item['Elective4'] = $tblSubject->getAcronym();
                                                $Item['Elective4Id'] = $tblSubject->getId();
                                                break;
                                            case 5:
                                                $Item['Elective5'] = $tblSubject->getAcronym();
                                                $Item['Elective5Id'] = $tblSubject->getId();
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
//            $export->setValue($export->getCell($i++, 1), "Geb.-Datum");
            $export->setValue($export->getCell($i++, 1), "Bg");
            $export->setValue($export->getCell($i++, 1), "FS 1");
            $export->setValue($export->getCell($i++, 1), "FS 2");
            $export->setValue($export->getCell($i++, 1), "FS 3");
//            $export->setValue($export->getCell($i++, 1), "Profil");
            $export->setValue($export->getCell($i++, 1), "WB");
            $export->setValue($export->getCell($i++, 1), "Rel.");
            $export->setValue($export->getCell($i++, 1), "WF 1-5");
            $export->setValue($export->getCell($i++, 1), "WF 1");
            $export->setValue($export->getCell($i++, 1), "WF 2");
            $export->setValue($export->getCell($i++, 1), "WF 3");
            $export->setValue($export->getCell($i++, 1), "WF 4");
            $export->setValue($export->getCell($i, 1), "WF 5");
            // Header bold
            $export->setStyle($export->getCell(0, 1), $export->getCell(14, 1))->setFontBold();

            // Zählung
            $CountList = $this->getSubjectCount($PersonList);

            $Row = 2;
            foreach ($PersonList as $PersonData) {
                $ElectiveRow = $Row;

                $export->setValue($export->getCell(0, $Row), $PersonData['Name']);
//                $export->setValue($export->getCell(1, $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell(1, $Row), $PersonData['Education']);
                $export->setValue($export->getCell(2, $Row), $PersonData['ForeignLanguage1']);
                $export->setValue($export->getCell(3, $Row), $PersonData['ForeignLanguage2']);
                $export->setValue($export->getCell(4, $Row), $PersonData['ForeignLanguage3']);
//                $export->setValue($export->getCell(6, $Row), $PersonData['Profile']);
                $export->setValue($export->getCell(5, $Row), $PersonData['Orientation']);
                $export->setValue($export->getCell(6, $Row), $PersonData['Religion']);

//                if (isset($PersonData['ExcelElective']) && !empty($PersonData['ExcelElective'])) {
//                    foreach ($PersonData['ExcelElective'] as $Elective) {
//                        $export->setValue($export->getCell(9, $ElectiveRow), $Elective);
//                        $ElectiveRow++;
//                    }
//                }
                if(!empty($PersonData['ExcelElective'])){
                    $export->setValue($export->getCell(7, $Row), implode(', ', $PersonData['ExcelElective']));
                }
                $export->setValue($export->getCell(8, $Row), $PersonData['Elective1']);
                $export->setValue($export->getCell(9, $Row), $PersonData['Elective2']);
                $export->setValue($export->getCell(10, $Row), $PersonData['Elective3']);
                $export->setValue($export->getCell(11, $Row), $PersonData['Elective4']);
                $export->setValue($export->getCell(12, $Row), $PersonData['Elective5']);

                $Row++;
                if ($ElectiveRow > $Row) {
                    $Row = $ElectiveRow;
                }

                // Gittertrennlinie
//                $export->setStyle($export->getCell(0, $Row - 1), $export->getCell(14, $Row - 1))->setBorderBottom();
            }

//            // Gitterlinien
//            $export->setStyle($export->getCell(0, 1), $export->getCell(14, 1))->setBorderBottom();
//            $export->setStyle($export->getCell(0, 1), $export->getCell(14, $Row - 1))->setBorderVertical();
            $export->setStyle($export->getCell(0, 1), $export->getCell(12, $Row - 1))
                ->setBorderAll()
                ->setWrapText()
                ->setAlignmentMiddle();

            // Personenanzahl
            $Row++;
            $RowReference = $RowReference2 = $Row;
            Person::setGenderFooter($export, $tblPersonList, $Row);

            // Stand
            $Row += 2;
            $export->setValue($export->getCell(0, $Row), 'Stand: ' . (new \DateTime())->format('d.m.Y'));

            foreach($CountList as $Type => $SubjectList){
                foreach($SubjectList as $SubjectId => $Count){
                    if(!($Type == 'Elective')){
                        // Spalte 1
                        $j = 3;
                        $tblSubject = Subject::useService()->getSubjectById($SubjectId);
                        $export->setValue($export->getCell($j, $RowReference), $Count);
                        $export->setStyle($export->getCell($j++, $RowReference))->setCellType(\PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        $export->setValue($export->getCell($j++, $RowReference), $tblSubject->getAcronym());
                        $export->setValue($export->getCell($j, $RowReference++), $tblSubject->getName());
                    } else {
                        // Spalte 2
                        $j = 7;
                        $tblSubject = Subject::useService()->getSubjectById($SubjectId);
                        $export->setValue($export->getCell($j, $RowReference2), $Count);
                        $export->setStyle($export->getCell($j++, $RowReference2))->setCellType(\PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        $export->setValue($export->getCell($j++, $RowReference2), $tblSubject->getAcronym());
                        $export->setValue($export->getCell($j, $RowReference2++), $tblSubject->getName());
                    }
                }
            }

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0))->setColumnWidth(27);
            $export->setStyle($export->getCell(1, 0))->setColumnWidth(7);
            $export->setStyle($export->getCell(2, 0))->setColumnWidth(8);
            $export->setStyle($export->getCell(3, 0))->setColumnWidth(8);
            $export->setStyle($export->getCell(4, 0))->setColumnWidth(8);
            $export->setStyle($export->getCell(5, 0))->setColumnWidth(7);
            $export->setStyle($export->getCell(6, 0))->setColumnWidth(7);
            $export->setStyle($export->getCell(7, 0))->setColumnWidth(17);
            $export->setStyle($export->getCell(8, 0))->setColumnWidth(8);
            $export->setStyle($export->getCell(9, 0))->setColumnWidth(8);
            $export->setStyle($export->getCell(10, 0))->setColumnWidth(8);
            $export->setStyle($export->getCell(11, 0))->setColumnWidth(8);
            $export->setStyle($export->getCell(12, 0))->setColumnWidth(8);

            $export->setPagePrintMargin('0.4', '0.4', '0.4', '0.4');
            $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    public function getSubjectCount($PersonList = array())
    {
        $CountList = array();
        foreach($PersonList as $PersonData){
            // Fremdsprachen
            for($i = 1; $i <= 3; $i++){
                if(isset($PersonData['ForeignLanguage'. $i.'Id'])){
                    if(!isset($CountList['ForeignLanguage'][$PersonData['ForeignLanguage'. $i.'Id']])){
                        $CountList['ForeignLanguage'][$PersonData['ForeignLanguage'. $i.'Id']] = 1;
                    } else {
                        $CountList['ForeignLanguage'][$PersonData['ForeignLanguage'. $i.'Id']] = $CountList['ForeignLanguage'][$PersonData['ForeignLanguage'. $i.'Id']] + 1;
                    }
                }
            }
            // Wahlbereich
            if(isset($PersonData['OrientationId'])){
                if(!isset($CountList['Orientation'][$PersonData['OrientationId']])){
                    $CountList['Orientation'][$PersonData['OrientationId']] = 1;
                } else {
                    $CountList['Orientation'][$PersonData['OrientationId']] = $CountList['Orientation'][$PersonData['OrientationId']] + 1;
                }
            }
            // Religion
            if(isset($PersonData['ReligionId'])){
                if(!isset($CountList['Religion'][$PersonData['ReligionId']])){
                    $CountList['Religion'][$PersonData['ReligionId']] = 1;
                } else {
                    $CountList['Religion'][$PersonData['ReligionId']] = $CountList['Religion'][$PersonData['ReligionId']] + 1;
                }
            }

            // Fremdsprachen
            for($i = 1; $i <= 5; $i++){
                if(isset($PersonData['Elective'. $i.'Id'])){
                    if(!isset($CountList['Elective'][$PersonData['Elective'. $i.'Id']])){
                        $CountList['Elective'][$PersonData['Elective'. $i.'Id']] = 1;
                    } else {
                        $CountList['Elective'][$PersonData['Elective'. $i.'Id']] = $CountList['Elective'][$PersonData['Elective'. $i.'Id']] + 1;
                    }
                }
            }
        }

        return $CountList;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createClassPhoneList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

        $TableContent = array();
        $Consumer = Consumer::useService()->getConsumerBySession();

        $CountNumber = 0;
        if (!empty( $tblPersonList )) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber, $tblDivision, $Consumer) {
                $CountNumber++;
                // Header (Excel)
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Consumer'] = '';
                $Item['DivisionYear'] = '';
                $Item['DivisionTeacher'] = '';
                if ($Consumer) {
                    $Item['Consumer'] = $Consumer->getName();
                }
                if ($tblDivision->getServiceTblYear()) {
                    $Item['DivisionYear'] = $tblDivision->getServiceTblYear()->getName().' '.$tblDivision->getServiceTblYear()->getDescription();
                }
                $tblTeacherList = Division::useService()->getTeacherAllByDivision($tblDivision);
                if ($tblTeacherList) {
                    foreach ($tblTeacherList as $tblTeacher) {
                        if ($Item['DivisionTeacher'] == '') {
                            $Item['DivisionTeacher'] = $tblTeacher->getSalutation().' '.$tblTeacher->getLastName();
                        } else {
                            $Item['DivisionTeacher'] .= ', '.$tblTeacher->getSalutation().' '.$tblTeacher->getLastName();
                        }
                    }
                }
                // Content
                $Item['Number'] = $CountNumber;
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['PhoneNumbers'] = '';
                $Item['ExcelPhoneNumbers'] = '';
                $Item['S1Private'] = '';
                $Item['S1ExcelPrivate'] = '';
                $Item['S1Business'] = '';
                $Item['S1ExcelBusiness'] = '';
                $Item['S2Private'] = '';
                $Item['S2ExcelPrivate'] = '';
                $Item['S2Business'] = '';
                $Item['S2ExcelBusiness'] = '';

                // Parent's
                $PhoneParent = array();
                if (($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
                    foreach ($tblToPersonList as $tblToPerson) {
                        $tblPersonParent = false;
                        switch($tblToPerson->getRanking()){
                            case '1':
                            case '2':
                                $tblPersonParent = $tblToPerson->getServiceTblPersonFrom();
                                break;
                        }
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
                                        $Item['S1Private'] = implode(', ', $PhoneList);
                                        $Item['S1ExcelPrivate'] = $PhoneList;
                                    }elseif($Type == TblType::VALUE_NAME_BUSINESS){
                                        $Item['S1Business'] = implode(', ', $PhoneList);
                                        $Item['S1ExcelBusiness'] = $PhoneList;
                                    }
                                }
                            }
                        } elseif($Ranking == 2){
                            foreach($PhoneTypeList as $Type => $PhoneList){
                                if($Type){
                                    if($Type == TblType::VALUE_NAME_PRIVATE){
                                        $Item['S2Private'] = implode(', ', $PhoneList);
                                        $Item['S2ExcelPrivate'] = $PhoneList;
                                    } elseif($Type == TblType::VALUE_NAME_BUSINESS) {
                                        $Item['S2Business'] = implode(', ', $PhoneList);
                                        $Item['S2ExcelBusiness'] = $PhoneList;
                                    }
                                }
                            }
                        }
                    }
                }

                $Item['PhoneParent'] = $PhoneParent;

                // PhoneNumbers
                $phoneNumbers = array();
                $phoneList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                if ($phoneList) {
                    foreach ($phoneList as $phone) {
                        $phoneNumbers[] = $phone->getTblPhone()->getNumber();
                    }
                }
                if(!empty($phoneNumbers)){
                    $Item['PhoneNumbers'] = implode(', ', $phoneNumbers);
                    $Item['ExcelPhoneNumbers'] = $phoneNumbers;
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
     * @return bool|\SPHERE\Application\Document\Storage\FilePointer
     */
    public function createClassPhoneListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::createFilePointer('xlsx');

            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export = $this->setHeader($export, 6);
            //TableHead
            $export->setValue($export->getCell(0, 3), "Nr.");
            $export->setValue($export->getCell(1, 3), "Name");
//            $export->setValue($export->getCell(2, 3), "Vorname");
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

            $Start = $Row = 4;
            foreach ($PersonList as $PersonData) {
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
                if ($Row == 4) {
                    $export->setValue($export->getCell(0, 0), 'Klasse: '.$PersonData['Division'].
                        ' - Telefon Liste');
                    $export->setValue($export->getCell(0, 1), $PersonData['Consumer']);
                    $export->setValue($export->getCell(0, 2), 'KL: '.$PersonData['DivisionTeacher']);
                    $export->setValue($export->getCell(3, 2), $PersonData['DivisionYear']);
                    $export->setValue($export->getCell(6, 2), (new \DateTime('now'))->format('d.m.Y'));
                }
                $Row++;
                $RowEmail = $RowParent = $RowPhone = $Row;
                $export->setValue($export->getCell(0, $Row), $PersonData['Number']);
                $export->setValue($export->getCell(1, $Row), $PersonData['Name']);
//                $export->setValue($export->getCell(2, $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell(2, $Row), $ExcelPhoneNumbers);
                $export->setValue($export->getCell(3, $Row), $S1ExcelPrivate);
                $export->setValue($export->getCell(4, $Row), $S1ExcelBusiness);
                $export->setValue($export->getCell(5, $Row), $S2ExcelPrivate);
                $export->setValue($export->getCell(6, $Row), $S2ExcelBusiness);
            }

            // Spaltenbreite
            $export->setStyle($export->getCell(0, 0), $export->getCell(0, $Row))->setColumnWidth(4);
            $export->setStyle($export->getCell(1, 0), $export->getCell(1, $Row))->setColumnWidth(26);
            $export->setStyle($export->getCell(2, 0), $export->getCell(2, $Row))->setColumnWidth(20);
            $export->setStyle($export->getCell(3, 0), $export->getCell(3, $Row))->setColumnWidth(20);
            $export->setStyle($export->getCell(4, 0), $export->getCell(4, $Row))->setColumnWidth(20);
            $export->setStyle($export->getCell(5, 0), $export->getCell(5, $Row))->setColumnWidth(20);
            $export->setStyle($export->getCell(6, 0), $export->getCell(6, $Row))->setColumnWidth(20);

            // Center
            $export->setStyle($export->getCell(0, 5), $export->getCell(6, $Row))
                ->setBorderAll()
                ->setWrapText()
                ->setAlignmentMiddle();
            $export->setPagePrintMargin('0.4', '0.4', '0.4', '0.4');

//            $Row++; $Row++;
//            Person::setGenderFooter($export, $tblPersonList, $Row, 1);
            $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @return array
     */
    public function createTeacherList()
    {

        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER));
        $TableContent = array();
        if (!empty( $tblPersonList )) {

            $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
            $i = 1;
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$i) {
                $Item['Number'] = $i++;
                $Item['Name'] = ($tblPerson->getTitle() ? $tblPerson->getTitle().' ' : '').$tblPerson->getLastFirstName();
                $Item['Birthday'] = '';
                $Item['Gender'] = '';
                $Item['Address'] = $Item['Street'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = $Item['District'] = '';
                $Item['Phone'] = '';
                $Item['PhoneList'] = array();
                if ($tblCommon = Common::useService()->getCommonByPerson($tblPerson)) {
                    if($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates()){
                        $Item['Birthday'] = $tblCommonBirthDates->getBirthday();
                        if($tblCommonGender = $tblCommonBirthDates->getTblCommonGender()){
                            $Item['Gender'] = $tblCommonGender->getShortName();
                        }
                    }
                }
                if(($tblAddress = Address::useService()->getAddressByPerson($tblPerson))){
                    $Item['Address'] = $tblAddress->getGuiString();
//                    $Item['Street'] = $tblAddress->getStreetName();
//                    $Item['StreetNumber'] = $tblAddress->getStreetNumber();
//                    if($tblCity = $tblAddress->getTblCity()){
//                        $Item['Code'] = $tblCity->getCode();
//                        $Item['City'] = $tblCity->getName();
//                        $Item['District'] = $tblCity->getDistrict();
//                    }
                }
                if(($tblToPersonList = Phone::useService()->getPhoneAllByPerson($tblPerson))){
                    foreach($tblToPersonList as $tblToPerson){
                        $tblPhone = $tblToPerson->getTblPhone();
                        $Item['PhoneList'][] = $tblPhone->getNumber();
                    }
                    if(!empty($Item['PhoneList'])){
                        $Item['Phone'] = implode(', ', $Item['PhoneList']);
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
     */
    public function createTeacherListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::createFilePointer('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());

            // Tabellenkopf auf jeder A4 Seite wiederholen
            /*start column and end column*/
            $StartEnd = [1,4];
            $export->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTop($StartEnd);

            //Settings Header
            $export = $this->setHeader($export, 5);

            // Fill Header
            $export->setValue($export->getCell(0, 0), 'Lehrerliste - Adressen, Telefon, Geburtstag');
            $export->setValue($export->getCell(0, 1), 'Christilicher Schulverein e.V.');
            $export->setValue($export->getCell(0, 2), 'Evangelische Oberschule Gersdorf staatlich anerkannte Ersatzschule');
            $Year = '';
            if($tblYearList = Term::useService()->getYearByNow()){
                $tblYear = current($tblYearList);
                $Year = $tblYear->getYear();
            }
            $export->setValue($export->getCell(4, 2), $Year);
            $export->setValue($export->getCell(5, 2), (new \DateTime('now'))->format('d.m.Y'));


            $i = 0;
            $export->setValue($export->getCell($i++, 3), "lfdNr.");
            $export->setValue($export->getCell($i++, 3), "Name, Vorname");
            $export->setValue($export->getCell($i++, 3), "G");
            $export->setValue($export->getCell($i++, 3), "Anschrift");
            $export->setValue($export->getCell($i++, 3), "Telefon");
            $export->setValue($export->getCell($i, 3), "Geburtsdatum");


            $Start = $Row = $RowCount = 4;

            foreach ($PersonList as $PersonData) {
                $i = 0;
                $export->setValue($export->getCell($i++, $Row), $PersonData['Number']);
                $export->setValue($export->getCell($i++, $Row), $PersonData['Name']);
                $export->setValue($export->getCell($i++, $Row), $PersonData['Gender']);
                $export->setValue($export->getCell($i++, $Row), $PersonData['Address']);
                if(!empty($PersonData['PhoneList'])){
                    foreach($PersonData['PhoneList'] as $Phone){
                        $export->setValue($export->getCell($i, $RowCount), $Phone);
                        $RowCount++;
                    }
                } else {
                    $RowCount++;
                }
                $i++;
                $export->setValue($export->getCell($i, $Row), $PersonData['Birthday']);

                // Border per RowCount
                $export->setStyle($export->getCell(0, $Row), $export->getCell($i, $RowCount -1))
                    ->setBorderOutline()
                    ->setBorderVertical()
                    ->setWrapText()
                    ->setAlignmentMiddle();
                $Row = $RowCount;

            }

            // TableBorder
//            $export->setStyle($export->getCell(0, ( $Start + 1 )), $export->getCell($i, $Row))
//                ->setBorderAll()
//                ->setWrapText()
//                ->setAlignmentMiddle();

            $i = 0;
            // Spaltenbreite
            $export->setStyle($export->getCell($i, 0), $export->getCell($i++, $Row))->setColumnWidth(5);
            $export->setStyle($export->getCell($i, 0), $export->getCell($i++, $Row))->setColumnWidth(20);
            $export->setStyle($export->getCell($i, 0), $export->getCell($i++, $Row))->setColumnWidth(3);
            $export->setStyle($export->getCell($i, 0), $export->getCell($i++, $Row))->setColumnWidth(38);
            $export->setStyle($export->getCell($i, 0), $export->getCell($i++, $Row))->setColumnWidth(16);
            $export->setStyle($export->getCell($i, 0), $export->getCell($i, $Row))->setColumnWidth(13);

            // Center
            $export->setStyle($export->getCell(0, 3), $export->getCell(0, $Row))->setAlignmentCenter();
            $export->setStyle($export->getCell(2, 3), $export->getCell(2, $Row))->setAlignmentCenter();
            $export->setStyle($export->getCell(4, 3), $export->getCell(4, $Row))->setAlignmentCenter();
            $export->setStyle($export->getCell(5, 3), $export->getCell(5, $Row))->setAlignmentCenter();

            $Row++;
            $Row++;
            Person::setGenderFooter($export, $tblPersonList, $Row, 1);

            $export->setPagePrintMargin('0.4', '0.4', '0.4', '0.4');

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
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
}