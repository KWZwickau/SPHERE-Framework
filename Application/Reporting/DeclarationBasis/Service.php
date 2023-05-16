<?php
namespace SPHERE\Application\Reporting\DeclarationBasis;

use DateTime;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 * @package SPHERE\Application\Reporting\DeclarationBasis
 */
class Service extends Extension
{
    /**
     * @param DateTime $date
     *
     * @return FilePointer|Stage
     */
    public function createDivisionReportExcel(DateTime $date)
    {
        $DataContent = array();
        $DataFocus = array();
        $DataAutismus = array();
        $DataBlind = array();
        $DataHear = array();
        $DataMental = array();
        $DataPhysical = array();
        $DataLanguage = array();
        $DataLearn = array();
        $DataEducation = array();
        $isSaxony = Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_SACHSEN);
        $isBerlin = Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN);
        $YearString = '';
        if(($tblYearList = Term::useService()->getYearAllByDate($date))) {
            $YearString = current($tblYearList)->getYear();
            foreach ($tblYearList as $tblYear) {
                if(($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYear))) {
                    foreach ($tblStudentEducationList as $tblStudentEducation) {
                        $this->fillDataContent($tblStudentEducation, $DataContent);
                        $this->fillDataFocus($tblStudentEducation, $date, $DataFocus, $DataAutismus, $DataBlind, $DataHear, $DataMental, $DataPhysical,
                            $DataLanguage, $DataLearn, $DataEducation);
                    }
                }
            }
        }
        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());
        $ResponsibilityString = '';
        $ResponsibilityStringExtended = '';
        $tblResponsibilityList = Responsibility::useService()->getResponsibilityAll();
        if($tblResponsibilityList) {
            foreach ($tblResponsibilityList as $tblResponsibility) {
                $tblCompanyResponsibility = $tblResponsibility->getServiceTblCompany();
                if($tblCompanyResponsibility) {
                    $ResponsibilityString = $tblCompanyResponsibility->getName();
                    $ResponsibilityStringExtended = $tblCompanyResponsibility->getExtendedName();
                }
            }
        }
        // get SchoolList
        $tblSchoolList = School::useService()->getSchoolAll();
        $IsFirstTab = true;
        ksort($DataContent);
        foreach ($DataContent as $Type => $LevelList) {
            $SchoolString = '';
            $SchoolStringExtended = '';
            $tblSchoolActive = false;
            if($tblSchoolList) {
                foreach ($tblSchoolList as $tblSchool) {
                    if($tblSchool->getServiceTblType() && $tblSchool->getServiceTblType()->getName() == $Type) {
                        $tblSchoolActive = $tblSchool;
                    }
                }
            }
            if($tblSchoolActive) {
                $tblCompany = $tblSchoolActive->getServiceTblCompany();
                if($tblCompany) {
                    $SchoolString = $tblCompany->getName();
                    $SchoolStringExtended = $tblCompany->getExtendedName();
                }
            }
            $row = 0;
            $TypeWorksheet = str_replace('/', '-', $Type);
            // choose/create Page
            if($IsFirstTab === true) {
                $export->renameWorksheet($TypeWorksheet);
                $IsFirstTab = false;
            } else {
                $export->createWorksheet($TypeWorksheet);
            }
            // set Page Options
            $PaperOrientation = new PaperOrientationParameter('LANDSCAPE');
            $export->setPaperOrientationParameter($PaperOrientation);
            $export->setWorksheetFitToPage();
            // Header
            if($isSaxony) {
                $export->setValue($export->getCell(0, $row), "Meldung der Schülerzahl gem. § 14 Abs. 2 SächsFrTrSchulG i.V.m. § 8 ZuschussVO");
                $export->setStyle($export->getCell(0, $row), $export->getCell(15, $row))->setFontSize(12)->mergeCells()->setFontBold()->setAlignmentCenter();
                $row++;
                $export->setValue($export->getCell(0, $row), "zum Antrag vom ………………….. auf Gewährung von Zuschüssen für Schulen in freier Trägerschaft");
                $export->setStyle($export->getCell(0, $row), $export->getCell(15, $row))->setFontSize(12)->mergeCells()->setFontBold()->setAlignmentCenter();
                $row++;
                $export->setValue($export->getCell(0, $row), "für allgemeinbildende Schulen und allgemeinbildende Förderschulen");
                $export->setStyle($export->getCell(0, $row), $export->getCell(15, $row))->setFontSize(12)->mergeCells()->setFontBold()->setAlignmentCenter();
                $row++;
                $export->setValue($export->getCell(0, $row), " - Abgabe beim Landesamt für Schule und Bildung spätestens:    24. Oktober - ");
                $export->setStyle($export->getCell(0, $row), $export->getCell(15, $row))->setRowHeight(23)->mergeCells()->setFontBold()->setAlignmentCenter()
                    ->setAlignmentMiddle()->setFontColor('FFFF0000')->setFontItalic();
                $row++;
            }
            $export->setValue($export->getCell(0, $row), "Schulträger:");
            $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row))->setFontItalic();
            $FirstBoxHeight = $row;
            $row++;
            $export->setValue($export->getCell(0, $row), $ResponsibilityString);
            $export->setStyle($export->getCell(0, $row), $export->getCell(4, ($row)))->mergeCells();
            $export->setValue($export->getCell(7, $row), "Stichtag, Datum: ...........");
            $export->setStyle($export->getCell(7, $row), $export->getCell(15, ($row)))->mergeCells()->setFontSize(12)->setFontBold()->setFontColor('FFFF0000');
            $row++;
            $export->setValue($export->getCell(0, $row), $ResponsibilityStringExtended);
            $export->setStyle($export->getCell(0, $row), $export->getCell(4, ($row)))->mergeCells();
            if($isSaxony) {
                $export->setValue($export->getCell(7, $row), "(10. Oktober oder abweichender Stichtag  gem. § 8 Abs. 3 Satz 6 ZuschussVO:
                    Fällt ein Stichtag auf einen unterrichtsfreien Tag, gilt der letzte vorhergehende
                    Unterrichtstag als Stichtag. Dieser ist anzugeben.)");
                $export->setStyle($export->getCell(7, $row), $export->getCell(15, ($row + 2)))->mergeCells()->setFontSize(10.5)->setFontItalic()
                    ->setFontColor('FFFF0000')->setWrapText();
            }
            $row++;
            $export->setValue($export->getCell(0, $row), "Name u. Standort der Schule:");
            $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row))->setFontItalic();
            $row++;
            $export->setValue($export->getCell(0, $row), $SchoolString);
            $export->setStyle($export->getCell(0, $row), $export->getCell(4, ($row)))->mergeCells();
            $row++;
            $export->setValue($export->getCell(0, $row), $SchoolStringExtended);
            $export->setStyle($export->getCell(0, $row), $export->getCell(4, ($row)))->mergeCells();
            // Rahmen erste Box
            $export->setStyle($export->getCell(0, $FirstBoxHeight), $export->getCell(4, $row))->setBorderOutline(2);
            $row++;
            $row++;
            $export->setValue($export->getCell(0, $row), "Schulart:");
            $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row))->setFontBold()->setAlignmentRight();
            $export->setValue($export->getCell(1, $row), $Type);
            $export->setStyle($export->getCell(1, $row), $export->getCell(15, $row))->setAlignmentCenter()->mergeCells()->setBorderBottom();
            $row++;
            if($isSaxony) {
                $export->setValue($export->getCell(1, $row), "(Bezeichnung entsprechend der Anlage zu § 1 ZuschussVO)");
                $export->setStyle($export->getCell(1, $row), $export->getCell(15, $row))->setAlignmentCenter()->setFontItalic()->mergeCells();
            }
            $row++;
            $export->setStyle($export->getCell(0, $row), $export->getCell(15, $row))->setBorderBottom(2);
            $row++;
            // 16 Spalten in die Breite
            $export->setValue($export->getCell(0, $row), "Schuljahr");
            $export->setStyle($export->getCell(0, $row), $export->getCell(0, ($row + 1)))->mergeCells()->setBorderLeft(2)->setBorderRight()->setBorderBottom()
                ->setAlignmentMiddle()->setAlignmentCenter();
            $export->setValue($export->getCell(1, $row), "Schülerzahl in Klassenstufe");
            $export->setStyle($export->getCell(1, $row), $export->getCell(15, $row))->mergeCells()->setBorderLeft()->setBorderRight(2)->setBorderBottom()
                ->setAlignmentCenter()->setFontBold();
            $row++;
            // Klassenspaltenstyle
            $export->setStyle($export->getCell(1, $row), $export->getCell(13, $row))->setBorderAll()->setBorderRight(2)->setRowHeight(40)->setAlignmentCenter()
                ->setAlignmentMiddle();
            $export->setValue($export->getCell(1, $row), "1");
            $export->setValue($export->getCell(2, $row), "2");
            $export->setValue($export->getCell(3, $row), "3");
            $export->setValue($export->getCell(4, $row), "4");
            $export->setValue($export->getCell(5, $row), "5");
            $export->setValue($export->getCell(6, $row), "6");
            $export->setValue($export->getCell(7, $row), "7");
            $export->setValue($export->getCell(8, $row), "8");
            $export->setValue($export->getCell(9, $row), "9");
            $export->setValue($export->getCell(10, $row), "10");
            $export->setValue($export->getCell(11, $row), "11");
            $export->setValue($export->getCell(12, $row), "12");
            $export->setValue($export->getCell(13, $row), "(13)");
            $export->setValue($export->getCell(14, $row), "Gesamt- schülerzahl");
            $export->setStyle($export->getCell(14, $row))->setBorderRight(2)->setBorderBottom(2)->setWrapText()->setFontBold()->setAlignmentCenter()
                ->setAlignmentMiddle();
            $export->setValue($export->getCell(15, $row), "davon:
Kostenerstattung durch andere öffentlichen Träger");
            $export->setStyle($export->getCell(14, $row))->setBorderRight(2)->setBorderBottom(2)->setWrapText()->setFontBold()->setAlignmentCenter()
                ->setAlignmentMiddle();
            $export->setStyle($export->getCell(15, $row))->setBorderRight(2)->setBorderBottom(2)->setWrapText()->setFontBold()->setFontSize(7.5)
                ->setAlignmentCenter()->setAlignmentMiddle();
            $row++;
            $export->setValue($export->getCell(0, $row), $YearString);
            $Sum = 0;
            ksort($LevelList);
            foreach ($LevelList as $Level => $StudentCount) {
                $export->setValue($export->getCell($Level, $row), $StudentCount);
                if($StudentCount) {
                    $Sum += $StudentCount;
                }
            }
            $export->setValue($export->getCell(14, $row), $Sum);
            $export->setStyle($export->getCell(0, $row))->setFontBold();
            $export->setStyle($export->getCell(0, $row), $export->getCell(13, $row))->setBorderLeft(2)->setBorderRight(2)->setBorderBottom()
                ->setBorderVertical()->setAlignmentCenter()->setAlignmentMiddle()->setRowHeight(23);
            $export->setStyle($export->getCell(14, $row), $export->getCell(15, $row))->setBorderRight(2)->setBorderBottom()->setBorderVertical(2)
                ->setAlignmentCenter()->setAlignmentMiddle();
            $row++;
            $export->setValue($export->getCell(0, $row), "Angabe des Förderschultyps");
            $export->setStyle($export->getCell(0, $row))->setBorderLeft(2)->setBorderBottom(2)->setBorderRight()->setFontBold()->setAlignmentMiddle()
                ->setRowHeight(23);
            $export->setValue($export->getCell(1, $row), "davon: Anzahl der Integrationsschüler");
            $export->setStyle($export->getCell(1, $row), $export->getCell(13, $row))->mergeCells()->setBorderBottom(2)->setBorderRight(2)->setFontBold()
                ->setAlignmentMiddle();
            $export->setStyle($export->getCell(14, $row), $export->getCell(15, $row))->setBorderBottom(2)->setBorderVertical(2)->setBorderRight(2);
            $row++;
            if($isBerlin) {
                $export->setValue($export->getCell(0, $row), "FSP Autismus");
                // Autismus Insert
                $SumAutismus = 0;
                if(isset($DataAutismus[$Type]) && !empty($DataAutismus[$Type])) {
                    foreach ($DataAutismus[$Type] as $Level => $StudentCount) {
                        $export->setValue($export->getCell($Level, $row), $StudentCount);
                        if($StudentCount) {
                            $SumAutismus += $StudentCount;
                        }
                    }
                }
                $export->setValue($export->getCell(14, $row), $SumAutismus);
                $export->setStyle($export->getCell(0, $row))->setBorderLeft(2)->setBorderBottom()->setBorderRight();
                $export->setStyle($export->getCell(1, $row), $export->getCell(13, $row))->setBorderLeft()->setBorderBottom()->setBorderVertical()
                    ->setBorderRight(2)->setAlignmentCenter();
                $export->setStyle($export->getCell(14, $row), $export->getCell(15, $row))->setBorderBottom()->setBorderVertical(2)->setBorderRight(2)
                    ->setAlignmentCenter();
                $row++;
            }
            $export->setValue($export->getCell(0, $row), "FSP Sehen");
            // Blind Insert
            $SumBlind = 0;
            if(isset($DataBlind[$Type]) && !empty($DataBlind[$Type])) {
                foreach ($DataBlind[$Type] as $Level => $StudentCount) {
                    $export->setValue($export->getCell($Level, $row), $StudentCount);
                    if($StudentCount) {
                        $SumBlind += $StudentCount;
                    }
                }
            }
            $export->setValue($export->getCell(14, $row), $SumBlind);
            $export->setStyle($export->getCell(0, $row))->setBorderLeft(2)->setBorderBottom()->setBorderRight();
            $export->setStyle($export->getCell(1, $row), $export->getCell(13, $row))->setBorderLeft()->setBorderBottom()->setBorderVertical()
                ->setBorderRight(2)->setAlignmentCenter();
            $export->setStyle($export->getCell(14, $row), $export->getCell(15, $row))->setBorderBottom()->setBorderVertical(2)->setBorderRight(2)
                ->setAlignmentCenter();
            $row++;
            $export->setValue($export->getCell(0, $row), "FSP Hören");
            // Hear Insert
            $SumHear = 0;
            if(isset($DataHear[$Type]) && !empty($DataHear[$Type])) {
                foreach ($DataHear[$Type] as $Level => $StudentCount) {
                    $export->setValue($export->getCell($Level, $row), $StudentCount);
                    if($StudentCount) {
                        $SumHear += $StudentCount;
                    }
                }
            }
            $export->setValue($export->getCell(14, $row), $SumHear);
            $export->setStyle($export->getCell(0, $row))->setBorderLeft(2)->setBorderBottom()->setBorderRight();
            $export->setStyle($export->getCell(1, $row), $export->getCell(13, $row))->setBorderLeft()->setBorderBottom()->setBorderVertical()
                ->setBorderRight(2)->setAlignmentCenter();
            $export->setStyle($export->getCell(14, $row), $export->getCell(15, $row))->setBorderBottom()->setBorderVertical(2)->setBorderRight(2)
                ->setAlignmentCenter();
            $row++;
            $export->setValue($export->getCell(0, $row), "FSP geistige Entwicklung");
            // Mental Insert
            $SumMental = 0;
            if(isset($DataMental[$Type]) && !empty($DataMental[$Type])) {
                foreach ($DataMental[$Type] as $Level => $StudentCount) {
                    $export->setValue($export->getCell($Level, $row), $StudentCount);
                    if($StudentCount) {
                        $SumMental += $StudentCount;
                    }
                }
            }
            $export->setValue($export->getCell(14, $row), $SumMental);
            $export->setStyle($export->getCell(0, $row))->setBorderLeft(2)->setBorderBottom()->setBorderRight();
            $export->setStyle($export->getCell(1, $row), $export->getCell(13, $row))->setBorderLeft()->setBorderBottom()->setBorderVertical()
                ->setBorderRight(2)->setAlignmentCenter();
            $export->setStyle($export->getCell(14, $row), $export->getCell(15, $row))->setBorderBottom()->setBorderVertical(2)->setBorderRight(2)
                ->setAlignmentCenter();
            $row++;
            $export->setValue($export->getCell(0, $row), "FSP körp. und mot. Entwicklung.");
            // Physical Insert
            $SumPhysical = 0;
            if(isset($DataPhysical[$Type]) && !empty($DataPhysical[$Type])) {
                foreach ($DataPhysical[$Type] as $Level => $StudentCount) {
                    $export->setValue($export->getCell($Level, $row), $StudentCount);
                    if($StudentCount) {
                        $SumPhysical += $StudentCount;
                    }
                }
            }
            $export->setValue($export->getCell(14, $row), $SumPhysical);
            $export->setStyle($export->getCell(0, $row))->setBorderLeft(2)->setBorderBottom()->setBorderRight();
            $export->setStyle($export->getCell(1, $row), $export->getCell(13, $row))->setBorderLeft()->setBorderBottom()->setBorderVertical()->setBorderRight(2)
                ->setAlignmentCenter();
            $export->setStyle($export->getCell(14, $row), $export->getCell(15, $row))->setBorderBottom()->setBorderVertical(2)->setBorderRight(2)
                ->setAlignmentCenter();
            $row++;
            $export->setValue($export->getCell(0, $row), "FSP Sprache");
            // Language Insert
            $SumLanguage = 0;
            if(isset($DataLanguage[$Type]) && !empty($DataLanguage[$Type])) {
                foreach ($DataLanguage[$Type] as $Level => $StudentCount) {
                    $export->setValue($export->getCell($Level, $row), $StudentCount);
                    if($StudentCount) {
                        $SumLanguage += $StudentCount;
                    }
                }
            }
            $export->setValue($export->getCell(14, $row), $SumLanguage);
            $export->setStyle($export->getCell(0, $row))->setBorderLeft(2)->setBorderBottom()->setBorderRight();
            $export->setStyle($export->getCell(1, $row), $export->getCell(13, $row))->setBorderLeft()->setBorderBottom()->setBorderVertical()
                ->setBorderRight(2)->setAlignmentCenter();
            $export->setStyle($export->getCell(14, $row), $export->getCell(15, $row))->setBorderBottom()->setBorderVertical(2)->setBorderRight(2)
                ->setAlignmentCenter();
            $row++;
            $export->setValue($export->getCell(0, $row), "FSP Lernen");
            // Lern Insert
            $SumLern = 0;
            if(isset($DataLearn[$Type]) && !empty($DataLearn[$Type])) {
                foreach ($DataLearn[$Type] as $Level => $StudentCount) {
                    $export->setValue($export->getCell($Level, $row), $StudentCount);
                    if($StudentCount) {
                        $SumLern += $StudentCount;
                    }
                }
            }
            $export->setValue($export->getCell(14, $row), $SumLern);
            $export->setStyle($export->getCell(0, $row))->setBorderLeft(2)->setBorderBottom()->setBorderRight();
            $export->setStyle($export->getCell(1, $row), $export->getCell(13, $row))->setBorderLeft()->setBorderBottom()->setBorderVertical()
                ->setBorderRight(2)->setAlignmentCenter();
            $export->setStyle($export->getCell(14, $row), $export->getCell(15, $row))->setBorderBottom()->setBorderVertical(2)->setBorderRight(2)
                ->setAlignmentCenter();
            $row++;
            $export->setValue($export->getCell(0, $row), "FSP emot. und soz. Entwicklung");
            // Education Insert
            $SumEducation = 0;
            if(isset($DataEducation[$Type]) && !empty($DataEducation[$Type])) {
                foreach ($DataEducation[$Type] as $Level => $StudentCount) {
                    $export->setValue($export->getCell($Level, $row), $StudentCount);
                    if($StudentCount) {
                        $SumEducation += $StudentCount;
                    }
                }
            }
            $export->setValue($export->getCell(14, $row), $SumEducation);
            $export->setStyle($export->getCell(0, $row))->setBorderLeft(2)->setBorderBottom(2)->setBorderRight();
            $export->setStyle($export->getCell(1, $row), $export->getCell(13, $row))->setBorderLeft()->setBorderBottom(2)->setBorderVertical()
                ->setBorderRight(2)->setAlignmentCenter();
            $export->setStyle($export->getCell(14, $row), $export->getCell(15, $row))->setBorderBottom(2)->setBorderVertical(2)->setBorderRight(2)
                ->setAlignmentCenter();
            $row++;
            $export->setValue($export->getCell(0, $row), 'Hinweis:  Schüler, die inklusiv unterrichtet werden, sind dem
Förderschultyp zuzuordnen, den sie ohne inklusive Beschulung besuchen würden.  ');
            $export->setStyle($export->getCell(0, $row), $export->getCell(15, $row))->mergeCells()->setFontBold()->setRowHeight(15)->setAlignmentBottom();
            $row++;
            $export->setValue($export->getCell(0, $row), '                    Eine Namensliste unter Angabe des
Förderschwerpunktes (FSP) ist der Meldung zusätzlich beizufügen.');
            $export->setStyle($export->getCell(0, $row), $export->getCell(15, $row))->mergeCells()->setFontBold()->setRowHeight(17)->setAlignmentTop();
            $row++;
            if($isSaxony) {
                $export->setValue($export->getCell(0, $row), '§ 14 Abs. 2 Nr. 1 SächsFrTrSchulG: Ein Schüler wird beschult, wenn er am maßgeblichen Stichtag aufgrund eines Vertragsverhältnisses am Unterricht teilnimmt
 oder entschuldigt nicht teilnimmt. Ist das Vertragsverhältnis am Stichtag bereits gekündigt und hat der Schüler den Schulbesuch am Stichtag bereits
 endgültig beendet oder abgebrochen, gilt er nicht als beschult.');
                $export->setStyle($export->getCell(0, $row), $export->getCell(15, $row))->setFontSize(10)->mergeCells()->setRowHeight(40)->setWrapText();
            }
            $row += 3;
            $export->setValue($export->getCell(0, $row), (new DateTime())->format('d.m.Y'));
            $export->setStyle($export->getCell(0, $row))->setBorderBottom();
            $export->setStyle($export->getCell(8, $row), $export->getCell(15, $row))->setBorderBottom();
            $row++;
            $export->setValue($export->getCell(0, $row), 'Datum');
            $export->setValue($export->getCell(8, $row), 'Unterschrift');
            $row++;
            $export->setValue($export->getCell(8, $row), 'Vorsitzende(r) / Geschäftsführer(in) des Schulträgers');
            // Spaltenhöhe Definieren
            if($isSaxony) {
                $export->setStyle($export->getCell(0, 10))->setRowHeight(10);
            } elseif($isBerlin) {
                $export->setStyle($export->getCell(0, 6))->setRowHeight(10);
            }
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
        // new last Page if Integrativ exist
        ksort($DataFocus);
        if(!empty($DataFocus)) {
            foreach ($DataFocus as $TypeIntegrativeList => $LevelList) {
                $row = 0;
                // create Page
                $CourseTypeName = str_replace('/', '-', $TypeIntegrativeList);
                $PageTitle = substr('Namensliste '.$CourseTypeName, 0, 30);
                $export->createWorksheet($PageTitle);
                // set Page Options
                $PaperOrientation = new PaperOrientationParameter('LANDSCAPE');
                $export->setPaperOrientationParameter($PaperOrientation);
                //Header
                $export->setValue($export->getCell(0, $row), "Namentliche Auflistung der gemeldeten Inklusionsschüler");
                $export->setStyle($export->getCell(0, $row), $export->getCell(9, $row))->mergeCells()->setFontBold()->setAlignmentCenter()->setFontSize(14)
                    ->setRowHeight(20);
                $row += 2;
                // Adresse suchen
                $SchoolString = '';
                $SchoolStringExtended = '';
                $AddressCompanyStreet = '';
                $AddressCompanyCodeCity = '';
                $tblSchoolActive = false;
                if($tblSchoolList) {
                    foreach ($tblSchoolList as $tblSchool) {
                        if($tblSchool->getServiceTblType() && $tblSchool->getServiceTblType()->getName() == $TypeIntegrativeList) {
                            $tblSchoolActive = $tblSchool;
                        }
                    }
                }
                if($tblSchoolActive) {
                    $tblCompany = $tblSchoolActive->getServiceTblCompany();
                    if($tblCompany) {
                        $SchoolString = $tblCompany->getName();
                        $SchoolStringExtended = $tblCompany->getExtendedName();
                        $tblAddressCompany = Address::useService()->getAddressByCompany($tblCompany);
                        if($tblAddressCompany) {
                            $AddressCompanyStreet = $tblAddressCompany->getStreetName().' '.$tblAddressCompany->getStreetNumber();
                            $tblCity = $tblAddressCompany->getTblCity();
                            if($tblCity) {
                                $AddressCompanyCodeCity = $tblCity->getCode().' '.$tblCity->getDisplayName();
                            }
                        }
                    }
                }
                // Adresse abbilden
                $export->setValue($export->getCell(0, $row), 'Name der Schule');
                $export->setStyle($export->getCell(0, $row))->setFontBold();
                $row++;
                $rowStartAddress = $row;
                $export->setValue($export->getCell(0, $row), 'Anschrift (Straße, Hausnummer, PLZ, Ort)');
                $export->setStyle($export->getCell(0, $row))->setFontSize(10);
                $row++;
                $export->setValue($export->getCell(0, $row), $SchoolString);
                $export->setStyle($export->getCell(0, $row), $export->getCell(4, $row))->mergeCells();
                $row++;
                if($SchoolStringExtended != '') {
                    $export->setValue($export->getCell(0, $row), $SchoolStringExtended);
                    $export->setStyle($export->getCell(0, $row), $export->getCell(4, $row))->mergeCells();
                    $row++;
                }
                $export->setValue($export->getCell(0, $row), $AddressCompanyStreet);
                $export->setStyle($export->getCell(0, $row), $export->getCell(4, $row))->mergeCells();
                $row++;
                $export->setValue($export->getCell(0, $row), $AddressCompanyCodeCity);
                $export->setStyle($export->getCell(0, $row), $export->getCell(4, $row))->mergeCells();
                if($SchoolStringExtended == '') {
                    $row++;
                    $export->setValue($export->getCell(0, $row), '');
                    $export->setStyle($export->getCell(0, $row), $export->getCell(4, $row))->mergeCells();
                }
                // Rahmen
                $export->setStyle($export->getCell(0, $rowStartAddress), $export->getCell(4, $row))->setBorderOutline(2);
                $row += 2;
                $export->setValue($export->getCell(0, $row), 'Bildungsgang (Schulart):');
                $export->setStyle($export->getCell(0, $row), $export->getCell(2, $row))->mergeCells()->setAlignmentRight()->setFontBold();
                $export->setValue($export->getCell(3, $row), $TypeIntegrativeList);
                $export->setStyle($export->getCell(3, $row), $export->getCell(9, $row))->mergeCells()->setAlignmentCenter()->setBorderBottom();
                $row++;
                if($isSaxony) {
                    $export->setValue($export->getCell(3, $row), '(Bezeichnung entsprechend der Anlage zu § 1 ZuschussVO)');
                    $export->setStyle($export->getCell(3, $row), $export->getCell(9, $row))->mergeCells()->setAlignmentCenter()->setFontItalic();
                }
                $row += 2;
                $rowStart = $row;
                // Header
                $export->setValue($export->getCell(0, $row), 'Name');
                $export->setStyle($export->getCell(0, $row), $export->getCell(1, $row))->mergeCells()->setWrapText()->setAlignmentCenter()->setAlignmentMiddle()
                    ->setFontBold();
                $export->setValue($export->getCell(2, $row), 'Vorname');
                $export->setStyle($export->getCell(2, $row), $export->getCell(3, $row))->mergeCells()->setWrapText()->setAlignmentCenter()->setAlignmentMiddle()
                    ->setFontBold();
                $export->setValue($export->getCell(4, $row), 'Datum des Bescheids');
                $export->setStyle($export->getCell(4, $row))->setWrapText()->setAlignmentCenter()->setAlignmentMiddle()->setFontBold();
                $export->setValue($export->getCell(5, $row), 'Klassen- stufe');
                $export->setStyle($export->getCell(5, $row))->setWrapText()->setAlignmentCenter()->setAlignmentMiddle()->setFontBold();
                $export->setValue($export->getCell(6, $row), 'Förderschwerpunkt');
                $export->setStyle($export->getCell(6, $row), $export->getCell(8, $row))->mergeCells()->setWrapText()->setAlignmentCenter()->setAlignmentMiddle()
                    ->setFontBold();
                $export->setValue($export->getCell(9, $row), 'Bemerkung');
                $export->setStyle($export->getCell(9, $row))->setWrapText()->setAlignmentCenter()->setAlignmentMiddle()->setFontBold();
                $row++;
                ksort($LevelList);
                foreach ($LevelList as $LevelName => $FocusList) {
                    foreach ($FocusList as $FocusName => $tblSupportList) {
                        foreach ($tblSupportList as $SupportId) {
                            if(($tblSupport = Student::useService()->getSupportById($SupportId))
                                && ($tblPersonIntegrative = $tblSupport->getServiceTblPerson())
                            ) {
                                $export->setValue($export->getCell(0, $row), $tblPersonIntegrative->getLastName());
                                $export->setStyle($export->getCell(0, $row), $export->getCell(1, $row))->mergeCells()->setAlignmentMiddle();
                                $export->setValue($export->getCell(2, $row), $tblPersonIntegrative->getFirstName());
                                $export->setStyle($export->getCell(2, $row), $export->getCell(3, $row))->mergeCells()->setAlignmentMiddle();
                                $DecisionDate = $tblSupport->getDate();
                                $IntegrationDescription = $tblSupport->getRemark(false);
                                $export->setValue($export->getCell(4, $row), $DecisionDate);
                                $export->setStyle($export->getCell(4, $row))->setAlignmentCenter()->setAlignmentMiddle();
                                $export->setValue($export->getCell(5, $row), $LevelName);
                                $export->setStyle($export->getCell(5, $row))->setAlignmentCenter()->setAlignmentMiddle();
                                $export->setValue($export->getCell(6, $row), $FocusName);
                                $export->setStyle($export->getCell(6, $row), $export->getCell(8, $row))->setAlignmentMiddle()->mergeCells();
                                $export->setValue($export->getCell(9, $row), $IntegrationDescription);
                                $export->setStyle($export->getCell(9, $row))->setAlignmentMiddle()->setWrapText();
                                $row++;
                            }
                        }
                    }
                }
                // Rahmen
                $export->setStyle($export->getCell(0, $rowStart), $export->getCell((9), ($row - 1)))->setBorderAll()->setBorderOutline(2);
                $export->setWorksheetFitToPage();
                $row += 2;
                $export->setValue($export->getCell(0, $row), (new DateTime())->format('d.m.Y'));
                $export->setStyle($export->getCell(0, $row), $export->getCell(2, $row))->mergeCells()->setBorderBottom();
                $export->setStyle($export->getCell(6, $row), $export->getCell(9, $row))->setBorderBottom();
                $row++;
                $export->setValue($export->getCell(0, $row), 'Datum');
                $export->setValue($export->getCell(6, $row), 'Unterschrift');
                $row++;
                $export->setValue($export->getCell(6, $row), 'Vorsitzende(r) / Geschäftsführer(in) des Schulträgers');
                // Spaltenbreite Definieren + nach rechts rücken
                $export->setStyle($export->getCell(0, 0))->setColumnWidth(8.5);
                $export->setStyle($export->getCell(1, 0))->setColumnWidth(8.5);
                $export->setStyle($export->getCell(2, 0))->setColumnWidth(8.5);
                $export->setStyle($export->getCell(3, 0))->setColumnWidth(8.5);
                $export->setStyle($export->getCell(4, 0))->setColumnWidth(12.0);
                $export->setStyle($export->getCell(5, 0))->setColumnWidth(10.5);
                $export->setStyle($export->getCell(6, 0))->setColumnWidth(10.5);
                $export->setStyle($export->getCell(7, 0))->setColumnWidth(10.5);
                $export->setStyle($export->getCell(8, 0))->setColumnWidth(10.5);
                $export->setStyle($export->getCell(9, 0))->setColumnWidth(34.5);
            }
        }
        $export->selectWorksheetByIndex(0);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     * @param array               $DataContent
     *
     * @return void
     */
    private function fillDataContent(TblStudentEducation $tblStudentEducation, array &$DataContent)
    {

        $level = $tblStudentEducation->getLevel();
        $Type = '';
        if(($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())){
            $Type = $tblSchoolType->getName();
        }
        if(isset($DataContent[$Type][$level])) {
            $DataContent[$Type][$level] += 1;
        } else {
            $DataContent[$Type][$level] = 1;
        }
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     * @param DateTime            $date
     * @param array               $DataFocus
     * @param array               $DataAutismus
     * @param array               $DataBlind
     * @param array               $DataHear
     * @param array               $DataMental
     * @param array               $DataPhysical
     * @param array               $DataLanguage
     * @param array               $DataLearn
     * @param array               $DataEducation
     *
     * @return void
     */
    private function fillDataFocus(TblStudentEducation $tblStudentEducation, DateTime $date, array &$DataFocus, array  &$DataAutismus, array &$DataBlind,
        array &$DataHear, array &$DataMental, array &$DataPhysical, array &$DataLanguage, array &$DataLearn, array &$DataEducation)
    {

        $tblPerson = $tblStudentEducation->getServiceTblPerson();
        $level = $tblStudentEducation->getLevel();
        $Type = '';
        if(($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())){
            $Type = $tblSchoolType->getName();
        }
        if($tblPerson
            && ($tblSupport = Student::useService()->getSupportForReportingByPerson($tblPerson, $date))
            && ($tblSupportFocus = Student::useService()->getSupportPrimaryFocusBySupport($tblSupport))
            && ($tblSupportFocusType = $tblSupportFocus->getTblSupportFocusType())
        ) {
            $focusName = $tblSupportFocusType->getName();
            // füllen der Förderschwerpunkte
            if($focusName == 'Autismus') {
                if(isset($DataBlind[$Type][$level])) {
                    $DataAutismus[$Type][$level] += 1;
                } else {
                    $DataAutismus[$Type][$level] = 1;
                }
                $DataFocus[$Type][$level][$focusName][] = $tblSupport->getId();
            }
            if($focusName == 'Sehen') {
                if(isset($DataBlind[$Type][$level])) {
                    $DataBlind[$Type][$level] += 1;
                } else {
                    $DataBlind[$Type][$level] = 1;
                }
                $DataFocus[$Type][$level][$focusName][] = $tblSupport->getId();
            }
            if($focusName == 'Hören') {
                if(isset($DataHear[$Type][$level])) {
                    $DataHear[$Type][$level] += 1;
                } else {
                    $DataHear[$Type][$level] = 1;
                }
                $DataFocus[$Type][$level][$focusName][] = $tblSupport->getId();
            }
            if($focusName == 'Geistige Entwicklung') {
                if(isset($DataMental[$Type][$level])) {
                    $DataMental[$Type][$level] += 1;
                } else {
                    $DataMental[$Type][$level] = 1;
                }
                $DataFocus[$Type][$level][$focusName][] = $tblSupport->getId();
            }
            if($focusName == 'Körperlich-motorische Entwicklung') {
                if(isset($DataPhysical[$Type][$level])) {
                    $DataPhysical[$Type][$level] += 1;
                } else {
                    $DataPhysical[$Type][$level] = 1;
                }
                $DataFocus[$Type][$level][$focusName][] = $tblSupport->getId();
            }
            if($focusName == 'Sprache') {
                if(isset($DataLanguage[$Type][$level])) {
                    $DataLanguage[$Type][$level] += 1;
                } else {
                    $DataLanguage[$Type][$level] = 1;
                }
                $DataFocus[$Type][$level][$focusName][] = $tblSupport->getId();
            }
            if($focusName == 'Lernen') {
                if(isset($DataLearn[$Type][$level])) {
                    $DataLearn[$Type][$level] += 1;
                } else {
                    $DataLearn[$Type][$level] = 1;
                }
                $DataFocus[$Type][$level][$focusName][] = $tblSupport->getId();
            }
            if($focusName == 'Emotionale-soziale Entwicklung') {
                if(isset($DataEducation[$Type][$level])) {
                    $DataEducation[$Type][$level] += 1;
                } else {
                    $DataEducation[$Type][$level] = 1;
                }
                $DataFocus[$Type][$level][$focusName][] = $tblSupport->getId();
            }
        }
    }
}