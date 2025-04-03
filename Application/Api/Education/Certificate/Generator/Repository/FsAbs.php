<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveComplexExam;
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
     * @return array
     */
    public function getApiModalColumns()
    {
        return array(
            'DateFrom' => 'Besucht "seit" die Fachschule',
            'DateTo' => 'Besuchte "bis" die Fachschule',

            'FsDestination' => 'Fachbereich',
            'SubjectArea' => 'Fachrichtung',
            'Focus' => 'Schwerpunkt',

            'JobEducationDuration'=> 'Berufspraktische Ausbildung (dauer in Wochen)',
            'ChosenArea1' => 'Wahlbereich 1',
            'ChosenArea2' => 'Wahlbereich 2',
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

        // leere Seite
        $pageList[] = new Page();

        $Page = (new Page());
        $Page->addSlice($this->getSchoolHeadAbs($personId));
        $Page->addSlice($this->getStudentHeadAbs($personId));

        $Page->addSlice((new Slice())->addElement((new Element())
            ->setContent('&nbsp;')
            ->stylePaddingTop('220px')
        ));
        $Page->addSlice($this->getIndividuallySignPart($personId, true));

        $pageList[] = $Page;

        $paddingTop = '13px';

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId, 'Abschlusszeugnis'))
            ->addSlice($this->getSubjectLinePerformance())
            ->addSlice($this->getSubjectLineDuty('10px'))
            ->addSlice($this->getSubjectLineBaseAbg($personId, $this->getCertificateEntity(),'Fachrichtungsübergreifender Bereich', 1, 5, 'auto', 1, 4, $paddingTop))
            ->addSlice($this->getSubjectLineBaseAbg($personId, $this->getCertificateEntity(),'Fachrichtungsbezogener Bereich', 1, 8, 'auto', 5, 14, $paddingTop))
            ->addSlice($this->getSubjectLineBaseAbg($personId, $this->getCertificateEntity(), 'Wahlpflichtbereich', 1, 2, 'auto', 15, 16, $paddingTop))
            ->addSlice($this->getSubjectLineComplexExam($personId, 'Schriftliche Komplexprüfung/en', 'EN', 4, 'auto', $paddingTop))
//            ->addSlice($this->getSubjectLineComplexExam($personId, 'Praktische Komplexprüfung', TblLeaveComplexExam::IDENTIFIER_PRAXIS, 1, 'auto', $paddingTop))
            ->addSlice($this->getSubjectLineJobEducationAbg($personId, $this->getCertificateEntity(), 'auto', $paddingTop))
        ;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId, 'Abschlusszeugnis', '3'))
            ->addSlice($this->getSubjectLineInformationalExpulsion($personId))
            ->addSlice($this->getSubjectLineSkilledWork($personId))
            ->addSlice($this->getChosenArea($personId))
            ->addSlice($this->getDescriptionFsContent($personId, '90px', true))
            ->addSlice($this->getSpace('250px'))
            ->addSlice($this->getFsInfoExtended('10px', '1)', new Container('Dem Zeugnis liegt die Schulordnung Fachschule vom 
            03.08.2017 (SächsGVBl. S. 428), in der jeweils geltenden Fassung, zu Grunde.')
            . new Container('Der Abschluss der Fachschule entspricht der Rahmenvereinbarung über Fachschulen 
            (Beschluss der Kultusminister-konferenz vom 07.11.2002 in der jeweils geltenden Fassung) und wird von 
            allen Ländern in der Bundesrepublik Deutschland anerkannt.')))
            ->addSlice($this->getFsInfoExtended('10px', '2)', new Container('Das Thema der Facharbeit und die Note werden
             nachrichtlich ausgewiesen.')))
//            ->addSlice($this->getFsInfoExtended('10px', 'K1 bis K4)', 'DAS LERNFELD WAR GEGENSTAND DER SCHRIFTLICHEN
//            KOMPLEXPRÜFUNG <1/2/3/4> UND WIRD NACHRICHTLICH AUSGEWIESEN.'))
//            ->addSlice($this->getFsInfoExtended('10px', 'KP)', 'DAS LERNFELD WAR GEGENSTAND DER PRAKTISCHEN KOMPLEXPRÜFUNG
//             UND WIRD NACHRICHTLICH AUSGEWIESEN.'))
            ->addSlice($this->getFsInfo('15px', 'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        return $pageList;
    }
}
