<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 19.02.2018
 * Time: 11:05
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblLeaveStudent")
 * @Cache(usage="READ_ONLY")
 */
class TblLeaveStudent extends Element
{
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_IS_APPROVED = 'IsApproved';
    const ATTR_IS_PRINTED = 'IsPrinted';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCertificate;

    /**
     * @Column(type="boolean")
     */
    protected bool $IsApproved;

    /**
     * @Column(type="boolean")
     */
    protected bool $IsPrinted;

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {
        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {
        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblDivisionCourse
     */
    public function getServiceTblDivision()
    {
        if (null === $this->serviceTblDivision) {
            return false;
        } else {
            return DivisionCourse::useService()->getDivisionCourseById($this->serviceTblDivision);
        }
    }

    /**
     * @param TblDivisionCourse|null $tblDivisionCourse
     */
    public function setServiceTblDivision(TblDivisionCourse $tblDivisionCourse = null)
    {
        $this->serviceTblDivision = (null === $tblDivisionCourse ? null : $tblDivisionCourse->getId());
    }

    /**
     * @return bool|TblCertificate
     */
    public function getServiceTblCertificate()
    {
        if (null === $this->serviceTblCertificate) {
            return false;
        } else {
            return Generator::useService()->getCertificateById($this->serviceTblCertificate);
        }
    }

    /**
     * @param TblCertificate|null $tblCertificate
     */
    public function setServiceTblCertificate(TblCertificate $tblCertificate = null)
    {
        $this->serviceTblCertificate = (null === $tblCertificate ? null : $tblCertificate->getId());
    }

    /**
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->IsApproved;
    }

    /**
     * @param bool $IsApproved
     */
    public function setApproved(bool $IsApproved)
    {
        $this->IsApproved = $IsApproved;
    }

    /**
     * @return bool
     */
    public function isPrinted(): bool
    {
        return $this->IsPrinted;
    }

    /**
     * @param bool $IsPrinted
     */
    public function setPrinted(bool $IsPrinted)
    {
        $this->IsPrinted = $IsPrinted;
    }
}