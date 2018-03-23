<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 11:28
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class E15
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E15. Schüler in Sprachenfolgen im Schuljahr {{ Content.Schoolyear.Current }} nach Klassenstufen')
            );


        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderAll()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fremdsprache')
                    ->styleBorderBottom()
                    ->styleBorderRight(), '70%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Schüler in der Klassenstufe'), '30%'
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('1.')
                    ->styleBorderRight(), '17.5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('2.')
                    ->styleBorderRight(), '17.5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('3.')
                    ->styleBorderRight(), '17.5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('4.')
                    ->styleBorderRight(), '17.5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('5')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('6')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('7')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('8')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('9')
                    ->styleBorderRight(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('10'), '5%'
                )
            );

        for ($i = 0; $i < 15; $i++) {
            $section = new Section();

            for ($j = 1; $j < 5; $j++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E15.S' . $i . '.N' . $j. ' is not empty) %}
                                {{ Content.E15.S' . $i . '.N' . $j. ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight(), '17.5%'
                    );
            }

            for ($j = 5; $j < 11; $j++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E15.S' . $i . '.L' . $j. ' is not empty) %}
                                {{ Content.E15.S' . $i . '.L' . $j. ' }}
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
                ->styleBorderRight(), '70%'
            );
        for ($j = 5; $j < 11; $j++) {
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E15.TotalCount.L' . $j. ' is not empty) %}
                                {{ Content.E15.TotalCount.L' . $j. ' }}
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