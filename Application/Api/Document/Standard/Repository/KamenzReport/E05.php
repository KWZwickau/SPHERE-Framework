<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 14:35
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReport;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E05
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E05. Schüler im Ethik- bzw. Religionsunterricht im Schuljahr {{Content.SchoolYear.Current}} nach Klassenstufen')
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
                    ->setContent('Teilnahme am Unterricht im Fach')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px'), '30%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            , '100%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('5')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('6')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('7')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            , '10%'
                        )
                    ), '50%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Sonderkl.')
                    ->styleAlignCenter()
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleBorderRight()
                    , '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Insgesamt')
                    ->styleAlignCenter()
                    ->styleTextBold()
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px'), '10%'
                )
            );

        for ($i = 0; $i < 4; $i++) {
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E05.S' . $i . '.SubjectName is not empty) %}
                                {{ Content.E05.S' . $i . '.SubjectName }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '30%'
                );
            for ($level = 5; $level <= 10; $level++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E05.S' . $i . '.L' . $level . ' is not empty) %}
                                {{ Content.E05.S' . $i . '.L' . $level . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '8.33%'
                    );
            }
            $section
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E05.S' . $i . '.TotalCount is not empty) %}
                                {{ Content.E05.S' . $i . '.TotalCount }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold(), '10%'
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
                ->styleAlignCenter()
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), '30%'
            );

        for ($level = 5; $level <= 10; $level++) {
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E05.TotalCount.L' . $level . ' is not empty) %}
                                {{ Content.E05.TotalCount.L' . $level . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '8.33%'
                );
        }

        $section
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), '10%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
                ->styleBackgroundColor('lightgrey'), '10%'
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