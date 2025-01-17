<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\LWSZ;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class LwszGsJ
 *
 * @package Application\Api\Education\Certificate\Generator\Repository\LWSZ
 */
class LwszGsJ extends Certificate
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
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $pageList[] = (new Page())
            ->addSlice(LwszGsStyle::getHeader($this->isSample()))
            ->addSlice($this->getSchoolName($personId))
            ->addSlice($this->getCertificateHead('Jahreszeugnis der Grundschule'))
            ->addSlice($this->getDivisionAndYear($personId))
            ->addSlice($this->getStudentName($personId))
            ->addSlice($this->getGradeLanesSmall($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Einschätzung:')
                )
                ->styleMarginTop('5px')
            )
            ->addSlice($this->getRatingContent($personId, '100px', '0px', ''))
            ->addSlice((new Slice())
                ->addElement(( new Element() )
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleMarginBottom('5px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanesSmall($personId)
                ->styleHeight('130px'))
            ->addSlice((new Slice())
                ->addElement((new Element()))
                ->styleHeight('50px')
            )
            ->addSlice($this->getDescriptionHead($personId, true))
            ->addSlice($this->getDescriptionContent($personId, '80px', '17px'))
            ->addSlice($this->getTransfer($personId))
            ->addSlice($this->getDateLine($personId))
            ->addSlice($this->getSignPart($personId, true))
            ->addSlice(LwszGsStyle::getParentSign())
            ->addSlice($this->getInfo('10px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend
                (6 = ungenügend nur bei der Bewertung der Leistungen)')
            );

        $pageList[] = LwszGsStyle::buildSecondPage($this, $tblPerson);

        return $pageList;
    }
}
