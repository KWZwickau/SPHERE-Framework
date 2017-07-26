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
    private $TableHead = array(
        'A'  => 'Id',
        'B'  => 'IdStat',
        'C'  => 'Name',
        'D'  => 'Vorname',
        'E'  => 'Geburtsdatum',
        'F'  => 'Geschlecht',
        'G'  => 'Geburtsort',
        'H'  => 'Geburtskreis',
        'I'  => 'Wohnort',
        'J'  => 'PLZ',
        'K'  => 'Strasse',
        'L'  => 'VornameZeugnis',
        'M'  => 'Stammkurs',
        'N'  => 'Tutor',
        'O'  => 'Klasse',
        'P'  => 'Fach1',
        'Q'  => 'Fach2',
        'R'  => 'Fach3',
        'S'  => 'Fach4',
        'T'  => 'Fach5',
        'U'  => 'Fach6',
        'V'  => 'Fach7',
        'W'  => 'Fach8',
        'X'  => 'Fach9',
        'Y'  => 'Fach10',
        'Z'  => 'Fach11',
        'AA' => 'Fach12',
        'AB' => 'Fach13',
        'AC' => 'Fach14',
        'AD' => 'Fach15',
        'AE' => 'Fach16',
        'AF' => 'Fach17',
        'AG' => 'Schwerpunkt',
        'AH' => 'ErsatzReligion',
        'AI' => 'ErsatzSport',
        'AJ' => 'Kurs11',
        'AK' => 'Kurs12',
        'AL' => 'Kurs13',
        'AM' => 'Kurs14',
        'AN' => 'Kurs15',
        'AO' => 'Kurs16',
        'AP' => 'Kurs17',
        'AQ' => 'Kurs18',
        'AR' => 'Kurs19',
        'AS' => 'Kurs110',
        'AT' => 'Kurs111',
        'AU' => 'Kurs112',
        'AV' => 'Kurs113',
        'AW' => 'Kurs114',
        'AX' => 'Kurs115',
        'AY' => 'Kurs116',
        'AZ' => 'Kurs117',
        'BA' => 'Kurs21',
        'BB' => 'Kurs22',
        'BC' => 'Kurs23',
        'BD' => 'Kurs24',
        'BE' => 'Kurs25',
        'BF' => 'Kurs26',
        'BG' => 'Kurs27',
        'BH' => 'Kurs28',
        'BI' => 'Kurs29',
        'BJ' => 'Kurs210',
        'BK' => 'Kurs211',
        'BL' => 'Kurs212',
        'BM' => 'Kurs213',
        'BN' => 'Kurs214',
        'BO' => 'Kurs215',
        'BP' => 'Kurs216',
        'BQ' => 'Kurs217',
        'BR' => 'Kurs31',
        'BS' => 'Kurs32',
        'BT' => 'Kurs33',
        'BU' => 'Kurs34',
        'BV' => 'Kurs35',
        'BW' => 'Kurs36',
        'BX' => 'Kurs37',
        'BY' => 'Kurs38',
        'BZ' => 'Kurs39',
        'CA' => 'Kurs310',
        'CB' => 'Kurs311',
        'CC' => 'Kurs312',
        'CD' => 'Kurs313',
        'CE' => 'Kurs314',
        'CF' => 'Kurs315',
        'CG' => 'Kurs316',
        'CH' => 'Kurs317',
        'CI' => 'Kurs41',
        'CJ' => 'Kurs42',
        'CK' => 'Kurs43',
        'CL' => 'Kurs44',
        'CM' => 'Kurs45',
        'CN' => 'Kurs46',
        'CO' => 'Kurs47',
        'CP' => 'Kurs48',
        'CQ' => 'Kurs49',
        'CR' => 'Kurs410',
        'CS' => 'Kurs411',
        'CT' => 'Kurs412',
        'CU' => 'Kurs413',
        'CV' => 'Kurs414',
        'CW' => 'Kurs415',
        'CX' => 'Kurs416',
        'CY' => 'Kurs417'
    );

    private $ColumnList = array();

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

//        foreach ($this->TableHead as $Column => $Value) {
//            $this->setPointer(new FieldPointer($Column, 'Field'));
////            $this->setSanitizer(new FieldSanitizer($Column, 'Field', array($this, 'sanitizeField')));
//        }

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
    public function getColumnList()
    {
        return $this->ColumnList;
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

        // TODO: Du weißt schon, anpassen aber ich verrate nich was ;)
        $ColumnNeeded = array(
            // Wird absolut benötigt für den Service
            'Kurs11',
            'Kurs12',
            'Kurs21',
            'Kurs22',
            'Kurs31',
            'Kurs32',
            'Kurs41',
            'Kurs42',
        );
        $ColumnScan = array(
            // Suchen nach
            'Kurs[1-4][0-9]+',
            'Ersatz(Religion|Sport)[1-4]?'
        );


        $ColumnMatch = array(
            // Muster in Spalte ?? gefunden
            // Match (Spaltenname) => Index (Spalte)
        );

        foreach ($Row as $Column => $Part) {
            foreach ($ColumnScan as $Pattern) {
                if (preg_match('!^(' . $Pattern . ')$!is', $Part['Field'], $Match)) {
                    $ColumnMatch[$Match[0]] = $Column;
                }
            }
        }

        $Preset = array_values( $ColumnNeeded );
        $Analysis = array_keys( $ColumnMatch );
        $Difference = array_diff( $Preset, $Analysis );

        if( empty( $Difference ) ) {
            // Alle notwendigen Spalten gefunden
            $this->Compare = true;
            $this->ScanResult = $ColumnMatch;
        } else {
            // Datei enthält nicht alle notwendigen Spalten
            $this->Compare = false;
            $this->ScanResult = array();
        }

//        $Result = array();
//        foreach ($Row as $Column => $Part) {
//            if (isset($Part['Field'])) {
//                $Result = array_merge($Result, array($Column => $Part['Field']));
//            }
//        }
//
//        $this->ColumnList = array_diff_assoc($Result, $this->TableHead);
//        if (empty($this->ColumnList)) {
//            $this->Compare = true;
//        }
    }
}