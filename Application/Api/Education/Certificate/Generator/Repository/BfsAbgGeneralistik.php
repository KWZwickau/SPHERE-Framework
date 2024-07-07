<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class BfsAbgGeneralistik extends BfsStyle
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
            ->addSlice($this->getSchoolHeadAbg($personId))
            ->addSlice($this->getStudentHeadAbg($personId))
            ->addSlice($this->getSubjectLineDuty())
            ->addSlice($this->getSubjectLineAcrossAbs($personId, $this->getCertificateEntity(), 'Berufsübergreifender Bereich', 1, 4, 1, 4, '130px'))
            ->addSlice($this->getSubjectLineAcrossAbs($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich', 1, 24, 5, 12))
        ;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId, 'Abgangszeugnis'))
            ->addSlice($this->getSubjectLineAcrossAbs($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich (Fortsetzung)', 24, 8, 5, 12, '185px'))
            ->addSlice($this->getSubjectLineAcrossAbs($personId, $this->getCertificateEntity(), 'Wahlpflichtbereich', 1, 4, 13, 14, '130px'))
            ->addSlice($this->getPraktikaAbg($personId, $this->getCertificateEntity(), 4))
            ->addSlice($this->getDescriptionBsContent($personId, '85px'))
            ->addSlice($this->getSpace('200px'))
            ->addSlice($this->getIndividuallySignPart($personId, false, false, false))
            ->addSlice($this->getBsInfo('20px',
                'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        return $pageList;
    }
}