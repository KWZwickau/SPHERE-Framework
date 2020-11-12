<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class FsHj
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class FsHj extends FsStyle
{

    /**
     * @return array
     */
    public function getApiModalColumns()
    {
        return array(
            // Page 2
            'FsDestination' => 'Fachbereich',
            'SubjectArea' => 'Fachrichtung',
            'Focus' => 'Schwerpunkt',
            // Page 3
//            'JobEducationDuration' => 'Berufspraktische Ausbildung (dauer in Wochen)',
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

        $pageList[] = (new Page())
            ->addSlice($this->getSchoolHead($personId, 'Halbjahreszeugnis'))
            ->addSlice($this->getStudentHead($personId, '', 'hat in der gesamten bisherigen Ausbildung folgende Leistungen erreicht:'))
            ->addSlice($this->getSubjectLineDuty())
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(),'Fachrichtungsübergreifender Bereich', 1, 5, '200px', 1, 4))
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(),'Fachrichtungsbezogener Bereich', 1, 8))
        ;

//        Debugger::screenDump($pageList[0]->getContent());
//        exit;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId, 'Halbjahreszeugnis'))
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(), 'Fachrichtungsbezogener Bereich (Fortsetzung)', 9, 11, '384px'))
            ->addSlice($this->getSubjectLineChosen($personId, $this->getCertificateEntity(), '110px'))
//            ->addSlice($this->getSubjectLineJobEducation($personId, $this->getCertificateEntity()))
            ->addSlice($this->getFachhochschulreife($personId, $this->getCertificateEntity()))
            ->addSlice($this->getChosenArea($personId))
            ->addSlice($this->getDescriptionBsContent($personId, '40px'))
            ->addSlice((new Slice())->addElement((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('50px')
            ))
            ->addSlice($this->getIndividuallySignPart($personId))
            ->addSlice($this->getFsInfo('25px',
                'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        return $pageList;
    }
}