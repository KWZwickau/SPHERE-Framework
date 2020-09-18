<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class F01
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS
 */
class F01
{
    /**
     * @param string $name
     *
     * @return Slice[]
     */
    public static function getContent($name = 'F01_1')
    {
        switch ($name) {
            case 'F01_1':
                $title = 'F01-1. Inklusiv unterrichtete Förderschüler im <u>Vollzeitunterricht</u> im 
                    {{ Content.SchoolYear.Current }} nach Förderschwerpunkten und Klassenstufen';
                break;
            case 'F01_2':
                $title = 'F01-2. Inklusiv unterrichtete Förderschüler im <u>Teilzeitunterricht</u> im 
                    {{ Content.SchoolYear.Current }} nach Förderschwerpunkten und Klassenstufen';
                break;
            default: $title = '';
        }

        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent($title)
            );
        $sliceList[] = (new Slice())
            ->styleMarginBottom('10px')
            ->addElement((new Element())
                ->setContent('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Die Schüler sind bei dem 
                    Förderschwerpunkt einzutragen, der primär gefördert wird.'
                )
            );

        $width[0] = '16%';
        $width[1] = '18%';
        $width[2] = '22%';
        $width[3] = '44%';
        $width['gender'] = '11%';

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Förderschwer-</br>punkt(e)')
                    ->styleBorderRight()
                    ->stylePaddingTop('17.5px')
                    ->stylePaddingBottom('17.7px')
                , $width[0])
                ->addElementColumn((new Element())
                    ->setContent('Klassenstufe')
                    ->styleBorderRight()
                    ->stylePaddingTop('25.2px')
                    ->stylePaddingBottom('27px')
                , $width[1])
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('17px')
                            ->stylePaddingBottom('17.2px')
                        , '30%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '50%'
                        )
                    )
                , $width[2])
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Darunter von Spalte Schüler')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler, deren Herkunftssprache nicht oder nicht ausschl. Deutsch ist')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Schüler mit gutachterlich bestätigtem Autismus')
                            ->styleBorderBottom(), '50%'
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
                    )
                , $width[3])
            );

        for ($i = 0; $i < 8; $i++) {
            $isBold = false;
            $hasBorderTop = false;
            if ($name == 'F01_1') {
                $maxLevel = 4;
                $paddingTop = '36.2px';
                $paddingBottom = '36.3px';
            } else {
                $maxLevel = 5;
                $paddingTop = '45px';
                $paddingBottom = '45.5px';
            }

            switch ($i) {
                // müssen wie in der Datenbank heißen, sonst werden die Inhalte nicht korrekt befüllt
                case 0: $text = 'Lernen'; break;
                case 1: $text = 'Sehen'; break;
                case 2: $text = 'Hören'; break;
                case 3: $text = 'Sprache'; break;
                case 4: $text = 'Körperlich-motorische Entwicklung';
                    if ($name == 'F01_1') {
                        $paddingTop = '27.8px';
                        $paddingBottom = '27.8px';
                    } else {
                        $paddingTop = '36px';
                        $paddingBottom = '37.2px';
                    }
                    break;
                case 5: $text = 'Geistige Entwicklung';
                    $hasBorderTop = $name == 'F01_2';
                    break;
                case 6: $text = 'Sozial-emotionale Entwicklung';
                    if ($name == 'F01_1') {
                        $hasBorderTop = true;
                        $paddingTop = '27.8px';
                        $paddingBottom = '27.8px';
                    } else {
                        $paddingTop = '36px';
                        $paddingBottom = '37.2px';
                    };
                    break;
                case 7: $text = 'Insgesamt'; $isBold = true; break;
                default: $text = '';
            }

            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent($text)
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->styleBorderTop($hasBorderTop ? '1px' : '0px')
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom($paddingBottom)
                , $width[0]);

            // Klassenstufe
            $lineSectionList = array();
            for ($j = 1; $j <= $maxLevel; $j++) {
                $lineSection = new Section();
                $lineSection
                    ->addElementColumn((new Element())
                        ->setContent($j)
                        ->styleBorderBottom()
                        ->styleBorderTop($hasBorderTop && $j == 1 ? '1px' : '0px')
                    );
                $lineSectionList[] = $lineSection;
            }
            $lineSectionList[] = (new Section())
                ->addElementColumn((new Element())
                    ->setContent('Zusammen')
                    ->styleTextBold()
                );
            $section
                ->addSliceColumn((new Slice())
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->addSectionList($lineSectionList)
                , $width[1]);

            // Schüler
            self::setColumn($section, $name, $text, 'Student', 'm', $width['gender'], $hasBorderTop);
            self::setColumn($section, $name, $text, 'Student', 'w', $width['gender'], $hasBorderTop);

            // Migrationshintergrund
            self::setColumn($section, $name, $text, 'HasMigrationBackground', 'm', $width['gender'], $hasBorderTop);
            self::setColumn($section, $name, $text, 'HasMigrationBackground', 'w', $width['gender'], $hasBorderTop);

            // Autismus
            self::setColumn($section, $name, $text, 'Autism', 'm', $width['gender'], $hasBorderTop);
            self::setColumn($section, $name, $text, 'Autism', 'w', $width['gender'], $hasBorderTop, true);

            $sliceList[] = (new Slice())
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->styleTextBold($isBold ? 'bold' : 'normal')
                ->addSection($section);
        }

        return $sliceList;
    }

    /**
     * @param Section $section
     * @param $name
     * @param $text
     * @param $identifier
     * @param $gender
     * @param string $width
     * @param $hasBorderTop
     * @param bool $isLastColumn
     */
    private static function setColumn(Section $section, $name, $text, $identifier, $gender, $width, $hasBorderTop, $isLastColumn = false)
    {
        $maxLevel = ($name == 'F01_1' ? 4 : 5);

        if ($text == 'Insgesamt') {
            $text = 'TotalCount';
        } else {
            $text = preg_replace('/[^a-zA-Z]/', '', $text);
        }

        $lineSectionList = array();
        for ($j = 1; $j <= $maxLevel; $j++) {
            $lineSection = new Section();
            $lineSection
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.' . $name . '.' . $text . '.' . $identifier . '.L' . $j . '.' . $gender . ' is not empty) %}
                                {{ Content.' . $name . '.' . $text . '.' . $identifier . '.L' . $j . '.' . $gender . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderBottom()
                );
            $lineSectionList[] = $lineSection;
        }
        $lineSectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.' . $name . '.' . $text . '.' . $identifier . '.TotalCount.' . $gender . ' is not empty) %}
                        {{ Content.' . $name . '.' . $text . '.' . $identifier . '.TotalCount.' . $gender . ' }}
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
                ->styleTextBold()
                ->styleBackgroundColor('lightgrey')
            );
        $section
            ->addSliceColumn((new Slice())
                ->styleBorderRight($isLastColumn ? '0px': '1px')
                ->styleBorderTop($hasBorderTop ? '1px' : '0px')
                ->addSectionList($lineSectionList)
            , $width);
    }
}