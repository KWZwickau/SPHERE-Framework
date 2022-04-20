<?php
namespace SPHERE\Application\Transfer\Untis\Import;
/**
 * GPU014.TXT
 * https://platform.untis.at/HTML/WebHelp/de/untis/hid_export.htm
 * Pos Feld
 * A Vertretungsnummer
 * B Datum
 * C Stunde
 * D Absenznummer
 * E Unterrichtsnummer
 * F Absenter Lehrer
 * G Vertretender Lehrer
 * H Fach
 * I Statistikkennzeichen des Fachs
 * J Vertretungsfach
 * K Statistikkennzeichen des Vertretungsfachs
 * L Raum
 * M Vertretungsraum
 * N Statistik-Kennzeichen
 * O Klasse(n) mit ~ getrennt
 * P Absenzgrund
 * Q Text zur Vertretung
 * R Art (Bitfeld)
 *    Bit 0 Entfall
 *    Bit 1 Betreuung
 *    Bit 2 Sondereinsatz
 *    Bit 3 Wegverlegung
 *    Bit 4 Freisetzung
 *    Bit 5 Plus als Vertreter
 *    Bit 6 Teilvertretung
 *    Bit 7 Hinverlegung
 *    Bit 16 Raumvertretung
 *    Bit 17 Pausenaufsichtsvertretung
 *    Bit 18 Stunde ist unterrichtsfrei
 *    Bit 20 Kennzeichen nicht drucken
 *    Bit 21 Kennzeichen neu
 * S Vertretungsklasse(n) mit ~ getrennt
 * T Vertretungsart
 *    "T" verlegt
 *    "F" verlegt von
 *    "W" Tausch
 *    "S" Betreuung
 *    "A" Sondereinsatz
 *    "C" Entfall
 *    "L" Freisetzung
 *    "P" Teil-Vertretung
 *    "R" Raumvertretung
 *    "B" Pausenaufsichtsvertretung
 *    "~" Lehrertausch
 *    "E" Klausur
 * U Zeit der letzten Änderung (JJJJMMTTHHMM)
 */

use DateTime;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;
use SPHERE\Application\Transfer\Gateway\Converter\FieldSanitizer;
use SPHERE\Common\Frontend\Text\Repository\Danger;

/**
 * Class ReplacementGPU014
 * @package SPHERE\Application\Transfer\Untis\Import
 */
class ReplacementGPU014 extends AbstractConverter
{

    private $WarningList = array();
    private $ImportList = array();
    private $CountImport = array();
    private $tblYearList;
    private $DateList = array();
    private $CourseList = array();

    /**
     * GPU014 constructor.
     *
     * @param string  $File GPU014.txt
     */
    public function __construct($File, $Data)
    {

        $Date = new \DateTime();
        $DayCount = $Date->format('N');
        for ($i = 1; $i <= 5; $i++){
            $DateTemp = clone($Date);
            if($i < $DayCount){
                $Diff = $DayCount - $i;
                $DateTemp->sub(new \DateInterval('P'.$Diff.'D'));
                $this->DateList[$i] = $DateTemp->format('Y-m-d');
            } elseif($i > $DayCount) {
                $Diff = $i - $DayCount;
                $DateTemp->add(new \DateInterval('P'.$Diff.'D'));
                $this->DateList[$i] = $DateTemp->format('Y-m-d');
            } else {
                $this->DateList[$i] = $DateTemp->format('Y-m-d');
            }
        }
        $this->tblYearList = Term::useService()->getYearAllByDate(new DateTime());

        $this->loadFile($File);

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        $this->setPointer(new FieldPointer('B', 'Date'));
        $this->setSanitizer(new FieldSanitizer('B', 'Date', array($this, 'sanitizeDate')));

        $this->setPointer(new FieldPointer('C', 'Hour'));
        $this->setPointer(new FieldPointer('F', 'Person'));
        $this->setPointer(new FieldPointer('F', 'tblPerson'));
        $this->setSanitizer(new FieldSanitizer('F', 'tblPerson', array($this, 'sanitizePerson')));
        $this->setPointer(new FieldPointer('G', 'PersonTo'));
        $this->setPointer(new FieldPointer('G', 'tblPersonTo'));
        $this->setSanitizer(new FieldSanitizer('G', 'tblPersonTo', array($this, 'sanitizePerson')));
        $this->setPointer(new FieldPointer('H', 'Subject'));
        $this->setPointer(new FieldPointer('H', 'tblSubject'));
        $this->setSanitizer(new FieldSanitizer('H', 'tblSubject', array($this, 'sanitizeSubject')));
        $this->setPointer(new FieldPointer('H', 'SubjectGroup'));
        $this->setSanitizer(new FieldSanitizer('H', 'SubjectGroup', array($this, 'sanitizeSubjectGroup')));
        $this->setPointer(new FieldPointer('I', 'SubjectTo'));
        $this->setPointer(new FieldPointer('I', 'tblSubjectTo'));
        $this->setSanitizer(new FieldSanitizer('I', 'tblSubjectTo', array($this, 'sanitizeSubject')));
        $this->setPointer(new FieldPointer('I', 'SubjectGroupTo'));
        $this->setSanitizer(new FieldSanitizer('I', 'SubjectGroupTo', array($this, 'sanitizeSubjectGroup')));
        $this->setPointer(new FieldPointer('L', 'Room'));
        $this->setPointer(new FieldPointer('M', 'RoomTo'));
        $this->setPointer(new FieldPointer('O', 'Division'));
        // ToDO Liste aus Klassen im String getrent durch "~"
        $this->setPointer(new FieldPointer('O', 'tblCourseString'));
        $this->setPointer(new FieldPointer('O', 'tblCourseList'));
        $this->setSanitizer(new FieldSanitizer('O', 'tblCourseList', array($this, 'sanitizeCourse')));
        $this->setPointer(new FieldPointer('R', 'IsCanceled'));

        $this->scanFile(0);
    }

    /**
     * @return array
     */
    public function getWarningList()
    {
        return $this->WarningList;
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
    public function getCountImport()
    {
        return $this->CountImport;
    }

    /**
     * @return array
     */
    public function getDateList()
    {
        return $this->DateList;
    }

    /**
     * @return array
     */
    public function getCourseList()
    {
        return $this->CourseList;
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

        //kurze schreibweise von ($Result['tblCourse'] ? $Result['tblCourse'] : null);
        $tblCourseList = ($Result['tblCourseList'] ? : null);
        $tblPerson = ($Result['tblPerson'] ? : null);
        $tblSubject = ($Result['tblSubject'] ? : null);

        if($Result['Date'] && $tblCourseList){
            foreach($tblCourseList as $tblCourse){
                if($Result['tblCourseString'] === '' || $Result['tblPerson'] === false || $Result['tblSubject'] === false){
                    // ignore Row complete
                } elseif($tblCourse && $tblSubject && $tblPerson){ // && $Result['Room'] != ''
                    $ImportRow = array(
                        'Date'         => $Result['Date'],
                        'Hour'         => $Result['Hour'],
                        'Room'         => $Result['Room'],
                        'IsCanceled'   => ($Result['IsCanceled'] == '0' ? 1 : 0),
                        'SubjectGroup' => $Result['SubjectGroup'],
                        'tblCourse'    => $tblCourse,
                        'tblSubject'   => $tblSubject,
                        'tblPerson'    => $tblPerson,
                    );
                    $this->ImportList[] = $ImportRow;
                } else {
                    $this->WarningList[] = $Result;
                }
            }
        }

    }

    /**
     * @param $Value
     *
     * @return array|false
     */
    protected function sanitizeCourse($Value)
    {
        $result = array();
        if($Value == ''){
//            $this->CountImport['Course']['Keine Klasse'][] = 'Klasse nicht gefunden';
            $result = false;
            return $result;
        }

        //ToDO Course
        $CourseList = explode('~', $Value);
        foreach($CourseList as $Course){
            $LevelName = null;
            $DivisionName = null;
            Division::useService()->matchDivision($Course, $LevelName, $DivisionName);
            $tblLevel = null;

            $tblDivisionList = array();
            // search with Level
            foreach($this->tblYearList as $tblYear){
                if (( $tblLevelList = Division::useService()->getLevelAllByName($LevelName) ) && $tblYear) {
                    foreach ($tblLevelList as $tblLevel) {
                        if (( $tblDivisionArray = Division::useService()->getDivisionByDivisionNameAndLevelAndYear($DivisionName, $tblLevel, $tblYear) )) {
                            foreach ($tblDivisionArray as $tblDivision) {
                                $tblDivisionList[] = $tblDivision;
                            }
                        }
                    }
                }
            }
            // search without Level with empty DivisionList
            if (empty($tblDivisionList)) {
                foreach($this->tblYearList as $tblYear){
                    if ($tblLevel === null && $tblYear && $LevelName == '') {
                        if (( $tblDivisionArray = Division::useService()->getDivisionByDivisionNameAndLevelAndYear($DivisionName, $tblLevel, $tblYear) )) {
                            foreach ($tblDivisionArray as $tblDivision) {
                                $tblDivisionList[] = $tblDivision;
                            }
                        }
                    }
                }
            }
            if(!empty($tblDivisionList) && count($tblDivisionList) == 1){
                $tblDivision = current($tblDivisionList);
                $result[] = $tblDivision;
                $this->CourseList[$tblDivision->getId()] = $tblDivision;
            } else {
                $this->CountImport['Course'][$Course][] = 'Klasse nicht gefunden';
            }
        }

        return $result;
    }

    /**
     * @param $Value
     * @return string
     */
    protected function sanitizeDate($Value)
    {

        if(strlen($Value) == 8){
            $Value = substr($Value, 0, 4).'-'.substr($Value, 4, 2).'-'.substr($Value, 6, 2);
            if(in_array($Value, $this->DateList)){
                return new DateTime($Value);
            }
        }
        return false;
    }

    /**
     * @param $Value
     * @return bool|TblPerson|string
     */
    protected function sanitizePerson($Value)
    {

        if($Value == ''){
//            $this->CountImport['Person']['Kein Lehrerkürzel'][] = 'Person nicht gefunden';
            return false;
        }
        if(($tblTeacher = Teacher::useService()->getTeacherByAcronym($Value))){
            if(($tblPerson = $tblTeacher->getServiceTblPerson())){
                return $tblPerson;
            }
        }

        $this->CountImport['Person'][$Value][] = 'Person nicht gefunden';
        return '';
    }

    /**
     * @param $Value
     *
     * @return null|Danger|int
     */
    protected function sanitizeSubject($Value)
    {

        if($Value == ''){
//            $this->CountImport['Subject']['Kein Fachkürzel'][] = 'Fach nicht gefunden';
            return false;
        }
        if(preg_match('!^([\w\/+]*)-([GL])-(\d)!is', $Value, $Match)){
            $Value = $Match[1];
        }

        if(($tblSubject = Subject::useService()->getSubjectByAcronym($Value))){
            return $tblSubject;
        }

        $this->CountImport['Subject'][$Value][] = 'Fach nicht gefunden';
        return '';
    }

    /**
     * @param $Value
     *
     * @return null|Danger|int
     */
    protected function sanitizeSubjectGroup($Value)
    {

        if(preg_match('!^([\w\/]*)-([GL])-(\d)!is', $Value, $Match)){
            return $Value;
        }
        return '';
    }
}