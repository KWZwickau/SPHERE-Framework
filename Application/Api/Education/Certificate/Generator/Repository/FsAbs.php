<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class FsAbs
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class FsAbs extends FsStyle
{

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $Page = (new Page());
        $Page->addSlice($this->getSchoolHeadAbs($personId));
        $Page->addSlice($this->getStudentHeadAbs($personId));
//                ->addSlice($this->getIndividuallySignPart($personId, true))

//            ->addSlice($this->getSubjectLineDuty())
//            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(),'Fachrichtungsübergreifender Bereich', 1, 5, false, '200px', 1, 4))
//            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(),'Fachrichtungsbezogener Bereich', 1, 8))
        ;
        //ToDO logik für die Anzeige des Zusatztextes <MITTLERE SCHULABSCHLUSS> auf dem Zeugnis
        if(false){
            $Page->addSlice($this->getSecondarySchoolDiploma($personId));
            $Page->addSlice((new Slice())->addElement((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('45px')
            ));
        } else {
            $Page->addSlice((new Slice())->addElement((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('220px')
            ));
        }
        $Page->addSlice($this->getIndividuallySignPart($personId, true));

        $pageList[] = $Page;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId, 'Jahreszeugnis'))
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(), 'Fachrichtungsbezogener Bereich (Fortsetzung)', 9, 4, true, '170px'))
            ->addSlice($this->getSubjectLineChosen($personId, $this->getCertificateEntity(), '110px'))
            ->addSlice($this->getSubjectLineJobEducation($personId, $this->getCertificateEntity()))
            ->addSlice($this->getFachhochschulreife($personId, $this->getCertificateEntity()))
            ->addSlice($this->getChosenArea($personId))
            ->addSlice($this->getDescriptionBsContent($personId))
            ->addSlice($this->getTransfer($personId))
            ->addSlice((new Slice())->addElement((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('82px')
            ))
            ->addSlice($this->getIndividuallySignPart($personId))
            ->addSlice($this->getFsInfo('20px',
                'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        return $pageList;
    }
}
