<?php
/**
 * Import Schüler-Kurse. Reihenfolge der Felder aus der CSV-Datei Schüler.csv
 * wird Dynamisch ausgelesen (Erfolgt in Control)
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
use SPHERE\Common\Frontend\Text\Repository\Danger;
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
     * @param string $File SpUnterricht.csv
     * @param TblYear $tblYear
     * @param null $Level
     * @param StudentCourseControl $Control
     */
    public function __construct($File, TblYear $tblYear, $Level = null, StudentCourseControl $Control)
    {
        $this->loadFile($File);
        $this->Year = $tblYear;
        $this->Level = $Level;

        $ColumnList = $Control->getScanResult();

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        $SubjectGroupList = array();

        // Klasse 11 1. Halbjahr (Default)
        $SubjectGroupList[1] = $ColumnList['Kurs11'];
        $SubjectGroupList[2] = $ColumnList['Kurs12'];
        $SubjectGroupList[3] = $ColumnList['Kurs13'];
        $SubjectGroupList[4] = $ColumnList['Kurs14'];
        $SubjectGroupList[5] = $ColumnList['Kurs15'];
        $SubjectGroupList[6] = $ColumnList['Kurs16'];
        $SubjectGroupList[7] = $ColumnList['Kurs17'];
        $SubjectGroupList[8] = $ColumnList['Kurs18'];
        $SubjectGroupList[9] = $ColumnList['Kurs19'];
        $SubjectGroupList[10] = $ColumnList['Kurs110'];
        $SubjectGroupList[11] = $ColumnList['Kurs111'];
        $SubjectGroupList[12] = $ColumnList['Kurs112'];
        $SubjectGroupList[13] = $ColumnList['Kurs113'];
        $SubjectGroupList[14] = $ColumnList['Kurs114'];
        $SubjectGroupList[15] = $ColumnList['Kurs115'];
        $SubjectGroupList[16] = $ColumnList['Kurs116'];
        $SubjectGroupList[17] = $ColumnList['Kurs117'];

        // Klasse 11 2. Halbjahr
        if ($Level == 2) {
            $SubjectGroupList[1] = $ColumnList['Kurs21'];
            $SubjectGroupList[2] = $ColumnList['Kurs22'];
            $SubjectGroupList[3] = $ColumnList['Kurs23'];
            $SubjectGroupList[4] = $ColumnList['Kurs24'];
            $SubjectGroupList[5] = $ColumnList['Kurs25'];
            $SubjectGroupList[6] = $ColumnList['Kurs26'];
            $SubjectGroupList[7] = $ColumnList['Kurs27'];
            $SubjectGroupList[8] = $ColumnList['Kurs28'];
            $SubjectGroupList[9] = $ColumnList['Kurs29'];
            $SubjectGroupList[10] = $ColumnList['Kurs210'];
            $SubjectGroupList[11] = $ColumnList['Kurs211'];
            $SubjectGroupList[12] = $ColumnList['Kurs212'];
            $SubjectGroupList[13] = $ColumnList['Kurs213'];
            $SubjectGroupList[14] = $ColumnList['Kurs214'];
            $SubjectGroupList[15] = $ColumnList['Kurs215'];
            $SubjectGroupList[16] = $ColumnList['Kurs216'];
            $SubjectGroupList[17] = $ColumnList['Kurs217'];
        } elseif ($Level == 3) {   // Klasse 12 1. Halbjahr
            $SubjectGroupList[1] = $ColumnList['Kurs31'];
            $SubjectGroupList[2] = $ColumnList['Kurs32'];
            $SubjectGroupList[3] = $ColumnList['Kurs33'];
            $SubjectGroupList[4] = $ColumnList['Kurs34'];
            $SubjectGroupList[5] = $ColumnList['Kurs35'];
            $SubjectGroupList[6] = $ColumnList['Kurs36'];
            $SubjectGroupList[7] = $ColumnList['Kurs37'];
            $SubjectGroupList[8] = $ColumnList['Kurs38'];
            $SubjectGroupList[9] = $ColumnList['Kurs39'];
            $SubjectGroupList[10] = $ColumnList['Kurs310'];
            $SubjectGroupList[11] = $ColumnList['Kurs311'];
            $SubjectGroupList[12] = $ColumnList['Kurs312'];
            $SubjectGroupList[13] = $ColumnList['Kurs313'];
            $SubjectGroupList[14] = $ColumnList['Kurs314'];
            $SubjectGroupList[15] = $ColumnList['Kurs315'];
            $SubjectGroupList[16] = $ColumnList['Kurs316'];
            $SubjectGroupList[17] = $ColumnList['Kurs317'];
        } elseif ($Level == 4) {    // Klasse 12 2. Halbjahr
            $SubjectGroupList[1] = $ColumnList['Kurs41'];
            $SubjectGroupList[2] = $ColumnList['Kurs42'];
            $SubjectGroupList[3] = $ColumnList['Kurs43'];
            $SubjectGroupList[4] = $ColumnList['Kurs44'];
            $SubjectGroupList[5] = $ColumnList['Kurs45'];
            $SubjectGroupList[6] = $ColumnList['Kurs46'];
            $SubjectGroupList[7] = $ColumnList['Kurs47'];
            $SubjectGroupList[8] = $ColumnList['Kurs48'];
            $SubjectGroupList[9] = $ColumnList['Kurs49'];
            $SubjectGroupList[10] = $ColumnList['Kurs410'];
            $SubjectGroupList[11] = $ColumnList['Kurs411'];
            $SubjectGroupList[12] = $ColumnList['Kurs412'];
            $SubjectGroupList[13] = $ColumnList['Kurs413'];
            $SubjectGroupList[14] = $ColumnList['Kurs414'];
            $SubjectGroupList[15] = $ColumnList['Kurs415'];
            $SubjectGroupList[16] = $ColumnList['Kurs416'];
            $SubjectGroupList[17] = $ColumnList['Kurs417'];
        }
        foreach ($SubjectGroupList as $Key => $FieldPosition) {
            $this->setPointer(new FieldPointer($FieldPosition, 'FileSubjectGroup'.$Key));
            $this->setPointer(new FieldPointer($FieldPosition, 'AppSubjectGroup'.$Key));
            $this->setPointer(new FieldPointer($FieldPosition, 'IsIntensiveCourse'.$Key));
            $this->setSanitizer(new FieldSanitizer($FieldPosition, 'IsIntensiveCourse'.$Key,
                array($this, 'fetchSubjectGroupIntensiveCourse')));
        }
        // Fächerangabe
        $SubjectList = array();
        $SubjectList[1] = $ColumnList['Fach1'];
        $SubjectList[2] = $ColumnList['Fach2'];
        $SubjectList[3] = $ColumnList['Fach3'];
        $SubjectList[4] = $ColumnList['Fach4'];
        $SubjectList[5] = $ColumnList['Fach5'];
        $SubjectList[6] = $ColumnList['Fach6'];
        $SubjectList[7] = $ColumnList['Fach7'];
        $SubjectList[8] = $ColumnList['Fach8'];
        $SubjectList[9] = $ColumnList['Fach9'];
        $SubjectList[10] = $ColumnList['Fach10'];
        $SubjectList[11] = $ColumnList['Fach11'];
        $SubjectList[12] = $ColumnList['Fach12'];
        $SubjectList[13] = $ColumnList['Fach13'];
        $SubjectList[14] = $ColumnList['Fach14'];
        $SubjectList[15] = $ColumnList['Fach15'];
        $SubjectList[16] = $ColumnList['Fach16'];
        $SubjectList[17] = $ColumnList['Fach17'];
        foreach ($SubjectList as $Key => $FieldPosition) {
            $this->setPointer(new FieldPointer($FieldPosition, 'FileSubject'.$Key));
            $this->setPointer(new FieldPointer($FieldPosition, 'AppSubject'.$Key));
            $this->setPointer(new FieldPointer($FieldPosition, 'SubjectId'.$Key));
            $this->setPointer(new FieldPointer($FieldPosition, 'IsIntensiveCourse'.$Key));
            $this->setSanitizer(new FieldSanitizer($FieldPosition, 'AppSubject'.$Key, array($this, 'sanitizeSubject')));
            $this->setSanitizer(new FieldSanitizer($FieldPosition, 'SubjectId'.$Key, array($this, 'fetchSubject')));
        }


        $this->setPointer(new FieldPointer($ColumnList['Vorname'], 'FirstName'));
        $this->setPointer(new FieldPointer($ColumnList['Name'], 'LastName'));
        $this->setPointer(new FieldPointer($ColumnList['Geburtsdatum'], 'Birthday'));

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
            $Result['AppPerson'] = new Danger(new WarningIcon().' Person nicht gefunden');
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
            return '';
        }
//        $Value = substr($Value, 0, -1);

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
//            $Value = substr($Value, 0, -1);
            $tblSubject = Subject::useService()->getSubjectByAcronym($Value);
//            if ($tblSubject) {
//                $this->Subject = $tblSubject->getId();
//            }
        }
        return ($tblSubject ? $tblSubject->getId() : null);
    }
}