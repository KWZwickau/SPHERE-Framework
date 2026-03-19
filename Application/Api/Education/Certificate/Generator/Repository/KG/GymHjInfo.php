<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\KG;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class GymHjInfo extends Style
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null) : Page
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice($this->getCustomHeader('Halbjahresinformation'))
            ->addSlice($this->getCustomDivisionYearStudent($personId))
            ->addSliceArray($this->getCustomRating($personId))
            ->addSlice($this->getCustomSubjectLanes($personId, array('Lane' => 1, 'Rank' => 3)))
            ->addSliceArray($this->getCustomTeamExtra($personId))
            ->addSliceArray($this->getCustomRemark($personId))
            ->addSlice($this->getCustomAbsence($personId))
            ->addSlice($this->getCustomSignPart($personId, false))
            ->addSlice($this->getCustomDateLine($personId));
    }
}