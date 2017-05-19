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

namespace SPHERE\Application\Transfer\Indiware\Import;

use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;

//use SPHERE\Application\Transfer\Gateway\Converter\FieldSanitizer;

/**
 * Class LectureshipGateway
 * @package SPHERE\Application\Transfer\Indiware\Import
 */
class LectureshipControl extends AbstractConverter
{

    private $Compare = false;
    private $TableHead = array(
        'A'  => 'Nummers',
        'B'  => 'Id',
        'C'  => 'Stunden',
        'D'  => 'Fach',
        'E'  => 'Lehrer',
        'F'  => 'Lehrer2',
        'G'  => 'Lehrer3',
        'H'  => 'AbwStunden',
        'I'  => 'LStunden1',
        'J'  => 'LStunden2',
        'K'  => 'LStunden3',
        'L'  => 'Klasse1',
        'M'  => 'Klasse2',
        'N'  => 'Klasse3',
        'O'  => 'Klasse4',
        'P'  => 'Klasse5',
        'Q'  => 'Klasse6',
        'R'  => 'Klasse7',
        'S'  => 'Klasse8',
        'T'  => 'Klasse9',
        'U'  => 'Klasse10',
        'V'  => 'Klasse11',
        'W'  => 'Klasse12',
        'X'  => 'Klasse13',
        'Y'  => 'Klasse14',
        'Z'  => 'Klasse15',
        'AA' => 'Klasse16',
        'AB' => 'Klasse17',
        'AC' => 'Klasse18',
        'AD' => 'Klasse19',
        'AE' => 'Klasse20',
        'AF' => 'Gruppe',
        'AG' => 'Kopplung',
        'AH' => 'Schueler',
        'AI' => 'Wunschraum',
        'AJ' => 'WR_fest',
        'AK' => 'Wunschraum2',
        'AL' => 'Ausweichraum',
        'AM' => 'PlanenManuell',
        'AN' => 'Randstunde',
        'AO' => 'Blockstunde',
        'AP' => 'Doppelstunden',
        'AQ' => 'Aufteilung',
        'AR' => 'Zeitraster1',
        'AS' => 'Zeitraster2',
        'AT' => 'Zeitraster3',
        'AU' => 'Zeitraster4',
        'AV' => 'Zeitraster5',
        'AW' => 'Zeitraster6',
        'AX' => 'Zeitraster7',
        'AY' => 'Zeitraster8',
        'AZ' => 'Zeitraster9',
        'BA' => 'Zeitraster10',
        'BB' => 'Zeitraster11',
        'BC' => 'Zeitraster12',
        'BD' => 'Zeitraster13',
        'BE' => 'Zeitraster14',
        'BF' => 'StatistikArt',
        'BG' => 'StatistikBilingual',
        'BH' => 'Fachfolge',
        'BI' => 'StundenProTag',
        'BJ' => 'Blockpartner',
        'BK' => 'ParallelKennzeichen',
        'BL' => 'TauschKennzeichen',
        'BM' => 'Inaktiv',
        'BN' => 'InaktivVp',
        'BO' => 'FaktorKlasse',
        'BP' => 'StundeMittelMin',
        'BQ' => 'StundeMittelMax',
        'BR' => 'UaId',
        'BS' => 'Kopplungsindex',
        'BT' => 'Automatikindex',
        'BU' => 'Wochenunterricht',
        'BV' => 'Version2'
    );

    private $ColumnList = array();

    /**
     * LectureshipGateway constructor.
     *
     * @param string $File SpUnterricht.csv
     */
    public function __construct($File)
    {
        $this->loadFile($File);

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        foreach ($this->TableHead as $Column => $Value) {
            $this->setPointer(new FieldPointer($Column, 'Field'));
//            $this->setSanitizer(new FieldSanitizer($Column, 'Field', array($this, 'sanitizeField')));
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
    public function getColumnList()
    {
        return $this->ColumnList;
    }

    /**
     * @param array $Row
     *
     * @return void
     */
    public function runConvert($Row)
    {


        $Result = array();
        foreach ($Row as $Column => $Part) {
            if (isset($Part['Field'])) {
                $Result = array_merge($Result, array($Column => $Part['Field']));
            }
        }

        $this->ColumnList = array_diff_assoc($Result, $this->TableHead);
        if (empty($this->ColumnList)) {
            $this->Compare = true;
        }
    }

//    /**
//     * @param $Value
//     *
//     * @return void
//     */
//    protected function sanitizeField($Value)
//    {
//
//        foreach($this->TableHead as $Key => $ColumnCompare){
//            if($ColumnCompare == $Value){
//                unset($this->TableHead[$Key]);
//            }
//        }
//
//        if(empty($this->TableHead)){
//            $this->Compare = true;
//        }
//    }
}