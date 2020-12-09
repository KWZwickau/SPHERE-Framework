<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveComplexExam;
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
        $Page->addSlice($this->getSubjectLineDuty('20px'));
        $Page->addSlice($this->getSubjectLineBaseAbg($personId, $this->getCertificateEntity(),'Fachrichtungsübergreifender Bereich', 1, 5, '220px', 1, 4));
        $Page->addSlice($this->getSubjectLineBaseAbg($personId, $this->getCertificateEntity(),'Fachrichtungsbezogener Bereich', 1, 8, 'auto', 5, 14));
        $pageList[] = $Page;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId, 'Abgangszeugnis'))
            ->addSlice($this->getSubjectLineBaseAbg($personId, $this->getCertificateEntity(),'Fachrichtungsbezogener Bereich (Fortsetzung)', 9, 11, '430px', 5, 14))
            ->addSlice($this->getSubjectLineBaseAbg($personId, $this->getCertificateEntity(), 'Wahlpflichtbereich', 1, 2, 'auto', 15, 16))
            ->addSlice($this->getSubjectLineComplexExam($personId, 'Schriftliche Komplexprüfung/en', TblLeaveComplexExam::IDENTIFIER_WRITTEN, 4))
            ->addSlice($this->getSubjectLineComplexExam($personId, 'Praktische Komplexprüfung', TblLeaveComplexExam::IDENTIFIER_PRAXIS, 1))
            ->addSlice($this->getSubjectLineJobEducationAbg($personId, $this->getCertificateEntity()))
        ;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId, 'Abgangszeugnis', '3'))
            ->addSlice($this->getSubjectLineInformationalExpulsion($personId))
            ->addSlice($this->getSubjectLineSkilledWork($personId))
            ->addSlice($this->getFachhochschulreife($personId))
            ->addSlice($this->getChosenArea($personId))
            ->addSlice($this->getDescriptionFsContent($personId, '40px'))
            ->addSlice($this->getSpace('30px'))
            ->addSlice($this->getIndividuallySignPart($personId, true))
            ->addSlice($this->getSpace('20px'))
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
