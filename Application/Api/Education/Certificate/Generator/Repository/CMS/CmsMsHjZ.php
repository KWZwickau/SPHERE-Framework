<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CMS;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CmsMsHjZ
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\CMS
 */
class CmsMsHjZ extends CmsStyle
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
            ->addSlice(
                (new CmsMsHj(
                    $this->getTblStudentEducation() ?: null,
                    $this->getTblPrepareCertificate() ?: null,
                    $this->isSample()
                ))->getCmsMsHjSlice($personId, 'Halbjahreszeugnis der Oberschule')
            );
    }
}