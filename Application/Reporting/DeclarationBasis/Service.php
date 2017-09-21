<?php

namespace SPHERE\Application\Reporting\DeclarationBasis;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentFocus;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 * @package SPHERE\Application\Reporting\DeclarationBasis
 */
class Service extends Extension
{
    /**
     *
     * @return FilePointer
     */
    public function createDivisionReportExcel()
    {

        $tblYearList = false;
        $DataContent = array();
        $DataBlind = array();
        $DataHear = array();
        $DataMental = array();
        $DataPhysical = array();
        $DataLanguage = array();
        $DataLearn = array();
        $DataEducation = array();

        $YearString = '20.../20...';
        $YearList = Term::useService()->getYearByNow();
        if ($YearList) {
            $YearString = current($YearList)->getYear();
            $tblYearList = Term::useService()->getYearsByYear(current($YearList));
        }
        if ($tblYearList) {
            foreach ($tblYearList as $tblYear) {
                $tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear);
                if ($tblDivisionList) {
                    foreach ($tblDivisionList as $tblDivision) {
                        $tblLevel = $tblDivision->getTblLevel();
                        if ($tblLevel && is_numeric($tblLevel->getName())) {
                            $tblDivisionStudentList = Division::useService()->getDivisionStudentAllByDivision($tblDivision);
                            if ($tblDivisionStudentList) {
                                if (isset($DataContent[$tblDivision->getTypeName()][$tblLevel->getName()])) {
                                    $DataContent[$tblDivision->getTypeName()][$tblLevel->getName()] =
                                        count($tblDivisionStudentList) + $DataContent[$tblDivision->getTypeName()][$tblLevel->getName()];
                                } else {
                                    $DataContent[$tblDivision->getTypeName()][$tblLevel->getName()] = count($tblDivisionStudentList);
                                }
                                foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                                    $tblPerson = $tblDivisionStudent->getServiceTblPerson();
                                    if ($tblPerson) {
                                        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                                        if ($tblStudent) {
                                            $tblStudentFocus = Student::useService()->getStudentFocusPrimary($tblStudent);
                                            /** @var TblStudentFocus $tblStudentFocus */
                                            if ($tblStudentFocus) {
                                                $tblStudentFocusType = $tblStudentFocus->getTblStudentFocusType();
                                                // füllen der Förderschwerpunkte
                                                if ($tblStudentFocusType->getName() == 'Sehen') {
                                                    if (isset($DataBlind[$tblDivision->getTypeName()][$tblLevel->getName()])) {
                                                        $DataBlind[$tblDivision->getTypeName()][$tblLevel->getName()] += 1;
                                                    } else {
                                                        $DataBlind[$tblDivision->getTypeName()][$tblLevel->getName()] = 1;
                                                    }
                                                }
                                                if ($tblStudentFocusType->getName() == 'Hören') {
                                                    if (isset($DataHear[$tblDivision->getTypeName()][$tblLevel->getName()])) {
                                                        $DataHear[$tblDivision->getTypeName()][$tblLevel->getName()] += 1;
                                                    } else {
                                                        $DataHear[$tblDivision->getTypeName()][$tblLevel->getName()] = 1;
                                                    }
                                                }
                                                if ($tblStudentFocusType->getName() == 'Geistige Entwicklung') {
                                                    if (isset($DataMental[$tblDivision->getTypeName()][$tblLevel->getName()])) {
                                                        $DataMental[$tblDivision->getTypeName()][$tblLevel->getName()] += 1;
                                                    } else {
                                                        $DataMental[$tblDivision->getTypeName()][$tblLevel->getName()] = 1;
                                                    }
                                                }
                                                if ($tblStudentFocusType->getName() == 'Körperlich-motorische Entwicklung') {
                                                    if (isset($DataPhysical[$tblDivision->getTypeName()][$tblLevel->getName()])) {
                                                        $DataPhysical[$tblDivision->getTypeName()][$tblLevel->getName()] += 1;
                                                    } else {
                                                        $DataPhysical[$tblDivision->getTypeName()][$tblLevel->getName()] = 1;
                                                    }
                                                }
                                                if ($tblStudentFocusType->getName() == 'Sprache') {
                                                    if (isset($DataLanguage[$tblDivision->getTypeName()][$tblLevel->getName()])) {
                                                        $DataLanguage[$tblDivision->getTypeName()][$tblLevel->getName()] += 1;
                                                    } else {
                                                        $DataLanguage[$tblDivision->getTypeName()][$tblLevel->getName()] = 1;
                                                    }
                                                }
                                                if ($tblStudentFocusType->getName() == 'Lernen') {
                                                    if (isset($DataLearn[$tblDivision->getTypeName()][$tblLevel->getName()])) {
                                                        $DataLearn[$tblDivision->getTypeName()][$tblLevel->getName()] += 1;
                                                    } else {
                                                        $DataLearn[$tblDivision->getTypeName()][$tblLevel->getName()] = 1;
                                                    }
                                                }
                                                if ($tblStudentFocusType->getName() == 'Sozial-emotionale Entwicklung') {
                                                    if (isset($DataEducation[$tblDivision->getTypeName()][$tblLevel->getName()])) {
                                                        $DataEducation[$tblDivision->getTypeName()][$tblLevel->getName()] += 1;
                                                    } else {
                                                        $DataEducation[$tblDivision->getTypeName()][$tblLevel->getName()] = 1;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

//        Debugger::screenDump($DataBlind);
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
                ->setFontSize(12)
                ->mergeCells()
                ->setFontBold()
                ->setAlignmentCenter();
            $Row++;
            $export->setValue($export->getCell(0, $Row),
                "zum Antrag vom ………………….. auf Gewährung von Zuschüssen für Schulen in freier Trägerschaft");
            $export->setStyle($export->getCell(0, $Row), $export->getCell(15, $Row))
                ->setFontSize(12)
                ->mergeCells()
                ->setFontBold()
                ->setAlignmentCenter();
            $Row++;
            $export->setValue($export->getCell(0, $Row),
                "für allgemeinbildende Schulen und allgemeinbildende Förderschulen");
            $export->setStyle($export->getCell(0, $Row), $export->getCell(15, $Row))
                ->setFontSize(12)
                ->mergeCells()
                ->setFontBold()
                ->setAlignmentCenter();
            $Row++;
            $export->setValue($export->getCell(0, $Row),
                " - Abgabe bei der Sächsischen Bildungsagentur spätestens:    24. Oktober - ");
            $export->setStyle($export->getCell(0, $Row), $export->getCell(15, $Row))
                ->setRowHeight(25)
                ->mergeCells()
                ->setFontBold()
                ->setAlignmentCenter()
                ->setAlignmentMiddle()
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
                ->setFontSize(12)
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
            $export->setValue($export->getCell(0, $Row), "Schulart:");
            $export->setStyle($export->getCell(0, $Row), $export->getCell(0, $Row))
                ->setFontBold()
                ->setAlignmentRight();
            $export->setValue($export->getCell(1, $Row), $Type);
            $export->setStyle($export->getCell(1, $Row), $export->getCell(15, $Row))
                ->setAlignmentCenter()
                ->mergeCells()
                ->setBorderBottom();
            $Row++;
            $export->setValue($export->getCell(1, $Row), "(Bezeichnung entsprechend der Anlage zu § 1 ZuschussVO)");
            $export->setStyle($export->getCell(1, $Row), $export->getCell(15, $Row))
                ->setAlignmentCenter()
                ->setFontItalic()
                ->mergeCells();
            $Row++;
            $export->setStyle($export->getCell(0, $Row), $export->getCell(15, $Row))
                ->setBorderBottom(2);
            $Row++;

            // 16 Spalten in die Breite
            $export->setValue($export->getCell(0, $Row), "Schuljahr");
            $export->setStyle($export->getCell(0, $Row), $export->getCell(0, ($Row + 1)))
                ->mergeCells()
                ->setBorderLeft(2)
                ->setBorderRight(1)
                ->setBorderBottom(1)
                ->setAlignmentMiddle()
                ->setAlignmentCenter();
            $export->setValue($export->getCell(1, $Row), "Schülerzahl in Klassenstufe");
            $export->setStyle($export->getCell(1, $Row), $export->getCell(15, $Row))
                ->mergeCells()
                ->setBorderLeft(1)
                ->setBorderRight(2)
                ->setBorderBottom(1)
                ->setAlignmentCenter()
                ->setFontBold();
            $Row++;
            // Klassenspaltenstyle
            $export->setStyle($export->getCell(1, $Row), $export->getCell(13, $Row))
                ->setBorderAll()
                ->setBorderRight(2)
                ->setRowHeight(45)
                ->setAlignmentCenter()
                ->setAlignmentMiddle();
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
            $export->setStyle($export->getCell(14, $Row))
                ->setBorderRight(2)
                ->setBorderBottom(2)
                ->setWrapText()
                ->setFontBold()
                ->setAlignmentCenter()
                ->setAlignmentMiddle();
            $export->setValue($export->getCell(15, $Row), "davon:
Kostenerstattung durch andere öffentlichen Träger");
            $export->setStyle($export->getCell(14, $Row))
                ->setBorderRight(2)
                ->setBorderBottom(2)
                ->setWrapText()
                ->setFontBold()
                ->setAlignmentCenter()
                ->setAlignmentMiddle();
            $export->setStyle($export->getCell(15, $Row))
                ->setBorderRight(2)
                ->setBorderBottom(2)
                ->setWrapText()
                ->setFontBold()
                ->setFontSize(7.5)
                ->setAlignmentCenter()
                ->setAlignmentMiddle();
            $Row++;
            $export->setValue($export->getCell(0, $Row), $YearString);
            $Sum = 0;
            foreach ($LevelList as $Level => $StudentCount) {
                $export->setValue($export->getCell($Level, $Row), $StudentCount);
                if ($StudentCount) {
                    $Sum += $StudentCount;
                }
            }
            $export->setValue($export->getCell(14, $Row), $Sum);
            $export->setStyle($export->getCell(0, $Row))
                ->setFontBold();
            $export->setStyle($export->getCell(0, $Row), $export->getCell(13, $Row))
                ->setBorderLeft(2)
                ->setBorderRight(2)
                ->setBorderBottom(1)
                ->setBorderVertical(1)
                ->setAlignmentCenter()
                ->setAlignmentMiddle()
                ->setRowHeight(25);
            $export->setStyle($export->getCell(14, $Row), $export->getCell(15, $Row))
                ->setBorderRight(2)
                ->setBorderBottom(1)
                ->setBorderVertical(2)
                ->setAlignmentCenter()
                ->setAlignmentMiddle();
            $Row++;
            $export->setValue($export->getCell(0, $Row), "Angabe des Förderschultyps");
            $export->setStyle($export->getCell(0, $Row))
                ->setBorderLeft(2)
                ->setBorderBottom(2)
                ->setBorderRight(1)
                ->setFontBold()
                ->setAlignmentMiddle()
                ->setRowHeight(25);
            $export->setValue($export->getCell(1, $Row), "davon: Anzahl der Integrationsschüler");
            $export->setStyle($export->getCell(1, $Row), $export->getCell(13, $Row))
                ->mergeCells()
                ->setBorderBottom(2)
                ->setBorderRight(2)
                ->setFontBold()
                ->setAlignmentMiddle();
            $export->setStyle($export->getCell(14, $Row), $export->getCell(15, $Row))
                ->setBorderBottom(2)
                ->setBorderVertical(2)
                ->setBorderRight(2);
            $Row++;
            $export->setValue($export->getCell(0, $Row), "Schule für Blinde und Sehbehinderte");
            // Blind Insert
            $SumBlind = 0;
            if (isset($DataBlind[$Type]) && !empty($DataBlind[$Type])) {
                foreach ($DataBlind[$Type] as $Level => $StudentCount) {
                    $export->setValue($export->getCell($Level, $Row), $StudentCount);
                    if ($StudentCount) {
                        $SumBlind += $StudentCount;
                    }
                }
            }
            $export->setValue($export->getCell(14, $Row), $SumBlind);

            $export->setStyle($export->getCell(0, $Row))
                ->setBorderLeft(2)
                ->setBorderBottom(1)
                ->setBorderRight(1);
            $export->setStyle($export->getCell(1, $Row), $export->getCell(13, $Row))
                ->setBorderLeft(1)
                ->setBorderBottom(1)
                ->setBorderVertical(1)
                ->setBorderRight(2)
                ->setAlignmentCenter();
            $export->setStyle($export->getCell(14, $Row), $export->getCell(15, $Row))
                ->setBorderBottom(1)
                ->setBorderVertical(2)
                ->setBorderRight(2)
                ->setAlignmentCenter();
            $Row++;
            $export->setValue($export->getCell(0, $Row), "Schule für Hörgeschädigte");
            // Hear Insert
            $SumHear = 0;
            if (isset($DataHear[$Type]) && !empty($DataHear[$Type])) {
                foreach ($DataHear[$Type] as $Level => $StudentCount) {
                    $export->setValue($export->getCell($Level, $Row), $StudentCount);
                    if ($StudentCount) {
                        $SumHear += $StudentCount;
                    }
                }
            }
            $export->setValue($export->getCell(14, $Row), $SumHear);

            $export->setStyle($export->getCell(0, $Row))
                ->setBorderLeft(2)
                ->setBorderBottom(1)
                ->setBorderRight(1);
            $export->setStyle($export->getCell(1, $Row), $export->getCell(13, $Row))
                ->setBorderLeft(1)
                ->setBorderBottom(1)
                ->setBorderVertical(1)
                ->setBorderRight(2)
                ->setAlignmentCenter();
            $export->setStyle($export->getCell(14, $Row), $export->getCell(15, $Row))
                ->setBorderBottom(1)
                ->setBorderVertical(2)
                ->setBorderRight(2)
                ->setAlignmentCenter();
            $Row++;
            $export->setValue($export->getCell(0, $Row), "Schule für geistig Behinderte");
            // Mental Insert
            $SumMental = 0;
            if (isset($DataMental[$Type]) && !empty($DataMental[$Type])) {
                foreach ($DataMental[$Type] as $Level => $StudentCount) {
                    $export->setValue($export->getCell($Level, $Row), $StudentCount);
                    if ($StudentCount) {
                        $SumMental += $StudentCount;
                    }
                }
            }
            $export->setValue($export->getCell(14, $Row), $SumMental);

            $export->setStyle($export->getCell(0, $Row))
                ->setBorderLeft(2)
                ->setBorderBottom(1)
                ->setBorderRight(1);
            $export->setStyle($export->getCell(1, $Row), $export->getCell(13, $Row))
                ->setBorderLeft(1)
                ->setBorderBottom(1)
                ->setBorderVertical(1)
                ->setBorderRight(2)
                ->setAlignmentCenter();
            $export->setStyle($export->getCell(14, $Row), $export->getCell(15, $Row))
                ->setBorderBottom(1)
                ->setBorderVertical(2)
                ->setBorderRight(2)
                ->setAlignmentCenter();
            $Row++;
            $export->setValue($export->getCell(0, $Row), "Schule für Körperbehinderte");
            // Physical Insert
            $SumPhysical = 0;
            if (isset($DataPhysical[$Type]) && !empty($DataPhysical[$Type])) {
                foreach ($DataPhysical[$Type] as $Level => $StudentCount) {
                    $export->setValue($export->getCell($Level, $Row), $StudentCount);
                    if ($StudentCount) {
                        $SumPhysical += $StudentCount;
                    }
                }
            }
            $export->setValue($export->getCell(14, $Row), $SumPhysical);

            $export->setStyle($export->getCell(0, $Row))
                ->setBorderLeft(2)
                ->setBorderBottom(1)
                ->setBorderRight(1);
            $export->setStyle($export->getCell(1, $Row), $export->getCell(13, $Row))
                ->setBorderLeft(1)
                ->setBorderBottom(1)
                ->setBorderVertical(1)
                ->setBorderRight(2)
                ->setAlignmentCenter();
            $export->setStyle($export->getCell(14, $Row), $export->getCell(15, $Row))
                ->setBorderBottom(1)
                ->setBorderVertical(2)
                ->setBorderRight(2)
                ->setAlignmentCenter();
            $Row++;
            $export->setValue($export->getCell(0, $Row), "Sprachheilschule");
            // Language Insert
            $SumLanguage = 0;
            if (isset($DataLanguage[$Type]) && !empty($DataLanguage[$Type])) {
                foreach ($DataLanguage[$Type] as $Level => $StudentCount) {
                    $export->setValue($export->getCell($Level, $Row), $StudentCount);
                    if ($StudentCount) {
                        $SumLanguage += $StudentCount;
                    }
                }
            }
            $export->setValue($export->getCell(14, $Row), $SumLanguage);

            $export->setStyle($export->getCell(0, $Row))
                ->setBorderLeft(2)
                ->setBorderBottom(1)
                ->setBorderRight(1);
            $export->setStyle($export->getCell(1, $Row), $export->getCell(13, $Row))
                ->setBorderLeft(1)
                ->setBorderBottom(1)
                ->setBorderVertical(1)
                ->setBorderRight(2)
                ->setAlignmentCenter();
            $export->setStyle($export->getCell(14, $Row), $export->getCell(15, $Row))
                ->setBorderBottom(1)
                ->setBorderVertical(2)
                ->setBorderRight(2)
                ->setAlignmentCenter();
            $Row++;
            $export->setValue($export->getCell(0, $Row), "Schule für Lernförderung");
            // Lern Insert
            $SumLern = 0;
            if (isset($DataLearn[$Type]) && !empty($DataLearn[$Type])) {
                foreach ($DataLearn[$Type] as $Level => $StudentCount) {
                    $export->setValue($export->getCell($Level, $Row), $StudentCount);
                    if ($StudentCount) {
                        $SumLern += $StudentCount;
                    }
                }
            }
            $export->setValue($export->getCell(14, $Row), $SumLern);

            $export->setStyle($export->getCell(0, $Row))
                ->setBorderLeft(2)
                ->setBorderBottom(1)
                ->setBorderRight(1);
            $export->setStyle($export->getCell(1, $Row), $export->getCell(13, $Row))
                ->setBorderLeft(1)
                ->setBorderBottom(1)
                ->setBorderVertical(1)
                ->setBorderRight(2)
                ->setAlignmentCenter();
            $export->setStyle($export->getCell(14, $Row), $export->getCell(15, $Row))
                ->setBorderBottom(1)
                ->setBorderVertical(2)
                ->setBorderRight(2)
                ->setAlignmentCenter();
            $Row++;
            $export->setValue($export->getCell(0, $Row), "Schule für Erziehungshilfe");
            // Education Insert
            $SumEducation = 0;
            if (isset($DataEducation[$Type]) && !empty($DataEducation[$Type])) {
                foreach ($DataEducation[$Type] as $Level => $StudentCount) {
                    $export->setValue($export->getCell($Level, $Row), $StudentCount);
                    if ($StudentCount) {
                        $SumEducation += $StudentCount;
                    }
                }
            }
            $export->setValue($export->getCell(14, $Row), $SumEducation);

            $export->setStyle($export->getCell(0, $Row))
                ->setBorderLeft(2)
                ->setBorderBottom(2)
                ->setBorderRight(1);
            $export->setStyle($export->getCell(1, $Row), $export->getCell(13, $Row))
                ->setBorderLeft(1)
                ->setBorderBottom(2)
                ->setBorderVertical(1)
                ->setBorderRight(2)
                ->setAlignmentCenter();
            $export->setStyle($export->getCell(14, $Row), $export->getCell(15, $Row))
                ->setBorderBottom(2)
                ->setBorderVertical(2)
                ->setBorderRight(2)
                ->setAlignmentCenter();
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Hinweis:  Schüler, die integrativ unterrichtet werden, sind
dem Förderschultyp zuzuordnen, den sie ohne integrative Beschulung besuchen würden.');
            $export->setStyle($export->getCell(0, $Row), $export->getCell(15, $Row))
                ->mergeCells()
                ->setFontBold()
                ->setRowHeight(25)
                ->setAlignmentBottom();
            $Row++;
            $export->setValue($export->getCell(0, $Row), '                    Eine Namensliste unter Angabe des Förderschwerpunktes
ist der Meldung zusätzlich beizufügen.');
            $export->setStyle($export->getCell(0, $Row), $export->getCell(15, $Row))
                ->mergeCells()
                ->setFontBold()
                ->setRowHeight(25)
                ->setAlignmentTop();
            $Row++;
            $export->setValue($export->getCell(0, $Row), '§ 14 Abs. 2 Nr. 1 SächsFrTrSchulG: Ein Schüler wird beschult, wenn er am maßgeblichen Stichtag aufgrund eines Vertragsverhältnisses am Unterricht teilnimmt
oder entschuldigt nicht teilnimmt. Ist das Vertragsverhältnis am Stichtag bereits gekündigt und hat der Schüler den Schulbesuch am Stichtag bereits
endgültig beendet oder abgebrochen, gilt er nicht als beschult.');
            $export->setStyle($export->getCell(0, $Row), $export->getCell(15, $Row))
                ->mergeCells()
                ->setRowHeight(50)
                ->setWrapText();
            $Row++;
            $Row++;
            $Row++;
            $export->setStyle($export->getCell(0, $Row))
                ->setBorderBottom();
            $export->setStyle($export->getCell(8, $Row), $export->getCell(15, $Row))
                ->setBorderBottom();
            $Row++;
            $export->setValue($export->getCell(0, $Row), 'Datum');
            $export->setValue($export->getCell(8, $Row), 'Unterschrift');
            $Row++;
            $export->setValue($export->getCell(8, $Row), 'Vorsitzende/r / Geschäftsführer/in des Schulträgers');

//            // Spaltenhöhe Definieren
//            $export->setStyle($export->getCell(0, 6))->setRowHeight(12);
//            $export->setStyle($export->getCell(0, 10))->setRowHeight(10);

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

        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));

        return $fileLocation;
    }
}