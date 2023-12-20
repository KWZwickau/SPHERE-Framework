<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class K01
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportFS
 */
class K01
{
    /**
     * @return Slice[]
     */
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginBottom('10px')
            ->addElement((new Element())
                ->setContent('K01. Klassen im Schuljahr {{ Content.SchoolYear.Current }} nach Zeitform des Unterrichts, 
                    Ausbildungsstatus und Klassenstufen'
                )
            );

        $width[0] = '20%';
        $width[1] = '30%';
        $width[2] = '40%';
        $width[3] = '10%';
        $width['level'] = '10%';

        $sliceList[] = (new Slice())
            ->styleBackgroundColor('lightgrey')
            ->styleAlignCenter()
            ->styleBorderTop()
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Ausbildungsstatus')
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop('26.5px')
                    ->stylePaddingBottom('27.7px')
                    , $width[0])
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Vollzeit')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('1')
                            ->styleAlignCenter()
                            ->stylePaddingTop('9px')
                            ->stylePaddingBottom('9px')
                            ->styleBorderRight(), '33.333%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('2')
                            ->styleAlignCenter()
                            ->stylePaddingTop('9px')
                            ->stylePaddingBottom('9px')
                            ->styleBorderRight(), '33.333%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('3')
                            ->styleAlignCenter()
                            ->stylePaddingTop('9px')
                            ->stylePaddingBottom('9px')
                            ->styleBorderRight(), '33.333%'
                        )
                    )
                    , $width[1])
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Teilzeit')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            ->styleBorderRight()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe')
                            ->styleAlignCenter()
                            ->styleAlignLeft()
                            ->stylePaddingLeft('2px')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                        )
                    )
                    ->addSection((new Section())
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('1')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('1. AJ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight(), '50%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('2. AJ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight(), '50%'
                                )
                            )
                        , '50%')
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('2')
                                    ->styleAlignCenter()
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('3. AJ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight(), '50%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('4. AJ')
                                    ->styleAlignCenter()
                                    ->styleBorderRight(), '50%'
                                )
                            )
                        , '50%')
                    ), $width[2])
                ->addElementColumn((new Element())
                    ->setContent('Insgesamt')
                    ->styleAlignCenter()
                    ->stylePaddingTop('26.5px')
                    ->stylePaddingBottom('26.7px')
                    ->styleTextBold()
                    , $width[3])
            );

        for ($i = 0; $i < 3; $i++) {
            $isBold = false;
            $isGrey = false;
            switch ($i) {
                case 0:
                    $text = 'Auszubildende/Schüler';
                    $identifier = 'Student';
                    break;
                case 1:
                    $text = 'Umschüler';
                    $identifier = 'ChangeStudent';
                    break;
                case 2:
                    $text = 'Insgesamt';
                    $identifier = 'TotalCount';
                    $isBold = true;
                    $isGrey = true;
                    break;
                default:
                    $text = '';
                    $identifier = '';
            }

            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent($text)
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    , $width[0]);

            // Klassenstufe Vollzeit
            for ($j = 1; $j < 4; $j++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.K01.FullTime.' . $identifier . '.L' . $j . ' is not empty) %}
                                {{ Content.K01.FullTime.' . $identifier . '.L' . $j . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBackgroundColor($isGrey ? 'lightgrey' : 'white')
                        ->styleBorderRight()
                        , $width['level']);
            }

            // Klassenstufe Teilzeit
            for ($j = 1; $j < 5; $j++) {
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.K01.PartTime.' . $identifier . '.L' . $j . ' is not empty) %}
                                {{ Content.K01.PartTime.' . $identifier . '.L' . $j . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleAlignCenter()
                        ->styleBackgroundColor($isGrey ? 'lightgrey' : 'white')
                        ->styleBorderRight()
                        , $width['level']);
            }

            // Insgesamt
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.K01.TotalCount.' . $identifier . ' is not empty) %}
                                {{ Content.K01.TotalCount.' . $identifier . ' }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleAlignCenter()
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold()
                    , $width['level']);

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
}