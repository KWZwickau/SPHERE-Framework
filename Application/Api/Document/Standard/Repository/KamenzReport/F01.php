<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 14:59
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReport;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class F01
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('F01. Inklusiv unterrichtete Schüler mit sonderpädagogischem Förderbedarf im Schuljahr
                    {{ Content.SchoolYear.Current }} </br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; nach 
                    Förderschwerpunkten und Klassenstufen')
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
                    ->setContent('Förderschwerpunkt(e)')
                    ->styleBorderRight()
                    ->stylePaddingTop('34.7px')
                    ->stylePaddingBottom('34.7px'), '20%'
                )
                ->addElementColumn((new Element())
                    ->setContent('Klassenstufe')
                    ->styleBorderRight()
                    ->stylePaddingTop('34.7px')
                    ->stylePaddingBottom('34.7px'), '30%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            ->stylePaddingTop('25.6px')
                            ->stylePaddingBottom('25.6px'), '30%'
                        )
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
                    ), '16.66%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('<b>Darunter</b> von Spalte Schüler'), '100%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Schüler mit<br/>Migrations-<br/>hintergrund')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Schüler mit<br/>gutachterl.<br/>best. Autismus')
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
                    , '33.34%'
                )
            );

        for ($i = 0; $i < 8; $i++) {
            $isBold = false;
            $paddingTop = '63.4px';
            $paddingBottom = '63.3px';
            switch ($i) {
                case 0: $text = 'Lernen'; break;
                case 1: $text = 'Sehen'; break;
                case 2: $text = 'Hören'; break;
                case 3: $text = 'Sprache'; break;
                case 4: $text = 'Körperlich-motorische Entwicklung'; $paddingTop = '46px'; $paddingBottom = '46px'; break;
                case 5: $text = 'Geistige Entwicklung'; break;
                case 6: $text = 'Sozial-emotionale Entwicklung'; $paddingTop = '54.7px'; $paddingBottom = '54.6px';break;
                case 7: $text = 'Insgesamt'; $isBold = true; break;
                default: $text = '';
            }

            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent($text)
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom($paddingBottom), '20%'
            );

            // Klassenstufe
            $lineSectionList = array();
            for ($j = 5; $j <11; $j++) {
                $lineSection = new Section();
                $lineSection
                    ->addElementColumn((new Element())
                        ->setContent($j)
                        ->styleBorderBottom()
                    );
                $lineSectionList[] = $lineSection;
            }
            $lineSectionList[] = (new Section())
                ->addElementColumn((new Element())
//                    ->setContent('Vorb.-kl. u. -gr. f. Migranten')
                    ->setContent('Sonderklassen')
                    ->styleBorderBottom()
                );
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
                    , '30%'
                );

            // Schüler
            self::setColumn($section, $text, 'Student', 'm');
            self::setColumn($section, $text, 'Student', 'w');

            // Migrationshintergrund
            self::setColumn($section, $text, 'HasMigrationBackground', 'm');
            self::setColumn($section, $text, 'HasMigrationBackground', 'w');

            // Autismus
            self::setColumn($section, $text, 'Autism', 'm');
            self::setColumn($section, $text, 'Autism', 'w');

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
     * @param $section
     * @param $text
     * @param $identifier
     * @param $gender
     */
    private static function setColumn(Section $section, $text, $identifier, $gender)
    {

        if ($text == 'Insgesamt') {
            $name = 'TotalCount';
            $isGrey = true;
        } else {
            $name = preg_replace('/[^a-zA-Z]/', '', $text);
            $isGrey = false;
        }

        $lineSectionList = array();
        for ($j = 5; $j < 11; $j++) {
            $lineSection = new Section();
            $lineSection
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.F01.' . $name . '.' . $identifier . '.L' . $j . '.' . $gender . ' is not empty) %}
                                {{ Content.F01.' . $name . '.' . $identifier . '.L' . $j . '.' . $gender . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBackgroundColor($isGrey ? 'lightgrey' : 'white')
                    ->styleBorderBottom()
                );
            $lineSectionList[] = $lineSection;
        }
        $lineSectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
//                ->setContent('
//                    {% if (Content.F01.' . $name . '.' . $identifier . '.IsInPreparationDivisionForMigrants.' . $gender . ' is not empty) %}
//                        {{ Content.F01.' . $name . '.' . $identifier . '.IsInPreparationDivisionForMigrants.' . $gender . ' }}
//                    {% else %}
//                        &nbsp;
//                    {% endif %}
//                ')
                ->styleBackgroundColor($isGrey ? 'lightgrey' : 'white')
                ->styleBorderBottom()
            );
        $lineSectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('
                    {% if (Content.F01.' . $name . '.' . $identifier . '.TotalCount.' . $gender . ' is not empty) %}
                        {{ Content.F01.' . $name . '.' . $identifier . '.TotalCount.' . $gender . ' }}
                    {% else %}
                        &nbsp;
                    {% endif %}
                ')
                ->styleBackgroundColor('lightgrey')
                ->styleTextBold()
            );
        $section
            ->addSliceColumn((new Slice())
                ->styleBorderRight()
                ->addSectionList($lineSectionList)
                , '8.333%'
            );
    }
}