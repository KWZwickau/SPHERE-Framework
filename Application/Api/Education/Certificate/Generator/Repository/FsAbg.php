<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class FsAbg
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class FsAbg extends FsStyle
{

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        // leere Seite
        $pageList[] = new Page();

        $Page = (new Page());
        $Page->addSlice($this->getSchoolHeadAbg($personId));
        $Page->addSlice($this->getStudentHeadAbg($personId));
//        $Page->addSlice($this->getIndividuallySignPart($personId, true));

        $Page->addSlice($this->getSubjectLineDuty());
        $Page->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(),'Fachrichtungsübergreifender Bereich', 1, 5, '200px', 1, 4));
        $Page->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(),'Fachrichtungsbezogener Bereich', 1, 8));
        
//        $Page->addSlice($this->getIndividuallySignPart($personId, true));

        $pageList[] = $Page;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId, 'Abgangszeugnis'))
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(),'Fachrichtungsbezogener Bereich (Fortsetzung)', 9, 11, '384px'))
            ->addSlice($this->getSubjectLineChosen($personId, $this->getCertificateEntity(), '100px'))
            ->addSlice($this->getSubjectLineWrittenTest($personId, '160px'))
            ->addSlice($this->getSubjectLinePractiseTest($personId))
            ->addSlice($this->getSubjectLineJobEducation($personId, $this->getCertificateEntity()))
//            ->addSlice($this->getFachhochschulreife($personId, $this->getCertificateEntity()))
//            ->addSlice($this->getChosenArea($personId))
//            ->addSlice($this->getDescriptionBsContent($personId))
//            ->addSlice($this->getTransfer($personId))
//            ->addSlice((new Slice())->addElement((new Element())
//                ->setContent('&nbsp;')
//                ->stylePaddingTop('82px')
//            ))
//            ->addSlice($this->getIndividuallySignPart($personId))
//            ->addSlice($this->getFsInfo('20px',
//                'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId, 'Abgangszeugnis'))
            ->addSlice($this->getSubjectLineInformationalExpulsion($personId))
            ->addSlice($this->getSubjectLineSkilledWork($personId))
            ->addSlice($this->getFachhochschulreife($personId, $this->getCertificateEntity()))
            ->addSlice($this->getChosenArea($personId))
            ->addSlice($this->getDescriptionFsContent($personId, '40px'))
            ->addSlice($this->getIndividuallySignPart($personId, false, true))
//            ->addSlice((new Slice())->addElement((new Element())
//                ->setContent('&nbsp;')
//                ->stylePaddingTop('10px')
//            ))
//            ->addSlice($this->getIndividuallySignPart($personId))
            ->addSlice($this->getFsInfoExtended('5px', '1)', new Container('Das Fach war Gegenstand des Erwerbs der
             Fachhochschulreife.')))
            ->addSlice($this->getFsInfoExtended('5px', '2)', new Container('Das Thema der Facharbeit und die Note
             werden nachrichtlich ausgewiesen.')))
            ->addSlice($this->getFsInfoExtended('5px', 'K1 bis K4)', 'DAS LERNFELD WAR GEGENSTAND DER SCHRIFTLICHEN
            KOMPLEXPRÜFUNG <1/2/3/4> UND WIRD NACHRICHTLICH AUSGEWIESEN.'))
            ->addSlice($this->getFsInfoExtended('5px', 'KP)', 'DAS LERNFELD WAR GEGENSTAND DER PRAKTISCHEN KOMPLEXPRÜFUNG
             UND WIRD NACHRICHTLICH AUSGEWIESEN.'))
            ->addSlice($this->getFsInfo('10px', 'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        return $pageList;
    }
}
