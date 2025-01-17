<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class BGymJ extends Style
{
    /**
     * @return array
     */
    public function selectValuesTransfer()
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

        $school = $this->getCustomSchoolName('Berufliches Schulzentrum');

        return (new Page())
            ->addSlice($this->getHeaderBGym($school))
            ->addSlice($this->getTitleBGym('Jahreszeugnis', 'des Beruflichen Gymnasiums', '10px'))
            ->addSlice($this->getSubjectArea($personId))
            ->addSlice($this->getDivisionYearStudent($personId, 'Schuljahr:', '10px', true, true))
            ->addSlice($this->getCustomSubjectLanesBGym($personId, 'Schuljahr'))
            ->addSlice($this->getCustomChosenLanesBGym($personId))
            ->addSlice($this->getCustomRemark($personId, '5px', '100px'))
            ->addSlice($this->getCustomAbsence($personId))
            ->addSlice($this->getCustomTransfer($personId))
            ->addSlice($this->getCustomDateLine($personId, '2px'))
            ->addSlice($this->getCustomSignPart($personId, true))
            ->addSlice($this->getCustomParentSign())
            ->addSlice($this->getCustomInfo())
        ;
    }
}