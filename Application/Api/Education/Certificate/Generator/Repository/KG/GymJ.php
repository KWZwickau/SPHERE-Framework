<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\KG;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class GymJ extends Style
{
    /**
     * @return array
     */
    public function selectValuesTransfer(): array
    {
        return array(
            1 => "wird versetzt",
            2 => "wird nicht versetzt"
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null) : Page
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice($this->getCustomHeader('Jahreszeugnis'))
            ->addSlice($this->getCustomDivisionYearStudent($personId))
            ->addSliceArray($this->getCustomRating($personId))
            ->addSlice($this->getCustomSubjectLanes($personId, array('Lane' => 1, 'Rank' => 3)))
            ->addSliceArray($this->getCustomTeamExtra($personId))
            ->addSliceArray($this->getCustomRemark($personId))
            ->addSlice($this->getCustomAbsence($personId))
            ->addSlice($this->getCustomTransfer($personId))
            ->addSlice($this->getCustomSignPart($personId, true))
            ->addSlice($this->getCustomDateLine($personId));
    }
}