<?php
namespace SPHERE\Application\People\Meta\Student;

use SPHERE\Application\IServiceInterface;
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
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Student
 */
class Service implements IServiceInterface
{

    /** @var null|Binding */
    private $Binding = null;
    /** @var null|Structure */
    private $Structure = null;

    /**
     * Define Database Connection
     *
     * @param Identifier $Identifier
     * @param string     $EntityPath
     * @param string     $EntityNamespace
     */
    public function __construct(Identifier $Identifier, $EntityPath, $EntityNamespace)
    {

        $this->Binding = new Binding($Identifier, $EntityPath, $EntityNamespace);
        $this->Structure = new Structure($Identifier);
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->Structure))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->Binding))->setupDatabaseContent();
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

        return (new Data($this->Binding))->getStudentById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentMedicalRecord
     */
    public function getStudentMedicalRecordById($Id)
    {

        return (new Data($this->Binding))->getStudentMedicalRecordById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransfer
     */
    public function getStudentTransferById($Id)
    {

        return (new Data($this->Binding))->getStudentTransferById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransferArrive
     */
    public function getStudentTransferArriveById($Id)
    {

        return (new Data($this->Binding))->getStudentTransferArriveById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransferEnrollment
     */
    public function getStudentTransferEnrollmentById($Id)
    {

        return (new Data($this->Binding))->getStudentTransferEnrollmentById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransferLeave
     */
    public function getStudentTransferLeaveById($Id)
    {

        return (new Data($this->Binding))->getStudentTransferLeaveById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransferProcess
     */
    public function getStudentTransferProcessById($Id)
    {

        return (new Data($this->Binding))->getStudentTransferProcessById($Id);
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
            (new Data($this->Binding))->updateStudentMedicalRecord(
                $tblStudent->getTblStudentMedicalRecord(),
                $Meta['MedicalRecord']['Disease'],
                $Meta['MedicalRecord']['Medication'],
                $Meta['MedicalRecord']['tblPersonAttendingDoctor'],
                $Meta['MedicalRecord']['InsuranceState'],
                $Meta['MedicalRecord']['Insurance']
            );
        } else {
            $tblStudentMedicalRecord = (new Data($this->Binding))->createStudentMedicalRecord(
                $Meta['MedicalRecord']['Disease'],
                $Meta['MedicalRecord']['Medication'],
                $Meta['MedicalRecord']['tblPersonAttendingDoctor'],
                $Meta['MedicalRecord']['InsuranceState'],
                $Meta['MedicalRecord']['Insurance']
            );
            (new Data($this->Binding))->createStudent(
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

        return (new Data($this->Binding))->getStudentByPerson($tblPerson);
    }
}
