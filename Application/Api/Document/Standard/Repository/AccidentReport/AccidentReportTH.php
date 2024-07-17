<?php
namespace SPHERE\Application\Api\Document\Standard\Repository\AccidentReport;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class AccidentReportTH
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
class AccidentReportTH extends AbstractDocument
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
                    /////// gender & nationality
                    ->addSection($this->Style->getGenderSection())
                    ->addSection($this->Style->getGenderDataSection())
                    /////// name Custody
                    ->addSection($this->Style->getCustodySection())
                    ->addSection($this->Style->getCustodyDataSection())
                    /////// health insurance
                    ->addSection($this->Style->getInsuranceSection())
                    ->addSection($this->Style->getInsuranceDataSection())
                    /////// Unfall Infos
                    ->addSection($this->Style->getDeadlyAccidentSectionTH())
                    ->addSection($this->Style->getDeadlyAccidentDataSectionTH())
                    /////// AccidentLocation
                    ->addSection($this->Style->getAccidentLocationSection())
                    ->addSection($this->Style->getAccidentLocationDataSection())
                    ////// Schilderung des Unfallhergangs
                    ->addSection($this->Style->getDescriptionSection())
                    ->addSection($this->Style->getDescriptionDataSection('152px'))
                    ->addSection($this->Style->getDescriptionInfoDataSectionTH())
                    ->addSection($this->Style->getDescriptionViolenceDataSectionTH())
                    /////// Verletzungen
                    ->addSection($this->Style->getHurtSection())
                    ->addSection($this->Style->getHurtDataSection())
                    /////// Unterbrechung
                    ->addSection($this->Style->getBreakSection())
                    /////// Vortsetzung
                    ->addSection($this->Style->getRevisitSection())
                    /////// Kenntnis
                    ->addSection($this->Style->getNoticSection())
                    ->addSection($this->Style->getNoticDataSection())
                    /////// Kenntnis
                    ->addSection($this->Style->getInitialTreatmentSection())
                    ->addSection($this->Style->getInitialTreatmentDataSection())
                    /////// Date
                    ->addSection($this->Style->getDateDataSection())
                    ->addSection($this->Style->getDateSection())
                )
            )
        );
    }

}