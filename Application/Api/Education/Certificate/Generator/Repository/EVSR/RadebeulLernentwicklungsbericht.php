<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.11.2016
 * Time: 11:23
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class RadebeulLernentwicklungsbericht
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR
 */
class RadebeulLernentwicklungsbericht extends Certificate
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
        $textSize = '12pt';
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
                            ->styleHeight('130px')
                            : (new Element())
                            ->setContent('&nbsp;')
                            ->styleTextBold()
                            ->styleTextSize('24px')
                            ->styleMarginBottom('0px')
                            ->styleHeight('130px')
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent(
                            new Container('Lernentwicklungsbericht')
                            .new Container('{% if( Content.P' . $personId . '.Company.Data.Name is not empty) %}
                                        {{ Content.P' . $personId . '.Company.Data.Name }}
                                    {% else %}
                                        Evangelisches Schulzentrum Radebeul
                                    {% endif %}')
                            .new Container('- Grundschule -')
                        )
                        ->styleLineHeight($lineHeight)
                        ->styleTextSize('20pt')
                        ->styleFontFamily($fontFamily)
                        ->styleAlignCenter()
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('- Staatlich anerkannte Ersatzschule in freier Trägerschaft -')
                        ->styleFontFamily($fontFamily)
                        ->styleLineHeight($lineHeight)
                        ->styleTextSize($textSize)
                        ->styleLineHeight('90%')
                        ->styleAlignCenter()
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent(
                            new Container('zum') .
                            new Container('Schuljahr {{ Content.P' . $personId . '.Division.Data.Year }}')
                        )
                        ->styleFontFamily($fontFamily)
                        ->styleLineHeight('80%')
                        ->styleMarginTop('20px')
                        ->styleTextSize('20pt')
                        ->styleAlignCenter()
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('für:')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->styleMarginTop('25px')
                        , '20%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.Last }}, {{ Content.P' . $personId . '.Person.Data.Name.First }}')
                        ->styleMarginTop('25px')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        , '80%')

                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('geboren am:')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->styleMarginTop('5px')
                        , '20%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {% if(Content.P' . $personId . '.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.P' . $personId . '.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->styleMarginTop('5px')
                        , '80%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Klasse:')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->styleMarginTop('5px')
                        , '20%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {{ Content.P' . $personId . '.Division.Data.Level.Name }}
                            ')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->styleMarginTop('5px')
                        , '80%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->setContent('Hinweis zu den einzelnen Lernbereichen / Fächern:')
                        ->styleMarginTop('30px')
                        ->styleTextBold()
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
                    ->styleAlignJustify()
                    ->styleFontFamily($fontFamily)
                    ->styleTextSize($textSize)
                    ->styleLineHeight($lineHeight)
                    ->styleMarginTop('30px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Versäumte Tage:')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->styleMarginTop('40px')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                        {% if(Content.P' . $personId . '.Input.Total.Missing is not empty) %}
                                            {{ Content.P' . $personId . '.Input.Total.Missing }}
                                        {% else %}
                                            0
                                        {% endif %}'
                        )
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->styleMarginTop('40px')
                        , '8%')
                    ->addElementColumn((new Element())
                        ->setContent('davon unentschuldigt:')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->styleMarginTop('40px')
                        , '26%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                        {% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                                            {{ Content.P' . $personId . '.Input.Bad.Missing }}
                                        {% else %}
                                            0
                                        {% endif %}'
                        )
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->styleMarginTop('40px')
                        ->styleAlignRight()
                        , '5%')
                    ->addElementColumn((new Element()))
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                                Radebeul, {{ Content.P' . $personId . '.Input.Date }}
                            ')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->styleMarginTop('20px')
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                        , '30%')
                    ->addElementColumn((new Element()))
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                                Ort, Datum
                            ')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->styleMarginTop('0px')
                        , '30%')
                    ->addElementColumn((new Element()))
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleMarginTop('25px')
                        ->styleBorderBottom()
                        , '30%')
                    ->addElementColumn((new Element()), '40%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleMarginTop('25px')
                        ->styleBorderBottom()
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                                {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                    {{ Content.P' . $personId . '.Headmaster.Description }}
                                {% else %}
                                    Schulleiter(in)
                                {% endif %}'
                        )
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize('11px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                    {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                                {% else %}
                                    Klassenlehrer(in)
                                {% endif %}'
                        )
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize('11px')
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
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize('11px')
                        ->stylePaddingTop('2px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                    {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize('11px')
                        ->stylePaddingTop('2px')
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize($textSize)
                        ->setContent('Zur Kenntnis genommen:')
                        ->styleMarginTop('25px')
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleMarginTop('35px')
                        ->styleBorderBottom()
                        , '50%')
                    ->addElementColumn((new Element())
                        , '20%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('Personensorgeberechtigte')
                        ->styleFontFamily($fontFamily)
                        ->styleTextSize('11px')
                        ->styleAlignCenter()
                        , '50%')
                    ->addElementColumn((new Element())
                        , '20%')
                )
            );
    }
}