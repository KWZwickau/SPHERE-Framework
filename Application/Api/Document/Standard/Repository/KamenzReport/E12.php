<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 14:52
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReport;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class E12
 * @deprecated
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReport
 */
class E12
{
    public static function getContent()
    {
        $sliceList = array();

        $sliceList[] = (new Slice())
            ->styleTextBold()
            ->styleMarginTop('20px')
            ->styleMarginBottom('5px')
            ->addElement((new Element())
                ->setContent('E12. Schüler im NEIGUNGSKURSBEREICH im Schuljahr {{ Content.SchoolYear.Current }} nach Klassenstufen')
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
                    ->setContent('Neigungskursbereich')
                    ->styleBorderRight()
                    ->stylePaddingTop('17.7px')
                    ->stylePaddingBottom('17.6px'), '30%'
                )
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Klassenstufe')
                            ->styleBorderRight()
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('5')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('6')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('7')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('8')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9')
                            ->styleBorderBottom()
                            ->styleBorderRight(), '10%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10')
                            ->styleBorderBottom()
                            ->styleBorderRight()
                            , '10%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w')
                            ->styleBorderRight()
                            , '5%'
                        )
                    ), '60%'
                )
                ->addSliceColumn((new Slice())
                    ->styleTextBold()
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Insgesamt')
                            ->styleBorderBottom()
                            ->stylePaddingTop('8.6px')
                            ->stylePaddingBottom('8.5px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('m')
                            ->styleBorderRight(), '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('w'), '50%'
                        )
                    ), '10%'
                )
            );

        for ($i = 0; $i < 10; $i++) {
            $section = new Section();
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E12.S' . $i . '.SubjectName is not empty) %}
                                {{ Content.E12.S' . $i . '.SubjectName }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight()
                    ->styleBackgroundColor('lightgrey')
                    , '30%'
                );
            for ($level = 5; $level <= 10; $level++) {
                if ($level > 6 && $level < 10) {
                    $isGrey = false;
                } else {
                    $isGrey = true;
                }

                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E12.S' . $i . '.L' . $level . '.m is not empty) %}
                                {{ Content.E12.S' . $i . '.L' . $level . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight()
                        ->styleBackgroundColor($isGrey ? 'lightgrey' : 'white')
                        , '5%'
                    );
                $section
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if (Content.E12.S' . $i . '.L' . $level . '.w is not empty) %}
                                {{ Content.E12.S' . $i . '.L' . $level . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                        ->styleBorderRight()
                        ->styleBackgroundColor($isGrey ? 'lightgrey' : 'white')
                        , '5%'
                    );
            }
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E12.S' . $i . '.TotalCount.m is not empty) %}
                                {{ Content.E12.S' . $i . '.TotalCount.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBackgroundColor('lightgrey')
                    ->styleBorderRight()
                    ->styleTextBold(), '5%'
                )
                ->addElementColumn((new Element())
                    ->setContent('
                                {% if (Content.E12.S' . $i . '.TotalCount.w is not empty) %}
                                    {{ Content.E12.S' . $i . '.TotalCount.w }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                    ->styleBackgroundColor('lightgrey')
                    ->styleTextBold(), '5%'
                );

            $sliceList[] = (new Slice())
                ->styleAlignCenter()
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
                ->setContent('
                            Insgesamt                       
                        ')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight(), '30%'
            );
        for ($level = 5; $level <= 10; $level++) {
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E12.TotalCount.L' . $level . '.m is not empty) %}
                                {{ Content.E12.TotalCount.L' . $level . '.m }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), '5%'
                );
            $section
                ->addElementColumn((new Element())
                    ->setContent('
                            {% if (Content.E12.TotalCount.L' . $level . '.w is not empty) %}
                                {{ Content.E12.TotalCount.L' . $level . '.w }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderRight(), '5%'
                );
        }
        $section
            ->addElementColumn((new Element())
                ->setContent('
                            &nbsp;
                        ')
                ->styleBackgroundColor('lightgrey')
                ->styleBorderRight()
                ->styleTextBold(), '5%'
            )
            ->addElementColumn((new Element())
                ->setContent('
                             &nbsp;   
                            ')
                ->styleBackgroundColor('lightgrey')
                ->styleTextBold(), '5%'
            );

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