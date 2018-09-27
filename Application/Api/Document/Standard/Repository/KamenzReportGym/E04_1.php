<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 09:51
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E04_1
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E04.1 Schüler im Schuljahr {{ Content.SchoolYear.Current }} nach der Anzahl der derzeit 
                    erlernten Fremdsprachen und Klassen- </br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    bzw. Jahrgangsstufen')
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
                    ->setContent('Anzahl der<br/>Fremdsprachen')
                    ->styleBorderRight()
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px'), '28%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe')
                            ->styleBorderRight()
                            , '100%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('5')
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('6')
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('7')
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10')
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('11')
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('12')
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px')
                            ->styleBorderRight(), '12.5%'
                        )
                    )
                )
                ->addElementColumn((new Element())
                    ->setContent('Insges.')
                    ->styleTextBold()
                    ->stylePaddingTop('17.1px')
                    ->stylePaddingBottom('17.1px'), '8%'
                )
            );

        for ($i = 0; $i < 5; $i++) {
            switch ($i) {
                case 0: $text = 'keine'; break;
                case 1: $text = 'eine'; break;
                case 2: $text = 'zwei'; break;
                case 3: $text = 'drei'; break;
                case 4: $text = 'vier und mehr'; break;
                default: $text = '&nbsp;';
            }

            $section = new Section();

            $section
                ->addElementColumn((new Element())
                    ->setContent($text)
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '28%'
                );

            for ($level = 5; $level < 13; $level++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E04_1.F' . $i . '.L' . $level . ' is not empty) %}
                                {{ Content.E04_1.F' . $i . '.L' . $level . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), '8%'
                    );
            }

            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E04_1.F' . $i . '.TotalCount is not empty) %}
                                {{ Content.E04_1.F' . $i . '.TotalCount }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleTextBold()
                    ->styleBackgroundColor('lightgrey'), '8%'
                );

            $sliceList[] = (new Slice())
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->addSection($section);
        }

        /**
         * Total
         */
        $section = new Section();

        $section
            ->addElementColumn((new Element())
                ->setContent('Insgesamt')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), '28%'
            );

        for ($level = 5; $level < 13; $level++) {
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E04_1.TotalCount.L' . $level . ' is not empty) %}
                                {{ Content.E04_1.TotalCount.L' . $level . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), '8%'
                );
        }

        $section
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBackgroundColor('lightgrey'), '8%'
            );

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