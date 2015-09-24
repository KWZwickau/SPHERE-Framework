<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentTransfer")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentTransfer extends Element
{

    /**
     * @Column(type="bigint")
     */
    protected $tblStudentTransferArrive;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentTransferEnrollment;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentTransferLeave;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentTransferProcess;

    /**
     * @return bool|TblStudentTransferArrive
     */
    public function getTblStudentTransferArrive()
    {

        if (null === $this->tblStudentTransferArrive) {
            return false;
        } else {
            return Student::useService()->getStudentTransferArriveById($this->tblStudentTransferArrive);
        }
    }

    /**
     * @param null|TblStudentTransferArrive $tblStudentTransferArrive
     */
    public function setTblStudentTransferArrive(TblStudentTransferArrive $tblStudentTransferArrive = null)
    {

        $this->tblStudentTransferArrive = ( null === $tblStudentTransferArrive ? null : $tblStudentTransferArrive->getId() );
    }

    /**
     * @return bool|TblStudentTransferEnrollment
     */
    public function getTblStudentTransferEnrollment()
    {

        if (null === $this->tblStudentTransferEnrollment) {
            return false;
        } else {
            return Student::useService()->getStudentTransferEnrollmentById($this->tblStudentTransferEnrollment);
        }
    }

    /**
     * @param null|TblStudentTransferEnrollment $tblStudentTransferEnrollment
     */
    public function setTblStudentTransferEnrollment(TblStudentTransferEnrollment $tblStudentTransferEnrollment = null)
    {

        $this->tblStudentTransferEnrollment = ( null === $tblStudentTransferEnrollment ? null : $tblStudentTransferEnrollment->getId() );
    }

    /**
     * @return bool|TblStudentTransferLeave
     */
    public function getTblStudentTransferLeave()
    {

        if (null === $this->tblStudentTransferLeave) {
            return false;
        } else {
            return Student::useService()->getStudentTransferLeaveById($this->tblStudentTransferLeave);
        }
    }

    /**
     * @param null|TblStudentTransferLeave $tblStudentTransferLeave
     */
    public function setTblStudentTransferLeave(TblStudentTransferLeave $tblStudentTransferLeave = null)
    {

        $this->tblStudentTransferLeave = ( null === $tblStudentTransferLeave ? null : $tblStudentTransferLeave->getId() );
    }

    /**
     * @return bool|TblStudentTransferProcess
     */
    public function getTblStudentTransferProcess()
    {

        if (null === $this->tblStudentTransferProcess) {
            return false;
        } else {
            return Student::useService()->getStudentTransferProcessById($this->tblStudentTransferProcess);
        }
    }

    /**
     * @param null|TblStudentTransferProcess $tblStudentTransferProcess
     */
    public function setTblStudentTransferProcess(TblStudentTransferProcess $tblStudentTransferProcess = null)
    {

        $this->tblStudentTransferProcess = ( null === $tblStudentTransferProcess ? null : $tblStudentTransferProcess->getId() );
    }
}
