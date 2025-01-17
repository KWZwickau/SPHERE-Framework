<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\LWSZ;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class LwszGsJOne
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\LWSZ
 */
class LwszGsJOne extends Certificate
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $pageList[] = (new Page())
            ->addSlice(LwszGsStyle::getHeader($this->isSample()))
            ->addSlice($this->getSchoolName($personId))
            ->addSlice($this->getCertificateHead('Jahreszeugnis der Grundschule'))
            ->addSlice($this->getDivisionAndYear($personId))
            ->addSlice($this->getStudentName($personId))
            ->addSlice((new Slice())
                ->addElement((new Element()))
                ->styleHeight('395px')
            )
            ->addSlice($this->getDescriptionHead($personId, true))
            ->addSlice($this->getDescriptionContent($personId, '80px', '17px'))
            ->addSlice((new Slice())
                ->addElement((new Element()))
                ->styleHeight('23px')
            )
            ->addSlice($this->getDateLine($personId))
            ->addSlice($this->getSignPart($personId, true))
            ->addSlice(LwszGsStyle::getParentSign())
        ;

        $pageList[] = LwszGsStyle::buildSecondPage($this, $tblPerson);

        return $pageList;
    }
}