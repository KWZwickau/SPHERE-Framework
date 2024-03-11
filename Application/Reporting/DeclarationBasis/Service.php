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
use SPHERE\Application\Education\School\Type\Service\Entity\TblCategory;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Application\Setting\Consumer\School\Service\Entity\TblSchool;
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
        // Prepare Sorting
        $DataContent = array(
            TblType::IDENT_GRUND_SCHULE => array(),
            TblType::IDENT_OBER_SCHULE => array(),
            TblType::IDENT_GYMNASIUM => array(),
        );
        $DataFocus = array();
        $DataTechnicalContent = array();
        $YearString = '';
        if(($tblYearList = Term::useService()->getYearAllByDate($date))) {
            $YearString = current($tblYearList)->getYear();
            foreach ($tblYearList as $tblYear) {
                if(($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYear))) {
                    foreach ($tblStudentEducationList as $tblStudentEducation) {
                        $DataContent = $this->fillDataContent($tblStudentEducation, $DataContent);
                        $DataFocus = $this->fillDataFocus($tblStudentEducation, $date, $DataFocus);
                        $DataTechnicalContent = $this->fillDataTechnicalContent($tblStudentEducation, $DataTechnicalContent);
                    }
                }
            }
        }

        $fileLocation = Storage::createFilePointer('xlsx');
        /** @var PhpExcel $export */
        $export = Document::getDocument($fileLocation->getFileLocation());

        $tblSchoolList = School::useService()->getSchoolAll();
        $IsFirstTab = true;
        foreach ($DataContent as $Type => $LevelList) {
            // prepared content
            $tblSchoolActive = $this->getSchoolByType($tblSchoolList, $Type);
            // Seiten Generieren
            $this->buildStudentCountPage($export, $IsFirstTab, $Type, $LevelList, $DataFocus, $YearString, $tblSchoolActive);
            if(isset($DataFocus[$Type]) && !empty($DataFocus[$Type])){
                // Zusatzseite Namensliste Integration
                $this->buildStudentIntegrationListPage($export, $Type, $DataFocus[$Type], $tblSchoolActive);
            }
        }
        // Berufsbildende Schulen anfügen
        // alphabetisch sortiert (Schulart)
        ksort($DataTechnicalContent);
        foreach ($DataTechnicalContent as $Type => $SchoolCourseList) {
            // alphabetisch sortiert (Bildungsgang / Berufsbezeichnung / Ausbildung)
            ksort($SchoolCourseList);
            foreach($SchoolCourseList as $SchoolCourse => $LevelList){
                // prepared content
                    // Fachschüler der Fachoberschule haben kein "Bildungsgang / Berufsbezeichnung / Ausbildung"
                if($SchoolCourse == 0){
                    $SchoolCourse = '';
                    $tblSchoolTempList = School::useService()->getSchoolAll();
                    foreach($tblSchoolTempList as $tblSchoolTemp){
                        if(($tblSchoolTypeTemp = $tblSchoolTemp->getServiceTblType())){
                            if($tblSchoolTypeTemp->getTblCategory()->getIdentifier() == TblCategory::TECHNICAL){
                                $tblSchoolActive = $tblSchoolTemp;
                            }
                        }
                    }
                } else {
                    $tblSchoolActive = $this->getSchoolByType($tblSchoolList, $Type);
                }

                // Seiten Generieren
                $this->buildStudentTechnicalCountPage($export, $IsFirstTab, $Type, $SchoolCourse, $LevelList, $YearString, $tblSchoolActive, $date);
            }
        }

        $export->selectWorksheetByIndex(0);
        $export->saveFile(new FileParameter($fileLocation->getFileLocation()));
        return $fileLocation;
    }

    /**
     * @param TblSchool[] $tblSchoolList
     * @param string      $Type
     *
     * @return false
     */
    private function getSchoolByType($tblSchoolList, $Type)
    {

        $tblSchoolActive = false;
        if($tblSchoolList) {
            foreach ($tblSchoolList as $tblSchool) {
                if($tblSchool->getServiceTblType() && $tblSchool->getServiceTblType()->getName() == $Type) {
                    $tblSchoolActive = $tblSchool;
                }
            }
        }
        return $tblSchoolActive;
    }

    /**
     * @param PhpExcel $export
     * @param          $IsFirstTab
     * @param          $Type
     * @param          $LevelList
     * @param          $DataFocus
     * @param          $YearString
     * @param          $tblSchoolActive
     *
     * @return PhpExcel
     * @throws \MOC\V\Component\Document\Component\Exception\ComponentException
     */
    private function buildStudentCountPage(PhpExcel $export, &$IsFirstTab, $Type, $LevelList, $DataFocus, $YearString, $tblSchoolActive)
    {

        // get SchoolList
        $isSaxony = Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_SACHSEN);
        $isBerlin = Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN);
        $ResponsibilityString = '';
        $ResponsibilityStringExtended = '';
        $tblResponsibilityList = Responsibility::useService()->getResponsibilityAll();
        if($tblResponsibilityList) {
            $tblResponsibility = current($tblResponsibilityList);
            if(($tblCompanyResponsibility = $tblResponsibility->getServiceTblCompany())) {
                $ResponsibilityString = $tblCompanyResponsibility->getName();
                $ResponsibilityStringExtended = $tblCompanyResponsibility->getExtendedName();
            }
        }

//        ksort($DataContent);
        if(empty($LevelList)){
            return $export;
        }
        $SchoolString = '';
        $SchoolStringExtended = '';
        if($tblSchoolActive) {
            $tblCompany = $tblSchoolActive->getServiceTblCompany();
            if($tblCompany) {
                $SchoolString = $tblCompany->getName();
                $SchoolStringExtended = $tblCompany->getExtendedName();
            }
        }
        $row = 0;
        // Maximum 31 characters allowed in work sheet title
        $TypeWorksheet = str_replace('/', '-', $Type == 'Vorbereitungsklasse mit beruflichem Aspekt' ? 'VKlbA' : $Type);
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
                ->setAlignmentMiddle()->setFontColor('FF0000')->setFontItalic();
            $row++;
        }
        $export->setValue($export->getCell(0, $row), "Schulträger:");
        $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row))->setFontItalic();
        $FirstBoxHeight = $row;
        $row++;
        $export->setValue($export->getCell(0, $row), $ResponsibilityString);
        $export->setStyle($export->getCell(0, $row), $export->getCell(4, ($row)))->mergeCells();
        $export->setValue($export->getCell(7, $row), "Stichtag, Datum: ...........");
        $export->setStyle($export->getCell(7, $row), $export->getCell(15, ($row)))->mergeCells()->setFontSize(12)->setFontBold()->setFontColor('FF0000');
        $row++;
        $export->setValue($export->getCell(0, $row), $ResponsibilityStringExtended);
        $export->setStyle($export->getCell(0, $row), $export->getCell(4, ($row)))->mergeCells();
        if($isSaxony) {
            $export->setValue($export->getCell(7, $row), "(10. Oktober oder abweichender Stichtag  gem. § 8 Abs. 3 Satz 6 ZuschussVO:
                Fällt ein Stichtag auf einen unterrichtsfreien Tag, gilt der letzte vorhergehende
                Unterrichtstag als Stichtag. Dieser ist anzugeben.)");
            $export->setStyle($export->getCell(7, $row), $export->getCell(15, ($row + 2)))->mergeCells()->setFontSize(10.5)->setFontItalic()
                ->setFontColor('FF0000')->setWrapText();
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
        $export->setValue($export->getCell(1, $row), "davon: Anzahl der Inklusionsschüler");
        $export->setStyle($export->getCell(1, $row), $export->getCell(13, $row))->mergeCells()->setBorderBottom(2)->setBorderRight(2)->setFontBold()
            ->setAlignmentMiddle();
        $export->setStyle($export->getCell(14, $row), $export->getCell(15, $row))->setBorderBottom(2)->setBorderVertical(2)->setBorderRight(2);
        $row++;
        if($isBerlin) {
            $this->setFocus($export, 'FSP Autismus', 'Autismus', $DataFocus, $Type, $row);
        }
        $this->setFocus($export, 'FSP Sehen', 'Sehen', $DataFocus, $Type, $row);
        $this->setFocus($export, 'FSP Hören', 'Hören', $DataFocus, $Type, $row);
        $this->setFocus($export, 'FSP geistige Entwicklung', 'Geistige Entwicklung', $DataFocus, $Type, $row);
        $this->setFocus($export, 'FSP körp. und mot. Entwicklung.', 'Körperlich-motorische Entwicklung', $DataFocus, $Type, $row);
        $this->setFocus($export, 'FSP Sprache', 'Sprache', $DataFocus, $Type, $row);
        $this->setFocus($export, 'FSP Lernen', 'Lernen', $DataFocus, $Type, $row);
        $this->setFocus($export, 'FSP emot. und soz. Entwicklung', 'Emotionale-soziale Entwicklung', $DataFocus, $Type, $row);

        $export->setValue($export->getCell(0, $row), 'Hinweis:  Schüler, die inklusiv unterrichtet werden, sind dem
Förderschultyp zuzuordnen, den sie ohne inklusive Beschulung besuchen würden.  ');
        $export->setStyle($export->getCell(0, $row), $export->getCell(15, $row))->mergeCells()->setFontBold()->setRowHeight(15)->setAlignmentBottom();
        $row++;
        $export->setValue($export->getCell(0, $row), '                    Eine Namensliste unter Angabe des
Förderschwerpunktes (FSP) ist der Meldung zusätzlich beizufügen.');
        $export->setStyle($export->getCell(0, $row), $export->getCell(15, $row))->mergeCells()->setFontBold()->setRowHeight(17)->setAlignmentTop();
        $row++;
        $export->setValue($export->getCell(0, $row), '§ 14 Abs. 2 Nr. 1 SächsFrTrSchulG: Ein Schüler wird beschult, wenn er am maßgeblichen Stichtag aufgrund eines Vertragsverhältnisses am Unterricht teilnimmt oder entschuldigt nicht teilnimmt.
Ist das Vertragsverhältnis am Stichtag bereits gekündigt und hat der Schüler den Schulbesuch am Stichtag bereits endgültig beendet oder abgebrochen, gilt er nicht als beschult.');
        $export->setStyle($export->getCell(0, $row), $export->getCell(15, $row))->setFontSize(7)->mergeCells()->setRowHeight(35)->setWrapText();
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

        // Excel wählt den zuletzt bearbeiten Bereich aus -> Bildungsgang
        $export->setStyle($export->getCell(2, 11))->setFontBold(false);

        return $export;
    }

    /**
     * @param PhpExcel $export
     * @param          $TypeIntegrativeList
     * @param array    $LevelList
     * @param          $tblSchoolActive
     *
     * @return void
     * @throws \MOC\V\Component\Document\Component\Exception\ComponentException
     */
    private function buildStudentIntegrationListPage(PhpExcel $export, $TypeIntegrativeList, array $LevelList, $tblSchoolActive)
    {

        $isSaxony = Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_SACHSEN);
        $isBerlin = Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN);

        if(!empty($LevelList)) {
            $row = 0;
            // create Page
            $CourseTypeName = str_replace('/', '-', $TypeIntegrativeList == 'Vorbereitungsklasse mit beruflichem Aspekt' ? 'VKlbA' : $TypeIntegrativeList);
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
                    if($FocusName == 'Count'){
                        continue;
                    }
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
            // Excel wählt den zuletzt bearbeiten Bereich aus -> Bildungsgang
            $export->setStyle($export->getCell(3, 9))->setFontBold(false);
        }
    }

    /**
     * @param PhpExcel  $export
     * @param bool      $IsFirstTab
     * @param string    $Type
     * @param string    $SchoolCourse
     * @param array     $LevelList
     * @param string    $YearString
     * @param TblSchool $tblSchoolActive
     *
     * @return PhpExcel|void
     * @throws \MOC\V\Component\Document\Component\Exception\ComponentException
     */
    private function buildStudentTechnicalCountPage(PhpExcel $export, bool &$IsFirstTab, string $Type, string  $SchoolCourse, array $LevelList,
        string $YearString, TblSchool $tblSchoolActive, DateTime $date)
    {

        $ResponsibilityString = '';
        $ResponsibilityStringExtended = '';
        $tblResponsibilityList = Responsibility::useService()->getResponsibilityAll();
        if($tblResponsibilityList) {
            $tblResponsibility = current($tblResponsibilityList);
            if(($tblCompanyResponsibility = $tblResponsibility->getServiceTblCompany())) {
                $ResponsibilityString = $tblCompanyResponsibility->getName();
                $ResponsibilityStringExtended = $tblCompanyResponsibility->getExtendedName();
            }
        }

        if(empty($LevelList)){
            return $export;
        }
        $SchoolString = '';
        $SchoolStringExtended = '';
        if($tblSchoolActive) {
            $tblCompany = $tblSchoolActive->getServiceTblCompany();
            if($tblCompany) {
                $SchoolString = $tblCompany->getName();
                $SchoolStringExtended = $tblCompany->getExtendedName();
            }
        }
        $row = 0;
        // Maximum 31 characters allowed in work sheet title
        $TypeWorksheet = $Type.($SchoolCourse ? ' '.$SchoolCourse : '');
        $TypeWorksheet = str_replace('/', '_', $TypeWorksheet);
        $TypeWorksheet = str_replace('\\', '_', $TypeWorksheet);
        $TypeWorksheet = substr($TypeWorksheet, 0, 31);
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
        $export->setValue($export->getCell(0, $row), "Meldung der Schülerzahl gem. § 14 Abs. 2 SächsFrTrSchulG i.V.m. § 8 ZuschussVO");
        $export->setStyle($export->getCell(0, $row), $export->getCell(11, $row))->setFontSize(12)->mergeCells()->setFontBold()->setAlignmentCenter();
        $row++;
        $export->setValue($export->getCell(0, $row), "zum Antrag vom ………………….. auf Gewährung von Zuschüssen für Schulen in freier Trägerschaft");
        $export->setStyle($export->getCell(0, $row), $export->getCell(11, $row))->setFontSize(12)->mergeCells()->setFontBold()->setAlignmentCenter();
        $row++;
        $export->setValue($export->getCell(0, $row), "für berufsbildende Schulen und berufsbildende Förderschulen");
        $export->setStyle($export->getCell(0, $row), $export->getCell(11, $row))->setFontSize(12)->mergeCells()->setFontBold()->setAlignmentCenter();
        $row++;
        $export->setValue($export->getCell(0, $row), " - Abgabe beim Landesamt für Schule und Bildung spätestens:    24. Oktober - ");
        $export->setStyle($export->getCell(0, $row), $export->getCell(11, $row))->setRowHeight(23)->mergeCells()->setFontBold()->setAlignmentCenter()
            ->setAlignmentMiddle()->setFontColor('FF0000')->setFontItalic();
        $row += 2;
        $export->setValue($export->getCell(0, $row), "Schulträger:");
        $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row))->setFontItalic();
        $FirstBoxHeight = $row;
        $row++;
        $export->setValue($export->getCell(0, $row), $ResponsibilityString);
        $export->setStyle($export->getCell(0, $row), $export->getCell(3, ($row)))->mergeCells();
        $export->setValue($export->getCell(5, $row), "Stichtag, Datum: ...........");
        $export->setStyle($export->getCell(5, $row), $export->getCell(11, ($row)))->mergeCells()->setFontSize(12)->setFontBold()->setFontColor('FF0000');
        $row++;
        $export->setValue($export->getCell(0, $row), $ResponsibilityStringExtended);
        $export->setStyle($export->getCell(0, $row), $export->getCell(3, ($row)))->mergeCells();
        $export->setValue($export->getCell(5, $row), "(10. Oktober oder abweichender Stichtag  gem. § 8 Abs. 3 Satz 6 ZuschussVO:
Fällt ein Stichtag auf einen unterrichtsfreien Tag, gilt der letzte vorhergehende
Unterrichtstag als Stichtag. Dieser ist anzugeben.)");
        $export->setStyle($export->getCell(5, $row), $export->getCell(11, ($row + 2)))->mergeCells()->setFontSize(9.5)->setFontItalic()
            ->setFontColor('FF0000')->setWrapText();
        $row++;
        $export->setValue($export->getCell(0, $row), "Name u. Standort der Schule:");
        $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row))->setFontItalic();
        $row++;
        $export->setValue($export->getCell(0, $row), $SchoolString);
        $export->setStyle($export->getCell(0, $row), $export->getCell(3, ($row)))->mergeCells();
        $row++;
        $export->setValue($export->getCell(0, $row), $SchoolStringExtended);
        $export->setStyle($export->getCell(0, $row), $export->getCell(3, ($row)))->mergeCells();
        // Rahmen erste Box
        $export->setStyle($export->getCell(0, $FirstBoxHeight), $export->getCell(3, $row))->setBorderOutline(2);
        $row++;
        $export->setValue($export->getCell(5, $row), 'Hinweis für berufsbildende Förderschulen:');
        $export->setStyle($export->getCell(5, $row), $export->getCell(11, $row))->setRowHeight(14)->setFontSize(9.5)->setBackgroundColor('FFFF00')->setFontBold()
            ->setAlignmentBottom();
        $row++;
        $export->setValue($export->getCell(5, $row), 'bitte zusätzlich das Formular zur Ermittlung des Sachausgabenanteils beifügen!');
        $export->setStyle($export->getCell(5, $row), $export->getCell(11, $row))->setRowHeight(12)->setFontSize(9)->setBackgroundColor('FFFF00')->setFontBold();
        $row++;
        $export->setValue($export->getCell(0, $row), "Schulart:");
        $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row))->setFontBold()->setAlignmentRight()->setAlignmentMiddle()->setRowHeight(25);
        $export->setValue($export->getCell(1, $row), $Type);
        $export->setStyle($export->getCell(1, $row), $export->getCell(7, $row))->setAlignmentCenter()->mergeCells()->setBorderBottom();
        $row++;
        $export->setValue($export->getCell(0, $row), "Bildungsgang:");
        $export->setStyle($export->getCell(0, $row))->setFontBold()->setAlignmentRight()->setAlignmentMiddle()->setRowHeight(25);
        $export->setValue($export->getCell(1, $row), $SchoolCourse);
        $export->setStyle($export->getCell(1, $row), $export->getCell(7, $row))->setAlignmentCenter()->mergeCells()->setBorderBottom();
        $export->setValue($export->getCell(9, $row), '□  Vollzeit'); // ☐ ☑
        $export->setStyle($export->getCell(9, $row))->setAlignmentCenter()->setAlignmentMiddle()->setFontBold()->setFontSize(13);
        $export->setValue($export->getCell(10, $row), '□  Teilzeit'); // ☐ ☑
        $export->setStyle($export->getCell(10, $row))->setAlignmentCenter()->setAlignmentMiddle()->setFontBold()->setFontSize(13);
        $row++;
        $export->setValue($export->getCell(1, $row), "(Bezeichnung entsprechend der Anlage zu § 1 ZuschussVO)");
        $export->setStyle($export->getCell(1, $row), $export->getCell(7, $row))->setAlignmentCenter()->setFontItalic()->mergeCells()->setFontSize(9);
        $row += 2;
        // Block Schülerzählung Head
        // precast border all
        $lowLevel = $this->getLevel($LevelList);
        $heightLevel = $this->getLevel($LevelList, false);
        $export->setStyle($export->getCell(0, $row), $export->getCell(7, $row+ (5 +($heightLevel - $lowLevel))))->setBorderAll();
        $export->setValue($export->getCell(0, $row), "Beginn des 
Schulbetriebes:");
        $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row+1))->setBorderOutline(2);
        $export->setStyle($export->getCell(0, $row))->setRowHeight(40)->setBorderBottom(0);
        $export->setStyle($export->getCell(0, $row+1))->setBorderTop(0);
        $DateYear = $date->format('y');
        $DateMonth = $date->format('m');
        if($DateMonth < 8){
            $DateYear -= 1;
        }
        if(strlen($DateYear) == 1){
            $DateYear = '0'.$DateYear;
        }
        $export->setValue($export->getCell(1, $row), "Schülerzahl im Schuljahr 20".$DateYear."/20".((int)$DateYear+1));
        $export->setStyle($export->getCell(1, $row), $export->getCell(3, $row+1))->mergeCells()->setBorderOutline(2)->setAlignmentCenter();
        $export->setValue($export->getCell(4, $row), "davon: 
Kostenerstattung durch einen 
anderen öffentlichen Träger");
        $export->setStyle($export->getCell(4, $row), $export->getCell(7, $row+1))->mergeCells()->setBorderOutline(2);
        $export->setStyle($export->getCell(0, $row), $export->getCell(7, $row+1))->setFontBold()->setAlignmentMiddle()->setWrapText();

        $export->setValue($export->getCell(9, $row), 'Hinweis:
Es ist die Gesamtschülerzahl des jeweiligen Ausbildungsjahres anzugeben. Die Anzahl der Schüler, die über andere öffentliche Träger (z.B.
Arbeitsagentur,Rentenversicherungen, Berufsgenossenschaften usw.) gefördert werden, sind in dieser Erfassung in der Spalte "davon" gesondert zu berücksichtigen.');
        $export->setStyle($export->getCell(9, $row),$export->getCell(11, $row+7))->mergeCells()->setFontSize(9)->setAlignmentMiddle()->setWrapText();

        $row += 2;
        $export->setValue($export->getCell(0, $row), "Schüler im
Ausbildungsjahr");
        $export->setStyle($export->getCell(0, $row), $export->getCell(0, $row+1))->mergeCells()->setFontBold()->setBorderOutline(2)->setWrapText();
        $export->setValue($export->getCell(1, $row), "Schülerzahl");
        $export->setStyle($export->getCell(1, $row), $export->getCell(1, $row+1))->mergeCells()->setFontBold()->setBorderOutline(2);
        $export->setValue($export->getCell(2, $row), "Beginn");
        $export->setValue($export->getCell(3, $row), "Ende");
        $export->setValue($export->getCell(2, $row+1), "der Ausbildung");
        $export->setStyle($export->getCell(2, $row+1), $export->getCell(3, $row+1))->mergeCells()->setBorderBottom(2);
        $export->setValue($export->getCell(4, $row), "Schülerzahl");
        $export->setStyle($export->getCell(4, $row), $export->getCell(7, $row+1))->mergeCells()->setBorderLeft(2)->setBorderBottom(2)->setBorderRight(5);
        $export->setStyle($export->getCell(1, $row), $export->getCell(7, $row+1))->setFontBold()->setAlignmentCenter()->setAlignmentMiddle();
        $row += 2;
        // Block Schülerzählung Body
        // precast alignment center
        $export->setStyle($export->getCell(0, $row),$export->getCell(7, $row+4))->setAlignmentCenter();
        $Sum = 0;
        foreach ($LevelList as $Level => $StudentCount) {
            $export->setValue($export->getCell(1, $row + (($Level)-$lowLevel)), $StudentCount);
            if($StudentCount) {
                $Sum += $StudentCount;
            }
        }
        for($i = $lowLevel; $i < ($heightLevel+1); $i++){
            $export->setValue($export->getCell(0, $row), $i);
            $export->setStyle($export->getCell(0, $row))->setBorderLeft(2)->setBorderRight(2);
            $export->setStyle($export->getCell(1, $row))->setBorderRight(2);
            $export->setStyle($export->getCell(4, $row),$export->getCell(7, $row))->mergeCells()->setBorderLeft(2)->setBorderRight(2);
            $row++;
        }
        $export->setValue($export->getCell(0, $row), "Gesamtschülerzahl");
        $export->setStyle($export->getCell(0, $row))->setBorderOutline(2);
        $export->setValue($export->getCell(1, $row), $Sum);
        $export->setStyle($export->getCell(1, $row))->setBorderOutline(2);
        $export->setValue($export->getCell(4, $row), "0");
        $export->setStyle($export->getCell(4, $row),$export->getCell(7, $row))->mergeCells()->setBorderLeft(2)->setBorderRight(2);
        $export->setStyle($export->getCell(0, $row), $export->getCell(7, $row))->setAlignmentCenter()->setAlignmentMiddle()->setBorderOutline(2)
            ->setRowHeight(25);
        $row++;
        if(($usedLines = $heightLevel - $lowLevel) < 5){
            for($usedLines; $usedLines < 2; $usedLines++){
                $row++;
            }
        }
        $export->setValue($export->getCell(0, $row), "§ 14 Abs. 2 Nr. 1 SächsFrTrSchulG: Ein Schüler wird beschult, wenn er am maßgeblichen Stichtag aufgrund eines Vertragsverhältnisses am Unterricht teilnimmt
oder entschuldigt nicht teilnimmt. Ist das Vertragsverhältnis am Stichtag bereits gekündigt und hat der Schüler den Schulbesuch am Stichtag bereits
endgültig beendet oder abgebrochen, gilt er nicht als beschult.");
        $export->setStyle($export->getCell(0, $row), $export->getCell(11, $row))->setFontSize(10)->mergeCells()->setRowHeight(40)->setWrapText();
        $row += 3;
        $export->setValue($export->getCell(0, $row), (new DateTime())->format('d.m.Y'));
        $export->setStyle($export->getCell(0, $row))->setBorderBottom();
        $export->setStyle($export->getCell(7, $row), $export->getCell(11, $row))->setBorderBottom();
        $row++;
        $export->setValue($export->getCell(0, $row), 'Datum');
        $export->setValue($export->getCell(7, $row), 'Unterschrift');
        $row++;
        $export->setValue($export->getCell(7, $row), 'Vorsitzende(r) / Geschäftsführer(in) des Schulträgers');
        // Spaltenbreite Definieren
        $export->setStyle($export->getCell(0, 0))->setColumnWidth(20);
        $export->setStyle($export->getCell(1, 0))->setColumnWidth(15.2);
        $export->setStyle($export->getCell(2, 0))->setColumnWidth(15.2);
        $export->setStyle($export->getCell(3, 0))->setColumnWidth(15.2);
        $export->setStyle($export->getCell(4, 0))->setColumnWidth(9);
        $export->setStyle($export->getCell(5, 0))->setColumnWidth(2);
        $export->setStyle($export->getCell(6, 0))->setColumnWidth(7);
        $export->setStyle($export->getCell(7, 0))->setColumnWidth(12);
        $export->setStyle($export->getCell(8, 0))->setColumnWidth(3);
        $export->setStyle($export->getCell(9, 0))->setColumnWidth(12);
        $export->setStyle($export->getCell(10, 0))->setColumnWidth(12);
        $export->setStyle($export->getCell(11, 0))->setColumnWidth(12);

        // Excel wählt den zuletzt bearbeiten Bereich aus -> Bildungsgang
        $export->setStyle($export->getCell(1, 13))->setFontBold(false);
    }

    /**
     * @param $LevelList
     *
     * @return int|string
     */
    private function getLevel($LevelList, $isLow = true)
    {

        $level = false;
        foreach($LevelList as $LevelTemp => $value){
            if($isLow && (!$level || $level > $LevelTemp)){
                $level = $LevelTemp;
            }
            if(!$isLow && (!$level || $level < $LevelTemp)){
                $level = $LevelTemp;
            }
        }
        return $level;
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     * @param array               $DataContent
     *
     * @return array
     */
    private function fillDataContent(TblStudentEducation $tblStudentEducation, array $DataContent)
    {
        $level = $tblStudentEducation->getLevel();
        $tblCategoryTechnical = Type::useService()->getCategoryByIdentifier(TblCategory::TECHNICAL);
        $excludeTypeList = Type::useService()->getTypeAllByCategory($tblCategoryTechnical);
        // StudentEducation muss eine aktive Klasse/Stammgruppe haben
        if ($level && ($tblStudentEducation->getTblDivision() || $tblStudentEducation->getTblCoreGroup())) {
            if (($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())) {
                $ignoreType = false;
                foreach($excludeTypeList as $excludeType){
                    if($excludeType->getId() == $tblSchoolType->getId()){
                        $ignoreType = true;
                    }
                }
                if(!$ignoreType){
                    $Type = $tblSchoolType->getName();
                    if (isset($DataContent[$Type][$level])) {
                        $DataContent[$Type][$level] += 1;
                    } else {
                        $DataContent[$Type][$level] = 1;
                    }
                }
            }
        }
        return $DataContent;
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     * @param array               $DataContent
     *
     * @return array
     */
    private function fillDataTechnicalContent(TblStudentEducation $tblStudentEducation, array $DataContent)
    {
        $level = $tblStudentEducation->getLevel();
        $tblCategoryTechnical = Type::useService()->getCategoryByIdentifier(TblCategory::TECHNICAL);
        $useTypeList = Type::useService()->getTypeAllByCategory($tblCategoryTechnical);
        // StudentEducation muss eine aktive Klasse/Stammgruppe haben
        if ($level && ($tblStudentEducation->getTblDivision() || $tblStudentEducation->getTblCoreGroup())) {
            if (($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())) {
                $useType = false;
                foreach($useTypeList as $excludeType){
                    if($excludeType->getId() == $tblSchoolType->getId()){
                        $useType = true;
                    }
                }
                if($useType){
                    $TechnicalCourse = false;
                    if(($tblPerson = $tblStudentEducation->getServiceTblPerson())
                    && ($tblStudent = $tblPerson->getStudent())
                    && ($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool()))
                    {
                        if(($tblTechnicalSchool = $tblStudentTechnicalSchool->getServiceTblTechnicalCourse())){
                            $TechnicalCourse = $tblTechnicalSchool->getName();
                        }
                    }
                    $Type = $tblSchoolType->getName();
                    if($TechnicalCourse){
                        if (isset($DataContent[$Type][$TechnicalCourse][$level])) {
                            $DataContent[$Type][$TechnicalCourse][$level] += 1;
                        } else {
                            $DataContent[$Type][$TechnicalCourse][$level] = 1;
                        }
                    } else {
                        if (isset($DataContent[$Type][$TechnicalCourse][$level])) {
                            $DataContent[$Type][0][$level] += 1;
                        } else {
                            $DataContent[$Type][0][$level] = 1;
                        }

                    }
                }
            }
        }
        return $DataContent;
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     * @param DateTime            $date
     * @param array               $DataFocus
     *
     * @return array
     */
    private function fillDataFocus(TblStudentEducation $tblStudentEducation, DateTime $date, array $DataFocus)
    {

        $tblPerson = $tblStudentEducation->getServiceTblPerson();
        $level = $tblStudentEducation->getLevel();
        $Type = '';
        if(($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())){
            $Type = $tblSchoolType->getName();
        }
        if($tblPerson
            // StudentEducation muss eine aktive Klasse/Stammgruppe haben
            && ($tblStudentEducation->getTblDivision() || $tblStudentEducation->getTblCoreGroup())
            && ($tblSupport = Student::useService()->getSupportForReportingByPerson($tblPerson, $date))
            && ($tblSupportFocus = Student::useService()->getSupportPrimaryFocusBySupport($tblSupport))
            && ($tblSupportFocusType = $tblSupportFocus->getTblSupportFocusType())
        ) {
            $focusName = $tblSupportFocusType->getName();
            // füllen der Förderschwerpunkte
                if(isset($DataFocus[$Type][$level]['Count'])) {
                    $DataFocus[$Type][$level]['Count'] += 1;
                } else {
                    $DataFocus[$Type][$level]['Count'] = 1;
                }
                $DataFocus[$Type][$level][$focusName][] = $tblSupport->getId();
        }
        return $DataFocus;
    }

    private function setFocus(PhpExcel $export, $Focus, $FocusUsage, $DataFocus, $Type, &$row)
    {

        $export->setValue($export->getCell(0, $row), $Focus);
        // Autismus Insert
        $SumAutismus = 0;
        if(isset($DataFocus[$Type]) && !empty($DataFocus[$Type])) {
            foreach ($DataFocus[$Type] as $Level => $StudentTypeList) {
                $StudentCount = '';
                if(isset($StudentTypeList[$FocusUsage])){
                    $StudentCount = count($StudentTypeList[$FocusUsage]);
                }
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
}