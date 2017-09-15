<?php

namespace SPHERE\Application\Reporting\Standard\Person;


use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Debugger;

class ServiceDivisionReport extends Extension
{
    /**
     *
     * @return FilePointer
     */
    public function createDivisionReportExcel()
    {

        $tblYearList = false;
        $DataContent = array();
        $YearList = Term::useService()->getYearByNow();
        if ($YearList) {
            $tblYearList = Term::useService()->getYearsByYear(current($YearList));
        }
        if ($tblYearList) {
            foreach ($tblYearList as $tblYear) {
                $tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear);
                if ($tblDivisionList) {
                    foreach ($tblDivisionList as $tblDivision) {
                        $tblLevel = $tblDivision->getTblLevel();
                        if ($tblLevel && is_numeric($tblLevel->getName())) {
                            $tblDivisionStudent = Division::useService()->getDivisionStudentAllByDivision($tblDivision);
                            if ($tblDivisionStudent) {
                                if (isset($DataContent[$tblDivision->getTypeName()][$tblLevel->getName()])) {
                                    $DataContent[$tblDivision->getTypeName()][$tblLevel->getName()] =
                                        count($tblDivisionStudent) + $DataContent[$tblDivision->getTypeName()][$tblLevel->getName()];
                                } else {
                                    $DataContent[$tblDivision->getTypeName()][$tblLevel->getName()] = count($tblDivisionStudent);
                                }
                            }
                        }
                    }
                }
            }
        }

//        Debugger::screenDump($DataContent);
//        exit;

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());

        $IsFirstTab = true;
        foreach ($DataContent as $Type => $LevelList) {
            $Row = 0;
            $Type = str_replace('/', '-', $Type);
            if ($IsFirstTab === true) {
                $export->renameWorksheet($Type);
                $IsFirstTab = false;
            } else {
                $export->createWorksheet($Type);
            }
            // Header
            $export->setValue($export->getCell(0, $Row),
                "Meldung der Schülerzahl gem. § 14 Abs. 2 SächsFrTrSchulG i.V.m. § 8 ZuschussVO");
            $export->setStyle($export->getCell(0, $Row), $export->getCell(15, $Row))
                ->mergeCells()
                ->setFontBold()
                ->setAlignmentCenter();
            $Row++;
            $export->setValue($export->getCell(0, $Row),
                "zum Antrag vom ………………….. auf Gewährung von Zuschüssen für Schulen in freier Trägerschaft");
            $export->setStyle($export->getCell(0, $Row), $export->getCell(15, $Row))
                ->mergeCells()
                ->setFontBold()
                ->setAlignmentCenter();
            $Row++;
            $export->setValue($export->getCell(0, $Row),
                "für allgemeinbildende Schulen und allgemeinbildende Förderschulen");
            $export->setStyle($export->getCell(0, $Row), $export->getCell(15, $Row))
                ->mergeCells()
                ->setFontBold()
                ->setAlignmentCenter();
            $Row++;
            $export->setValue($export->getCell(0, $Row),
                " - Abgabe bei der Sächsischen Bildungsagentur spätestens:    24. Oktober - ");
            $export->setStyle($export->getCell(0, $Row), $export->getCell(15, $Row))
                ->mergeCells()
                ->setFontBold()
                ->setAlignmentCenter()
                ->setFontColor('FFFF0000')
                ->setFontItalic();
            $Row++;
            $export->setValue($export->getCell(0, $Row), "Schulträger:");
            $export->setStyle($export->getCell(0, $Row), $export->getCell(0, $Row))
                ->setFontItalic();
            $FirstBoxHeight = $Row;
            $Row++;
            $export->setStyle($export->getCell(0, $Row), $export->getCell(4, ($Row + 1)))
                ->mergeCells();
            $export->setValue($export->getCell(7, $Row), "Stichtag, Datum: ...........");
            $export->setStyle($export->getCell(7, $Row))
                ->setFontBold()
                ->setFontColor('FFFF0000');
            $Row++;
            $export->setValue($export->getCell(7, $Row), "(10. Oktober oder abweichender Stichtag  gem. § 8 Abs. 3 Satz6 ZuschussVO:
Fällt ein Stichtag auf einen unterrichtsfreien Tag, gilt der letzte vorhergehende
Unterrichtstag als Stichtag. Dieser ist anzugeben.)");
            $export->setStyle($export->getCell(7, $Row), $export->getCell(15, ($Row + 2)))
                ->mergeCells()
                ->setFontSize(10.5)
                ->setFontItalic()
                ->setFontColor('FFFF0000')
                ->setWrapText();
            $Row++;
            $export->setValue($export->getCell(0, $Row), "Name u. Standort der Schule:");
            $export->setStyle($export->getCell(0, $Row), $export->getCell(0, $Row))
                ->setFontItalic();
            $Row++;
            $export->setStyle($export->getCell(0, $Row), $export->getCell(4, ($Row + 1)))
                ->mergeCells();
            $Row++;
            // Rahmen erste Box
            $export->setStyle($export->getCell(0, $FirstBoxHeight), $export->getCell(4, $Row))
                ->setBorderOutline(2);
            $Row++;
            $Row++;
            $export->setValue($export->getCell(0, $Row), "Schulart");
            $export->setStyle($export->getCell(0, $Row), $export->getCell(0, $Row))
                ->setFontBold()
                ->setAlignmentRight();
            $export->setStyle($export->getCell(1, $Row), $export->getCell(15, $Row))
                ->mergeCells()
                ->setBorderBottom();
            $Row++;
            $export->setValue($export->getCell(1, $Row), "");
            $export->setStyle($export->getCell(1, $Row), $export->getCell(15, $Row))
                ->mergeCells();
            $Row++;
            $Row++;


            // 16 Spalten in die Breite
            $export->setValue($export->getCell(0, $Row), "Schuljahr");
            $export->setValue($export->getCell(1, $Row), "1");
            $export->setValue($export->getCell(2, $Row), "2");
            $export->setValue($export->getCell(3, $Row), "3");
            $export->setValue($export->getCell(4, $Row), "4");
            $export->setValue($export->getCell(5, $Row), "5");
            $export->setValue($export->getCell(6, $Row), "6");
            $export->setValue($export->getCell(7, $Row), "7");
            $export->setValue($export->getCell(8, $Row), "8");
            $export->setValue($export->getCell(9, $Row), "9");
            $export->setValue($export->getCell(10, $Row), "10");
            $export->setValue($export->getCell(11, $Row), "11");
            $export->setValue($export->getCell(12, $Row), "12");
            $export->setValue($export->getCell(13, $Row), "(13)");
            $export->setValue($export->getCell(14, $Row), "Gesamt- schülerzahl");
            $export->setValue($export->getCell(15, $Row), "davon: Kostenerstattung durch andere öffentlichen Träger");
            $Row++;
            foreach ($LevelList as $Level => $StudentCount) {
                $export->setValue($export->getCell($Level, $Row), $StudentCount);
            }


            // Spaltenhöhe Definieren
            $export->setStyle($export->getCell(0, 6))->setRowHeight(12);
            $export->setStyle($export->getCell(0, 10))->setRowHeight(10);

            // Spaltenbreite Definieren
            $export->setStyle($export->getCell(0, 0))->setColumnWidth(30);
            $export->setStyle($export->getCell(1, 0))->setColumnWidth(6.6);
            $export->setStyle($export->getCell(2, 0))->setColumnWidth(6.6);
            $export->setStyle($export->getCell(3, 0))->setColumnWidth(6.6);
            $export->setStyle($export->getCell(4, 0))->setColumnWidth(6.6);
            $export->setStyle($export->getCell(5, 0))->setColumnWidth(6.6);
            $export->setStyle($export->getCell(6, 0))->setColumnWidth(6.6);
            $export->setStyle($export->getCell(7, 0))->setColumnWidth(6.6);
            $export->setStyle($export->getCell(8, 0))->setColumnWidth(6.6);
            $export->setStyle($export->getCell(9, 0))->setColumnWidth(6.6);
            $export->setStyle($export->getCell(10, 0))->setColumnWidth(6.6);
            $export->setStyle($export->getCell(11, 0))->setColumnWidth(6.6);
            $export->setStyle($export->getCell(12, 0))->setColumnWidth(6.6);
            $export->setStyle($export->getCell(13, 0))->setColumnWidth(6.6);
            $export->setStyle($export->getCell(14, 0))->setColumnWidth(12);
            $export->setStyle($export->getCell(15, 0))->setColumnWidth(17);
        }
        $export->selectWorksheetByIndex(0);

//        $export->setValue($export->getCell(0, $Row), "Klasse");
//        $export->setValue($export->getCell(1, $Row), "Schülernummer");
//        $export->setValue($export->getCell(2, $Row), "Vorname");
//        $export->setValue($export->getCell(3, $Row), "Nachname");

//        foreach ($TableContent as $PersonData) {
//            $Row++;
//
//            $export->setValue($export->getCell(0, $Row), $PersonData['Division']);
//            $export->setValue($export->getCell(1, $Row), $PersonData['StudentNumber']);
//            $export->setValue($export->getCell(2, $Row), $PersonData['FirstName']);
//            $export->setValue($export->getCell(3, $Row), $PersonData['LastName']);
//        }

        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

        return $fileLocation;
    }
}