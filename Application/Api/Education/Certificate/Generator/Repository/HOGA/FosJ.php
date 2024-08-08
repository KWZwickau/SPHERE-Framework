<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class FosJ extends Style
{
    /**
     * @return array
     */
    public function selectValuesJobGradeText()
    {
        return array(
            1 => "bestanden",
            2 => "nicht bestanden"
        );
    }

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

        $school = $this->getCustomSchoolName('Berufliches Schulzentrum');

        return (new Page())
            ->addSlice($this->getHeader($school))
            ->addSlice($this->getCustomFosTitle($personId, 'Jahreszeugnis'))
            ->addSlice($this->getCustomFosDivisionYearStudent($personId, 'Schuljahr'))
            ->addSlice($this->getCustomFosSubjectLanes($personId, '10px', true)->styleHeight('270px'))
            ->addSlice($this->getCustomFosRemark($personId))
            ->addSlice($this->getCustomFosTransfer($personId))
            ->addSlice($this->getCustomFosSignPart($personId))
            ->addSlice($this->getCustomFosParentSign())
            ->addSlice($this->getCustomFosInfo());
    }
}