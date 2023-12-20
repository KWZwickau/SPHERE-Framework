<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 11:16
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E12
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E12. Schüler und Profilgruppen in weiteren Profilen im Schuljahr {{ Content.SchoolYear.Current }} nach Klassenstufen')
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderAll()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Merkmal')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop('17.2px')
                    ->stylePaddingBottom('17.1px'), '40%'
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
                            ->setContent('8')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '33.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '33.33%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '33.34%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '16.66%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '16.67%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleAlignCenter()
                            ->styleBorderRight(), '16.67%'
                        )
                    ), '45%'
                )
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleAlignCenter()
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
                    ), '15%'
                )
            );

        for ($i = 0; $i < 5; $i++) {
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E12.S' . $i . '.SubjectName is not empty) %}
                                {{ Content.E12.S' . $i . '.SubjectName }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    , '40%'
                );
            for ($level = 8; $level < 11; $level++) {

                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E12.S' . $i . '.L' . $level . '.m is not empty) %}
                                {{ Content.E12.S' . $i . '.L' . $level . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight()
                        , '7.5%'
                    );
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E12.S' . $i . '.L' . $level . '.w is not empty) %}
                                {{ Content.E12.S' . $i . '.L' . $level . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBorderRight()
                        , '7.5%'
                    );
            }
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E12.S' . $i . '.TotalCount.m is not empty) %}
                                {{ Content.E12.S' . $i . '.TotalCount.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->styleTextBold(), '7.5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                                {% if (Content.E12.S' . $i . '.TotalCount.w is not empty) %}
                                    {{ Content.E12.S' . $i . '.TotalCount.w }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold(), '7.5%'
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