<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class S04
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS
 */
class S04
{
    /**
     * @param string $name
     *
     * @return array
     */
    public static function getContent($name = 'S04_1')
    {
        switch ($name) {
            case 'S04_1':
                $title = 'S04-1. Schüler mit fremdsprachlichem Unterricht (an dieser Schule neu begonnene bzw. fortgeführte 
                    Fremdsprachen) im <u>Vollzeitunterricht</u> im Schuljahr</br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;{{ Content.SchoolYear.Current }} nach 
                    Fremdsprachen, Ausbildungsstatus und Klassenstufen';
                $maxLevel = 4;
                $width[0] = '22%';
                $width[1] = '18%';
                $width[2] = '48%';
                $width[3] = '12%';
                $width['gender'] = '6%';
                break;
            case 'S04_2':
                $title = 'S04-2. Schüler mit fremdsprachlichem Unterricht (an dieser Schule neu begonnene bzw. fortgeführte 
                    Fremdsprachen) im <u>Teilzeitunterricht</u> im Schuljahr</br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;{{ Content.SchoolYear.Current }} nach 
                    Fremdsprachen, Ausbildungsstatus und Klassenstufen';
                $maxLevel = 5;
                $width[0] = '22%';
                $width[1] = '18%';
                $width[2] = '50%';
                $width[3] = '10%';
                $width['gender'] = '5%';
                break;
            default:
                $title = '';
                $maxLevel = 5;
                $width[0] = '17%';
                $width[1] = '17%';
                $width[2] = '55%';
                $width[3] = '11%';
                $width['gender'] = '5.5%';
        }

        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginBottom('10px')
            ->addElement((new Element())
                ->setContent($title)
            );

        $padding = '3.8px';

        $sectionLevel = new Section();
        for ($i = 1; $i <= $maxLevel; $i++)
        {
            $sectionLevel
                ->addElementColumn((new Element())
                    ->setContent($i)
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    ->stylePaddingTop($padding)
                    ->stylePaddingBottom($padding)
                    , (floatval(100) / floatval($maxLevel)) . '%' );
        }

        $tempWidth = (floatval(100) / floatval(2 * $maxLevel)) . '%';
        $sectionGender = new Section();
        for ($i = 1; $i <= $maxLevel; $i++) {
            $sectionGender
                ->addElementColumn((new Element())
                    ->setContent('m')
                    ->styleBorderRight()
                    , $tempWidth)
                ->addElementColumn((new Element())
                    ->setContent('w')
                    ->styleBorderRight()
                    , $tempWidth);
        }

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fremdsprache¹')
                    ->styleBorderRight()
                    ->stylePaddingTop('25px')
                    ->stylePaddingBottom('26.5px')
                    , $width[0])
                ->addElementColumn((new Element())
                    ->setContent('Ausbildungsstatus²')
                    ->styleBorderRight()
                    ->stylePaddingTop('25px')
                    ->stylePaddingBottom('26.5px')
                    , $width[1])
                ->addSliceColumn((new Slice())
                    ->addElement((new Element())
                        ->setContent('Schüler in Klassenstufe')
                        ->styleBorderRight()
                        ->styleBorderBottom()
                        ->stylePaddingTop($padding)
                        ->stylePaddingBottom($padding)
                    )
                    ->addSection($sectionLevel)
                    ->addSection($sectionGender)
                    , $width[2])
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addElement((new Element())
                        ->setContent('Insgesamt')
                        ->styleBorderBottom()
                        ->stylePaddingTop('16px')
                        ->stylePaddingBottom('17.3px')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight()
                            , '50%')
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            , '50%')
                    )
                    , $width[3])
            );

        for ($i = 0; $i < 6; $i++) {
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.' . $name . '.R' . $i . '.Language is not empty) %}
                            {{ Content.' . $name . '.R' . $i . '.Language }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    , $width[0]);
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if (Content.' . $name . '.R' . $i . '.Status is not empty) %}
                            {{ Content.' . $name . '.R' . $i . '.Status }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    , $width[1]);

            for ($j = 1; $j <= $maxLevel; $j++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.L' . $j . '.m is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.L' . $j . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight()
                        ->styleAlignCenter()
                        , $width['gender'])
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.' . $name . '.R' . $i . '.L' . $j . '.w is not empty) %}
                                {{ Content.' . $name . '.R' . $i . '.L' . $j . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight()
                        ->styleAlignCenter()
                        , $width['gender']);
            }

            $section
                ->addElementColumn((new Element())
                    ->setContent('
                                {% if (Content.' . $name . '.R' . $i . '.TotalCount.m is not empty) %}
                                    {{ Content.' . $name . '.R' . $i . '.TotalCount.m }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->styleAlignCenter()
                    ->styleTextBold()
                    , $width['gender'])
                ->addElementColumn((new Element())
                    ->setContent('
                                {% if (Content.' . $name . '.R' . $i . '.TotalCount.w is not empty) %}
                                    {{ Content.' . $name . '.R' . $i . '.TotalCount.w }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                    ->styleBackgroundColor('lightgrey')
                    ->styleAlignCenter()
                    ->styleTextBold()
                    , $width['gender']);

            $sliceList[] = (new Slice())
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->addSection($section);
        }

        $sliceList[] = (new Slice())
            ->addElement((new Element())
                ->setContent(
                    '1)&nbsp;&nbsp;Jeder Schüler wird entsprechend der Zahl der belegten Fremdsprachen gezählt, also Mehrfachzählung möglich.</br>
                    2)&nbsp;&nbsp;Bitte signieren: Auszubildende/Schüler; Umschüler (Schüler in Maßnahmen der beruflichen Umschulung)'
                )
                ->styleMarginTop('15px')
            );

        return $sliceList;
    }
}