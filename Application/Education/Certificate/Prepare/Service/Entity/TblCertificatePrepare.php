<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 11:19
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblCertificatePrepare")
 * @Cache(usage="READ_ONLY")
 */
class TblCertificatePrepare extends Element
{

    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_IS_APPROVED = 'IsApproved';
    const ATTR_IS_PRINTED = 'IsPrinted';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="datetime")
     */
    protected $Date;

    /**
     * @Column(type="boolean")
     */
    protected $IsApproved;

    /**
     * @Column(type="boolean")
     */
    protected $IsPrinted;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAppointedDateTask;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblBehaviorTask;

    /**
     * @return string
     */
    public function getDate()
    {

        if (null === $this->Date) {
            return false;
        }
        /** @var \DateTime $Date */
        $Date = $this->Date;
        if ($Date instanceof \DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|\DateTime $Date
     */
    public function setDate(\DateTime $Date = null)
    {

        $this->Date = $Date;
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
     * @return bool|TblTask
     */
    public function getServiceTblAppointedDateTask()
    {

        if (null === $this->serviceTblAppointedDateTask) {
            return false;
        } else {
            return Evaluation::useService()->getTaskById($this->serviceTblAppointedDateTask);
        }
    }

    /**
     * @param TblTask|null $tblTask
     */
    public function setServiceTblAppointedDateTask(TblTask $tblTask = null)
    {

        $this->serviceTblAppointedDateTask = (null === $tblTask ? null : $tblTask->getId());
    }

    /**
     * @return bool|TblTask
     */
    public function getServiceTblBehaviorTask()
    {

        if (null === $this->serviceTblBehaviorTask) {
            return false;
        } else {
            return Evaluation::useService()->getTaskById($this->serviceTblBehaviorTask);
        }
    }

    /**
     * @param TblTask|null $tblTask
     */
    public function setServiceTblBehaviorTask(TblTask $tblTask = null)
    {

        $this->serviceTblBehaviorTask = (null === $tblTask ? null : $tblTask->getId());
    }
}