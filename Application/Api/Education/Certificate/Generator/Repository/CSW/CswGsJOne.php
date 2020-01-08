<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\GsJOne;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CswGsJOne
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW
 */
class CswGsJOne extends Certificate
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $pageList = array();
        $pageList[] = (new GsJOne(
            $this->getTblDivision() ? $this->getTblDivision() : null,
            $this->getTblPrepareCertificate() ? $this->getTblPrepareCertificate() : null,
            $this->isSample()
        ))->buildPages($tblPerson);

        $pageList[] = CswGsStyle::buildSecondPage($tblPerson);

        return $pageList;
    }
}