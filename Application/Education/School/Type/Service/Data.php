<?php
namespace SPHERE\Application\Education\School\Type\Service;

use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\School\Type\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

//        $this->createType('Berufliches Gymnasium', 'BGy');
//        $this->createType('Berufsfachschule', 'BFS');
//        $this->createType('Berufsschule', 'BS');
//        $this->createType('Fachoberschule', 'FOS');
//        $this->createType('Fachschule', 'FS');
//        $this->createType('Grundschule', 'GS');
//        $this->createType('Gymnasium', 'Gy');
//        $this->createType('Mittelschule / Oberschule', 'OS');
//        $this->createType('Förderschule', 'FöS');

        // todo remove nach dem es einmal auf allen Mandanten ausgeführt wurde, danach wieder die normalen von oben verwenden
        if (($tblType = $this->createType('Berufliches Gymnasium', 'BGy'))) {
            $this->updateTypeShortName($tblType, 'BGy');
        }
        if (($tblType = $this->createType('Berufsfachschule', 'BFS'))) {
            $this->updateTypeShortName($tblType, 'BFS');
        }
        if (($tblType = $this->createType('Berufsschule', 'BS'))) {
            $this->updateTypeShortName($tblType, 'BS');
        }
        if (($tblType = $this->createType('Fachoberschule', 'FOS'))) {
            $this->updateTypeShortName($tblType, 'FOS');
        }
        if (($tblType = $this->createType('Fachschule', 'FS'))) {
            $this->updateTypeShortName($tblType, 'FS');
        }
        if (($tblType = $this->createType('Grundschule', 'GS'))) {
            $this->updateTypeShortName($tblType, 'GS');
        }
        if (($tblType = $this->createType('Gymnasium', 'Gy'))) {
            $this->updateTypeShortName($tblType, 'Gy');
        }
        if (($tblType = $this->createType('Mittelschule / Oberschule', 'OS'))) {
            $this->updateTypeShortName($tblType, 'OS');
        }

        // zusätzlich rename Förderschule
        if (($tblType = $this->getTypeByName('allgemein bildende Förderschule'))) {
            $this->updateType($tblType, 'Förderschule', 'FöS', '');
        } else {
            $this->createType('Förderschule', 'FöS');
        }
    }

    /**
     * @param $Name
     * @param $ShortName
     * @param string $Description
     *
     * @return null|object|TblType
     */
    public function createType($Name, $ShortName, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblType')
            ->findOneBy(array(TblType::ATTR_NAME => $Name));

        if (null === $Entity) {
            $Entity = new TblType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setShortName($ShortName);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblType $tblType
     *
     * @param $ShortName
     */
    public function updateTypeShortName(TblType $tblType, $ShortName)
    {
        $this->updateType(
            $tblType,
            $tblType->getName(),
            $ShortName,
            $tblType->getDescription()
        );
    }

    /**
     * @param TblType $tblType
     * @param $Name
     * @param $ShortName
     * @param $Description
     *
     * @return bool
     */
    public function updateType(TblType $tblType, $Name, $ShortName, $Description)
    {
        $Manager = $this->getEntityManager();

        /** @var TblType $Entity */
        $Entity = $Manager->getEntityById('TblType', $tblType->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setShortName($ShortName);
            $Entity->setDescription($Description);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return bool|TblType
     */
    public function getTypeById($Id)
    {
        /** @var TblType $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblType', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblType
     */
    public function getTypeByName($Name)
    {
        /** @var TblType $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblType')
            ->findOneBy(array(TblType::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__,$this->getConnection()->getEntityManager(),'TblType',array('Name'=>self::ORDER_ASC));
    }
}
