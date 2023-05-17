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
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Transfer\Education\Education;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImport;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportMapping;
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

        /** @var TblDivisionCourse $tblDivisionCourse */
        $tblDivisionCourse = ($Result['tblCourse'] ? : null);
        $tblPerson = ($Result['tblPerson'] ? : null);
        $tblSubject = ($Result['tblSubject'] ? : null);

        if($Result['tblCourse'] === false || $Result['tblPerson'] === false || $Result['tblSubject'] === false){
            // ignore Row complete
        } elseif($tblDivisionCourse && $tblSubject && $tblPerson){ // && $Result['Room'] != ''
            $CombineString = $Result['Hour'].'*'.$Result['Day'].'*'.$Result['Room'].'*'.$tblDivisionCourse->getId().'*'.$tblSubject->getId().'*'.$tblPerson->getId();
            if(!isset($this->CombineList[$CombineString])){
                // Spezialfall: Stundenplan für SekII -> es werden direkt beim Stundenplan die SekII-Kurse zugeordnet, falls vorhanden
                if (DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse)
                    && ($tblStudentList = $tblDivisionCourse->getStudents())
                    && ($tblYear = $tblDivisionCourse->getServiceTblYear())
                ) {
                    foreach ($tblStudentList as $tblStudent) {
                        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblStudent, $tblYear))
                            && ($level = $tblStudentEducation->getLevel())
                            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                        ) {
                            $divisionCourseName = Education::useService()->getCourseNameForSystem(
                                TblImport::EXTERN_SOFTWARE_NAME_UNTIS, $Result['SubjectGroup'], $level, $tblSchoolType
                            );

                            // mapping SekII-Kurs
                            if (($tblDivisionCourseCourseSystem = Education::useService()->getImportMappingValueBy(
                                TblImportMapping::TYPE_COURSE_NAME_TO_DIVISION_COURSE_NAME, $divisionCourseName, $tblYear
                            ))) {

                                // found SekII-Kurs
                            } elseif (($tblDivisionCourseCourseSystem = DivisionCourse::useService()->getDivisionCourseByNameAndYear(
                                $divisionCourseName, $tblYear
                            ))) {

                            }

                            if ($tblDivisionCourseCourseSystem
                                && ($tblDivisionCourseCourseSystem->getServiceTblSubject())
                                && $tblDivisionCourseCourseSystem->getServiceTblSubject()->getId() == $tblSubject->getId()
                            ) {
                                $tblDivisionCourse = $tblDivisionCourseCourseSystem;
                            }

                            break;
                        }
                    }
                }
                $ImportRow = array(
                    'Hour'         => $Result['Hour'],
                    'Day'          => $Result['Day'],
                    'Week'         => '',
                    'Room'         => $Result['Room'],
                    'SubjectGroup' => $Result['SubjectGroup'],
                    'Level'        => '',
                    'tblCourse'    => $tblDivisionCourse,
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
     * @return false|TblDivisionCourse|string
     */
    protected function sanitizeCourse($Value)
    {
        if($Value == ''){
//            $this->CountImport['Course']['Keine Klasse'][] = 'Klasse nicht gefunden';
            return false;
        }

        $tblDivisionCourse = false;
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

        $result = '';
        if($tblDivisionCourse){
            $result = $tblDivisionCourse;
        } else {
            $this->CountImport['Course'][$Value][] = 'Klasse nicht gefunden';
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
     * @return bool|TblSubject|string
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