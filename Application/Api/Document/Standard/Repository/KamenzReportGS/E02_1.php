<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 05.09.2018
 * Time: 12:01
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS;


use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class E02_1
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS
 */
class E02_1
{
    public static function getContent()
    {
        $sliceList = array();

        $left = '50%';

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E02.1 Darunter <u>Schüler, deren Herkunftssprache nicht oder nicht ausschließlich Deutsch ist</u>, im </br> 
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Schuljahr
                    {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen')
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
                    ->setContent('Geburtsjahr')
                    ->styleBorderRight()
                    ->stylePaddingTop('26.1px')
                    ->stylePaddingBottom('26.1px'), $left
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe in diesem Schuljahr')
                            ->styleBorderRight()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('1')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('2')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('3')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->setContent('4')
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '25%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '12.5%'
                        )
                    ), '40%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
//                        ->addElementColumn((new Element())
//                            ->setContent('Vorb.kl. u.<br/>-gr. f.<br/>Migranten')
//                            ->styleBorderBottom()
//                            ->styleBorderRight(), '50%'
//                        )
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleTextBold()
                            ->styleBorderBottom()
                            ->stylePaddingTop('17.1px')
                            ->stylePaddingBottom('17.1px'), '50%'
                        )
                    )
                    ->addSection((new Section())
//                        ->addElementColumn((new Element())
//                            ->setContent('m')
//                            ->styleBorderRight(), '25%'
//                        )
//                        ->addElementColumn((new Element())
//                            ->setContent('w')
//                            ->styleBorderRight(), '25%'
//                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleTextBold()
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleTextBold()
                            , '25%'
                        )
                    ), '10%'
                )
            );

        for ($i = 0; $i < 10; $i++) {
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E02_1.Y' . $i . '.YearName is not empty) %}
                                {{ Content.E02_1.Y' . $i . '.YearName }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), $left
                );
            for ($level = 1; $level < 5; $level++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E02_1.Y' . $i . '.L' . $level . '.m is not empty) %}
                                {{ Content.E02_1.Y' . $i . '.L' . $level . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E02_1.Y' . $i . '.L' . $level . '.w is not empty) %}
                                {{ Content.E02_1.Y' . $i . '.L' . $level . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), '5%'
                    );
            }

            $section
//                ->addElementColumn((new Element())
//                    ->setContent('
//                            {% if (Content.E02_1.Y' . $i . '.LMigration.m is not empty) %}
//                                {{ Content.E02_1.Y' . $i . '.LMigration.m }}
//                            {% else %}
//                                &nbsp;
//                            {% endif %}
//                        ')
//                    ->styleBackgroundColor('lightgrey')
//                    ->styleBorderRight(), '5%'
//                )
//                ->addElementColumn((new Element())
//                    ->setContent('
//                            {% if (Content.E02_1.Y' . $i . '.LMigration.w is not empty) %}
//                                {{ Content.E02_1.Y' . $i . '.LMigration.w }}
//                            {% else %}
//                                &nbsp;
//                            {% endif %}
//                        ')
//                    ->styleBackgroundColor('lightgrey')
//                    ->styleBorderRight(), '5%'
//                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E02_1.Y' . $i . '.m is not empty) %}
                                {{ Content.E02_1.Y' . $i . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E02_1.Y' . $i . '.w is not empty) %}
                                {{ Content.E02_1.Y' . $i . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold(), '5%'
                );

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
                ->setContent('
                            Insgesamt
                        ')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), $left
            );
        for ($level = 1; $level < 5; $level++) {
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E02_1.TotalCount.L' . $level . '.m is not empty) %}
                                {{ Content.E02_1.TotalCount.L' . $level . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E02_1.TotalCount.L' . $level . '.w is not empty) %}
                                {{ Content.E02_1.TotalCount.L' . $level . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), '5%'
                );
        }

        $section
//            ->addElementColumn((new Element())
//                ->setContent('
//                            {% if (Content.E02_1.TotalCount.LMigration.m is not empty) %}
//                                {{ Content.E02_1.TotalCount.LMigration.m }}
//                            {% else %}
//                                &nbsp;
//                            {% endif %}
//                        ')
//                ->styleBackgroundColor('lightgrey')
//                ->styleBorderRight(), '5%'
//            )
//            ->addElementColumn((new Element())
//                ->setContent('
//                            {% if (Content.E02_1.TotalCount.LMigration.w is not empty) %}
//                                {{ Content.E02_1.TotalCount.LMigration.w }}
//                            {% else %}
//                                &nbsp;
//                            {% endif %}
//                        ')
//                ->styleBackgroundColor('lightgrey')
//                ->styleBorderRight(), '5%'
//            )
            ->addElementColumn((new Element())
                ->setContent('
                            {% if (Content.E02_1.TotalCount.m is not empty) %}
                                {{ Content.E02_1.TotalCount.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                            {% if (Content.E02_1.TotalCount.w is not empty) %}
                                {{ Content.E02_1.TotalCount.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                ->styleBackgroundColor('lightgrey'), '5%'
            );

        $sliceList[] = (new Slice())
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleTextBold()
            ->addSection($section);

        return $sliceList;
    }
}