<?php
namespace SPHERE\Application\Education\Graduation\ScoreType\Service;

use SPHERE\Application\Education\Graduation\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Graduation\ScoreType\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createScoreType('Besondere Leistungsfeststellung', 'BLF');
        $this->createScoreType('Hausaufgaben', 'HA');
        $this->createScoreType('Klassenarbeit', 'KA');
        $this->createScoreType('Kurzkontrolle', 'KK');
        $this->createScoreType('Klausur', 'KL');
        $this->createScoreType('Komplexe Leistung', 'KoL');
        $this->createScoreType('Leistungskontrolle', 'LK');
        $this->createScoreType('Mitarbeit', 'MA');
        $this->createScoreType('Mündlich', 'MDL');
        $this->createScoreType('Mündliche Leitungskontrolle', 'MLK');
        $this->createScoreType('Projekt', 'Prj');
        $this->createScoreType('Tägliche Übung', 'TÜ');
    }

    /**
     * @param $Name
     * @param $Short
     *
     * @return null|object|TblScoreType
     */
    public function createScoreType($Name, $Short)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblScoreType')
            ->findOneBy(array(TblScoreType::ATTR_NAME => $Name));

        if (null === $Entity) {
            $Entity = new TblScoreType();
            $Entity->setName($Name);
            $Entity->setShort($Short);

            var_dump($Entity);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblScoreType $tblScoreType
     *
     * @return bool
     */
    public function removeScoreTypeByEntity(TblScoreType $tblScoreType)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblScoreType')->findOneBy(
            array(
                'Id' => $tblScoreType->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreType
     */
    public function getScoreTypeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblScoreType', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblScoreType
     */
    public function getScoreTypeByName($Name)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblScoreType')
            ->findOneBy(array(TblScoreType::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblScoreType[]
     */
    public function getScoreTypeAll()
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblScoreType')->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }
}
