<?php

namespace SPHERE\Application\Api\Education\Graduation\Grade;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;

class Task implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/TaskGrades/Download',
            __CLASS__ . '::downloadTaskGrades'
        ));
    }

    public static function useService()
    {
    }

    public static function useFrontend()
    {
    }

    /**
     * @param $TaskId
     * @param $DivisionCourseId
     *
     * @return string
     */
    public static function downloadTaskGrades($TaskId = null, $DivisionCourseId = null): string
    {
        if (($tblTask = Grade::useService()->getTaskById($TaskId))
            && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        ) {
            if ($tblTask->getIsTypeBehavior()) {
                list($headerList, $bodyList) = Grade::useService()->getBehaviorTaskGradesViewData($tblTask, $tblDivisionCourse);
            } else {
                list($headerList, $bodyList) = Grade::useService()->getAppointedDateTaskGradesViewData($tblTask, $tblDivisionCourse);
            }

            if ($headerList && $bodyList) {
                // Excel-Datei mit Notenübersicht erstellen
                if ($tblTask->getIsTypeBehavior()) {
                    $fileLocation = self::getBehaviorTaskGradesExcel($headerList, $bodyList);
                } else {
                    $fileLocation = self::getAppointedDateTaskGradesExcel($headerList, $bodyList);
                }

                // Download-Link für Excel-Datei erstellen
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Zensurenübersicht " . date("Y-m-d H:i:s") . ".xlsx")->__toString();
            }

        }

        return 'Keine Daten vorhanden';
    }

    /**
     * @param $tableHeader
     * @param $tableContent
     *
     * @return false|FilePointer
     */
    private static function getAppointedDateTaskGradesExcel($tableHeader, $tableContent)
    {
        if (!empty($tableHeader) && !empty($tableContent)) {
            $fileLocation = Storage::createFilePointer('xlsx');

            $Row = 0;
            $Column = 0;

            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($Column++, $Row), '#');
            $export->setValue($export->getCell($Column++, $Row), 'Vorname');
            $export->setValue($export->getCell($Column++, $Row), 'Nachname');
            unset($tableHeader['Number']);
            unset($tableHeader['FirstName']);
            unset($tableHeader['LastName']);
            foreach ($tableHeader as $Value){
                $export->setValue($export->getCell($Column++, $Row), $Value);
                $Column++;
                $export->setStyle($export->getCell($Column-2, $Row), $export->getCell($Column-1, $Row))
                    ->mergeCells();
            }
            $export->setStyle($export->getCell(0, $Row), $export->getCell($Column-1, $Row))
                // Header Fett
                ->setFontBold()
                // Strich nach dem Header
                ->setBorderBottom();
            $export->getActiveSheet()
                ->getStyle('A:A')
                ->getFont()
                ->setBold(true);

            // Befüllen der Tabelle
            foreach ($tableContent as $tableRow) {
                $Row++;
                $Column = 0;
                $export->setValue($export->getCell($Column++, $Row), $tableRow['Number']);
                $export->setValue($export->getCell($Column++, $Row), $tableRow['FirstName']);
                $export->setValue($export->getCell($Column++, $Row), $tableRow['LastName']);
                $export->setStyle($export->getCell($Column, $Row), $export->getCell($Column, $Row));

                foreach ($tableHeader as $SubjectKey => $Value) {
                    if (strpos($SubjectKey, 'Subject') !== false) {
                        if (isset($tableRow[$SubjectKey . 'Grade']) && $tableRow[$SubjectKey . 'Grade'] != 'f') {
                            $export->setValue($export->getCell($Column, $Row), $tableRow[$SubjectKey . 'Grade']);
                        }
                        // Trennstrich pro Fach
                        $export->setStyle($export->getCell($Column, $Row), $export->getCell($Column, $Row))->setBorderLeft();
                        $Column++;
                        if (isset($tableRow[$SubjectKey . 'Average'])) {
                            $export->setValue($export->getCell($Column, $Row), $tableRow[$SubjectKey . 'Average']);
                        }
                        $Column++;
                    }
                }

                if (isset($tableHeader['Average'])) {
                    $export->setValue($export->getCell($Column, $Row), $tableRow['Average']);
                    // Trennstrich Durchschnitt
                    $export->setStyle($export->getCell($Column, $Row), $export->getCell($Column, $Row))->setBorderLeft();
                }
            }
            // set column width
            $export->setStyle($export->getCell(0, 0))->setColumnWidth(3);
            $export->setStyle($export->getCell(1, 0))->setColumnWidth(13);
            $export->setStyle($export->getCell(2, 0))->setColumnWidth(15);
            $colCount = count($tableHeader);
            $colCount *= 2;
            $a = 3;
            for ($col = 3; $a <= $colCount; $col++) {
                $width = ($a % 2 == 1) ? 3 : 7;
                $export->setStyle($export->getCell($col, 0))->setColumnWidth($width);
                $a++;
            }

            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }

    /**
     * @param $tableHeader
     * @param $tableContent
     *
     * @return false|FilePointer
     */
    private static function getBehaviorTaskGradesExcel($tableHeader, $tableContent)
    {
        if (!empty($tableHeader) && !empty($tableContent)) {
            $fileLocation = Storage::createFilePointer('xlsx');

            $Row = 0;
            $Column = 0;

            /** @var PhpExcel $export */
            $export = Document::getDocument($fileLocation->getFileLocation());
            $export->setValue($export->getCell($Column++, $Row), '#');
            $export->setValue($export->getCell($Column++, $Row), 'Vorname');
            $export->setValue($export->getCell($Column++, $Row), 'Nachname');
            unset($tableHeader['Number']);
            unset($tableHeader['FirstName']);
            unset($tableHeader['LastName']);
            $maxCount = 0;
            foreach ($tableContent as $columnList) {
                foreach ($columnList as $subjectAcronym => $gradeList) {
                    if (strpos($subjectAcronym, 'GradeType') !== false) {
                        $count = count($gradeList);
                        if ($count > $maxCount) {
                            $maxCount = $count;
                        }
                    }
                }
            }
            // Header verbinden
            foreach ($tableHeader as $Value) {
                for ($i = 0; $i <= $maxCount; $i++) {
                    $export->setValue($export->getCell($Column++, $Row), $Value);
                }
                $export->setStyle($export->getCell($Column - $i, $Row), $export->getCell($Column - 1, $Row))
                    ->mergeCells()
                    ->setBorderLeft();
            }
            $export->setStyle($export->getCell(0, $Row), $export->getCell($Column - 1, $Row))
                // Header Fett mit Unterstrich
                ->setFontBold()
                ->setBorderBottom();
            $export->getActiveSheet()->getStyle('A:A')
                ->getFont()
                ->setBold(true);
            $maxCount++;
            // Befüllen der Tabelle
            foreach ($tableContent as $tableRow) {
                $Row++;
                $Column = 0;
                $export->setValue($export->getCell($Column++, $Row), $tableRow['Number']);
                $export->setValue($export->getCell($Column++, $Row), $tableRow['FirstName']);
                $export->setValue($export->getCell($Column++, $Row), $tableRow['LastName']);
                foreach ($tableRow as $subjectKey => $personGradeList) {
                    if (strpos($subjectKey, 'GradeType') !== false) {
                        $countRows = 0;
                        $gradeTypeId = str_replace('GradeType', '', $subjectKey);
                        if (isset($tableRow['AverageExcel' . $gradeTypeId])) {
                            $countRows++;
                            $export->setValue($export->getCell($Column++, $Row), $tableRow['AverageExcel' . $gradeTypeId]);
                            $export->setStyle($export->getCell($Column-1, $Row), $export->getCell($Column, $Row))
                                ->setBorderLeft();
                        }
                        foreach ($personGradeList as $gradeText) {
                            $countRows++;
                            $export->setValue($export->getCell($Column++, $Row), $gradeText);
                        }
                        if ($countRows < $maxCount) {
                            $Column += $maxCount - $countRows;
                        }
                    }
                }
            }
            // set column width
            $widths = [3, 11, 15];
            for ($i = 0; $i < $Column; $i++) {
                if (isset($widths[$i])) {
                    $export->setStyle($export->getCell($i, 0))->setColumnWidth($widths[$i]);
                }
            }
            $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

            return $fileLocation;
        }

        return false;
    }
}