<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class BfsHjInfo
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class BfsHjInfo extends BfsStyle
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
            ->addSlice($this->getSchoolHead($personId, 'Halbjahresinformation'))
            ->addSlice($this->getStudentHead($personId, 'Schulhalbjahr', 'folgende Leistungen erreicht:', true))
            ->addSlice($this->getSubjectLineDuty())
            ->addSlice($this->getSubjectLineAcross($personId, $this->getCertificateEntity(), 'Berufsübergreifender Bereich', 1, 6, false))
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(),'Berufsbezogener Bereich', 1, 10))
        ;

//        Debugger::screenDump($pageList[0]->getContent());
//        exit;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId))
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich (Fortsetzung)', 11, 4, true, '220px'))
            ->addSlice($this->getSubjectLineChosen($personId, $this->getCertificateEntity()))
            ->addSlice($this->getPraktika($personId, $this->getCertificateEntity()))
            ->addSlice($this->getDescriptionBsContent($personId))
            ->addSlice((new Slice())->addElement((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('123px')
            ))
            ->addSlice($this->getBottomInformation($personId))
            ->addSlice($this->getBsInfo('20px',
                'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        return $pageList;
    }
}
