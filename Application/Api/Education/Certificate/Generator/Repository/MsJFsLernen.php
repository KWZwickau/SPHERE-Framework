<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 16.01.2019
 * Time: 15:46
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;


use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class MsJFsLernen
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class MsJFsLernen extends Certificate
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
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $Header = $this->getHead($this->isSample());

        return (new Page())
            ->addSlice(
                $Header
            )
            ->addSlice($this->getSchoolName($personId))
            ->addSlice($this->getCertificateHead('Jahreszeugnis der Oberschule'))
            ->addSlice($this->getDivisionAndYear($personId, '20px'))
            ->addSlice($this->getStudentName($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('nahm am Unterricht mit dem Ziel des Abschlusses im Förderschwerpunkt Lernen teil.')
                    ->styleTextSize('12px')
                    ->styleMarginTop('8px')
                )
            )
            ->addSlice($this->getGradeLanesSmall($personId))
            ->addSlice($this->getRatingContent($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleMarginBottom('5px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanesSmall(
                $personId,
                true,
                array(),
                '14px',
                false,
                false,
                true
            )->styleHeight('220px'))
            ->addSlice($this->getDescriptionHead($personId, true))
            ->addSlice($this->getDescriptionContent($personId, '72px', '15px'))
            ->addSlice($this->getTransfer($personId, '13px'))
            ->addSlice($this->getDateLine($personId, '15px'))
            ->addSlice($this->getSignPart($personId, true, '25px'))
            ->addSlice($this->getParentSign('15px'))
            ->addSlice($this->getInfo('15px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend 
                    (6 = ungenügend nur bei der Bewertung der Leistungen)',
                '¹ &nbsp;&nbsp;&nbsp; gemäß § 27 Absatz 6 der Schulordnung Ober- und Abendoberschulen'
            ));
    }
}