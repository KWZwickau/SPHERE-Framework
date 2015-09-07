<?php
namespace SPHERE\Application\People\Person\Service;

use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\DataCacheable;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Person\Service
 */
class Data extends DataCacheable
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

        $this->createSalutation('Herr', true);
        $this->createSalutation('Frau', true);
    }

    /**
     * @param string $Salutation
     * @param bool   $IsLocked
     *
     * @return TblSalutation
     */
    public function createSalutation($Salutation, $IsLocked = false)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblSalutation')->findOneBy(array(TblSalutation::ATTR_SALUTATION => $Salutation));
        if (null === $Entity) {
            $Entity = new TblSalutation($Salutation);
            $Entity->setIsLocked($IsLocked);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblSalutation $tblSalutation
     * @param string        $Title
     * @param string        $FirstName
     * @param string        $SecondName
     * @param string        $LastName
     *
     * @return TblPerson
     */
    public function createPerson(TblSalutation $tblSalutation, $Title, $FirstName, $SecondName, $LastName)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = new TblPerson();
        $Entity->setTblSalutation($tblSalutation);
        $Entity->setTitle($Title);
        $Entity->setFirstName($FirstName);
        $Entity->setSecondName($SecondName);
        $Entity->setLastName($LastName);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param TblPerson     $tblPerson
     * @param TblSalutation $tblSalutation
     * @param string        $Title
     * @param string        $FirstName
     * @param string        $SecondName
     * @param string        $LastName
     *
     * @return bool
     */
    public function updatePerson(
        TblPerson $tblPerson,
        TblSalutation $tblSalutation,
        $Title,
        $FirstName,
        $SecondName,
        $LastName
    ) {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblPerson $Entity */
        $Entity = $Manager->getEntityById('TblPerson', $tblPerson->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setTblSalutation($tblSalutation);
            $Entity->setTitle($Title);
            $Entity->setFirstName($FirstName);
            $Entity->setSecondName($SecondName);
            $Entity->setLastName($LastName);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @return bool|TblSalutation[]
     */
    public function getSalutationAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity('TblSalutation')->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @return bool|TblPerson[]
     */
    public function getPersonAll()
    {

        return $this->getCachedEntityListBy('PersonAll', array(), array($this, 'getPersonAllCacheable'));
    }

    /**
     * @return \SPHERE\System\Database\Fitting\Repository
     */
    public function getPersonRepository()
    {

        return $this->Connection->getEntityManager()->getEntity('TblPerson');
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblPerson
     */
    public function getPersonById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblPerson', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblSalutation
     */
    public function getSalutationById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblSalutation', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return array
     */
    protected function getPersonAllCacheable()
    {

        return $this->Connection->getEntityManager()->getEntity('TblPerson')->findAll();
    }
}
