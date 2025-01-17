<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class GsJa
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class GsJa extends Certificate
{

    /**
     * @return array
     */
    public function selectValuesTransfer()
    {
        return array(
            1 => "wird versetzt",
            2 => "wird nicht versetzt"
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null){

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $Header = $this->getHead($this->isSample());
        // get Content while building certificate
        $Data = $this->getCertificateData($tblPerson, $this->getTblPrepareCertificate());

        $subjectRowCount = 0;

        return (new Page())
                ->addSlice(
                    $Header
                )
                ->addSlice($this->getSchoolName($personId))
                ->addSlice($this->getCertificateHead('Jahreszeugnis der Grundschule'))
                ->addSlice($this->getDivisionAndYear($personId))
                ->addSlice($this->getStudentName($personId))
                ->addSlice($this->getGradeLanesSmall($personId))
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Einschätzung:')
                    )
                    ->styleMarginTop('5px')
                )
                ->addSlice($this->getRatingContent($personId, '110px', '0px', ''))
                ->addSlice((new Slice())
                    ->addElement(( new Element() )
                        ->setContent('Leistungen in den einzelnen Fächern:')
                        ->styleMarginTop('15px')
                        ->styleMarginBottom('5px')
                        ->styleTextBold()
                    )
                )
                ->addSlice($this->getSubjectLanesSmall($personId, true, array(), '14px', false, false, false, Certificate::BACKGROUND_GRADE_FIELD, $subjectRowCount)
                    ->styleHeight($subjectRowCount > 6 ? '155px': '130px'))
                ->addSlice($this->getDescriptionHead($personId, true))
//                ->addSlice($this->getDescriptionContent($personId, '130px', '5px', '', false, $Data['Remark']))
                ->addSlice($this->getDescriptionContent($personId, '130px', '5px'))
                ->addSlice($this->getTransfer($personId))
                ->addSlice($this->getDateLine($personId))
                ->addSlice($this->getSignPart($personId, true))
                ->addSlice($this->getParentSign())
                ->addSlice($this->getInfo('1px',
                    'Notenerläuterung:',
                    '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend
                (6 = ungenügend nur bei der Bewertung der Leistungen)')
        );
    }
}
