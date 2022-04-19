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
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
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

    /**
     * GPU014 constructor.
     *
     * @param string  $File GPU014.txt
     */
    public function __construct($File, $Data)
    {
        $this->loadFile($File);
        $DateFrom = new DateTime($Data['DateFrom']);
        $this->tblYearList = Term::useService()->getYearAllByDate($DateFrom);

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        $this->setPointer(new FieldPointer('B', 'Date'));
        $this->setSanitizer(new FieldSanitizer('B', 'Date', array($this, 'sanitizeDate')));
        $this->setPointer(new FieldPointer('C', 'Hour'));
        $this->setPointer(new FieldPointer('F', 'Person'));
        $this->setPointer(new FieldPointer('F', 'TblPerson'));
        $this->setSanitizer(new FieldSanitizer('F', 'TblPerson', array($this, 'sanitizePerson')));
        $this->setPointer(new FieldPointer('G', 'PersonTo'));
        $this->setPointer(new FieldPointer('G', 'TblPersonTo'));
        $this->setSanitizer(new FieldSanitizer('G', 'TblPersonTo', array($this, 'sanitizePerson')));
        $this->setPointer(new FieldPointer('H', 'Subject'));
        $this->setPointer(new FieldPointer('H', 'tblSubject'));
        $this->setPointer(new FieldPointer('H', 'SubjectGroup'));
        $this->setSanitizer(new FieldSanitizer('H', 'tblSubject', array($this, 'sanitizeSubject')));
        $this->setSanitizer(new FieldSanitizer('H', 'SubjectGroup', array($this, 'sanitizeSubjectGroup')));
        $this->setPointer(new FieldPointer('I', 'SubjectTo'));
        $this->setPointer(new FieldPointer('I', 'tblSubjectTo'));
        $this->setPointer(new FieldPointer('I', 'SubjectGroupTo'));
        $this->setSanitizer(new FieldSanitizer('I', 'tblSubjectTo', array($this, 'sanitizeSubject')));
        $this->setSanitizer(new FieldSanitizer('I', 'SubjectGroupTo', array($this, 'sanitizeSubjectGroup')));
        $this->setPointer(new FieldPointer('L', 'Room'));
        $this->setPointer(new FieldPointer('M', 'RoomTo'));
        $this->setPointer(new FieldPointer('O', 'Division'));
        // ToDO Liste aus Klassen im String getrent durch "~"
        $this->setPointer(new FieldPointer('O', 'tblCourseList'));
        $this->setSanitizer(new FieldSanitizer('O', 'tblCourse', array($this, 'sanitizeCourse')));

        $this->scanFile(0);
    }

    /**
     * @return int
     */
    public function getWarningCount()
    {
        return count($this->WarningList);
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
        $tblCourse = ($Result['tblCourse'] ? : null);
        $tblPerson = ($Result['tblPerson'] ? : null);
        $tblSubject = ($Result['tblSubject'] ? : null);

        /** @var TblDivision $tblCourse */
        $Level = '';
        if($tblCourse){
            if($tblCourse->getTblLevel()) {
                $Level = $tblCourse->getTblLevel()->getName();
            }
        }

        if($Result['tblCourse'] === false || $Result['tblPerson'] === false || $Result['tblSubject'] === false){
            // ignore Row complete
        } elseif($tblCourse && $tblSubject && $tblPerson){ // && $Result['Room'] != ''
            $ImportRow = array(
                'Hour'         => $Result['Hour'],
                'Day'          => $Result['Day'],
                'Week'         => '',
                'Room'         => $Result['Room'],
                'SubjectGroup' => $Result['SubjectGroup'],
                'Level'        => $Level,
                'tblCourse'    => $tblCourse,
                'tblSubject'   => $tblSubject,
                'tblPerson'    => $tblPerson,
            );
            $this->ImportList[] = $ImportRow;
        } else {
            $this->WarningList[] = $Result;
        }
    }

    /**
     * @param $Value
     *
     * @return null|Danger|int
     */
    protected function sanitizeCourse($Value)
    {
        $result = array();
        if($Value == ''){
//            $this->CountImport['Course']['Keine Klasse'][] = 'Klasse nicht gefunden';
            return $result;
        }

        //ToDO Klassen aus einer Liste erkennen + Fehlermeldung anpassen
        $CourseList = explode('~', $Value);
        foreach($CourseList as $Course){
            $LevelName = null;
            $DivisionName = null;
            Division::useService()->matchDivision($Value, $LevelName, $DivisionName);
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
                $result[] = $tblDivisionList[0];
            }
            if($result == '' && $Value != ''){
                $this->CountImport['Course'][$Value][] = 'Klasse nicht gefunden';
            }
        }

        return $result;
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