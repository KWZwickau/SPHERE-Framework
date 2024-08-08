<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class FosHjZ extends Style
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null) : Page
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $school = $this->getCustomSchoolName('Berufliches Schulzentrum');

        return (new Page())
            ->addSlice($this->getHeader($school))
            ->addSlice($this->getCustomFosTitle($personId, 'Halbjahreszeugnis'))
            ->addSlice($this->getCustomFosDivisionYearStudent($personId, '1. Schulhalbjahr'))
            ->addSlice($this->getCustomFosSubjectLanes($personId, '10px', false)->styleHeight('270px'))
            ->addSlice($this->getCustomFosRemark($personId, '10px', '100px'))
            ->addSlice($this->getCustomFosSignPart($personId))
            ->addSlice($this->getCustomFosParentSign())
            ->addSlice($this->getCustomFosInfo());
    }
}