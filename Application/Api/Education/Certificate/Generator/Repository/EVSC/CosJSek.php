<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSC;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CosJSek
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class CosJSek extends Certificate
{

    const TEXT_SIZE = '13px';

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $gradeLanesSlice = $this->getGradeLanesCoswig($personId, self::TEXT_SIZE, false, '10px');
        $subjectLanesSlice = $this->getSubjectLanesCoswig($personId, true, array(), self::TEXT_SIZE,
            false);
        $obligationToVotePart = $this->getObligationToVotePartCustomForCoswig($personId,
            self::TEXT_SIZE);

        return CosHjSek::buildContentPage($personId, $this->isSample(), 'Jahreszeugnis der Schule (Sekundarstufe)',
            'Schuljahr', $gradeLanesSlice, $subjectLanesSlice, $obligationToVotePart
        );
    }
}