<?php
namespace SPHERE\Application\Billing\Inventory\Import;

use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;

/**
 * Class ImportControl
 * @package SPHERE\Application\Billing\Inventory\Import
 */
class ImportControl extends AbstractConverter
{

    private $Compare = false;
    private $DifferenceList = array();
    private $ScanResult = array();
    private $ColumnNeeded = array(
        'Zählung',
        'Verursacher Vorname',
        'Verursacher Nachname',
//        'Verursacher Geburtstag',
        'Individueller Preis',
        'Preis-Variante',
        'Beitragsart',
        'Mandatsreferenznummer',
        'Mandatsreferenznummer gültig ab',
        'Datum beitragspflichtig von',
//        'Datum beitragspflichtig bis',
        'Zahler Vorname',
        'Zahler Nachname',
        'Debitorennummer',
        'IBAN',
//        'BIC',
//        'Bank Name',
    );

    // Suchen nach
    //        'Ersatz(Religion|Sport)[1-4]?'
    private $ColumnScan = array(
        'Zählung',
        'Verursacher Vorname',
        'Verursacher Nachname',
        'Verursacher Geburtstag',
        'Individueller Preis',
        'Preis-Variante',
        'Beitragsart',
        'Mandatsreferenznummer',
        'Mandatsref Beschreibung',
        'Mandatsreferenznummer gültig ab',
        'Datum beitragspflichtig von',
        'Datum beitragspflichtig bis',
        'Zahler Vorname',
        'Zahler Nachname',
        'Debitorennummer',
        'IBAN',
        'BIC',
        'Bank Name',
    );

    /**
     * @param string $LowerBound
     * @param string $UpperBound
     *
     * @return \Generator
     */
    private function excelColumnRangeGenerator($LowerBound, $UpperBound)
    {
        ++$UpperBound;
        for ($Run = $LowerBound; $Run !== $UpperBound; ++$Run) {
            yield $Run;
        }
    }

    /**
     * LectureshipGateway constructor.
     *
     * @param string $File '.xlsx
     */
    public function __construct($File)
    {
        $this->loadFile($File);

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        foreach ($this->excelColumnRangeGenerator('A', 'ZZ') as $Column ) {
            $this->setPointer(new FieldPointer($Column, 'Field'));
        }

        $this->scanFile(2, 1);
    }

    /**
     * @return bool
     */
    public function getCompare()
    {
        return $this->Compare;
    }

    /**
     * @return array
     */
    public function getDifferenceList()
    {
        return $this->DifferenceList;
    }

    /**
     * @return array
     */
    public function getScanResult()
    {
        return $this->ScanResult;
    }

    /**
     * @param array $Row
     *
     * @return void
     */
    public function runConvert($Row)
    {

        $ColumnMatch = array(
            // Muster in Spalte ?? gefunden
            // Match (Spaltenname) => Index (Spalte)
        );

        $this->ColumnScan;
        foreach ($Row as $Column => $Part) {
            foreach ($this->ColumnScan as $Pattern) {
                if (preg_match('!^('.$Pattern.')$!is', $Part['Field'], $Match)) {
                    $ColumnMatch[$Match[0]] = $Column;
                }
            }
        }

        $Preset = array_values($this->ColumnNeeded);
        $Analysis = array_keys($ColumnMatch);
        $this->DifferenceList = array_diff($Preset, $Analysis);

        if (empty($this->DifferenceList)) {
            // Alle notwendigen Spalten gefunden
            $this->Compare = true;
            $this->ScanResult = $ColumnMatch;
        } else {
            // Datei enthält nicht alle notwendigen Spalten
            $this->Compare = false;
            $this->ScanResult = array();
        }
    }
}