<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVMO;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

/**
 * Class Style
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVMO
 */
abstract class Style extends Certificate
{
    const COLOR = '#555';

    /**
     * @param int $personId
     * @param string $title
     * @param string $period
     *
     * @return Slice
     */
    public function getCustomHead($personId, $title = 'Halbjahresinformation', $period = '1. Schulhalbjahr')
    {
        if ($this->isSample()) {
            $elementSample = (new Element\Sample());
        } else {
            $elementSample = (new Element())->setContent('&nbsp;');
        }

        return (new Slice())
            ->styleMarginTop('40px')
            ->addSection((new Section())
                ->addElementColumn($elementSample
                    ->styleTextSize('30px')
                    ->stylePaddingTop('20px')
                    , '26.5%')
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent($title)
                            ->styleAlignCenter()
                            ->stylePaddingTop('5px')
                            ->styleTextBold()
                            ->styleTextSize('16pt')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('der Grundschule')
                            ->styleAlignCenter()
                            ->styleTextBold()
                            ->styleTextSize('16pt')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klasse {{ Content.P' . $personId . '.Division.Data.Name }}')
                            ->styleMarginTop('20px')
                            ->stylePaddingLeft('8px')
                            ->stylePaddingBottom('4px')
                            ->styleTextSize('11pt')
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent($period . ' {{ Content.P' . $personId . '.Division.Data.Year }}')
                            ->styleMarginTop('20px')
                            ->styleAlignRight()
                            ->stylePaddingRight('8px')
                            ->stylePaddingBottom('4px')
                            ->styleTextSize('11pt')
                        )
                    )
                    ->styleBorderAll()
                    , '50%')
                ->addElementColumn((new Element()))
            );
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    public function getCustomStudentName($personId, $MarginTop = '130px')
    {
        return (new Slice())
            ->styleMarginTop($MarginTop)
            ->addSection((new Section())
                ->addElementColumn((new Element()), '5%')
                ->addElementColumn((new Element())
                    ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }} {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                    ->styleTextSize('14pt')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->stylePaddingBottom('4px')
                    ->styleBorderBottom('1px', '#BBB')
                )
                ->addElementColumn((new Element()), '5%')
            );
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getCustomStudentNameForGrades($personId, $MarginTop = '20px')
    {
        return (new Slice())
            ->styleMarginTop($MarginTop)
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Vorname und Name:')
                    ->styleTextSize('11pt')
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                  {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                    ->styleTextSize('11pt')
                    ->styleBorderBottom()
                )
            );
    }

    /**
     * @param $personId
     *
     * @return Section
     */
    public function getCustomMissing($personId)
    {
        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('Fehltage entschuldigt:')
                ->styleTextSize('10pt')
                , '22%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                        {{ Content.P' . $personId . '.Input.Missing }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleTextSize('10pt')
                , '7%')
            ->addElementColumn((new Element()), '10%')
            ->addElementColumn((new Element())
                ->setContent('unentschuldigt:')
                ->styleTextSize('10pt')
                , '16%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                        &nbsp;{{ Content.P' . $personId . '.Input.Bad.Missing }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
                ->styleTextSize('10pt')
                ->styleAlignCenter()
                ->styleBorderBottom()
                , '7%')
            ->addElementColumn((new Element()));
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Section
     */
    public function getCustomDate($personId, $MarginTop = '12px')
    {
        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('Datum:')
                ->styleTextSize('10pt')
                ->styleMarginTop($MarginTop)
                , '7%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                        {{ Content.P' . $personId . '.Input.Date }}
                    {% else %}
                        &nbsp;
                    {% endif %}')
                ->styleTextSize('10pt')
                ->styleMarginTop($MarginTop)
                ->styleBorderBottom()
                ->styleAlignCenter()
                , '11%')
            ->addElementColumn((new Element())
                , '65%');
    }

    /**
     * @param $personId
     * @param bool $isExtended with directory and stamp
     * @param string $MarginTop
     *
     * @return Slice
     */
    public function getCustomSignPart($personId, $isExtended = true, $MarginTop = '23px')
    {
        $SignSlice = (new Slice());
        if ($isExtended) {
            $SignSlice
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom('1px', self::COLOR)
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom('1px', self::COLOR)
                        , '30%')
                )
                ->styleMarginTop($MarginTop)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                {{ Content.P' . $personId . '.Headmaster.Description }}
                            {% else %}
                                Schulleiter(in)
                            {% endif %}'
                        )
                        ->stylePaddingLeft('8px')
                        ->styleTextSize('11px')
                        ->styleTextColor(self::COLOR)
                        , '30%')
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
//                        ->setContent('Dienstsiegel der Schule')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        ->styleTextColor(self::COLOR)
                        , '30%')
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                Klassenlehrer(in)
                            {% endif %}'
                        )
                        ->stylePaddingLeft('8px')
                        ->styleTextSize('11px')
                        ->styleTextColor(self::COLOR)
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                                {{ Content.P' . $personId . '.Headmaster.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        ->styleTextSize('11px')
                        ->styleTextColor(self::COLOR)
                        ->stylePaddingTop('2px')
                        ->stylePaddingLeft('8px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        ->styleTextSize('11px')
                        ->styleTextColor(self::COLOR)
                        ->stylePaddingTop('2px')
                        ->stylePaddingLeft('8px')
                        , '30%')
                );
        } else {
            $SignSlice
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                    , '70%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('1px', self::COLOR)
                    , '30%')
                )
                ->styleMarginTop($MarginTop)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('
                        {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                            {% else %}
                                Klassenlehrer(in)
                            {% endif %}
                        ')
                        ->stylePaddingLeft('8px')
                        ->styleTextSize('11px')
                        ->styleTextColor(self::COLOR)
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '70%')
                    ->addElementColumn((new Element())
                        ->setContent(
                            '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                        )
                        ->styleTextSize('11px')
                        ->styleTextColor(self::COLOR)
                        ->stylePaddingTop('2px')
                        ->stylePaddingLeft('8px')
                        , '30%')
                );
        }

        return $SignSlice;
    }

    /**
     * @param string $MarginTop
     *
     * @return Slice
     */
    protected function getCustomParentSign($MarginTop = '35px')
    {
        return (new Slice())
            ->styleMarginTop($MarginTop)
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Zur Kenntnis genommen:')
                    ->styleTextSize('10pt')
                    ->styleTextColor(self::COLOR)
                    , '26%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('1px', self::COLOR)
                    , '40%')
                ->addElementColumn((new Element()))
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '26%')
                ->addElementColumn((new Element())
                    ->setContent('Personensorgeberechtigte(r)')
                    ->stylePaddingLeft('8px')
                    ->styleTextSize('11px')
                    ->styleTextColor(self::COLOR)
                    , '40%')
                ->addElementColumn((new Element()))
            );
    }

    /**
     * @param $personId
     * @param string $MarginTop
     *
     * @return Slice
     */
    public function getCustomTransfer($personId, $MarginTop = '10px')
    {
        return (new Slice())
            ->styleMarginTop($MarginTop)
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Versetzungsvermerk:')
                    ->styleTextSize('10pt')
                    , '22%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Transfer) %}
                            {{ Content.P' . $personId . '.Input.Transfer }}.
                        {% else %}
                              &nbsp;
                        {% endif %}')
                    ->styleTextSize('10pt')
//                    ->styleBorderBottom('1px')
                    , '58%')
                ->addElementColumn((new Element())
                    , '20%')
            );
    }
}