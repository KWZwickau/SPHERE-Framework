<?php

namespace SPHERE\Application\Education\ClassRegister\ScheduleTime\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblClassRegisterScheduleTimeSchoolType")
 * @Cache(usage="READ_ONLY")
 */
class TblScheduleTimeSchoolType extends Element
{
    const ATTR_TABLE_SCHEDULE_TIME = 'tblClassRegisterScheduleTime';
    const SERVICE_TABLE_SCHOOL_TYPE = 'serviceTblSchoolType';

    /**
     * @Column(type="bigint")
     */
    protected int $tblClassRegisterScheduleTime;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblSchoolType;

    /**
     * @param TblScheduleTime $tblScheduleTime
     *
     * @return $this
     */
    public function setTblScheduleTime(TblScheduleTime $tblScheduleTime): TblScheduleTimeSchoolType
    {
        $this->tblClassRegisterScheduleTime = $tblScheduleTime->getId();

        return $this;
    }

    /**
     * @param TblType $tblSchoolType
     *
     * @return $this
     */
    public function setServiceTblSchoolType(TblType $tblSchoolType): TblScheduleTimeSchoolType
    {
        $this->serviceTblSchoolType = $tblSchoolType->getId();

        return $this;
    }

    /**
     * @return TblType|bool
     */
    public function getServiceTblSchoolType(): TblType|bool
    {
        return Type::useService()->getTypeById($this->serviceTblSchoolType);
    }
}