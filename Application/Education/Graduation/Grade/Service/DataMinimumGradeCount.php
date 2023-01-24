<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service;

use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCount;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCountLevelLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCountSubjectLink;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Platform\System\Protocol\Protocol;

abstract class DataMinimumGradeCount extends DataMigrate
{
    /**
     * @param $Id
     *
     * @return false|TblMinimumGradeCount
     */
    public function getMinimumGradeCountById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblMinimumGradeCount', $Id);
    }

    /**
     * @return false|TblMinimumGradeCount[]
     */
    public function getMinimumGradeCountAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblMinimumGradeCount');
    }

    /**
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     *
     * @return false|TblMinimumGradeCountLevelLink[]
     */
    public function getMinimumGradeCountLevelLinkByMinimumGradeCount(TblMinimumGradeCount $tblMinimumGradeCount)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblMinimumGradeCountLevelLink',
            array(TblMinimumGradeCountLevelLink::ATTR_TBL_MINIMUM_GRADE_COUNT => $tblMinimumGradeCount->getId()));
    }

    /**
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     *
     * @return false|TblMinimumGradeCountSubjectLink[]
     */
    public function getMinimumGradeCountSubjectLinkByMinimumGradeCount(TblMinimumGradeCount $tblMinimumGradeCount)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblMinimumGradeCountSubjectLink',
            array(TblMinimumGradeCountSubjectLink::ATTR_TBL_MINIMUM_GRADE_COUNT => $tblMinimumGradeCount->getId()));
    }

    /**
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     * @param TblSubject $tblSubject
     *
     * @return false|TblMinimumGradeCountSubjectLink
     */
    public function getMinimumGradeCountSubjectLinkByMinimumGradeCountAndSubject(TblMinimumGradeCount $tblMinimumGradeCount, TblSubject $tblSubject)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblMinimumGradeCountSubjectLink', array(
            TblMinimumGradeCountSubjectLink::ATTR_TBL_MINIMUM_GRADE_COUNT => $tblMinimumGradeCount->getId(),
            TblMinimumGradeCountSubjectLink::ATTR_TBL_SERVICE_TBL_SUBJECT => $tblSubject->getId()
        ));
    }

    /**
     * @param $Count
     * @param TblGradeType|null $tblGradeType
     * @param $Period
     * @param $Highlighted
     * @param $Course
     *
     * @return TblMinimumGradeCount
     */
    public function createMinimumGradeCount($Count, ?TblGradeType $tblGradeType, $Period, $Highlighted, $Course): TblMinimumGradeCount
    {
        $Manager = $this->getEntityManager();
        $Entity = new TblMinimumGradeCount($Count, $tblGradeType, $Period, $Highlighted, $Course);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param $Count
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     * @param TblGradeType|null $tblGradeType
     * @param $Period
     * @param $Highlighted
     * @param $Course
     *
     * @return bool
     */
    public function updateMinimumGradeCount(
        TblMinimumGradeCount $tblMinimumGradeCount, $Count, ?TblGradeType $tblGradeType, $Period, $Highlighted, $Course
    ): bool {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblMinimumGradeCount $Entity */
        $Entity = $Manager->getEntityById('TblMinimumGradeCount', $tblMinimumGradeCount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setCount($Count);
            $Entity->setTblGradeType($tblGradeType);
            $Entity->setPeriod($Period);
            $Entity->setHighlighted($Highlighted);
            $Entity->setCourse($Course);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblType $tblSchoolType
     * @param int $Level
     * @param TblSubject $tblSubject
     *
     * @return TblMinimumGradeCount[]|false
     */
    public function getMinimumGradeCountListBySchoolTypeAndLevelAndSubject(TblType $tblSchoolType, int $Level, TblSubject $tblSubject)
    {
        $resultList = array();
        if (($tempList = $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblMinimumGradeCountLevelLink', array(
            TblMinimumGradeCountLevelLink::ATTR_SERVICE_TBL_SCHOOL_TYPE => $tblSchoolType->getId(),
            TblMinimumGradeCountLevelLink::ATTR_LEVEL => $Level
        )))) {
            /** @var TblMinimumGradeCountLevelLink $temp */
            foreach ($tempList as $temp) {
                if (($tblMinimumGradeCount = $temp->getMinimumGradeCount())
                    && (!$this->getMinimumGradeCountSubjectLinkByMinimumGradeCount($tblMinimumGradeCount)
                        || $this->getMinimumGradeCountSubjectLinkByMinimumGradeCountAndSubject($tblMinimumGradeCount, $tblSubject))
                ) {
                    $resultList[$tblMinimumGradeCount->getId()] = $tblMinimumGradeCount;
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }
}