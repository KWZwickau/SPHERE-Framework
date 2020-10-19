<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class BfsJ
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class BfsJ extends BfsStyle
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
            ->addSlice($this->getSchoolHead($personId, 'Jahreszeugnis', true))
            ->addSlice($this->getStudentHead($personId, 'Schuljahr', 'folgende Leistungen erreicht:', true))
            ->addSlice($this->getSubjectLineAcross($personId, $this->getCertificateEntity()))
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich', 1, 10))
        ;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId, 'Endjahresinformation'))
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich (Fortsetzung)', 11, 4, true, '220px'))
            ->addSlice($this->getSubjectLineChosen($personId, $this->getCertificateEntity()))
            ->addSlice($this->getPraktika($personId, $this->getCertificateEntity()))
            ->addSlice($this->getDescriptionBsContent($personId, '77px'))
            ->addSlice($this->getTransfer($personId))
            ->addSlice((new Slice())->addElement((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('102px')
            ))
            ->addSlice($this->getIndividuallySignPart($personId))
            ->addSlice($this->getBsInfo('20px',
                'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        return $pageList;
    }
}