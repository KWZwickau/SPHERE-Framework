<?php

namespace SPHERE\Application\Platform\System\BasicData\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\System\BasicData\BasicData;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblHoliday")
 * @Cache(usage="READ_ONLY")
 */
class TblHoliday extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_FROM_DATE = 'FromDate';
    const ATTR_TO_DATE = 'ToDate';
    const ATTR_TBL_HOLIDAY_TYPE = 'tblHolidayType';
    const ATTR_TBL_STATE = 'tblState';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="datetime")
     */
    protected $FromDate;

    /**
     * @Column(type="datetime")
     */
    protected $ToDate;

    /**
     * @Column(type="bigint")
     */
    protected $tblHolidayType;

    /**
     * @Column(type="bigint")
     */
    protected $tblState;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {
        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getFromDate()
    {
        if (null === $this->FromDate) {
            return false;
        }
        /** @var \DateTime $Date */
        $Date = $this->FromDate;
        if ($Date instanceof \DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @return false|\DateTime
     */
    public function getFromDateTime()
    {
        if (null === $this->FromDate) {
            return false;
        }

        return $this->FromDate;
    }

    /**
     * @param null|\DateTime $Date
     */
    public function setFromDate(\DateTime $Date = null)
    {
        $this->FromDate = $Date;
    }

    /**
     * @return string
     */
    public function getToDate()
    {
        if (null === $this->ToDate) {
            return false;
        }
        /** @var \DateTime $Date */
        $Date = $this->ToDate;
        if ($Date instanceof \DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @return false|\DateTime
     */
    public function getToDateTime()
    {
        if (null === $this->ToDate) {
            return false;
        }

        return $this->ToDate;
    }

    /**
     * @param null|\DateTime $Date
     */
    public function setToDate(\DateTime $Date = null)
    {
        $this->ToDate = $Date;
    }

    /**
     * @return bool|TblHolidayType
     */
    public function getTblHolidayType()
    {
        if (null === $this->tblHolidayType) {
            return false;
        } else {
            return BasicData::useService()->getHolidayTypeById($this->tblHolidayType);
        }
    }

    /**
     * @param TblHolidayType|null $tblHolidayType
     */
    public function setTblHolidayType(TblHolidayType $tblHolidayType = null)
    {

        $this->tblHolidayType = (null === $tblHolidayType ? null : $tblHolidayType->getId());
    }

    /**
     * @return bool|TblState
     */
    public function getTblState()
    {
        if (null === $this->tblState) {
            return false;
        } else {
            return BasicData::useService()->getStateById($this->tblState);
        }
    }

    /**
     * @param null|TblState $tblState
     */
    public function setTblState(TblState $tblState = null)
    {
        $this->tblState = (null === $tblState ? null : $tblState->getId());
    }
}
