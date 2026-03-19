<?php
namespace SPHERE\Application\Reporting\Custom\Kreuzgymnasium\Person;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Reporting\Standard\Person\Person;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Custom\Kreuzgymnasium\Person
 */
class Service extends Extension
{

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

        $export->setValue($export->getCell(0, 0), 'Klasse: '.$PersonData['Division'].' '.$Zusatz);
        $export->setValue($export->getCell(0, 1), $PersonData['Consumer']);
        $export->setValue($export->getCell(0, 2), 'Klassenleiter: '.$PersonData['DivisionTeacher']);
        $export->setValue($export->getCell($lastColumn, 0), $PersonData['DivisionYear']);
        $export->setValue($export->getCell($lastColumn, 2), (new \DateTime('now'))->format('d.m.Y'));
    }

    /**
     * @param array $TableContent
     * @param array $tblPersonList
     *
     * @return FilePointer
     */
    public function createSignListExcel(array $TableContent, array $tblPersonList, $isLandscape = false)
    {

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        if($isLandscape){
            $MaxColumn = 5;
        } else {
            $MaxColumn = 4;
        }

        $column = 0;
        $row = 3;
        $export->setValue($export->getCell($column++, $row), "lfdNr.");
        $export->setValue($export->getCell($column++, $row), "Name");
        $export->setValue($export->getCell($column, $row++), "Vorname");
        // Settings Header
        $export = $this->setHeader($export, $MaxColumn);
        foreach ($TableContent as $PersonData) {
            // Fill Header
            if ($row == 4) {
                $this->fillHeader($export, $PersonData, ' - Unterschriften Liste', $MaxColumn);
            }
            $column = 0;
            $export->setValue($export->getCell($column++, $row), $PersonData['Count']);
            $export->setValue($export->getCell($column++, $row), $PersonData['LastName']);
            $export->setValue($export->getCell($column, $row++), $PersonData['FirstName']);
        }

        $RowHeight = '20,1'; // 20 would be ignored
        $ColumnWith = 24;
        if($isLandscape){
            $RowHeight = '15';
            $ColumnWith = 26;
        }
        // TableBorder
        $export->setStyle($export->getCell(0, 4), $export->getCell($MaxColumn, ($row - 1)))
            ->setBorderAll()->setRowHeight($RowHeight)->setWrapText()->setAlignmentMiddle();
        // Spaltenbreite
        $column = 0;
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(6);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(20);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(24);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth($ColumnWith);
        if($isLandscape){
            $export->setStyle($export->getCell($column++, 0))->setColumnWidth($ColumnWith);
        }
        $export->setStyle($export->getCell($column, 0))->setColumnWidth($ColumnWith);
        // Center
        $export->setStyle($export->getCell(0, 4), $export->getCell(0, ($row - 1)))->setAlignmentCenter();
        $row++;
        if($isLandscape){
            $column = 1;
            $export->setValue($export->getCell($column++, $row), 'Weiblich: '.Person::countFemaleGenderByPersonList($tblPersonList));
            $export->setValue($export->getCell($column++, $row), 'Männlich: '.Person::countMaleGenderByPersonList($tblPersonList));
            if(($DiversCount = Person::countDiversGenderByPersonList($tblPersonList))){
                $export->setValue($export->getCell($column++, $row), 'Divers: '.$DiversCount);
            }
            if(($OtherCount = Person::countOtherGenderByPersonList($tblPersonList))){
                $export->setValue($export->getCell($column++, $row), 'Ohne Angabe: '.$OtherCount);
            }
            $export->setValue($export->getCell($column, $row), 'Gesamt: '.count($tblPersonList));
        } else {
            Person::setGenderFooter($export, $tblPersonList, $row, 1);
        }

        $export->setPagePrintMargin('0.4', '0.4', '0.4', '0.4');
        if($isLandscape){
            $export->setPaperOrientationParameter(new PaperOrientationParameter('LANDSCAPE'));
        }
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    public function getStudentCountDivisionList($tblYearList)
    {
        $DivisionCourseSek1List = array();
        $DivisionCourseSek2List = array();
        $DivisionCourseDAZList = array();
        $DivisionCourseSiAList = array();
        foreach($tblYearList as $tblYear){
            $tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByYear($tblYear);
            if($tblDivisionCourseList) {
                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                    $DivisionName = $tblDivisionCourse->getDisplayName();
                    // Suche nur für Klassen und Stammgruppen
                    if($tblDivisionCourse->getTypeIdentifier() != TblDivisionCourseType::TYPE_DIVISION
                        && $tblDivisionCourse->getTypeIdentifier() != TblDivisionCourseType::TYPE_CORE_GROUP){
                        continue;
                    }
                    // trennen nach Vorgabe
                    if(preg_match('!^(0[5-9]|10)-!is', $DivisionName)) {
                        $DivisionCourseSek1List[] = $tblDivisionCourse;
                    } elseif(preg_match('!^(11|12)-!is', $DivisionName)) {
                        $DivisionCourseSek2List[] = $tblDivisionCourse;
                    } elseif(preg_match('!^DAZ!is', $DivisionName)) {
                        $DivisionCourseDAZList[] = $tblDivisionCourse;
                    } elseif(preg_match('!^SiA!is', $DivisionName)) {
                        $DivisionCourseSiAList[] = $tblDivisionCourse;
                    }
                }
            }
        }
        return array($DivisionCourseSek1List, $DivisionCourseSek2List, $DivisionCourseDAZList, $DivisionCourseSiAList);
    }

    public function createStudentCount($divisionCourseList, $divisionType, $tblGroup) {
        $content = array();

        if ($divisionCourseList) {
            /** @var $tblDivisionCourse TblDivisionCourse */
            foreach ($divisionCourseList as $tblDivisionCourse) {
                $item = array();
                $Name = $tblDivisionCourse->getDisplayName();
                $item['Name'] = $Name;
                $item['Teacher'] = '';
                $item['MaleCount'] = $item['MaleKruzianerCount'] = $item['MaleNoKruzianerCount'] = $item['FemaleCount'] = $item['Count'] = 0;

                // Markierung für die Herkunft (Sek1, Sek2 oder DAZ)
                $item['Sek1'] = $divisionType === 'Sek1' ? 1 : 0;
                $item['Sek2'] = $divisionType === 'Sek2' ? 1 : 0;
                $item['DAZ'] = $divisionType === 'DAZ' ? 1 : 0;

                // Schüler zählen
                if (($tblPersonList = $tblDivisionCourse->getStudents())) {
                    foreach ($tblPersonList as $tblPerson) {
                        $item['Count']++;
                        $tblCommonGender = $tblPerson->getGender();
                        if ($tblCommonGender) {
                            if ($tblCommonGender->getId() == TblCommonGender::VALUE_MALE) {
                                if ($tblGroup && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                                    $item['MaleKruzianerCount']++;
                                    $item['MaleCount']++;
                                } else {
                                    $item['MaleNoKruzianerCount']++;
                                    $item['MaleCount']++;
                                }
                            } elseif ($tblCommonGender->getId() == TblCommonGender::VALUE_FEMALE) {
                                $item['FemaleCount']++;
                            }
                        }
                    }
                }

                // Lehrer zuordnen
                if (($tblPersonTeacherList = $tblDivisionCourse->getDivisionTeacherList())) {
                    foreach ($tblPersonTeacherList as $tblPersonTeacher) {
                        if ($item['Teacher']) {
                            $item['Teacher'] .= '/';
                        }
                        $item['Teacher'] .= $tblPersonTeacher->getLastName();
                    }
                }

                $content[] = $item;
            }
        }
        // Sort
        return $this->sortDivisionList($content);
    }

    public function sortDivisionList($DivisionContent)
    {

        // multisort content
        if (!empty($DivisionContent)) {
            $name = array();
            foreach ($DivisionContent as $key => $row) {
                $name[$key] = $row['Name'];
            }
            array_multisort($name, SORT_ASC, $DivisionContent);
        }
        return $DivisionContent;
    }

    public function createStudentCountExcel($Year, $DivisionCourseSek1List, $DivisionCourseSek2List, $DivisionCourseDAZList, $DivisionCourseSiAList)
    {
        $fileLocation = Storage::createFilePointer('xlsx');

        // Zählung
        $tblGroup = Group::useService()->getGroupByName('Kruzianer');
        $Sek1Content = $this->createStudentCount($DivisionCourseSek1List, 'Sek1', $tblGroup);
        $SumSek1 = $this->getCountValueList($Sek1Content);
        $Sek2Content = $this->createStudentCount($DivisionCourseSek2List, 'Sek2', $tblGroup);
        $SumSek2 = $this->getCountValueList($Sek2Content);
        $DAZContent = $this->createStudentCount($DivisionCourseDAZList, 'DAZ', $tblGroup);
        $SumDAZ = $this->getCountValueList($DAZContent);
        $ContentAll = array();
        $ContentAll = array_merge($ContentAll, $Sek1Content);
        $ContentAll = array_merge($ContentAll, $Sek2Content);
        $ContentAll = array_merge($ContentAll, $DAZContent);
        $SumAll = $this->getCountValueList($ContentAll);
        $SiAContent = $this->createStudentCount($DivisionCourseSiAList, 'SiA', $tblGroup);
        $ContentAllAddSiA = array_merge($ContentAll, $SiAContent);
        $SumAllAddSiA = $this->getCountValueList($ContentAllAddSiA);

        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $column = $row = 0;
        $export->setValue($export->getCell($column, $row), "Ev. Kreuzgymnasium");
        $column = 2;
        $export->setValue($export->getCell($column, $row), "Schülerzahlen Schuljahr ".$Year);
        $column = 6;
        $export->setValue($export->getCell($column, $row), (new \DateTime())->format('d.m.Y'));
        $export->setStyle($export->getCell($column, $row))->setAlignmentRight();

        $column = 0;
        $row = 1;
        $export->setValue($export->getCell($column++, $row), "Klassen");
        $export->setValue($export->getCell($column++, $row), "Klassenleiter|n");
        $export->setValue($export->getCell($column, $row), "Jungen");
        $export->setStyle($export->getCell($column, $row), $export->getCell((++$column), $row))->mergeCells()
            ->setBorderTop()->setBorderRight()->setBorderBottom();
        $column++;
        $export->setValue($export->getCell($column++, $row), "Jungen");
        $export->setValue($export->getCell($column++, $row), "Mädchen");
        $export->setValue($export->getCell($column, $row++), "Gesamt");

        $column = 0;
        $export->setValue($export->getCell($column++, $row), $Year);
        $export->setValue($export->getCell($column++, $row), "");
        $export->setValue($export->getCell($column, $row), "Nicht-Kruzianer");
        $export->setStyle($export->getCell($column++, $row))->setFontSize(7);
        $export->setValue($export->getCell($column++, $row), "Kruzianer");
        $export->setValue($export->getCell($column, $row++), "Gesamt");

        // border header
        $column = 0;
        $row = 1;
        $export->setStyle($export->getCell($column++, $row))->setBorderTop()->setBorderLeft()->setBorderRight();
        $export->setStyle($export->getCell($column++, $row))->setBorderTop()->setBorderRight();
        $column++;
        $column++;
        $export->setStyle($export->getCell($column++, $row))->setBorderTop()->setBorderRight();
        $export->setStyle($export->getCell($column++, $row))->setBorderTop()->setBorderRight();
        $export->setStyle($export->getCell($column, $row++))->setBorderTop()->setBorderRight();
        $column = 0;
        $export->setStyle($export->getCell($column++, $row))->setBorderLeft()->setBorderRight();
        $export->setStyle($export->getCell($column++, $row))->setBorderRight();
        $export->setStyle($export->getCell($column++, $row))->setBorderRight();
        $export->setStyle($export->getCell($column++, $row))->setBorderRight();
        $export->setStyle($export->getCell($column++, $row))->setBorderRight();
        $export->setStyle($export->getCell($column++, $row))->setBorderRight();
        $export->setStyle($export->getCell($column, $row++))->setBorderRight();

        if(!empty($Sek1Content)){
            foreach($Sek1Content as $RowSek1){
                $column = 0;
                $export->setValue($export->getCell($column++, $row), $RowSek1['Name']);
                $export->setValue($export->getCell($column++, $row), $RowSek1['Teacher']);
                $export->setValue($export->getCell($column++, $row), $RowSek1['MaleNoKruzianerCount']);
                $export->setValue($export->getCell($column++, $row), $RowSek1['MaleKruzianerCount']);
                $export->setValue($export->getCell($column++, $row), $RowSek1['MaleCount']);
                $export->setValue($export->getCell($column++, $row), $RowSek1['FemaleCount']);
                $export->setValue($export->getCell($column, $row++), $RowSek1['Count']);
            }
        }
        $column = 0;
        $export->setValue($export->getCell($column, $row), 'Kurse '.$Year);
        $export->setStyle($export->getCell($column++, $row), $export->getCell(($column), $row++))->mergeCells()->setFontBold();
        if(!empty($Sek2Content)){
            foreach($Sek2Content as $RowSek2){
                $column = 0;
                $export->setValue($export->getCell($column++, $row), $RowSek2['Name']);
                $export->setValue($export->getCell($column++, $row), $RowSek2['Teacher']);
                $export->setValue($export->getCell($column++, $row), $RowSek2['MaleNoKruzianerCount']);
                $export->setValue($export->getCell($column++, $row), $RowSek2['MaleKruzianerCount']);
                $export->setValue($export->getCell($column++, $row), $RowSek2['MaleCount']);
                $export->setValue($export->getCell($column++, $row), $RowSek2['FemaleCount']);
                $export->setValue($export->getCell($column, $row++), $RowSek2['Count']);
            }
        }
        if(!empty($DAZContent)){
            foreach($DAZContent as $RowDAZ){
                $column = 0;
                $export->setValue($export->getCell($column++, $row), $RowDAZ['Name']);
                $export->setValue($export->getCell($column++, $row), $RowDAZ['Teacher']);
                $export->setValue($export->getCell($column++, $row), $RowDAZ['MaleNoKruzianerCount']);
                $export->setValue($export->getCell($column++, $row), $RowDAZ['MaleKruzianerCount']);
                $export->setValue($export->getCell($column++, $row), $RowDAZ['MaleCount']);
                $export->setValue($export->getCell($column++, $row), $RowDAZ['FemaleCount']);
                $export->setValue($export->getCell($column, $row++), $RowDAZ['Count']);
            }
        }
        $column = 1;
        $export->setValue($export->getCell($column, $row++), 'Summe');
        $export->setValue($export->getCell($column++, $row), 'Klassen 5-10:');
        $export->setValue($export->getCell($column++, $row), $SumSek1['MaleNoKruzianerCount']);
        $export->setValue($export->getCell($column++, $row), $SumSek1['MaleKruzianerCount']);
        $export->setValue($export->getCell($column++, $row), $SumSek1['MaleCount']);
        $export->setValue($export->getCell($column++, $row), $SumSek1['FemaleCount']);
        $export->setValue($export->getCell($column, $row++), $SumSek1['Count']);
        $column = 1;
        $export->setValue($export->getCell($column++, $row), 'Klassen 11+12:');
        $export->setValue($export->getCell($column++, $row), $SumSek2['MaleNoKruzianerCount']);
        $export->setValue($export->getCell($column++, $row), $SumSek2['MaleKruzianerCount']);
        $export->setValue($export->getCell($column++, $row), $SumSek2['MaleCount']);
        $export->setValue($export->getCell($column++, $row), $SumSek2['FemaleCount']);
        $export->setValue($export->getCell($column, $row++), $SumSek2['Count']);
        $export->setStyle($export->getCell(0, $row), $export->getCell(6, ($row)))->setBorderVertical()->setBorderLeft()->setBorderRight();
        $column = 1;
        $export->setValue($export->getCell($column++, $row), 'DaZ');
        $export->setValue($export->getCell($column++, $row), $SumDAZ['MaleNoKruzianerCount']);
        $export->setValue($export->getCell($column++, $row), $SumDAZ['MaleKruzianerCount']);
        $export->setValue($export->getCell($column++, $row), $SumDAZ['MaleCount']);
        $export->setValue($export->getCell($column++, $row), $SumDAZ['FemaleCount']);
        $export->setValue($export->getCell($column, $row++), $SumDAZ['Count']);
        $column = 2;
        $export->setStyle($export->getCell(0, $row), $export->getCell(6, ($row)))->setBorderVertical()->setBorderBottom()->setBorderLeft()->setBorderRight()
            ->setFontSize(20)->setFontBold();
        $export->setValue($export->getCell($column++, $row), $SumAll['MaleNoKruzianerCount']);
        $export->setValue($export->getCell($column++, $row), $SumAll['MaleKruzianerCount']);
        $export->setValue($export->getCell($column++, $row), $SumAll['MaleCount']);
        $export->setValue($export->getCell($column++, $row), $SumAll['FemaleCount']);
        $export->setValue($export->getCell($column, $row++), $SumAll['Count']);

        // TableBorder
        $export->setStyle($export->getCell(0, 3), $export->getCell(6, ($row - 3)))
            ->setBorderAll()->setRowHeight('13')->setFontSize(10); //->setRowHeight('23')->setWrapText()->setAlignmentMiddle();
        $export->setStyle($export->getCell(0, ($row - 2)), $export->getCell(6, ($row - 2)))
            ->setRowHeight('13')->setFontSize(10);
        $export->setStyle($export->getCell(0, ($row)), $export->getCell(6, ($row + 1)))
            ->setRowHeight('13')->setFontSize(10);

        $column = 0;
        $export->setValue($export->getCell($column, $row++), 'Schüler im');
        $export->setValue($export->getCell($column++, $row), 'Ausland');
        $export->setValue($export->getCell($column, $row), 'Gesamt');
        $export->setStyle($export->getCell($column, $row))->setAlignmentRight();
        // alle Zahlen rechtsbündig
        $export->setStyle($export->getCell(2, 3), $export->getCell(6, $row))->setAlignmentRight();
        // Gesamtspalte fett
        $export->setStyle($export->getCell(6, 3), $export->getCell(6, $row--))->setFontBold();
        if(!empty($SiAContent)){
            foreach($SiAContent as $RowSiA){
                $column = 2;
                $export->setValue($export->getCell($column++, $row), $RowSiA['MaleNoKruzianerCount']);
                $export->setValue($export->getCell($column++, $row), $RowSiA['MaleKruzianerCount']);
                $export->setValue($export->getCell($column++, $row), $RowSiA['MaleCount']);
                $export->setValue($export->getCell($column++, $row), $RowSiA['FemaleCount']);
                $export->setValue($export->getCell($column, $row), $RowSiA['Count']);
            }
        } else {
            $column = 2;
            $export->setValue($export->getCell($column++, $row), '0');
            $export->setValue($export->getCell($column++, $row), '0');
            $export->setValue($export->getCell($column++, $row), '0');
            $export->setValue($export->getCell($column++, $row), '0');
            $export->setValue($export->getCell($column, $row), '0');
        }
        $row++;
        $column = 2;
        $export->setValue($export->getCell($column++, $row), $SumAllAddSiA['MaleNoKruzianerCount']);
        $export->setValue($export->getCell($column++, $row), $SumAllAddSiA['MaleKruzianerCount']);
        $export->setValue($export->getCell($column++, $row), $SumAllAddSiA['MaleCount']);
        $export->setValue($export->getCell($column++, $row), $SumAllAddSiA['FemaleCount']);
        $export->setValue($export->getCell($column, $row), $SumAllAddSiA['Count']);

        // Spaltenbreite
        $column = 0;
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(12);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(28);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(10);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(10);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(10);
        $export->setStyle($export->getCell($column++, 0))->setColumnWidth(10);
        $export->setStyle($export->getCell($column, 0))->setColumnWidth(10);
        // A4 Seitenränder einstellen
//        $export->setPagePrintMargin('0.4', '0.4', '0.4', '0.4');
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param $Content
     *
     * @return array()
     */
    private function getCountValueList($Content)
    {
        $MaleNoKruzianerCount = $MaleKruzianerCount = $MaleCount = $FemaleCount = $Count = 0;
        if(!empty($Content)){
            foreach($Content as $Row){
                $MaleNoKruzianerCount += $Row['MaleNoKruzianerCount'];
                $MaleKruzianerCount += $Row['MaleKruzianerCount'];
                $MaleCount += $Row['MaleCount'];
                $FemaleCount += $Row['FemaleCount'];
                $Count += $Row['Count'];
            }
        }

        return array('MaleNoKruzianerCount' => $MaleNoKruzianerCount,
            'MaleKruzianerCount' => $MaleKruzianerCount,
            'MaleCount' => $MaleCount,
            'FemaleCount' => $FemaleCount,
            'Count' => $Count);
    }
}