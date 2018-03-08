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
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
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
    const ATTR_SERVICE_TBL_CERTIFICATE = 'serviceTblCertificate';
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
    protected $IsApproved;

    /**
     * @Column(type="boolean")
     */
    protected $IsPrinted;

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
     * @return bool|TblDivision
     */
    public function getServiceTblDivision()
    {

        if (null === $this->serviceTblDivision) {
            return false;
        } else {
            return Division::useService()->getDivisionById($this->serviceTblDivision);
        }
    }

    /**
     * @param TblDivision|null $tblDivision
     */
    public function setServiceTblDivision(TblDivision $tblDivision = null)
    {

        $this->serviceTblDivision = (null === $tblDivision ? null : $tblDivision->getId());
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
    public function isApproved()
    {

        return $this->IsApproved;
    }

    /**
     * @param bool $IsApproved
     */
    public function setApproved($IsApproved)
    {

        $this->IsApproved = (bool)$IsApproved;
    }

    /**
     * @return bool
     */
    public function isPrinted()
    {

        return $this->IsPrinted;
    }

    /**
     * @param bool $IsPrinted
     */
    public function setPrinted($IsPrinted)
    {

        $this->IsPrinted = (bool)$IsPrinted;
    }

}