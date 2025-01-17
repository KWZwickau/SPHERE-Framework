<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 15:18
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class E01
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGS
 */
class E01
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
                ->setContent('E01. Schüler und Klassen im Schuljahr {{ Content.SchoolYear.Current }} nach Klassenstufen')
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
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop('26.1px')
                    ->stylePaddingBottom('26.1px'), $left
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe')
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
                            ->setContent('4')
                            ->styleAlignCenter()
                            ->styleBorderRight()
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
                            ->styleTextBold(), '25%'
                        )
                    ), '10%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Schüler')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), $left
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.Student.L1.m is not empty) %}
                            {{ Content.E01.Student.L1.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.Student.L1.w is not empty) %}
                            {{ Content.E01.Student.L1.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.Student.L2.m is not empty) %}
                            {{ Content.E01.Student.L2.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.Student.L2.w is not empty) %}
                            {{ Content.E01.Student.L2.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.Student.L3.m is not empty) %}
                            {{ Content.E01.Student.L3.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.Student.L3.w is not empty) %}
                            {{ Content.E01.Student.L3.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.Student.L4.m is not empty) %}
                            {{ Content.E01.Student.L4.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.Student.L4.w is not empty) %}
                            {{ Content.E01.Student.L4.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
//                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E01.Student.Migration.m is not empty) %}
//                            {{ Content.E01.Student.Migration.m }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
//                    ->styleBorderRight(), '5%'
//                )
//                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E01.Student.Migration.w is not empty) %}
//                            {{ Content.E01.Student.Migration.w }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
//                    ->styleBorderRight(), '5%'
//                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.Student.TotalCount.m is not empty) %}
                            {{ Content.E01.Student.TotalCount.m }}
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
                        {% if (Content.E01.Student.TotalCount.w is not empty) %}
                            {{ Content.E01.Student.TotalCount.w }}
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
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Klassen')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), $left
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.Division.L1 is not empty) %}
                            {{ Content.E01.Division.L1 }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.Division.L2 is not empty) %}
                            {{ Content.E01.Division.L2 }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.Division.L3 is not empty) %}
                            {{ Content.E01.Division.L3 }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.Division.L4 is not empty) %}
                            {{ Content.E01.Division.L4 }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '10%'
                )
//                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E01.Division.Migration is not empty) %}
//                            {{ Content.E01.Division.Migration }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
//                    ->styleBorderRight(), '10%'
//                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.Division.TotalCount is not empty) %}
                            {{ Content.E01.Division.TotalCount }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleTextBold()
                    ->styleBackgroundColor('lightgrey')
                    , '10%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Klassen jüU')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), $left
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.DivisionMixed.L1 is not empty) %}
                            {{ Content.E01.DivisionMixed.L1 }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.DivisionMixed.L2 is not empty) %}
                            {{ Content.E01.DivisionMixed.L2 }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.DivisionMixed.L3 is not empty) %}
                            {{ Content.E01.DivisionMixed.L3 }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '10%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.DivisionMixed.L4 is not empty) %}
                            {{ Content.E01.DivisionMixed.L4 }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '10%'
                )
//                ->addElementColumn((new Element())
//                    ->setContent('
//                        {% if (Content.E01.DivisionMixed.Migration is not empty) %}
//                            {{ Content.E01.DivisionMixed.Migration }}
//                        {% else %}
//                            &nbsp;
//                        {% endif %}
//                    ')
//                    ->styleBorderRight(), '10%'
//                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.E01.DivisionMixed.TotalCount is not empty) %}
                            {{ Content.E01.DivisionMixed.TotalCount }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleTextBold()
                    ->styleBackgroundColor('lightgrey')
                    , '10%'
                )
            );

        return $sliceList;
    }
}