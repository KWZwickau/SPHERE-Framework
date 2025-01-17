<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESS;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EssGsHjOne
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class EssGsHjOne extends Certificate
{

    const TEXT_SIZE_BIG = '18pt';
    const TEXT_SIZE = '12pt';
    const TEXT_SIZE_SMALL = '8pt';
    const TEXT_FAMILY = 'MyriadPro';

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        if ($this->isSample()) {
            $Header = (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '25%')
                    ->addElementColumn((new Element\Sample())
                        ->styleTextSize('30px')
                        ->styleHeight('1px')
                        ->styleMarginTop('-110px')
                    )
                    ->addElementColumn((new Element())
                        , '25%')
                );
        } else {
            $Header = (new Slice());
        }

        return (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleHeight('110px')
                    )
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ESS_Grundschule_Head.jpg', '700px')))
                )
            )
            ->addSlice($Header)
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '25%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('HALBJAHRESINFORMATION')
                        ->styleLineHeight('90%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleTextSize(self::TEXT_SIZE_BIG)
                        ->styleMarginTop('15px')
                        , '75%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->stylePaddingTop('8px')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Klasse {{ Content.P' . $personId . '.Division.Data.Level.Name }}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight('90%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        , '25%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('1. Schulhalbjahr {{ Content.P' . $personId . '.Division.Data.Year }}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight('90%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        , '45%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Schneeberg, den 
                            {% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                {{ Content.P' . $personId . '.Input.Date }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight('90%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleAlignRight()
                        , '30%'
                    )
                )
                ->stylePaddingBottom('30px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                      {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleTextSize(self::TEXT_SIZE_BIG)
                        ->styleLineHeight('90%')
                        ->styleFontFamily(self::TEXT_FAMILY)
//                        ->styleTextBold()
                        , '100%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vor- und Zuname')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        ->styleLineHeight('75%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        , '100%'
                    )
                )
            )

            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                                {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight('90%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleAlignJustify()
                        ->stylePaddingTop('20px')
                        ->stylePaddingBottom('20px')
                        ->styleHeight('412px')
                    )
                )
            )

            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.DivisionTeacher.Gender is not empty) %}
                                {% if(Content.P' . $personId . '.DivisionTeacher.Gender == "M") %}
                                    Dein Lehrer
                                {% else %}
                                    Deine Lehrerin
                                {% endif %}
                            {% else %}
                                Dein(e) Lehrer(in)
                            {% endif %}
                            {% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight('90%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->stylePaddingTop('30px')
                    , '100%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '42%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Zur Kenntnis genommen:')
                        ->styleTextSize('10pt')
                        ->styleLineHeight('90%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->stylePaddingTop('20px')
                        , '23%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->stylePaddingTop('20px')
                        ->styleBorderBottom('1px', '#000', 'dotted')
                        , '34%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '1%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '66%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Eltern')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        ->styleLineHeight('90%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->stylePaddingTop('5px')
                        ->styleAlignCenter()
                        , '34%'
                    )
                )
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('15px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element\Image('/Common/Style/Resource/Logo/ESS_Grundschule_Down.jpg', '700px'))
                    ->stylePaddingTop('20px')
                )
            );
    }
}
