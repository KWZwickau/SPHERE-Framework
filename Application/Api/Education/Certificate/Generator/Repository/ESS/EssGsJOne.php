<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESS;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EssGsJOne
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class EssGsJOne extends Certificate
{

    const TEXT_SIZE = '12pt';
    const TEXT_SIZE_SMALL = '10pt';
    const TEXT_SIZE_VERY_SMALL = '7pt';
    const TEXT_FAMILY = 'MyriadPro';

    /**
     * @return array
     */
    public function selectValuesTransfer()
    {
        return array(
            1 => "steigt auf in Klasse 2.",
            2 => "steigt nicht auf."
        );
    }

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
                ->addElement((new Element\Image('/Common/Style/Resource/Logo/ESS_Grundschule_Head.png', '700px'))
                    ->styleAlignCenter()
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->stylePaddingTop('8px')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.DescriptionWithE }} Klasse {{ Content.P' . $personId . '.Division.Data.Level.Name }}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        , '25%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('2. Schulhalbjahr der Klasse {{ Content.P' . $personId . '.Division.Data.Level.Name }}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        , '45%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Schuljahr {{ Content.P' . $personId . '.Division.Data.Year }}')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight('105%')
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
                        ->styleTextSize('15pt')
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
//                        ->styleTextBold()
                        , '55%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Schneeberg, den 
                            {% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                {{ Content.P' . $personId . '.Input.Date }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                        ->styleTextSize('15pt')
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleAlignRight()
//                        ->styleTextBold()
                        , '45%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vor- und Zuname')
                        ->styleTextSize(self::TEXT_SIZE_VERY_SMALL)
                        ->styleLineHeight('75%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                    )
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Jahreszeugnis der Grundschule')
                    ->styleTextSize('15pt')
                    ->styleLineHeight('105%')
                    ->styleFontFamily(self::TEXT_FAMILY)
                    ->styleAlignCenter()
                    ->stylePaddingTop('25px')
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
                    ->styleLineHeight('105%')
                    ->styleFontFamily(self::TEXT_FAMILY)
                    ->styleAlignJustify()
                    ->stylePaddingTop('25px')
                    ->stylePaddingRight('40px')
                    ->stylePaddingLeft('40px')
                    ->stylePaddingBottom('20px')
                    ->styleHeight('300px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Transfer) %}
                        {{ Content.P' . $personId . '.Input.Transfer }}
                    {% else %}
                          &nbsp;
                    {% endif %}')
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleLineHeight('105%')
                    ->styleFontFamily(self::TEXT_FAMILY)
                    ->stylePaddingRight('40px')
                    ->stylePaddingLeft('40px')
                    ->styleMarginTop('5px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '12%')
                    ->addElementColumn((new Element())
                        ->setContent('Zur Kenntnis genommen:')
                        ->styleTextSize(self::TEXT_SIZE)
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleAlignRight()
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom()
                        , '38%')
                    ->addElementColumn((new Element())
                        , '20%')
                )
                ->styleMarginTop('25px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '42%')
                    ->addElementColumn((new Element())
                        ->setContent('Eltern')
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleAlignCenter()
                        ->styleTextSize(self::TEXT_SIZE_VERY_SMALL)
                        , '38%')
                    ->addElementColumn((new Element())
                        , '20%')
                )
                ->styleMarginTop('3px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '40px')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom()
                        , '30%')
                    ->addElementColumn((new Element())
                        , '70%')
                )
                ->styleMarginTop('37px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                Klassenleiter/in
                            {% endif %}
                            ')
                        ->styleTextSize($this::TEXT_SIZE_SMALL)
                        ->styleLineHeight('80%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->stylePaddingLeft('40px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent('Dienstsiegel')
                        , '30%')
                )
                ->styleMarginTop('3px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '40px')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom()
                        , '30%')
                    ->addElementColumn((new Element())
                        , '70%')
                )
                ->styleMarginTop('37px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                    {{ Content.P' . $personId . '.Headmaster.Description }}
                            {% else %}
                                Schulleiter/in
                            {% endif %}
                            ')
                        ->styleTextSize($this::TEXT_SIZE_SMALL)
                        ->styleLineHeight('80%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->stylePaddingLeft('40px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('Schneeberg, den 
                            {% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                {{ Content.P' . $personId . '.Input.Date }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                                ')
                        ->styleLineHeight('105%')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleAlignRight()
                        ->stylePaddingRight('40px')
                        , '40%')
                )
                ->styleMarginTop('3px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('5px')
                )
                ->addElement((new Element\Image('/Common/Style/Resource/Logo/ESS_Grundschule_down.png', '610px'))
                    ->styleAlignCenter()
                )
            );
    }
}
