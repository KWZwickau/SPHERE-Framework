<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSC;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CosJPri
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class CosJPri extends Certificate
{

    const TEXT_SIZE = '13px';

    /**
     * @param TblPerson|null $tblPerson
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $gradeLanesSlice = $this->getGradeLanesCoswig($personId, self::TEXT_SIZE, false, '25px');
        $subjectLanesSlice = $this->getSubjectLanesCoswig($personId, true, array(), self::TEXT_SIZE,
            false);

        return CosHjPri::buildContentPage($personId, $this->isSample(), 'Jahreszeugnis der Schule (Primarstufe)',
            $gradeLanesSlice, $subjectLanesSlice
        );
    }
}
