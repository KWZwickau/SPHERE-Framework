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

class E07
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E07. Schüler im Schuljahr {{ Content.SchoolYear.Current }} nach Klassenstufen und der im vergangenen Schuljahr besuchten
                 </br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Schulart')
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
                    ->setContent('Im vergang. Schuljahr besuchte Schulart')
                    ->styleBorderRight()
                    ->stylePaddingTop('26.1px')
                    ->stylePaddingBottom('26.1px'), '50%'
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
                            ->styleBorderRight()
                            ->styleTextBold(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleTextBold(), '25%'
                        )
                    ), '10%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Schulanfänger')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '50%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.NewSchoolStarter.L1.m is not empty) %}
                            {{ Content.E07.NewSchoolStarter.L1.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.NewSchoolStarter.L1.w is not empty) %}
                            {{ Content.E07.NewSchoolStarter.L1.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.NewSchoolStarter.L2.m is not empty) %}
                            {{ Content.E07.NewSchoolStarter.L2.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.NewSchoolStarter.L2.w is not empty) %}
                            {{ Content.E07.NewSchoolStarter.L2.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
//                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E07.NewSchoolStarter.Migration.m is not empty) %}
//                            {{ Content.E07.NewSchoolStarter.Migration.m }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
//                    ->styleAlignCenter()
//                    ->styleBorderRight(), '5%'
//                )
//                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E07.NewSchoolStarter.Migration.w is not empty) %}
//                            {{ Content.E07.NewSchoolStarter.Migration.w }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
//                    ->styleAlignCenter()
//                    ->styleBorderRight(), '5%'
//                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.NewSchoolStarter.TotalCount.m is not empty) %}
                            {{ Content.E07.NewSchoolStarter.TotalCount.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleTextBold()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.NewSchoolStarter.TotalCount.w is not empty) %}
                            {{ Content.E07.NewSchoolStarter.TotalCount.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleTextBold()
                    ->styleBackgroundColor('lightgrey'), '5%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Grundschule')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '50%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.PrimarySchool.L1.m is not empty) %}
                            {{ Content.E07.PrimarySchool.L1.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.PrimarySchool.L1.w is not empty) %}
                            {{ Content.E07.PrimarySchool.L1.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.PrimarySchool.L2.m is not empty) %}
                            {{ Content.E07.PrimarySchool.L2.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.PrimarySchool.L2.w is not empty) %}
                            {{ Content.E07.PrimarySchool.L2.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.PrimarySchool.L3.m is not empty) %}
                            {{ Content.E07.PrimarySchool.L3.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.PrimarySchool.L3.w is not empty) %}
                            {{ Content.E07.PrimarySchool.L3.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.PrimarySchool.L4.m is not empty) %}
                            {{ Content.E07.PrimarySchool.L4.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.PrimarySchool.L4.w is not empty) %}
                            {{ Content.E07.PrimarySchool.L4.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
//                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E07.PrimarySchool.Migration.m is not empty) %}
//                            {{ Content.E07.PrimarySchool.Migration.m }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
//                    ->styleAlignCenter()
//                    ->styleBorderRight(), '5%'
//                )
//                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E07.PrimarySchool.Migration.w is not empty) %}
//                            {{ Content.E07.PrimarySchool.Migration.w }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
//                    ->styleAlignCenter()
//                    ->styleBorderRight(), '5%'
//                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.PrimarySchool.TotalCount.m is not empty) %}
                            {{ Content.E07.PrimarySchool.TotalCount.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleTextBold()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.PrimarySchool.TotalCount.w is not empty) %}
                            {{ Content.E07.PrimarySchool.TotalCount.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleTextBold()
                    ->styleBackgroundColor('lightgrey'), '5%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleTextBold()
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Insgesamt')
                    ->styleBorderRight(), '50%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.TotalCount.L1.m is not empty) %}
                            {{ Content.E07.TotalCount.L1.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.TotalCount.L1.w is not empty) %}
                            {{ Content.E07.TotalCount.L1.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.TotalCount.L2.m is not empty) %}
                            {{ Content.E07.TotalCount.L2.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.TotalCount.L2.w is not empty) %}
                            {{ Content.E07.TotalCount.L2.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.TotalCount.L3.m is not empty) %}
                            {{ Content.E07.TotalCount.L3.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.TotalCount.L3.w is not empty) %}
                            {{ Content.E07.TotalCount.L3.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.TotalCount.L4.m is not empty) %}
                            {{ Content.E07.TotalCount.L4.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.TotalCount.L4.w is not empty) %}
                            {{ Content.E07.TotalCount.L4.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
//                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E07.TotalCount.Migration.m is not empty) %}
//                            {{ Content.E07.TotalCount.Migration.m }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
//                    ->styleAlignCenter()
//                    ->styleBorderRight(), '5%'
//                )
//                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E07.TotalCount.Migration.w is not empty) %}
//                            {{ Content.E07.TotalCount.Migration.w }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
//                    ->styleAlignCenter()
//                    ->styleBorderRight(), '5%'
//                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.TotalCount.TotalCount.m is not empty) %}
                            {{ Content.E07.TotalCount.TotalCount.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleTextBold()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E07.TotalCount.TotalCount.w is not empty) %}
                            {{ Content.E07.TotalCount.TotalCount.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleTextBold()
                    ->styleBackgroundColor('lightgrey'), '5%'
                )
            );

        return $sliceList;
    }
}