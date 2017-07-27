<?php
/**
 * Export Unterricht (Schüler.csv) Gekürzt Reihenfolge der Felder in der CSV-Datei Schüler.csv
 * Spalte   Feld
 * 'A' => 'Id'
 * 'B' => 'IdStat'
 * 'C' => 'Name'
 * 'D' => 'Vorname'
 * 'E' => 'Geburtsdatum'
 * 'F' => 'Geschlecht'
 * 'G' => 'Geburtsort'
 * 'H' => 'Geburtskreis'
 * 'I' => 'Wohnort'
 * 'J' => 'PLZ'
 * 'K' => 'Strasse'
 * 'L' => 'VornameZeugnis'
 * 'M' => 'Stammkurs'
 * 'N' => 'Tutor'
 * 'O' => 'Klasse'
 * 'P' => 'Fach1'
 * 'Q' => 'Fach2'
 * 'R' => 'Fach3'
 * 'S' => 'Fach4'
 * 'T' => 'Fach5'
 * 'U' => 'Fach6'
 * 'V' => 'Fach7'
 * 'W' => 'Fach8'
 * 'X' => 'Fach9'
 * 'Y' => 'Fach10'
 * 'Z' => 'Fach11'
 * 'AA' => 'Fach12'
 * 'AB' => 'Fach13'
 * 'AC' => 'Fach14'
 * 'AD' => 'Fach15'
 * 'AE' => 'Fach16'
 * 'AF' => 'Fach17'
 * 'AG' => 'Schwerpunkt'
 * 'AH' => 'ErsatzReligion'
 * 'AI' => 'Ersatzsport'
 * 'AJ' => 'Kurs11'
 * 'AK' => 'Kurs12'
 * 'AL' => 'Kurs13'
 * 'AM' => 'Kurs14'
 * 'AN' => 'Kurs15'
 * 'AO' => 'Kurs16'
 * 'AP' => 'Kurs17'
 * 'AQ' => 'Kurs18'
 * 'AR' => 'Kurs19'
 * 'AS' => 'Kurs110'
 * 'AT' => 'Kurs111'
 * 'AU' => 'Kurs112'
 * 'AV' => 'Kurs113'
 * 'AW' => 'Kurs114'
 * 'AX' => 'Kurs115'
 * 'AY' => 'Kurs116'
 * 'AZ' => 'Kurs117'
 * 'BA' => 'Kurs21'
 * 'BB' => 'Kurs22'
 * 'BC' => 'Kurs23'
 * 'BD' => 'Kurs24'
 * 'BE' => 'Kurs25'
 * 'BF' => 'Kurs26'
 * 'BG' => 'Kurs27'
 * 'BH' => 'Kurs28'
 * 'BI' => 'Kurs29'
 * 'BJ' => 'Kurs210'
 * 'BK' => 'Kurs211'
 * 'BL' => 'Kurs212'
 * 'BM' => 'Kurs213'
 * 'BN' => 'Kurs214'
 * 'BO' => 'Kurs215'
 * 'BP' => 'Kurs216'
 * 'BQ' => 'Kurs217'
 * 'BR' => 'Kurs31'
 * 'BS' => 'Kurs32'
 * 'BT' => 'Kurs33'
 * 'BU' => 'Kurs34'
 * 'BV' => 'Kurs35'
 * 'BW' => 'Kurs36'
 * 'BX' => 'Kurs37'
 * 'BY' => 'Kurs38'
 * 'BZ' => 'Kurs39'
 * 'CA' => 'Kurs310'
 * 'CB' => 'Kurs311'
 * 'CC' => 'Kurs312'
 * 'CD' => 'Kurs313'
 * 'CE' => 'Kurs314'
 * 'CF' => 'Kurs315'
 * 'CG' => 'Kurs316'
 * 'CH' => 'Kurs317'
 * 'CI' => 'Kurs41'
 * 'CJ' => 'Kurs42'
 * 'CK' => 'Kurs43'
 * 'CL' => 'Kurs44'
 * 'CM' => 'Kurs45'
 * 'CN' => 'Kurs46'
 * 'CO' => 'Kurs47'
 * 'CP' => 'Kurs48'
 * 'CQ' => 'Kurs49'
 * 'CR' => 'Kurs410'
 * 'CS' => 'Kurs411'
 * 'CT' => 'Kurs412'
 * 'CU' => 'Kurs413'
 * 'CV' => 'Kurs414'
 * 'CW' => 'Kurs415'
 * 'CX' => 'Kurs416'
 * 'CY' => 'Kurs417'
 */

namespace SPHERE\Application\Transfer\Indiware\Import;

use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;

//use SPHERE\Application\Transfer\Gateway\Converter\FieldSanitizer;

/**
 * Class StudentCourseControl
 * @package SPHERE\Application\Transfer\Indiware\Import
 */
class StudentCourseControl extends AbstractConverter
{

    private $Compare = false;
    private $DifferenceList = array();
    private $ColumnNeeded = array(
        'Name',
        'Vorname',
        'Geburtsdatum',
        'Fach1',
        'Fach2',
        'Fach3',
        'Fach4',
        'Fach5',
        'Fach6',
        'Fach7',
        'Fach8',
        'Fach9',
        'Fach10',
        'Fach11',
        'Fach12',
        'Fach13',
        'Fach14',
        'Fach15',
        'Fach16',
        'Fach17',
        'Kurs11',
        'Kurs12',
        'Kurs13',
        'Kurs14',
        'Kurs15',
        'Kurs16',
        'Kurs17',
        'Kurs18',
        'Kurs19',
        'Kurs110',
        'Kurs111',
        'Kurs112',
        'Kurs113',
        'Kurs114',
        'Kurs115',
        'Kurs116',
        'Kurs117',
        'Kurs21',
        'Kurs22',
        'Kurs23',
        'Kurs24',
        'Kurs25',
        'Kurs26',
        'Kurs27',
        'Kurs28',
        'Kurs29',
        'Kurs210',
        'Kurs211',
        'Kurs212',
        'Kurs213',
        'Kurs214',
        'Kurs215',
        'Kurs216',
        'Kurs217',
        'Kurs31',
        'Kurs32',
        'Kurs33',
        'Kurs34',
        'Kurs35',
        'Kurs36',
        'Kurs37',
        'Kurs38',
        'Kurs39',
        'Kurs310',
        'Kurs311',
        'Kurs312',
        'Kurs313',
        'Kurs314',
        'Kurs315',
        'Kurs316',
        'Kurs317',
        'Kurs41',
        'Kurs42',
        'Kurs43',
        'Kurs44',
        'Kurs45',
        'Kurs46',
        'Kurs47',
        'Kurs48',
        'Kurs49',
        'Kurs410',
        'Kurs411',
        'Kurs412',
        'Kurs413',
        'Kurs414',
        'Kurs415',
        'Kurs416',
        'Kurs417'
    );

    // Suchen nach
    //        'Ersatz(Religion|Sport)[1-4]?'
    private $ColumnScan = array(
        'Kurs[1-4][0-9]+',
        'fach[0-9]+',
        'Name',
        'Vorname',
        'Geburtsdatum',
    );

    /**
     * @param string $LowerBound
     * @param string $UpperBound
     * @return \Generator
     */
    private function excelColumnRangeGenerator($LowerBound, $UpperBound) {
        ++$UpperBound;
        for ($Run = $LowerBound; $Run !== $UpperBound; ++$Run) {
            yield $Run;
        }
    }

    /**
     * LectureshipGateway constructor.
     *
     * @param string $File SpUnterricht.csv
     */
    public function __construct($File)
    {
        $this->loadFile($File);

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        foreach( $this->excelColumnRangeGenerator( 'A', 'ZZ' ) as $Column ) {
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

    private $ScanResult = array();
    /**
     *
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
                if (preg_match('!^(' . $Pattern . ')$!is', $Part['Field'], $Match)) {
                    $ColumnMatch[$Match[0]] = $Column;
                }
            }
        }

        $Preset = array_values($this->ColumnNeeded);
        $Analysis = array_keys( $ColumnMatch );
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