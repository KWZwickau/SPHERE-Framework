<?php
namespace SPHERE\Application\People\Meta\Common\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCommon")
 * @Cache(usage="READ_ONLY")
 */
class TblCommon extends Element
{

    const SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="text")
     */
    protected $Remark;
    /**
     * @Column(type="bigint")
     */
    protected $tblCommonBirthDates;
    /**
     * @Column(type="bigint")
     */
    protected $tblCommonInformation;

    /**
     * @return string
     */
    public function getRemark()
    {

        return $this->Remark;
    }

    /**
     * @param string $Remark
     */
    public function setRemark($Remark)
    {

        $this->Remark = $Remark;
    }

    /**
     * @return bool|TblCommonBirthDates
     */
    public function getTblCommonBirthDates()
    {

        if (null === $this->tblCommonBirthDates) {
            return false;
        } else {
            return Common::useService()->getCommonBirthDatesById($this->tblCommonBirthDates);
        }
    }

    /**
     * @param null|TblCommonBirthDates $tblCommonBirthDates
     */
    public function setTblCommonBirthDates(TblCommonBirthDates $tblCommonBirthDates = null)
    {

        $this->tblCommonBirthDates = ( null === $tblCommonBirthDates ? null : $tblCommonBirthDates->getId() );
    }

    /**
     * @return bool|TblCommonInformation
     */
    public function getTblCommonInformation()
    {

        if (null === $this->tblCommonInformation) {
            return false;
        } else {
            return Common::useService()->getCommonInformationById($this->tblCommonInformation);
        }
    }

    /**
     * @param null|TblCommonInformation $tblCommonInformation
     */
    public function setTblCommonInformation(TblCommonInformation $tblCommonInformation = null)
    {

        $this->tblCommonInformation = ( null === $tblCommonInformation ? null : $tblCommonInformation->getId() );
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
}
