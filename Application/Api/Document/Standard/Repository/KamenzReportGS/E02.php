<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 15:19
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class E02
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS
 */
class E02
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
                ->setContent('E02. Schüler im Schuljahr {{ Content.SchoolYear.Current }} nach Geburtsjahren und Klassenstufen')
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
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop('26.1px')
                    ->stylePaddingBottom('26.1px'), $left
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe in diesem Schuljahr')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('1')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('2')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('3')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->setContent('4')
                            ->styleAlignCenter()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px'), '25%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
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
                            ->styleAlignCenter()
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
                            ->styleAlignCenter()
                            ->styleTextBold()
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
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
                            {% if (Content.E02.Y' . $i . '.YearName is not empty) %}
                                {{ Content.E02.Y' . $i . '.YearName }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), $left
                );
            for ($level = 1; $level < 5; $level++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E02.Y' . $i . '.L' . $level . '.m is not empty) %}
                                {{ Content.E02.Y' . $i . '.L' . $level . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E02.Y' . $i . '.L' . $level . '.w is not empty) %}
                                {{ Content.E02.Y' . $i . '.L' . $level . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '5%'
                    );
            }

            $section
//                ->addElementColumn((new Element())
//                    ->setContent('
//                            {% if (Content.E02.Y' . $i . '.LMigration.m is not empty) %}
//                                {{ Content.E02.Y' . $i . '.LMigration.m }}
//                            {% else %}
//                                &nbsp;
//                            {% endif %}
//                        ')
//                    ->styleBackgroundColor('lightgrey')
//                    ->styleBorderRight(), '5%'
//                )
//                ->addElementColumn((new Element())
//                    ->setContent('
//                            {% if (Content.E02.Y' . $i . '.LMigration.w is not empty) %}
//                                {{ Content.E02.Y' . $i . '.LMigration.w }}
//                            {% else %}
//                                &nbsp;
//                            {% endif %}
//                        ')
//                    ->styleBackgroundColor('lightgrey')
//                    ->styleBorderRight(), '5%'
//                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E02.Y' . $i . '.m is not empty) %}
                                {{ Content.E02.Y' . $i . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E02.Y' . $i . '.w is not empty) %}
                                {{ Content.E02.Y' . $i . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
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
                ->styleAlignCenter()
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), $left
            );
        for ($level = 1; $level < 5; $level++) {
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E02.TotalCount.L' . $level . '.m is not empty) %}
                                {{ Content.E02.TotalCount.L' . $level . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E02.TotalCount.L' . $level . '.w is not empty) %}
                                {{ Content.E02.TotalCount.L' . $level . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                );
        }

        $section
//            ->addElementColumn((new Element())
//                ->setContent('
//                            {% if (Content.E02.TotalCount.LMigration.m is not empty) %}
//                                {{ Content.E02.TotalCount.LMigration.m }}
//                            {% else %}
//                                &nbsp;
//                            {% endif %}
//                        ')
//                ->styleBackgroundColor('lightgrey')
//                ->styleBorderRight(), '5%'
//            )
//            ->addElementColumn((new Element())
//                ->setContent('
//                            {% if (Content.E02.TotalCount.LMigration.w is not empty) %}
//                                {{ Content.E02.TotalCount.LMigration.w }}
//                            {% else %}
//                                &nbsp;
//                            {% endif %}
//                        ')
//                ->styleBackgroundColor('lightgrey')
//                ->styleBorderRight(), '5%'
//            )
            ->addElementColumn((new Element())
                ->setContent('
                            {% if (Content.E02.TotalCount.m is not empty) %}
                                {{ Content.E02.TotalCount.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                ->styleAlignCenter()
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                            {% if (Content.E02.TotalCount.w is not empty) %}
                                {{ Content.E02.TotalCount.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                ->styleAlignCenter()
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