<?php
namespace SPHERE\Application\People\Meta\Custody;

use SPHERE\Application\People\Meta\Custody\Service\Data;
use SPHERE\Application\People\Meta\Custody\Service\Entity\TblCustody;
use SPHERE\Application\People\Meta\Custody\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Custody
 */
class Service extends AbstractService
{

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
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblCustody
     */
    public function updateMetaService(TblPerson $tblPerson, $Meta)
    {
        if (($tblCustody = $this->getCustodyByPerson($tblPerson))) {
            return (new Data($this->getBinding()))->updateCustody(
                $tblCustody,
                $Meta['Remark'],
                $Meta['Occupation'],
                $Meta['Employment']
            );
        } else {
            return (new Data($this->getBinding()))->createCustody(
                $tblPerson,
                $Meta['Remark'],
                $Meta['Occupation'],
                $Meta['Employment']
            );
        }
    }

    /**
     *
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblCustody
     */
    public function getCustodyByPerson(TblPerson $tblPerson, $isForced = false)
    {

        return (new Data($this->getBinding()))->getCustodyByPerson($tblPerson, $isForced);
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $Occupation
     * @param           $Employment
     * @param           $Remark
     */
    public function insertMeta(TblPerson $tblPerson, $Occupation, $Employment, $Remark)
    {

        (new Data($this->getBinding()))->createCustody($tblPerson, $Remark, $Occupation, $Employment);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCustody
     */
    public function getCustodyById($Id)
    {

        return (new Data($this->getBinding()))->getCustodyById($Id);
    }

    /**
     * @param TblCustody $tblCustody
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyCustody(TblCustody $tblCustody, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->destroyCustody($tblCustody, $IsSoftRemove);
    }

    /**
     * @param TblCustody $tblCustody
     *
     * @return bool
     */
    public function restoreCustody(TblCustody $tblCustody)
    {

        return (new Data($this->getBinding()))->restoreCustody($tblCustody);
    }
}
