<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

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

        $school = $this->getCustomSchoolName('Allgemeinbildendes Gymnasium');
        $title = 'Halbjahresinformation des Gymnasiums';

        return (new Page())
            ->addSlice($this->getHeader($school, $title))
            ->addSlice($this->getDivisionYearStudent($personId, '1. Schulhalbjahr:'))
            ->addSlice($this->getCustomGradeLanes($personId, '0px'))
            ->addSlice($this->getSliceSpace('50px'))
            ->addSlice($this->getCustomSubjectLanes($personId, true, array('Lane' => 1, 'Rank' => 3))->styleHeight('280px'))
            ->addSlice($this->getCustomProfile($personId))
            ->addSlice($this->getCustomRemark($personId))
            ->addSlice($this->getCustomAbsence($personId))
            ->addSlice($this->getCustomDateLine($personId))
            ->addSlice($this->getCustomSignPart($personId, false))
            ->addSlice($this->getCustomParentSign())
            ->addSlice($this->getCustomInfo());
    }
}