<?php

namespace SPHERE\Application\Education\Lesson\LeaveStudent\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblLessonLeaveStudent")
 * @Cache(usage="READ_ONLY")
 */
class TblLeaveStudent extends Element
{
    const ATTR_SERVICE_TBL_SCHOOL_TYPE = 'serviceTblSchoolType';
    const ATTR_SERVICE_TBL_YEAR = 'serviceTblYear';

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblSchoolType;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblYear;
    /**
     * @Column(type="string")
     */
    protected string $Data;

    /**
     * @return false|TblType
     */
    public function getServiceTblSchoolType(): bool|TblType
    {
        return Type::useService()->getTypeById($this->serviceTblSchoolType);
    }

    /**
     * @param TblType $tblSchoolType
     */
    public function setServiceTblSchoolType(TblType $tblSchoolType): void
    {
        $this->serviceTblSchoolType = $tblSchoolType->getId();
    }

    /**
     * @return false|TblYear
     */
    public function getServiceTblYear(): bool|TblYear
    {
        return Term::useService()->getYearById($this->serviceTblYear);
    }

    /**
     * @param TblYear $tblYear
     */
    public function setServiceTblYear(TblYear $tblYear): void
    {
        $this->serviceTblYear = $tblYear->getId();
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return json_decode($this->Data, true) ?: [];
    }

    /**
     * @param array $Data
     * @return void
     */
    public function setData(array $Data): void
    {
        $this->Data = json_encode($Data);
    }
}