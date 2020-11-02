<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use DateTime;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;
use SPHERE\Application\Transfer\Gateway\Converter\FieldSanitizer;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Text\Repository\Danger;

/**
 * Class StudentCourseGPU010
 * @package SPHERE\Application\Transfer\Indiware\Import
 */
class StudentCourseGPU010 extends AbstractConverter
{

    private $ResultList = array();
    private $ImportList = array();
    private $IsError = false;
    private $tblYear;

    /**
     * GPU010 constructor.
     *
     * @param string  $File GPU010.txt
     * @param TblYear $tblYear
     */
    public function __construct($File, TblYear $tblYear)
    {
        $this->loadFile($File);

        $this->tblYear = $tblYear;
        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        $this->setPointer(new FieldPointer('A', 'ShortName'));
        $this->setPointer(new FieldPointer('B', 'FileLastName'));
        $this->setPointer(new FieldPointer('H', 'FileFirstName'));


        $this->setPointer(new FieldPointer('J', 'FileDivision'));
        $this->setPointer(new FieldPointer('J', 'AppDivision'));
        $this->setPointer(new FieldPointer('J', 'DivisionId'));
        $this->setSanitizer(new FieldSanitizer('J', 'AppDivision', array($this, 'sanitizeDivision')));
        $this->setSanitizer(new FieldSanitizer('J', 'DivisionId', array($this, 'fetchDivision')));

        $this->setPointer(new FieldPointer('M', 'Birthday'));

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
        $tblPerson = false;
        $tblDivision = Division::useService()->getDivisionById($Result['DivisionId']);

        $Result['readableBirthday'] = '';
        $BirthDay = false;
        if(isset($Result['Birthday']) && ($BirthDay = $this->getDateTimeString($Result['Birthday']))){
            $Result['readableBirthday'] = $Result['Birthday'].' => '.$BirthDay;
        }

        if(isset($Result['FileFirstName']) && isset($Result['FileLastName']) && $BirthDay){
            if(($tblPerson = Person::useService()->getPersonByNameAndBirthday($Result['FileFirstName'],
                $Result['FileLastName'],
                $BirthDay))){
                $Result['AppPerson'] = $tblPerson->getLastFirstName();
            } else {
                $this->IsError = true;
                $Result['AppPerson'] = new Danger(new Ban().' Person nicht gefunden! (Name + Geburtsdatum)');
            }
        } else {
            $this->IsError = true;
            if(!$BirthDay){
                if(isset($Result['Birthday'])){
                    $Result['AppPerson'] = new Danger(new Ban().'Geburtsdatum nicht zuzuordnen ('.$Result['Birthday'].') FORMAT JJJJMMDD');
                } else {
                    $Result['AppPerson'] = new Danger(new Ban().'Geburtsdatum nicht Vorhanden FORMAT JJJJMMDD');
                }
            } else {
                $Result['AppPerson'] = new Danger(new Ban().' Name fehlt!');
            }
        }

        if(!$this->IsError){

            $ImportRow = array(
                'ShortName'      => $Result['ShortName'],
                'FileLastName'   => $Result['FileLastName'],
                'FileFirstName'  => $Result['FileFirstName'],
                'AppPerson'      => $Result['AppPerson'],
                'EntityPerson'   => $tblPerson,
                'FileDivision'   => $Result['FileDivision'],
                'AppDivision'    => $Result['AppDivision'],
                'EntityDivision' => $tblDivision,
                'DivisionId'     => $Result['DivisionId'],
            );
            $this->ImportList[] = $ImportRow;
        } else {
            $this->IsError = false;
            // only Errors in ResultList
            $this->ResultList[] = $Result;
        }
    }

    /**
     * @param $BirthdayString (JJJJMMDD)
     *
     * @return false
     */
    private function getDateTimeString($BirthdayString){

        if(strlen($BirthdayString) == 8){
            $CalculateDate = new DateTime();
            $CalculateDate->setDate(substr($BirthdayString, 0, 4), substr($BirthdayString, 4, 2), substr($BirthdayString, 6, 2));
            return $CalculateDate->format('d.m.Y');
        }
        return false;
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

        $tblDivisionList = array();
        // search with Level
        if (( $tblLevelList = Division::useService()->getLevelAllByName($LevelName) ) && $this->tblYear) {
            foreach ($tblLevelList as $tblLevel) {
                if (( $tblDivisionArray = Division::useService()->getDivisionByDivisionNameAndLevelAndYear($DivisionName, $tblLevel, $this->tblYear) )) {
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
        if ($tblLevel === null && $this->tblYear && $LevelName == '') {
            if (( $tblDivisionArray = Division::useService()->getDivisionByDivisionNameAndLevelAndYear($DivisionName, $tblLevel, $this->tblYear) )) {
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
        if (!$this->tblYear) {
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

            $tblDivisionList = array();
            // search with Level
            if (($tblLevelList = Division::useService()->getLevelAllByName($LevelName)) && $this->tblYear) {
                foreach ($tblLevelList as $tblLevel) {
                    if (($tblDivisionArray = Division::useService()->getDivisionByDivisionNameAndLevelAndYear($DivisionName,
                        $tblLevel, $this->tblYear))
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
            if ($tblLevel === null && $this->tblYear && $LevelName == '') {
                if (($tblDivisionArray = Division::useService()->getDivisionByDivisionNameAndLevelAndYear($DivisionName,
                    $tblLevel, $this->tblYear))
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
        } elseif (preg_match('!^(.*?)$!is', $Value, $Match)) {
            $LevelName = $Match[1];
            $DivisionName = null;
        }
    }
}