<?php
namespace SPHERE\Application\People\Person\Service;

use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Cache\Cache;
use SPHERE\System\Cache\IApiInterface;
use SPHERE\System\Cache\Type\Memcached;
use SPHERE\System\Cache\Type\Memory;
use SPHERE\System\Database\Fitting\Binding;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Person\Service
 */
class Data
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

        /** @var IApiInterface $Cache */
        $Cache = (new Cache(new Memory()))->getCache();
        if (!( $Entity = $Cache->getValue(__METHOD__) )) {
            $EntityList = $this->Connection->getEntityManager()->getEntity('TblSalutation')->findAll();
            $Cache->setValue(__METHOD__, ( empty( $EntityList ) ? false : $EntityList ), 300);
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @return bool|TblPerson[]
     */
    public function getPersonAll()
    {

        /** @var IApiInterface $Cache */
        $Cache = (new Cache(new Memory()))->getCache();
        if (!( $Entity = $Cache->getValue(__METHOD__) )) {
            $EntityList = $this->Connection->getEntityManager()->getEntity('TblPerson')->findAll();
            $Cache->setValue(__METHOD__, ( empty( $EntityList ) ? false : $EntityList ), 300);
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblPerson
     */
    public function getPersonById($Id)
    {

        /** @var IApiInterface $Cache */
        $Cache = (new Cache(new Memcached()))->getCache();
        if (!( $Entity = $Cache->getValue(__METHOD__.'::'.$Id) )) {
            $Entity = $this->Connection->getEntityManager()->getEntityById('TblPerson', $Id);
            $Cache->setValue(__METHOD__.'::'.$Id, ( null === $Entity ? false : $Entity ), 500);
        }
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblSalutation
     */
    public function getSalutationById($Id)
    {

        /** @var IApiInterface $Cache */
        $Cache = (new Cache(new Memcached()))->getCache();
        if (!( $Entity = $Cache->getValue(__METHOD__.'::'.$Id) )) {
            $Entity = $this->Connection->getEntityManager()->getEntityById('TblSalutation', $Id);
            $Cache->setValue(__METHOD__.'::'.$Id, ( null === $Entity ? false : $Entity ), 500);
        }
        return ( null === $Entity ? false : $Entity );
    }

}
