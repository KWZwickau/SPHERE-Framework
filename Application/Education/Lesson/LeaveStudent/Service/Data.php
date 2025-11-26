<?php

namespace SPHERE\Application\Education\Lesson\LeaveStudent\Service;

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
     *
     * @return TblLeaveStudent
     */
    public function updateLeaveStudent(TblType $tblSchoolType, TblYear $tblYear, array $Data): TblLeaveStudent
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

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        } else {
            $Protocol = clone $Entity;
            $Entity->setServiceTblSchoolType($tblSchoolType);
            $Entity->setServiceTblYear($tblYear);
            $Entity->setData($Data);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }

        return $Entity;
    }
}