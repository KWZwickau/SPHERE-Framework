<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service;

use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCount;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCountLevelLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCountSubjectLink;

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
}