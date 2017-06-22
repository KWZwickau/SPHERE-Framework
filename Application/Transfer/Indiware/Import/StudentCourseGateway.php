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

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;
use SPHERE\Application\Transfer\Gateway\Converter\FieldSanitizer;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Warning;

/**
 * Class StudentCourseGateway
 * @package SPHERE\Application\Transfer\Indiware\Import
 */
class StudentCourseGateway extends AbstractConverter
{

    private $ResultList = array();
    private $ImportList = array();
    private $Year = false;
    private $Level = false;
    private $StudentCourseList = array();

    /**
     * LectureshipGateway constructor.
     *
     * @param string  $File SpUnterricht.csv
     * @param TblYear $tblYear
     * @param null    $Level
     * @param null    $Period
     */
    public function __construct($File, TblYear $tblYear, $Level = null, $Period = null)
    {
        $this->loadFile($File);
        $this->Year = $tblYear;
        $this->Level = $Level;

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        // Klasse 11 1. Halbjahr (Default)
        $SubjectList = array(
            1  => 'AJ',
            2  => 'AK',
            3  => 'AL',
            4  => 'AM',
            5  => 'AN',
            6  => 'AO',
            7  => 'AP',
            8  => 'AQ',
            9  => 'AR',
            10 => 'AS',
            11 => 'AT',
            12 => 'AU',
            13 => 'AV',
            14 => 'AW',
            15 => 'AX',
            16 => 'AY',
            17 => 'AZ'
        );

        // Klasse 11 2. Halbjahr
        if ($Level == 11 && $Period == 2) {
            $SubjectList = array(
                1  => 'BA',
                2  => 'BB',
                3  => 'BC',
                4  => 'BD',
                5  => 'BE',
                6  => 'BF',
                7  => 'BG',
                8  => 'BH',
                9  => 'BI',
                10 => 'BJ',
                11 => 'BK',
                12 => 'BL',
                13 => 'BM',
                14 => 'BN',
                15 => 'BO',
                16 => 'BP',
                17 => 'BQ'
            );
        } elseif ($Level == 12 && $Period == 1 || $Level == 11 && $Period == 3) {   // Klasse 12 1. Halbjahr
            $SubjectList = array(
                1  => 'BR',
                2  => 'BS',
                3  => 'BT',
                4  => 'BU',
                5  => 'BV',
                6  => 'BW',
                7  => 'BX',
                8  => 'BY',
                9  => 'BZ',
                10 => 'CA',
                11 => 'CB',
                12 => 'CC',
                13 => 'CD',
                14 => 'CE',
                15 => 'CF',
                16 => 'CG',
                17 => 'CH'
            );
        } elseif ($Level == 12 && $Period == 2) {    // Klasse 12 2. Halbjahr
            $SubjectList = array(
                1  => 'CI',
                2  => 'CJ',
                3  => 'CK',
                4  => 'CL',
                5  => 'CM',
                6  => 'CN',
                7  => 'CO',
                8  => 'CP',
                9  => 'CQ',
                10 => 'CR',
                11 => 'CS',
                12 => 'CT',
                13 => 'CU',
                14 => 'CV',
                15 => 'CW',
                16 => 'CX',
                17 => 'CY'
            );
        }
        foreach ($SubjectList as $Key => $FieldPosition) {
            $this->setPointer(new FieldPointer($FieldPosition, 'FileSubject'.$Key));
            $this->setPointer(new FieldPointer($FieldPosition, 'AppSubject'.$Key));
            $this->setPointer(new FieldPointer($FieldPosition, 'SubjectId'.$Key));
            $this->setPointer(new FieldPointer($FieldPosition, 'FileSubjectGroup'.$Key));
            $this->setPointer(new FieldPointer($FieldPosition, 'AppSubjectGroup'.$Key));
            $this->setPointer(new FieldPointer($FieldPosition, 'IsIntensiveCourse'.$Key));
            $this->setSanitizer(new FieldSanitizer($FieldPosition, 'AppSubject'.$Key, array($this, 'sanitizeSubject')));
            $this->setSanitizer(new FieldSanitizer($FieldPosition, 'SubjectId'.$Key, array($this, 'fetchSubject')));
            $this->setSanitizer(new FieldSanitizer($FieldPosition, 'IsIntensiveCourse'.$Key,
                array($this, 'fetchSubjectGroupIntensiveCourse')));
        }

        $this->setPointer(new FieldPointer('D', 'FirstName'));
        $this->setPointer(new FieldPointer('C', 'LastName'));
        $this->setPointer(new FieldPointer('E', 'Birthday'));

        $this->scanFile(1);
    }

    /**
     * @return array
     */
    public function getResultList()
    {
        return $this->ResultList;
    }

    /**
     * @return array
     */
    public function getImportList()
    {
        return $this->ImportList;
    }

    /**
     * @return array
     */
    public function getLectureship()
    {
        return $this->StudentCourseList;
    }

    /**
     * @param array $Row
     *
     * @return void
     */
    public function runConvert($Row)
    {
        $Result = array();
        foreach ($Row as $Part) {
            $Result = array_merge($Result, $Part);
        }

//        if (!$this->IsError) {
        $tblPerson = false;
        if (isset($Result['FirstName']) && isset($Result['LastName']) && isset($Result['Birthday'])) {
            $tblPerson = Person::useService()->getPersonByNameAndBirthday($Result['FirstName'], $Result['LastName'],
                $Result['Birthday']);
        }
        if (isset($tblPerson) && !$tblPerson) {
            $Result['AppPerson'] = new Warning(new WarningIcon().' Person nicht gefunden');
        } elseif ($tblPerson) {
            $Result['AppPerson'] = $tblPerson->getFullName();
        }
        // search Division
        $tblDivision = false;
        if ($tblPerson) {
            $tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPerson, $this->Year);
        }

        for ($i = 1; $i <= 17; $i++) {
            $Result['tblSubject'.$i] = (isset($Result['SubjectId'.$i]) && $Result['SubjectId'.$i] !== null ? Subject::useService()
                ->getSubjectById($Result['SubjectId'.$i]) : null);
        }

        // Importe nur mit gültigen Personen
        if ($tblPerson) {
            $ImportRow = array(
                'tblPerson'    => $tblPerson,
                'tblDivision'  => $tblDivision,
                'FileSubject1' => $Result['FileSubject1'],
                'FileSubject2' => $Result['FileSubject2'],
                'FileSubject3' => $Result['FileSubject3'],
                'FileSubject4' => $Result['FileSubject4'],
                'FileSubject5' => $Result['FileSubject5'],
                'FileSubject6' => $Result['FileSubject6'],
                'FileSubject7' => $Result['FileSubject7'],
                'FileSubject8' => $Result['FileSubject8'],
                'FileSubject9'        => $Result['FileSubject9'],
                'FileSubject10'       => $Result['FileSubject10'],
                'FileSubject11'       => $Result['FileSubject11'],
                'FileSubject12'       => $Result['FileSubject12'],
                'FileSubject13'       => $Result['FileSubject13'],
                'FileSubject14'       => $Result['FileSubject14'],
                'FileSubject15'       => $Result['FileSubject15'],
                'FileSubject16'       => $Result['FileSubject16'],
                'FileSubject17'       => $Result['FileSubject17'],
                'AppSubjectGroup1'    => $Result['AppSubjectGroup1'],
                'AppSubjectGroup2'    => $Result['AppSubjectGroup2'],
                'AppSubjectGroup3'    => $Result['AppSubjectGroup3'],
                'AppSubjectGroup4'    => $Result['AppSubjectGroup4'],
                'AppSubjectGroup5'    => $Result['AppSubjectGroup5'],
                'AppSubjectGroup6'    => $Result['AppSubjectGroup6'],
                'AppSubjectGroup7'    => $Result['AppSubjectGroup7'],
                'AppSubjectGroup8'    => $Result['AppSubjectGroup8'],
                'AppSubjectGroup9'    => $Result['AppSubjectGroup9'],
                'AppSubjectGroup10'   => $Result['AppSubjectGroup10'],
                'AppSubjectGroup11'   => $Result['AppSubjectGroup11'],
                'AppSubjectGroup12'   => $Result['AppSubjectGroup12'],
                'AppSubjectGroup13'   => $Result['AppSubjectGroup13'],
                'AppSubjectGroup14'   => $Result['AppSubjectGroup14'],
                'AppSubjectGroup15'   => $Result['AppSubjectGroup15'],
                'AppSubjectGroup16'   => $Result['AppSubjectGroup16'],
                'AppSubjectGroup17'   => $Result['AppSubjectGroup17'],
                'IsIntensiveCourse1'  => $Result['IsIntensiveCourse1'],
                'IsIntensiveCourse2'  => $Result['IsIntensiveCourse2'],
                'IsIntensiveCourse3'  => $Result['IsIntensiveCourse3'],
                'IsIntensiveCourse4'  => $Result['IsIntensiveCourse4'],
                'IsIntensiveCourse5'  => $Result['IsIntensiveCourse5'],
                'IsIntensiveCourse6'  => $Result['IsIntensiveCourse6'],
                'IsIntensiveCourse7'  => $Result['IsIntensiveCourse7'],
                'IsIntensiveCourse8'  => $Result['IsIntensiveCourse8'],
                'IsIntensiveCourse9'  => $Result['IsIntensiveCourse9'],
                'IsIntensiveCourse10' => $Result['IsIntensiveCourse10'],
                'IsIntensiveCourse11' => $Result['IsIntensiveCourse11'],
                'IsIntensiveCourse12' => $Result['IsIntensiveCourse12'],
                'IsIntensiveCourse13' => $Result['IsIntensiveCourse13'],
                'IsIntensiveCourse14' => $Result['IsIntensiveCourse14'],
                'IsIntensiveCourse15' => $Result['IsIntensiveCourse15'],
                'IsIntensiveCourse16' => $Result['IsIntensiveCourse16'],
                'IsIntensiveCourse17' => $Result['IsIntensiveCourse17'],
                'tblSubject1'         => $Result['tblSubject1'],
                'tblSubject2'         => $Result['tblSubject2'],
                'tblSubject3'         => $Result['tblSubject3'],
                'tblSubject4'         => $Result['tblSubject4'],
                'tblSubject5'         => $Result['tblSubject5'],
                'tblSubject6'         => $Result['tblSubject6'],
                'tblSubject7'         => $Result['tblSubject7'],
                'tblSubject8'         => $Result['tblSubject8'],
                'tblSubject9'         => $Result['tblSubject9'],
                'tblSubject10'        => $Result['tblSubject10'],
                'tblSubject11'        => $Result['tblSubject11'],
                'tblSubject12'        => $Result['tblSubject12'],
                'tblSubject13'        => $Result['tblSubject13'],
                'tblSubject14'        => $Result['tblSubject14'],
                'tblSubject15'        => $Result['tblSubject15'],
                'tblSubject16'        => $Result['tblSubject16'],
                'tblSubject17'        => $Result['tblSubject17'],
            );
            $this->ImportList[] = $ImportRow;
        }
//        } else {
//            $this->IsError = false;
//        }

//        Debugger::screenDump($Result);
        $this->ResultList[] = $Result;
//        exit;
    }

    /**
     * @param $Value
     *
     * @return Warning|string
     */
    protected function sanitizeSubjectGroup($Value)
    {
        if (preg_match('!^(.+?)$!is', $Value, $Match)) {
            return $Match[1];
        }
        return '';
    }

    /**
     * @param $Value
     *
     * @return string
     */
    protected function sanitizeSubject($Value)
    {
        if (empty($Value)) {
            return new Muted(new WarningIcon());
        }
        $Value = substr($Value, 0, -1);

        if (!($tblSubject = Subject::useService()->getSubjectByAcronym($Value))) {
            return new Warning(new WarningIcon().' Das Fach '.$Value.' ist in der Schulsoftware nicht vorhanden');
        } else {
            return $tblSubject->getAcronym().' - '.$tblSubject->getName();
        }
    }

    /**
     * @param $Value
     *
     * @return bool
     */
    protected function fetchSubjectGroupIntensiveCourse($Value)
    {
        if (empty($Value)) {
            return false;
        }
        $Value = substr($Value, 0, -1);
        if (ctype_upper($Value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $Value
     *
     * @return bool|TblSubject
     */
    protected function fetchSubject($Value)
    {

        $tblSubject = false;
        if (!empty($Value)) {
            $Value = substr($Value, 0, -1);
            $tblSubject = Subject::useService()->getSubjectByAcronym($Value);
//            if ($tblSubject) {
//                $this->Subject = $tblSubject->getId();
//            }
        }
        return ($tblSubject ? $tblSubject->getId() : null);
    }
}