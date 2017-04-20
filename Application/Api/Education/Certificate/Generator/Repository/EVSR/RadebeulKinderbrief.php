<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.11.2016
 * Time: 09:53
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

/**
 * Class RadebeulKinderbrief
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR
 */
class RadebeulKinderbrief  extends Certificate
{

    /**
     * @param bool $IsSample
     *
     * @return Frame
     */
    public function buildCertificate($IsSample = true)
    {

        $fontFamily = 'MetaPro';
        $fontSize = '12pt';

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn(
                            $IsSample
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
                                {{ Content.Person.Data.Name.First }}
                                {{ Content.Person.Data.Name.Last }}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleLineHeight('80%')
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
                                nach dem ersten Halbjahr des Schuljahres {{ Content.Division.Data.Year }} 
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleLineHeight('80%')
                            ->styleMarginTop('30px')
                            ->styleTextSize('20px')
                            ->styleAlignCenter()
                            ->stylePaddingTop('5px')
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addElement(( new Element() )
                        ->setContent('
                                {% if(Content.Input.Rating is not empty) %}
                                    {{ Content.Input.Rating|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($fontSize)
                        ->styleLineHeight('80%')
                        ->styleMarginTop('30px')
                        ->stylePaddingLeft('30px')
                        ->stylePaddingRight('30px')
                    )
                )
            )
        );
    }
}