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

/**
 * Class F01
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReport
 */
class F01
{
    /**
     * End max 7
     * @param int $Start
     * @param int $End
     *
     * @return Slice[]
     */
    public static function getContent($Start = 0, $End = 4)
    {
        $sliceList = array();
        //display Header
        if($Start == 0){
            $sliceList[] = (new Slice())
                ->styleTextBold()
                ->styleMarginTop('20px')
                ->styleMarginBottom('5px')
                ->addElement((new Element())
                    ->setContent('F01. Inklusiv unterrichtete Schüler mit sonderpädagogischem Förderbedarf im Schuljahr
                {{ Content.SchoolYear.Current }} nach <br /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Förderschwerpunkten und Klassenstufen')
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
                        ->styleAlignCenter()
                        ->styleBorderRight()
                        ->stylePaddingTop('43.35px')
                        ->stylePaddingBottom('43.35px'), '20%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Klassenstufe')
                        ->styleAlignCenter()
                        ->styleBorderRight()
                        ->stylePaddingTop('43.35px')
                        ->stylePaddingBottom('43.35px'), '15%'
                    )
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Schüler')
                                ->styleAlignCenter()
                                ->styleBorderBottom()
                                ->styleBorderRight()
                                ->stylePaddingTop('34.25px')
                                ->stylePaddingBottom('34.25px'), '30%'
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
                                ->styleAlignCenter()
                                ->styleBorderRight(), '50%'
                            )
                        ), '21.66%'
                    )
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('<b>Darunter</b> von Spalte Schüler')
                                ->styleAlignCenter(), '100%'
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Schüler, deren<br/>Herkunftssprache nicht<br/>oder nicht ausschl.<br/>Deutsch ist')
                                ->styleAlignCenter()
                                ->styleBorderBottom()
                                ->styleBorderRight(), '50%'
                            )
                            ->addElementColumn((new Element())
                                ->setContent('Schüler mit<br/>gutachterl.<br/>best. Autismus<br/>&nbsp;')
                                ->styleAlignCenter()
                                ->styleBorderBottom(), '50%'
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('m')
                                ->styleAlignCenter()
                                ->styleBorderRight(), '25%'
                            )
                            ->addElementColumn((new Element())
                                ->setContent('w')
                                ->styleAlignCenter()
                                ->styleBorderRight(), '25%'
                            )
                            ->addElementColumn((new Element())
                                ->setContent('m')
                                ->styleAlignCenter()
                                ->styleBorderRight(), '25%'
                            )
                            ->addElementColumn((new Element())
                                ->setContent('w')
                                ->styleAlignCenter(), '25%'
                            )
                        )
                        , '43.34%'
                    )
                );
        }

        for ($i = $Start; $i <= $End; $i++) {
            $isBold = false;
            $paddingTop = '63.4px';
            $paddingBottom = '63.3px';
            switch ($i) {
                case 0: $text = 'Lernen'; break;
                case 1: $text = 'Sehen'; break;
                case 2: $text = 'Hören'; break;
                case 3: $text = 'Sprache'; break;
//                case 4: $text = 'Körperlich-motorische Entwicklung'; $paddingTop = '46px'; $paddingBottom = '46px'; break;
                case 4: $text = 'Körperlich-motorische Entwicklung'; $paddingTop = '54.7px'; $paddingBottom = '54.6px'; break;
                case 5: $text = 'Geistige Entwicklung'; break;
                case 6: $text = 'Emotionale-soziale Entwicklung'; $paddingTop = '54.7px'; $paddingBottom = '54.6px';break;
                case 7: $text = 'Insgesamt'; $isBold = true; break;
                default: $text = '';
            }

            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent($text)
                    ->styleAlignCenter()
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
                        ->styleAlignCenter()
                        ->styleBorderBottom()
                    );
                $lineSectionList[] = $lineSection;
            }
            $lineSectionList[] = (new Section())
                ->addElementColumn((new Element())
//                    ->setContent('Vorb.-kl. u. -gr. f. Migranten')
                    ->setContent('Sonderklassen')
                    ->styleAlignCenter()
                    ->styleBorderBottom()
                );
            $lineSectionList[] = (new Section())
                ->addElementColumn((new Element())
                    ->setContent('Zusammen')
                    ->styleAlignCenter()
                    ->styleTextBold()
                );
            $section
                ->addSliceColumn((new Slice())
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->addSectionList($lineSectionList)
                    , '15%'
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
     * @param Section $section
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
                    ->styleAlignCenter()
                    ->styleBackgroundColor($isGrey ? 'lightgrey' : 'white')
                    ->styleBorderBottom()
                );
            $lineSectionList[] = $lineSection;
        }
        $lineSectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleAlignCenter()
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
                ->styleAlignCenter()
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
                , '10.833%'
            );
    }
}