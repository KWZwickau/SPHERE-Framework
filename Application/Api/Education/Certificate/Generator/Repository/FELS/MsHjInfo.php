<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\FELS;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class MsHjInfo
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\FELS
 */
class MsHjInfo extends Certificate
{
    /**
     * @param TblPerson|null $tblPerson
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice(FelsStyle::getHeader($this->isSample(), 'Oberschule'))
//            ->addSlice($this->getSchoolName($personId))
            ->addSlice($this->getCertificateHead('Halbjahresinformation der Oberschule'))
            ->addSlice($this->getDivisionAndYear($personId, '20px', '1. Schulhalbjahr'))
            ->addSlice($this->getStudentName($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleTextSize('12px')
                    ->styleMarginTop('8px')
                )
            )
            ->addSlice($this->getGradeLanesSmall($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleMarginBottom('5px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanesSmall(
                $personId,
                true,
                array(),
                '14px',
                false,
                false,
                true
            )->styleHeight('220px'))
            ->addSlice(FelsStyle::getCustomDescription($personId, '180px', '15px', true, $this->getTblPrepareCertificate() ?: null))
//            ->addSlice($this->getDescriptionHead($personId, true))
//            ->addSlice($this->getDescriptionContent($personId, '178px', '15px'))
            ->addSlice($this->getDateLine($personId))
            ->addSlice($this->getSignPart($personId, false))
            ->addSlice($this->getParentSign())
            ->addSlice($this->getInfo('45px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend 
                    (6 = ungenügend nur bei der Bewertung der Leistungen)')
            );
    }
}