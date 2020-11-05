<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;

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
//
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
            ->addSlice($this->getSecondPageHead($personId, 'Abschlusszeugnis'))
            ->addSlice($this->getSubjectLinePerformance())
            ->addSlice($this->getSubjectLineDuty('15px'))
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(),'Fachrichtungsübergreifender Bereich', 1, 5, false, '190px', 1, 4))
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(),'Fachrichtungsbezogener Bereich', 1, 8, true, '282px'))
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
            ->addSlice($this->getSecondPageHead($personId, 'Abschlusszeugnis'))
            ->addSlice($this->getSubjectLineInformationalExpulsion($personId))
            ->addSlice($this->getSubjectLineSkilledWork($personId))
            ->addSlice($this->getChosenArea($personId))
            ->addSlice($this->getDescriptionBsContent($personId))
//            ->addSlice($this->getTransfer($personId))
            ->addSlice((new Slice())->addElement((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('120px')
            ))
//            ->addSlice($this->getIndividuallySignPart($personId))
            ->addSlice($this->getFsInfoExtended('10px', '1)', new Container('Dem Zeugnis liegt die Schulordnung Fachschule vom 
            03.08.2017 (SächsGVBl. S. 428), in der jeweils geltenden Fassung, zu Grunde.')
            . new Container('Der Abschluss der Fachschule entspricht der Rahmenvereinbarung über Fachschulen 
            (Beschluss der Kultusminister-konferenz vom 07.11.2002 in der jeweils geltenden Fassung) und wird von 
            allen Ländern in der Bundesrepublik Deutschland anerkannt.')))
            ->addSlice($this->getFsInfoExtended('10px', '2)', new Container('Das Thema der Facharbeit und die Note werden
             nachrichtlich ausgewiesen.')))
            ->addSlice($this->getFsInfoExtended('10px', 'K1 bis K4)', 'DAS LERNFELD WAR GEGENSTAND DER SCHRIFTLICHEN
            KOMPLEXPRÜFUNG <1/2/3/4> UND WIRD NACHRICHTLICH AUSGEWIESEN.'))
            ->addSlice($this->getFsInfoExtended('10px', 'KP)', 'DAS LERNFELD WAR GEGENSTAND DER PRAKTISCHEN KOMPLEXPRÜFUNG
             UND WIRD NACHRICHTLICH AUSGEWIESEN.'))
            ->addSlice($this->getFsInfo('15px', 'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        return $pageList;
    }
}
