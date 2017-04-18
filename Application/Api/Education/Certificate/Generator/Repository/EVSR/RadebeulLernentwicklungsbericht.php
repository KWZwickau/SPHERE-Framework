<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.11.2016
 * Time: 11:23
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class RadebeulLernentwicklungsbericht
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR
 */
class RadebeulLernentwicklungsbericht extends Certificate
{

    /**
     * @param bool $IsSample
     *
     * @return Frame
     */
    public function buildCertificate($IsSample = true)
    {

        $fontFamily = 'MetaPro';
        $textSize = '12pt';

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
                                ->styleHeight('130px')
                                : (new Element())
                                ->setContent('&nbsp;')
                                ->styleHeight('130px')
                                ->styleMarginBottom('0px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent(
                                new Container('Lernentwicklungsbericht') .
                                new Container('der') .
                                new Container('Evangelischen Grundschule Radebeul')
                            )
                            ->styleLineHeight('80%')
                            ->styleTextSize('20pt')
                            ->styleFontFamily($fontFamily)
                            ->styleAlignCenter()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('- Staatlich anerkannte Ersatzschule in freier Trägerschaft -')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleLineHeight('90%')
                            ->styleAlignCenter()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent(
                                new Container('zum') .
                                new Container('Schuljahr {{ Content.Division.Data.Year }}')
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
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('für:')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleMarginTop('25px')
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Person.Data.Name.Last }}, {{ Content.Person.Data.Name.First }}')
                            ->styleMarginTop('25px')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            , '72%')
                        ->addElementColumn((new Element())
                            , '4%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('geboren am:')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleMarginTop('5px')
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Person.Common.BirthDates.Birthday is not empty) %}
                                    {{ Content.Person.Common.BirthDates.Birthday|date("d.m.Y") }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleMarginTop('5px')
                            , '72%'
                        )
                        ->addElementColumn((new Element())
                            , '4%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Klasse:')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleMarginTop('5px')
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {{ Content.Division.Data.Level.Name }}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleMarginTop('5px')
                            , '72%'
                        )
                        ->addElementColumn((new Element())
                            , '4%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->setContent('Hinweis zu den einzelnen Lernbereichen / Fächern:')
                            ->styleMarginTop('30px')
                            ->styleTextBold()
                            , '96%'
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('
                            {% if(Content.Input.Rating is not empty) %}
                                {{ Content.Input.Rating|nl2br }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                            ->styleAlignJustify()
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleLineHeight('80%')
                            ->styleMarginTop('30px')
                            , '92%')
                        ->addElementColumn((new Element())
                            , '4%')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Versäumte Tage:')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleMarginTop('40px')
                            , '22%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                        {% if(Content.Input.Total.Missing is not empty) %}
                                            {{ Content.Input.Total.Missing }}
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
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                        {% if(Content.Input.Bad.Missing is not empty) %}
                                            {{ Content.Input.Bad.Missing }}
                                        {% else %}
                                            0
                                        {% endif %}'
                            )
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize($textSize)
                            ->styleMarginTop('40px')
                            ->styleAlignRight()
                            , '8%')
                        ->addElementColumn((new Element()))
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                Radebeul, {{ Content.Input.Date }}
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
                            , '4%')
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
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleMarginTop('25px')
                            ->styleBorderBottom()
                            , '30%')
                        ->addElementColumn((new Element()), '32%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleMarginTop('25px')
                            ->styleBorderBottom()
                            , '30%')
                        ->addElementColumn((new Element())
                            , '4%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Headmaster.Description is not empty) %}
                                    {{ Content.Headmaster.Description }}
                                {% else %}
                                    Schulleiter(in)
                                {% endif %}'
                            )
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize('11px')
                            , '30%')
                        ->addElementColumn((new Element())
                            , '32%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.DivisionTeacher.Description is not empty) %}
                                    {{ Content.DivisionTeacher.Description }}
                                {% else %}
                                    Klassenlehrer(in)
                                {% endif %}'
                            )
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize('11px')
                            , '30%')
                        ->addElementColumn((new Element())
                            , '4%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent(
                                '{% if(Content.Headmaster.Name is not empty) %}
                                {{ Content.Headmaster.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                            )
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize('11px')
                            ->stylePaddingTop('2px')
                            , '30%')
                        ->addElementColumn((new Element())
                            , '32%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.DivisionTeacher.Name is not empty) %}
                                    {{ Content.DivisionTeacher.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize('11px')
                            ->stylePaddingTop('2px')
                            , '30%')
                        ->addElementColumn((new Element())
                            , '4%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '4%')
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
                            , '16%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '34%')
                        ->addElementColumn((new Element())
                            ->setContent('Erziehungsberechtigte')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            , '50%')
                        ->addElementColumn((new Element())
                            , '16%')
                    )
                )
            )
        );
    }
}