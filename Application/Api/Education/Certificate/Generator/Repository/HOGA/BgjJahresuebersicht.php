<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class BgjJahresuebersicht extends Style
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
            ->addSlice($this->getCustomBgjTitle('Jahresübersicht', 'zu den Fächern entsprechend der Fachoberschule'))
            ->addSlice($this->getDivisionYearStudentBgj($personId, 'Schuljahr'))
            ->addSlice($this->getCustomSubjectLanesBgj($personId)->styleHeight('450px'))
            ->addSlice($this->getCustomRemarkBgj($personId, '2px'))
            ->addSlice($this->getCustomSignPartBgj($personId))
            ->addSlice($this->getCustomInfoBgj());
    }
}