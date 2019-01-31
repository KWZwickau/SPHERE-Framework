<?php
namespace SPHERE\Application\People\Meta\Club;

use SPHERE\Application\People\Meta\Club\Service\Data;
use SPHERE\Application\People\Meta\Club\Service\Entity\TblClub;
use SPHERE\Application\People\Meta\Club\Service\Entity\ViewPeopleMetaClub;
use SPHERE\Application\People\Meta\Club\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{

    /**
     * @return false|ViewPeopleMetaClub[]
     */
    public function viewPeopleMetaClub()
    {

        return ( new Data($this->getBinding()) )->viewPeopleMetaClub();
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblClub
     */
    public function updateMetaService(TblPerson $tblPerson, $Meta)
    {
        if ($tblClub = $this->getClubByPerson($tblPerson)) {
            return (new Data($this->getBinding()))->updateClub(
                $tblClub,
                $Meta['Identifier'],
                $Meta['EntryDate'],
                $Meta['ExitDate'],
                $Meta['Remark']
            );
        } else {
            return (new Data($this->getBinding()))->createClub(
                $tblPerson,
                $Meta['Identifier'],
                $Meta['EntryDate'],
                $Meta['ExitDate'],
                $Meta['Remark']
            );
        }
    }

    /**
     *
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblClub
     */
    public function getClubByPerson(TblPerson $tblPerson, $isForced = false)
    {

        return (new Data($this->getBinding()))->getClubByPerson($tblPerson, $isForced);
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Identifier
     * @param string $EntryDate
     * @param string $ExitDate
     * @param string $Remark
     */
    public function insertMeta(
        TblPerson $tblPerson,
        $Identifier,
        $EntryDate = '',
        $ExitDate = '',
        $Remark = ''
    ) {

        $tblClub = $this->getClubByPerson($tblPerson);
        if ($tblClub) {
            (new Data($this->getBinding()))->updateClub(
                $tblClub,
                $Identifier,
                $EntryDate,
                $ExitDate,
                $Remark
            );
        } else {
            (new Data($this->getBinding()))->createClub(
                $tblPerson,
                $Identifier,
                $EntryDate,
                $ExitDate,
                $Remark
            );
        }
    }

    /**
     * @param TblClub $tblClub
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyClub(TblClub $tblClub, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->destroyClub($tblClub, $IsSoftRemove);
    }

    /**
     * @param TblClub $tblClub
     *
     * @return bool
     */
    public function restoreClub(TblClub $tblClub)
    {

        return (new Data($this->getBinding()))->restoreClub($tblClub);
    }
}