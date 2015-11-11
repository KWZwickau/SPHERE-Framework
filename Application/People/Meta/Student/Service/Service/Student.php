<?php
namespace SPHERE\Application\People\Meta\Student\Service\Service;

use SPHERE\Application\People\Meta\Student\Service\Data;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Student
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Service
 */
abstract class Student extends AbstractService
{

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
