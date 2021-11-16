<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class MsJ extends Style
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
    public function buildPages(TblPerson $tblPerson = null) : Page
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $school[] = 'Oberschule der';
        $school[] = 'HOGA Schloss Albrechtsberg g SchulgmbH';
        $school[] = 'Staatlich anerkannte Schule in freier Trägerschaft';

        $title = 'Jahreszeugnis der Oberschule';

        return (new Page())
            ->addSlice($this->getHeader($school, $title))
            ->addSlice($this->getDivisionYearStudent($personId, 'Schuljahr:'))
            ->addSlice($this->getCustomGradeLanes($personId))
            ->addSlice($this->getCustomRating($personId))
            ->addSlice($this->getCustomSubjectLanes($personId, true, array(), false, true)->styleHeight('300px'))
            ->addSlice($this->getCustomElective($personId))
            ->addSlice($this->getCustomTeamExtra($personId))
            ->addSlice($this->getCustomRemark($personId, '2px'))
            ->addSlice($this->getCustomAbsence($personId))
            ->addSlice($this->getCustomTransfer($personId))
            ->addSlice($this->getCustomDateLine($personId, '2px'))
            ->addSlice($this->getCustomSignPart($personId, true))
            ->addSlice($this->getCustomParentSign())
            ->addSlice($this->getCustomInfo());
    }
}