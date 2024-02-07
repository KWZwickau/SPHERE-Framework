<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class BGymHjZ extends BGymStyle
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null): Page
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice($this->getHeaderBGym('Halbjahreszeugnis'))
            ->addSlice($this->getSubjectArea($personId))
            ->addSlice($this->getLevelYearStudent($personId, '1. Schulhalbjahr'))
            ->addSlice($this->getSubjectLanesBGym($personId, '1. Schulhalbjahr'))
            ->addSlice($this->getChosenLanesBGym($personId))
            ->addSlice($this->getRemarkBGym($personId, false))
            ->addSlice((new Slice())->styleHeight('20px'))
            ->addSlice($this->getSignPartBGym($personId))
            ->addSlice($this->getGradeLevel())
            ;
    }
}