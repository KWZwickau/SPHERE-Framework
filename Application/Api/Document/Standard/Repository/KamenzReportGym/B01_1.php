<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 08:52
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class B01_1
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('B01.1 Darunter <u>Absolventen/Abgänger mit Migrationshintergund</u> aus dem Schuljahr {{ Content.Schoolyear.Past }} nach Abschlussarten und Klassen- bzw. Jahrgangsstufen')
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
                    ->setContent('Abschlussart')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop('17.7px')
                    ->stylePaddingBottom('17.6px'), '30%'
                )
                ->addSliceColumn((new Slice())
                    ->styleBorderRight()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassen- bzw. Jahrgangsstufe')
                            ->styleAlignCenter()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('7')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('11')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('12')
                            ->styleAlignCenter()
                            ->styleBorderBottom(), '10%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter(), '5%'
                        )
                    ), '60%'
                )
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter(), '50%'
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
                    ->setContent('Abgangszeugnis <b>ohne</b> Vermerk')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '30%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.Leave.L7.m is not empty) %}
                            {{ Content.B01_1.Leave.L7.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.Leave.L7.w is not empty) %}
                            {{ Content.B01_1.Leave.L7.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.Leave.L8.m is not empty) %}
                            {{ Content.B01_1.Leave.L8.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.Leave.L8.w is not empty) %}
                            {{ Content.B01_1.Leave.L8.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.Leave.L9.m is not empty) %}
                            {{ Content.B01_1.Leave.L9.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.Leave.L9.w is not empty) %}
                            {{ Content.B01_1.Leave.L9.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.Leave.L10.m is not empty) %}
                            {{ Content.B01_1.Leave.L10.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.Leave.L10.w is not empty) %}
                            {{ Content.B01_1.Leave.L10.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.Leave.L11.m is not empty) %}
                            {{ Content.B01_1.Leave.L11.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.Leave.L11.w is not empty) %}
                            {{ Content.B01_1.Leave.L11.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.Leave.L12.m is not empty) %}
                            {{ Content.B01_1.Leave.L12.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.Leave.L12.w is not empty) %}
                            {{ Content.B01_1.Leave.L12.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.Leave.TotalCount.m is not empty) %}
                            {{ Content.B01_1.Leave.TotalCount.m }}
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
                        {% if (Content.B01_1.Leave.TotalCount.w is not empty) %}
                            {{ Content.B01_1.Leave.TotalCount.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey'), '5%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Hauptschulabschluss<br/>(Abgangszeugnis mit Vermerk)')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '30%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveHS.L7.m is not empty) %}
                            {{ Content.B01_1.LeaveHS.L7.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveHS.L7.w is not empty) %}
                            {{ Content.B01_1.LeaveHS.L7.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveHS.L8.m is not empty) %}
                            {{ Content.B01_1.LeaveHS.L8.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveHS.L8.w is not empty) %}
                            {{ Content.B01_1.LeaveHS.L8.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveHS.L9.m is not empty) %}
                            {{ Content.B01_1.LeaveHS.L9.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveHS.L9.w is not empty) %}
                            {{ Content.B01_1.LeaveHS.L9.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveHS.L10.m is not empty) %}
                            {{ Content.B01_1.LeaveHS.L10.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveHS.L10.w is not empty) %}
                            {{ Content.B01_1.LeaveHS.L10.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveHS.L11.m is not empty) %}
                            {{ Content.B01_1.LeaveHS.L11.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveHS.L11.w is not empty) %}
                            {{ Content.B01_1.LeaveHS.L11.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveHS.L12.m is not empty) %}
                            {{ Content.B01_1.LeaveHS.L12.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveHS.L12.w is not empty) %}
                            {{ Content.B01_1.LeaveHS.L12.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveHS.TotalCount.m is not empty) %}
                            {{ Content.B01_1.LeaveHS.TotalCount.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveHS.TotalCount.w is not empty) %}
                            {{ Content.B01_1.LeaveHS.TotalCount.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey'), '5%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Realschulabschluss<br/>(Abgangszeugnis mit Vermerk)')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '30%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveRS.L7.m is not empty) %}
                            {{ Content.B01_1.LeaveRS.L7.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveRS.L7.w is not empty) %}
                            {{ Content.B01_1.LeaveRS.L7.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveRS.L8.m is not empty) %}
                            {{ Content.B01_1.LeaveRS.L8.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveRS.L8.w is not empty) %}
                            {{ Content.B01_1.LeaveRS.L8.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveRS.L9.m is not empty) %}
                            {{ Content.B01_1.LeaveRS.L9.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveRS.L9.w is not empty) %}
                            {{ Content.B01_1.LeaveRS.L9.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveRS.L10.m is not empty) %}
                            {{ Content.B01_1.LeaveRS.L10.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveRS.L10.w is not empty) %}
                            {{ Content.B01_1.LeaveRS.L10.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveRS.L11.m is not empty) %}
                            {{ Content.B01_1.LeaveRS.L11.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveRS.L11.w is not empty) %}
                            {{ Content.B01_1.LeaveRS.L11.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveRS.L12.m is not empty) %}
                            {{ Content.B01_1.LeaveRS.L12.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveRS.L12.w is not empty) %}
                            {{ Content.B01_1.LeaveRS.L12.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveRS.TotalCount.m is not empty) %}
                            {{ Content.B01_1.LeaveRS.TotalCount.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.LeaveRS.TotalCount.w is not empty) %}
                            {{ Content.B01_1.LeaveRS.TotalCount.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey'), '5%'
                )
            );

        $sliceList[] = (new Slice())
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Allgemeine Hochschulreife')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '30%'
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
                    ->setContent('
                        {% if (Content.B01_1.GymAbitur.L12.m is not empty) %}
                            {{ Content.B01_1.GymAbitur.L12.m }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.GymAbitur.L12.w is not empty) %}
                            {{ Content.B01_1.GymAbitur.L12.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.B01_1.GymAbitur.TotalCount.m is not empty) %}
                            {{ Content.B01_1.GymAbitur.TotalCount.m }}
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
                        {% if (Content.B01_1.GymAbitur.TotalCount.w is not empty) %}
                            {{ Content.B01_1.GymAbitur.TotalCount.w }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
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
                    ->styleAlignCenter()
                    ->styleBorderRight(), '30%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;'), '5%'
                )
            );

        return $sliceList;
    }
}