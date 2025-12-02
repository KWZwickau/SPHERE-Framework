<?php

namespace SPHERE\Application\Education\Lesson\LeaveStudent\Service;

use DateTime;
use SPHERE\Application\Education\Lesson\LeaveStudent\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

class Data extends AbstractData
{
    public function setupDatabaseContent()
    {

    }

    /**
     * @param $id
     *
     * @return TblLeaveStudent|false
     */
    public function getLeaveStudentById($id): TblLeaveStudent|false
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblLeaveStudent', $id);
    }

    /**
     * @param TblType $tblSchoolType
     * @param TblYear $tblYear
     *
     * @return false|TblLeaveStudent
     */
    public function getLeaveStudentBy(TblType $tblSchoolType, TblYear $tblYear): false|TblLeaveStudent
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblLeaveStudent', [
            TblLeaveStudent::ATTR_SERVICE_TBL_SCHOOL_TYPE => $tblSchoolType->getId(),
            TblLeaveStudent::ATTR_SERVICE_TBL_YEAR => $tblYear->getId(),
        ]);
    }

    /**
     * @param TblType $tblSchoolType
     * @param TblYear $tblYear
     * @param array $Data
     * @param bool|null $IsPrintView
     *
     * @return TblLeaveStudent
     */
    public function updateLeaveStudent(TblType $tblSchoolType, TblYear $tblYear, array $Data, ?bool $IsPrintView): TblLeaveStudent
    {
        $Manager = $this->getEntityManager();
        /** @var TblLeaveStudent $Entity */
        $Entity = $Manager->getEntity('TblLeaveStudent')
            ->findOneBy(
                array(
                    TblLeaveStudent::ATTR_SERVICE_TBL_SCHOOL_TYPE => $tblSchoolType->getId(),
                    TblLeaveStudent::ATTR_SERVICE_TBL_YEAR => $tblYear->getId(),
                )
            );
        if (null === $Entity) {
            $Entity = new TblLeaveStudent();
            $Entity->setServiceTblSchoolType($tblSchoolType);
            $Entity->setServiceTblYear($tblYear);
            $Entity->setData($Data);
            $Entity->setIsPrintView($IsPrintView === true);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        } else {
            $Protocol = clone $Entity;
            $Entity->setServiceTblSchoolType($tblSchoolType);
            $Entity->setServiceTblYear($tblYear);
            $Entity->setData($Data);
            if ($IsPrintView !== null) {
                $Entity->setIsPrintView($IsPrintView);
            }

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblType $tblSchoolType
     * @param TblYear $tblYear
     * @param DateTime|null $documentDate
     *
     * @return TblLeaveStudent
     */
    public function updateLeaveStudentSetDocumentDate(TblType $tblSchoolType, TblYear $tblYear, ?DateTime $documentDate): TblLeaveStudent
    {
        $Manager = $this->getEntityManager();
        /** @var TblLeaveStudent $Entity */
        $Entity = $Manager->getEntity('TblLeaveStudent')
            ->findOneBy(
                array(
                    TblLeaveStudent::ATTR_SERVICE_TBL_SCHOOL_TYPE => $tblSchoolType->getId(),
                    TblLeaveStudent::ATTR_SERVICE_TBL_YEAR => $tblYear->getId(),
                )
            );
        if (null ==! $Entity) {
            $Protocol = clone $Entity;
            $Entity->setDocumentDate($documentDate);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }

        return $Entity;
    }
}