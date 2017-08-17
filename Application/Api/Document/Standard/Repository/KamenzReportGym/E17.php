<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 14:57
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E17
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('E17. Anzahl der Leistungskurse an dieser Schule im Schuljahr {{ Content.SchoolYear.Current }} nach Jahrgangsstufen')
                )
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderAll()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fach')
                    ->styleBorderRight()
                    ->stylePaddingTop('17.6px')
                    ->stylePaddingBottom('17.6px'), '40%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Anzahl der eingerichteten Leistungskurs')
                            ->styleBorderBottom()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleTextBold()
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '33.34%'
                        )
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('davon in der Jahrgangsstufe')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('11')
                                    ->styleBorderRight(), '50%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('12'), '50%'
                                )
                            ), '66.66%'
                        )
                    ), '60%'
                )
            );

        for ($i = 0; $i < 15; $i++) {
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E17.S' . $i . '.SubjectName is not empty) %}
                                {{ Content.E17.S' . $i . '.SubjectName }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->stylePaddingLeft('5px')
                    ->styleBorderRight()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '40%'
                );

                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E17.S' . $i . '.TotalCount is not empty) %}
                                {{ Content.E17.S' . $i . '.TotalCount }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight()
                        ->styleAlignCenter()
                        ->styleTextBold()
                        ->styleBorderRight(), '20%'
                    );

            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E17.S' . $i . '.L11 is not empty) %}
                                {{ Content.E17.S' . $i . '.L11 }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight()
                    ->styleAlignCenter()
                    ->styleBorderRight(), '20%'
                );

            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E17.S' . $i . '.L12 is not empty) %}
                                {{ Content.E17.S' . $i . '.L12 }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight()
                    ->styleAlignCenter()
                    ->styleBorderRight(), '20%'
                );

            $sliceList[] = (new Slice())
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->addSection($section);
        }

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleTextBold()
            ->styleAlignCenter()
            ->styleBackgroundColor('lightgrey')
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Insgesamt')
                    ->styleBorderRight(), '40%'
                )->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E17.TotalCount.TotalCount is not empty) %}
                            {{ Content.E17.TotalCount.TotalCount }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleBorderRight(), '20%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E17.TotalCount.L11 is not empty) %}
                            {{ Content.E17.TotalCount.L11 }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleBorderRight(), '20%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E17.TotalCount.L12 is not empty) %}
                            {{ Content.E17.TotalCount.L12 }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleBorderRight(), '20%'
                )
            );

        return $sliceList;
    }
}