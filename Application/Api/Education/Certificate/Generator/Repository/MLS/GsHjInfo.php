<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\MLS;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class GsHjInfo extends Style
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null): Page
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page)
            ->addSlice(self::getCustomHead())
            ->addSlice(self::getCustomDivisionAndYear($personId, '1. Schulhalbjahr'))
            ->addSlice($this->getCustomStudentName($personId))
            ->addSlice($this->getCustomGradeLanes($personId))
            ->addSlice($this->getCustomSubjectLanes($personId))
            ->addSlice($this->getCustomRemark($personId))
            ->addSlice($this->getCustomDateLine($personId))
            ->addSlice($this->getSignPart($personId, false, '15px'))
            ->addSlice($this->getParentSign('15px'));
    }
}