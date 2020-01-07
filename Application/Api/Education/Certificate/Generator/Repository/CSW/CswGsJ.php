<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\GsJa;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CswGsJ
 *
 * @package Application\Api\Education\Certificate\Generator\Repository\CSW
 */
class CswGsJ extends Certificate
{

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
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $pageList = array();
        $pageList[] = (new GsJa(
            $this->getTblDivision() ? $this->getTblDivision() : null,
            $this->getTblPrepareCertificate() ? $this->getTblPrepareCertificate() : null,
            $this->isSample()
        ))->buildPages($tblPerson);

        $pageList[] = CswGsStyle::buildSecondPage($tblPerson);

        return $pageList;
    }
}
