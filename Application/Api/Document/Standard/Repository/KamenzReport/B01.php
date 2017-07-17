<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 13:10
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReport;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class B01
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('B01. Absolventen/Abgänger aus dem Schuljahr {{ Content.SchoolYear.Past }} nach Abschlussarten und Klassenstufen')
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
                    ->styleBorderRight()
                    ->stylePaddingTop('17.2px')
                    ->stylePaddingBottom('18px'), '30%'
                )
                ->addSliceColumn((new Slice())
                    ->addElement((new Element())
                        ->setContent('Klassenstufe')
                        ->styleBorderRight()
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('7')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '25%'
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
                    ), '56%'
                )
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addElement((new Element())
                        ->setContent('Insgesamt')
                        ->styleBorderBottom()
                        ->stylePaddingTop('8.6px')
                        ->stylePaddingBottom('8.5px')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w'), '50%'
                        )
                    ), '14%'
                )
            );

        for ($i = 0; $i < 4; $i++) {
            switch ($i) {
                case 0: $text = 'Abgangszeugnis'; $identifier = 'Leave'; break;
                case 1: $text = 'Hauptschulabschluss'; $identifier = 'MsAbsHs'; break;
                case 2: $text = 'Qual. Hauptschulabschluss'; $identifier = 'MsAbsHsQ'; break;
                case 3: $text = 'Realschulabschluss'; $identifier = 'MsAbsRs'; break;
                default: $text = ''; $identifier = 'Default';
            }
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent($text)
                    ->stylePaddingLeft('5px')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight(), '30%'
                );
            for ($level = 7; $level <= 10; $level++) {
                $isGrey = false;
                if ($identifier == 'Leave') {
                    if ($level == 10) {
                        $isGrey = true;
                    }
                } elseif ($identifier == 'MsAbsRs') {
                    if ($level != 10) {
                        $isGrey = true;
                    }
                } else {
                    if ($level < 9) {
                        $isGrey = true;
                    }
                }
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.B01.' . $identifier . '.L' . $level . '.m is not empty) %}
                                {{ Content.B01.' . $identifier . '.L' . $level . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBackgroundColor($isGrey ? 'lightgrey' : 'white')
                        ->styleBorderRight(), '7%'
                    );
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.B01.' . $identifier . '.L' . $level . '.w is not empty) %}
                                {{ Content.B01.' . $identifier . '.L' . $level . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBackgroundColor($isGrey ? 'lightgrey' : 'white')
                        ->styleBorderRight(), '7%'
                    );
            }
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.B01.' . $identifier . '.TotalCount.m is not empty) %}
                                {{ Content.B01.' . $identifier . '.TotalCount.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->styleAlignCenter()
                    ->styleTextBold(), '7%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                                {% if (Content.B01.' . $identifier . '.TotalCount.w is not empty) %}
                                    {{ Content.B01.' . $identifier . '.TotalCount.w }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                    ->styleBackgroundColor('lightgrey')
                    ->styleAlignCenter()
                    ->styleTextBold(), '7%'
                );

            $sliceList[] = (new Slice())
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
                ->styleBorderRight(), '30%'
            );
        for ($level = 7; $level <= 10; $level++) {
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.B01.TotalCount.L' . $level . '.m is not empty) %}
                                {{ Content.B01.TotalCount.L' . $level . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), '7%'
                );
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.B01.TotalCount.L' . $level . '.w is not empty) %}
                                {{ Content.B01.TotalCount.L' . $level . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), '7%'
                );
        }
        $section
            ->addElementColumn((new Element())
                ->setContent('
                            &nbsp;
                        ')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight()
                ->styleTextBold(), '7%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                             &nbsp;   
                            ')
                ->styleBackgroundColor('lightgrey')
                ->styleTextBold(), '7%'
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