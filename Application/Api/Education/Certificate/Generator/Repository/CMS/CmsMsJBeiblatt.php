<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.05.2019
 * Time: 10:50
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CMS;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CmsMsJBeiblatt
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\CMS
 */
class CmsMsJBeiblatt extends CmsStyle
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new CmsMsHjBeiblatt(
            $this->getTblStudentEducation() ?: null,
            $this->getTblPrepareCertificate() ?: null,
            $this->isSample()
        ))->getPage($personId, 'Beiblatt zum Zeugnis', true);
    }
}