<?php
namespace SPHERE\Application\People\Person\Service;

use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Person\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createSalutation('Herr', true);
        $this->createSalutation('Frau', true);
        $this->createSalutation('Schüler', true);

    }

    /**
     * @param string $Salutation
     * @param bool $IsLocked
     *
     * @return TblSalutation
     */
    public function createSalutation($Salutation, $IsLocked = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblSalutation')->findOneBy(array(TblSalutation::ATTR_SALUTATION => $Salutation));
        if (null === $Entity) {
            $Entity = new TblSalutation($Salutation);
            $Entity->setIsLocked($IsLocked);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param $Salutation
     * @param string $Title
     * @param string $FirstName
     * @param string $SecondName
     * @param string $LastName
     * @param string $BirthName
     *
     * @return TblPerson
     */
    public function createPerson($Salutation, $Title, $FirstName, $SecondName, $LastName, $BirthName = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblPerson();
        $Entity->setTblSalutation($Salutation !== null ? $this->getSalutationById($Salutation) : $Salutation);
        $Entity->setTitle($Title);
        $Entity->setFirstName($FirstName);
        $Entity->setSecondName($SecondName);
        $Entity->setLastName($LastName);
        $Entity->setBirthName($BirthName);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblSalutation
     */
    public function getSalutationById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSalutation', $Id);
//        return $this->getConnection()->getEntityManager()->getEntityById('TblSalutation', $Id);
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Salutation
     * @param string $Title
     * @param string $FirstName
     * @param string $SecondName
     * @param string $LastName
     * @param string $BirthName
     *
     * @return bool
     */
    public function updatePerson(
        TblPerson $tblPerson,
        $Salutation,
        $Title,
        $FirstName,
        $SecondName,
        $LastName,
        $BirthName = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblPerson $Entity */
        $Entity = $Manager->getEntityById('TblPerson', $tblPerson->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setTblSalutation($Salutation !== null ? $this->getSalutationById($Salutation) : $Salutation);
            $Entity->setTitle($Title);
            $Entity->setFirstName($FirstName);
            $Entity->setSecondName($SecondName);
            $Entity->setLastName($LastName);
            $Entity->setBirthName($BirthName);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @return bool|TblSalutation[]
     */
    public function getSalutationAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSalutation');
    }

    /**
     * @return bool|TblPerson[]
     */
    public function getPersonAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPerson');
    }

    /**
     * @param $FirstName
     * @param $LastName
     *
     * @return bool|TblPerson[]
     */
    public function getPersonAllByFirstNameAndLastName($FirstName, $LastName)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblPerson')->findBy(array(
            TblPerson::ATTR_FIRST_NAME => $FirstName,
            TblPerson::ATTR_LAST_NAME => $LastName
        ));

        return empty($EntityList) ? false : $EntityList;
    }

    /**
     * @return int
     */
    public function countPersonAll()
    {

        return $this->getConnection()->getEntityManager()->getEntity('TblPerson')->count();
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblPerson
     */
    public function getPersonById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPerson', $Id);
//        return $this->getConnection()->getEntityManager()->getEntityById('TblPerson', $Id);
    }
}
