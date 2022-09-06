<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class BfsAbg
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class BfsAbg extends BfsStyle
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
            ->addSlice($this->getSubjectLineBaseAbg($personId, $this->getCertificateEntity(), 'Berufsübergreifender Bereich', -1, 5, '220px', 1, 4))
            ->addSlice($this->getSubjectLineBaseAbg($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich', 1, 10))
        ;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId, 'Abgangszeugnis'))
            ->addSlice($this->getSubjectLineBaseAbg($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich (Fortsetzung)', 11, 6, 'auto'))
            ->addSlice($this->getSubjectLineBaseAbg($personId, $this->getCertificateEntity(), 'Wahlpflichtbereich', 1, 2, 'auto', 13, 14))
            ->addSlice($this->getPraktikaAbg($personId, $this->getCertificateEntity()))
            ->addSlice($this->getDescriptionBsContent($personId, '85px'))
            ->addSlice($this->getSpace('120px'))
            ->addSlice($this->getIndividuallySignPart($personId, true))
            ->addSlice($this->getBsInfo('85px',
                'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        return $pageList;
    }
}