<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Common\Frontend\Layout\Repository\Container;

/**
 * Class F01
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS
 */
class F01
{
    /**
     * @param string $name
     * @param string $schoolTypeName
     *
     * @return Slice[]
     */
    public static function getContent($name, $schoolTypeName)
    {
        switch ($name) {
            case 'F01_1':
                $title = 'F01-1. Inklusiv unterrichtete Schüler mit sonderpädagogischem Förderbedarf <u>im Vollzeitunterricht</u>
                    im Schuljahr {{ Content.SchoolYear.Current }} nach Förderschwerpunkten und <br />' . Common::getBlankSpace(11)
                    . 'Klassenstufen';
                break;
            case 'F01_2':
                $title = 'F01-2. Inklusiv unterrichtete Schüler mit sonderpädagogischem Förderbedarf <u>im Teilzeitunterricht</u>
                    im Schuljahr {{ Content.SchoolYear.Current }} nach Förderschwerpunkten und <br />' . Common::getBlankSpace(11)
                    . 'Klassenstufen';
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

        $width[0] = '15%';
        $width[1] = '15%';
        $width[2] = '50%';
        $width[3] = '20%';
        $width['gender'] = '10%';

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent(new Container('Förderschwer-').'punkt(e)')
                    ->styleBorderRight()
                    ->stylePaddingTop('43px')
                    ->stylePaddingBottom('44.4px')
                , $width[0])
                ->addElementColumn((new Element())
                    ->setContent('Klassenstufe')
                    ->styleBorderRight()
                    ->stylePaddingTop('50.3px')
                    ->stylePaddingBottom('54.3px')
                , $width[1])
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('43px')
                            ->stylePaddingBottom('43.4px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('männlich')
                            ->styleBorderRight(), '20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('weiblich')
                            ->styleBorderRight(), '20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('divers')
                            ->styleBorderRight(), '20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('ohne Angabe¹')
                            ->styleBorderRight(), '20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('insgesamt')
                            ->styleTextBold()
                            ->styleBorderRight(), '20%'
                        )
                    )
                , $width[2])
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Darunter')
                            ->styleBorderBottom()
                            ->styleTextBold()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler, deren<br /> Herkunftsspr.<br /> nicht oder nicht<br /> ausschließlich<br /> Deutsch ist')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Schüler mit<br /> gutachterlich<br /> bestätigtem<br /> Autismus')
                            ->stylePaddingTop('8.5px')
                            ->stylePaddingBottom('8.6px')
                            ->styleBorderBottom(), '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('insgesamt')
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('insgesamt'), '50%'
                        )
                    )
                , $width[3])
            );

        for ($i = 0; $i < 8; $i++) {
            $isBold = false;
            $hasBorderTop = false;

            if ($schoolTypeName == 'Berufsfachschule') {
                if ($name == 'F01_1') {
                    $maxLevel = 4;
                    $paddingTop = '36.2px';
                    $paddingBottom = '36.3px';
                } else {
                    $maxLevel = 5;
                    $paddingTop = '45px';
                    $paddingBottom = '45.5px';
                }
            } else {
                // $schoolTypeName == 'Fachschule'
                if ($name == 'F01_1') {
                    $maxLevel = 3;
                    $paddingTop = '27.2px';
                    $paddingBottom = '27.3px';
                } else {
                    $maxLevel = 4;
                    $paddingTop = '36.2px';
                    $paddingBottom = '36.3px';
                }
            }

            switch ($i) {
                // müssen wie in der Datenbank heißen, sonst werden die Inhalte nicht korrekt befüllt
                case 0: $text = 'Lernen'; break;
                case 1: $text = 'Sehen'; break;
                case 2: $text = 'Hören'; break;
                case 3: $text = 'Sprache'; break;
                case 4: $text = 'Körperlich-motorische Entwicklung';
//                    $hasBorderTop = $name == 'F01_2';
                    if ($maxLevel == 4) {
                        $paddingTop = '27.8px';
                        $paddingBottom = '27.8px';
                    } elseif ($maxLevel == 5) {
                        $paddingTop = '36px';
                        $paddingBottom = '37.2px';
                    } else {
                        // $maxLevel == 3
                        $paddingTop = '18.5px';
                        $paddingBottom = '18.8px';
                    }
                    break;
                case 5: $text = 'Geistige Entwicklung';
//                    $hasBorderTop = $name == 'F01_1';
                    break;
                case 6: $text = 'Emotionale-soziale Entwicklung';
                    if ($maxLevel == 4) {
//                        $hasBorderTop = true;
                        $paddingTop = '27.8px';
                        $paddingBottom = '27.8px';
                    } elseif ($maxLevel == 5) {
                        $paddingTop = '36px';
                        $paddingBottom = '37.2px';
                    } else {
                        // $maxLevel == 3
                        $paddingTop = '18.5px';
                        $paddingBottom = '18.8px';
                    }
                    break;
                case 7: $text = 'Insgesamt';
                    $isBold = true;
//                    $hasBorderTop = $maxLevel == 3;
                    break;
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
                if ($schoolTypeName == 'Fachschule' && $name == 'F01_2') {
                    $textLevel = ($j < 3 ? '1' : '2') . ' / ' . $j . ' . AJ';
                } else {
                    $textLevel = $j;
                }

                $lineSection = new Section();
                $lineSection
                    ->addElementColumn((new Element())
                        ->setContent($textLevel)
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
            self::setColumn($section, $name, $text, 'Student', 'm', $width['gender'], $hasBorderTop, $maxLevel);
            self::setColumn($section, $name, $text, 'Student', 'w', $width['gender'], $hasBorderTop, $maxLevel);
            self::setColumn($section, $name, $text, 'Student', 'd', $width['gender'], $hasBorderTop, $maxLevel);
            self::setColumn($section, $name, $text, 'Student', 'o', $width['gender'], $hasBorderTop, $maxLevel);
            self::setColumn($section, $name, $text, 'Student', 'TotalCount', $width['gender'], $hasBorderTop, $maxLevel, true);

            // Migrationshintergrund
            self::setColumn($section, $name, $text, 'HasMigrationBackground', 'TotalCount', $width['gender'], $hasBorderTop, $maxLevel);

            // Autismus
            self::setColumn($section, $name, $text, 'Autism', 'TotalCount', $width['gender'], $hasBorderTop, $maxLevel, false, true);

            $sliceList[] = (new Slice())
                ->styleAlignCenter()
                ->styleBorderBottom()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->styleTextBold($isBold ? 'bold' : 'normal')
                ->addSection($section);
        }

        $array[] = 'Laut Eintrag im Geburtenregister';
        $sliceList[] = Common::setFootnotes($array);

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
     * @param $maxLevel
     * @param bool $hasGrayBackground
     * @param bool $isLastColumn
     */
    private static function setColumn(Section $section, $name, $text, $identifier, $gender, $width, $hasBorderTop, $maxLevel,
        $hasGrayBackground = false, $isLastColumn = false
    ) {
        if ($text == 'Insgesamt') {
            $text = 'TotalCount';
            $hasGrayBackground = true;
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
                    ->styleBackgroundColor($hasGrayBackground ? 'lightgrey' : 'white')
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