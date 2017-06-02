<?php

namespace SPHERE\Application\Api\People\Meta\Subject;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Meta\Student\Service\Data as DataAPP;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectRanking;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectType;
use SPHERE\Application\Platform\System\Protocol\Protocol;

class Data extends DataAPP
{
    /**
     * @param TblStudent               $tblStudent
     * @param TblSubject|null          $tblSubject
     * @param TblStudentSubjectType    $tblStudentSubjectType
     * @param TblStudentSubjectRanking $tblStudentSubjectRanking
     * @param TblLevel|null            $tblLevelFrom
     * @param TblLevel|null            $tblLevelTill
     *
     * @return TblStudentSubject
     */
    public function createStudentSubject(
        TblStudent $tblStudent,
        TblSubject $tblSubject = null,
        TblStudentSubjectType $tblStudentSubjectType,
        TblStudentSubjectRanking $tblStudentSubjectRanking,
        TblLevel $tblLevelFrom = null,
        TblLevel $tblLevelTill = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblStudentSubject $Entity */
        $Entity = $Manager->getEntity('TblStudentSubject')->findOneBy(array(
            TblStudentSubject::ATTR_TBL_STUDENT                 => $tblStudent->getId(),
            TblStudentSubject::ATTR_TBL_STUDENT_SUBJECT_TYPE    => $tblStudentSubjectType->getId(),
            TblStudentSubject::ATTR_TBL_STUDENT_SUBJECT_RANKING => $tblStudentSubjectRanking->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblStudentSubject();

            $Entity->setTblStudent($tblStudent);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setTblStudentSubjectType($tblStudentSubjectType);
            $Entity->setTblStudentSubjectRanking($tblStudentSubjectRanking);
            $Entity->setServiceTblLevelFrom($tblLevelFrom);
            $Entity->setServiceTblLevelTill($tblLevelTill);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        } else {
            $Protocol = clone $Entity;

            $Entity->setTblStudent($tblStudent);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setTblStudentSubjectType($tblStudentSubjectType);
            $Entity->setTblStudentSubjectRanking($tblStudentSubjectRanking);
            $Entity->setServiceTblLevelFrom($tblLevelFrom);
            $Entity->setServiceTblLevelTill($tblLevelTill);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }

        return $Entity;
    }
}