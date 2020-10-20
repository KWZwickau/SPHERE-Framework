<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class BfsAbs
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class BfsAbs extends BfsStyle
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

            ->addSlice($this->getSchoolHead($personId, 'Abschlusszeugnis', true, true))
            ->addSlice($this->getStudentHeadAbs($personId))
            ->addSlice((new Slice())->addElement((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('330px')
            ))
            ->addSlice($this->getIndividuallySignPart($personId, true))

//            ->addSlice($this->getSubjectLineAcross($personId, $this->getCertificateEntity()))
//            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich', 1, 10))
        ;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId, 'Abschlusszeugnis', true))
            ->addSlice($this->getSubjectLinePerformance())
            ->addSlice($this->getSubjectLineDuty())
            ->addSlice($this->getSubjectLineAcross($personId, $this->getCertificateEntity(), 'Berufsübergreifender Bereich', 1, 6, false, 1, 4))
            ->addSlice($this->getSubjectLineAcross($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich', 1, 14, true, 5, 12, '310px'))
            ->addSlice($this->getSubjectLineAcross($personId, $this->getCertificateEntity(), 'Wahlpflichtbereich', 1, 14, false, 13, 14, '100px'))
//            ->addSlice($this->getSubjectLineChosen($personId, $this->getCertificateEntity()))
            ->addSlice($this->getPraktika($personId, $this->getCertificateEntity()))
            ->addSlice($this->getDescriptionBsContent($personId, '85px'))
            ->addSlice((new Slice())->addElement((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('11px')
            ))
            ->addSlice($this->getBsInfo('20px',
                'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        return $pageList;
    }
}