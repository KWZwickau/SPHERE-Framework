<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.11.2018
 * Time: 10:53
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class RadebeulOsHalbjahresinformation
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR
 */
class RadebeulOsHalbjahresinformation extends Certificate
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice(RadebeulOsJahreszeugnis::getHeader('Halbjahresinformation'))
            ->addSliceArray((new RadebeulOsJahreszeugnis($this->getTblDivision()))->getBody($personId, false));
    }
}