<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class BGymJ extends BGymStyle
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
    public function buildPages(TblPerson $tblPerson = null): Page
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice($this->getHeaderBGym('Jahreszeugnis'))
            ->addSlice($this->getSubjectArea($personId))
            ->addSlice($this->getLevelYearStudent($personId, 'Schuljahr'))
            ->addSlice($this->getSubjectLanesBGym($personId, 'Schuljahr'))
            ->addSlice($this->getChosenLanesBGym($personId))
            ->addSlice($this->getRemarkBGym($personId, false))
            ->addSlice($this->getTransferBGym($personId))
            ->addSlice($this->getSignPartBGym($personId))
            ->addSlice($this->getGradeLevel())
            ;
    }
}