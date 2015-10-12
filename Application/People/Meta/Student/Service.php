<?php
namespace SPHERE\Application\People\Meta\Student;

use SPHERE\Application\People\Meta\Student\Service\Data;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransfer;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferArrive;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferEnrollment;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferLeave;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferProcess;
use SPHERE\Application\People\Meta\Student\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Student
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudent
     */
    public function getStudentById($Id)
    {

        return (new Data($this->getBinding()))->getStudentById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentMedicalRecord
     */
    public function getStudentMedicalRecordById($Id)
    {

        return (new Data($this->getBinding()))->getStudentMedicalRecordById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransfer
     */
    public function getStudentTransferById($Id)
    {

        return (new Data($this->getBinding()))->getStudentTransferById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransferArrive
     */
    public function getStudentTransferArriveById($Id)
    {

        return (new Data($this->getBinding()))->getStudentTransferArriveById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransferEnrollment
     */
    public function getStudentTransferEnrollmentById($Id)
    {

        return (new Data($this->getBinding()))->getStudentTransferEnrollmentById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransferLeave
     */
    public function getStudentTransferLeaveById($Id)
    {

        return (new Data($this->getBinding()))->getStudentTransferLeaveById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransferProcess
     */
    public function getStudentTransferProcessById($Id)
    {

        return (new Data($this->getBinding()))->getStudentTransferProcessById($Id);
    }

    /**
     * @param IFormInterface $Form
     * @param TblPerson      $tblPerson
     * @param array          $Meta
     *
     * @return IFormInterface|Redirect
     */
    public function createMeta(IFormInterface $Form = null, TblPerson $tblPerson, $Meta)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Meta) {
            return $Form;
        }

        $tblStudent = $this->getStudentByPerson($tblPerson);
        if ($tblStudent) {
            (new Data($this->getBinding()))->updateStudentMedicalRecord(
                $tblStudent->getTblStudentMedicalRecord(),
                $Meta['MedicalRecord']['Disease'],
                $Meta['MedicalRecord']['Medication'],
                $Meta['MedicalRecord']['tblPersonAttendingDoctor'],
                $Meta['MedicalRecord']['InsuranceState'],
                $Meta['MedicalRecord']['Insurance']
            );
        } else {
            $tblStudentMedicalRecord = (new Data($this->getBinding()))->createStudentMedicalRecord(
                $Meta['MedicalRecord']['Disease'],
                $Meta['MedicalRecord']['Medication'],
                $Meta['MedicalRecord']['tblPersonAttendingDoctor'],
                $Meta['MedicalRecord']['InsuranceState'],
                $Meta['MedicalRecord']['Insurance']
            );
            (new Data($this->getBinding()))->createStudent(
                $tblPerson,
                $tblStudentMedicalRecord
            );
        }
        return new Success('Die Daten wurde erfolgreich gespeichert')
        .new Redirect('/People/Person', 3, array('Id' => $tblPerson->getId()));
    }

    /**
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblStudent
     */
    public function getStudentByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getStudentByPerson($tblPerson);
    }
}
