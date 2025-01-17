<?php
namespace SPHERE\Application\People\Meta\Common;

use SPHERE\Application\People\Meta\Common\Service\Data;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommon;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Common\Service\Entity\ViewPeopleMetaCommon;
use SPHERE\Application\People\Meta\Common\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Common
 */
class Service extends AbstractService
{

    /**
     * @return false|ViewPeopleMetaCommon[]
     */
    public function viewPeopleMetaCommon()
    {

        return (new Data($this->getBinding()))->viewPeopleMetaCommon();
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param string $Name
     *
     * @return TblCommonGender
     */
    public function createCommonGender($Name)
    {
        return (new Data($this->getBinding()))->createCommonGender($Name);
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblCommon
     */
    public function updateMetaService(TblPerson $tblPerson, $Meta)
    {
        $tblCommon = $this->getCommonByPerson($tblPerson, true);
        // bei deativiertem Feld erforderlich, vorerst anders umgesetzt
//        if(!isset($Meta['Information']['AuthorizedToCollect'])){
//            $Meta['Information']['AuthorizedToCollect'] = '';
//        }
        if ($tblCommon) {
            (new Data($this->getBinding()))->updateCommonBirthDates(
                $tblCommon->getTblCommonBirthDates(),
                $Meta['BirthDates']['Birthday'],
                $Meta['BirthDates']['Birthplace'],
                ($tblCommonGender = $this->getCommonGenderById($Meta['BirthDates']['Gender'])) ? $tblCommonGender : null
            );
            (new Data($this->getBinding()))->updateCommonInformation(
                $tblCommon->getTblCommonInformation(),
                $Meta['Information']['Nationality'],
                $Meta['Information']['Denomination'],
                $Meta['Information']['IsAssistance'],
                $Meta['Information']['AssistanceActivity']
            );

            return (new Data($this->getBinding()))->updateCommon(
                $tblCommon,
                $Meta['Remark']
            );
        } else {
            $tblCommonBirthDates = (new Data($this->getBinding()))->createCommonBirthDates(
                $Meta['BirthDates']['Birthday'],
                $Meta['BirthDates']['Birthplace'],
                ($tblCommonGender = $this->getCommonGenderById($Meta['BirthDates']['Gender'])) ? $tblCommonGender : null
            );
            $tblCommonInformation = (new Data($this->getBinding()))->createCommonInformation(
                $Meta['Information']['Nationality'],
                $Meta['Information']['Denomination'],
                $Meta['Information']['IsAssistance'],
                $Meta['Information']['AssistanceActivity']
            );

            return (new Data($this->getBinding()))->createCommon(
                $tblPerson,
                $tblCommonBirthDates,
                $tblCommonInformation,
                $Meta['Remark']
            );
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool      $IsForced
     *
     * @return bool|TblCommon
     */
    public function getCommonByPerson(TblPerson $tblPerson, $IsForced = false)
    {

        return (new Data($this->getBinding()))->getCommonByPerson($tblPerson, $IsForced);
    }

    /**
     * @param TblPerson            $tblPerson
     * @param string               $Birthday
     * @param string               $Birthplace
     * @param TblCommonGender|null $tblCommonGender
     * @param string               $Nationality
     * @param string               $Denomination
     * @param int                  $IsAssistance
     * @param string               $AssistanceActivity
     * @param string               $Remark
     * @param string               $ContactNumber
     */
    public function insertMeta(
        TblPerson $tblPerson,
        $Birthday,
        $Birthplace,
        TblCommonGender $tblCommonGender = null,
        $Nationality,
        $Denomination,
        $IsAssistance,
        $AssistanceActivity,
        $Remark,
        $ContactNumber = ''
    ) {

        $tblCommonBirthDates = (new Data($this->getBinding()))->createCommonBirthDates(
            $Birthday,
            $Birthplace,
            $tblCommonGender
        );
        $tblCommonInformation = (new Data($this->getBinding()))->createCommonInformation(
            $Nationality,
            $Denomination,
            $IsAssistance,
            $AssistanceActivity,
            $ContactNumber
        );
        (new Data($this->getBinding()))->createCommon(
            $tblPerson,
            $tblCommonBirthDates,
            $tblCommonInformation,
            $Remark
        );
    }

    /**
     * @param TblCommon $tblCommon
     * @param string $Remark
     */
    public function insertUpdateCommon($tblCommon, $Remark)
    {

        (new Data($this->getBinding()))->updateCommon($tblCommon, $Remark);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCommon
     */
    public function getCommonById($Id)
    {

        return (new Data($this->getBinding()))->getCommonById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCommonGender
     */
    public function getCommonGenderById($Id)
    {

        return (new Data($this->getBinding()))->getCommonGenderById($Id);
    }

    /**
     * @return bool|TblCommonGender[]
     */
    public function getCommonGenderAll(bool $isPreSort = false)
    {

        if($isPreSort){
            $tblCommonGenderAll = (new Data($this->getBinding()))->getCommonGenderAll();
            $returnList = array();
            foreach($tblCommonGenderAll as $tblCommonGender){
                if($tblCommonGender->getName() == 'Weiblich'){
                    $returnList[0] = $tblCommonGender;
                } elseif($tblCommonGender->getName() == 'Männlich'){
                    $returnList[1] = $tblCommonGender;
                } elseif($tblCommonGender->getName() == 'Divers'){
                    $returnList[2] = $tblCommonGender;
                } elseif($tblCommonGender->getName() == 'Ohne Angabe'){
                    $returnList[3] = $tblCommonGender;
                } else {
                    $returnList[$tblCommonGender->getId()] = $tblCommonGender;
                }
            }
            ksort($returnList);
            return $returnList;
        }

        return (new Data($this->getBinding()))->getCommonGenderAll();
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCommonBirthDates
     */
    public function getCommonBirthDatesById($Id)
    {

        return (new Data($this->getBinding()))->getCommonBirthDatesById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCommonInformation
     */
    public function getCommonInformationById($Id)
    {

        return (new Data($this->getBinding()))->getCommonInformationById($Id);
    }

    /**
     * @return bool|TblCommonInformation[]
     */
    public function getCommonInformationAll()
    {

        return (new Data($this->getBinding()))->getCommonInformationAll();
    }

    /**
     * @return bool|TblCommonBirthDates[]
     */
    public function getCommonBirthDatesAll()
    {

        return (new Data($this->getBinding()))->getCommonBirthDatesAll();
    }

    /**
     * @return bool|ViewPeopleMetaCommon[]
     */
    public function getViewPeopleMetaCommonAll()
    {

        return (new Data($this->getBinding()))->getViewPeopleMetaCommonAll();
    }

    /**
     * @param TblCommon $tblCommon
     * @param string $Remark
     *
     * @return bool
     */
    public function updateCommon(TblCommon $tblCommon, $Remark){

        return (new Data($this->getBinding()))->updateCommon( $tblCommon, $Remark );
    }

    /**
     * @param TblCommonBirthDates  $tblCommonBirthDates
     * @param string               $Birthday
     * @param string               $Birthplace
     * @param TblCommonGender|null $tblCommonGender
     *
     * @return bool
     */
    public function updateCommonBirthDates(
        TblCommonBirthDates $tblCommonBirthDates,
        $Birthday,
        $Birthplace,
        TblCommonGender $tblCommonGender = null
    ) {
        return (new Data($this->getBinding()))->updateCommonBirthDates( $tblCommonBirthDates, $Birthday, $Birthplace, $tblCommonGender );
    }

    /**
     * @param TblCommonInformation $tblCommonInformation
     * @param string               $Nationality
     * @param string               $Denomination
     * @param int                  $IsAssistance
     * @param string               $AssistanceActivity
     *
     * @return bool
     */
    public function updateCommonInformation(
        TblCommonInformation $tblCommonInformation,
        $Nationality,
        $Denomination,
        $IsAssistance,
        $AssistanceActivity
    ) {
        return (new Data($this->getBinding()))->updateCommonInformation($tblCommonInformation, $Nationality, $Denomination, $IsAssistance, $AssistanceActivity);
    }

    /**
     * @param TblCommon $tblCommon
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyCommon(TblCommon $tblCommon, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->destroyCommon($tblCommon, $IsSoftRemove);
    }

    /**
     * @param $Name
     *
     * @return false|TblCommonGender
     */
    public function getCommonGenderByName($Name)
    {

        return (new Data($this->getBinding()))->getCommonGenderByName($Name);
    }

    /**
     * @param TblCommon $tblCommon
     *
     * @return bool
     */
    public function restoreCommon(TblCommon $tblCommon)
    {

        return (new Data($this->getBinding()))->restoreCommon($tblCommon);
    }
}
