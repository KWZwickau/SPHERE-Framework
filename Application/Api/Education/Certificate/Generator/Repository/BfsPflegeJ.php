<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class BfsPflegeJ
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class BfsPflegeJ extends BfsStyle
{

    /**
     * @return array
     */
    public function getApiModalColumns()
    {
        return array(
            'Subarea1'            => 'Teilbereich 1',
            'SubareaTimeH1'       => 'Teilbereich 1 Dauer der Ausbildung (h)',
            'SubareaTimeHDone1'   => 'Teilbereich 1 Davon anwesend (h)',
            'Subarea2'            => 'Teilbereich 2',
            'SubareaTimeH2'       => 'Teilbereich 2 Dauer der Ausbildung (h)',
            'SubareaTimeHDone2'   => 'Teilbereich 2 Davon anwesend (h)',
            'Subarea3'            => 'Teilbereich 3',
            'SubareaTimeH3'       => 'Teilbereich 3 Dauer der Ausbildung (h)',
            'SubareaTimeHDone3'   => 'Teilbereich 3 Davon anwesend (h)',
            'Subarea4'            => 'Teilbereich 4',
            'SubareaTimeH4'       => 'Teilbereich 4 Dauer der Ausbildung (h)',
            'SubareaTimeHDone4'   => 'Teilbereich 4 Davon anwesend (h)',
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
            ->addSlice($this->getSchoolHead($personId, 'Jahreszeugnis', false, false, true))
            ->addSlice($this->getOccupation($personId))
            ->addSlice($this->getStudentHead($personId, 'Schuljahr', 'folgende Leistungen erreicht:', true, false))
            ->addSlice($this->getSubjectLineDuty())
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(), 'Berufsübergreifender Bereich', 1, 1))
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich', 1, 10))
        ;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId, 'Jahreszeugnis', false))
            ->addSlice($this->getSubjectLineChosen($personId, $this->getCertificateEntity(), '200px', 4))
            ->addSlice($this->getYearGradeAverage($personId))
            ->addSlice($this->getMidTerm($personId))
            ->addSlice($this->getPracticalCare($personId, $this->getCertificateEntity()))
            ->addSlice($this->getDescriptionBsContent($personId, '112px', true))
//            ->addSlice($this->getAbsence($personId))
            ->addSlice((new Slice())->addElement((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('79px')
            ))
            ->addSlice($this->getIndividuallySignPart($personId, false, true))
            ->addSlice($this->getBsInfo('20px',
                'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        return $pageList;
    }
}