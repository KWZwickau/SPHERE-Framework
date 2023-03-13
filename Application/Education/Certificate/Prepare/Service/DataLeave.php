<?php

namespace SPHERE\Application\Education\Certificate\Prepare\Service;

use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveAdditionalGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveComplexExam;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveGrade;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveInformation;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareAdditionalGradeType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;

abstract class DataLeave extends DataDiploma
{
    /**
     * @param $Id
     *
     * @return false|TblLeaveStudent
     */
    public function getLeaveStudentById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblLeaveStudent', $Id);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return false|TblLeaveStudent
     */
    public function getLeaveStudentBy(TblPerson $tblPerson, TblYear $tblYear)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblLeaveStudent',
            array(
                TblLeaveStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblLeaveStudent::ATTR_SERVICE_TBL_YEAR => $tblYear->getId()
            )
        );
    }

    /**
     * @return false|TblLeaveStudent[]
     */
    public function  getLeaveStudentAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblLeaveStudent');
    }

    /**
     * @param bool $IsApproved
     * @param bool $IsPrinted
     *
     * @return false|TblLeaveStudent[]
     */
    public function getLeaveStudentAllBy(bool $IsApproved = false, bool $IsPrinted = false)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblLeaveStudent',
            array(
                TblLeaveStudent::ATTR_IS_APPROVED => $IsApproved,
                TblLeaveStudent::ATTR_IS_PRINTED => $IsPrinted
            )
        );
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblLeaveStudent[]
     */
    public function getLeaveStudentAllByYear(TblYear $tblYear)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblLeaveStudent',
            array(
                TblLeaveStudent::ATTR_SERVICE_TBL_YEAR => $tblYear->getId()
            )
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblCertificate $tblCertificate
     * @param bool $IsApproved
     * @param bool $IsPrinted
     *
     * @return null|TblLeaveStudent
     */
    public function createLeaveStudent(
        TblPerson $tblPerson,
        TblYear $tblYear,
        TblCertificate $tblCertificate,
        bool $IsApproved = false,
        bool $IsPrinted = false
    ): TblLeaveStudent {
        $Manager = $this->getEntityManager();

        $Entity = $Manager->getEntity('TblLeaveStudent')
            ->findOneBy(array(
                TblLeaveStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblLeaveStudent::ATTR_SERVICE_TBL_YEAR => $tblYear->getId()
            ));

        if (null === $Entity) {
            $Entity = new TblLeaveStudent();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblYear($tblYear);
            $Entity->setServiceTblCertificate($tblCertificate);
            $Entity->setApproved($IsApproved);
            $Entity->setPrinted($IsPrinted);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param bool $IsApproved
     * @param bool $IsPrinted
     *
     * @return bool
     */
    public function updateLeaveStudent(
        TblLeaveStudent $tblLeaveStudent,
        bool $IsApproved = false,
        bool $IsPrinted = false
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblLeaveStudent $Entity */
        $Entity = $Manager->getEntityById('TblLeaveStudent', $tblLeaveStudent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setApproved($IsApproved);
            $Entity->setPrinted($IsPrinted);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param TblCertificate $tblCertificate
     *
     * @return bool
     */
    public function updateLeaveStudentCertificate(
        TblLeaveStudent $tblLeaveStudent,
        TblCertificate $tblCertificate
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblLeaveStudent $Entity */
        $Entity = $Manager->getEntityById('TblLeaveStudent', $tblLeaveStudent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblCertificate($tblCertificate);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param TblSubject $tblSubject
     *
     * @return false|TblLeaveGrade
     */
    public function  getLeaveGradeBy(TblLeaveStudent $tblLeaveStudent, TblSubject $tblSubject)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblLeaveGrade', array(
            TblLeaveGrade::ATTR_TBL_LEAVE_STUDENT => $tblLeaveStudent->getId(),
            TblLeaveGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId()
        ));
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     *
     * @return false|TblLeaveGrade[]
     */
    public function getLeaveGradeAllByLeaveStudent(TblLeaveStudent $tblLeaveStudent)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblLeaveGrade', array(
            TblLeaveGrade::ATTR_TBL_LEAVE_STUDENT => $tblLeaveStudent->getId()
        ));
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param TblSubject $tblSubject
     * @param $Grade
     *
     * @return TblLeaveGrade
     */
    public function createLeaveGrade(TblLeaveStudent $tblLeaveStudent, TblSubject $tblSubject, $Grade): TblLeaveGrade
    {
        $Manager = $this->getEntityManager();

        $Entity = $Manager->getEntity('TblLeaveGrade')
            ->findOneBy(array(
                TblLeaveGrade::ATTR_TBL_LEAVE_STUDENT => $tblLeaveStudent->getId(),
                TblLeaveGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId()
            ));

        if (null === $Entity) {
            $Entity = new TblLeaveGrade();
            $Entity->setTblLeaveStudent($tblLeaveStudent);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setGrade($Grade);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblLeaveGrade $tblLeaveGrade
     * @param $Grade
     *
     * @return bool
     */
    public function updateLeaveGrade(
        TblLeaveGrade $tblLeaveGrade,
        $Grade
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblLeaveGrade $Entity */
        $Entity = $Manager->getEntityById('TblLeaveGrade', $tblLeaveGrade->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setGrade($Grade);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param $Field
     *
     * @return false|TblLeaveInformation
     */
    public function getLeaveInformationBy(TblLeaveStudent $tblLeaveStudent, $Field)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblLeaveInformation', array(
            TblLeaveInformation::ATTR_TBL_LEAVE_STUDENT => $tblLeaveStudent->getId(),
            TblLeaveInformation::ATTR_FIELD => $Field
        ));
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     *
     * @return false|TblLeaveInformation[]
     */
    public function getLeaveInformationAllByLeaveStudent(TblLeaveStudent $tblLeaveStudent)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblLeaveInformation', array(
            TblLeaveInformation::ATTR_TBL_LEAVE_STUDENT => $tblLeaveStudent->getId()
        ));
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param $Field
     * @param $Value
     *
     * @return TblLeaveInformation
     */
    public function createLeaveInformation(TblLeaveStudent $tblLeaveStudent, $Field, $Value): TblLeaveInformation
    {
        $Manager = $this->getEntityManager();

        $Entity = $Manager->getEntity('TblLeaveInformation')
            ->findOneBy(array(
                TblLeaveInformation::ATTR_TBL_LEAVE_STUDENT => $tblLeaveStudent->getId(),
                TblLeaveInformation::ATTR_FIELD => $Field
            ));

        if (null === $Entity) {
            $Entity = new TblLeaveInformation();
            $Entity->setTblLeaveStudent($tblLeaveStudent);
            $Entity->setField($Field);
            $Entity->setValue($Value);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblLeaveInformation $tblLeaveInformation
     * @param $Value
     *
     * @return bool
     */
    public function updateLeaveInformation(
        TblLeaveInformation $tblLeaveInformation,
        $Value
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblLeaveInformation $Entity */
        $Entity = $Manager->getEntityById('TblLeaveInformation', $tblLeaveInformation->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setValue($Value);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param TblSubject $tblSubject
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param bool $isForced
     *
     * @return false|TblLeaveAdditionalGrade
     */
    public function getLeaveAdditionalGradeBy(
        TblLeaveStudent $tblLeaveStudent,
        TblSubject $tblSubject,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        bool $isForced = false
    ) {
        if ($isForced) {
            return $this->getForceEntityBy(
                __METHOD__,
                $this->getEntityManager(),
                'TblLeaveAdditionalGrade',
                array(
                    TblLeaveAdditionalGrade::ATTR_TBL_LEAVE_STUDENT => $tblLeaveStudent->getId(),
                    TblLeaveAdditionalGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                    TblLeaveAdditionalGrade::ATTR_TBL_PREPARE_ADDITIONAL_GRADE_TYPE => $tblPrepareAdditionalGradeType->getId()
                )
            );
        } else {
            return $this->getCachedEntityBy(
                __METHOD__,
                $this->getEntityManager(),
                'TblLeaveAdditionalGrade',
                array(
                    TblLeaveAdditionalGrade::ATTR_TBL_LEAVE_STUDENT => $tblLeaveStudent->getId(),
                    TblLeaveAdditionalGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                    TblLeaveAdditionalGrade::ATTR_TBL_PREPARE_ADDITIONAL_GRADE_TYPE => $tblPrepareAdditionalGradeType->getId()
                )
            );
        }
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param TblSubject $tblSubject
     * @param TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType
     * @param $grade
     * @param bool $isLocked
     *
     * @return TblLeaveAdditionalGrade
     */
    public function createLeaveAdditionalGrade(
        TblLeaveStudent $tblLeaveStudent,
        TblSubject $tblSubject,
        TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType,
        $grade,
        bool $isLocked = false
    ): TblLeaveAdditionalGrade {
        $Manager = $this->getEntityManager();

        /** @var TblLeaveAdditionalGrade $Entity */
        $Entity = $Manager->getEntity('TblLeaveAdditionalGrade')->findOneBy(array(
            TblLeaveAdditionalGrade::ATTR_TBL_LEAVE_STUDENT => $tblLeaveStudent->getId(),
            TblLeaveAdditionalGrade::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
            TblLeaveAdditionalGrade::ATTR_TBL_PREPARE_ADDITIONAL_GRADE_TYPE => $tblPrepareAdditionalGradeType->getId()
        ));

        if ($Entity === null) {
            $Entity = new TblLeaveAdditionalGrade();
            $Entity->setTblLeaveStudent($tblLeaveStudent);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setTblPrepareAdditionalGradeType($tblPrepareAdditionalGradeType);
            $Entity->setGrade($grade);
            $Entity->setLocked($isLocked);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblLeaveAdditionalGrade $tblLeaveAdditionalGrade
     * @param $grade
     *
     * @return bool
     */
    public function updateLeaveAdditionalGrade(
        TblLeaveAdditionalGrade $tblLeaveAdditionalGrade,
        $grade
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblLeaveAdditionalGrade $Entity */
        $Entity = $Manager->getEntityById('TblLeaveAdditionalGrade', $tblLeaveAdditionalGrade->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setGrade($grade);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param $identifier
     * @param $ranking
     * @param $grade
     * @param TblSubject|null $tblFirstSubject
     * @param TblSubject|null $tblSecondSubject
     *
     * @return TblLeaveComplexExam
     */
    public function createLeaveComplexExam(
        TblLeaveStudent $tblLeaveStudent,
        $identifier,
        $ranking,
        $grade,
        TblSubject $tblFirstSubject = null,
        TblSubject $tblSecondSubject = null
    ): TblLeaveComplexExam {
        $Manager = $this->getEntityManager();

        /** @var TblLeaveComplexExam $Entity */
        $Entity = $Manager->getEntity('TblLeaveComplexExam')->findOneBy(array(
            TblLeaveComplexExam::ATTR_TBL_LEAVE_STUDENT => $tblLeaveStudent->getId(),
            TblLeaveComplexExam::ATTR_IDENTIFIER => $identifier,
            TblLeaveComplexExam::ATTR_RANKING => $ranking
        ));

        if ($Entity === null) {
            $Entity = new TblLeaveComplexExam();
            $Entity->setTblLeaveStudent($tblLeaveStudent);
            $Entity->setIdentifier($identifier);
            $Entity->setRanking($ranking);
            $Entity->setGrade($grade);
            $Entity->setServiceTblFirstSubject($tblFirstSubject);
            $Entity->setServiceTblSecondSubject($tblSecondSubject);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblLeaveComplexExam $tblLeaveComplexExam
     * @param $grade
     * @param TblSubject|null $tblFirstSubject
     * @param TblSubject|null $tblSecondSubject
     *
     * @return bool
     */
    public function updateLeaveComplexExam(
        TblLeaveComplexExam $tblLeaveComplexExam,
        $grade,
        TblSubject $tblFirstSubject = null,
        TblSubject $tblSecondSubject = null
    ): bool {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblLeaveComplexExam $Entity */
        $Entity = $Manager->getEntityById('TblLeaveComplexExam', $tblLeaveComplexExam->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setGrade($grade);
            $Entity->setServiceTblFirstSubject($tblFirstSubject);
            $Entity->setServiceTblSecondSubject($tblSecondSubject);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     * @param $identifier
     * @param $ranking
     *
     * @return false|TblLeaveComplexExam
     */
    public function getLeaveComplexExamBy(
        TblLeaveStudent $tblLeaveStudent,
        $identifier,
        $ranking
    ) {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblLeaveComplexExam', array(
            TblLeaveComplexExam::ATTR_TBL_LEAVE_STUDENT => $tblLeaveStudent->getId(),
            TblLeaveComplexExam::ATTR_IDENTIFIER => $identifier,
            TblLeaveComplexExam::ATTR_RANKING => $ranking
        ));
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     *
     * @return false|TblLeaveComplexExam[]
     */
    public function getLeaveComplexExamAllByLeaveStudent(TblLeaveStudent $tblLeaveStudent)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblLeaveComplexExam',
            array(TblLeaveComplexExam::ATTR_TBL_LEAVE_STUDENT => $tblLeaveStudent->getId()),
            array(
                TblLeaveComplexExam::ATTR_IDENTIFIER => self::ORDER_DESC,
                TblLeaveComplexExam::ATTR_RANKING => self::ORDER_ASC
            )
        );
    }

    /**
     * @return false|TblLeaveStudent[]
     */
    protected function getLeaveStudentAllByYearIsNull()
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblLeaveStudent', array(
            TblLeaveStudent::ATTR_SERVICE_TBL_YEAR => null
        ));
    }

    /**
     * @param TblLeaveStudent $tblLeaveStudent
     *
     * @return bool
     */
    public function destroyLeaveStudent(TblLeaveStudent $tblLeaveStudent): bool
    {
        $Manager = $this->getEntityManager();

        /** @var TblLeaveStudent $Entity */
        $Entity = $Manager->getEntityById('TblLeaveStudent', $tblLeaveStudent->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }
}