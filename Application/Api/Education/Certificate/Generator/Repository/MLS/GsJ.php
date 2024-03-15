<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\MLS;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class GsJ extends Style
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

        return (new Page)
            ->addSlice(self::getCustomHead('Jahreszeugnis der Grundschule'))
            ->addSlice(self::getCustomDivisionAndYear($personId, ''))
            ->addSlice($this->getCustomStudentName($personId))
            ->addSlice($this->getCustomGradeLanes($personId))
            ->addSlice($this->getCustomRating($personId))
            ->addSlice($this->getCustomSubjectLanes($personId))
            ->addSlice($this->getCustomRemark($personId, '220px'))
            ->addSlice($this->getCustomTransfer($personId))
            ->addSlice($this->getCustomDateLine($personId, '20px'))
            ->addSlice($this->getSignPart($personId, true, '15px'))
            ->addSlice($this->getParentSign('15px'));
    }
}