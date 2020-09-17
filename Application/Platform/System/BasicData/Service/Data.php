<?php

namespace SPHERE\Application\Platform\System\BasicData\Service;

use SPHERE\Application\Platform\System\BasicData\Service\Entity\TblHoliday;
use SPHERE\Application\Platform\System\BasicData\Service\Entity\TblHolidayType;
use SPHERE\Application\Platform\System\BasicData\Service\Entity\TblState;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Platform\System\BasicData\Service
 */
class Data extends AbstractData
{
    /**
     * @return void
     */
    public function setupDatabaseContent()
    {
        $this->createState('Baden-Württemberg');
        $this->createState('Bremen');
        $this->createState('Niedersachsen');
        $this->createState('Sachsen');
        $this->createState('Bayern');
        $this->createState('Hamburg');
        $this->createState('Nordrhein-Westfalen');
        $this->createState('Sachsen-Anhalt');
        $this->createState('Berlin');
        $this->createState('Hessen');
        $this->createState('Rheinland-Pfalz');
        $this->createState('Schleswig-Holstein');
        $this->createState('Brandenburg');
        $this->createState('Mecklenburg-Vorpommern');
        $this->createState('Saarland');
        $this->createState('Thüringen');

        $this->createHolidayType('Ferien', 'HOLIDAY');
        $this->createHolidayType('Feiertag', 'PUBLIC_HOLIDAY');
        $this->createHolidayType('Unterrichtsfreier Tag', 'SCHOOL_FREE_DAY');
    }

    /**
     * @param $Name
     *
     * @return TblState
     */
    public function createState($Name)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblState')->findOneBy(array(
            TblState::ATTR_NAME => $Name,
        ));
        if (null === $Entity) {
            $Entity = new TblState($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblState
     */
    public function getStateById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblState', $Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblState
     */
    public function getStateByName($Name)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblState', array(
            TblState::ATTR_NAME => $Name,
        ));
    }

    /**
     * @param $Name
     * @param $Identifier
     *
     * @return TblHolidayType
     */
    public function createHolidayType($Name, $Identifier)
    {
        $Manager = $this->getEntityManager();

        $Entity = $Manager->getEntity('TblHolidayType')->findOneBy(array(
            TblHolidayType::ATTR_NAME => $Name,
            TblHolidayType::ATTR_IDENTIFIER => strtoupper($Identifier)
        ));

        if (null === $Entity) {
            $Entity = new TblHolidayType();
            $Entity->setName($Name);
            $Entity->setIdentifier(strtoupper($Identifier));

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return false|TblHolidayType
     */
    public function getHolidayTypeById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblHolidayType', $Id);
    }

    /**
     * @param $Identifier
     *
     * @return false|TblHolidayType
     */
    public function getHolidayTypeByIdentifier($Identifier)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblHolidayType',
            array(
                TblHolidayType::ATTR_IDENTIFIER => $Identifier
            )
        );
    }

    /**
     * @param $Name
     *
     * @return false|TblHolidayType
     */
    public function getHolidayTypeByName($Name)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblHolidayType',
            array(
                TblHolidayType::ATTR_NAME => $Name
            )
        );
    }

    /**
     * @return false|TblHolidayType[]
     */
    public function getHolidayTypeAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblHolidayType');
    }

    /**
     * @param TblHolidayType $tblHolidayType
     * @param $fromDate
     * @param $toDate
     * @param TblState|null $tblState
     *
     * @return false|TblHoliday
     */
    public function getHolidayBy(TblHolidayType $tblHolidayType, $fromDate, $toDate, TblState $tblState = null)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblHoliday', array(
            TblHoliday::ATTR_TBL_HOLIDAY_TYPE => $tblHolidayType->getId(),
            TblHoliday::ATTR_FROM_DATE => $fromDate,
            TblHoliday::ATTR_TO_DATE => $toDate,
            TblHoliday::ATTR_TBL_STATE => $tblState ? $tblState->getId() : null
        ));
    }

    /**
     * @return false|TblHoliday[]
     */
    public function getHolidayAll() {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblHoliday', array(TblHoliday::ATTR_FROM_DATE => self::ORDER_DESC));
    }

    /**
     * @param TblHolidayType $tblHolidayType
     * @param $fromDate
     * @param $toDate
     * @param $name
     * @param TblState|null $tblState
     *
     * @return object|TblHoliday|null
     */
    public function createHoliday(TblHolidayType $tblHolidayType, $fromDate, $toDate, $name, TblState $tblState = null)
    {
        $Manager = $this->getConnection()->getEntityManager();
//        $Entity = $Manager->getEntity('TblState')->findOneBy(array(
//            TblHoliday::ATTR_TBL_HOLIDAY_TYPE => $tblHolidayType->getId(),
//            TblHoliday::ATTR_FROM_DATE => $fromDate,
//            TblHoliday::ATTR_TO_DATE => $toDate,
//            TblHoliday::ATTR_TBL_STATE => $tblState ? $tblState->getId() : null
//        ));
//        if (null === $Entity) {
            $Entity = new TblHoliday();
            $Entity->setTblHolidayType($tblHolidayType);
            $Entity->setFromDate($fromDate);
            $Entity->setToDate($toDate);
            $Entity->setName($name);
            $Entity->setTblState($tblState);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
//        }

        return $Entity;
    }
}