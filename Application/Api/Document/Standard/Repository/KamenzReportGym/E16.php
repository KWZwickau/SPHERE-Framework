<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 12:58
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E16
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E16. Schüler in Grundkursen an dieser Schule im Schuljahr {{ Content.SchoolYear.Current }} 
                              nach Jahrgangsstufen <br> 
                              E16.1 Schüler in Grundkursen gemäß § 41 Abs. 1 SOGYA bzw. in fächerverbindenden Grundkursen
                              im Schuljahr {{ Content.SchoolYear.Current }} nach Jahrgangsstufen' )
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderAll()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fach')
                    ->styleBorderRight()
                    ->stylePaddingTop('34.1px')
                    ->stylePaddingBottom('34.1px'), '40%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Jahrgangsstufe 11')
                            ->styleBorderRight()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Anzahl der<br/>eingerich-<br/>teten<br/>Grundkurse')
                            ->styleBorderRight(), '40%'
                        )
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Schüler')
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('m')
                                    ->styleBorderRight()
                                    ->stylePaddingTop('16.5px')
                                    ->stylePaddingBottom('16.5px'), '50%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('w')
                                    ->styleBorderRight()
                                    ->stylePaddingTop('16.5px')
                                    ->stylePaddingBottom('16.5px'), '50%'
                                )
                            ), '60%'
                        )
                    ), '30%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Jahrgangsstufe 12')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Anzahl der<br/>eingerich-<br/>teten<br/>Grundkurse')
                            ->styleBorderRight(), '40%'
                        )
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Schüler')
                                    ->styleBorderBottom()
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('m')
                                    ->styleBorderRight()
                                    ->stylePaddingTop('16.5px')
                                    ->stylePaddingBottom('16.5px'), '50%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('w')
                                    ->stylePaddingTop('16.5px')
                                    ->stylePaddingBottom('16.5px'), '50%'
                                )
                            ), '60%'
                        )
                    ), '30%'
                )
            );

        for ($i = 0; $i < 30; $i++) {
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E16.S' . $i . '.SubjectName is not empty) %}
                                {{ Content.E16.S' . $i . '.SubjectName }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->stylePaddingLeft('5px')
                    ->styleBorderRight()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '40%'
                );
            for ($level = 11; $level < 13; $level++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E16.S' . $i . '.L' . $level . '.CoursesCount is not empty) %}
                                {{ Content.E16.S' . $i . '.L' . $level . '.CoursesCount }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight()
                        ->styleAlignCenter()
                        ->styleBorderRight(), '12%'
                    );
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E16.S' . $i . '.L' . $level . '.m is not empty) %}
                                {{ Content.E16.S' . $i . '.L' . $level . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight()
                        ->styleAlignCenter()
                        ->styleBorderRight(), '9%'
                    );
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E16.S' . $i . '.L' . $level . '.w is not empty) %}
                                {{ Content.E16.S' . $i . '.L' . $level . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight()
                        ->styleAlignCenter()
                        ->styleBorderRight(), '9%'
                    );
            }

            $sliceList[] = (new Slice())
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->addSection($section);
        }

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleAlignCenter()
            ->styleTextBold()
            ->styleBackgroundColor('lightgrey')
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Insgesamt')
                    ->styleBorderRight(), '40%'
                )
                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E16.TotalCount.L11.CoursesCount is not empty) %}
//                            {{ Content.E16.TotalCount.L11.CoursesCount }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E16.TotalCount.L11.m is not empty) %}
//                            {{ Content.E16.TotalCount.L11.m }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E16.TotalCount.L11.w is not empty) %}
//                            {{ Content.E16.TotalCount.L11.w }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E16.TotalCount.L12.CoursesCount is not empty) %}
//                            {{ Content.E16.TotalCount.L12.CoursesCount }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '12%'
                )
                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E16.TotalCount.L12.m is not empty) %}
//                            {{ Content.E16.TotalCount.L12.m }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E16.TotalCount.L12.w is not empty) %}
//                            {{ Content.E16.TotalCount.L12.w }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '9%'
                )
            );

        return $sliceList;
    }
}