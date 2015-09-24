<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudent")
 * @Cache(usage="READ_ONLY")
 */
class TblStudent extends Element
{

    const SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentMedicalRecord;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentTransport;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentTransfer;

    /**
     * @return bool|TblStudentMedicalRecord
     */
    public function getTblStudentMedicalRecord()
    {

        if (null === $this->tblStudentMedicalRecord) {
            return false;
        } else {
            return Student::useService()->getStudentMedicalRecordById($this->tblStudentMedicalRecord);
        }
    }

    /**
     * @param null|TblStudentMedicalRecord $tblStudentMedicalRecord
     */
    public function setTblStudentMedicalRecord(TblStudentMedicalRecord $tblStudentMedicalRecord = null)
    {

        $this->tblStudentMedicalRecord = ( null === $tblStudentMedicalRecord ? null : $tblStudentMedicalRecord->getId() );
    }

    /**
     * @return bool|TblStudentTransport
     */
    public function getTblStudentTransport()
    {

        if (null === $this->tblStudentTransport) {
            return false;
        } else {
            return Student::useService()->getStudentTransportById($this->tblStudentTransport);
        }
    }

    /**
     * @param null|TblStudentTransport $tblStudentTransport
     */
    public function setTblStudentTransport(TblStudentTransport $tblStudentTransport = null)
    {

        $this->tblStudentTransport = ( null === $tblStudentTransport ? null : $tblStudentTransport->getId() );
    }

    /**
     * @return bool|TblStudentTransfer
     */
    public function getTblStudentTransfer()
    {

        if (null === $this->tblStudentTransfer) {
            return false;
        } else {
            return Student::useService()->getStudentTransferById($this->tblStudentTransfer);
        }
    }

    /**
     * @param null|TblStudentTransfer $tblStudentTransfer
     */
    public function setTblStudentTransfer(TblStudentTransfer $tblStudentTransfer = null)
    {

        $this->tblStudentTransfer = ( null === $tblStudentTransfer ? null : $tblStudentTransfer->getId() );
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
