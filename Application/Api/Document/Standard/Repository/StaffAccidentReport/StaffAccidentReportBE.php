<?php
namespace SPHERE\Application\Api\Document\Standard\Repository\StaffAccidentReport;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class StaffAccidentReportBE
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
class StaffAccidentReportBE extends AbstractDocument
{

    /**
     * @param array $Data
     */
    function __construct($Data)
    {

        $this->Style = new Style($Data);
    }

    /**
     * @var Style
     */
    private Style $Style;


    /**
     * @return string
     */
    public function getName()
    {

        return 'Unfallbericht';
    }

    /**
     *
     * @param array $pageList
     * @param string $part
     *
     * @return Frame
     */
    public function buildDocument(array $pageList = array(), string $part = '0'): Frame
    {
        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->styleBorderAll()
                    ->addSection($this->Style->getHeaderSection())
                    ->addSection($this->Style->getBorderBottomSection())
                    /////// Name Geburtstag
                    ->addSection($this->Style->getNameAndBirthdaySection())
                    ->addSection($this->Style->getNameAndBirthdayDataSection())
                    ///////// Adresse
                    ->addSection($this->Style->getAddressSection())
                    ->addSection($this->Style->getAddressDataSection())
                    /////// Meta
                    ->addSection($this->Style->getGenderSection())
                    ->addSection($this->Style->getGenderDataSection())
                    // Neue Reihe
                    ->addSection($this->Style->getEducationDataSectionBE())
                    ->addSection($this->Style->getInsuranceSection())
                    ->addSection($this->Style->getInsuranceDataSection())
                    /////// Unfall Infos
                    ->addSection($this->Style->getDeadlyAccidentSection())
                    ->addSection($this->Style->getDeadlyAccidentDataSection())
                    ->addSection($this->Style->getAccidentLocationSection())
                    ->addSection($this->Style->getAccidentLocationDataSection())
                    ////// Schilderung des Unfallhergangs
                    ->addSection($this->Style->getDescriptionSection())
                    ->addSection($this->Style->getDescriptionDataSection())
                    ->addSection($this->Style->getDescriptionInfoDataSection())
                    ->addSection($this->Style->getBorderBottomSection())
                    /////// Verletzungen
                    ->addSection($this->Style->getHurtSection())
                    ->addSection($this->Style->getHurtDataSection())
                    ->addSection($this->Style->getNoticSection())
                    ->addSection($this->Style->getNoticDataSection())
                    ->addSectionList($this->Style->getInitialTreatmentSectionList())
                    ->addSection($this->Style->getInitialTreatmentDataSection())
                    ->addSection($this->Style->getAccidentJobSection())
                    ->addSection($this->Style->getAccidentJobDataSection())
                    ->addSection($this->Style->getCompanyPartSection())
                    ->addSection($this->Style->getCompanyPartDataSection())
                    /////// Unterbrechung
                    ->addSection($this->Style->getBreakDataSection())
                    /////// Wiederaufnahme
                    ->addSection($this->Style->getRevisitDataSection())
                    /////// Kenntnis
                    ->addSection($this->Style->getDateDataSection())
                    ->addSection($this->Style->getDateSection())
                )
            )
        );
    }

}