<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class MsHj
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class MsHj extends Certificate
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

        $Header = $this->getHead($this->isSample(), true, 'auto', '50px');

        return (new Page())
                ->addSlice(
                    $Header
                )
                ->addSlice($this->getSchoolName($personId))
                ->addSlice($this->getCertificateHead('Halbjahreszeugnis'))
                ->addSlice($this->getDivisionAndYear($personId, '20px', '1. Schulhalbjahr'))
                ->addSlice($this->getStudentName($personId))
                // für selbe Höhe wie bei Varianten mit Bildungsgang
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('&nbsp;')
                        ->styleTextSize('12px')
                        ->styleMarginTop('8px')
                    )
                )
                ->addSlice($this->getGradeLanes($personId))
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Leistungen in den einzelnen Fächern:')
                        ->styleMarginTop('15px')
                        ->styleTextBold()
                    )
                )
                ->addSlice($this->getSubjectLanes($personId)->styleHeight('270px'))
                ->addSlice($this->getOrientationStandard($personId))
                ->addSlice($this->getDescriptionHead($personId, true))
                ->addSlice($this->getDescriptionContent($personId, '85px', '15px'))
                ->addSlice($this->getDateLine($personId))
                ->addSlice($this->getSignPart($personId))
                ->addSlice($this->getParentSign())
                ->addSlice($this->getInfo('25px',
                    'Notenerläuterung:',
                    '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend 
                    (6 = ungenügend nur bei der Bewertung der Leistungen)')
        );
    }
}
