<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 19.01.2018
 * Time: 13:24
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CswMsHalbjahresinformation
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW
 */
class CswMsHalbjahresinformation extends Certificate
{

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;
        $pictureHeight = '90px';

        if ($this->isSample()) {
            $Header = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '25%'
                    )
                    ->addElementColumn((new Element\Sample())
                        ->styleTextSize('30px')
                    )
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/CSW_Logo_EOK_100x100.png',
                        'auto', $pictureHeight))->styleAlignRight()
                        , '25%')
                );
        } else {
            $Header = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element()), '75%')
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/CSW_Logo_EOK_100x100.png',
                        'auto', $pictureHeight))->styleAlignRight()
                        , '25%')
                );
        }
        $Header->styleHeight('50px');

        return (new Page())
            ->addSlice(
                $Header
            )
            ->addSlice($this->getIndividualSchoolLine($personId))
            ->addSlice($this->getCertificateHead('Halbjahresinformation'))
            ->addSlice($this->getDivisionAndYear($personId, '20px', '1. Schulhalbjahr'))
            ->addSlice($this->getStudentName($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('nahm am Unterricht der Schulart Mittelschule teil.')
                    ->styleTextSize('12px')
                    ->styleMarginTop('8px')
                )
            )
            ->addSlice($this->getGradeLanes($personId))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanes($personId)->styleHeight('270px'))
            ->addSlice($this->getOrientationStandard($personId))
            ->addSlice($this->getDescriptionHead($personId, true))
            ->addSlice($this->getDescriptionContent($personId, '85px', '15px'))
            ->addSlice($this->getDateLine($personId))
            ->addSlice($this->getSignPart($personId, false))
            ->addSlice($this->getParentSign())
            ->addSlice($this->getInfo('25px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend 
                    (6 = ungenügend nur bei der Bewertung der Leistungen)')
            );
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    public static function getIndividualSchoolLine($personId)
    {

        $slice = (new Slice());
            $slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Name der Schule:')
                    , '18%')
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if(Content.P'.$personId.'.Company.Data.Name) %}
                            <strong> {{ Content.P'.$personId.'.Company.Data.Name }} </strong>
                            {% if(Content.P'.$personId.'.Company.Data.ExtendedName) %}
                                <br>
                                - {{ Content.P'.$personId.'.Company.Data.ExtendedName }} -
                            {% else %}
                                &nbsp;
                            {% endif %}
                        {% else %}
                              &nbsp;
                        {% endif %}    
                    ')
                    ->styleAlignCenter()
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    , '18%')
            )->styleMarginTop('5px');

        $slice->addSection((new Section())
            ->addElementColumn((new Element())
                , '18%')
            ->addElementColumn((new Element())
                ->styleBorderBottom()
                ->styleMarginTop('2px')
            )
        );

        return $slice;
    }
}
