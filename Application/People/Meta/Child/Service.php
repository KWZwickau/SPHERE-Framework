<?php

namespace SPHERE\Application\People\Meta\Child;

use SPHERE\Application\People\Meta\Child\Service\Data;
use SPHERE\Application\People\Meta\Child\Service\Entity\TblChild;
use SPHERE\Application\People\Meta\Child\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Child
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
     *
     * @param TblPerson $tblPerson
     * @param bool      $IsForced
     *
     * @return bool|TblChild
     */
    public function getChildByPerson(TblPerson $tblPerson, $IsForced = false)
    {
        return (new Data($this->getBinding()))->getChildByPerson($tblPerson, $IsForced);
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblChild
     */
    public function updateMetaService(TblPerson $tblPerson, $Meta)
    {
        if ($tblChild = $this->getChildByPerson($tblPerson)) {
            return (new Data($this->getBinding()))->updateChild(
                $tblChild,
                $Meta['AuthorizedToCollect']
            );
        } else {
            return (new Data($this->getBinding()))->createChild(
                $tblPerson,
                $Meta['AuthorizedToCollect']
            );
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblChild
     */
    public function insertChild(TblPerson $tblPerson, $AuthorizedToCollect)
    {

        return (new Data($this->getBinding()))->createChild($tblPerson, $AuthorizedToCollect);
    }
}