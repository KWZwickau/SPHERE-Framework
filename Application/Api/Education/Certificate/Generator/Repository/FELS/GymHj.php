<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\FELS;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class GymHj extends Certificate
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
            ->addSlice(FelsStyle::getHeader($this->isSample(), 'Gymnasium'))
//            ->addSlice($this->getSchoolName($personId))
            ->addSlice($this->getCertificateHead('Halbjahreszeugnis des Gymnasiums'))
            ->addSlice($this->getDivisionAndYear($personId, '20px', '1. Schulhalbjahr'))
            ->addSlice($this->getStudentName($personId))
            ->addSlice($this->getGradeLanesSmall($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleMarginBottom('5px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanesSmall($personId, true, array('Lane' => 1, 'Rank' => 3))->styleHeight('220px'))
            ->addSlice($this->getProfileStandardNew($personId, '14px', false, true, false))
            ->addSlice($this->getDescriptionHead($personId, true))
            ->addSlice($this->getDescriptionContent($personId, '160px', '15px'))
            ->addSlice($this->getDateLine($personId))
            ->addSlice($this->getSignPart($personId))
            ->addSlice($this->getParentSign())
            ->addSlice($this->getInfo('20px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft;
                                          6 = ungenügend (6 = ungenügend nur bei der Bewertung der Leistungen)'
            ));
    }
}