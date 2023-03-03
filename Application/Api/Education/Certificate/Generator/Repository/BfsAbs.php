<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class BfsAbs
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class BfsAbs extends BfsStyle
{
    /**
     * @return array
     */
    public function getApiModalColumns()
    {
        return array(
            'DateFrom' => 'Besucht "seit" die Fachschule',
            'DateTo' => 'Besuchte "bis" die Fachschule',
            'BfsDestination'      => 'Berufsfachschule für ...',

            'OperationTimeTotal' => 'Berufspraktische Ausbildung Dauer in Wochen',
            'Operation1' => 'Einsatzgebiet 1',
            'OperationTime1' => 'Einsatzgebiet Dauer in Wochen 1',
            'Operation2' => 'Einsatzgebiet 2',
            'OperationTime2' => 'Einsatzgebiet Dauer in Wochen 2',
            'Operation3' => 'Einsatzgebiet 3',
            'OperationTime3' => 'Einsatzgebiet Dauer in Wochen 3',
            'Operation4' => 'Einsatzgebiet 4',
            'OperationTime4' => 'Einsatzgebiet Dauer in Wochen 4',
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
            ->addSlice($this->getSchoolHead($personId, 'Abschlusszeugnis', false, true))
            ->addSlice($this->getStudentHeadAbs($personId))
            ->addSlice((new Slice())->addElement((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('330px')
            ))
            ->addSlice($this->getIndividuallySignPart($personId, true))
        ;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId, 'Abschlusszeugnis', false))
            ->addSlice($this->getSubjectLinePerformance())
            ->addSlice($this->getSubjectLineDuty('10px'))
            ->addSlice($this->getSubjectLineAcrossAbs($personId, $this->getCertificateEntity(), 'Berufsübergreifender Bereich', 1, 5, 1, 4, '150px'))
            ->addSlice($this->getSubjectLineAcrossAbs($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich', 1, 14, 5, 12, '320px'))
            ->addSlice($this->getSubjectLineAcrossAbs($personId, $this->getCertificateEntity(), 'Wahlpflichtbereich', 1, 2, 13, 13, '80px'))
            ->addSlice($this->getPraktika($personId, $this->getCertificateEntity(), true))
            ->addSlice($this->getDescriptionBsContent($personId, '85px'))
            ->addSlice((new Slice())->addElement((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('11px')
            ))
            ->addSlice($this->getBsInfo('23px',
                'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        return $pageList;
    }
}