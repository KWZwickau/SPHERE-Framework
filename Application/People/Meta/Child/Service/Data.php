<?php

namespace SPHERE\Application\People\Meta\Child\Service;

use SPHERE\Application\People\Meta\Child\Service\Entity\TblChild;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Meta\Child\Service
 */
class Data extends AbstractData
{
    public function setupDatabaseContent()
    {

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
        if($IsForced){
            return $this->getForceEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblChild', array(
                TblChild::SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        } else {
            return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblChild', array(
                TblChild::SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $AuthorizedToCollect
     * 
     * @return TblChild
     */
    public function createChild(TblPerson $tblPerson, $AuthorizedToCollect)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblChild')->findOneBy(array(TblChild::SERVICE_TBL_PERSON => $tblPerson->getId()));
        if (null === $Entity) {
            $Entity = new TblChild();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setAuthorizedToCollect($AuthorizedToCollect);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        
        return $Entity;
    }

    /**
     * @param TblChild $tblChild
     * @param string $AuthorizedToCollect
     *
     * @return bool
     */
    public function updateChild(
        TblChild $tblChild,
        $AuthorizedToCollect
    ) {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblChild $Entity */
        $Entity = $Manager->getEntityById('TblChild', $tblChild->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setAuthorizedToCollect($AuthorizedToCollect);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }
}