<?php

namespace SPHERE\Application\Reporting\Custom\Herrnhut\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\System\Extension\Extension;

class Service extends Extension
{

    /**
     * @param TblDivision $tblDivision
     *
     * @return array
     */
    public function createSignList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        foreach ($tblPersonList as $key => $row) {
            $lastName[$key] = strtoupper($row->getLastName());
            $firstName[$key] = strtoupper($row->getFirstName());
            $id[$key] = $row->getId();
        }
        array_multisort($lastName, SORT_ASC, $firstName, SORT_ASC, $tblPersonList);
        $TableContent = array();

        $CountNumber = 0;
        if (!empty( $tblPersonList )) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber) {
                $CountNumber++;
                $Item['Count'] = $CountNumber;
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['Empty'] = '';
                array_push($TableContent, $Item);
            });
        }

        return $TableContent;
    }

    /**
     * @param array $PersonList
     * @param array $tblPersonList
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createSignListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::useWriter()->getTemporary('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "0"), "lfdNr.");
            $export->setValue($export->getCell("1", "0"), "Name");
            $export->setValue($export->getCell("2", "0"), "Vorname");
            $export->setValue($export->getCell("3", "0"), "Unterschrift");

            /**
             * Settings Header
             */
            $export->setStyle($export->getCell(0, 0), $export->getCell(12, 0))
                ->mergeCells()
                ->setFontSize(18)
                ->setFontBold()
                ->setBorderOutline();
            $export->setStyle($export->getCell(0, 1), $export->getCell(12, 1))
                ->mergeCells()
                ->setFontSize(14)
                ->setBorderOutline();
            $export->setStyle($export->getCell(0, 2), $export->getCell(6, 2))
                ->mergeCells();
            $export->setStyle($export->getCell(7, 2), $export->getCell(8, 2))
                ->mergeCells()
                ->setAlignmentCenter();
            $export->setStyle($export->getCell(9, 2), $export->getCell(12, 2))
                ->mergeCells()
                ->setAlignmentCenter();
            $export->setStyle($export->getCell(0, 2), $export->getCell(12, 2))
                ->setBorderOutline();
            $export->setStyle($export->getCell(0, 3), $export->getCell(12, 3))
                ->setBorderLeft()
                ->setBorderVertical()
                ->setBorderRight()
                ->setBorderBottom()
                ->setFontBold();

            $Row = 3;
            foreach ($PersonList as $PersonData) {
                /**
                 * Fill Header
                 */
                if ($Row == 3) {
                    $export->setValue($export->getCell("0", "0"), 'Klasse: '.$PersonData['Division'].
                        ' - Unterschriften Liste');
                    $export->setValue($export->getCell("0", "1"), $PersonData['Consumer']);
                    $export->setValue($export->getCell("0", "2"), 'Klassenleiter: '.$PersonData['DivisionTeacher']);
                    $export->setValue($export->getCell("7", "2"), $PersonData['DivisionYear']);
                    $export->setValue($export->getCell("9", "2"), (new \DateTime('now'))->format('d.m.Y'));
                }

                $Row++;

                $export->setValue($export->getCell("0", $Row), $PersonData['Count']);
                $export->setValue($export->getCell("1", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("3", $Row), $PersonData['Empty']);
            }
            $Row++;
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

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
    public function createLanguageList(TblDivision $tblDivision)
    {

        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

        foreach ($tblPersonList as $key => $row) {
            $lastName[$key] = strtoupper($row->getLastName());
            $firstName[$key] = strtoupper($row->getFirstName());
            $id[$key] = $row->getId();
        }
        array_multisort($lastName, SORT_ASC, $firstName, SORT_ASC, $tblPersonList);
        $TableContent = array();
        $Consumer = Consumer::useService()->getConsumerBySession();

        $CountNumber = 0;
        if (!empty( $tblPersonList )) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, &$CountNumber, $tblDivision, $Consumer) {
                $CountNumber++;
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Consumer'] = '';
                $Item['DivisionYear'] = '';
                $Item['DivisionTeacher'] = '';
                $Item['Count'] = $CountNumber;
                $Item['FirstName'] = $tblPerson->getFirstSecondName();
                $Item['LastName'] = $tblPerson->getLastName();
                $Item['StreetName'] = $Item['StreetNumber'] = $Item['Code'] = $Item['City'] = '';
                $Item['Address'] = '';
                $Item['Birthday'] = $Item['Birthplace'] = $Item['Age'] = '';
                $Item['FS1'] = $Item['FS2'] = $Item['FS3'] = $Item['FS4'] = '';

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
                if (( $addressList = Address::useService()->getAddressAllByPerson($tblPerson) )) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }
                if ($address !== null) {
                    $Item['StreetName'] = $address->getTblAddress()->getStreetName();
                    $Item['StreetNumber'] = $address->getTblAddress()->getStreetNumber();
                    $Item['Code'] = $address->getTblAddress()->getTblCity()->getCode();
                    $Item['City'] = $address->getTblAddress()->getTblCity()->getName();

                    $Item['Address'] = $address->getTblAddress()->getStreetName().' '.
                        $address->getTblAddress()->getStreetNumber().' '.
                        $address->getTblAddress()->getTblCity()->getCode().' '.
                        $address->getTblAddress()->getTblCity()->getName();
                }
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    $Item['Birthday'] = $common->getTblCommonBirthDates()->getBirthday();
                    $Item['Birthplace'] = $common->getTblCommonBirthDates()->getBirthplace();
                }

                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE');
                    $tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType);
                    if ($tblStudentSubjectList) {
                        foreach ($tblStudentSubjectList as $tblStudentSubject) {
                            if ($tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == 1) {
                                $tblSubject = $tblStudentSubject->getServiceTblSubject();
                                if ($tblSubject) {
                                    $Item['FS1'] = $tblSubject->getAcronym();
                                }
                            }
                            if ($tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == 2) {
                                $tblSubject = $tblStudentSubject->getServiceTblSubject();
                                if ($tblSubject) {
                                    $Item['FS2'] = $tblSubject->getAcronym();
                                }
                            }
                            if ($tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == 3) {
                                $tblSubject = $tblStudentSubject->getServiceTblSubject();
                                if ($tblSubject) {
                                    $Item['FS3'] = $tblSubject->getAcronym();
                                }
                            }
                            if ($tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier() == 4) {
                                $tblSubject = $tblStudentSubject->getServiceTblSubject();
                                if ($tblSubject) {
                                    $Item['FS4'] = $tblSubject->getAcronym();
                                }
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
     *
     * @return \SPHERE\Application\Document\Explorer\Storage\Writer\Type\Temporary
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createLanguageListExcel($PersonList, $tblPersonList)
    {

        if (!empty( $PersonList )) {

            $fileLocation = Storage::useWriter()->getTemporary('xlsx');
            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell("0", "3"), "lfdNr.");
            $export->setValue($export->getCell("1", "3"), "Name");
            $export->setValue($export->getCell("2", "3"), "Vorname");
            $export->setValue($export->getCell("3", "3"), "Straße");
            $export->setValue($export->getCell("4", "3"), "Nummer");
            $export->setValue($export->getCell("5", "3"), "PLZ");
            $export->setValue($export->getCell("6", "3"), "Ort");
            $export->setValue($export->getCell("7", "3"), "Geb.-datum");
            $export->setValue($export->getCell("8", "3"), "Geburtsort");
            $export->setValue($export->getCell("9", "3"), "FS 1");
            $export->setValue($export->getCell("10", "3"), "FS 2");
            $export->setValue($export->getCell("11", "3"), "FS 3");
            $export->setValue($export->getCell("12", "3"), "FS 4");

            /**
             * Settings Header
             */
            $export->setStyle($export->getCell(0, 0), $export->getCell(12, 0))
                ->mergeCells()
                ->setFontSize(18)
                ->setFontBold()
                ->setBorderOutline();
            $export->setStyle($export->getCell(0, 1), $export->getCell(12, 1))
                ->mergeCells()
                ->setFontSize(14)
                ->setBorderOutline();
            $export->setStyle($export->getCell(0, 2), $export->getCell(6, 2))
                ->mergeCells();
            $export->setStyle($export->getCell(7, 2), $export->getCell(8, 2))
                ->mergeCells()
                ->setAlignmentCenter();
            $export->setStyle($export->getCell(9, 2), $export->getCell(12, 2))
                ->mergeCells()
                ->setAlignmentCenter();
            $export->setStyle($export->getCell(0, 2), $export->getCell(12, 2))
                ->setBorderOutline();
            $export->setStyle($export->getCell(0, 3), $export->getCell(12, 3))
                ->setBorderLeft()
                ->setBorderVertical()
                ->setBorderRight()
                ->setBorderBottom()
                ->setFontBold();

            $Start = $Row = 3;
            foreach ($PersonList as $PersonData) {
                /**
                 * Fill Header
                 */
                if ($Row == 3) {
                    $export->setValue($export->getCell("0", "0"), 'Klasse: '.$PersonData['Division'].
                        ' - Klassenliste - Fremdsprachen');
                    $export->setValue($export->getCell("0", "1"), $PersonData['Consumer']);
                    $export->setValue($export->getCell("0", "2"), 'Klassenleiter: '.$PersonData['DivisionTeacher']);
                    $export->setValue($export->getCell("7", "2"), $PersonData['DivisionYear']);
                    $export->setValue($export->getCell("9", "2"), (new \DateTime('now'))->format('d.m.Y'));
                }

                $Row++;

                $export->setValue($export->getCell("0", $Row), $PersonData['Count']);
                $export->setValue($export->getCell("1", $Row), $PersonData['LastName']);
                $export->setValue($export->getCell("2", $Row), $PersonData['FirstName']);
                $export->setValue($export->getCell("3", $Row), $PersonData['StreetName']);
                $export->setValue($export->getCell("4", $Row), $PersonData['StreetNumber']);
                $export->setValue($export->getCell("5", $Row), $PersonData['Code']);
                $export->setValue($export->getCell("6", $Row), $PersonData['City']);
                $export->setValue($export->getCell("7", $Row), $PersonData['Birthday']);
                $export->setValue($export->getCell("8", $Row), $PersonData['Birthplace']);
                $export->setValue($export->getCell("9", $Row), $PersonData['FS1']);
                $export->setValue($export->getCell("10", $Row), $PersonData['FS2']);
                $export->setValue($export->getCell("11", $Row), $PersonData['FS3']);
                $export->setValue($export->getCell("12", $Row), $PersonData['FS4']);
            }

            /**
             * TableBorder
             */
            $export->setStyle($export->getCell(0, $Start), $export->getCell(0, $Row))
                ->setBorderLeft();
            $export->setStyle($export->getCell(12, $Start), $export->getCell(12, $Row))
                ->setBorderRight();
            $export->setStyle($export->getCell(0, $Row), $export->getCell(12, $Row))
                ->setBorderBottom();

            $Row++;
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Weiblich:');
            $export->setValue($export->getCell("1", $Row), Person::countFemaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Männlich:');
            $export->setValue($export->getCell("1", $Row), Person::countMaleGenderByPersonList($tblPersonList));
            $Row++;
            $export->setValue($export->getCell("0", $Row), 'Gesamt:');
            $export->setValue($export->getCell("1", $Row), count($tblPersonList));

            $Row -= 2;
            $LanguageHigh = $Row;
            $LanguageNumberList = $this->countSubject($tblPersonList, 'FOREIGN_LANGUAGE');
            if (!empty( $LanguageNumberList )) {
                foreach ($LanguageNumberList as $FSCounter => $Language) {
                    if ($FSCounter == 1) {
                        $export->setValue($export->getCell(4, $Row), 'Fremdsprache 1');
                        $export->setStyle($export->getCell(4, $Row), $export->getCell(5, $Row))
                            ->mergeCells();
                        if ($Language) {
                            foreach ($Language as $key => $Count) {
                                $Row++;
                                $export->setValue($export->getCell(4, $Row), $key.':');
                                $export->setValue($export->getCell(5, $Row), $Count);
                            }
                        }
                    } elseif ($FSCounter == 2) {
                        $export->setValue($export->getCell(6, $Row), 'Fremdsprache 2');
                        $export->setStyle($export->getCell(6, $Row), $export->getCell(7, $Row))
                            ->mergeCells();
                        if ($Language) {
                            foreach ($Language as $key => $Count) {
                                $Row++;
                                $export->setValue($export->getCell(6, $Row), $key.':');
                                $export->setValue($export->getCell(7, $Row), $Count);
                            }
                        }
                    } elseif ($FSCounter == 3) {
                        $export->setValue($export->getCell(8, $Row), 'Fremdsprache 3');
                        $export->setStyle($export->getCell(8, $Row), $export->getCell(9, $Row))
                            ->mergeCells();
                        if ($Language) {
                            foreach ($Language as $key => $Count) {
                                $Row++;
                                $export->setValue($export->getCell(8, $Row), $key.':');
                                $export->setValue($export->getCell(9, $Row), $Count);
                            }
                        }
                    } elseif ($FSCounter == 4) {
                        $export->setValue($export->getCell(10, $Row), 'Fremdsprache 4');
                        $export->setStyle($export->getCell(10, $Row), $export->getCell(11, $Row))
                            ->mergeCells();
                        if ($Language) {
                            foreach ($Language as $key => $Count) {
                                $Row++;
                                $export->setValue($export->getCell(10, $Row), $key.':');
                                $export->setValue($export->getCell(11, $Row), $Count);
                            }
                        }
                    }

                    $Row = $LanguageHigh;
                }
            }


            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param array  $tblPersonList
     * @param string $SubjectType "Identifier"
     *
     * @return array
     */
    private function countSubject($tblPersonList, $SubjectType)
    {

        $result = array();
        if (empty( $tblPersonList )) {
            return $result;
        } else {
            $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier($SubjectType);
            foreach ($tblPersonList as $tblPerson) {
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    $tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent, $tblStudentSubjectType);
                    if ($tblStudentSubjectList) {
                        foreach ($tblStudentSubjectList as $tblStudentSubject) {
                            if ($tblStudentSubject->getServiceTblSubject()) {
                                if (!isset( $result[$tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier()][$tblStudentSubject->getServiceTblSubject()->getName()] )) {
                                    $result[$tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier()][$tblStudentSubject->getServiceTblSubject()->getName()] = 1;
                                } else {
                                    $result[$tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier()][$tblStudentSubject->getServiceTblSubject()->getName()] += 1;
                                }
                            }
                            ksort($result[$tblStudentSubject->getTblStudentSubjectRanking()->getIdentifier()]);
                        }
                    }
                }
            }
            return $result;
        }
    }

}