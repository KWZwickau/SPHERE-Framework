<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Graduation\Grade\Service\Data;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCount;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCountLevelLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCountSubjectLink;

abstract class ServiceMinimumGradeCount extends ServiceGradeType
{
    /**
     * @param $Id
     *
     * @return false|TblMinimumGradeCount
     */
    public function getMinimumGradeCountById($Id)
    {
        return (new Data($this->getBinding()))->getMinimumGradeCountById($Id);
    }

    /**
     * @return false|TblMinimumGradeCount[]
     */
    public function getMinimumGradeCountAll()
    {
        return (new Data($this->getBinding()))->getMinimumGradeCountAll();
    }

    /**
     * @return array
     */
    public function migrateMinimumGradeCounts(): array
    {
        return (new Data($this->getBinding()))->migrateMinimumGradeCounts();
    }

    /**
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     *
     * @return false|TblMinimumGradeCountLevelLink[]
     */
    public function getMinimumGradeCountLevelLinkByMinimumGradeCount(TblMinimumGradeCount $tblMinimumGradeCount)
    {
        return (new Data($this->getBinding()))->getMinimumGradeCountLevelLinkByMinimumGradeCount($tblMinimumGradeCount);
    }

    /**
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     *
     * @return false|TblMinimumGradeCountSubjectLink[]
     */
    public function getMinimumGradeCountSubjectLinkByMinimumGradeCount(TblMinimumGradeCount $tblMinimumGradeCount)
    {
        return (new Data($this->getBinding()))->getMinimumGradeCountSubjectLinkByMinimumGradeCount($tblMinimumGradeCount);
    }
}