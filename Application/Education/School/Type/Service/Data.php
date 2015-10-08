<?php
namespace SPHERE\Application\Education\School\Type\Service;

use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\School\Type\Service
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

        $this->createType('Berufliches Gymnasium', '');
        $this->createType('Berufsfachschule', '');
        $this->createType('Berufsschule', '');
        $this->createType('Fachoberschule', '');
        $this->createType('Fachschule', '');
        $this->createType('Grundschule', '');
        $this->createType('Gymnasium', '');
        $this->createType('Mittelschule / Oberschule', '');
        $this->createType('allgemein bildende Förderschule', '');
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return null|object|TblType
     */
    public function createType($Name, $Description = '')
    {

        $Manager = $this->Connection->getEntityManager();

        $Entity = $Manager->getEntity('TblType')
            ->findOneBy(array(TblType::ATTR_NAME => $Name));

        if (null === $Entity) {
            $Entity = new TblType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return bool|TblType
     */
    public function getTypeById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblType', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblType
     */
    public function getTypeByName($Name)
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblType')
            ->findOneBy(array(TblType::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity('TblType')->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }
}
