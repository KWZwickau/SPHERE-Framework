<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.07.2016
 * Time: 10:03
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblPrepareStudent")
 * @Cache(usage="READ_ONLY")
 */
class TblPrepareStudent extends Element
{

    const ATTR_TBL_CERTIFICATE_PREPARE = 'tblCertificatePrepare';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_IS_APPROVED = 'IsApproved';
    const ATTR_IS_PRINTED = 'IsPrinted';
    const ATTR_SERVICE_TBL_CERTIFICATE = 'serviceTblCertificate';

    /**
     * @Column(type="bigint")
     */
    protected $tblCertificatePrepare;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

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
     * @Column(type="integer")
     */
    protected $ExcusedDays;

    /**
     * @Column(type="integer")
     */
    protected $UnexcusedDays;


    /**
     * @return false|TblCertificatePrepare
     */
    public function getTblCertificatePrepare()
    {

        if (null === $this->tblCertificatePrepare) {
            return false;
        } else {
            return Prepare::useService()->getPrepareById($this->tblCertificatePrepare);
        }
    }

    /**
     * @param TblCertificatePrepare|null $tblCertificatePrepare
     */
    public function setTblCertificatePrepare(TblCertificatePrepare $tblCertificatePrepare = null)
    {

        $this->tblCertificatePrepare = (null === $tblCertificatePrepare ? null : $tblCertificatePrepare->getId());
    }

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
     * @return integer|null
     */
    public function getExcusedDays()
    {
        return $this->ExcusedDays;
    }

    /**
     * @param integer|null $ExcusedDays
     */
    public function setExcusedDays($ExcusedDays)
    {
        $this->ExcusedDays = $ExcusedDays;
    }

    /**
     * @return integer|null
     */
    public function getUnexcusedDays()
    {
        return $this->UnexcusedDays;
    }

    /**
     * @param integer|null $UnexcusedDays
     */
    public function setUnexcusedDays($UnexcusedDays)
    {
        $this->UnexcusedDays = $UnexcusedDays;
    }
}