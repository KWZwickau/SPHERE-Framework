<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.11.2016
 * Time: 09:53
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class RadebeulKinderbrief
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR
 */
class RadebeulKinderbrief extends Certificate
{

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $fontFamily = 'MetaPro';
        $fontSize = '13pt';
        $lineHeight = '100%';

        return (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn(
                        $this->isSample()
                            ? (new Element())
                            ->setContent('MUSTER')
                            ->styleAlignCenter()
                            ->styleTextBold()
                            ->styleTextColor('darkred')
                            ->styleTextSize('24px')
                            ->styleMarginBottom('0px')
                            ->styleHeight('100px')
                            : (new Element())
                            ->setContent('&nbsp;')
                            ->styleHeight('100px')
                            ->styleMarginBottom('0px')
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                                Kinderbrief für 
                                {{ Content.P' . $personId . '.Person.Data.Name.First }}
                                {{ Content.P' . $personId . '.Person.Data.Name.Last }}
                            ')
                        ->styleFontFamily($fontFamily)
                        ->styleLineHeight($lineHeight)
                        ->styleTextSize('20px')
                        ->styleAlignCenter()
                        ->stylePaddingTop('40px')
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                                nach dem ersten Halbjahr des Schuljahres {{ Content.P' . $personId . '.Division.Data.Year }} 
                            ')
                        ->styleFontFamily($fontFamily)
                        ->styleLineHeight($lineHeight)
                        ->styleMarginTop('30px')
                        ->styleTextSize('20px')
                        ->styleAlignCenter()
                        ->stylePaddingTop('5px')
                    )
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('
                                {% if(Content.P' . $personId . '.Input.Rating is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Rating|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($fontSize)
                    ->styleLineHeight($lineHeight)
                    ->styleMarginTop('30px')
                )
            );
    }
}