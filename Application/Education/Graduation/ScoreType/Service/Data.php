<?php
namespace SPHERE\Application\Education\Graduation\ScoreType\Service;

use SPHERE\Application\Education\Graduation\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;

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

        $Manager = $this->Connection->getEntityManager();

        $Entity = $Manager->getEntity('TblScoreType')
            ->findOneBy(array(TblScoreType::ATTR_NAME => $Name));

        if (null === $Entity) {
            $Entity = new TblScoreType();
            $Entity->setName($Name);
            $Entity->setShort($Short);

            var_dump($Entity);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
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

        $Manager = $this->Connection->getEntityManager();

        $Entity = $Manager->getEntity('TblScoreType')->findOneBy(
            array(
                'Id' => $tblScoreType->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->Connection->getDatabase(),
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

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblScoreType', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblScoreType
     */
    public function getScoreTypeByName($Name)
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblScoreType')
            ->findOneBy(array(TblScoreType::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblScoreType[]
     */
    public function getScoreTypeAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity('TblScoreType')->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }
}
