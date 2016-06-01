<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 17.05.2016
 * Time: 08:27
 */

namespace SPHERE\Application\People\Meta\Club\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblClub")
 * @Cache(usage="READ_ONLY")
 */
class TblClub extends Element
{

    const SERVICE_TBL_PERSON = 'serviceTblPerson';
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="datetime")
     */
    protected $EntryDate;

    /**
     * @Column(type="datetime")
     */
    protected $ExitDate;

    /**
     * @Column(type="string")
     */
    protected $Identifier;

    /**
     * @Column(type="string")
     */
    protected $Remark;

    /**
     * @return string
     */
    public function getEntryDate()
    {

        if (null === $this->EntryDate) {
            return false;
        }
        /** @var \DateTime $EntryDate */
        $EntryDate = $this->EntryDate;
        if ($EntryDate instanceof \DateTime) {
            return $EntryDate->format('d.m.Y');
        } else {
            return (string)$EntryDate;
        }
    }

    /**
     * @param null|\DateTime $EntryDate
     */
    public function setEntryDate(\DateTime $EntryDate = null)
    {

        $this->EntryDate = $EntryDate;
    }

    /**
     * @return string
     */
    public function getExitDate()
    {

        if (null === $this->ExitDate) {
            return false;
        }
        /** @var \DateTime $ExitDate */
        $ExitDate = $this->ExitDate;
        if ($ExitDate instanceof \DateTime) {
            return $ExitDate->format('d.m.Y');
        } else {
            return (string)$ExitDate;
        }
    }

    /**
     * @param null|\DateTime $ExitDate
     */
    public function setExitDate(\DateTime $ExitDate = null)
    {

        $this->ExitDate = $ExitDate;
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
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->Identifier;
    }

    /**
     * @param mixed $Identifier
     */
    public function setIdentifier($Identifier)
    {
        $this->Identifier = $Identifier;
    }

    /**
     * @return mixed
     */
    public function getRemark()
    {
        return $this->Remark;
    }

    /**
     * @param mixed $Remark
     */
    public function setRemark($Remark)
    {
        $this->Remark = $Remark;
    }
}