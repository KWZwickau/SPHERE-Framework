<?php
namespace SPHERE\Application\Api\Education\Graduation\Certificate\Repository;

use SPHERE\Application\Api\Education\Graduation\Certificate\Certificate;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Document;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Element;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Frame;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Page;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Section;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Slice;

class HorHjOne extends Certificate
{

    /**
     * @return Frame
     */
    public function buildCertificate()
    {

        $Header = (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Hormersdorf Halbjahreszeugnis Klasse 1.pdf')
                    ->styleTextSize('12px')
                    ->styleTextColor('#CCC')
                    ->styleAlignCenter()
                    , '25%')
                ->addElementColumn((new Element\Sample())
                    ->styleTextSize('30px')
                )
                ->addElementColumn((new Element())
                    , '25%')
            );

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice(
                    $Header
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/Hormersdorf_logo.jpg', '150px'))
                            ->styleAlignCenter()
                            , '25%')
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Name der Schule:')
                                    ->styleTextSize('11px')
                                    ->styleMarginTop('6px')
                                    , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent('Freie Evangelische Grundschule Hormersdorf')
                                    ->styleTextSize('17px')
                                    ->styleTextBold()
                                    ->styleBorderBottom('1px', '#BBB')
                                    ->styleAlignCenter()
                                    , '80%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    , '27%')
                                ->addElementColumn((new Element())
                                    ->setContent('(Staatlich anerkannte Ersatzschule)')
                                    ->styleAlignCenter()
                                    , '73%')
                            )
                            ->styleMarginTop('30px')
                            , '75%')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('HALBJAHRESINFORMATION')
                        ->styleTextSize('24px')
                        ->styleTextBold()
                        ->styleAlignCenter()
                        ->styleMarginTop('20px')
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('DER GRUNDSCHULE')
                        ->styleTextSize('24px')
                        ->styleTextBold()
                        ->styleAlignCenter()
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klasse')
                            ->styleBorderBottom('1px', '#BBB')
                            , '8%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }}')
                            ->styleBorderBottom('1px', '#BBB')
                            , '47%')
                        ->addElementColumn((new Element())
                            ->setContent('1. Schulhalbjahr')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleAlignRight()
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('2015/16')
                            ->styleBorderBottom('1px', '#BBB')
                            ->styleAlignCenter()
                            , '15%')
                    )->styleMarginTop('30px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vor- und Zuname:')
                            ->styleBorderBottom('1px', '#BBB')
                            , '20%')
                        ->addElementColumn((new Element())
                            ->setContent('{{ Content.Person.Data.Name.First }}
                                          {{ Content.Person.Data.Name.Last }}')
                            ->styleBorderBottom('1px', '#BBB')
                            , '80%')
                    )->styleMarginTop('5px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('{% if(Content.Input.Remark is not empty) %}
                                    {{ Content.Input.Remark|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleMarginTop('15px')
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Fehltage entschuldigt:')
                            ->styleBorderBottom('1px', '#BBB')
                            , '23%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Missing is not empty) %}
                                    {{ Content.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleBorderBottom('1px', '#BBB')
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('unentschuldigt:')
                            ->styleBorderBottom('1px', '#BBB')
                            , '17%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Bad.Missing is not empty) %}
                                    {{ Content.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleBorderBottom('1px', '#BBB')
                            , '50%')
                    )->styleMarginTop('300px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Datum:')
                            ->styleBorderBottom('1px', '#BBB')
                            , '10%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Input.Date is not empty) %}
                                    {{ Content.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleAlignCenter()
                            ->styleBorderBottom('1px', '#BBB')
                            , '25%')
                        ->addElementColumn((new Element())
                            , '65%')
                    )->styleMarginTop('30px')
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '35%')
                        ->addElementColumn((new Element())
                            ->setContent('Dienststempel der Schule')
                            ->styleTextSize('9px')
                            ->styleAlignCenter()
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderBottom('1px', '#BBB')
                            , '35%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '65%')
                        ->addElementColumn((new Element())
                            ->setContent('Klassenlehrer/in')
                            ->styleAlignCenter()
                            ->styleTextSize('11px')
                            , '35%')
                    )
                    ->styleMarginTop('30px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Zur Kenntnis genommen:')
                        ->styleBorderBottom('1px', '#BBB')
                    )
                    ->styleMarginTop('30px')
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('Personensorgeberechtigte/r')
                        ->styleAlignCenter()
                    )
                )
            )
        );
    }
}