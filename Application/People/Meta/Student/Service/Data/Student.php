<?php
namespace SPHERE\Application\People\Meta\Student\Service\Data;

use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
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
     * @param TblPerson $tblPerson
     * @param string    $Identifier
     *
     * @return TblStudent
     */
    public function createStudent(
        TblPerson $tblPerson,
        $Identifier
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $this->getStudentByPerson($tblPerson);
        if (null === $Entity) {

            $Entity = new TblStudent();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setIdentifier($Identifier);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
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

    /**
     * @param TblStudent $tblStudent
     * @param string     $Identifier
     *
     * @return bool
     */
    public function updateStudent(
        TblStudent $tblStudent,
        $Identifier
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudent $Entity */
        $Entity = $Manager->getEntityById('TblStudent', $tblStudent->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setServiceTblPerson($tblStudent);
            $Entity->setIdentifier($Identifier);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
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
}
