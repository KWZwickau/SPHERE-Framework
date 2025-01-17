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

class E04
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E04. Schüler im Fremdsprachenunterricht im Schuljahr {{ Content.SchoolYear.Current }} nach Fremdsprachen und Klassenstufen')
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
                    ->setContent('Fremdsprache')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop('17.1px')
                    ->stylePaddingBottom('17.1px'), '50%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '100%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('1')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.6px'), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('2')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.6px'), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('3')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.6px'), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('4')
                            ->styleAlignCenter()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.6px')
                            ->styleBorderRight(), '25%'
                        )
                    ), '40%'
                )
//                ->addElementColumn((new Element())
//                    ->setContent('Vorb.kl. u.<br/>-gr. f.<br/>Migranten')
//                    ->styleBorderRight(), '10%'
//                )
                ->addElementColumn((new Element())
                    ->setContent('Insgesamt')
                    ->styleAlignCenter()
                    ->styleTextBold()
                    ->stylePaddingTop('17.1px')
                    ->stylePaddingBottom('17.1px'), '10%'
                )
            );

        for ($i = 0; $i < 8; $i++) {
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E04.S' . $i . '.SubjectName is not empty) %}
                                {{ Content.E04.S' . $i . '.SubjectName }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->styleBackgroundColor('lightgrey'), '50%'
                );
            for ($level = 1; $level < 5; $level++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E04.S' . $i . '.L' . $level . ' is not empty) %}
                                {{ Content.E04.S' . $i . '.L' . $level . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight(), '10%'
                    );
            }
//            $section
//                ->addElementColumn((new Element())
//                    ->setContent('
//                            {% if (Content.E04.S' . $i . '.Migration is not empty) %}
//                                {{ Content.E04.S' . $i . '.Migration }}
//                            {% else %}
//                                &nbsp;
//                            {% endif %}
//                        ')
//                    ->styleBorderRight(), '10%'
//                );
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E04.S' . $i . '.TotalCount is not empty) %}
                                {{ Content.E04.S' . $i . '.TotalCount }}
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

        return $sliceList;
    }
}