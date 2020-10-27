<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class S02
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS
 */
class S02
{
    /**
     * @param string $name
     *
     * @return array
     */
    public static function getContent($name = 'S02_1')
    {
        switch ($name) {
            case 'S02_1':
                $title = 'S02-1. Schüler im <u>Vollzeitunterricht</u> im Schuljahr {{ Content.SchoolYear.Current }} nach 
                    Geburtsjahren, Ausbildungsstatus und Klassenstufen';
                $columnName = 'Geburtsjahr¹';
                $maxLevel = 3;
                $width[0] = '10%';
                $width[1] = '18%';
                $width[2] = '54%';
                $width[3] = '18%';
                $width['gender'] = '9%';
                $footNote = '1)&nbsp;&nbsp;Jedes Geburtsjahr erscheint pro Ausbildungsstatus nur einmal. Schüler eines Geburtsjahres 
                     bitte zusammenfassen.</br>';
                break;
            case 'S02_1_1':
                $title = 'S02-1.1 Darunter Schüler, deren Herkunftssprache nicht oder nicht ausschließlich Deutsch ist,
                    im <u>Vollzeitunterricht</u> im Schuljahr {{ Content.SchoolYear.Current }} nach
                    </br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Geburtsjahren, 
                    Ausbildungsstatus und Klassenstufen';
                $columnName = 'Geburtsjahr¹';
                $maxLevel = 3;
                $width[0] = '10%';
                $width[1] = '18%';
                $width[2] = '54%';
                $width[3] = '18%';
                $width['gender'] = '9%';
                $footNote = '1)&nbsp;&nbsp;Jedes Geburtsjahr erscheint pro Ausbildungsstatus nur einmal. Schüler eines Geburtsjahres 
                     bitte zusammenfassen.</br>';
                break;
            case 'S03_1':
                $title = 'S03-1. Schüler, deren Herkunftssprache nicht oder nicht ausschließlich Deutsch ist, im 
                    <u>Vollzeitunterricht</u> im Schuljahr {{ Content.SchoolYear.Current }} nach Land der
                    </br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Staatsangehörigkeit, Ausbildungsstatus und Klassenstufen';
                $columnName = 'Land der Staatsangehörigkeit';
                $maxLevel = 3;
                $width[0] = '18%';
                $width[1] = '16%';
                $width[2] = '49.5%';
                $width[3] = '16.5%';
                $width['gender'] = '8.25%';
                $footNote = '';
                break;
            default:
                $title = '';
                $columnName = '';
                $maxLevel = 4;
                $width[0] = '10%';
                $width[1] = '18%';
                $width[2] = '57.6%';
                $width[3] = '14.4%';
                $width['gender'] = '7.2%';
                $footNote = '';
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
                    ->setContent($columnName)
                    ->styleBorderRight()
                    ->stylePaddingTop('25px')
                    ->stylePaddingBottom('26.5px')
                    , $width[0])
                ->addElementColumn((new Element())
                    ->setContent('Ausbildungsstatus' . ($columnName == 'Geburtsjahr¹' ? '²' : '¹'))
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
                        {% if (Content.' . $name . '.R' . $i . '.Name is not empty) %}
                            {{ Content.' . $name . '.R' . $i . '.Name }}
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

        /**
         * TotalCount
         */
        $section = new Section();
        $section
            ->addElementColumn((new Element())
                ->setContent('Insgesamt')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight()
                ->stylePaddingTop('8.5px')
                ->stylePaddingBottom('9.6px')
                , $width[0])
            ->addSliceColumn((new Slice())
                ->addElement((new Element())
                    ->setContent('Auszubildende/Schüler')
                    ->styleBorderRight()
                    ->styleBorderBottom()
                )
                ->addElement((new Element())
                    ->setContent('Umschüler')
                    ->styleBorderRight()
                )
                , $width[1]);

        for ($i = 1; $i <= $maxLevel; $i++) {
            $section->addSliceColumn(self::getTotalSlice($name, 'L' . $i, 'm'), $width['gender']);
            $section->addSliceColumn(self::getTotalSlice($name, 'L' . $i, 'w'), $width['gender']);
        }

        $section
            ->addSliceColumn(self::getTotalSlice($name, 'TotalCount', 'm'), $width['gender'])
            ->addSliceColumn(self::getTotalSlice($name, 'TotalCount', 'w', true), $width['gender']);

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleTextBold()
            ->styleAlignCenter()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection($section);

        $sliceList[] = (new Slice())
            ->addElement((new Element())
                ->setContent(
                    $columnName == 'Geburtsjahr¹'
                        ? $footNote . '2)&nbsp;&nbsp;Bitte signieren: Auszubildende/Schüler; Umschüler (Schüler in 
                        Maßnahmen der beruflichen Umschulung)'
                        : '1)&nbsp;&nbsp;Bitte signieren: Auszubildende/Schüler; Umschüler (Schüler in 
                        Maßnahmen der beruflichen Umschulung)'
                )
                ->styleMarginTop('15px')
            );

        return $sliceList;
    }

    /**
     * @param $name
     * @param $identifier
     * @param $gender
     *
     * @param bool $isLastColumn
     *
     * @return Slice
     */
    private static function getTotalSlice($name, $identifier, $gender, $isLastColumn = false)
    {
        return (new Slice())
            ->addElement((new Element())
                ->setContent('
                        {% if (Content.' . $name . '.TotalCount.Student.' . $identifier . '.' . $gender . ' is not empty) %}
                            {{ Content.' . $name . '.TotalCount.Student.' . $identifier . '.' . $gender . ' }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleBorderRight($isLastColumn ? '0px': '1px')
                ->styleBorderBottom()
            )
            ->addElement((new Element())
                ->setContent('
                        {% if (Content.' . $name . '.TotalCount.ChangeStudent.' . $identifier . '.' . $gender . ' is not empty) %}
                            {{ Content.' . $name . '.TotalCount.ChangeStudent.' . $identifier . '.' . $gender . ' }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                ->styleBorderRight($isLastColumn ? '0px': '1px')
            );
    }
}