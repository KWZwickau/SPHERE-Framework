<?php
/**
 * Export Unterricht (SpUnterricht.csv) Reihenfolge der Felder in der CSV-Datei SpUnterricht.csv
 * SpUnterricht.csv CSV-Datei Unterricht:
 * Nummer        Feld       Art
 * 1        Nummer          Num
 * 2        Fach            Str
 * 3        Lehrer          Str
 * 4        Lehrer2         Str
 * 5        Lehrer3         Str
 * 6        Klasse1         Str
 * 7        Klasse2         Str
 * 8        Gruppe          Str
 *
 */

namespace SPHERE\Application\Transfer\Indiware\Import;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\TblTeacher;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;
use SPHERE\Application\Transfer\Gateway\Converter\FieldSanitizer;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Warning;

/**
 * Class LectureshipGateway
 * @package SPHERE\Application\Transfer\Indiware\Import
 */
class LectureshipGateway extends AbstractConverter
{

    private $ResultList = array();
    private $ImportList = array();
    private $IsError = false;
    private $Year = false;
    private $Division = false;
    private $Subject = false;

    /**
     * LectureshipGateway constructor.
     *
     * @param string  $File SpUnterricht.csv
     * @param TblYear $tblYear
     */
    public function __construct($File, TblYear $tblYear)
    {
        $this->loadFile($File);
        $this->Year = $tblYear;

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        $this->setPointer(new FieldPointer('B', 'FileSubject'));
        $this->setPointer(new FieldPointer('B', 'AppSubject'));
        $this->setPointer(new FieldPointer('B', 'SubjectId'));
        $this->setSanitizer(new FieldSanitizer('B', 'AppSubject', array($this, 'sanitizeSubject')));
        $this->setSanitizer(new FieldSanitizer('B', 'SubjectId', array($this, 'fetchSubject')));

        $this->setPointer(new FieldPointer('C', 'FileTeacher1'));
        $this->setPointer(new FieldPointer('C', 'AppTeacher1'));
        $this->setPointer(new FieldPointer('C', 'TeacherId1'));
        $this->setSanitizer(new FieldSanitizer('C', 'AppTeacher1', array($this, 'sanitizeTeacher')));
        $this->setSanitizer(new FieldSanitizer('C', 'TeacherId1', array($this, 'fetchTeacher')));

        $this->setPointer(new FieldPointer('D', 'FileTeacher2'));
        $this->setPointer(new FieldPointer('D', 'AppTeacher2'));
        $this->setPointer(new FieldPointer('D', 'TeacherId2'));
        $this->setSanitizer(new FieldSanitizer('D', 'AppTeacher2', array($this, 'sanitizeTeacher')));
        $this->setSanitizer(new FieldSanitizer('D', 'TeacherId2', array($this, 'fetchTeacher')));

        $this->setPointer(new FieldPointer('E', 'FileTeacher3'));
        $this->setPointer(new FieldPointer('E', 'AppTeacher3'));
        $this->setPointer(new FieldPointer('E', 'TeacherId3'));
        $this->setSanitizer(new FieldSanitizer('E', 'AppTeacher3', array($this, 'sanitizeTeacher')));
        $this->setSanitizer(new FieldSanitizer('E', 'TeacherId3', array($this, 'fetchTeacher')));

        $this->setPointer(new FieldPointer('F', 'FileDivision1'));
        $this->setPointer(new FieldPointer('F', 'AppDivision1'));
        $this->setPointer(new FieldPointer('F', 'DivisionId1'));
        $this->setSanitizer(new FieldSanitizer('F', 'AppDivision1', array($this, 'sanitizeDivision')));
        $this->setSanitizer(new FieldSanitizer('F', 'DivisionId1', array($this, 'fetchDivision')));

        $this->setPointer(new FieldPointer('G', 'FileDivision2'));
        $this->setPointer(new FieldPointer('G', 'AppDivision2'));
        $this->setPointer(new FieldPointer('G', 'DivisionId2'));
        $this->setSanitizer(new FieldSanitizer('G', 'AppDivision2', array($this, 'sanitizeDivision2')));
        $this->setSanitizer(new FieldSanitizer('G', 'DivisionId2', array($this, 'fetchDivision')));

        $this->setPointer(new FieldPointer('H', 'FileSubjectGroup'));
        $this->setPointer(new FieldPointer('H', 'AppSubjectGroup'));
        $this->setSanitizer(new FieldSanitizer('H', 'AppSubjectGroup', array($this, 'sanitizeSubjectGroup')));

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

        if (!$this->IsError) {
            $tblDivision1 = (isset($Result['DivisionId1']) && $Result['DivisionId1'] !== null ? Division::useService()->getDivisionById($Result['DivisionId1']) : null);
            $tblDivision2 = (isset($Result['DivisionId2']) && $Result['DivisionId2'] !== null ? Division::useService()->getDivisionById($Result['DivisionId2']) : null);
            $tblTeacher1 = (isset($Result['TeacherId1']) && $Result['TeacherId1'] !== null ? Teacher::useService()->getTeacherById($Result['TeacherId1']) : null);
            $tblTeacher2 = (isset($Result['TeacherId2']) && $Result['TeacherId2'] !== null ? Teacher::useService()->getTeacherById($Result['TeacherId2']) : null);
            $tblTeacher3 = (isset($Result['TeacherId3']) && $Result['TeacherId3'] !== null ? Teacher::useService()->getTeacherById($Result['TeacherId3']) : null);
            $tblSubject = (isset($Result['SubjectId']) && $Result['SubjectId'] !== null ? Subject::useService()->getSubjectById($Result['SubjectId']) : null);
            $FileDivision1 = $Result['FileDivision1'];
            $FileDivision2 = $Result['FileDivision2'];
            $FileTeacher1 = $Result['FileTeacher1'];
            $FileTeacher2 = $Result['FileTeacher2'];
            $FileTeacher3 = $Result['FileTeacher3'];
            $FileSubject = $Result['FileSubject'];
            $FileSubjectGroup = $Result['FileSubjectGroup'];
            $AppSubjectGroup = $Result['AppSubjectGroup'];

            $ImportRow = array(
                'tblDivision1'     => $tblDivision1,
                'tblDivision2'     => $tblDivision2,
                'tblTeacher1'      => $tblTeacher1,
                'tblTeacher2'      => $tblTeacher2,
                'tblTeacher3'      => $tblTeacher3,
                'tblSubject'       => $tblSubject,
                'FileDivision1'    => $FileDivision1,
                'FileDivision2'    => $FileDivision2,
                'FileTeacher1'     => $FileTeacher1,
                'FileTeacher2'     => $FileTeacher2,
                'FileTeacher3'     => $FileTeacher3,
                'FileSubject'      => $FileSubject,
                'FileSubjectGroup' => $FileSubjectGroup,
                'AppSubjectGroup'  => $AppSubjectGroup
            );
            $this->ImportList[] = $ImportRow;
        } else {
            $this->IsError = false;
        }

        $this->ResultList[] = $Result;
    }

    /**
     * @param $Value
     *
     * @return null|Danger|int
     */
    protected function sanitizeDivision($Value)
    {
        $LevelName = null;
        $DivisionName = null;
        if ($Value === '') {
            $this->IsError = true;
            return new Danger(new Ban().' Keine Klasse angegeben!');
        }
        $this->MatchDivision($Value, $LevelName, $DivisionName);
        $tblLevel = null;
        $tblYear = Term::useService()->getYearById($this->Year);

        $tblDivisionList = array();
        // search with Level
        if (($tblLevelList = Division::useService()->getLevelAllByName($LevelName)) && $tblYear) {
            foreach ($tblLevelList as $tblLevel) {
                if (($tblDivisionArray = Division::useService()->getDivisionByDivisionNameAndLevelAndYear($DivisionName,
                    $tblLevel, $tblYear))
                ) {
                    if ($tblDivisionArray) {
                        foreach ($tblDivisionArray as $tblDivision) {
                            $tblDivisionList[] = $tblDivision;
                        }
                    }
                }
            }
            if (empty($tblDivisionList)) {
                $this->IsError = true;
                return new Danger(new Ban().' Klasse nicht gefunden!');
            } elseif (count($tblDivisionList) == 1) {
                /** @var TblDivision $tblDivision */
                $tblDivision = $tblDivisionList[0];
                return $tblDivision->getDisplayName();
            } else {
                $this->IsError = true;
                return new Danger(new Ban().' Zu viele Treffer für die Klasse!');
            }
        }
        // search without Level
        if ($tblLevel === null && $tblYear) {
            if (($tblDivisionArray = Division::useService()->getDivisionByDivisionNameAndLevelAndYear($DivisionName,
                $tblLevel, $tblYear))
            ) {
                if ($tblDivisionArray) {
                    foreach ($tblDivisionArray as $tblDivision) {
                        $tblDivisionList[] = $tblDivision;
                    }
                }
            }
            if (empty($tblDivisionList)) {
                $this->IsError = true;
                return new Danger(new Ban().' Klasse nicht gefunden!');
            } elseif (count($tblDivisionList) == 1) {
                $tblDivision = $tblDivisionList[0];
                return $tblDivision->getDisplayName();
            } else {
                $this->IsError = true;
                return new Danger(new Ban().' Zu viele Treffer für die Klasse!');
            }
        }
        if (!$tblYear) {
            $this->IsError = true;
            return new Danger(new Ban().' Schuljahr nicht gefunden!');
        }
        return null;
    }

    /**
     * @param $Value
     *
     * @return null|Danger|int
     */
    protected function sanitizeDivision2($Value)
    {
        $LevelName = null;
        $DivisionName = null;
        if ($Value === '') {
            return null;
        }
        $this->MatchDivision($Value, $LevelName, $DivisionName);
        $tblLevel = null;
        $tblYear = Term::useService()->getYearById($this->Year);

        $tblDivisionList = array();
        // search with Level
        if (($tblLevelList = Division::useService()->getLevelAllByName($LevelName)) && $tblYear) {
            foreach ($tblLevelList as $tblLevel) {
                if (($tblDivisionArray = Division::useService()->getDivisionByDivisionNameAndLevelAndYear($DivisionName,
                    $tblLevel, $tblYear))
                ) {
                    if ($tblDivisionArray) {
                        foreach ($tblDivisionArray as $tblDivision) {
                            $tblDivisionList[] = $tblDivision;
                        }
                    }
                }
            }
            if (empty($tblDivisionList)) {
                return null;
            } elseif (count($tblDivisionList) == 1) {
                /** @var TblDivision $tblDivision */
                $tblDivision = $tblDivisionList[0];
                return $tblDivision->getDisplayName();
            } else {
                return null;
            }
        }

        if (!$tblYear) {
            $this->IsError = true;
            return new Danger(new Ban().' Schuljahr nicht gefunden!');
        }
        return null;
    }

    /**
     * @param $Value
     *
     * @return null|int
     */
    protected function fetchDivision($Value)
    {

        if ($Value != '') {
            $LevelName = null;
            $DivisionName = null;
            $this->MatchDivision($Value, $LevelName, $DivisionName);
            $tblLevel = null;
            $tblYear = Term::useService()->getYearById($this->Year);


            $tblDivisionList = array();
            // search with Level
            if (($tblLevelList = Division::useService()->getLevelAllByName($LevelName)) && $tblYear) {
                foreach ($tblLevelList as $tblLevel) {
                    if (($tblDivisionArray = Division::useService()->getDivisionByDivisionNameAndLevelAndYear($DivisionName,
                        $tblLevel, $tblYear))
                    ) {
                        if ($tblDivisionArray) {
                            foreach ($tblDivisionArray as $tblDivision) {
                                $tblDivisionList[] = $tblDivision;
                            }
                        }
                    }
                }
                if (!empty($tblDivisionList) && count($tblDivisionList) == 1) {
                    $tblDivision = $tblDivisionList[0];
                    $this->Division = $tblDivision->getId();
                    return $tblDivision->getId();
                }
            }
            // search without Level
            if ($tblLevel === null && $tblYear) {
                if (($tblDivisionArray = Division::useService()->getDivisionByDivisionNameAndLevelAndYear($DivisionName,
                    $tblLevel, $tblYear))
                ) {
                    if ($tblDivisionArray) {
                        foreach ($tblDivisionArray as $tblDivision) {
                            $tblDivisionList[] = $tblDivision;
                        }
                    }
                }
                if (!empty($tblDivisionList) && count($tblDivisionList) == 1) {
                    $tblDivision = $tblDivisionList[0];
                    $this->Division = $tblDivision->getId();
                    return $tblDivision->getId();
                }
            }
        }
        return null;
    }

    /**
     * @param $Value
     * @param $LevelName
     * @param $DivisionName
     */
    protected function MatchDivision($Value, &$LevelName, &$DivisionName)
    {

        if (preg_match('!^(\d+)([a-zA-Z]*?)$!is', $Value, $Match)) {
            $LevelName = $Match[1];
            $DivisionName = $Match[2];
        } elseif (preg_match('!^(.*?)\s([a-zA-Z]*?)$!is', $Value, $Match)) {
            $LevelName = $Match[1];
            $DivisionName = $Match[2];
        } elseif (preg_match('!^([a-zA-Z]*?)\s(.W?)$!is', $Value, $Match)) {
            $DivisionName = $Match[1];
            $LevelName = $Match[2];
        } elseif (preg_match('!^([0-9]*?)$!is', $Value, $Match)) {
            $DivisionName = null;
            $LevelName = $Match[1];
        } elseif (preg_match('!^(.*?)$!is', $Value, $Match)) {
            $DivisionName = $Match[1];
            $LevelName = null;
        }
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
     * @return Warning|string
     */
    protected function sanitizeTeacher($Value)
    {
        if (empty($Value)) {
            return new Warning(new WarningIcon().' Lehrer wurde nicht angegeben');
        }

        if (!($tblTeacher = Teacher::useService()->getTeacherByAcronym($Value))) {
            return new Warning(new WarningIcon().' Das Lehrer-Kürzel '.$Value.' ist in der Schulsoftware nicht vorhanden');
        } else {
            return $tblTeacher->getAcronym().' - '.$tblTeacher->getServiceTblPerson()->getFullName();
        }
    }

    /**
     * @param $Value
     *
     * @return bool|TblTeacher
     */
    protected function fetchTeacher($Value)
    {

        $tblTeacher = false;
        if ($Value != '') {
            $tblTeacher = Teacher::useService()->getTeacherByAcronym($Value);
        }

        return ($tblTeacher ? $tblTeacher->getId() : null);
    }

    /**
     * @param $Value
     *
     * @return string
     */
    protected function sanitizeSubject($Value)
    {
        if (empty($Value)) {
            return new Warning(new WarningIcon().' Fach wurde nicht angegeben');
        }

        if (!($tblSubject = Subject::useService()->getSubjectByAcronym($Value))) {
            return new Warning(new WarningIcon().' Das Fach '.$Value.' ist in der Schulsoftware nicht vorhanden');
        } else {
            return $tblSubject->getAcronym().' - '.$tblSubject->getName();
        }
    }

    /**
     * @param $Value
     *
     * @return bool|TblSubject
     */
    protected function fetchSubject($Value)
    {
        $tblSubject = Subject::useService()->getSubjectByAcronym($Value);
        if ($tblSubject) {
            $this->Subject = $tblSubject->getId();
        }
        return ($tblSubject ? $tblSubject->getId() : null);
    }
}