<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 15:20
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS;


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
                ->setContent('E05. Schüler im Ethik- bzw. Religionsunterricht im Schuljahr {{ Content.SchoolYear.Current }} nach Klassenstufen')
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
                    ->styleBorderRight()
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px'), '50%'
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
                            ->setContent('1')
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('2')
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('3')
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('4')
                            ->styleBorderRight()
                            , '25%'
                        )
                    ), '40%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Insgesamt')
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
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '50%'
                );
            for ($level = 1; $level < 5; $level++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E05.S' . $i . '.L' . $level . ' is not empty) %}
                                {{ Content.E05.S' . $i . '.L' . $level . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), '10%'
                    );
            }
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E05.S' . $i . '.TotalCount is not empty) %}
                                {{ Content.E05.S' . $i . '.TotalCount }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
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
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), '50%'
            );

        for ($level = 1; $level < 5; $level++) {
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E05.TotalCount.L' . $level . ' is not empty) %}
                                {{ Content.E05.TotalCount.L' . $level . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), '10%'
                );
        }

        $section
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
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