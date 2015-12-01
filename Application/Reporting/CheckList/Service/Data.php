<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.12.2015
 * Time: 10:39
 */

namespace SPHERE\Application\Reporting\CheckList\Service;

use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblElementType;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblListElementList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblListType;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Reporting\CheckList\Service
 */
class Data extends AbstractData
{
    public function setupDatabaseContent()
    {

        $this->createListType('Personen', 'PERSON');
        $this->createListType('Firmen', 'COMPANY');

        $this->createElementType('CheckBox', 'CHECKBOX');
        $this->createElementType('Datum', 'DATE');
        $this->createElementType('Text', 'TEXT');
    }

    /**
     * @param $Id
     *
     * @return bool|TblList
     */
    public function getListById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblList', $Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblListType
     */
    public function getListTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblListType', $Id);
    }

    /**
     * @param $Identifier
     *
     * @return bool|TblListType
     */
    public function getListTypeByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblListType',
            array(
                TblListType::ATTR_IDENTIFIER => $Identifier
            ));
    }

    /**
     * @return false|TblListType[]
     */
    public function getListTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblListType');
    }

    /**
     * @param $Id
     *
     * @return bool|TblListElementList
     */
    public function getListElementListById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblListElementList', $Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblElementType
     */
    public function getElementTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblElementType', $Id);
    }

    /**
     * @param $Identifier
     *
     * @return bool|TblElementType
     */
    public function getElementTypeByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblElementType',
            array(
                TblElementType::ATTR_IDENTIFIER => $Identifier
            ));
    }

    /**
     * @return false|TblElementType[]
     */
    public function getElementTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblElementType');
    }

    /**
     * @param $Name
     * @param $Identifier
     *
     * @return TblListType
     */
    public function createListType(
        $Name,
        $Identifier
    ) {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblListType')
            ->findOneBy(array(
                TblListType::ATTR_NAME => $Name,
                TblListType::ATTR_IDENTIFIER => $Identifier
            ));

        if (null === $Entity) {
            $Entity = new TblListType();
            $Entity->setName($Name);
            $Entity->setIdentifier($Identifier);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param $Name
     * @param $Identifier
     *
     * @return TblElementType
     */
    public function createElementType(
        $Name,
        $Identifier
    ) {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblElementType')
            ->findOneBy(array(
                TblElementType::ATTR_NAME => $Name,
                TblElementType::ATTR_IDENTIFIER => $Identifier
            ));

        if (null === $Entity) {
            $Entity = new TblElementType();
            $Entity->setName($Name);
            $Entity->setIdentifier($Identifier);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }
}