<?php
namespace SPHERE\Application\People\Meta\Student;

use SPHERE\Application\People\Meta\Student\Service\Data;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBaptism;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBilling;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLocker;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransport;
use SPHERE\Application\People\Meta\Student\Service\Service\Integration;
use SPHERE\Application\People\Meta\Student\Service\Setup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Student
 */
class Service extends Integration
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
     * @param IFormInterface $Form
     * @param TblPerson $tblPerson
     * @param array $Meta
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

        $this->getDebugger()->screenDump($Meta['MedicalRecord']);

        $AttendingDoctor = Person::useService()->getPersonById($Meta['MedicalRecord']['AttendingDoctor']);

        if ($tblStudent) {

            if ($tblStudent->getIdentifier() !== $Meta['Student']['Identifier']) {
                (new Data($this->getBinding()))->updateStudentIdentifier(
                    $tblStudent,
                    $Meta['Student']['Identifier']);
            }
            (new Data($this->getBinding()))->updateStudentMedicalRecord(
                $tblStudent->getTblStudentMedicalRecord(),
                $Meta['MedicalRecord']['Disease'],
                $Meta['MedicalRecord']['Medication'],
                $AttendingDoctor ? $AttendingDoctor : null,
                $Meta['MedicalRecord']['Insurance']['State'],
                $Meta['MedicalRecord']['Insurance']['Company']
            );
        } else {
            $tblStudentMedicalRecord = (new Data($this->getBinding()))->createStudentMedicalRecord(
                $Meta['MedicalRecord']['Disease'],
                $Meta['MedicalRecord']['Medication'],
                $AttendingDoctor ? $AttendingDoctor : null,
                $Meta['MedicalRecord']['Insurance']['State'],
                $Meta['MedicalRecord']['Insurance']['Company']
            );
            (new Data($this->getBinding()))->createStudent(
                $tblPerson,
                $Meta['Student']['Identifier'],
                $tblStudentMedicalRecord
            );
        }

        return new Success('Die Daten wurde erfolgreich gespeichert')
        . new Redirect('/People/Person', 3, array('Id' => $tblPerson->getId()));
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
     * @return bool|TblStudentBaptism
     */
    public function getStudentBaptismById($Id)
    {

        return (new Data($this->getBinding()))->getStudentBaptismById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentBilling
     */
    public function getStudentBillingById($Id)
    {

        return (new Data($this->getBinding()))->getStudentBillingById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentLocker
     */
    public function getStudentLockerById($Id)
    {

        return (new Data($this->getBinding()))->getStudentLockerById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransport
     */
    public function getStudentTransportById($Id)
    {

        return (new Data($this->getBinding()))->getStudentTransportById($Id);
    }
}
