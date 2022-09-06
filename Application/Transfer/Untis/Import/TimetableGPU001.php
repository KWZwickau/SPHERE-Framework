<?php
namespace SPHERE\Application\Transfer\Untis\Import;
/**
 * GPU001.TXT
 * https://platform.untis.at/HTML/WebHelp/de/untis/hid_export.htm
 * Nr Feld
 * A Unterr.-Nr.
 * B Klasse (Kürzel)
 * C Lehrer (Kürzel)
 * D Fach
 * E Raum
 * F Tag (1 = Montag, 2 = Dienstag, 3 = Mittwoch, 4 = Donnerstag, 5 = Freitag)
 * G Stunde
 * H Stundenlänge (hh:mm) (nur in Minute, sonst leer)
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
 * Class StudentCourseGPU001
 * @package SPHERE\Application\Transfer\Untis\Import
 */
class TimetableGPU001 extends AbstractConverter
{

    private $WarningList = array();
    private $ImportList = array();
    private $CountImport = array();
    private $tblYearList;
    private $CombineList = array();

    /**
     * GPU001 constructor.
     *
     * @param string  $File GPU001.txt
     */
    public function __construct($File, $Data)
    {
        $this->loadFile($File);
        $DateFrom = new DateTime($Data['DateFrom']);
        $this->tblYearList = Term::useService()->getYearAllByDate($DateFrom);

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        $this->setPointer(new FieldPointer('A', 'Number'));
        $this->setPointer(new FieldPointer('B', 'Division'));
        $this->setPointer(new FieldPointer('B', 'tblCourse'));
        $this->setSanitizer(new FieldSanitizer('B', 'tblCourse', array($this, 'sanitizeCourse')));

        $this->setPointer(new FieldPointer('C', 'Person'));
        $this->setPointer(new FieldPointer('C', 'tblPerson'));
        $this->setSanitizer(new FieldSanitizer('C', 'tblPerson', array($this, 'sanitizePerson')));
        $this->setPointer(new FieldPointer('D', 'Subject'));
        $this->setPointer(new FieldPointer('D', 'tblSubject'));
        $this->setPointer(new FieldPointer('D', 'SubjectGroup'));
        $this->setSanitizer(new FieldSanitizer('D', 'tblSubject', array($this, 'sanitizeSubject')));
        $this->setSanitizer(new FieldSanitizer('D', 'SubjectGroup', array($this, 'sanitizeSubjectGroup')));
        $this->setPointer(new FieldPointer('E', 'Room'));
        $this->setPointer(new FieldPointer('F', 'Day'));
        $this->setPointer(new FieldPointer('G', 'Hour'));
        $this->setPointer(new FieldPointer('H', 'Length'));

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
            $CombineString = $Result['Hour'].'*'.$Result['Day'].'*'.$Result['Room'].'*'.$tblCourse->getId().'*'.$tblSubject->getId().'*'.$tblPerson->getId();
            if(!isset($this->CombineList[$CombineString])){
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
                $this->CombineList[$CombineString] = true;
            }

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
        if($Value == ''){
//            $this->CountImport['Course']['Keine Klasse'][] = 'Klasse nicht gefunden';
            return false;
        }


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
        $result = '';
        if(!empty($tblDivisionList) && count($tblDivisionList) == 1){
            $result = $tblDivisionList[0];
        }
        if($result == '' && $Value != ''){
            $this->CountImport['Course'][$Value][] = 'Klasse nicht gefunden';
        }
        return $result;
    }

//    /**
//     * @param $Value
//     * @param $LevelName
//     * @param $DivisionName
//     */
//    protected function matchDivision($Value, &$LevelName, &$DivisionName)
//    {
//        // EVAMTL (5 OS)
//        if (preg_match('!^([0-9]*?) ([a-zA-Z]*?)$!is', $Value, $Match)) {
//            $LevelName = $Match[1];
//            $DivisionName = $Match[2];
//        }
//        // ESBD (5-1) -> bei uns 51
//        elseif (preg_match('!^([0-9]*?)(-[0-9]*?)$!is', $Value, $Match)) {
//            $LevelName = $Match[1] ;
//            $DivisionName = substr($Match[2], 1); // Minus entfernen
//        }
//        // HOGA (11 BGy-20/4)
//        elseif (preg_match('!^([0-9]*?) ([a-zA-Z0-9/-]*?)$!is', $Value, $Match)) {
//            $LevelName = $Match[1] ;
//            $DivisionName = $Match[2];
//        } elseif (preg_match('!^(.*?)$!is', $Value, $Match)) {
//            $LevelName = $Match[1];
//            $DivisionName = null;
//        }
//    }

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