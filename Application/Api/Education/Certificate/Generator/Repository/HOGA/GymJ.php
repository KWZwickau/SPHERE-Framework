<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class GymJ extends Style
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

        $title = 'Jahreszeugnis des Gymnasiums';
        $school = $this->getCustomSchoolName('Allgemeinbildendes Gymnasium');

        return (new Page())
            ->addSlice($this->getHeader($school, $title))
            ->addSlice($this->getDivisionYearStudent($personId, 'Schuljahr:'))
            ->addSlice($this->getCustomGradeLanes($personId, '0px'))
            ->addSlice($this->getCustomRating($personId))
            ->addSlice($this->getCustomSubjectLanes($personId, true, array('Lane' => 1, 'Rank' => 3))->styleHeight('280px'))
            ->addSlice($this->getCustomProfile($personId))
            ->addSlice($this->getCustomTeamExtra($personId))
            ->addSlice($this->getCustomRemark($personId, '5px', '75px'))
            ->addSlice($this->getCustomAbsence($personId))
            ->addSlice($this->getCustomTransfer($personId))
            ->addSlice($this->getCustomDateLine($personId, '2px'))
            ->addSlice($this->getCustomSignPart($personId, true))
            ->addSlice($this->getCustomParentSign())
            ->addSlice($this->getCustomInfo());
    }
}