<?php
/**
 * Export Unterricht (SpUnterricht.csv) Ungekürzt Reihenfolge der Felder in der CSV-Datei SpUnterricht.csv
 * SpUnterricht.csv CSV-Datei Unterricht:
 * Spalte   Feld            Art
 * 'A' => 'Nummer'
 * 'B' => 'Id'
 * 'C' => 'Stunden'
 * 'D' => 'Fach'
 * 'E' => 'Lehrer'
 * 'F' => 'Lehrer2'
 * 'G' => 'Lehrer3'
 * 'H' => 'AbwStunden'
 * 'I' => 'LStunden1'
 * 'J' => 'LStunden2'
 * 'K' => 'LStunden3'
 * 'L' => 'Klasse1'
 * 'M' => 'Klasse2'
 * 'N' => 'Klasse3'
 * 'O' => 'Klasse4'
 * 'P' => 'Klasse5'
 * 'Q' => 'Klasse6'
 * 'R' => 'Klasse7'
 * 'S' => 'Klasse8'
 * 'T' => 'Klasse9'
 * 'U' => 'Klasse10'
 * 'V' => 'Klasse11'
 * 'W' => 'Klasse12'
 * 'X' => 'Klasse13'
 * 'Y' => 'Klasse14'
 * 'Z' => 'Klasse15'
 * 'AA' => 'Klasse16'
 * 'AB' => 'Klasse17'
 * 'AC' => 'Klasse18'
 * 'AD' => 'Klasse19'
 * 'AE' => 'Klasse20'
 * 'AF' => 'Gruppe'
 * 'AG' => 'Kopplung'
 * 'AH' => 'Schueler'
 * 'AI' => 'Wunschraum'
 * 'AJ' => 'WR_fest'
 * 'AK' => 'Wunschraum2'
 * 'AL' => 'Ausweichraum'
 * 'AM' => 'PlanenManuell'
 * 'AN' => 'Randstunde'
 * 'AO' => 'Blockstunde'
 * 'AP' => 'Doppelstunden'
 * 'AQ' => 'Aufteilung'
 * 'AR' => 'Zeitraster1'
 * 'AS' => 'Zeitraster2'
 * 'AT' => 'Zeitraster3'
 * 'AU' => 'Zeitraster4'
 * 'AV' => 'Zeitraster5'
 * 'AW' => 'Zeitraster6'
 * 'AX' => 'Zeitraster7'
 * 'AY' => 'Zeitraster8'
 * 'AZ' => 'Zeitraster9'
 * 'BA' => 'Zeitraster10'
 * 'BB' => 'Zeitraster11'
 * 'BC' => 'Zeitraster12'
 * 'BD' => 'Zeitraster13'
 * 'BE' => 'Zeitraster14'
 * 'BF' => 'StatistikArt'
 * 'BG' => 'StatistikBilingual'
 * 'BH' => 'Fachfolge'
 * 'BI' => 'StundenProTag'
 * 'BJ' => 'Blockpartner'
 * 'BK' => 'ParallelKennzeichen'
 * 'BL' => 'TauschKennzeichen'
 * 'BM' => 'Inaktiv'
 * 'BN' => 'InaktivVp'
 * 'BO' => 'FaktorKlasse'
 * 'BP' => 'StundeMittelMin'
 * 'BQ' => 'StundeMittelMax'
 * 'BR' => 'UaId'
 * 'BS' => 'Kopplungsindex'
 * 'BT' => 'Automatikindex'
 * 'BU' => 'Wochenunterricht'
 * 'BV' => 'Version2'
 */

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
//        'Geburtstag',
        'Beitrag',
        'Preis-Variante',
        'Beitragsart',
        'Mandatsreferenznummer',
        'Mandatsreferenznummer Gültig ab',
        'Datum beitragspflichtig von',
//        'Datum beitragspflichtig bis',
        'Zahler Vorname',
        'Zahler Nachname',
        'Debitorennummer',
        'IBAN',
//        'BIC',
//        'Bank',
    );

    // Suchen nach
    //        'Ersatz(Religion|Sport)[1-4]?'
    private $ColumnScan = array(
        'Zählung',
        'Verursacher Vorname',
        'Verursacher Nachname',
        'Geburtstag',
        'Beitrag',
        'Preis-Variante',
        'Beitragsart',
        'Mandatsreferenznummer',
        'Mandatsreferenznummer Gültig ab',
        'Datum beitragspflichtig von',
        'Datum beitragspflichtig bis',
        'Zahler Vorname',
        'Zahler Nachname',
        'Debitorennummer',
        'IBAN',
        'BIC',
        'Bank',
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

        $this->scanFile(0, 1);
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