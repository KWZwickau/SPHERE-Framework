<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HGGT;

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
     * @return Page|Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice($this->getCustomHeader($this->isSample(), 'Jahreszeugnis'))
            ->addSlice($this->getCustomDivisionAndYear($personId, 'Schuljahr'))
            ->addSlice($this->getCustomRatingContent($personId))
            ->addSlice($this->getCustomGradeLanes($personId))
            ->addSlice($this->getCustomSubjectLanes($personId))
            ->addSlice($this->getCustomRemark($personId))
            ->addSlice($this->getCustomMissing($personId))
            ->addSlice($this->getCustomTransfer($personId, false))
            ->addSlice($this->getCustomDateLine($personId))
            ->addSlice($this->getCustomSignPart($personId))
            ->addSlice($this->getCustomParentSign());
    }
}