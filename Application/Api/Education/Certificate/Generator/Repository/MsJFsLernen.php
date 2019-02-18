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
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
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

        $Header = $this->getHead($this->isSample(), true, 'auto', '50px');

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
            ->addSlice($this->getGradeLanes($personId))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Einschätzung: {% if(Content.P'.$personId.'.Input.Rating is not empty) %}
                                {{ Content.P'.$personId.'.Input.Rating|nl2br }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                        ->styleHeight('50px')
                    )
                )
                ->styleMarginTop('15px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanes(
                $personId,
                true,
                array(),
                '14px',
                false,
                false,
                true
            )->styleHeight('290px'))
            ->addSlice($this->getDescriptionHead($personId, true))
            ->addSlice($this->getDescriptionContent($personId, '70px', '8px'))
            ->addSlice($this->getTransfer($personId, '13px'))
            ->addSlice($this->getDateLine($personId, '15px'))
            ->addSlice($this->getSignPart($personId, true, '15px'))
            ->addSlice($this->getParentSign('15px'))
            ->addSlice($this->getInfo('15px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend 
                    (6 = ungenügend nur bei der Bewertung der Leistungen)',
                '¹ &nbsp;&nbsp;&nbsp; gemäß § 27 Absatz 6 der Schulordnung Ober- und Abendoberschulen'
            ));
    }
}