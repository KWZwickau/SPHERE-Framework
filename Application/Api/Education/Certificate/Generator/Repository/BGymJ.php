<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

class BGymJ extends BGymStyle
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

        $isMissing = GatekeeperConsumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'EMSP');

        return (new Page())
            ->addSlice($this->getHeaderBGym('Jahreszeugnis'))
            ->addSlice($this->getSubjectArea($personId))
            ->addSlice($this->getLevelYearStudent($personId, 'Schuljahr'))
            ->addSlice($this->getSubjectLanesBGym($personId, 'Schuljahr'))
            ->addSlice($this->getChosenLanesBGym($personId))
            ->addSlice($this->getRemarkBGym($personId, $isMissing))
            ->addSlice($this->getTransferBGym($personId))
            ->addSlice($this->getSignPartBGym($personId))
            ->addSlice($this->getGradeLevel())
            ;
    }
}