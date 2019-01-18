<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.06.2017
 * Time: 09:44
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportGym;

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
                ->setContent('E04. Schüler im Fremdsprachenunterricht im Schuljahr {{Content.SchoolYear.Current}} 
                    nach Fremdsprachen und Klassenstufen')
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
                    ->styleBorderRight()
                    ->stylePaddingTop('8.6px')
                    ->stylePaddingBottom('8.5px')
                    , '28%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassen- bzw. Jahrgangsstufe')
                            ->styleBorderRight(), '100%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('5')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('6')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('7')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('11')
                            ->styleBorderRight(), '12.5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('12')
                            ->styleBorderRight(), '12.5%'
                        )
                    )
                )
                ->addElementColumn((new Element())
                    ->setContent('Insges.')
                    ->styleTextBold()
                    ->stylePaddingTop('8.5px')
                    ->stylePaddingBottom('8.5px'), '8%'
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
                    ->styleBorderRight(), '28%'
                );
            for ($level = 5; $level < 13; $level++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E04.S' . $i . '.L' . $level . ' is not empty) %}
                                {{ Content.E04.S' . $i . '.L' . $level . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBackgroundColor($level < 11 ? 'white' : 'lightgrey')
                        ->styleBorderRight(), '8%'
                    );
            }
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E04.S' . $i . '.TotalCount is not empty) %}
                                {{ Content.E04.S' . $i . '.TotalCount }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold(), '8%'
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