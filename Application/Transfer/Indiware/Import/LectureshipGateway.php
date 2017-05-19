<?php
/**
 * Export Unterricht (SpUnterricht.csv) Ungekürzt Reihenfolge der Felder in der CSV-Datei SpUnterricht.csv
 * SpUnterricht.csv CSV-Datei Unterricht:
 * Nummer        Feld       Art
 * 1        Nummer          Int
 * 2        Id              Int
 * 3        Stunden         Int
 * 4        Fach            Str
 * 5        Lehrer          Str
 * 6        Lehrer2         Str
 * 7        Lehrer3         Str
 * 8        AbwStunden      Str
 * 9        LStunden1       Str
 * 10       LStunden2       Str
 * 11       LStunden3       Str
 * 12       Klasse1         Str
 * 13       Klasse2         Str
 * 14       Klasse3         Str
 * 15       Klasse4         Str
 * 16       Klasse5         Str
 * 17       Klasse6         Str
 * 18       Klasse7         Str
 * 19       Klasse8         Str
 * 20       Klasse9         Str
 * 21       Klasse10        Str
 * 22       Klasse11        Str
 * 23       Klasse12        Str
 * 24       Klasse13        Str
 * 25       Klasse14        Str
 * 26       Klasse15        Str
 * 27       Klasse16        Str
 * 28       Klasse17        Str
 * 29       Klasse18        Str
 * 30       Klasse19        Str
 * 31       Klasse20        Str
 * 32       Gruppe          Str
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
//    private $IsError = false;
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

        $this->setPointer(new FieldPointer('D', 'FileSubject'));
        $this->setPointer(new FieldPointer('D', 'AppSubject'));
        $this->setPointer(new FieldPointer('D', 'SubjectId'));
        $this->setSanitizer(new FieldSanitizer('D', 'AppSubject', array($this, 'sanitizeSubject')));
        $this->setSanitizer(new FieldSanitizer('D', 'SubjectId', array($this, 'fetchSubject')));

        $TeacherList = array(1 => 'E', 2 => 'F', 3 => 'G');
        foreach ($TeacherList as $Key => $FieldPosition) {
            $this->setPointer(new FieldPointer($FieldPosition, 'FileTeacher'.$Key));
            $this->setPointer(new FieldPointer($FieldPosition, 'AppTeacher'.$Key));
            $this->setPointer(new FieldPointer($FieldPosition, 'TeacherId'.$Key));
            $this->setSanitizer(new FieldSanitizer($FieldPosition, 'AppTeacher'.$Key, array($this, 'sanitizeTeacher')));
            $this->setSanitizer(new FieldSanitizer($FieldPosition, 'TeacherId'.$Key, array($this, 'fetchTeacher')));
        }

        $this->setPointer(new FieldPointer('L', 'FileDivision1'));
        $this->setPointer(new FieldPointer('L', 'AppDivision1'));
        $this->setPointer(new FieldPointer('L', 'DivisionId1'));
        $this->setSanitizer(new FieldSanitizer('L', 'AppDivision1', array($this, 'sanitizeDivision2')));
        $this->setSanitizer(new FieldSanitizer('L', 'DivisionId1', array($this, 'fetchDivision')));

        $DivisionList = array(
            2  => 'M',
            3  => 'N',
            4  => 'O',
            5  => 'P',
            6  => 'Q',
            7  => 'R',
            8  => 'S',
            9  => 'T',
            10 => 'U',
            11 => 'V',
            12 => 'W',
            13 => 'X',
            14 => 'Y',
            15 => 'Z',
            16 => 'AA',
            17 => 'AB',
            18 => 'AC',
            19 => 'AD',
            20 => 'AE'
        );
        foreach ($DivisionList as $Key => $FieldPosition) {
            $this->setPointer(new FieldPointer($FieldPosition, 'FileDivision'.$Key));
            $this->setPointer(new FieldPointer($FieldPosition, 'AppDivision'.$Key));
            $this->setPointer(new FieldPointer($FieldPosition, 'DivisionId'.$Key));
            $this->setSanitizer(new FieldSanitizer($FieldPosition, 'AppDivision'.$Key,
                array($this, 'sanitizeDivision2')));
            $this->setSanitizer(new FieldSanitizer($FieldPosition, 'DivisionId'.$Key, array($this, 'fetchDivision')));
        }

        $this->setPointer(new FieldPointer('AF', 'FileSubjectGroup'));
        $this->setPointer(new FieldPointer('AF', 'AppSubjectGroup'));
        $this->setSanitizer(new FieldSanitizer('AF', 'AppSubjectGroup', array($this, 'sanitizeSubjectGroup')));

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

//        if (!$this->IsError) {
        $tblDivision1 = (isset($Result['DivisionId1']) && $Result['DivisionId1'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId1']) : null);
        $tblDivision2 = (isset($Result['DivisionId2']) && $Result['DivisionId2'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId2']) : null);
        $tblDivision3 = (isset($Result['DivisionId3']) && $Result['DivisionId3'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId3']) : null);
        $tblDivision4 = (isset($Result['DivisionId4']) && $Result['DivisionId4'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId4']) : null);
        $tblDivision5 = (isset($Result['DivisionId5']) && $Result['DivisionId5'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId5']) : null);
        $tblDivision6 = (isset($Result['DivisionId6']) && $Result['DivisionId6'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId6']) : null);
        $tblDivision7 = (isset($Result['DivisionId7']) && $Result['DivisionId7'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId7']) : null);
        $tblDivision8 = (isset($Result['DivisionId8']) && $Result['DivisionId8'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId8']) : null);
        $tblDivision9 = (isset($Result['DivisionId9']) && $Result['DivisionId9'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId9']) : null);
        $tblDivision10 = (isset($Result['DivisionId10']) && $Result['DivisionId10'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId10']) : null);
        $tblDivision11 = (isset($Result['DivisionId11']) && $Result['DivisionId11'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId11']) : null);
        $tblDivision12 = (isset($Result['DivisionId12']) && $Result['DivisionId12'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId12']) : null);
        $tblDivision13 = (isset($Result['DivisionId13']) && $Result['DivisionId13'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId13']) : null);
        $tblDivision14 = (isset($Result['DivisionId14']) && $Result['DivisionId14'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId14']) : null);
        $tblDivision15 = (isset($Result['DivisionId15']) && $Result['DivisionId15'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId15']) : null);
        $tblDivision16 = (isset($Result['DivisionId16']) && $Result['DivisionId16'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId16']) : null);
        $tblDivision17 = (isset($Result['DivisionId17']) && $Result['DivisionId17'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId17']) : null);
        $tblDivision18 = (isset($Result['DivisionId18']) && $Result['DivisionId18'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId18']) : null);
        $tblDivision19 = (isset($Result['DivisionId19']) && $Result['DivisionId19'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId19']) : null);
        $tblDivision20 = (isset($Result['DivisionId20']) && $Result['DivisionId20'] !== null ? Division::useService()
            ->getDivisionById($Result['DivisionId20']) : null);
        $tblTeacher1 = (isset($Result['TeacherId1']) && $Result['TeacherId1'] !== null ? Teacher::useService()
            ->getTeacherById($Result['TeacherId1']) : null);
        $tblTeacher2 = (isset($Result['TeacherId2']) && $Result['TeacherId2'] !== null ? Teacher::useService()
            ->getTeacherById($Result['TeacherId2']) : null);
        $tblTeacher3 = (isset($Result['TeacherId3']) && $Result['TeacherId3'] !== null ? Teacher::useService()
            ->getTeacherById($Result['TeacherId3']) : null);
        $tblSubject = (isset($Result['SubjectId']) && $Result['SubjectId'] !== null ? Subject::useService()
            ->getSubjectById($Result['SubjectId']) : null);
        $FileDivision1 = $Result['FileDivision1'];
        $FileDivision2 = $Result['FileDivision2'];
        $FileDivision3 = $Result['FileDivision3'];
        $FileDivision4 = $Result['FileDivision4'];
        $FileDivision5 = $Result['FileDivision5'];
        $FileDivision6 = $Result['FileDivision6'];
        $FileDivision7 = $Result['FileDivision7'];
        $FileDivision8 = $Result['FileDivision8'];
        $FileDivision9 = $Result['FileDivision9'];
        $FileDivision10 = $Result['FileDivision10'];
        $FileDivision11 = $Result['FileDivision11'];
        $FileDivision12 = $Result['FileDivision12'];
        $FileDivision13 = $Result['FileDivision13'];
        $FileDivision14 = $Result['FileDivision14'];
        $FileDivision15 = $Result['FileDivision15'];
        $FileDivision16 = $Result['FileDivision16'];
        $FileDivision17 = $Result['FileDivision17'];
        $FileDivision18 = $Result['FileDivision18'];
        $FileDivision19 = $Result['FileDivision19'];
        $FileDivision20 = $Result['FileDivision20'];
        $FileTeacher1 = $Result['FileTeacher1'];
        $FileTeacher2 = $Result['FileTeacher2'];
        $FileTeacher3 = $Result['FileTeacher3'];
        $FileSubject = $Result['FileSubject'];
        $FileSubjectGroup = $Result['FileSubjectGroup'];
        $AppSubjectGroup = $Result['AppSubjectGroup'];

        $ImportRow = array(
            'tblDivision1'     => $tblDivision1,
            'tblDivision2'     => $tblDivision2,
            'tblDivision3'     => $tblDivision3,
            'tblDivision4'     => $tblDivision4,
            'tblDivision5'     => $tblDivision5,
            'tblDivision6'     => $tblDivision6,
            'tblDivision7'     => $tblDivision7,
            'tblDivision8'     => $tblDivision8,
            'tblDivision9'     => $tblDivision9,
            'tblDivision10'    => $tblDivision10,
            'tblDivision11'    => $tblDivision11,
            'tblDivision12'    => $tblDivision12,
            'tblDivision13'    => $tblDivision13,
            'tblDivision14'    => $tblDivision14,
            'tblDivision15'    => $tblDivision15,
            'tblDivision16'    => $tblDivision16,
            'tblDivision17'    => $tblDivision17,
            'tblDivision18'    => $tblDivision18,
            'tblDivision19'    => $tblDivision19,
            'tblDivision20'    => $tblDivision20,
            'tblTeacher1'      => $tblTeacher1,
            'tblTeacher2'      => $tblTeacher2,
            'tblTeacher3'      => $tblTeacher3,
            'tblSubject'       => $tblSubject,
            'FileDivision1'    => $FileDivision1,
            'FileDivision2'    => $FileDivision2,
            'FileDivision3'    => $FileDivision3,
            'FileDivision4'    => $FileDivision4,
            'FileDivision5'    => $FileDivision5,
            'FileDivision6'    => $FileDivision6,
            'FileDivision7'    => $FileDivision7,
            'FileDivision8'    => $FileDivision8,
            'FileDivision9'    => $FileDivision9,
            'FileDivision10'   => $FileDivision10,
            'FileDivision11'   => $FileDivision11,
            'FileDivision12'   => $FileDivision12,
            'FileDivision13'   => $FileDivision13,
            'FileDivision14'   => $FileDivision14,
            'FileDivision15'   => $FileDivision15,
            'FileDivision16'   => $FileDivision16,
            'FileDivision17'   => $FileDivision17,
            'FileDivision18'   => $FileDivision18,
            'FileDivision19'   => $FileDivision19,
            'FileDivision20'   => $FileDivision20,
            'FileTeacher1'     => $FileTeacher1,
            'FileTeacher2'     => $FileTeacher2,
            'FileTeacher3'     => $FileTeacher3,
            'FileSubject'      => $FileSubject,
            'FileSubjectGroup' => $FileSubjectGroup,
            'AppSubjectGroup'  => $AppSubjectGroup
        );
        $this->ImportList[] = $ImportRow;
//        } else {
//            $this->IsError = false;
//        }

        $this->ResultList[] = $Result;
    }

//    /**
//     * @param $Value
//     *
//     * @return null|Danger|int
//     */
//    protected function sanitizeDivision($Value)
//    {
//        $LevelName = null;
//        $DivisionName = null;
//        if ($Value === '') {
//            $this->IsError = true;
//            return new Danger(new Ban().' Keine Klasse angegeben!');
//        }
//        $this->MatchDivision($Value, $LevelName, $DivisionName);
//        $tblLevel = null;
//        $tblYear = Term::useService()->getYearById($this->Year);
//
//        $tblDivisionList = array();
//        // search with Level
//        if (($tblLevelList = Division::useService()->getLevelAllByName($LevelName)) && $tblYear) {
//            foreach ($tblLevelList as $tblLevel) {
//                if (($tblDivisionArray = Division::useService()->getDivisionByDivisionNameAndLevelAndYear($DivisionName,
//                    $tblLevel, $tblYear))
//                ) {
//                    if ($tblDivisionArray) {
//                        foreach ($tblDivisionArray as $tblDivision) {
//                            $tblDivisionList[] = $tblDivision;
//                        }
//                    }
//                }
//            }
//            if (empty($tblDivisionList)) {
//                $this->IsError = true;
//                return new Danger(new Ban().' Klasse nicht gefunden!');
//            } elseif (count($tblDivisionList) == 1) {
//                /** @var TblDivision $tblDivision */
//                $tblDivision = $tblDivisionList[0];
//                return $tblDivision->getDisplayName();
//            } else {
//                $this->IsError = true;
//                return new Danger(new Ban().' Zu viele Treffer für die Klasse!');
//            }
//        }
//        // search without Level
//        if ($tblLevel === null && $tblYear && $LevelName == '') {
//            if (($tblDivisionArray = Division::useService()->getDivisionByDivisionNameAndLevelAndYear($DivisionName,
//                $tblLevel, $tblYear))
//            ) {
//                if ($tblDivisionArray) {
//                    foreach ($tblDivisionArray as $tblDivision) {
//                        $tblDivisionList[] = $tblDivision;
//                    }
//                }
//            }
//            if (empty($tblDivisionList)) {
//                $this->IsError = true;
//                return new Danger(new Ban().' Klasse nicht gefunden!');
//            } elseif (count($tblDivisionList) == 1) {
//                $tblDivision = $tblDivisionList[0];
//                return $tblDivision->getDisplayName();
//            } else {
//                $this->IsError = true;
//                return new Danger(new Ban().' Zu viele Treffer für die Klasse!');
//            }
//        }
//        if (!$tblYear) {
//            $this->IsError = true;
//            return new Danger(new Ban().' Schuljahr nicht gefunden!');
//        } else {
//            $this->IsError = true;
//            return new Danger(new Ban().' Klasse nicht gefunden!');
//        }
//    }

    /**
     * @param $Value
     *
     * @return null|string|int
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
                if ($DivisionName != '') {
                    return new Warning(new Danger(new WarningIcon()).' Klasse wurde nicht gefunden');
                } else {
                    return null;
                }
            } elseif (count($tblDivisionList) == 1) {
                /** @var TblDivision $tblDivision */
                $tblDivision = $tblDivisionList[0];
                return $tblDivision->getDisplayName();
            } else {
                return null;
            }
        }

//        if (!$tblYear) {
//            $this->IsError = true;
//            return new Danger(new Ban().' Schuljahr nicht gefunden!');
//        }
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
            if ($tblLevel === null && $tblYear && $LevelName == '') {
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
        } elseif (preg_match('!^([a-zA-Z]*?)(\d+)$!is', $Value, $Match)) {
            $LevelName = $Match[2];
            $DivisionName = $Match[1];
        } elseif (preg_match('!^(.*?)$!is', $Value, $Match)) {
            $DivisionName = $Match[1];
            $LevelName = null;
        }
        $DivisionName = trim($DivisionName);
        $LevelName = trim($LevelName);
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