<?php
namespace SPHERE\Application\Api\Education\Graduation\Certificate\Repository;

use SPHERE\Application\Api\Education\Graduation\Certificate\Certificate;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Document;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Element;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Frame;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Page;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Section;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Slice;

class CosHjPri extends Certificate
{

    /**
     * @return Frame
     */
    public function buildCertificate()
    {

        $Header = (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Coswig Halbjahresinformation (Primarstufe).pdf')
                    ->styleTextSize('12px')
                    ->styleTextColor('#CCC')
                    ->styleAlignCenter()
                    , '25%')
                ->addElementColumn((new Element\Sample())
                    ->setContent('MUSTER')
                    ->styleTextSize('30px')
                    , '50%')
                ->addElementColumn((new Element())
                    , '25%')
            );

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice(
                    $Header
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('FREISTAAT SACHSEN')
                        ->styleAlignCenter()
                        ->styleTextSize('22px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('Name der Schule')
                            ->styleAlignCenter()
                            ->styleMarginTop('80px')
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('Evangelische Schule Coswig')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleMarginTop('80px')
                            , '40%')
                        ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/Coswig_logo.png', '120px'))
                            ->styleAlignCenter()
                            , '30%')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Halbjahresinformation der Schule (Primarstufe)')
                        ->styleTextSize('22px')
                        ->styleTextBold()
                        ->styleAlignCenter()
                        ->styleMarginTop('20px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klasse')
                            , '7%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            , '38%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            , '7%')
                        ->addElementColumn((new Element())
                            ->setContent('1. Schulhalbjahr')
                            , '16%')
                        ->addElementColumn((new Element())
                            ->setContent('2015/16')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            , '32%')
                    )->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vor- und Zuname:')
                            , '18%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Person.Data.Name.First }}
                                          {{ Content.Person.Data.Name.Last }}')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            , '64%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom()
                            , '18%')
                    )->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Betragen')
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.ToDO is not empty) %}
                                    {{ Content.Grade.Data.ToDO }}
                                {% else %}
                                    ---
                                {% endif %}')//ToDO Kopfnoten
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#F1F1F1')
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Mitarbeit')
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.ToDO is not empty) %}
                                    {{ Content.Grade.Data.ToDO }}
                                {% else %}
                                    ---
                                {% endif %}')//ToDO Kopfnoten
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#F1F1F1')
                            , '9%')
                    )
                    ->styleMarginTop('40px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Fleiß')
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.ToDO is not empty) %}
                                    {{ Content.Grade.Data.ToDO }}
                                {% else %}
                                    ---
                                {% endif %}')//ToDO Kopfnoten
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#F1F1F1')
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Ordnung')
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.ToDO is not empty) %}
                                    {{ Content.Grade.Data.ToDO }}
                                {% else %}
                                    ---
                                {% endif %}')//ToDO Kopfnoten
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#F1F1F1')
                            , '9%')
                    )
                    ->styleMarginTop('17px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Leistung in den einzelnen Fächern')
                        ->styleTextItalic()
                        ->styleMarginTop('40px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Deutsch')
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.DE is not empty) %}
                                    {{ Content.Grade.Data.DE }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#F1F1F1')
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Mathematik')
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.MA is not empty) %}
                                    {{ Content.Grade.Data.MA }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#F1F1F1')
                            , '9%')
                    )
                    ->styleMarginTop('22px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Sachunterricht')
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.ToDO is not empty) %}
                                    {{ Content.Grade.Data.ToDO }}
                                {% else %}
                                    ---
                                {% endif %}')// ToDO Sachunterricht ist kein vorgegebenes Fach
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#F1F1F1')
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Werken')
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.ToDO is not empty) %}
                                    {{ Content.Grade.Data.ToDO }}
                                {% else %}
                                    ---
                                {% endif %}')// ToDO Werken ist kein vorgegebenes Fach
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#F1F1F1')
                            , '9%')
                    )
                    ->styleMarginTop('17px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Kunst')
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.KU is not empty) %}
                                    {{ Content.Grade.Data.KU }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#F1F1F1')
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Ev. Religion')
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.REV is not empty) %}
                                    {{ Content.Grade.Data.REV }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#F1F1F1')
                            , '9%')
                    )
                    ->styleMarginTop('17px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Musik')
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.MU is not empty) %}
                                    {{ Content.Grade.Data.MU }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#F1F1F1')
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('Sport')
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.ToDO is not empty) %}
                                    {{ Content.Grade.Data.ToDO }}
                                {% else %}
                                    ---
                                {% endif %}')//ToDO Sport ist kein vorgegebenes Fach
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#F1F1F1')
                            , '9%')
                    )
                    ->styleMarginTop('17px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Englisch')
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Grade.Data.EN is not empty) %}
                                    {{ Content.Grade.Data.EN }}
                                {% else %}
                                    ---
                                {% endif %}')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#F1F1F1')
                            , '9%')
                        ->addElementColumn((new Element())
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom()
                            , '39%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleAlignCenter()
                            ->styleBackgroundColor('#F1F1F1')
                            , '9%')
                    )
                    ->styleMarginTop('17px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Notenstufen 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhaft, 6 = ungenügend')
                        ->styleTextSize('9px')
                        ->styleMarginTop('17px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Bemerkungen:')
                            ->styleTextItalic()
                            , '15%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Remark is not empty) %}
                                    {{ Content.Input.Remark|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            , '85%')
                    )
                    ->styleMarginTop('30px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('&nbsp;')
                    )
                    ->styleMarginTop('10px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Fehltage entschuldigt:')
                            , '22%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Input.Missing }}')
                            , '7%')
                        ->addElementColumn((new Element())
                            ->setContent('unentschuldigt:')
                            , '15%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Input.Bad.Missing }}')
                            , '7%')
                        ->addElementColumn((new Element())
                            , '49%')
                    )
                    ->styleMarginTop('15px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Datum:')
                            , '7%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Date is not empty) %}
                                    {{ Content.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            , '20%')
                        ->addElementColumn((new Element())
                            , '56%')
                    )
                    ->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            , '35%')
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            , '35%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schulleiter/in')
                            ->styleTextSize('11px')
                            , '35%')
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('Klassenleiter/in')
                            ->styleTextSize('11px')
                            , '35%')
                    )
                    ->styleMarginTop('25px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Zur Kenntnis genommen:')
                            , '25%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom()
                            , '75%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Personensorgeberechtigte/r')
                            ->styleAlignCenter()
                            ->styleTextSize('11px')
                            , '100%')
                    )
                    ->styleMarginTop('25px')
                )
            )
        );
    }
}