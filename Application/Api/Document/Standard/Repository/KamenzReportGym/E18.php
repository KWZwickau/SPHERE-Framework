<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 15:28
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E18
{
    public static function getContent()
    {
        $sliceList = array();


        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E18. Schüler in Leistungskursen im Schuljahr {{ Content.Schoolyear.Current }} nach Jahrgangsstufen')
            );

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderAll()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fächerkombination')
                    ->styleBorderRight()
                    ->stylePaddingTop('13.2')
                    ->stylePaddingBottom('13.1'), '80%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('S. in der Jgs.-stufe')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('11')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->styleBorderBottom()
                            ->setContent('12'), '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w'), '25%'
                        )
                    ), '20%'
                )
            );

        for ($i = 0; $i < 30; $i++) {
            $section = new Section();

            for ($j = 0; $j < 4; $j++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E18.S' . $i . '.N' . $j. ' is not empty) %}
                                {{ Content.E18.S' . $i . '.N' . $j. ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), '20%'
                    );
            }

            for ($j = 11; $j < 13; $j++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E18.S' . $i . '.L' . $j. '.m is not empty) %}
                                {{ Content.E18.S' . $i . '.L' . $j. '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), '5%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E18.S' . $i . '.L' . $j. '.w is not empty) %}
                                {{ Content.E18.S' . $i . '.L' . $j. '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), '5%'
                    );
            }

            $sliceList[] = (new Slice())
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->addSection($section);
        }

        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Insgesamt')
                ->styleBorderRight(), '80%'
            );
        for ($j = 11; $j < 13; $j++) {
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E18.TotalCount.L' . $j. '.m is not empty) %}
                                {{ Content.E18.TotalCount.L' . $j. '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E18.TotalCount.L' . $j. '.w is not empty) %}
                                {{ Content.E18.TotalCount.L' . $j. '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), '5%'
                );
        }

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