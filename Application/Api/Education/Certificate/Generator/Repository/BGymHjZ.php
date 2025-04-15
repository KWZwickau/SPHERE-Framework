<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

class BGymHjZ extends BGymStyle
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null): Page
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $isMissing = GatekeeperConsumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'EMSP');

        return (new Page())
            ->addSlice($this->getHeaderBGym('Halbjahreszeugnis'))
            ->addSlice($this->getSubjectArea($personId))
            ->addSlice($this->getLevelYearStudent($personId, '1. Schulhalbjahr'))
            ->addSlice($this->getSubjectLanesBGym($personId, '1. Schulhalbjahr'))
            ->addSlice($this->getChosenLanesBGym($personId))
            ->addSlice($this->getRemarkBGym($personId, $isMissing))
            ->addSlice((new Slice())->styleHeight('20px'))
            ->addSlice($this->getSignPartBGym($personId))
            ->addSlice($this->getGradeLevel())
            ;
    }
}