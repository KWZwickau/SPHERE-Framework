<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\GsHjOneInfo;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CswGsOneHjInfo
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW
 */
class CswGsOneHjInfo extends Certificate
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $pageList = array();
        $pageList[] = (new GsHjOneInfo(
            $this->getTblDivision() ? $this->getTblDivision() : null,
            $this->getTblPrepareCertificate() ? $this->getTblPrepareCertificate() : null,
            $this->isSample()
        ))->buildPages($tblPerson);

        $pageList[] = CswGsStyle::buildSecondPage($tblPerson);

        return $pageList;
    }
}