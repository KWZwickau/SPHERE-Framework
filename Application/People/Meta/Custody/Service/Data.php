<?php
namespace SPHERE\Application\People\Meta\Custody\Service;

use SPHERE\Application\People\Meta\Custody\Service\Entity\TblCustody;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Cacheable;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Meta\Custody\Service
 */
class Data extends Cacheable
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    function __construct(Binding $Connection)
    {

        $this->Connection = $Connection;
    }

    public function setupDatabaseContent()
    {

    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $Remark
     * @param string    $Occupation
     * @param string    $Employment
     *
     * @return TblCustody
     */
    public function createCustody(
        TblPerson $tblPerson,
        $Remark,
        $Occupation,
        $Employment
    ) {

        $Manager = $this->Connection->getEntityManager();

        $Entity = new TblCustody();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setRemark($Remark);
        $Entity->setOccupation($Occupation);
        $Entity->setEmployment($Employment);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblCustody $tblCustody
     * @param string     $Remark
     * @param string     $Occupation
     * @param string     $Employment
     *
     * @return TblCustody
     */
    public function updateCustody(
        TblCustody $tblCustody,
        $Remark,
        $Occupation,
        $Employment
    ) {

        $Manager = $this->Connection->getEntityManager();
        /** @var null|TblCustody $Entity */
        $Entity = $Manager->getEntityById('TblCustody', $tblCustody->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setRemark($Remark);
            $Entity->setOccupation($Occupation);
            $Entity->setEmployment($Employment);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblCustody
     */
    public function getCustodyByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->Connection->getEntityManager(), 'TblCustody', array(
            TblCustody::SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCustody
     */
    public function getCustodyById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblCustody', $Id);
    }
}
