<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 09:13
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E01
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E01. Schüler und Klassen im Schuljahr {{ Content.SchoolYear.Current }} nach Klassen- bzw. Jahrgangsstufen')
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Merkmal')
                    ->styleBorderRight()
                    ->stylePaddingTop('17.7px')
                    ->stylePaddingBottom('17.6px'), '10%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassen- bzw. Jahrgangsstufe')
                            ->styleBorderRight()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('5')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('6')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('7')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('11')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('12')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            , '10%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight()
                            , '5%'
                        )
                    ), '80%'
                )
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleBorderBottom()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            , '50%'
                        )
                    ), '10%'
                )
            );

        /**
         * Schüler
         */
        $section = new Section();
        $section
            ->addElementColumn((new Element())
            ->setContent('Schüler')
            ->styleBackgroundColor('lightgrey')
            ->styleBorderRight(), '10%'
        );
        for ($level = 5; $level < 13; $level++) {
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E01.Student.L' . $level . '.m is not empty) %}
                                {{ Content.E01.Student.L' . $level . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E01.Student.L' . $level . '.w is not empty) %}
                                {{ Content.E01.Student.L' . $level . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                );
        }

        $section
            ->addElementColumn((new Element())
                ->setContent('
                        {% if (Content.E01.Student.TotalCount.m is not empty) %}
                            {{ Content.E01.Student.TotalCount.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleTextBold()
                ->styleAlignCenter()
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                        {% if (Content.E01.Student.TotalCount.w is not empty) %}
                            {{ Content.E01.Student.TotalCount.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleTextBold()
                ->styleAlignCenter()
                ->styleBackgroundColor('lightgrey'), '5%'
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection($section);

        /**
         * Klassen
         */
        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Klassen')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), '10%'
            );
        for ($level = 5; $level < 13; $level++) {
            if ($level < 11) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E01.Division.L' . $level . ' is not empty) %}
                                {{ Content.E01.Division.L' . $level . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '10%'
                    );
            } else {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            &nbsp;
                        ')
                        ->styleAlignCenter()
                        ->styleBackgroundColor('lightgrey')
                        ->styleBorderRight(), '10%'
                    );
            }
        }

        $section
            ->addElementColumn((new Element())
                ->setContent('
                        {% if (Content.E01.Division.TotalCount is not empty) %}
                            {{ Content.E01.Division.TotalCount }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleTextBold()
                ->styleAlignCenter()
                ->styleBackgroundColor('lightgrey')
                , '10%'
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection($section);

        return $sliceList;
    }

}