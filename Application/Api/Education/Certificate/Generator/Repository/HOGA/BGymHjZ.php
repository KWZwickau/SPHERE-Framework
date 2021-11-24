<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class BGymHjZ extends Style
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null): Page
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $school[] = 'Berufliches Schulzentrum';
        $school[] = 'der HOGA Schloss Albrechtsberg g SchulgmbH';
        $school[] = 'Staatlich anerkannte Schulen in freier Trägerschaft';

        return (new Page())
            ->addSlice($this->getHeaderBGym($school))
            ->addSlice($this->getTitleBGym('Halbjahreszeugnis', 'des Beruflichen Gymnasiums', '10px'))
            ->addSlice($this->getSubjectArea($personId))
            ->addSlice($this->getDivisionYearStudent($personId, 'Schulhalbjahr:', '10px', true))
            ->addSlice($this->getCustomSubjectLanesBGym($personId, 'Schulhalbjahr'))
            ->addSlice($this->getCustomChosenLanesBGym($personId))
            ->addSlice($this->getCustomRemark($personId, '5px', '100px'))
            ->addSlice($this->getSliceSpace('25px'))
            ->addSlice($this->getCustomDateLine($personId, '2px'))
            ->addSlice($this->getCustomSignPart($personId, true))
            ->addSlice($this->getCustomParentSign())
            ->addSlice($this->getCustomInfo())
            ;
    }
}