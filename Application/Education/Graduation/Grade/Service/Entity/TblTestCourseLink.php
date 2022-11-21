<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationTestCourseLink")
 * @Cache(usage="READ_ONLY")
 */
class TblTestCourseLink extends Element
{
    const ATTR_TBL_TEST = 'tblGraduationTest';
    const ATTR_SERVICE_TBL_DIVISION_COURSE = 'serviceTblDivisionCourse';

    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationTest;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblDivisionCourse;

    /**
     * @param TblTest $tblTest
     * @param TblDivisionCourse $tblDivisionCourse
     */
    public function __construct(TblTest $tblTest, TblDivisionCourse $tblDivisionCourse)
    {
        $this->tblGraduationTest = $tblTest->getId();
        $this->serviceTblDivisionCourse = $tblDivisionCourse->getId();
    }

    /**
     * @return TblTest
     */
    public function getTblTest(): TblTest
    {
        return Grade::useService()->getTestById($this->tblGraduationTest);
    }

    /**
     * @param TblTest $tblTest
     */
    public function setTblTest(TblTest $tblTest)
    {
        $this->tblGraduationTest = $tblTest->getId();
    }

    /**
     * @return false|TblDivisionCourse
     */
    public function getServiceTblDivisionCourse()
    {
        return DivisionCourse::useService()->getDivisionCourseById($this->serviceTblDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     */
    public function setServiceTblDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        $this->serviceTblDivisionCourse = $tblDivisionCourse->getId();
    }
}