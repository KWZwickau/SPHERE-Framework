<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 15:21
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class G01
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('G01. Klassenfrequenz im Schuljahr {{ Content.SchoolYear.Current }} zum Stichtag 25. Oktober {{ Content.Year.Current }}')
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
                    ->setContent('Klasse')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleBorderRight(), '40%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufen')
                            ->styleBorderRight()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('1')
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('2')
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('jüU 1+2')
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('3')
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('4')
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('jüU 3+4')
                            ->styleBorderRight(), '16.66%'
                        )
                    ), '60%'
                )
            );

        for ($i = 1; $i < 6; $i++) {
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent($i . '. Klasse')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(),
                    '40%'
                );
            for ($level = 1; $level < 5; $level++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.G01.D' . $i . '.L' . $level . ' is not empty) %}
                                {{ Content.G01.D' . $i . '.L' . $level . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), '10%'
                    );

                if ($level == 2) {
                    // jüU 1+2
                    $section
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderRight(), '10%'
                        );
                }

                if ($level == 4) {
                    // jüU 3+4
                    $section
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleBorderRight(), '10%'
                        );
                }
            }

            $sliceList[] = (new Slice())
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->addSection($section);
        }

        /**
         * TotalCount
         */
        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Insgesamt')
                ->styleBorderRight(), '40%'
            );

        for ($level = 1; $level < 5; $level++) {
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.G01.L' . $level . '.TotalCount is not empty) %}
                                {{ Content.G01.L' . $level . '.TotalCount }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), '10%'
                );

            if ($level == 2) {
                // jüU 1+2
                $section
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderRight(), '10%'
                    );
            }

            if ($level == 4) {
                // jüU 3+4
                $section
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderRight(), '10%'
                    );
            }
        }

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleTextBold()
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection($section);

        return $sliceList;
    }
}