<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\MLS;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class GsHjInfoOne extends Style
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null): Page
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page)
            ->addSlice(self::getCustomHead())
            ->addSlice(self::getCustomDivisionAndYear($personId, '1. Schulhalbjahr'))
            ->addSlice($this->getCustomStudentName($personId))
            ->addSlice($this->getCustomRemarkWithoutHeader($personId, '580px'))
            ->addSlice($this->getCustomAbsence($personId))
            ->addSlice($this->getCustomDateLine($personId, '10px'))
            ->addSlice($this->getSignPart($personId, false, '15px'))
            ->addSlice($this->getParentSign('15px'));
    }
}