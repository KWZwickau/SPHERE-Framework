<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 15:07
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReport;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class G01
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->addElement((new Element())
                ->setContent('G01. Klassenfrequenz im Schuljahr {{ Content.SchoolYear.Current }} zum Stichtag 02. September {{ Content.Year.Current }}')
                ->styleTextBold()
                ->styleMarginTop('20px')
            );

//        $sliceList[] = (new Slice())
//            ->addElement((new Element())
//                ->setContent('weicht von Vorlage ab')
//                ->styleMarginBottom('8px')
//            );

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
                    ->styleBorderRight()
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px'), '40%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufen')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('5')
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('6')
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('7')
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10'), '16.67%'
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
            for ($level = 5; $level <= 10; $level++) {
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

        for ($level = 5; $level <= 10; $level++) {
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