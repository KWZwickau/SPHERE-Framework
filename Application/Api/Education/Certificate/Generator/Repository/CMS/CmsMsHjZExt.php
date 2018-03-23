<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CMS;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CmsMsHjZExt
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\CMS
 */
class CmsMsHjZExt extends CmsStyle
{

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new CmsMsHjExt)->getCmsMsHjPageList($personId, 'Halbjahreszeugnis der Oberschule');
    }
}