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

    const TEXT_SIZE = '12pt';
    const TEXT_SIZE_SMALL = '9pt';
    const TEXT_SIZE_VERY_SMALL = '7pt';

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
                    )
                    ->addElementColumn((new Element())
                        , '25%')
                );
        } else {
            $Header = (new Slice());
        }

        return (new Page())
            ->addSlice($Header)
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->stylePaddingTop('15px')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '3%')
                    ->addElementColumn((new Element())
                        ->setContent('Klasse {{ Content.P' . $personId . '.Division.Data.Level.Name }}')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        , '15%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('1. Schulhalbjahr')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        , '15%'
                    )
                    ->addElementColumn((new Element())
                        , '37%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Schuljahr {{ Content.P' . $personId . '.Division.Data.Year }}')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        ->stylePaddingTop('10px')
                        , '30%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '3%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                      {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleTextSize('15pt'))
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '3%')
                    ->addElementColumn((new Element())
                        ->setContent('Vor- und Zuname')
                        ->stylePaddingBottom('20px')
                        ->styleTextSize(self::TEXT_SIZE_VERY_SMALL))
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '3%')
                    ->addElementColumn((new Element())
                        ->setContent('EVANGELISCHE GRUNDSCHULE')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        , '30%'
                    )
                    ->addElementColumn((new Element())
                        , '37%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('SCHNEEBERG, DEN')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        , '30%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                                {% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        , '30%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleAlignJustify()
                    ->stylePaddingTop('60px')
                    ->stylePaddingRight('20px')
                    ->stylePaddingLeft('20px')
                    ->stylePaddingBottom('20px')
//                        ->styleHeight('485px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Für das 2. Schulhalbjahr wünsche ich dir recht viel Freude und Erfolg beim Lernen.')
                    ->styleTextSize(self::TEXT_SIZE)
                    ->stylePaddingTop('30px')
                    ->stylePaddingLeft('25%')
                    ->stylePaddingRight('20%')
                )
                ->addElement((new Element())
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
                    ->stylePaddingTop('30px')
                    ->stylePaddingLeft('25%')
                    ->stylePaddingRight('20%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '25%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Zur Kenntnis genommen:')
                        ->styleTextSize(self::TEXT_SIZE_SMALL)
                        ->stylePaddingTop('100px')
                        , '21%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->stylePaddingTop('100px')
                        ->styleBorderBottom()
                        , '34%'
                    )
                    ->addElementColumn((new Element())
                        , '20%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '46%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Erziehungsberechtigten')
                        ->styleTextSize(self::TEXT_SIZE_VERY_SMALL)
                        ->styleAlignCenter()
                        , '34%'
                    )
                    ->addElementColumn((new Element())
                        , '20%'
                    )
                )
            );
    }
}
