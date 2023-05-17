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
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Transfer\Education\Education;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportMapping;
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

        //ToDO Ist das Datum relevant oder soll der Nutzer beim Export entscheiden?
        if(isset($Data['Date']) && $Data['Date'] != ''){
            $Date = new \DateTime($Data['Date']);
        } else {
            $Date = new \DateTime();
        }

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
        $this->setPointer(new FieldPointer('J', 'SubjectTo'));
        $this->setPointer(new FieldPointer('J', 'tblSubjectTo'));
        $this->setSanitizer(new FieldSanitizer('J', 'tblSubjectTo', array($this, 'sanitizeSubject')));
        $this->setPointer(new FieldPointer('J', 'SubjectGroupTo'));
        $this->setSanitizer(new FieldSanitizer('J', 'SubjectGroupTo', array($this, 'sanitizeSubjectGroup')));
        $this->setPointer(new FieldPointer('L', 'Room'));
        $this->setPointer(new FieldPointer('M', 'RoomTo'));
        $this->setPointer(new FieldPointer('O', 'Course'));
        // Liste aus Klassen im String getrent durch "~"
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

        //kurze schreibweise von ($Result['tblCourseList'] ? $Result['tblCourseList'] : null);
        $tblCourseList = ($Result['tblCourseList'] ? : null);
        // Vertretungseintrag hat Vorrang
        $Room = ($Result['RoomTo'] ? : ($Result['Room']));
        $Person = ($Result['PersonTo'] ? : ($Result['Person'] ? : ''));
        $tblPerson = ($Result['tblPersonTo'] ? : ($Result['tblPerson'] ? : null));
        $Subject = ($Result['SubjectTo'] ? : ($Result['Subject'] ? : ''));
        $tblSubject = ($Result['tblSubject'] ? : null);
        $tblSubstituteSubject = ($Result['tblSubjectTo'] ? : ($Result['tblSubject'] ? : null));
        // Gruppe nur wählen, wenn es auch ein Vertretungsfach gibt
        $tblSubjectGroup = ($Result['tblSubjectTo'] ? $Result['SubjectGroupTo'] : ($Result['SubjectGroupTo']));

        if($Result['Date'] && $tblCourseList){
            foreach($tblCourseList as $tblCourse){
                if($tblCourse && $tblSubject && $tblPerson){ // && $Result['Room'] != ''
                    $ImportRow = array(
                        'Date'                 => $Result['Date'],
                        'Hour'                 => $Result['Hour'],
                        'Room'                 => $Room,
                        'IsCanceled'           => ($Result['IsCanceled'] == '0' ? 1 : 0),
                        'SubjectGroup'         => $tblSubjectGroup,
                        'tblCourse'            => $tblCourse,
                        'tblSubject'           => $tblSubject,
                        'tblSubstituteSubject' => $tblSubstituteSubject,
                        'tblPerson'            => $tblPerson,
                    );
                    $this->ImportList[] = $ImportRow;
                } elseif($Result['Course'] != '' || $Person != '' || $Subject != '') {
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
            return false;
        }

        $CourseList = explode('~', $Value);
        foreach($CourseList as $Course){
            $tblDivisionCourse = false;
            // search with Level
            foreach($this->tblYearList as $tblYear){
                // Mapping
                if (($tblDivisionCourse = Education::useService()->getImportMappingValueBy(
                    TblImportMapping::TYPE_DIVISION_NAME_TO_DIVISION_COURSE_NAME, $Value, $tblYear
                ))) {

                    // Found
                } else {
                    $tblDivisionCourse = Education::useService()->getDivisionCourseByDivisionNameAndYear($Value, $tblYear);
                }

                if ($tblDivisionCourse) {
                    break;
                }
            }

            if($tblDivisionCourse){
                $result[] = $tblDivisionCourse;
                $this->CourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
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

        // Mapping
        if (($tblPerson = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_TEACHER_ACRONYM_TO_PERSON_ID, $Value))) {

        // Found
        } elseif (($tblTeacher = Teacher::useService()->getTeacherByAcronym($Value))) {
            $tblPerson = $tblTeacher->getServiceTblPerson();
        }

        if ($tblPerson) {
            return $tblPerson;
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

        // Mapping
        if (($tblSubject = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_SUBJECT_ACRONYM_TO_SUBJECT_ID, $Value))) {

            // Found
        } else {
            $tblSubject = Subject::useService()->getSubjectByVariantAcronym($Value);
        }

        if ($tblSubject) {
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