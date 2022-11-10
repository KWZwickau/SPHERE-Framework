<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationTestStudentLink")
 * @Cache(usage="READ_ONLY")
 */
class TblTestStudentLink extends Element
{
    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationTest;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblPerson;

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
     * @param bool $IsForce
     *
     * @return false|TblPerson
     */
    public function getServiceTblPerson(bool $IsForce = false)
    {
        return Person::useService()->getPersonById($this->serviceTblPerson, $IsForce);
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson)
    {
        $this->serviceTblPerson = $tblPerson->getId();
    }
}