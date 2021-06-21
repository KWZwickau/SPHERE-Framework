<?php
/**
 * Export Unterricht (GPU002.TXT) Reihenfolge der Felder in der DIF-Datei GPU002.TXT
 * GPU002.TXT DIF-Datei Unterricht:
 * Nummer        Feld                Art
 * 1        Unt-Nummer        Num
 * 2        Wochenstunden        Num
 * 3        Wochenstd. Kla.        Num *1)
 * 4        Wochenstd. Le.        Num *1)
 * 5        Klasse
 * 6        Lehrer
 * 7        Fach
 * 8        Fachraum
 * 9        Statistik 1 Unt.
 * 10       Studentenzahl        Num
 * 11       Wochenwert        Num *2)
 * 12       Gruppe
 * 13       Zeilentext 1
 * 14       Zeilenwert (in Tausendstel) *3)
 * 15       Datum von        Datum
 * 16       Datum bis        Datum
 * 17       Jahreswert Num *4)
 * 18       Text (früher U-ID)
 * 19       Teilungs-Nummer
 * 20       Stammraum
 * 21       Beschreibung
 * 22       Farbe Vg.        Farbe
 * 23       Farbe Hg.        Farbe
 * 24       Kennzeichen
 * 25       Fachfolge Klassen
 * 26       Fachfolge Lehrer
 * 27       Klassen-Kollisions-Kennz.
 * 28       Doppelstd. min.        Num
 * 29       Doppelstd. max.        Num
 * 30       Blockgröße        Num
 * 31       Std. im Raum        Num
 * 32       Priorität
 * 33       Statistik 1 Lehrer
 * 34       Studenten männl.        Num
 * 35       Studenten weibl.        Num
 * 36       Wert bzw. Faktor
 * 37       2. Block
 * 38       3. Block
 * 39       Zeilentext-2
 * 40       Eigenwert (ohne Faktoren - ausser Faktor Unterricht)
 * 41       Eigenwert (in 1/100000)
 * 42       Schülergruppe
 * 43       Wochenstunden in Jahres-Perioden-Planung (z.B. '2,4,0,2,3')
 * 44       Jahresstunden
 * 45       Zeilen-Unterrichtsgruppe
 *
 *
 * *1) Wochenstunden Klassen/Lehrer: Erscheint eine Klasse in mehreren Zeilen des selben Unterrichtes (mehrere Lehrer in dieser Klasse), so ist nur in der ersten Zeile diese Anzahl ungleich null.
 * *2) Wochenwert: Aus den Faktoren gerechneter Wochenwert für den Lehrer dieser Zeile.
 * *3) Zeilenwert: Nicht umgeschlüsselte Eintragung im Feld Zeilenwert.
 * *4) Jahreswert: Ohne Stundenplan gemittelte Jahreswertstunden (Wochenwert mal Jahreswochen).
 */
namespace SPHERE\Application\Transfer\Untis\Import;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
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
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;

/**
 * Class LectureshipGateway
 * @package SPHERE\Application\Transfer\Untis\Import
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
     * @param string  $File GPU002.TXT
     * @param TblYear $tblYear
     */
    public function __construct($File, TblYear $tblYear)
    {
        $this->loadFile($File);
        $this->Year = $tblYear;

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        $this->setPointer(new FieldPointer('E', 'FileDivision'));
        $this->setPointer(new FieldPointer('E', 'AppDivision'));
        $this->setPointer(new FieldPointer('E', 'DivisionId'));
        $this->setSanitizer(new FieldSanitizer('E', 'AppDivision', array($this, 'sanitizeDivision')));
        $this->setSanitizer(new FieldSanitizer('E', 'DivisionId', array($this, 'fetchDivision')));

        $this->setPointer(new FieldPointer('F', 'FileTeacher'));
        $this->setPointer(new FieldPointer('F', 'AppTeacher'));
        $this->setPointer(new FieldPointer('F', 'TeacherId'));
        $this->setSanitizer(new FieldSanitizer('F', 'AppTeacher', array($this, 'sanitizeTeacher')));
        $this->setSanitizer(new FieldSanitizer('F', 'TeacherId', array($this, 'fetchTeacher')));

        $this->setPointer(new FieldPointer('G', 'FileSubject'));
        $this->setPointer(new FieldPointer('G', 'AppSubject'));
        $this->setPointer(new FieldPointer('G', 'SubjectId'));
        $this->setSanitizer(new FieldSanitizer('G', 'AppSubject', array($this, 'sanitizeSubject')));
        $this->setSanitizer(new FieldSanitizer('G', 'SubjectId', array($this, 'fetchSubject')));

        $this->setPointer(new FieldPointer('L', 'FileSubjectGroup'));
        $this->setPointer(new FieldPointer('L', 'AppSubjectGroup'));
        $this->setSanitizer(new FieldSanitizer('L', 'AppSubjectGroup', array($this, 'sanitizeSubjectGroup')));

        $this->scanFile(0);
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
            $tblDivision = ( isset($Result['DivisionId']) && $Result['DivisionId'] !== null ? Division::useService()->getDivisionById($Result['DivisionId']) : null );
            $tblTeacher = ( isset($Result['TeacherId']) && $Result['TeacherId'] !== null ? Teacher::useService()->getTeacherById($Result['TeacherId']) : null );
            $tblSubject = ( isset($Result['SubjectId']) && $Result['SubjectId'] !== null ? Subject::useService()->getSubjectById($Result['SubjectId']) : null );
            $FileDivision = $Result['FileDivision'];
            $FileTeacher = $Result['FileTeacher'];
            $FileSubject = $Result['FileSubject'];
            $FileSubjectGroup = $Result['FileSubjectGroup'];
            $AppSubjectGroup = $Result['AppSubjectGroup'];


            // Work around Sek II mit einem String als Fach und Fachgruppe
            // Nur bei Klasse 11 & 12 & 13
            if(preg_match('!^[1][1-3]!is', $FileDivision)){
                $subjectAcronym = $this->getSubjectAcronym($FileSubject);
                $subjectGroup = $this->getSubjectGroup($FileSubject);
                // nur wenn das Fach & die Fachgruppe als String hinterlegt sind
                if($subjectAcronym && $subjectGroup){
                    // findes des Fach's über das extrahierte Acronym
                    if(($SubjectId = $this->fetchSubject($subjectAcronym))){
                        $tblSubject = Subject::useService()->getSubjectById($SubjectId);
                        if(!$tblSubject){
                            $tblSubject = null;
                        }
                    }
                    // korrektur des Array Elements
                    $Result['AppSubject'] = $this->sanitizeSubject($subjectAcronym);
                    // Sowohl im Array als auch als Variable mitgeben
                    $Result['FileSubjectGroup'] = $FileSubjectGroup = $subjectGroup;
                    // Hinterlegung der Fachgruppen, egal ob vorhanden oder nicht, -> wird zur not neu angelegt
                    $AppSubjectGroup = $subjectGroup;
                    // Anzeige, welche Gruppen vorhanden sind oder neu angelegt werden
                    if(!$this->fetchSubjectGroup($subjectGroup)){
                        $subjectGroup .= new Small(new Small(new Muted(' (wird neu angelegt)')));
                    }
                    $Result['AppSubjectGroup'] = $subjectGroup;
                }
            }

            $ImportRow = array('tblDivision'      => $tblDivision,
                               'tblTeacher'       => $tblTeacher,
                               'tblSubject'       => $tblSubject,
                               'FileDivision'     => $FileDivision,
                               'FileTeacher'      => $FileTeacher,
                               'FileSubject'      => $FileSubject,
                               'FileSubjectGroup' => $FileSubjectGroup,
                               'AppSubjectGroup'  => $AppSubjectGroup);
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
        $tblYear = Term::useService()->getYearById($this->Year->getId());

        $tblDivisionList = array();
        // search with Level
        if (( $tblLevelList = Division::useService()->getLevelAllByName($LevelName) ) && $tblYear) {
            foreach ($tblLevelList as $tblLevel) {
                if (( $tblDivisionArray = Division::useService()->getDivisionByDivisionNameAndLevelAndYear($DivisionName, $tblLevel, $tblYear) )) {
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
        if ($tblLevel === null && $tblYear && $LevelName == '') {
            if (( $tblDivisionArray = Division::useService()->getDivisionByDivisionNameAndLevelAndYear($DivisionName, $tblLevel, $tblYear) )) {
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
        } else {
            $this->IsError = true;
            return new Danger(new Ban().' Klasse nicht gefunden!');
        }
    }

    /**
     * @param $Value
     *
     * @return null|Danger|int
     */
    protected function fetchDivision($Value)
    {
        if ($Value != '') {
            $LevelName = null;
            $DivisionName = null;
            $this->MatchDivision($Value, $LevelName, $DivisionName);
            $tblLevel = null;
            $tblYear = Term::useService()->getYearById($this->Year->getId());

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
        // EVAMTL (5 OS)
        if (preg_match('!^([0-9]*?) ([a-zA-Z]*?)$!is', $Value, $Match)) {
            $LevelName = $Match[1];
            $DivisionName = $Match[2];
        }
        // ESBD (5-1) -> bei uns 51
          elseif (preg_match('!^([0-9]*?)(-[0-9]*?)$!is', $Value, $Match)) {
            $LevelName = $Match[1] ;
            $DivisionName = substr($Match[2], 1); // Minus entfernen
        } // HOGA (12dGy/- 20)
          elseif (preg_match('!^([0-9]*?)([a-zA-Z -/_][a-zA-Z0-9 -/_]*?)$!is', $Value, $Match)) {
            $LevelName = $Match[1];
            $DivisionName = trim($Match[2]);
        } elseif (preg_match('!^(.*?)$!is', $Value, $Match)) {
            $LevelName = $Match[1];
            $DivisionName = null;
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
//            $GroupName = $Match[1];
//            $tblDivision = Division::useService()->getDivisionById($this->Division);
//            $tblSubject = Subject::useService()->getSubjectById($this->Subject);
//            if ($tblDivision && $tblSubject) {
//                $tblSubjectGroup = Division::useService()->getSubjectGroupByNameAndDivisionAndSubject($GroupName, $tblDivision, $tblSubject);
//                if ($tblSubjectGroup) {
//                    return $tblSubjectGroup->getName().', '.$tblSubjectGroup->getDescription();
//                }
//                return new Warning(new WarningIcon().' Gruppe nicht gefunden');
//            }
//            return new Warning(new WarningIcon().' Klasse/Fach fehlt');
            return $Match[1];
        }
        return '';
    }

    /**
     * @param $Value
     *
     * @return string
     */
    protected function fetchSubjectGroup($Value)
    {
        if (preg_match('!^([\w\/]{1,}-[GLgl]-[\d])!', $Value, $Match)) {
            $GroupName = $Match[1];
            $tblDivision = Division::useService()->getDivisionById($this->Division);
            $tblSubject = Subject::useService()->getSubjectById($this->Subject);
            if ($tblDivision && $tblSubject) {
                $tblSubjectGroup = Division::useService()->getSubjectGroupByNameAndDivisionAndSubject($GroupName, $tblDivision, $tblSubject);
                if ($tblSubjectGroup) {
                    return $tblSubjectGroup->getName();
                }
            }
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

        if (!( $tblTeacher = Teacher::useService()->getTeacherByAcronym($Value) )) {
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

        return ( $tblTeacher ? $tblTeacher->getId() : null );
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

        if (!( $tblSubject = Subject::useService()->getSubjectByAcronym($Value) )) {
            return new Warning(new WarningIcon().' Das Fach '.$Value.' ist in der Schulsoftware nicht vorhanden');
        } else {
            return $tblSubject->getAcronym().' - '.$tblSubject->getName();
        }
    }

    /**
     * @param $Value
     *
     * @return null|int
     */
    protected function fetchSubject($Value)
    {

        $tblSubject = Subject::useService()->getSubjectByAcronym($Value);
        if ($tblSubject) {
            $this->Subject = $tblSubject->getId();
        } elseif($Acronym = $this->getSubjectAcronym($Value)) {
            $tblSubject = Subject::useService()->getSubjectByAcronym($Acronym);
            if ($tblSubject){
                $this->Subject = $tblSubject->getId();
            }
        }
        return ( $tblSubject ? $tblSubject->getId() : null );
    }

    /**
     * @param $SubjectString
     *
     * @return false|string false| eg. EN | DE
     */
    protected function getSubjectAcronym($SubjectString)
    {
        if(preg_match('!^([\w\/]{1,})-([GLgl]-[\d])!', $SubjectString, $Match)){
            return $Match[1];
        }
        return false;
    }

    /**
     * @param string $SubjectString
     *
     * @return false|string false| eg. EN-L-1 | EN-G-1
     */
    private function getSubjectGroup($SubjectString = '')
    {

        if(preg_match('!^([\w\/]{1,}-[GLgl]-[\d])!', $SubjectString, $Match)){
            if(isset($Match[1])){
                return $Match[1];
            }
        }
        return false;
    }
}