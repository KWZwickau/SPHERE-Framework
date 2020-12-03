<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;
use SPHERE\Application\Transfer\Gateway\Converter\FieldSanitizer;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\Text\Repository\Warning;

/**
 * Class StudentCourseGPU015
 * @package SPHERE\Application\Transfer\Untis\Import
 */
class StudentCourseGPU015 extends AbstractConverter
{

    private $Gateway010;

    /**
     * GPU010 constructor.
     *
     * @param string  $File GPU015.txt
     * @param array   $Gateway010
     */
    public function __construct($File, $Gateway010)
    {
        $this->loadFile($File);
        $this->Gateway010 = $Gateway010;

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        $this->setPointer(new FieldPointer('A', 'ShortName'));
        $this->setPointer(new FieldPointer('C', 'FileSubject'));
        $this->setPointer(new FieldPointer('C', 'AppSubject'));
        $this->setPointer(new FieldPointer('C', 'EntitySubject'));
        $this->setPointer(new FieldPointer('C', 'SubjectGroup'));
        $this->setSanitizer(new FieldSanitizer('C', 'AppSubject', array($this, 'sanitizeSubject')));
        $this->setSanitizer(new FieldSanitizer('C', 'EntitySubject', array($this, 'sanitizeEntitySubject')));
        $this->setSanitizer(new FieldSanitizer('C', 'SubjectGroup', array($this, 'sanitizeSubjectGroup')));

        $this->setPointer(new FieldPointer('E', 'FileDivision'));

        $this->scanFile(0);
    }

    /**
     * @return array
     */
    public function getImportList()
    {
        // Sortierung der Ausgabe 1.L & 2.G diese aber alphabetisch
        foreach($this->Gateway010 as $RKey => &$Row){
            if(isset($Row['SubjectList'])){
                $L_Group = array();
                $G_Group = array();
                foreach($Row['SubjectList'] as $Subject){
                    if(preg_match('!-[Ll]-!',$Subject['SubjectGroup'])){
                        $L_Group[] = $Subject;
                    } else {
                        $G_Group[] = $Subject;
                    }
                }
                sort($L_Group);
                sort($G_Group);
                $Row['SubjectList'] = array_merge($L_Group, $G_Group);
            }
        }
        return $this->Gateway010;
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

        $tempKey = false;
        foreach($this->Gateway010 as $Key => $Row10){
            if($Row10['ShortName'] == $Result['ShortName']
                && $Row10['FileDivision'] == $Result['FileDivision']){
                $tempKey = $Key;
                break;
            }
        }
        if($tempKey !== false){
            $this->Gateway010[$tempKey]['SubjectList'][] = array(
                'FileSubject'   => $Result['FileSubject'],
                'AppSubject'    => $Result['AppSubject'],
                'EntitySubject' => $Result['EntitySubject'],
                'SubjectGroup'  => $Result['SubjectGroup'],
            );
        }
    }

    /**
     * @param $Value
     *
     * @return Warning|string
     */
    protected function sanitizeSubjectGroup($Value)
    {
        if(preg_match('!^([\w\/]{1,}-[GLgl]-[\d])!', $Value, $Match)){
            if(isset($Match[1])){
                return $Match[1];
            } else {
                return new Warning(new WarningIcon().' Die Fachgruppe'.$Value.' kann aus dem Feld nicht ermittelt werden');
            }
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
        if(empty($Value)){
            return '';
        }
        if(preg_match('!^([\w\/]{1,})-([GLgl]-[\d])!', $Value, $Match)){
            return $Match[1];
        }
        return $Value;
    }

    /**
     * @param $Value
     *
     * @return TblSubject|false
     */
    protected function sanitizeEntitySubject($Value)
    {
        if(preg_match('!^([\w\/]{1,})-([GLgl]-[\d])!', $Value, $Match)){
//            return $Match[1];
            $SubjectAcronym = $Match[1];
            if(($tblSubject = Subject::useService()->getSubjectByAcronym($SubjectAcronym))){
                return $tblSubject;
            }
        }
        return false;
    }
}