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
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\TblTeacher;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;
use SPHERE\Application\Transfer\Gateway\Converter\FieldSanitizer;
use SPHERE\Common\Frontend\Text\Repository\Danger;

/**
 * Class LectureshipGateway
 * @package SPHERE\Application\Transfer\Untis\Import
 */
class LectureshipGateway extends AbstractConverter
{

    private $ResultList = array();

    /**
     * LectureshipGateway constructor.
     * @param string $File GPU002.TXT
     */
    public function __construct($File)
    {
        $this->loadFile($File);

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        $this->setPointer(new FieldPointer('E', 'Datei: Klasse'));
        $this->setPointer(new FieldPointer('E', 'Software: Klassenstufe'));
        $this->setPointer(new FieldPointer('E', 'tblLevel'));
        $this->setSanitizer(new FieldSanitizer('E', 'Software: Klassenstufe', array($this, 'sanitizeLevel')));
        $this->setSanitizer(new FieldSanitizer('E', 'tblLevel', array($this, 'fetchLevel')));
        $this->setPointer(new FieldPointer('E', 'Software: Klassengruppe'));
        $this->setSanitizer(new FieldSanitizer('E', 'Software: Klassengruppe', array($this, 'sanitizeGroup')));

        $this->setPointer(new FieldPointer('F', 'Datei: Lehrer'));
        $this->setPointer(new FieldPointer('F', 'Software: Lehrer'));
        $this->setPointer(new FieldPointer('F', 'tblTeacher'));
        $this->setSanitizer(new FieldSanitizer('F', 'Software: Lehrer', array($this, 'sanitizeTeacher')));
        $this->setSanitizer(new FieldSanitizer('F', 'tblTeacher', array($this, 'fetchTeacher')));

        $this->setPointer(new FieldPointer('G', 'Datei: Fach'));
        $this->setPointer(new FieldPointer('G', 'Software: Fach'));
        $this->setPointer(new FieldPointer('G', 'tblSubject'));
        $this->setSanitizer(new FieldSanitizer('G', 'Software: Fach', array($this, 'sanitizeSubject')));
        $this->setSanitizer(new FieldSanitizer('G', 'tblSubject', array($this, 'fetchSubject')));

        $this->setPointer(new FieldPointer('L', 'Datei: Gruppe'));
        $this->setPointer(new FieldPointer('L', 'Software: Gruppe'));


        $this->scanFile(0,2);
    }

    /**
     * @return array
     */
    public function getResultList()
    {
        return $this->ResultList;
    }

    /**
     * @param array $Row
     *
     * @return void
     */
    public function runConvert($Row)
    {
        // TODO: Implement runConvert() method.
        $Result = array();
        foreach ($Row as $Part) {
            $Result = array_merge($Result, $Part);
        }
        $this->ResultList[] = $Result;
    }

    /**
     * @param $Value
     * @return Danger|string
     */
    protected function sanitizeLevel($Value)
    {
        if (preg_match('!^(.*?)\s.*?$!is', $Value, $Match)) {
            return $Match[1];
        }
        return '';
    }
    /**
     * @param $Value
     * @return bool|TblLevel
     */
    protected function fetchLevel($Value)
    {
        return '';
    }

    /**
     * @param $Value
     * @return Danger|string
     */
    protected function sanitizeGroup($Value)
    {
        if (preg_match('!^.*?\s(.*?)$!is', $Value, $Match)) {
            return $Match[1];
        }
        return '';
    }

    /**
     * @param $Value
     * @return Danger|string
     */
    protected function sanitizeTeacher( $Value )
    {
        if( empty($Value) ) {
            return new Danger( 'Lehrer wurde nicht angegeben' );
        }

        if( !($tblTeacher = Teacher::useService()->getTeacherByAcronym( $Value )) ) {
            return new Danger( 'Das Lehrer-Kürzel '.$Value.' ist in der Schulsoftware nicht vorhanden' );
        } else {
            return $tblTeacher->getAcronym().' - '.$tblTeacher->getServiceTblPerson()->getFullName();
        }
    }
    /**
     * @param $Value
     * @return bool|TblTeacher
     */
    protected function fetchTeacher($Value)
    {
        return Teacher::useService()->getTeacherByAcronym($Value);
    }

    /**
     * @param $Value
     * @return Danger|string
     */
    protected function sanitizeSubject($Value)
    {
        if (empty($Value)) {
            return new Danger('Fach wurde nicht angegeben');
        }

        if (!($tblSubject = Subject::useService()->getSubjectByAcronym($Value))) {
            return new Danger('Das Fach ' . $Value . ' ist in der Schulsoftware nicht vorhanden');
        } else {
            return $tblSubject->getAcronym() . ' - ' . $tblSubject->getName();
        }
    }

    /**
     * @param $Value
     * @return bool|TblSubject
     */
    protected function fetchSubject($Value)
    {
        return Subject::useService()->getSubjectByAcronym($Value);
    }
}