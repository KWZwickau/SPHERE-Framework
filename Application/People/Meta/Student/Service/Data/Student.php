<?php
namespace SPHERE\Application\People\Meta\Student\Service\Data;

use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Student
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Data
 */
abstract class Student extends AbstractData
{

    /**
     * @param TblPerson               $tblPerson
     * @param TblStudentMedicalRecord $tblStudentMedicalRecord
     *
     * @return TblStudent
     */
    public function createStudent(
        TblPerson $tblPerson,
        TblStudentMedicalRecord $tblStudentMedicalRecord
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblStudent();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setTblStudentMedicalRecord($tblStudentMedicalRecord);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudent
     */
    public function getStudentById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudent', $Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblStudent
     */
    public function getStudentByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudent', array(
            TblStudent::SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }
}
