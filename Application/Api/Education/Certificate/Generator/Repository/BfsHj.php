<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class BfsHj
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class BfsHj extends BfsStyle
{

    /**
     * @return array
     */
    public function getApiModalColumns()
    {
        return array(
            // Page 2
            'BfsDestination' => 'Berufsfachschule für ...',
            'CertificateName' => 'Abweichender Zeugnisname (Endjahresinformation)',
            // Page 3
            'OperationTimeTotal' => 'Praktische Ausbildung Dauer in Wochen',
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
            ->addSlice($this->getSchoolHead($personId, 'Halbjahreszeugnis', true))
            ->addSlice($this->getStudentHead($personId, 'Schulhalbjahr', 'hat in der gesamten bisherigen Ausbildung folgende Leistungen erreicht:'))
            ->addSlice($this->getSubjectLineDuty())
            ->addSlice($this->getSubjectLineAcross($personId, $this->getCertificateEntity(), 'Berufsübergreifender Bereich', 1, 6))
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(),'Berufsbezogener Bereich', 1, 10))
        ;

        $pageList[] = (new Page())
            ->addSlice($this->getSecondPageHead($personId, 'Halbjahreszeugnis', true))
            ->addSlice($this->getSubjectLineBase($personId, $this->getCertificateEntity(), 'Berufsbezogener Bereich (Fortsetzung)', 11, 4, '220px'))
            ->addSlice($this->getSubjectLineChosen($personId, $this->getCertificateEntity()))
            ->addSlice($this->getPraktika($personId, $this->getCertificateEntity()))
            ->addSlice($this->getDescriptionBsContent($personId, '195px')
                ->stylePaddingBottom('1px')
            )
            ->addSlice($this->getIndividuallySignPart($personId))
            ->addSlice($this->getBsInfo('20px',
                'NOTENSTUFEN: sehr gut (1), gut (2), befriedigend (3), ausreichend (4), mangelhaft (5), ungenügend (6)'))
        ;

        return $pageList;
    }
}
